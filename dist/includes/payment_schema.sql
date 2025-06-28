-- Enhanced Payment System Database Schema for TumainiFuraha
-- This file contains all the database tables needed for secure payment processing

-- 1. Payment Methods Table
CREATE TABLE IF NOT EXISTS payment_methods (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    processing_fee_percentage DECIMAL(5,2) DEFAULT 0.00,
    processing_fee_fixed DECIMAL(10,2) DEFAULT 0.00,
    min_amount DECIMAL(10,2) DEFAULT 1.00,
    max_amount DECIMAL(10,2) DEFAULT 999999.99,
    supported_currencies JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default payment methods
INSERT INTO payment_methods (name, code, description, supported_currencies) VALUES
('Credit Card', 'credit_card', 'Visa, MasterCard, American Express', '["USD", "KES", "TZS", "UGX"]'),
('PayPal', 'paypal', 'PayPal online payment system', '["USD", "EUR", "GBP"]'),
('Bank Transfer', 'bank_transfer', 'Direct bank transfer', '["USD", "KES", "TZS", "UGX"]'),
('M-Pesa', 'mpesa', 'Mobile money payment (Kenya)', '["KES"]'),
('Halopesa', 'halopesa', 'Mobile money payment (Tanzania)', '["TZS"]'),
('Airtel Money', 'airtel_money', 'Mobile money payment (Multi-country)', '["KES", "TZS", "UGX"]');

-- 2. Enhanced Transactions Table
CREATE TABLE IF NOT EXISTS transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_id VARCHAR(100) NOT NULL UNIQUE,
    donor_id INT NOT NULL,
    orphanage_id INT NOT NULL,
    campaign_id INT NULL,
    payment_method_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    processing_fee DECIMAL(10,2) DEFAULT 0.00,
    net_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_gateway VARCHAR(50),
    gateway_transaction_id VARCHAR(255),
    gateway_response JSON,
    donor_message TEXT,
    is_anonymous BOOLEAN DEFAULT FALSE,
    payment_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (donor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (orphanage_id) REFERENCES orphanages(id) ON DELETE CASCADE,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE SET NULL,
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id),
    
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_donor_id (donor_id),
    INDEX idx_orphanage_id (orphanage_id),
    INDEX idx_status (status),
    INDEX idx_payment_date (payment_date)
);

-- 3. Payment Details Table (for storing encrypted payment information)
CREATE TABLE IF NOT EXISTS payment_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_id VARCHAR(100) NOT NULL,
    payment_method_code VARCHAR(20) NOT NULL,
    
    -- Credit Card Details (encrypted)
    card_holder_name_encrypted TEXT,
    card_last_four VARCHAR(4),
    card_type VARCHAR(20),
    card_expiry_month INT,
    card_expiry_year INT,
    
    -- PayPal Details
    paypal_email_encrypted TEXT,
    paypal_payer_id VARCHAR(100),
    
    -- Bank Transfer Details
    bank_name VARCHAR(100),
    account_holder_name_encrypted TEXT,
    account_number_encrypted TEXT,
    routing_number_encrypted TEXT,
    swift_code VARCHAR(20),
    
    -- Mobile Money Details
    mobile_number_encrypted TEXT,
    mobile_network VARCHAR(50),
    
    -- Security and Audit
    encryption_key_id VARCHAR(50),
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id) ON DELETE CASCADE,
    INDEX idx_transaction_id (transaction_id)
);

-- 4. Transaction Logs Table (for audit trail)
CREATE TABLE IF NOT EXISTS transaction_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_id VARCHAR(100) NOT NULL,
    status_from VARCHAR(20),
    status_to VARCHAR(20) NOT NULL,
    message TEXT,
    error_code VARCHAR(50),
    gateway_response JSON,
    created_by INT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_created_at (created_at)
);

