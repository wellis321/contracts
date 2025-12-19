-- Migration: Add email verification fields to users table
-- Run this SQL to add email verification support
-- Safe to run multiple times (checks if columns exist first)

-- Add email_verified column if it doesn't exist
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'email_verified'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE users ADD COLUMN email_verified BOOLEAN DEFAULT FALSE AFTER is_active',
    'SELECT "Column email_verified already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add verification_token column if it doesn't exist
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'verification_token'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE users ADD COLUMN verification_token VARCHAR(255) NULL AFTER email_verified',
    'SELECT "Column verification_token already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add verification_token_expires_at column if it doesn't exist
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'verification_token_expires_at'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE users ADD COLUMN verification_token_expires_at TIMESTAMP NULL AFTER verification_token',
    'SELECT "Column verification_token_expires_at already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index idx_verification_token if it doesn't exist
SET @idx_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'users' 
    AND INDEX_NAME = 'idx_verification_token'
);

SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE users ADD INDEX idx_verification_token (verification_token)',
    'SELECT "Index idx_verification_token already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index idx_email_verified if it doesn't exist
SET @idx_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'users' 
    AND INDEX_NAME = 'idx_email_verified'
);

SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE users ADD INDEX idx_email_verified (email_verified)',
    'SELECT "Index idx_email_verified already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
