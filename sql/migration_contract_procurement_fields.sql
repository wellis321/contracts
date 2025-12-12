-- Migration: Add procurement and contract management fields
-- Enhances contracts table with procurement route, tender status, and framework information

ALTER TABLE contracts 
ADD COLUMN procurement_route VARCHAR(100) NULL AFTER contract_number,
ADD COLUMN tender_status VARCHAR(100) NULL AFTER procurement_route,
ADD COLUMN framework_agreement_id VARCHAR(255) NULL AFTER tender_status,
ADD COLUMN evaluation_criteria TEXT NULL AFTER framework_agreement_id,
ADD COLUMN quality_price_weighting VARCHAR(50) NULL AFTER evaluation_criteria,
ADD COLUMN contract_duration_months INT NULL AFTER end_date,
ADD COLUMN extension_options TEXT NULL AFTER contract_duration_months,
ADD COLUMN price_review_mechanism VARCHAR(255) NULL AFTER extension_options,
ADD COLUMN inflation_indexation VARCHAR(100) NULL AFTER price_review_mechanism,
ADD COLUMN fair_work_compliance BOOLEAN DEFAULT FALSE AFTER inflation_indexation,
ADD COLUMN community_benefits TEXT NULL AFTER fair_work_compliance,
ADD INDEX idx_procurement_route (procurement_route),
ADD INDEX idx_tender_status (tender_status);

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
