<?php
/**
 * Database Fix for PayPal Integration
 * Run this once to add missing columns
 */

require_once 'dist/includes/connection.php';

echo "<h1>Database Fix for PayPal Integration</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; }</style>";

try {
    // Check if columns already exist
    $result = $db->query("SHOW COLUMNS FROM donations LIKE 'gateway_payment_id'");
    
    if ($result->num_rows == 0) {
        echo "<p>Adding gateway_payment_id column...</p>";
        $db->query("ALTER TABLE donations ADD COLUMN gateway_payment_id VARCHAR(255) NULL AFTER transaction_id");
        echo "<p style='color: green;'>✅ gateway_payment_id column added</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ gateway_payment_id column already exists</p>";
    }
    
    // Check checkout_request_id
    $result = $db->query("SHOW COLUMNS FROM donations LIKE 'checkout_request_id'");
    
    if ($result->num_rows == 0) {
        echo "<p>Adding checkout_request_id column...</p>";
        $db->query("ALTER TABLE donations ADD COLUMN checkout_request_id VARCHAR(255) NULL AFTER gateway_payment_id");
        echo "<p style='color: green;'>✅ checkout_request_id column added</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ checkout_request_id column already exists</p>";
    }
    
    // Check gateway_transaction_id
    $result = $db->query("SHOW COLUMNS FROM donations LIKE 'gateway_transaction_id'");
    
    if ($result->num_rows == 0) {
        echo "<p>Adding gateway_transaction_id column...</p>";
        $db->query("ALTER TABLE donations ADD COLUMN gateway_transaction_id VARCHAR(255) NULL AFTER checkout_request_id");
        echo "<p style='color: green;'>✅ gateway_transaction_id column added</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ gateway_transaction_id column already exists</p>";
    }
    
    // Check updated_at
    $result = $db->query("SHOW COLUMNS FROM donations LIKE 'updated_at'");
    
    if ($result->num_rows == 0) {
        echo "<p>Adding updated_at column...</p>";
        $db->query("ALTER TABLE donations ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
        echo "<p style='color: green;'>✅ updated_at column added</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ updated_at column already exists</p>";
    }
    
    // Show final table structure
    echo "<h2>Updated Table Structure:</h2>";
    $result = $db->query("DESCRIBE donations");
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<h2>✅ Database Fix Complete!</h2>";
    echo "<p>Your PayPal integration should now work perfectly.</p>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li>Test PayPal donation: <a href='make-donation.php?orphanage_id=1'>Make Test Donation</a></li>";
    echo "<li>Run PayPal test: <a href='test_paypal_donation.php'>Run PayPal Test</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Please run the SQL commands manually in phpMyAdmin.</p>";
}
?>
