-- Migration: Add contract number prefix to organisations
-- This allows each organisation to have a custom prefix for auto-generated contract numbers

SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organisations'
    AND COLUMN_NAME = 'contract_number_prefix'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE organisations ADD COLUMN contract_number_prefix VARCHAR(20) NULL AFTER name',
    'SELECT "Column contract_number_prefix already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

