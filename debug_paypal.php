<?php
/**
 * Quick PayPal Debug Script
 * Run this to see exactly what's happening with your PayPal integration
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'dist/includes/payment_config.php';

echo "<h1>PayPal Debug Information</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } pre { background: #f5f5f5; padding: 10px; border-radius: 3px; }</style>";

// Step 1: Check configuration
echo "<h2>1. Configuration Check</h2>";
$config = PaymentConfig::getGatewayConfig('paypal');
echo "<pre>";
print_r($config);
echo "</pre>";

if (!$config) {
    echo "<p style='color: red;'>❌ PayPal configuration not found!</p>";
    exit;
}

// Step 2: Test authentication manually
echo "<h2>2. Manual Authentication Test</h2>";

$client_id = $config['client_id'];
$client_secret = $config['client_secret'];
$test_mode = $config['test_mode'];

$base_url = $test_mode ? 'https://api.sandbox.paypal.com' : 'https://api.paypal.com';

echo "<p><strong>Using:</strong></p>";
echo "<ul>";
echo "<li>Base URL: $base_url</li>";
echo "<li>Client ID: " . substr($client_id, 0, 20) . "...</li>";
echo "<li>Client Secret: " . substr($client_secret, 0, 20) . "...</li>";
echo "</ul>";

// Test authentication
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $base_url . '/v1/oauth2/token');
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, $client_id . ':' . $client_secret);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Accept-Language: en_US'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "<h3>Authentication Response:</h3>";
echo "<p><strong>HTTP Code:</strong> $http_code</p>";

if ($curl_error) {
    echo "<p style='color: red;'><strong>cURL Error:</strong> $curl_error</p>";
}

echo "<p><strong>Response:</strong></p>";
echo "<pre>$response</pre>";

$auth_data = json_decode($response, true);

if ($http_code === 200 && isset($auth_data['access_token'])) {
    echo "<p style='color: green;'>✅ Authentication successful!</p>";
    $access_token = $auth_data['access_token'];
    echo "<p><strong>Access Token:</strong> " . substr($access_token, 0, 30) . "...</p>";
    
    // Step 3: Test payment creation
    echo "<h2>3. Payment Creation Test</h2>";
    
    $payment_payload = [
        'intent' => 'sale',
        'payer' => [
            'payment_method' => 'paypal'
        ],
        'transactions' => [[
            'amount' => [
                'total' => '10.00',
                'currency' => 'USD'
            ],
            'description' => 'Test donation to TumainiFuraha',
            'custom' => 'TEST_' . time()
        ]],
        'redirect_urls' => [
            'return_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/paypal-return.php',
            'cancel_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/make-donation.php'
        ]
    ];
    
    echo "<p><strong>Payment Payload:</strong></p>";
    echo "<pre>" . json_encode($payment_payload, JSON_PRETTY_PRINT) . "</pre>";
    
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $base_url . '/v1/payments/payment');
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ]);
    
    $payment_response = curl_exec($ch);
    $payment_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $payment_curl_error = curl_error($ch);
    curl_close($ch);
    
    echo "<h3>Payment Creation Response:</h3>";
    echo "<p><strong>HTTP Code:</strong> $payment_http_code</p>";
    
    if ($payment_curl_error) {
        echo "<p style='color: red;'><strong>cURL Error:</strong> $payment_curl_error</p>";
    }
    
    echo "<p><strong>Response:</strong></p>";
    echo "<pre>$payment_response</pre>";
    
    if ($payment_http_code === 201) {
        echo "<p style='color: green;'>✅ Payment creation successful!</p>";
        $payment_data = json_decode($payment_response, true);
        
        // Find approval URL
        $approval_url = '';
        if (isset($payment_data['links'])) {
            foreach ($payment_data['links'] as $link) {
                if ($link['rel'] === 'approval_url') {
                    $approval_url = $link['href'];
                    break;
                }
            }
        }
        
        if ($approval_url) {
            echo "<p><strong>Approval URL:</strong> <a href='$approval_url' target='_blank'>$approval_url</a></p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Payment creation failed!</p>";
        $error_data = json_decode($payment_response, true);
        if ($error_data) {
            echo "<p><strong>Error Details:</strong></p>";
            echo "<pre>" . json_encode($error_data, JSON_PRETTY_PRINT) . "</pre>";
        }
    }
    
} else {
    echo "<p style='color: red;'>❌ Authentication failed!</p>";
    
    if (isset($auth_data['error'])) {
        echo "<p><strong>Error:</strong> " . $auth_data['error'] . "</p>";
    }
    if (isset($auth_data['error_description'])) {
        echo "<p><strong>Description:</strong> " . $auth_data['error_description'] . "</p>";
    }
    
    echo "<h3>Common Issues:</h3>";
    echo "<ul>";
    echo "<li>Check if your Client ID and Client Secret are correct</li>";
    echo "<li>Make sure you're using the right environment (sandbox vs live)</li>";
    echo "<li>Verify your PayPal app is properly configured</li>";
    echo "<li>Check if your app has the required permissions</li>";
    echo "</ul>";
}

// Step 4: Environment check
echo "<h2>4. Environment Check</h2>";
echo "<ul>";
echo "<li><strong>PHP Version:</strong> " . phpversion() . "</li>";
echo "<li><strong>cURL:</strong> " . (extension_loaded('curl') ? '✅ Enabled' : '❌ Disabled') . "</li>";
echo "<li><strong>OpenSSL:</strong> " . (extension_loaded('openssl') ? '✅ Enabled' : '❌ Disabled') . "</li>";
echo "<li><strong>JSON:</strong> " . (extension_loaded('json') ? '✅ Enabled' : '❌ Disabled') . "</li>";
echo "</ul>";

echo "<p><strong>Debug completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
