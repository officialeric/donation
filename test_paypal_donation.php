<?php
/**
 * Test PayPal Donation Flow
 * This simulates the exact donation process to identify PayPal issues
 */

session_start();
require_once 'dist/includes/connection.php';
require_once 'dist/includes/payment_config.php';
require_once 'dist/includes/paypal_integration.php';

// Set up test session
$_SESSION['user_id'] = 1; // Test user

echo "<h1>PayPal Donation Flow Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
</style>";

// Test 1: Simulate donation data
echo "<div class='test-section'>";
echo "<h2>Test 1: Donation Data Preparation</h2>";

$test_donation_data = [
    'orphanage_id' => 1,
    'campaign_id' => null,
    'amount' => 25.00,
    'payment_method' => 'paypal',
    'donor_message' => 'Test donation message'
];

echo "<p><strong>Test Donation Data:</strong></p>";
echo "<pre>" . json_encode($test_donation_data, JSON_PRETTY_PRINT) . "</pre>";

// Generate transaction ID
$transaction_id = 'TF_' . date('Ymd') . '_' . strtoupper(bin2hex(random_bytes(8)));
echo "<p><strong>Generated Transaction ID:</strong> $transaction_id</p>";

echo "</div>";

// Test 2: Get orphanage details
echo "<div class='test-section'>";
echo "<h2>Test 2: Database Queries</h2>";

try {
    // Get orphanage details
    $stmt = $db->prepare("SELECT id, name FROM orphanages WHERE id = ? AND status = 'active'");
    $stmt->bind_param('i', $test_donation_data['orphanage_id']);
    $stmt->execute();
    $orphanage_result = $stmt->get_result();
    
    if ($orphanage_result->num_rows > 0) {
        $orphanage = $orphanage_result->fetch_assoc();
        echo "<p class='success'>✅ Orphanage found: " . $orphanage['name'] . "</p>";
    } else {
        echo "<p class='error'>❌ Orphanage not found</p>";
        exit;
    }
    
    // Get campaign details if specified
    $campaign_title = '';
    if ($test_donation_data['campaign_id']) {
        $stmt = $db->prepare("SELECT title FROM campaigns WHERE id = ?");
        $stmt->bind_param('i', $test_donation_data['campaign_id']);
        $stmt->execute();
        $campaign_result = $stmt->get_result();
        $campaign = $campaign_result->fetch_assoc();
        $campaign_title = $campaign['title'] ?? '';
        echo "<p class='info'>Campaign: $campaign_title</p>";
    } else {
        echo "<p class='info'>No campaign specified</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Database error: " . $e->getMessage() . "</p>";
    exit;
}

echo "</div>";

// Test 3: Prepare PayPal payment data
echo "<div class='test-section'>";
echo "<h2>Test 3: PayPal Payment Data Preparation</h2>";

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . '://' . $host;

// For localhost development, use http
if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
    $base_url = 'http://' . $host;
}

$payment_data = [
    'amount' => floatval($test_donation_data['amount']),
    'currency' => 'USD',
    'transaction_id' => $transaction_id,
    'orphanage_name' => $orphanage['name'],
    'campaign_title' => $campaign_title,
    'return_url' => $base_url . '/donation/paypal-return.php?transaction_id=' . urlencode($transaction_id),
    'cancel_url' => $base_url . '/donation/make-donation.php?orphanage_id=' . $test_donation_data['orphanage_id'] . '&error=' . urlencode('Payment cancelled')
];

echo "<p><strong>PayPal Payment Data:</strong></p>";
echo "<pre>" . json_encode($payment_data, JSON_PRETTY_PRINT) . "</pre>";

echo "</div>";

// Test 4: Create PayPal payment
echo "<div class='test-section'>";
echo "<h2>Test 4: PayPal Payment Creation</h2>";

