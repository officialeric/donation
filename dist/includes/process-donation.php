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

// Include payment gateway integrations
require_once 'stripe_integration.php';
require_once 'paypal_integration.php';
require_once 'mpesa_integration.php';

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
 * Process credit card payment (Real Stripe integration)
 */
function processCreditCardPayment($transaction_id, $amount, $post_data) {
    // Validate credit card data first
    $card_errors = SecurityValidator::validateCreditCardData($post_data);
    if (!empty($card_errors)) {
        return ['success' => false, 'error' => implode('. ', $card_errors)];
    }

    try {
        // Initialize Stripe integration
        $stripe = new StripeIntegration();

        // Prepare payment data
        $expiry_parts = explode('/', $post_data['card_expiry']);
        $payment_data = [
            'transaction_id' => $transaction_id,
            'amount' => $amount,
            'currency' => 'USD',
            'card_number' => PaymentConfig::sanitizeCardNumber($post_data['card_number']),
            'exp_month' => intval($expiry_parts[0]),
            'exp_year' => intval('20' . $expiry_parts[1]), // Convert YY to YYYY
            'cvc' => $post_data['card_cvv'],
            'cardholder_name' => $post_data['card_holder_name'],
            'orphanage_id' => $_POST['orphanage_id'] ?? null,
            'donor_id' => $_SESSION['user_id'] ?? null,
            'campaign_id' => $_POST['campaign_id'] ?? null
        ];

        // Process payment through Stripe
        $result = $stripe->processPayment($payment_data);

        if ($result['success']) {
            return [
                'success' => true,
                'gateway_transaction_id' => $result['transaction_id'],
                'data' => [
                    'card_type' => $result['brand'] ?? 'unknown',
                    'last_four' => $result['last_four'] ?? '****',
                    'status' => 'completed'
                ]
            ];
        } else {
            return [
                'success' => false,
                'error' => $result['error'] ?? 'Payment processing failed'
            ];
        }

    } catch (Exception $e) {
        error_log('Stripe payment error: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Payment processing failed. Please try again.'
        ];
    }
}

/**
 * Process PayPal payment (Real PayPal integration)
 */
function processPayPalPayment($transaction_id, $amount, $post_data) {
    // Validate PayPal data
    $paypal_errors = SecurityValidator::validatePayPalData($post_data);
    if (!empty($paypal_errors)) {
        return ['success' => false, 'error' => implode('. ', $paypal_errors)];
    }

    try {
        // Initialize PayPal integration
        $paypal = new PayPalIntegration();

        // Get orphanage details for payment description
        global $db;
        $orphanage_id = $_POST['orphanage_id'] ?? null;
        $campaign_id = $_POST['campaign_id'] ?? null;

        $stmt = $db->prepare("SELECT name FROM orphanages WHERE id = ?");
        $stmt->bind_param('i', $orphanage_id);
        $stmt->execute();
        $orphanage_result = $stmt->get_result();
        $orphanage = $orphanage_result->fetch_assoc();

        $campaign_title = '';
        if ($campaign_id) {
            $stmt = $db->prepare("SELECT title FROM campaigns WHERE id = ?");
            $stmt->bind_param('i', $campaign_id);
            $stmt->execute();
            $campaign_result = $stmt->get_result();
            $campaign = $campaign_result->fetch_assoc();
            $campaign_title = $campaign['title'] ?? '';
        }

        // Prepare payment data
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $base_url = $protocol . '://' . $host;

        // For localhost development, use http
        if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
            $base_url = 'http://' . $host;
        }

        $payment_data = [
            'amount' => floatval($amount),
            'currency' => 'USD',
            'transaction_id' => $transaction_id,
            'orphanage_name' => $orphanage['name'] ?? 'Unknown Orphanage',
            'campaign_title' => $campaign_title,
            'return_url' => $base_url . '/donation/paypal-return.php?transaction_id=' . urlencode($transaction_id),
            'cancel_url' => $base_url . '/donation/make-donation.php?orphanage_id=' . $orphanage_id . '&error=' . urlencode('Payment cancelled')
        ];

        // Create PayPal payment
        $result = $paypal->createPayment($payment_data);

        if ($result['success']) {
            // Store PayPal payment ID for later execution
            $stmt = $db->prepare("UPDATE donations SET gateway_payment_id = ? WHERE transaction_id = ?");
            $stmt->bind_param('ss', $result['payment_id'], $transaction_id);
            $stmt->execute();

            // Redirect to PayPal for approval
            header('Location: ' . $result['approval_url']);
            exit;
        } else {
            return [
                'success' => false,
                'error' => $result['error'] ?? 'PayPal payment creation failed'
            ];
        }

    } catch (Exception $e) {
        error_log('PayPal payment error: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Payment processing failed. Please try again.'
        ];
    }
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
 * Process M-Pesa payment (Real M-Pesa integration)
 */
function processMPesaPayment($transaction_id, $amount, $post_data) {
    // Validate M-Pesa data
    $mpesa_errors = SecurityValidator::validateMPesaData($post_data);
    if (!empty($mpesa_errors)) {
        return ['success' => false, 'error' => implode('. ', $mpesa_errors)];
    }

    try {
        // Initialize M-Pesa integration
        $mpesa = new MPesaIntegration();

        // Get orphanage details
        global $db;
        $orphanage_id = $_POST['orphanage_id'] ?? null;

        $stmt = $db->prepare("SELECT name FROM orphanages WHERE id = ?");
        $stmt->bind_param('i', $orphanage_id);
        $stmt->execute();
        $orphanage_result = $stmt->get_result();
        $orphanage = $orphanage_result->fetch_assoc();

        // Prepare payment data
        $payment_data = [
            'amount' => $amount,
            'phone' => $post_data['mpesa_phone'],
            'transaction_id' => $transaction_id,
            'orphanage_name' => $orphanage['name'] ?? 'Unknown Orphanage',
            'callback_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/mpesa-callback.php'
        ];

        // Initiate STK Push
        $result = $mpesa->initiateSTKPush($payment_data);

        if ($result['success']) {
            // Store checkout request ID for status tracking
            $stmt = $db->prepare("UPDATE donations SET checkout_request_id = ? WHERE transaction_id = ?");
            $stmt->bind_param('ss', $result['checkout_request_id'], $transaction_id);
            $stmt->execute();

            return [
                'success' => true,
                'gateway_transaction_id' => $result['checkout_request_id'],
                'data' => [
                    'phone_number' => $post_data['mpesa_phone'],
                    'status' => 'pending',
                    'message' => 'Please check your phone for M-Pesa prompt'
                ]
            ];
        } else {
            return [
                'success' => false,
                'error' => $result['error'] ?? 'M-Pesa payment initiation failed'
            ];
        }

    } catch (Exception $e) {
        error_log('M-Pesa payment error: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Payment processing failed. Please try again.'
        ];
    }
}
?>