-- 5. Payment Notifications Table (for email/SMS confirmations)
CREATE TABLE IF NOT EXISTS payment_notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_id VARCHAR(100) NOT NULL,
    notification_type ENUM('email', 'sms', 'push') NOT NULL,
    recipient VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    message TEXT,
    status ENUM('pending', 'sent', 'failed', 'bounced') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    error_message TEXT,
    attempts INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id) ON DELETE CASCADE,
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_status (status)
);

-- 6. Supported Banks Table (for bank transfer options)
CREATE TABLE IF NOT EXISTS supported_banks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bank_name VARCHAR(100) NOT NULL,
    bank_code VARCHAR(20),
    country_code VARCHAR(3) NOT NULL,
    swift_code VARCHAR(20),
    routing_number_format VARCHAR(50),
    account_number_format VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    logo_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_country_code (country_code),
    INDEX idx_is_active (is_active)
);

-- Insert major East African banks
INSERT INTO supported_banks (bank_name, bank_code, country_code, swift_code) VALUES
-- Kenya
('Kenya Commercial Bank (KCB)', 'KCB', 'KEN', 'KCBLKENX'),
('Equity Bank Kenya', 'EQUITY', 'KEN', 'EQBLKENA'),
('Cooperative Bank of Kenya', 'COOP', 'KEN', 'KCOOKENA'),
('Standard Chartered Kenya', 'SCB', 'KEN', 'SCBLKENX'),
('Barclays Bank Kenya', 'BARCLAYS', 'KEN', 'BARCKENX'),

-- Tanzania
('CRDB Bank Tanzania', 'CRDB', 'TZA', 'CORUTZTZ'),
('National Microfinance Bank (NMB)', 'NMB', 'TZA', 'NMIBTZTZ'),
('Exim Bank Tanzania', 'EXIM', 'TZA', 'EXTNTZTZ'),
('Standard Chartered Tanzania', 'SCB_TZ', 'TZA', 'SCBLTZTZ'),

-- Uganda
('Stanbic Bank Uganda', 'STANBIC', 'UGA', 'SBICUGKX'),
('Centenary Bank Uganda', 'CENTENARY', 'UGA', 'CENTUUKX'),
('Bank of Africa Uganda', 'BOA', 'UGA', 'AFRIUGKX'),
('Standard Chartered Uganda', 'SCB_UG', 'UGA', 'SCBLUGKX');

-- 7. Currency Exchange Rates Table (for multi-currency support)
CREATE TABLE IF NOT EXISTS currency_rates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    from_currency VARCHAR(3) NOT NULL,
    to_currency VARCHAR(3) NOT NULL,
    rate DECIMAL(10,6) NOT NULL,
    source VARCHAR(50) DEFAULT 'manual',
    valid_from TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    valid_until TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_currency_pair (from_currency, to_currency, valid_from),
    INDEX idx_currencies (from_currency, to_currency),
    INDEX idx_valid_period (valid_from, valid_until)
);

-- Insert basic exchange rates (these should be updated regularly)
INSERT INTO currency_rates (from_currency, to_currency, rate) VALUES
('USD', 'KES', 150.00),
('USD', 'TZS', 2500.00),
('USD', 'UGX', 3700.00),
('KES', 'USD', 0.0067),
('TZS', 'USD', 0.0004),
('UGX', 'USD', 0.00027);

-- 8. Payment Security Settings Table
CREATE TABLE IF NOT EXISTS payment_security_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description TEXT,
    is_encrypted BOOLEAN DEFAULT FALSE,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default security settings
INSERT INTO payment_security_settings (setting_key, setting_value, description) VALUES
('max_daily_amount', '10000.00', 'Maximum daily donation amount per user'),
('max_transaction_amount', '5000.00', 'Maximum single transaction amount'),
('require_3d_secure', 'true', 'Require 3D Secure for credit card payments'),
('payment_timeout_minutes', '15', 'Payment session timeout in minutes'),
('max_failed_attempts', '3', 'Maximum failed payment attempts before blocking'),
('encryption_algorithm', 'AES-256-GCM', 'Encryption algorithm for sensitive data');