try {
    $paypal = new PayPalIntegration();
    
    echo "<p class='info'>Creating PayPal payment...</p>";
    
    $result = $paypal->createPayment($payment_data);
    
    echo "<p><strong>PayPal Response:</strong></p>";
    echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
    
    if ($result['success']) {
        echo "<p class='success'>✅ PayPal payment created successfully!</p>";
        echo "<p><strong>Payment ID:</strong> " . $result['payment_id'] . "</p>";
        echo "<p><strong>Approval URL:</strong> <a href='" . $result['approval_url'] . "' target='_blank'>Click to test payment</a></p>";
        
        // Test database insertion
        echo "<h3>Database Insertion Test:</h3>";
        
        $stmt = $db->prepare("
            INSERT INTO donations 
            (user_id, orphanage_id, campaign_id, amount, payment_method, transaction_id, message, payment_status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        
        $stmt->bind_param('iiidsss', 
            $_SESSION['user_id'], 
            $test_donation_data['orphanage_id'], 
            $test_donation_data['campaign_id'], 
            $test_donation_data['amount'], 
            $test_donation_data['payment_method'], 
            $transaction_id, 
            $test_donation_data['donor_message']
        );
        
        if ($stmt->execute()) {
            echo "<p class='success'>✅ Donation record created in database</p>";
            
            // Store PayPal payment ID
            $stmt = $db->prepare("UPDATE donations SET gateway_payment_id = ? WHERE transaction_id = ?");
            $stmt->bind_param('ss', $result['payment_id'], $transaction_id);
            
            if ($stmt->execute()) {
                echo "<p class='success'>✅ PayPal payment ID stored</p>";
            } else {
                echo "<p class='error'>❌ Failed to store PayPal payment ID</p>";
            }
        } else {
            echo "<p class='error'>❌ Failed to create donation record</p>";
        }
        
    } else {
        echo "<p class='error'>❌ PayPal payment creation failed!</p>";
        echo "<p><strong>Error:</strong> " . $result['error'] . "</p>";
        
        // Additional debugging
        echo "<h3>Debugging Information:</h3>";
        echo "<p>Check the following:</p>";
        echo "<ul>";
        echo "<li>Are your PayPal credentials correct?</li>";
        echo "<li>Are the return/cancel URLs valid?</li>";
        echo "<li>Is the amount format correct?</li>";
        echo "<li>Are all required fields present?</li>";
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Exception during PayPal payment creation: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</div>";

// Test 5: URL Validation
echo "<div class='test-section'>";
echo "<h2>Test 5: URL Validation</h2>";

echo "<p><strong>Return URL:</strong> " . $payment_data['return_url'] . "</p>";
echo "<p><strong>Cancel URL:</strong> " . $payment_data['cancel_url'] . "</p>";

// Test if URLs are accessible
$return_url_valid = filter_var($payment_data['return_url'], FILTER_VALIDATE_URL);
$cancel_url_valid = filter_var($payment_data['cancel_url'], FILTER_VALIDATE_URL);

echo "<p>Return URL valid: " . ($return_url_valid ? "✅ Yes" : "❌ No") . "</p>";
echo "<p>Cancel URL valid: " . ($cancel_url_valid ? "✅ Yes" : "❌ No") . "</p>";

echo "</div>";

// Test 6: Manual PayPal API Test
echo "<div class='test-section'>";
echo "<h2>Test 6: Manual PayPal API Test</h2>";

echo "<p class='info'>Testing direct PayPal API call...</p>";

$config = PaymentConfig::getGatewayConfig('paypal');
$base_url = $config['test_mode'] ? 'https://api.sandbox.paypal.com' : 'https://api.paypal.com';

// Get access token
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

$auth_response = curl_exec($ch);
$auth_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($auth_http_code === 200) {
    $auth_data = json_decode($auth_response, true);
    $access_token = $auth_data['access_token'];
    
    echo "<p class='success'>✅ Authentication successful</p>";
    
    // Create simple payment
    $simple_payload = [
        'intent' => 'sale',
        'payer' => ['payment_method' => 'paypal'],
        'transactions' => [[
            'amount' => [
                'total' => '10.00',
                'currency' => 'USD'
            ],
            'description' => 'Test donation'
        ]],
        'redirect_urls' => [
            'return_url' => $base_url . '/donation/paypal-return.php',
            'cancel_url' => $base_url . '/donation/make-donation.php'
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $base_url . '/v1/payments/payment');
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($simple_payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ]);
    
    $payment_response = curl_exec($ch);
    $payment_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p><strong>Simple Payment Test - HTTP Code:</strong> $payment_http_code</p>";
    echo "<p><strong>Response:</strong></p>";
    echo "<pre>" . substr($payment_response, 0, 1000) . "</pre>";
    
    if ($payment_http_code === 201) {
        echo "<p class='success'>✅ Simple payment creation successful</p>";
    } else {
        echo "<p class='error'>❌ Simple payment creation failed</p>";
    }
    
} else {
    echo "<p class='error'>❌ Authentication failed</p>";
}

echo "</div>";

echo "<p><strong>Test completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
