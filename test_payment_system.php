<?php
/**
 * Payment System Testing Script for TumainiFuraha
 * Tests all payment methods and validation functions
 */

session_start();
require_once 'dist/includes/connection.php';
require_once 'dist/includes/payment_config.php';
require_once 'dist/includes/security_validator.php';

// Set up test environment
$_SESSION['user_id'] = 1; // Test user ID

echo "<h1>TumainiFuraha Payment System Test Suite</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .pass { color: green; font-weight: bold; }
    .fail { color: red; font-weight: bold; }
    .info { color: blue; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 3px; }
</style>";

// Test 1: Payment Configuration
echo "<div class='test-section'>";
echo "<h2>Test 1: Payment Configuration</h2>";

$test_amount = 100.50;
$formatted_amount = PaymentConfig::formatAmount($test_amount);
echo "<p>Format Amount Test: $test_amount → $formatted_amount " . 
     ($formatted_amount === '$100.50' ? "<span class='pass'>PASS</span>" : "<span class='fail'>FAIL</span>") . "</p>";

$transaction_id = PaymentConfig::generateTransactionId();
echo "<p>Transaction ID Generation: $transaction_id " . 
     (preg_match('/^TF_\d{8}_[A-F0-9]{16}$/', $transaction_id) ? "<span class='pass'>PASS</span>" : "<span class='fail'>FAIL</span>") . "</p>";

echo "</div>";

// Test 2: Credit Card Validation
echo "<div class='test-section'>";
echo "<h2>Test 2: Credit Card Validation</h2>";

$test_cards = [
    ['4532015112830366', 'visa', true],
    ['5555555555554444', 'mastercard', true],
    ['378282246310005', 'amex', true],
    ['1234567890123456', 'unknown', false],
    ['4532015112830367', 'visa', false] // Invalid Luhn
];

foreach ($test_cards as $test) {
    $card_number = $test[0];
    $expected_type = $test[1];
    $should_be_valid = $test[2];
    
    $detected_type = PaymentConfig::detectCardType($card_number);
    $is_valid = PaymentConfig::validateCardNumber($card_number);
    
    $type_test = ($detected_type === $expected_type) ? "<span class='pass'>PASS</span>" : "<span class='fail'>FAIL</span>";
    $valid_test = ($is_valid === $should_be_valid) ? "<span class='pass'>PASS</span>" : "<span class='fail'>FAIL</span>";
    
    echo "<p>Card: $card_number → Type: $detected_type $type_test, Valid: " . ($is_valid ? 'Yes' : 'No') . " $valid_test</p>";
}

// Test CVV validation
$cvv_tests = [
    ['123', 'visa', true],
    ['1234', 'amex', true],
    ['12', 'visa', false],
    ['12345', 'visa', false]
];

foreach ($cvv_tests as $test) {
    $cvv = $test[0];
    $card_type = $test[1];
    $should_be_valid = $test[2];
    
    $is_valid = PaymentConfig::validateCVV($cvv, $card_type);
    $result = ($is_valid === $should_be_valid) ? "<span class='pass'>PASS</span>" : "<span class='fail'>FAIL</span>";
    
    echo "<p>CVV: $cvv ($card_type) → " . ($is_valid ? 'Valid' : 'Invalid') . " $result</p>";
}

// Test expiry date validation
$expiry_tests = [
    ['12', '25', true],
    ['01', '24', false], // Past date
    ['13', '25', false], // Invalid month
    ['06', '23', false]  // Past date
];

foreach ($expiry_tests as $test) {
    $month = $test[0];
    $year = $test[1];
    $should_be_valid = $test[2];
    
    $is_valid = PaymentConfig::validateExpiryDate($month, $year);
    $result = ($is_valid === $should_be_valid) ? "<span class='pass'>PASS</span>" : "<span class='fail'>FAIL</span>";
    
    echo "<p>Expiry: $month/$year → " . ($is_valid ? 'Valid' : 'Invalid') . " $result</p>";
}

echo "</div>";

// Test 3: Security Validation
echo "<div class='test-section'>";
echo "<h2>Test 3: Security Validation</h2>";

// Test amount validation
$amount_tests = [
    [50, []],
    [0, ['Invalid amount']],
    [-10, ['Invalid amount']],
    [6000, ['Amount exceeds maximum transaction limit of $5,000']]
];

foreach ($amount_tests as $test) {
    $amount = $test[0];
    $expected_errors = $test[1];
    
    $errors = SecurityValidator::validatePaymentAmount($amount);
    $matches = (count($errors) === count($expected_errors));
    
    if ($matches && !empty($expected_errors)) {
        foreach ($expected_errors as $expected_error) {
            if (!in_array($expected_error, $errors)) {
                $matches = false;
                break;
            }
        }
    }
    
    $result = $matches ? "<span class='pass'>PASS</span>" : "<span class='fail'>FAIL</span>";
    echo "<p>Amount: $amount → Errors: " . count($errors) . " $result</p>";
    if (!empty($errors)) {
        echo "<pre>" . implode("\n", $errors) . "</pre>";
    }
}

echo "</div>";

// Test 4: Payment Method Validation
echo "<div class='test-section'>";
echo "<h2>Test 4: Payment Method Validation</h2>";

