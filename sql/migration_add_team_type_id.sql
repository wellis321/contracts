-- Migration: Add team_type_id column to teams table
-- This updates existing teams table to support custom team types

-- Check if team_type_id column exists, if not add it
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'teams' 
    AND COLUMN_NAME = 'team_type_id'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE teams ADD COLUMN team_type_id INT NULL AFTER parent_team_id, ADD FOREIGN KEY fk_team_team_type (team_type_id) REFERENCES team_types(id) ON DELETE SET NULL, ADD INDEX idx_team_type (team_type_id)',
    'SELECT "Column team_type_id already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- If teams table has old ENUM team_type column, we can optionally remove it
-- But first check if it exists
SET @old_col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'teams' 
    AND COLUMN_NAME = 'team_type'
    AND COLUMN_TYPE LIKE '%ENUM%'
);

-- Note: We're not automatically removing the old column in case you want to migrate data first
-- If you want to remove it, uncomment the following:
-- SET @sql = IF(@old_col_exists > 0,
--     'ALTER TABLE teams DROP COLUMN team_type',
--     'SELECT "Old team_type column does not exist" AS message'
-- );
-- PREPARE stmt FROM @sql;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

