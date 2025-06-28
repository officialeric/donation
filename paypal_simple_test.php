<?php
/**
 * Simple PayPal Test - Minimal test to identify the exact issue
 */

require_once 'dist/includes/payment_config.php';

echo "<h1>Simple PayPal Test</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } pre { background: #f5f5f5; padding: 10px; }</style>";

$config = PaymentConfig::getGatewayConfig('paypal');
$base_url = $config['test_mode'] ? 'https://api.sandbox.paypal.com' : 'https://api.paypal.com';

echo "<h2>1. Get Access Token</h2>";

// Step 1: Get access token
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $base_url . '/v1/oauth2/token');
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, $config['client_id'] . ':' . $config['client_secret']);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Accept-Language: en_US'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $http_code</p>";
echo "<p><strong>Response:</strong></p>";
echo "<pre>$response</pre>";

if ($http_code !== 200) {
    echo "<p style='color: red;'>❌ Authentication failed!</p>";
    exit;
}

$auth_data = json_decode($response, true);
$access_token = $auth_data['access_token'];

echo "<p style='color: green;'>✅ Authentication successful!</p>";

echo "<h2>2. Create Payment - Version 1 (Minimal)</h2>";

// Step 2: Create minimal payment
$minimal_payload = [
    'intent' => 'sale',
    'payer' => [
        'payment_method' => 'paypal'
    ],
    'transactions' => [[
        'amount' => [
            'total' => '10.00',
            'currency' => 'USD'
        ],
        'description' => 'Test payment'
    ]],
    'redirect_urls' => [
        'return_url' => 'http://localhost/donation/success.php',
        'cancel_url' => 'http://localhost/donation/cancel.php'
    ]
];

echo "<p><strong>Payload:</strong></p>";
echo "<pre>" . json_encode($minimal_payload, JSON_PRETTY_PRINT) . "</pre>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $base_url . '/v1/payments/payment');
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($minimal_payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $access_token
]);

$payment_response = curl_exec($ch);
$payment_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $payment_http_code</p>";
echo "<p><strong>Response:</strong></p>";
echo "<pre>$payment_response</pre>";

if ($payment_http_code === 201) {
    echo "<p style='color: green;'>✅ Minimal payment creation successful!</p>";
} else {
    echo "<p style='color: red;'>❌ Minimal payment creation failed!</p>";
}

echo "<h2>3. Create Payment - Version 2 (With Items)</h2>";

// Step 3: Create payment with items (like our actual implementation)
$detailed_payload = [
    'intent' => 'sale',
    'payer' => [
        'payment_method' => 'paypal'
    ],
    'transactions' => [[
        'amount' => [
            'total' => '25.00',
            'currency' => 'USD'
        ],
        'description' => 'Donation to Test Orphanage',
        'custom' => 'TEST_TRANSACTION_123',
        'item_list' => [
            'items' => [[
                'name' => 'Donation to Test Orphanage',
                'description' => 'General donation',
                'quantity' => '1',
                'price' => '25.00',
                'currency' => 'USD'
            ]]
        ]
    ]],
    'redirect_urls' => [
        'return_url' => 'http://localhost/donation/paypal-return.php?transaction_id=TEST_123',
        'cancel_url' => 'http://localhost/donation/make-donation.php?error=cancelled'
    ]
];

echo "<p><strong>Payload:</strong></p>";
echo "<pre>" . json_encode($detailed_payload, JSON_PRETTY_PRINT) . "</pre>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $base_url . '/v1/payments/payment');
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($detailed_payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $access_token
]);

$payment_response2 = curl_exec($ch);
$payment_http_code2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $payment_http_code2</p>";
echo "<p><strong>Response:</strong></p>";
echo "<pre>$payment_response2</pre>";

if ($payment_http_code2 === 201) {
    echo "<p style='color: green;'>✅ Detailed payment creation successful!</p>";
    
    $payment_data = json_decode($payment_response2, true);
    if (isset($payment_data['links'])) {
        foreach ($payment_data['links'] as $link) {
            if ($link['rel'] === 'approval_url') {
                echo "<p><strong>Approval URL:</strong> <a href='" . $link['href'] . "' target='_blank'>Test Payment</a></p>";
                break;
            }
        }
    }
} else {
    echo "<p style='color: red;'>❌ Detailed payment creation failed!</p>";
    
    $error_data = json_decode($payment_response2, true);
    if ($error_data && isset($error_data['details'])) {
        echo "<p><strong>Error Details:</strong></p>";
        foreach ($error_data['details'] as $detail) {
            echo "<p>• " . $detail['issue'] . ": " . $detail['description'] . "</p>";
        }
    }
}

echo "<h2>4. Analysis</h2>";

if ($payment_http_code === 201 && $payment_http_code2 !== 201) {
    echo "<p>The minimal payment works but the detailed payment fails.</p>";
    echo "<p>This suggests an issue with:</p>";
    echo "<ul>";
    echo "<li>Item list structure</li>";
    echo "<li>Amount/price mismatch</li>";
    echo "<li>Description length</li>";
    echo "<li>Custom field format</li>";
    echo "</ul>";
} elseif ($payment_http_code !== 201 && $payment_http_code2 !== 201) {
    echo "<p>Both payments fail - this suggests:</p>";
    echo "<ul>";
    echo "<li>URL format issues</li>";
    echo "<li>Authentication problems</li>";
    echo "<li>Account configuration issues</li>";
    echo "</ul>";
} else {
    echo "<p>Both payments work - the issue might be in the integration code.</p>";
}

echo "<p><strong>Test completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
