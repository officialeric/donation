# 🚀 Real Payment Gateway Integration Setup Guide

## Overview
This guide will walk you through setting up real payment gateways for TumainiFuraha to process actual transactions with Stripe, PayPal, and M-Pesa.

## 🔧 **Prerequisites**

### **Server Requirements:**
- PHP 7.4+ with cURL extension
- MySQL 5.7+ or MariaDB 10.2+
- HTTPS/SSL certificate (required for production)
- Composer (for PHP package management)

### **Required Accounts:**
- Stripe Developer Account
- PayPal Developer Account  
- M-Pesa Developer Account (for Kenya)

## 📋 **Step-by-Step Setup**

### **Step 1: Install Required Dependencies**

#### **Install Composer (if not already installed):**
```bash
# Download and install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

#### **Install Stripe PHP Library:**
```bash
# Navigate to your project directory
cd /path/to/your/project

# Install Stripe PHP library
composer require stripe/stripe-php
```

#### **Update your PHP files to include Composer autoloader:**
Add this line to the top of your payment processing files:
```php
require_once 'vendor/autoload.php';
```

### **Step 2: Stripe Setup**

#### **1. Create Stripe Account:**
- Go to https://stripe.com
- Sign up for a new account
- Complete business verification

#### **2. Get API Keys:**
- Login to Stripe Dashboard
- Go to Developers → API keys
- Copy your **Publishable key** and **Secret key**
- For webhooks: Go to Developers → Webhooks → Add endpoint

#### **3. Update Configuration:**
Edit `dist/includes/payment_config.php`:
```php
'stripe' => [
    'name' => 'Stripe',
    'test_mode' => false, // Set to false for production
    'public_key' => 'pk_live_YOUR_ACTUAL_PUBLISHABLE_KEY',
    'secret_key' => 'sk_live_YOUR_ACTUAL_SECRET_KEY',
    'webhook_secret' => 'whsec_YOUR_WEBHOOK_SECRET',
    'supported_methods' => ['credit_card'],
    'currencies' => ['USD', 'EUR', 'GBP', 'KES']
],
```

#### **4. Set up Webhooks:**
- Webhook URL: `https://yourdomain.com/stripe-webhook.php`
- Events to listen for:
  - `payment_intent.succeeded`
  - `payment_intent.payment_failed`

### **Step 3: PayPal Setup**

#### **1. Create PayPal Developer Account:**
- Go to https://developer.paypal.com
- Sign up or login with your PayPal account
- Create a new app

#### **2. Get API Credentials:**
- Go to My Apps & Credentials
- Create new app or select existing
- Copy **Client ID** and **Client Secret**

#### **3. Update Configuration:**
Edit `dist/includes/payment_config.php`:
```php
'paypal' => [
    'name' => 'PayPal',
    'test_mode' => false, // Set to false for production
    'client_id' => 'YOUR_ACTUAL_PAYPAL_CLIENT_ID',
    'client_secret' => 'YOUR_ACTUAL_PAYPAL_CLIENT_SECRET',
    'supported_methods' => ['paypal'],
    'currencies' => ['USD', 'EUR', 'GBP']
],
```

#### **4. Create PayPal Return Handler:**
Create `paypal-return.php` in your root directory:
```php
<?php
session_start();
require_once 'dist/includes/connection.php';
require_once 'dist/includes/paypal_integration.php';

$payment_id = $_GET['paymentId'] ?? '';
$payer_id = $_GET['PayerID'] ?? '';
$transaction_id = $_GET['transaction_id'] ?? '';

if ($payment_id && $payer_id) {
    $paypal = new PayPalIntegration();
    $result = $paypal->executePayment($payment_id, $payer_id);
    
    if ($result['success']) {
        // Update database
        $stmt = $db->prepare("UPDATE donations SET payment_status = 'completed', gateway_transaction_id = ? WHERE transaction_id = ?");
        $stmt->bind_param('ss', $result['transaction_id'], $transaction_id);
        $stmt->execute();
        
        // Send confirmation email
        require_once 'dist/includes/email_notifications.php';
        sendDonationConfirmationEmail($transaction_id);
        
        // Redirect to success page
        header('Location: payment-confirmation.php?transaction_id=' . urlencode($transaction_id));
    } else {
        header('Location: make-donation.php?error=' . urlencode('Payment failed'));
    }
} else {
    header('Location: index.php');
}
?>
```

### **Step 4: M-Pesa Setup (Kenya)**

#### **1. Create M-Pesa Developer Account:**
- Go to https://developer.safaricom.co.ke
- Sign up for developer account
- Create new app

#### **2. Get API Credentials:**
- Consumer Key
- Consumer Secret
- Business Short Code
- Passkey (for Lipa na M-Pesa Online)

