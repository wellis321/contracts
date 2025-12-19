-- Migration: Add procurement and contract management fields
-- Enhances contracts table with procurement route, tender status, and framework information

-- Safe to run multiple times (checks if columns exist first)
-- Add procurement_route column
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'contracts' 
    AND COLUMN_NAME = 'procurement_route'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE contracts ADD COLUMN procurement_route VARCHAR(100) NULL AFTER contract_number',
    'SELECT "Column procurement_route already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add tender_status column
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'contracts' 
    AND COLUMN_NAME = 'tender_status'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE contracts ADD COLUMN tender_status VARCHAR(100) NULL AFTER procurement_route',
    'SELECT "Column tender_status already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add framework_agreement_id column
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'contracts' 
    AND COLUMN_NAME = 'framework_agreement_id'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE contracts ADD COLUMN framework_agreement_id VARCHAR(255) NULL AFTER tender_status',
    'SELECT "Column framework_agreement_id already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add evaluation_criteria column
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'contracts' 
    AND COLUMN_NAME = 'evaluation_criteria'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE contracts ADD COLUMN evaluation_criteria TEXT NULL AFTER framework_agreement_id',
    'SELECT "Column evaluation_criteria already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add quality_price_weighting column
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'contracts' 
    AND COLUMN_NAME = 'quality_price_weighting'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE contracts ADD COLUMN quality_price_weighting VARCHAR(50) NULL AFTER evaluation_criteria',
    'SELECT "Column quality_price_weighting already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add contract_duration_months column
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'contracts' 
    AND COLUMN_NAME = 'contract_duration_months'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE contracts ADD COLUMN contract_duration_months INT NULL AFTER end_date',
    'SELECT "Column contract_duration_months already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add extension_options column
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'contracts' 
    AND COLUMN_NAME = 'extension_options'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE contracts ADD COLUMN extension_options TEXT NULL AFTER contract_duration_months',
    'SELECT "Column extension_options already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add price_review_mechanism column
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'contracts' 
    AND COLUMN_NAME = 'price_review_mechanism'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE contracts ADD COLUMN price_review_mechanism VARCHAR(255) NULL AFTER extension_options',
    'SELECT "Column price_review_mechanism already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add inflation_indexation column
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'contracts' 
    AND COLUMN_NAME = 'inflation_indexation'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE contracts ADD COLUMN inflation_indexation VARCHAR(100) NULL AFTER price_review_mechanism',
    'SELECT "Column inflation_indexation already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add fair_work_compliance column
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'contracts' 
    AND COLUMN_NAME = 'fair_work_compliance'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE contracts ADD COLUMN fair_work_compliance BOOLEAN DEFAULT FALSE AFTER inflation_indexation',
    'SELECT "Column fair_work_compliance already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add community_benefits column
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'contracts' 
    AND COLUMN_NAME = 'community_benefits'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE contracts ADD COLUMN community_benefits TEXT NULL AFTER fair_work_compliance',
    'SELECT "Column community_benefits already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index idx_procurement_route if it doesn't exist
SET @idx_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'contracts' 
    AND INDEX_NAME = 'idx_procurement_route'
);
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE contracts ADD INDEX idx_procurement_route (procurement_route)',
    'SELECT "Index idx_procurement_route already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index idx_tender_status if it doesn't exist
SET @idx_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'contracts' 
    AND INDEX_NAME = 'idx_tender_status'
);
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE contracts ADD INDEX idx_tender_status (tender_status)',
    'SELECT "Index idx_tender_status already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Create procurement routes reference table
CREATE TABLE IF NOT EXISTS procurement_routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create tender statuses reference table
CREATE TABLE IF NOT EXISTS tender_statuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default procurement routes
INSERT INTO procurement_routes (name, description) VALUES
('Competitive Tender - Open', 'Open procedure where any provider can submit a tender'),
('Competitive Tender - Restricted', 'Two-stage process: pre-qualification then invitation to tender'),
('Framework Agreement Call-Off', 'Contract awarded from pre-qualified framework without re-tendering'),
('Direct Award', 'Direct award without competition (e.g., SDS Option 1 or 2, specialist service)'),
('Spot Purchase', 'Emergency or urgent placement, negotiated rate'),
('Block Contract', 'Authority buys set capacity (e.g., 100 hours/week)'),
('Dynamic Purchasing System', 'Electronic system for repeat purchases where providers can join at any time'),
('Public Social Partnership', 'Collaborative approach focusing on innovation rather than price competition'),
('Competitive Dialogue', 'Complex contracts where requirements may evolve'),
('Innovation Partnership', 'For developing innovative solutions')
ON DUPLICATE KEY UPDATE description=VALUES(description);

-- Insert default tender statuses (in workflow order)
INSERT INTO tender_statuses (name, description, display_order) VALUES
('Market Engagement', 'Initial discussions with local authority about requirements', 1),
('Pre-Qualification', 'Submitting pre-qualification questionnaire', 2),
('Tender Submitted', 'Tender submitted, awaiting evaluation', 3),
('Under Evaluation', 'Local authority evaluating tender', 4),
('Clarification Requested', 'Local authority requesting additional information', 5),
('Awarded', 'Contract awarded to your organisation', 6),
('Lost', 'Contract awarded to another provider', 7),
('Contract Live', 'Contract is active and operational', 8),
('Extension Negotiation', 'Negotiating contract extension', 9),
('Retender Pending', 'Contract ending, retender process starting', 10),
('Contract Ended', 'Contract has concluded', 11)
ON DUPLICATE KEY UPDATE description=VALUES(description), display_order=VALUES(display_order);
