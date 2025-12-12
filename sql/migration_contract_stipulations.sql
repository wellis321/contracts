-- Migration: Add Contract Stipulations/Requirements Field
-- Allows storing contract-specific conditions, requirements, and stipulations
-- Examples: staff training requirements, location restrictions, compliance requirements, etc.

-- Check if stipulations column exists, if not add it
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'contracts' 
    AND COLUMN_NAME = 'stipulations'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE contracts ADD COLUMN stipulations TEXT NULL AFTER description',
    'SELECT "Column stipulations already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Note: The stipulations field can store:
-- - Staff training requirements (e.g., "All staff must be trained in manual handling")
-- - Location restrictions (e.g., "Person must remain within Glasgow City boundaries")
-- - Compliance requirements (e.g., "Must comply with Care Inspectorate standards")
-- - Operational requirements (e.g., "24/7 on-call support required")
-- - Any other contract-specific conditions or stipulations

