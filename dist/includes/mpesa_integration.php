<?php
/**
 * M-Pesa Payment Integration for TumainiFuraha
 * Real M-Pesa processing using Safaricom M-Pesa API
 */

require_once 'payment_config.php';

class MPesaIntegration {
    
    private $consumer_key;
    private $consumer_secret;
    private $business_short_code;
    private $passkey;
    private $base_url;
    private $test_mode;
    
    public function __construct() {
        $config = PaymentConfig::getGatewayConfig('mpesa');
        $this->consumer_key = $config['consumer_key'];
        $this->consumer_secret = $config['consumer_secret'];
        $this->business_short_code = $config['business_short_code'];
        $this->passkey = $config['passkey'];
        $this->test_mode = $config['test_mode'];
        
        // Set base URL based on environment
        $this->base_url = $this->test_mode ? 
            'https://sandbox.safaricom.co.ke' : 
            'https://api.safaricom.co.ke';
    }
    
    /**
     * Get M-Pesa access token
     */
    private function getAccessToken() {
        $credentials = base64_encode($this->consumer_key . ':' . $this->consumer_secret);
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/oauth/v1/generate?grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . $credentials,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $data = json_decode($response, true);
            return $data['access_token'];
        }
        
        return false;
    }
    
    /**
     * Generate password for STK push
     */
    private function generatePassword() {
        $timestamp = date('YmdHis');
        $password = base64_encode($this->business_short_code . $this->passkey . $timestamp);
        
        return [
            'password' => $password,
            'timestamp' => $timestamp
        ];
    }
    
    /**
     * Initiate STK Push (Lipa na M-Pesa Online)
     */
    public function initiateSTKPush($payment_data) {
        $access_token = $this->getAccessToken();
        
        if (!$access_token) {
            return ['success' => false, 'error' => 'Failed to authenticate with M-Pesa'];
        }
        
        $password_data = $this->generatePassword();
        
        // Format phone number (remove leading 0, add 254)
        $phone = $payment_data['phone'];
        if (substr($phone, 0, 1) === '0') {
            $phone = '254' . substr($phone, 1);
        }
        
        $stk_payload = [
            'BusinessShortCode' => $this->business_short_code,
            'Password' => $password_data['password'],
            'Timestamp' => $password_data['timestamp'],
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int)$payment_data['amount'],
            'PartyA' => $phone,
            'PartyB' => $this->business_short_code,
            'PhoneNumber' => $phone,
            'CallBackURL' => $payment_data['callback_url'],
            'AccountReference' => $payment_data['transaction_id'],
            'TransactionDesc' => 'Donation to ' . $payment_data['orphanage_name']
        ];
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/mpesa/stkpush/v1/processrequest');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($stk_payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $data = json_decode($response, true);
            
            if ($data['ResponseCode'] === '0') {
                return [
                    'success' => true,
                    'checkout_request_id' => $data['CheckoutRequestID'],
                    'merchant_request_id' => $data['MerchantRequestID'],
                    'status' => 'pending',
                    'message' => 'STK push sent successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $data['ResponseDescription'] ?? 'STK push failed'
                ];
            }
        }
        
        return [
            'success' => false,
            'error' => 'Failed to initiate M-Pesa payment'
        ];
    }
    
    /**
     * Query STK Push status
     */
    public function querySTKStatus($checkout_request_id) {
        $access_token = $this->getAccessToken();
        
        if (!$access_token) {
            return ['success' => false, 'error' => 'Failed to authenticate with M-Pesa'];
        }
        
        $password_data = $this->generatePassword();
        
        $query_payload = [
            'BusinessShortCode' => $this->business_short_code,
            'Password' => $password_data['password'],
            'Timestamp' => $password_data['timestamp'],
            'CheckoutRequestID' => $checkout_request_id
        ];
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/mpesa/stkpushquery/v1/query');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query_payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $data = json_decode($response, true);
            
            return [
                'success' => true,
                'result_code' => $data['ResultCode'],
                'result_desc' => $data['ResultDesc'],
                'status' => $data['ResultCode'] === '0' ? 'completed' : 'failed'
            ];
        }
        
        return [
            'success' => false,
            'error' => 'Failed to query payment status'
        ];
    }
    
    /**
     * Handle M-Pesa callback
     */
    public function handleCallback($payload) {
        $callback_data = json_decode($payload, true);
        
        if (isset($callback_data['Body']['stkCallback'])) {
            $stk_callback = $callback_data['Body']['stkCallback'];
            
            $checkout_request_id = $stk_callback['CheckoutRequestID'];
            $result_code = $stk_callback['ResultCode'];
            
            if ($result_code === 0) {
                // Payment successful
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
                
                $this->updatePaymentStatus($checkout_request_id, 'completed', $mpesa_receipt_number);
                
                return [
                    'success' => true,
                    'status' => 'completed',
                    'amount' => $amount,
                    'receipt_number' => $mpesa_receipt_number,
                    'phone_number' => $phone_number
                ];
            } else {
                // Payment failed
                $this->updatePaymentStatus($checkout_request_id, 'failed', null);
                
                return [
                    'success' => false,
                    'status' => 'failed',
                    'error' => $stk_callback['ResultDesc']
                ];
            }
        }
        
        return ['success' => false, 'error' => 'Invalid callback data'];
    }
    
    /**
     * Update payment status in database
     */
    private function updatePaymentStatus($checkout_request_id, $status, $receipt_number) {
        global $db;
        
        $stmt = $db->prepare("UPDATE donations SET payment_status = ?, gateway_transaction_id = ? WHERE checkout_request_id = ?");
        $stmt->bind_param('sss', $status, $receipt_number, $checkout_request_id);
        $stmt->execute();
        
        if ($status === 'completed') {
            // Get transaction ID and send confirmation email
            $stmt = $db->prepare("SELECT transaction_id FROM donations WHERE checkout_request_id = ?");
            $stmt->bind_param('s', $checkout_request_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                require_once 'email_notifications.php';
                sendDonationConfirmationEmail($row['transaction_id']);
            }
        }
    }
}

/**
 * Installation function for M-Pesa
 */
function installMPesa() {
    echo "M-Pesa integration uses REST API - no additional libraries required.\n";
    echo "Make sure cURL is enabled in your PHP installation.\n";
    echo "Sign up for M-Pesa Developer account at: https://developer.safaricom.co.ke/\n";
    echo "You'll need to register your app and get:\n";
    echo "- Consumer Key\n";
    echo "- Consumer Secret\n";
    echo "- Business Short Code\n";
    echo "- Passkey\n";
}
?>
