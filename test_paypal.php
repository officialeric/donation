<?php
/**
 * PayPal Integration Test Script
 * Use this to test your PayPal credentials and diagnose issues
 */

require_once 'dist/includes/payment_config.php';
require_once 'dist/includes/paypal_integration.php';

echo "<h1>PayPal Integration Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
</style>";

// Test 1: Configuration Check
echo "<div class='test-section'>";
echo "<h2>Test 1: Configuration Check</h2>";

$config = PaymentConfig::getGatewayConfig('paypal');

echo "<p><strong>PayPal Configuration:</strong></p>";
echo "<pre>";
echo "Test Mode: " . ($config['test_mode'] ? 'Yes' : 'No') . "\n";
echo "Client ID: " . (strlen($config['client_id']) > 10 ? substr($config['client_id'], 0, 10) . '...' : 'NOT SET') . "\n";
echo "Client Secret: " . (strlen($config['client_secret']) > 10 ? substr($config['client_secret'], 0, 10) . '...' : 'NOT SET') . "\n";
echo "</pre>";

if (empty($config['client_id']) || empty($config['client_secret'])) {
    echo "<p class='error'>❌ PayPal credentials are not properly configured!</p>";
    echo "<p>Please update your credentials in <code>dist/includes/payment_config.php</code></p>";
} else {
    echo "<p class='success'>✅ PayPal credentials are configured</p>";
}

echo "</div>";

// Test 2: Authentication Test
echo "<div class='test-section'>";
echo "<h2>Test 2: PayPal Authentication Test</h2>";

try {
    $paypal = new PayPalIntegration();
    
    // Use reflection to access private method for testing
    $reflection = new ReflectionClass($paypal);
    $getAccessToken = $reflection->getMethod('getAccessToken');
    $getAccessToken->setAccessible(true);
    
    echo "<p class='info'>Testing PayPal authentication...</p>";
    
    $access_token = $getAccessToken->invoke($paypal);
    
    if ($access_token) {
        echo "<p class='success'>✅ PayPal authentication successful!</p>";
        echo "<p>Access Token: " . substr($access_token, 0, 20) . "...</p>";
    } else {
        echo "<p class='error'>❌ PayPal authentication failed!</p>";
        echo "<p>Check your Client ID and Client Secret in the configuration.</p>";
        echo "<p>Also check the error logs for more details.</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error during authentication test: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Test 3: Payment Creation Test
echo "<div class='test-section'>";
echo "<h2>Test 3: Payment Creation Test</h2>";

try {
    $paypal = new PayPalIntegration();
    
    // Test payment data
    $test_payment_data = [
        'amount' => 10.00,
        'currency' => 'USD',
        'transaction_id' => 'TEST_' . time(),
        'orphanage_name' => 'Test Orphanage',
        'campaign_title' => 'Test Campaign',
        'return_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/paypal-return.php?transaction_id=TEST_' . time(),
        'cancel_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/make-donation.php?error=Payment cancelled'
    ];
    
    echo "<p class='info'>Testing PayPal payment creation...</p>";
    echo "<p><strong>Test Payment Data:</strong></p>";
    echo "<pre>" . json_encode($test_payment_data, JSON_PRETTY_PRINT) . "</pre>";
    
    $result = $paypal->createPayment($test_payment_data);
    
    echo "<p><strong>PayPal Response:</strong></p>";
    echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
    
    if ($result['success']) {
        echo "<p class='success'>✅ PayPal payment creation successful!</p>";
        echo "<p>Payment ID: " . $result['payment_id'] . "</p>";
        echo "<p>Approval URL: <a href='" . $result['approval_url'] . "' target='_blank'>Click to test payment</a></p>";
    } else {
        echo "<p class='error'>❌ PayPal payment creation failed!</p>";
        echo "<p>Error: " . $result['error'] . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error during payment creation test: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Test 4: Common Issues and Solutions
echo "<div class='test-section'>";
echo "<h2>Test 4: Common Issues and Solutions</h2>";

echo "<h3>If authentication fails:</h3>";
echo "<ul>";
echo "<li>Verify your Client ID and Client Secret are correct</li>";
echo "<li>Make sure you're using the right environment (sandbox vs live)</li>";
echo "<li>Check that your PayPal app is properly configured</li>";
echo "<li>Ensure cURL is enabled on your server</li>";
echo "</ul>";

echo "<h3>If payment creation fails:</h3>";
echo "<ul>";
echo "<li>Check that your return and cancel URLs are valid HTTPS URLs</li>";
echo "<li>Verify the amount format (should be a number with 2 decimal places)</li>";
echo "<li>Make sure the currency is supported (USD, EUR, GBP, etc.)</li>";
echo "<li>Check that all required fields are provided</li>";
echo "</ul>";

echo "<h3>PayPal Developer Resources:</h3>";
echo "<ul>";
echo "<li><a href='https://developer.paypal.com/' target='_blank'>PayPal Developer Portal</a></li>";
echo "<li><a href='https://developer.paypal.com/docs/api/payments/v1/' target='_blank'>PayPal Payments API Documentation</a></li>";
echo "<li><a href='https://developer.paypal.com/developer/applications/' target='_blank'>Manage Your Apps</a></li>";
echo "</ul>";

echo "</div>";

// Test 5: Server Environment Check
echo "<div class='test-section'>";
echo "<h2>Test 5: Server Environment Check</h2>";

echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>cURL Enabled:</strong> " . (extension_loaded('curl') ? '✅ Yes' : '❌ No') . "</p>";
echo "<p><strong>OpenSSL Enabled:</strong> " . (extension_loaded('openssl') ? '✅ Yes' : '❌ No') . "</p>";
echo "<p><strong>JSON Enabled:</strong> " . (extension_loaded('json') ? '✅ Yes' : '❌ No') . "</p>";

if (!extension_loaded('curl')) {
    echo "<p class='error'>❌ cURL extension is required for PayPal integration!</p>";
}

if (!extension_loaded('openssl')) {
    echo "<p class='error'>❌ OpenSSL extension is required for secure connections!</p>";
}

echo "</div>";

// Test 6: Error Log Check
echo "<div class='test-section'>";
echo "<h2>Test 6: Recent Error Logs</h2>";

echo "<p class='info'>Check your PHP error logs for PayPal-related errors:</p>";
echo "<p>Common log locations:</p>";
echo "<ul>";
echo "<li>/var/log/apache2/error.log</li>";
echo "<li>/var/log/nginx/error.log</li>";
echo "<li>C:\\xampp\\apache\\logs\\error.log (XAMPP on Windows)</li>";
echo "</ul>";

echo "<p>Look for entries containing 'PayPal' to see detailed error messages.</p>";

echo "</div>";

echo "<p><strong>Test completed at: " . date('Y-m-d H:i:s') . "</strong></p>";
?>
