<?php
/**
 * PayPal Payment Integration for TumainiFuraha
 * Real PayPal processing using PayPal REST API
 */

require_once 'payment_config.php';

class PayPalIntegration {
    
    private $client_id;
    private $client_secret;
    private $base_url;
    private $test_mode;
    
    public function __construct() {
        $config = PaymentConfig::getGatewayConfig('paypal');

        if (!$config) {
            throw new Exception('PayPal configuration not found');
        }

        if (empty($config['client_id']) || empty($config['client_secret'])) {
            throw new Exception('PayPal client ID and secret are required');
        }

        $this->client_id = $config['client_id'];
        $this->client_secret = $config['client_secret'];
        $this->test_mode = $config['test_mode'] ?? true;

        // Set base URL based on environment
        $this->base_url = $this->test_mode ?
            'https://api.sandbox.paypal.com' :
            'https://api.paypal.com';

        // Debug logging
        error_log("PayPal Integration initialized - Test Mode: " . ($this->test_mode ? 'Yes' : 'No'));
        error_log("PayPal Base URL: " . $this->base_url);
    }
    
    /**
     * Get PayPal access token
     */
    private function getAccessToken() {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->client_id . ':' . $this->client_secret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Accept-Language: en_US'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        // Debug logging
        error_log("PayPal Auth Request - URL: " . $this->base_url . '/v1/oauth2/token');
        error_log("PayPal Auth Response - HTTP Code: $http_code");
        error_log("PayPal Auth Response - Body: " . substr($response, 0, 500));

        if ($curl_error) {
            error_log("PayPal Auth cURL Error: " . $curl_error);
            return false;
        }

        if ($http_code === 200) {
            $data = json_decode($response, true);
            if (isset($data['access_token'])) {
                return $data['access_token'];
            } else {
                error_log("PayPal Auth Error: No access token in response");
                return false;
            }
        } else {
            error_log("PayPal Auth Failed - HTTP $http_code: $response");
            return false;
        }
    }
    
    /**
     * Create PayPal payment
     */
    public function createPayment($payment_data) {
        $access_token = $this->getAccessToken();
        
        if (!$access_token) {
            return ['success' => false, 'error' => 'Failed to authenticate with PayPal'];
        }
        
        // Validate required data
        if (!isset($payment_data['amount']) || !isset($payment_data['return_url']) || !isset($payment_data['cancel_url'])) {
            return ['success' => false, 'error' => 'Missing required payment data'];
        }

        // Ensure amount is properly formatted
        $amount = number_format(floatval($payment_data['amount']), 2, '.', '');
        $currency = $payment_data['currency'] ?? 'USD';

        // Sanitize text fields
        $orphanage_name = substr(strip_tags($payment_data['orphanage_name'] ?? 'TumainiFuraha'), 0, 127);
        $description = substr('Donation to ' . $orphanage_name, 0, 127);
        $item_name = substr('Donation to ' . $orphanage_name, 0, 127);
        $item_description = substr($payment_data['campaign_title'] ?? 'General donation', 0, 127);

        $payment_payload = [
            'intent' => 'sale',
            'payer' => [
                'payment_method' => 'paypal'
            ],
            'transactions' => [[
                'amount' => [
                    'total' => $amount,
                    'currency' => $currency
                ],
                'description' => $description,
                'custom' => $payment_data['transaction_id'],
                'item_list' => [
                    'items' => [[
                        'name' => $item_name,
                        'description' => $item_description,
                        'quantity' => '1',
                        'price' => $amount,
                        'currency' => $currency
                    ]]
                ]
            ]],
            'redirect_urls' => [
                'return_url' => $payment_data['return_url'],
                'cancel_url' => $payment_data['cancel_url']
            ]
        ];
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/v1/payments/payment');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        // Debug logging
        error_log("PayPal Create Payment - HTTP Code: $http_code");
        error_log("PayPal Create Payment - Response: " . substr($response, 0, 1000));
        error_log("PayPal Create Payment - Payload: " . json_encode($payment_payload));

        if ($curl_error) {
            error_log("PayPal Create Payment cURL Error: " . $curl_error);
            return [
                'success' => false,
                'error' => 'Network error: ' . $curl_error
            ];
        }

        if ($http_code === 201) {
            $payment = json_decode($response, true);

            if (!$payment || !isset($payment['id'])) {
                error_log("PayPal Create Payment Error: Invalid response structure");
                return [
                    'success' => false,
                    'error' => 'Invalid PayPal response'
                ];
            }

            // Find approval URL
            $approval_url = '';
            if (isset($payment['links'])) {
                foreach ($payment['links'] as $link) {
                    if ($link['rel'] === 'approval_url') {
                        $approval_url = $link['href'];
                        break;
                    }
                }
            }

            if (empty($approval_url)) {
                error_log("PayPal Create Payment Error: No approval URL found");
                return [
                    'success' => false,
                    'error' => 'No approval URL received from PayPal'
                ];
            }

            return [
                'success' => true,
                'payment_id' => $payment['id'],
                'approval_url' => $approval_url,
                'status' => 'created'
            ];
        } else {
            // Parse error response
            $error_data = json_decode($response, true);
            $error_message = 'Failed to create PayPal payment';

            if ($error_data && isset($error_data['message'])) {
                $error_message = $error_data['message'];
            } elseif ($error_data && isset($error_data['error_description'])) {
                $error_message = $error_data['error_description'];
            }

            error_log("PayPal Create Payment Failed - HTTP $http_code: $error_message");

            return [
                'success' => false,
                'error' => $error_message
            ];
        }
    }
    
