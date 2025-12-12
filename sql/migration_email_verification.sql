-- Migration: Add email verification fields to users table
-- Run this SQL to add email verification support

ALTER TABLE users 
ADD COLUMN email_verified BOOLEAN DEFAULT FALSE AFTER is_active,
ADD COLUMN verification_token VARCHAR(255) NULL AFTER email_verified,
ADD COLUMN verification_token_expires_at TIMESTAMP NULL AFTER verification_token,
ADD INDEX idx_verification_token (verification_token),
ADD INDEX idx_email_verified (email_verified);
