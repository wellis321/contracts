-- Migration: Add terminology preferences to organisations table
-- Allows organisations to customise how "people" are referred to throughout the site
-- Safe to run multiple times (checks if columns exist first)

-- Add person_singular column if it doesn't exist
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'organisations' 
    AND COLUMN_NAME = 'person_singular'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE organisations ADD COLUMN person_singular VARCHAR(100) DEFAULT \'person\' AFTER seats_used',
    'SELECT "Column person_singular already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add person_plural column if it doesn't exist
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'organisations' 
    AND COLUMN_NAME = 'person_plural'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE organisations ADD COLUMN person_plural VARCHAR(100) DEFAULT \'people\' AFTER person_singular',
    'SELECT "Column person_plural already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing organisations to use defaults
UPDATE organisations 
SET person_singular = 'person', person_plural = 'people' 
WHERE person_singular IS NULL OR person_plural IS NULL;
