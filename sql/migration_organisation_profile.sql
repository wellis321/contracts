-- Migration: Add organization profile fields for tender applications
-- These fields store information needed for tender submissions

-- Add organization profile fields (with conditional checks to prevent errors if columns already exist)
SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organisations'
    AND COLUMN_NAME = 'company_registration_number'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE organisations ADD COLUMN company_registration_number VARCHAR(50) NULL AFTER domain',
    'SELECT "Column company_registration_number already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organisations'
    AND COLUMN_NAME = 'care_inspectorate_registration'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE organisations ADD COLUMN care_inspectorate_registration VARCHAR(50) NULL AFTER company_registration_number',
    'SELECT "Column care_inspectorate_registration already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organisations'
    AND COLUMN_NAME = 'charity_number'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE organisations ADD COLUMN charity_number VARCHAR(50) NULL AFTER care_inspectorate_registration',
    'SELECT "Column charity_number already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organisations'
    AND COLUMN_NAME = 'vat_number'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE organisations ADD COLUMN vat_number VARCHAR(50) NULL AFTER charity_number',
    'SELECT "Column vat_number already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organisations'
    AND COLUMN_NAME = 'registered_address'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE organisations ADD COLUMN registered_address TEXT NULL AFTER vat_number',
    'SELECT "Column registered_address already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organisations'
    AND COLUMN_NAME = 'trading_address'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE organisations ADD COLUMN trading_address TEXT NULL AFTER registered_address',
    'SELECT "Column trading_address already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organisations'
    AND COLUMN_NAME = 'phone'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE organisations ADD COLUMN phone VARCHAR(50) NULL AFTER trading_address',
    'SELECT "Column phone already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organisations'
    AND COLUMN_NAME = 'website'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE organisations ADD COLUMN website VARCHAR(255) NULL AFTER phone',
    'SELECT "Column website already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organisations'
    AND COLUMN_NAME = 'care_inspectorate_rating'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE organisations ADD COLUMN care_inspectorate_rating VARCHAR(50) NULL AFTER website',
    'SELECT "Column care_inspectorate_rating already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organisations'
    AND COLUMN_NAME = 'last_inspection_date'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE organisations ADD COLUMN last_inspection_date DATE NULL AFTER care_inspectorate_rating',
    'SELECT "Column last_inspection_date already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organisations'
    AND COLUMN_NAME = 'main_contact_name'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE organisations ADD COLUMN main_contact_name VARCHAR(255) NULL AFTER last_inspection_date',
    'SELECT "Column main_contact_name already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organisations'
    AND COLUMN_NAME = 'main_contact_email'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE organisations ADD COLUMN main_contact_email VARCHAR(255) NULL AFTER main_contact_name',
    'SELECT "Column main_contact_email already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organisations'
    AND COLUMN_NAME = 'main_contact_phone'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE organisations ADD COLUMN main_contact_phone VARCHAR(50) NULL AFTER main_contact_email',
    'SELECT "Column main_contact_phone already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organisations'
    AND COLUMN_NAME = 'geographic_coverage'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE organisations ADD COLUMN geographic_coverage TEXT NULL AFTER main_contact_phone',
    'SELECT "Column geographic_coverage already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organisations'
    AND COLUMN_NAME = 'service_types'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE organisations ADD COLUMN service_types TEXT NULL AFTER geographic_coverage',
    'SELECT "Column service_types already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organisations'
    AND COLUMN_NAME = 'languages_spoken'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE organisations ADD COLUMN languages_spoken TEXT NULL AFTER service_types',
    'SELECT "Column languages_spoken already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organisations'
    AND COLUMN_NAME = 'specialist_expertise'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE organisations ADD COLUMN specialist_expertise TEXT NULL AFTER languages_spoken',
    'SELECT "Column specialist_expertise already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add indexes for common lookups (with conditional checks)
SET @idx_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organisations'
    AND INDEX_NAME = 'idx_care_inspectorate_reg'
);

SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE organisations ADD INDEX idx_care_inspectorate_reg (care_inspectorate_registration)',
    'SELECT "Index idx_care_inspectorate_reg already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organisations'
    AND INDEX_NAME = 'idx_company_reg'
);

SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE organisations ADD INDEX idx_company_reg (company_registration_number)',
    'SELECT "Index idx_company_reg already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