#### **3. Update Configuration:**
Edit `dist/includes/payment_config.php`:
```php
'mpesa' => [
    'name' => 'M-Pesa',
    'test_mode' => false, // Set to false for production
    'consumer_key' => 'YOUR_ACTUAL_MPESA_CONSUMER_KEY',
    'consumer_secret' => 'YOUR_ACTUAL_MPESA_CONSUMER_SECRET',
    'business_short_code' => 'YOUR_BUSINESS_SHORT_CODE',
    'passkey' => 'YOUR_ACTUAL_MPESA_PASSKEY',
    'supported_methods' => ['mpesa'],
    'currencies' => ['KES']
],
```

#### **4. Create M-Pesa Callback Handler:**
Create `mpesa-callback.php` in your root directory:
```php
<?php
require_once 'dist/includes/connection.php';
require_once 'dist/includes/mpesa_integration.php';

// Get callback data
$callback_data = file_get_contents('php://input');

// Log callback for debugging
error_log('M-Pesa Callback: ' . $callback_data);

// Process callback
$mpesa = new MPesaIntegration();
$result = $mpesa->handleCallback($callback_data);

// Return success response
http_response_code(200);
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Success']);
?>
```

### **Step 5: Database Updates**

#### **Add required columns to donations table:**
```sql
ALTER TABLE donations 
ADD COLUMN gateway_payment_id VARCHAR(255) NULL,
ADD COLUMN checkout_request_id VARCHAR(255) NULL,
ADD COLUMN gateway_transaction_id VARCHAR(255) NULL;
```

### **Step 6: Security Configuration**

#### **1. Update Security Settings:**
Edit `dist/includes/payment_config.php`:
```php
const SECURITY_CONFIG = [
    'encryption_key' => 'your-actual-32-character-encryption-key-here', // Generate a secure key
    'encryption_method' => 'AES-256-GCM',
    'max_transaction_amount' => 5000.00,
    'max_daily_amount' => 10000.00,
    'session_timeout' => 900, // 15 minutes
    'max_failed_attempts' => 3,
    'require_https' => true, // MUST be true for production
    'pci_compliance_mode' => true
];
```

#### **2. Generate Encryption Key:**
```php
// Run this once to generate a secure encryption key
echo bin2hex(random_bytes(16)); // Generates 32-character key
```

### **Step 7: Email Configuration**

#### **Update Email Settings:**
Edit `dist/includes/email_notifications.php`:
```php
private $from_email = 'noreply@yourdomain.com';
private $from_name = 'TumainiFuraha';
private $smtp_host = 'your-smtp-server.com';
private $smtp_port = 587;
private $smtp_username = 'your-smtp-username';
private $smtp_password = 'your-smtp-password';
```

### **Step 8: Testing**

#### **1. Test in Sandbox Mode:**
- Keep `test_mode => true` initially
- Use test card numbers and credentials
- Verify all payment flows work

#### **2. Test Card Numbers (Stripe):**
- Visa: 4242424242424242
- MasterCard: 5555555555554444
- American Express: 378282246310005

#### **3. Test PayPal:**
- Use PayPal sandbox accounts
- Test complete payment flow

#### **4. Test M-Pesa:**
- Use M-Pesa sandbox environment
- Test STK push functionality

### **Step 9: Go Live**

#### **1. Switch to Production:**
```php
// Update all gateways
'test_mode' => false,
```

#### **2. Update URLs:**
- Ensure all callback URLs use HTTPS
- Update webhook endpoints
- Test with small amounts first

#### **3. Monitor:**
- Check payment gateway dashboards
- Monitor error logs
- Test email notifications

## 🔒 **Security Checklist**

### **Before Going Live:**
- [ ] HTTPS enabled and working
- [ ] All API keys are production keys
- [ ] Webhook endpoints secured
- [ ] Database properly secured
- [ ] Error logging enabled
- [ ] Email notifications working
- [ ] Test all payment methods
- [ ] Verify refund processes
- [ ] Check compliance requirements

## 📞 **Support Resources**

### **Documentation:**
- **Stripe:** https://stripe.com/docs
- **PayPal:** https://developer.paypal.com/docs
- **M-Pesa:** https://developer.safaricom.co.ke/docs

### **Testing Tools:**
- Stripe CLI for webhook testing
- PayPal sandbox for testing
- M-Pesa simulator for testing

## 🚨 **Important Notes**

1. **Never commit API keys to version control**
2. **Always test in sandbox first**
3. **Monitor transactions closely after going live**
4. **Keep payment gateway libraries updated**
5. **Implement proper error handling and logging**
6. **Ensure PCI compliance for credit card processing**
7. **Regular security audits recommended**

## 🎯 **Next Steps**

1. **Set up accounts** with all payment providers
2. **Install dependencies** (Composer, Stripe library)
3. **Configure API credentials** in payment_config.php
4. **Test in sandbox mode** thoroughly
5. **Set up webhooks and callbacks**
6. **Configure email notifications**
7. **Test all payment flows**
8. **Switch to production** when ready
9. **Monitor and maintain** the system

Your TumainiFuraha platform will now be able to process real payments through multiple gateways! 🚀
