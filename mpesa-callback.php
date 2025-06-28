<?php
/**
 * M-Pesa Callback Handler for TumainiFuraha
 * Handles real-time payment status updates from M-Pesa
 */

require_once 'dist/includes/connection.php';
require_once 'dist/includes/mpesa_integration.php';
require_once 'dist/includes/email_notifications.php';

// Set content type
header('Content-Type: application/json');

try {
    // Get callback data
    $callback_data = file_get_contents('php://input');
    
    // Log callback for debugging (remove in production)
    error_log('M-Pesa Callback Received: ' . $callback_data);
    
    // Validate callback data
    if (empty($callback_data)) {
        http_response_code(400);
        echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'No callback data received']);
        exit;
    }
    
    // Initialize M-Pesa integration
    $mpesa = new MPesaIntegration();
    
    // Process the callback
    $result = $mpesa->handleCallback($callback_data);
    
    if ($result['success']) {
        // Parse callback data to get transaction details
        $callback_json = json_decode($callback_data, true);
        
        if (isset($callback_json['Body']['stkCallback'])) {
            $stk_callback = $callback_json['Body']['stkCallback'];
            $checkout_request_id = $stk_callback['CheckoutRequestID'];
            $result_code = $stk_callback['ResultCode'];
            
            if ($result_code === 0) {
                // Payment successful - update database
                $callback_metadata = $stk_callback['CallbackMetadata']['Item'];
                
                $amount = 0;
                $mpesa_receipt_number = '';
                $phone_number = '';
                
                foreach ($callback_metadata as $item) {
                    switch ($item['Name']) {
                        case 'Amount':
                            $amount = $item['Value'];
                            break;
                        case 'MpesaReceiptNumber':
                            $mpesa_receipt_number = $item['Value'];
                            break;
                        case 'PhoneNumber':
                            $phone_number = $item['Value'];
                            break;
                    }
                }
                
                // Update donation status
                $stmt = $db->prepare("
                    UPDATE donations 
                    SET payment_status = 'completed',
                        gateway_transaction_id = ?,
                        updated_at = NOW()
                    WHERE checkout_request_id = ?
                ");
                $stmt->bind_param('ss', $mpesa_receipt_number, $checkout_request_id);
                
                if ($stmt->execute()) {
                    // Get donation details for campaign update
                    $stmt = $db->prepare("
                        SELECT transaction_id, campaign_id, amount 
                        FROM donations 
                        WHERE checkout_request_id = ?
                    ");
                    $stmt->bind_param('s', $checkout_request_id);
                    $stmt->execute();
                    $donation_result = $stmt->get_result();
                    $donation = $donation_result->fetch_assoc();
                    
                    if ($donation) {
                        // Update campaign amount if applicable
                        if ($donation['campaign_id']) {
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
                        sendDonationConfirmationEmail($donation['transaction_id']);
                    }
                }
                
                // Return success response
                http_response_code(200);
                echo json_encode([
                    'ResultCode' => 0,
                    'ResultDesc' => 'Payment processed successfully'
                ]);
                
            } else {
                // Payment failed - update database
                $stmt = $db->prepare("
                    UPDATE donations 
                    SET payment_status = 'failed',
                        updated_at = NOW()
                    WHERE checkout_request_id = ?
                ");
                $stmt->bind_param('s', $checkout_request_id);
                $stmt->execute();
                
                // Return success response (we processed the callback successfully)
                http_response_code(200);
                echo json_encode([
                    'ResultCode' => 0,
                    'ResultDesc' => 'Payment failure processed'
                ]);
            }
        } else {
            throw new Exception('Invalid callback structure');
        }
    } else {
        throw new Exception($result['error'] ?? 'Callback processing failed');
    }
    
} catch (Exception $e) {
    error_log('M-Pesa Callback Error: ' . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'ResultCode' => 1,
        'ResultDesc' => 'Internal server error'
    ]);
}
?>
