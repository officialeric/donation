<?php
/**
 * Payment Configuration and Security Utilities for TumainiFuraha
 * Handles payment processing configuration, encryption, and security
 */

class PaymentConfig {
    
    // Payment Gateway Configurations
    const PAYMENT_GATEWAYS = [
        'stripe' => [
            'name' => 'Stripe',
            'test_mode' => true,
            'public_key' => 'pk_test_...',  // Replace with actual Stripe keys
            'secret_key' => 'sk_test_...',
            'webhook_secret' => 'whsec_...',
            'supported_methods' => ['credit_card'],
            'currencies' => ['USD', 'EUR', 'GBP']
        ],
        'paypal' => [
            'name' => 'PayPal',
            'test_mode' => true,
            'client_id' => 'your_paypal_client_id',
            'client_secret' => 'your_paypal_client_secret',
            'supported_methods' => ['paypal'],
            'currencies' => ['USD', 'EUR', 'GBP']
        ],
        'mpesa' => [
            'name' => 'M-Pesa',
            'test_mode' => true,
            'consumer_key' => 'your_mpesa_consumer_key',
            'consumer_secret' => 'your_mpesa_consumer_secret',
            'business_short_code' => '174379',
            'passkey' => 'your_mpesa_passkey',
            'supported_methods' => ['mpesa'],
            'currencies' => ['KES']
        ]
    ];
    
    // Security Configuration
    const SECURITY_CONFIG = [
        'encryption_key' => 'your-32-character-encryption-key-here',
        'encryption_method' => 'AES-256-GCM',
        'max_transaction_amount' => 5000.00,
        'max_daily_amount' => 10000.00,
        'session_timeout' => 900, // 15 minutes
        'max_failed_attempts' => 3,
        'require_https' => true,
        'pci_compliance_mode' => true
    ];
    
    // Supported Currencies
    const SUPPORTED_CURRENCIES = [
        'USD' => ['symbol' => '$', 'name' => 'US Dollar', 'decimals' => 2],
        'KES' => ['symbol' => 'KSh', 'name' => 'Kenyan Shilling', 'decimals' => 2],
        'TZS' => ['symbol' => 'TSh', 'name' => 'Tanzanian Shilling', 'decimals' => 2],
        'UGX' => ['symbol' => 'USh', 'name' => 'Ugandan Shilling', 'decimals' => 0],
        'EUR' => ['symbol' => '€', 'name' => 'Euro', 'decimals' => 2],
        'GBP' => ['symbol' => '£', 'name' => 'British Pound', 'decimals' => 2]
    ];
    
    /**
     * Get payment gateway configuration
     */
    public static function getGatewayConfig($gateway) {
        return self::PAYMENT_GATEWAYS[$gateway] ?? null;
    }
    
    /**
     * Check if HTTPS is required and enforce it
     */
    public static function enforceHTTPS() {
        if (self::SECURITY_CONFIG['require_https'] && !isset($_SERVER['HTTPS'])) {
            $redirectURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("Location: $redirectURL");
            exit();
        }
    }
    
    /**
     * Generate secure transaction ID
     */
    public static function generateTransactionId() {
        return 'TF_' . date('Ymd') . '_' . strtoupper(bin2hex(random_bytes(8)));
    }
    
    /**
     * Encrypt sensitive data
     */
    public static function encryptData($data) {
        $key = self::SECURITY_CONFIG['encryption_key'];
        $method = self::SECURITY_CONFIG['encryption_method'];
        
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, $method, $key, 0, $iv, $tag);
        
