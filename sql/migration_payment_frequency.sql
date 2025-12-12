-- Migration: Add payment frequency to contract_payments table
-- This allows tracking payment frequency (Weekly, Monthly, Quarterly, etc.)

ALTER TABLE contract_payments 
ADD COLUMN payment_frequency VARCHAR(50) NULL AFTER payment_method_id;

-- Add index for payment frequency queries
CREATE INDEX idx_payment_frequency ON contract_payments(payment_frequency);

