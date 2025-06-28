<?php
/**
 * Stripe Payment Integration for TumainiFuraha
 * Real credit card processing using Stripe API
 */

require_once 'payment_config.php';

class StripeIntegration {
    
    private $stripe_secret_key;
    private $stripe_public_key;
    private $test_mode;
    
    public function __construct() {
        $config = PaymentConfig::getGatewayConfig('stripe');
        $this->stripe_secret_key = $config['secret_key'];
        $this->stripe_public_key = $config['public_key'];
        $this->test_mode = $config['test_mode'];
        
        // Set Stripe API key
        \Stripe\Stripe::setApiKey($this->stripe_secret_key);
    }
    
    /**
     * Process credit card payment through Stripe
     */
    public function processPayment($payment_data) {
        try {
            // Create payment intent
            $payment_intent = \Stripe\PaymentIntent::create([
                'amount' => $payment_data['amount'] * 100, // Convert to cents
                'currency' => strtolower($payment_data['currency'] ?? 'usd'),
                'payment_method_types' => ['card'],
                'metadata' => [
                    'transaction_id' => $payment_data['transaction_id'],
                    'orphanage_id' => $payment_data['orphanage_id'],
                    'donor_id' => $payment_data['donor_id'],
                    'campaign_id' => $payment_data['campaign_id'] ?? null
                ]
            ]);
            
            // Create payment method
            $payment_method = \Stripe\PaymentMethod::create([
                'type' => 'card',
                'card' => [
                    'number' => $payment_data['card_number'],
                    'exp_month' => $payment_data['exp_month'],
                    'exp_year' => $payment_data['exp_year'],
                    'cvc' => $payment_data['cvc']
                ],
                'billing_details' => [
                    'name' => $payment_data['cardholder_name']
                ]
            ]);
            
            // Confirm payment
            $confirmed_intent = $payment_intent->confirm([
                'payment_method' => $payment_method->id
            ]);
            
            if ($confirmed_intent->status === 'succeeded') {
                return [
                    'success' => true,
                    'transaction_id' => $confirmed_intent->id,
                    'status' => 'completed',
                    'amount' => $confirmed_intent->amount / 100,
                    'currency' => strtoupper($confirmed_intent->currency),
                    'payment_method' => 'card',
                    'last_four' => $payment_method->card->last4,
                    'brand' => $payment_method->card->brand
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Payment requires additional authentication',
                    'client_secret' => $confirmed_intent->client_secret
                ];
            }
            
        } catch (\Stripe\Exception\CardException $e) {
            return [
                'success' => false,
                'error' => $e->getError()->message
            ];
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return [
                'success' => false,
                'error' => 'Invalid payment information'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Payment processing failed'
            ];
        }
    }
    
    /**
     * Create customer for recurring donations
     */
    public function createCustomer($customer_data) {
        try {
            $customer = \Stripe\Customer::create([
                'email' => $customer_data['email'],
                'name' => $customer_data['name'],
                'metadata' => [
                    'user_id' => $customer_data['user_id']
                ]
            ]);
            
            return [
                'success' => true,
                'customer_id' => $customer->id
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Handle webhook events
     */
    public function handleWebhook($payload, $sig_header) {
        $endpoint_secret = PaymentConfig::getGatewayConfig('stripe')['webhook_secret'];
        
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
            
            switch ($event['type']) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentSuccess($event['data']['object']);
                    break;
                case 'payment_intent.payment_failed':
                    $this->handlePaymentFailure($event['data']['object']);
                    break;
                default:
                    error_log('Unhandled Stripe event type: ' . $event['type']);
            }
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function handlePaymentSuccess($payment_intent) {
        // Update transaction status in database
        global $db;
        
        $transaction_id = $payment_intent['metadata']['transaction_id'];
        $stmt = $db->prepare("UPDATE donations SET payment_status = 'completed', gateway_transaction_id = ? WHERE transaction_id = ?");
        $stmt->bind_param('ss', $payment_intent['id'], $transaction_id);
        $stmt->execute();
        
        // Send confirmation email
        require_once 'email_notifications.php';
        sendDonationConfirmationEmail($transaction_id);
    }
    
    private function handlePaymentFailure($payment_intent) {
        // Update transaction status in database
        global $db;
        
        $transaction_id = $payment_intent['metadata']['transaction_id'];
        $stmt = $db->prepare("UPDATE donations SET payment_status = 'failed' WHERE transaction_id = ?");
        $stmt->bind_param('s', $transaction_id);
        $stmt->execute();
    }
}

/**
 * Installation function for Stripe
 */
function installStripe() {
    // Check if Stripe PHP library is installed
    if (!class_exists('\Stripe\Stripe')) {
        echo "Installing Stripe PHP library...\n";
        
        // Create composer.json if it doesn't exist
        if (!file_exists('composer.json')) {
            $composer_config = [
                'require' => [
                    'stripe/stripe-php' => '^7.0'
                ]
            ];
            file_put_contents('composer.json', json_encode($composer_config, JSON_PRETTY_PRINT));
        }
        
        // Install via composer
        exec('composer install', $output, $return_code);
        
        if ($return_code === 0) {
            echo "Stripe library installed successfully!\n";
            echo "Include the autoloader in your files: require_once 'vendor/autoload.php';\n";
        } else {
            echo "Failed to install Stripe library. Please run 'composer require stripe/stripe-php' manually.\n";
        }
    }
}
?>