        return base64_encode($iv . $tag . $encrypted);
    }
    
    /**
     * Decrypt sensitive data
     */
    public static function decryptData($encryptedData) {
        $key = self::SECURITY_CONFIG['encryption_key'];
        $method = self::SECURITY_CONFIG['encryption_method'];
        
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $tag = substr($data, 16, 16);
        $encrypted = substr($data, 32);
        
        return openssl_decrypt($encrypted, $method, $key, 0, $iv, $tag);
    }
    
    /**
     * Validate transaction amount
     */
    public static function validateAmount($amount, $currency = 'USD') {
        $errors = [];
        
        if (!is_numeric($amount) || $amount <= 0) {
            $errors[] = 'Amount must be a positive number';
        }
        
        if ($amount > self::SECURITY_CONFIG['max_transaction_amount']) {
            $errors[] = 'Amount exceeds maximum transaction limit';
        }
        
        return $errors;
    }
    
    /**
     * Get currency symbol
     */
    public static function getCurrencySymbol($currency) {
        return self::SUPPORTED_CURRENCIES[$currency]['symbol'] ?? '$';
    }
    
    /**
     * Format amount with currency
     */
    public static function formatAmount($amount, $currency = 'USD') {
        $symbol = self::getCurrencySymbol($currency);
        $decimals = self::SUPPORTED_CURRENCIES[$currency]['decimals'] ?? 2;
        
        return $symbol . number_format($amount, $decimals);
    }
    
    /**
     * Sanitize card number (remove spaces, dashes)
     */
    public static function sanitizeCardNumber($cardNumber) {
        return preg_replace('/[^0-9]/', '', $cardNumber);
    }
    
    /**
     * Detect card type from card number
     */
    public static function detectCardType($cardNumber) {
        $cardNumber = self::sanitizeCardNumber($cardNumber);
        
        // Visa
        if (preg_match('/^4[0-9]{12}(?:[0-9]{3})?$/', $cardNumber)) {
            return 'visa';
        }
        
        // MasterCard
        if (preg_match('/^5[1-5][0-9]{14}$/', $cardNumber)) {
            return 'mastercard';
        }
        
        // American Express
        if (preg_match('/^3[47][0-9]{13}$/', $cardNumber)) {
            return 'amex';
        }
        
        // Discover
        if (preg_match('/^6(?:011|5[0-9]{2})[0-9]{12}$/', $cardNumber)) {
            return 'discover';
        }
        
        return 'unknown';
    }
    
    /**
     * Validate credit card number using Luhn algorithm
     */
    public static function validateCardNumber($cardNumber) {
        $cardNumber = self::sanitizeCardNumber($cardNumber);
        
        if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            return false;
        }
        
        $sum = 0;
        $alternate = false;
        
        for ($i = strlen($cardNumber) - 1; $i >= 0; $i--) {
            $n = intval($cardNumber[$i]);
            
            if ($alternate) {
                $n *= 2;
                if ($n > 9) {
                    $n = ($n % 10) + 1;
                }
            }
            
            $sum += $n;
            $alternate = !$alternate;
        }
        
        return ($sum % 10 == 0);
    }
    
    /**
     * Validate CVV
     */
    public static function validateCVV($cvv, $cardType = null) {
        if (!preg_match('/^[0-9]+$/', $cvv)) {
            return false;
        }
        
        $length = strlen($cvv);
        
        if ($cardType === 'amex') {
            return $length === 4;
        } else {
            return $length === 3;
        }
    }
    
    /**
     * Validate expiry date
     */
    public static function validateExpiryDate($month, $year) {
        if (!is_numeric($month) || !is_numeric($year)) {
            return false;
        }
        
        $month = intval($month);
        $year = intval($year);
        
        if ($month < 1 || $month > 12) {
            return false;
        }
        
        // Convert 2-digit year to 4-digit
        if ($year < 100) {
            $year += 2000;
        }
        
        $currentYear = date('Y');
        $currentMonth = date('n');
        
        if ($year < $currentYear || ($year == $currentYear && $month < $currentMonth)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Log payment activity
     */
    public static function logActivity($transactionId, $action, $details = null, $userId = null) {
        include_once 'connection.php';
        global $db;
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $stmt = $db->prepare("
            INSERT INTO transaction_logs 
            (transaction_id, status_to, message, created_by, ip_address) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $message = is_array($details) ? json_encode($details) : $details;
        $stmt->bind_param('sssis', $transactionId, $action, $message, $userId, $ip);
        $stmt->execute();
    }
    
    /**
     * Check rate limiting for failed attempts
     */
    public static function checkRateLimit($userId, $action = 'payment_attempt') {
        // For now, return true to allow payments
        // In production, implement proper rate limiting with database
        return true;
    }
}

// Initialize security checks
PaymentConfig::enforceHTTPS();
?>
