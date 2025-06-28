<?php
/**
 * PayPal Return Handler for TumainiFuraha
 * Handles PayPal payment completion after user approval
 */

session_start();
require_once 'dist/includes/connection.php';
require_once 'dist/includes/paypal_integration.php';
require_once 'dist/includes/email_notifications.php';

// Get PayPal parameters
$payment_id = $_GET['paymentId'] ?? '';
$payer_id = $_GET['PayerID'] ?? '';
$transaction_id = $_GET['transaction_id'] ?? '';

// Validate parameters
if (empty($payment_id) || empty($payer_id) || empty($transaction_id)) {
    header('Location: index.php?error=' . urlencode('Invalid payment parameters'));
    exit;
}

try {
    // Initialize PayPal integration
    $paypal = new PayPalIntegration();
    
    // Execute the payment
    $result = $paypal->executePayment($payment_id, $payer_id);
    
    if ($result['success']) {
        // Update database with completed payment
        $stmt = $db->prepare("
            UPDATE donations 
            SET payment_status = 'completed', 
                gateway_transaction_id = ?,
                updated_at = NOW()
            WHERE transaction_id = ?
        ");
        $stmt->bind_param('ss', $result['transaction_id'], $transaction_id);
        
        if ($stmt->execute()) {
            // Update campaign amount if applicable
            $stmt = $db->prepare("
                SELECT campaign_id, amount 
                FROM donations 
                WHERE transaction_id = ?
            ");
            $stmt->bind_param('s', $transaction_id);
            $stmt->execute();
            $donation_result = $stmt->get_result();
            $donation = $donation_result->fetch_assoc();
            
            if ($donation && $donation['campaign_id']) {
                $stmt = $db->prepare("
                    UPDATE campaigns 
                    SET current_amount = current_amount + ? 
                    WHERE id = ?
                ");
                $stmt->bind_param('di', $donation['amount'], $donation['campaign_id']);
                $stmt->execute();
                
                // Check if campaign target is reached
                $stmt = $db->prepare("
                    SELECT target_amount, current_amount 
                    FROM campaigns 
                    WHERE id = ?
                ");
                $stmt->bind_param('i', $donation['campaign_id']);
                $stmt->execute();
                $campaign_result = $stmt->get_result();
                $campaign = $campaign_result->fetch_assoc();
                
                if ($campaign && $campaign['current_amount'] >= $campaign['target_amount']) {
                    $stmt = $db->prepare("
                        UPDATE campaigns 
                        SET status = 'completed' 
                        WHERE id = ?
                    ");
                    $stmt->bind_param('i', $donation['campaign_id']);
                    $stmt->execute();
                }
            }
            
            // Send confirmation email
            sendDonationConfirmationEmail($transaction_id);
            
            // Redirect to success page
            header('Location: payment-confirmation.php?transaction_id=' . urlencode($transaction_id));
            exit;
        } else {
            throw new Exception('Failed to update donation record');
        }
    } else {
        // Payment execution failed
        $stmt = $db->prepare("
            UPDATE donations 
            SET payment_status = 'failed',
                updated_at = NOW()
            WHERE transaction_id = ?
        ");
        $stmt->bind_param('s', $transaction_id);
        $stmt->execute();
        
        // Get orphanage ID for redirect
        $stmt = $db->prepare("SELECT orphanage_id FROM donations WHERE transaction_id = ?");
        $stmt->bind_param('s', $transaction_id);
        $stmt->execute();
        $donation_result = $stmt->get_result();
        $donation = $donation_result->fetch_assoc();
        
        $redirect_url = 'make-donation.php';
        if ($donation) {
            $redirect_url .= '?orphanage_id=' . $donation['orphanage_id'];
        }
        $redirect_url .= '&error=' . urlencode($result['error'] ?? 'PayPal payment failed');
        
        header('Location: ' . $redirect_url);
        exit;
    }
    
} catch (Exception $e) {
    error_log('PayPal return handler error: ' . $e->getMessage());
    
    // Update payment status to failed
    $stmt = $db->prepare("
        UPDATE donations 
        SET payment_status = 'failed',
            updated_at = NOW()
        WHERE transaction_id = ?
    ");
    $stmt->bind_param('s', $transaction_id);
    $stmt->execute();
    
    header('Location: index.php?error=' . urlencode('Payment processing failed'));
    exit;
}
?>