// Test credit card data validation
$credit_card_data = [
    'card_number' => '4532015112830366',
    'card_holder_name' => 'John Doe',
    'card_expiry' => '12/25',
    'card_cvv' => '123'
];

$cc_errors = SecurityValidator::validateCreditCardData($credit_card_data);
$cc_result = empty($cc_errors) ? "<span class='pass'>PASS</span>" : "<span class='fail'>FAIL</span>";
echo "<p>Valid Credit Card Data → $cc_result</p>";
if (!empty($cc_errors)) {
    echo "<pre>" . implode("\n", $cc_errors) . "</pre>";
}

// Test invalid credit card data
$invalid_cc_data = [
    'card_number' => '1234567890123456',
    'card_holder_name' => '',
    'card_expiry' => '13/20',
    'card_cvv' => '12'
];

$invalid_cc_errors = SecurityValidator::validateCreditCardData($invalid_cc_data);
$invalid_cc_result = (!empty($invalid_cc_errors)) ? "<span class='pass'>PASS</span>" : "<span class='fail'>FAIL</span>";
echo "<p>Invalid Credit Card Data (should have errors) → $invalid_cc_result</p>";
if (!empty($invalid_cc_errors)) {
    echo "<pre>" . implode("\n", $invalid_cc_errors) . "</pre>";
}

// Test PayPal validation
$paypal_data = ['paypal_email' => 'test@example.com'];
$paypal_errors = SecurityValidator::validatePayPalData($paypal_data);
$paypal_result = empty($paypal_errors) ? "<span class='pass'>PASS</span>" : "<span class='fail'>FAIL</span>";
echo "<p>Valid PayPal Data → $paypal_result</p>";

$invalid_paypal_data = ['paypal_email' => 'invalid-email'];
$invalid_paypal_errors = SecurityValidator::validatePayPalData($invalid_paypal_data);
$invalid_paypal_result = (!empty($invalid_paypal_errors)) ? "<span class='pass'>PASS</span>" : "<span class='fail'>FAIL</span>";
echo "<p>Invalid PayPal Data (should have errors) → $invalid_paypal_result</p>";

// Test M-Pesa validation
$mpesa_data = ['mpesa_phone' => '0712345678'];
$mpesa_errors = SecurityValidator::validateMPesaData($mpesa_data);
$mpesa_result = empty($mpesa_errors) ? "<span class='pass'>PASS</span>" : "<span class='fail'>FAIL</span>";
echo "<p>Valid M-Pesa Data → $mpesa_result</p>";

$invalid_mpesa_data = ['mpesa_phone' => '123456'];
$invalid_mpesa_errors = SecurityValidator::validateMPesaData($invalid_mpesa_data);
$invalid_mpesa_result = (!empty($invalid_mpesa_errors)) ? "<span class='pass'>PASS</span>" : "<span class='fail'>FAIL</span>";
echo "<p>Invalid M-Pesa Data (should have errors) → $invalid_mpesa_result</p>";

echo "</div>";

// Test 5: Database Connection and Structure
echo "<div class='test-section'>";
echo "<h2>Test 5: Database Connection</h2>";

try {
    // Test database connection
    $db_test = $db->query("SELECT 1");
    echo "<p>Database Connection → <span class='pass'>PASS</span></p>";
    
    // Test required tables exist
    $required_tables = ['donations', 'orphanages', 'campaigns', 'users'];
    foreach ($required_tables as $table) {
        $result = $db->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "<p>Table '$table' exists → <span class='pass'>PASS</span></p>";
        } else {
            echo "<p>Table '$table' missing → <span class='fail'>FAIL</span></p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>Database Connection → <span class='fail'>FAIL</span></p>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Test 6: Security Features
echo "<div class='test-section'>";
echo "<h2>Test 6: Security Features</h2>";

// Test CSRF token generation
$csrf_token = SecurityValidator::generateCSRFToken();
echo "<p>CSRF Token Generated: " . substr($csrf_token, 0, 16) . "... " . 
     (strlen($csrf_token) === 64 ? "<span class='pass'>PASS</span>" : "<span class='fail'>FAIL</span>") . "</p>";

// Test input sanitization
$dirty_input = "<script>alert('xss')</script>";
$clean_input = SecurityValidator::sanitizeInput($dirty_input);
$sanitize_result = ($clean_input !== $dirty_input && !strpos($clean_input, '<script>')) ? 
    "<span class='pass'>PASS</span>" : "<span class='fail'>FAIL</span>";
echo "<p>Input Sanitization → $sanitize_result</p>";
echo "<p class='info'>Original: $dirty_input</p>";
echo "<p class='info'>Sanitized: $clean_input</p>";

echo "</div>";

// Test Summary
echo "<div class='test-section'>";
echo "<h2>Test Summary</h2>";
echo "<p class='info'>All core payment system components have been tested.</p>";
echo "<p class='info'>Review the results above to ensure all tests pass.</p>";
echo "<p class='info'>For production deployment:</p>";
echo "<ul>";
echo "<li>Configure real payment gateway credentials</li>";
echo "<li>Enable HTTPS enforcement</li>";
echo "<li>Set up proper email SMTP settings</li>";
echo "<li>Configure database with enhanced tables</li>";
echo "<li>Enable rate limiting and security logging</li>";
echo "</ul>";
echo "</div>";

echo "<p><strong>Test completed at: " . date('Y-m-d H:i:s') . "</strong></p>";
?>
