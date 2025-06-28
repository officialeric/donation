-- Fix for PayPal Integration Database Schema
-- Add missing columns to donations table

-- Add gateway_payment_id column for storing PayPal payment IDs
ALTER TABLE donations 
ADD COLUMN gateway_payment_id VARCHAR(255) NULL AFTER transaction_id;

-- Add checkout_request_id column for M-Pesa integration
ALTER TABLE donations 
ADD COLUMN checkout_request_id VARCHAR(255) NULL AFTER gateway_payment_id;

-- Add gateway_transaction_id column for storing final transaction IDs from gateways
ALTER TABLE donations 
ADD COLUMN gateway_transaction_id VARCHAR(255) NULL AFTER checkout_request_id;

-- Add updated_at column for tracking record updates
ALTER TABLE donations 
ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Add index for better performance on gateway lookups
ALTER TABLE donations 
ADD INDEX idx_gateway_payment_id (gateway_payment_id),
ADD INDEX idx_checkout_request_id (checkout_request_id),
ADD INDEX idx_gateway_transaction_id (gateway_transaction_id);

-- Show the updated table structure
DESCRIBE donations;