    /**
     * Execute PayPal payment after approval
     */
    public function executePayment($payment_id, $payer_id) {
        $access_token = $this->getAccessToken();
        
        if (!$access_token) {
            return ['success' => false, 'error' => 'Failed to authenticate with PayPal'];
        }
        
        $execute_payload = [
            'payer_id' => $payer_id
        ];
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/v1/payments/payment/' . $payment_id . '/execute');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($execute_payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $payment = json_decode($response, true);
            
            if ($payment['state'] === 'approved') {
                return [
                    'success' => true,
                    'transaction_id' => $payment['id'],
                    'status' => 'completed',
                    'payer_email' => $payment['payer']['payer_info']['email'],
                    'amount' => $payment['transactions'][0]['amount']['total'],
                    'currency' => $payment['transactions'][0]['amount']['currency']
                ];
            }
        }
        
        return [
            'success' => false,
            'error' => 'Failed to execute PayPal payment'
        ];
    }
    
    /**
     * Handle PayPal webhook
     */
    public function handleWebhook($payload, $headers) {
        // Verify webhook signature (implement based on PayPal documentation)
        // Process webhook events
        
        $event = json_decode($payload, true);
        
        switch ($event['event_type']) {
            case 'PAYMENT.SALE.COMPLETED':
                $this->handlePaymentCompleted($event);
                break;
            case 'PAYMENT.SALE.DENIED':
                $this->handlePaymentDenied($event);
                break;
        }
        
        return ['success' => true];
    }
    
    private function handlePaymentCompleted($event) {
        // Update database with completed payment
        global $db;
        
        $custom_id = $event['resource']['custom'];
        $stmt = $db->prepare("UPDATE donations SET payment_status = 'completed', gateway_transaction_id = ? WHERE transaction_id = ?");
        $stmt->bind_param('ss', $event['resource']['id'], $custom_id);
        $stmt->execute();
    }
    
    private function handlePaymentDenied($event) {
        // Update database with failed payment
        global $db;
        
        $custom_id = $event['resource']['custom'];
        $stmt = $db->prepare("UPDATE donations SET payment_status = 'failed' WHERE transaction_id = ?");
        $stmt->bind_param('s', $custom_id);
        $stmt->execute();
    }
}

/**
 * Installation function for PayPal
 */
function installPayPal() {
    echo "PayPal integration uses REST API - no additional libraries required.\n";
    echo "Make sure cURL is enabled in your PHP installation.\n";
    echo "Sign up for PayPal Developer account at: https://developer.paypal.com/\n";
}
?>
