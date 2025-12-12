-- Migration: Create tender opportunities table
-- Tracks available tender opportunities that can be applied for

CREATE TABLE IF NOT EXISTS tender_opportunities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT NULL COMMENT 'NULL for system-wide opportunities, or specific org',
    local_authority_id INT NOT NULL,
    contract_type_id INT NULL,
    
    -- Opportunity details
    title VARCHAR(255) NOT NULL,
    description TEXT,
    tender_reference VARCHAR(255) NULL COMMENT 'Reference from tender portal (e.g., Public Contracts Scotland)',
    source VARCHAR(100) DEFAULT 'manual' COMMENT 'manual, public_contracts_scotland, other',
    source_url TEXT NULL COMMENT 'Link to original tender notice',
    
    -- Key dates
    published_date DATE NULL,
    submission_deadline DATE NOT NULL,
    clarification_deadline DATE NULL,
    award_date_expected DATE NULL,
    
    -- Contract details
    estimated_value DECIMAL(12, 2) NULL,
    contract_duration_months INT NULL,
    number_of_people INT NULL,
    geographic_coverage TEXT NULL,
    
    -- Status and tracking
    status VARCHAR(50) DEFAULT 'open' COMMENT 'open, interested, applied, closed, awarded',
    interest_level VARCHAR(50) NULL COMMENT 'high, medium, low',
    notes TEXT NULL COMMENT 'Internal notes about this opportunity',
    
    -- Application tracking
    application_created BOOLEAN DEFAULT FALSE,
    tender_application_id INT NULL COMMENT 'Link to tender_application if created',
    
    -- Metadata
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (local_authority_id) REFERENCES local_authorities(id) ON DELETE CASCADE,
    FOREIGN KEY (contract_type_id) REFERENCES contract_types(id) ON DELETE SET NULL,
    FOREIGN KEY (tender_application_id) REFERENCES tender_applications(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_organisation (organisation_id),
    INDEX idx_local_authority (local_authority_id),
    INDEX idx_status (status),
    INDEX idx_submission_deadline (submission_deadline),
    INDEX idx_source (source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

