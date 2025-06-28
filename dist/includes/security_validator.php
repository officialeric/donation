<?php
/**
 * Security Validation for TumainiFuraha Payment System
 * Implements additional security checks and validations
 */

class SecurityValidator {
    
    /**
     * Validate HTTPS requirement for payment pages
     */
    public static function enforceHTTPS() {
        // Skip HTTPS enforcement for localhost/development
        if (isset($_SERVER['HTTP_HOST']) &&
            (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
             strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false)) {
            return; // Allow HTTP for development
        }

        if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
            // In development, we'll just log this
            error_log('HTTPS required for payment processing');
            // In production, uncomment the line below:
            // header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            // exit;
        }
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            return false;
        }
        return true;
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitizeInput($data, $type = 'string') {
        switch ($type) {
            case 'email':
                return filter_var($data, FILTER_SANITIZE_EMAIL);
            case 'int':
                return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'url':
                return filter_var($data, FILTER_SANITIZE_URL);
            default:
                return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Validate payment amount against limits
     */
    public static function validatePaymentAmount($amount, $user_id = null) {
        $errors = [];
        
        // Basic amount validation
        if (!is_numeric($amount) || $amount <= 0) {
            $errors[] = 'Invalid amount';
        }
        
        // Maximum transaction limit
        if ($amount > 5000) {
            $errors[] = 'Amount exceeds maximum transaction limit of $5,000';
        }
        
        // Minimum amount
        if ($amount < 1) {
            $errors[] = 'Minimum donation amount is $1';
        }
        
        // Daily limit check (if user is provided)
        if ($user_id && !self::checkDailyLimit($user_id, $amount)) {
            $errors[] = 'Daily donation limit exceeded';
        }
        
        return $errors;
    }
    
    /**
     * Check daily donation limit for user
     */
    private static function checkDailyLimit($user_id, $amount) {
        global $db;
        
        $daily_limit = 10000; // $10,000 daily limit
        $today = date('Y-m-d');
        
        $stmt = $db->prepare("
            SELECT SUM(amount) as daily_total 
            FROM donations 
            WHERE user_id = ? 
            AND DATE(created_at) = ? 
            AND payment_status = 'completed'
        ");
        
        $stmt->bind_param('is', $user_id, $today);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $current_total = $row['daily_total'] ?? 0;
        
        return ($current_total + $amount) <= $daily_limit;
    }
    
    /**
     * Log security events
     */
    public static function logSecurityEvent($event_type, $details, $user_id = null) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event_type' => $event_type,
            'user_id' => $user_id,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => $details
        ];
        
        error_log('SECURITY_EVENT: ' . json_encode($log_entry));
    }
    
    /**
     * Validate credit card data
     */
    public static function validateCreditCardData($card_data) {
        $errors = [];
        
        // Validate card number
        $card_number = PaymentConfig::sanitizeCardNumber($card_data['card_number'] ?? '');
        if (!PaymentConfig::validateCardNumber($card_number)) {
            $errors[] = 'Invalid credit card number';
        }
        
        // Validate cardholder name
        $card_holder_name = trim($card_data['card_holder_name'] ?? '');
        if (empty($card_holder_name) || strlen($card_holder_name) < 2) {
            $errors[] = 'Cardholder name is required';
        }
        
        // Validate expiry date
        $card_expiry = $card_data['card_expiry'] ?? '';
        $expiry_parts = explode('/', $card_expiry);
        if (count($expiry_parts) !== 2 || !PaymentConfig::validateExpiryDate($expiry_parts[0], $expiry_parts[1])) {
            $errors[] = 'Invalid expiry date';
        }
        
        // Validate CVV
        $card_cvv = $card_data['card_cvv'] ?? '';
        $card_type = PaymentConfig::detectCardType($card_number);
        if (!PaymentConfig::validateCVV($card_cvv, $card_type)) {
            $errors[] = 'Invalid CVV';
        }
        
        return $errors;
    }
    
    /**
     * Validate PayPal data
     */
    public static function validatePayPalData($paypal_data) {
        $errors = [];
        
        $paypal_email = filter_var($paypal_data['paypal_email'] ?? '', FILTER_VALIDATE_EMAIL);
        if (!$paypal_email) {
            $errors[] = 'Invalid PayPal email address';
        }
        
        return $errors;
    }
    
    /**
     * Validate bank transfer data
     */
    public static function validateBankTransferData($bank_data) {
        $errors = [];
        
        $required_fields = ['bank_name', 'account_holder_name', 'account_number'];
        
        foreach ($required_fields as $field) {
            if (empty(trim($bank_data[$field] ?? ''))) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        
        // Validate account number format (basic check)
        $account_number = preg_replace('/[^0-9]/', '', $bank_data['account_number'] ?? '');
        if (strlen($account_number) < 8 || strlen($account_number) > 20) {
            $errors[] = 'Invalid account number format';
        }
        
        return $errors;
    }
    
    /**
     * Validate M-Pesa data
     */
    public static function validateMPesaData($mpesa_data) {
        $errors = [];
        
        $mpesa_phone = preg_replace('/[^0-9]/', '', $mpesa_data['mpesa_phone'] ?? '');
        
        if (strlen($mpesa_phone) !== 10) {
            $errors[] = 'M-Pesa phone number must be 10 digits';
        }
        
        if (!preg_match('/^07/', $mpesa_phone)) {
            $errors[] = 'M-Pesa phone number must start with 07';
        }
        
        return $errors;
    }
    
    /**
     * Check for suspicious activity
     */
    public static function checkSuspiciousActivity($user_id, $amount, $payment_method) {
        global $db;

        $suspicious_flags = [];

        // Skip fraud detection for localhost/development
        if (isset($_SERVER['HTTP_HOST']) &&
            (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
             strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false)) {
            return $suspicious_flags; // Return empty array for development
        }

        // Check for rapid successive donations (production only)
        $stmt = $db->prepare("
            SELECT COUNT(*) as recent_count
            FROM donations
            WHERE user_id = ?
            AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ");

        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['recent_count'] > 10) { // Increased threshold
            $suspicious_flags[] = 'Multiple rapid donations detected';
        }

        // Check for unusually large amounts
        if ($amount > 5000) { // Increased threshold
            $suspicious_flags[] = 'Large donation amount';
        }

        // Check for failed attempts
        $stmt = $db->prepare("
            SELECT COUNT(*) as failed_count
            FROM donations
            WHERE user_id = ?
            AND payment_status = 'failed'
            AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");

        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['failed_count'] > 5) { // Increased threshold
            $suspicious_flags[] = 'Multiple failed payment attempts';
        }

        if (!empty($suspicious_flags)) {
            self::logSecurityEvent('suspicious_activity', [
                'flags' => $suspicious_flags,
                'amount' => $amount,
                'payment_method' => $payment_method
            ], $user_id);
        }

        return $suspicious_flags;
    }
    
    /**
     * Rate limiting check
     */
    public static function checkRateLimit($user_id, $action = 'payment') {
        global $db;
        
        $time_window = 3600; // 1 hour
        $max_attempts = 10; // 10 attempts per hour
        
        $stmt = $db->prepare("
            SELECT COUNT(*) as attempts 
            FROM donations 
            WHERE user_id = ? 
            AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        
        $stmt->bind_param('ii', $user_id, $time_window);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['attempts'] >= $max_attempts) {
            self::logSecurityEvent('rate_limit_exceeded', [
                'attempts' => $row['attempts'],
                'time_window' => $time_window
            ], $user_id);
            
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate session security
     */
    public static function validateSession() {
        // Check if session is valid
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Check session timeout (30 minutes)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            session_destroy();
            return false;
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    /**
     * Generate secure random string
     */
    public static function generateSecureRandom($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
}

// Initialize security checks
SecurityValidator::enforceHTTPS();
?>
