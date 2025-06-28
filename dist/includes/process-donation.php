<?php
/**
 * Enhanced Payment Processing for TumainiFuraha
 * Handles secure donation processing with multiple payment methods
 */

session_start();
require_once 'connection.php';
require_once 'payment_config.php';
require_once 'email_notifications.php';
require_once 'security_validator.php';

// Security checks
if (!SecurityValidator::validateSession()) {
    header('Location: ../../login.php');
    exit;
}

// CSRF protection (basic implementation)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../index.php');
    exit;
}

// Rate limiting check
if (!SecurityValidator::checkRateLimit($_SESSION['user_id'])) {
    SecurityValidator::logSecurityEvent('rate_limit_exceeded', 'Payment attempt blocked', $_SESSION['user_id']);
    header('Location: ../../make-donation.php?error=' . urlencode('Too many attempts. Please try again later.'));
    exit;
}

try {
    // Validate and sanitize input data
    $user_id = $_SESSION['user_id'];
    $orphanage_id = filter_input(INPUT_POST, 'orphanage_id', FILTER_VALIDATE_INT);
    $campaign_id = filter_input(INPUT_POST, 'campaign_id', FILTER_VALIDATE_INT);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);
    $donor_message = filter_input(INPUT_POST, 'donor_message', FILTER_SANITIZE_STRING);
    $anonymous = filter_input(INPUT_POST, 'anonymous', FILTER_VALIDATE_BOOLEAN);
    
    // Build redirect URL for errors
    $redirect_url = "../../make-donation.php?orphanage_id=$orphanage_id";
    if ($campaign_id) {
        $redirect_url .= "&campaign_id=$campaign_id";
    }
    
    // Validation
    $errors = [];
    
    if (!$orphanage_id) {
        $errors[] = 'Invalid orphanage selected';
    }
    
    // Validate amount with security checks
    $amount_errors = SecurityValidator::validatePaymentAmount($amount, $_SESSION['user_id']);
    $errors = array_merge($errors, $amount_errors);

    // Check for suspicious activity
    $suspicious_flags = SecurityValidator::checkSuspiciousActivity($_SESSION['user_id'], $amount, $payment_method);
    if (!empty($suspicious_flags)) {
        $errors[] = 'Transaction flagged for review. Please contact support.';
    }
    
    if (!in_array($payment_method, ['credit_card', 'paypal', 'bank_transfer', 'mpesa'])) {
        $errors[] = 'Invalid payment method selected';
    }
    
    // Verify orphanage exists and is active
    $stmt = $db->prepare("SELECT id, name FROM orphanages WHERE id = ? AND status = 'active'");
    $stmt->bind_param('i', $orphanage_id);
    $stmt->execute();
    $orphanage_result = $stmt->get_result();
    
    if ($orphanage_result->num_rows === 0) {
        $errors[] = 'Orphanage not found or inactive';
    }
    
    // Verify campaign if specified
    if ($campaign_id) {
        $stmt = $db->prepare("SELECT id, title FROM campaigns WHERE id = ? AND orphanage_id = ? AND status = 'active'");
        $stmt->bind_param('ii', $campaign_id, $orphanage_id);
        $stmt->execute();
        $campaign_result = $stmt->get_result();
        
        if ($campaign_result->num_rows === 0) {
            $errors[] = 'Campaign not found or inactive';
        }
    }
    
    // If there are validation errors, redirect back
    if (!empty($errors)) {
        $error_message = implode('. ', $errors);
        header("Location: $redirect_url&error=" . urlencode($error_message));
        exit;
    }
    
    // Generate transaction ID
    $transaction_id = 'TF_' . date('Ymd') . '_' . strtoupper(bin2hex(random_bytes(8)));
    
    // Process payment based on method
    $payment_result = processPayment($payment_method, $transaction_id, $amount, $_POST);
    
    if ($payment_result['success']) {
        // Insert donation record
        $stmt = $db->prepare("
            INSERT INTO donations 
            (user_id, orphanage_id, campaign_id, amount, payment_method, transaction_id, message, payment_status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'completed', NOW())
        ");
        
        $stmt->bind_param('iiidsss', 
            $user_id, 
            $orphanage_id, 
            $campaign_id, 
            $amount, 
            $payment_method, 
            $transaction_id, 
            $donor_message
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create donation record');
        }
        
        // Update campaign amount if this is a campaign donation
        if ($campaign_id) {
            $stmt = $db->prepare("UPDATE campaigns SET current_amount = current_amount + ? WHERE id = ?");
            $stmt->bind_param('di', $amount, $campaign_id);
            $stmt->execute();
            
            // Check if campaign target is reached and mark as completed
            $stmt = $db->prepare("SELECT target_amount, current_amount FROM campaigns WHERE id = ?");
            $stmt->bind_param('i', $campaign_id);
            $stmt->execute();
            $completion_result = $stmt->get_result();
            $completion_data = $completion_result->fetch_assoc();
            
            if ($completion_data['current_amount'] >= $completion_data['target_amount']) {
                $stmt = $db->prepare("UPDATE campaigns SET status = 'completed' WHERE id = ?");
                $stmt->bind_param('i', $campaign_id);
                $stmt->execute();
            }
        }
        
        // Send confirmation email
        sendDonationConfirmationEmail($transaction_id);

        // Redirect to confirmation page
        header("Location: ../../payment-confirmation.php?transaction_id=" . urlencode($transaction_id));
        exit;
        
    } else {
        // Payment failed - insert failed record
        $stmt = $db->prepare("
            INSERT INTO donations 
            (user_id, orphanage_id, campaign_id, amount, payment_method, transaction_id, message, payment_status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'failed', NOW())
        ");
        
        $stmt->bind_param('iiidsss', 
            $user_id, 
            $orphanage_id, 
            $campaign_id, 
            $amount, 
            $payment_method, 
            $transaction_id, 
            $donor_message
        );
        $stmt->execute();
        
        header("Location: $redirect_url&error=" . urlencode($payment_result['error']));
        exit;
    }
    
} catch (Exception $e) {
    // Log error
    error_log("Payment processing error: " . $e->getMessage());
    
    // Redirect with error
    $redirect_url = "../../make-donation.php";
    if (isset($orphanage_id)) $redirect_url .= "?orphanage_id=$orphanage_id";
    if (isset($campaign_id)) $redirect_url .= "&campaign_id=$campaign_id";
    $redirect_url .= "&error=" . urlencode('Payment processing failed. Please try again.');
    header("Location: $redirect_url");
    exit;
}

/**
 * Process payment based on selected method
 */
function processPayment($payment_method, $transaction_id, $amount, $post_data) {
    switch ($payment_method) {
        case 'credit_card':
            return processCreditCardPayment($transaction_id, $amount, $post_data);
        case 'paypal':
            return processPayPalPayment($transaction_id, $amount, $post_data);
        case 'bank_transfer':
            return processBankTransferPayment($transaction_id, $amount, $post_data);
        case 'mpesa':
            return processMPesaPayment($transaction_id, $amount, $post_data);
        default:
            return ['success' => false, 'error' => 'Unsupported payment method'];
    }
}

/**
 * Process credit card payment (Stripe integration)
 */
function processCreditCardPayment($transaction_id, $amount, $post_data) {
    // Validate credit card data
    $card_number = PaymentConfig::sanitizeCardNumber($post_data['card_number'] ?? '');
    $card_holder_name = trim($post_data['card_holder_name'] ?? '');
    $card_expiry = $post_data['card_expiry'] ?? '';
    $card_cvv = $post_data['card_cvv'] ?? '';
    
    if (!PaymentConfig::validateCardNumber($card_number)) {
        return ['success' => false, 'error' => 'Invalid credit card number'];
    }
    
    if (!$card_holder_name) {
        return ['success' => false, 'error' => 'Cardholder name is required'];
    }
    
    $expiry_parts = explode('/', $card_expiry);
    if (count($expiry_parts) !== 2 || !PaymentConfig::validateExpiryDate($expiry_parts[0], $expiry_parts[1])) {
        return ['success' => false, 'error' => 'Invalid expiry date'];
    }
    
    $card_type = PaymentConfig::detectCardType($card_number);
    if (!PaymentConfig::validateCVV($card_cvv, $card_type)) {
        return ['success' => false, 'error' => 'Invalid CVV'];
    }
    
    // In a real implementation, you would integrate with Stripe here
    // For demo purposes, we'll simulate a successful payment
    return [
        'success' => true,
        'gateway_transaction_id' => 'stripe_' . uniqid(),
        'data' => [
            'card_type' => $card_type,
            'last_four' => substr($card_number, -4),
            'status' => 'completed'
        ]
    ];
}

/**
 * Process PayPal payment
 */
function processPayPalPayment($transaction_id, $amount, $post_data) {
    $paypal_email = filter_var($post_data['paypal_email'] ?? '', FILTER_VALIDATE_EMAIL);
    
    if (!$paypal_email) {
        return ['success' => false, 'error' => 'Invalid PayPal email address'];
    }
    
    // In a real implementation, you would integrate with PayPal API here
    return [
        'success' => true,
        'gateway_transaction_id' => 'paypal_' . uniqid(),
        'data' => [
            'paypal_email' => $paypal_email,
            'status' => 'completed'
        ]
    ];
}

/**
 * Process bank transfer payment
 */
function processBankTransferPayment($transaction_id, $amount, $post_data) {
    $bank_name = trim($post_data['bank_name'] ?? '');
    $account_holder_name = trim($post_data['account_holder_name'] ?? '');
    $account_number = trim($post_data['account_number'] ?? '');
    
    if (!$bank_name || !$account_holder_name || !$account_number) {
        return ['success' => false, 'error' => 'All bank details are required'];
    }
    
    return [
        'success' => true,
        'gateway_transaction_id' => 'bank_' . uniqid(),
        'data' => [
            'bank_name' => $bank_name,
            'account_holder_name' => $account_holder_name,
            'status' => 'pending_verification'
        ]
    ];
}

/**
 * Process M-Pesa payment
 */
function processMPesaPayment($transaction_id, $amount, $post_data) {
    $mpesa_phone = preg_replace('/[^0-9]/', '', $post_data['mpesa_phone'] ?? '');
    
    if (strlen($mpesa_phone) !== 10 || !preg_match('/^07/', $mpesa_phone)) {
        return ['success' => false, 'error' => 'Invalid M-Pesa phone number'];
    }
    
    // In a real implementation, you would integrate with M-Pesa API here
    return [
        'success' => true,
        'gateway_transaction_id' => 'mpesa_' . uniqid(),
        'data' => [
            'phone_number' => $mpesa_phone,
            'status' => 'completed'
        ]
    ];
}
?>
