-- Migration: Create tender applications table
-- Stores tender application data with pre-filled information from contracts

CREATE TABLE IF NOT EXISTS tender_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT NOT NULL,
    local_authority_id INT NOT NULL,
    procurement_route VARCHAR(100) NULL,
    contract_type_id INT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    service_description TEXT,
    number_of_people INT NULL,
    geographic_coverage TEXT NULL,
    
    -- Pricing information (pre-filled from contract types)
    rates_json TEXT NULL COMMENT 'JSON structure storing rates per contract type',
    total_contract_value DECIMAL(12, 2) NULL,
    payment_terms TEXT NULL,
    price_review_mechanism VARCHAR(255) NULL,
    inflation_indexation VARCHAR(100) NULL,
    
    -- Quality and experience
    care_inspectorate_rating VARCHAR(50) NULL,
    relevant_experience TEXT NULL COMMENT 'References to existing contracts',
    staff_qualifications TEXT NULL,
    training_programs TEXT NULL,
    
    -- Fair work and community benefits
    fair_work_compliance BOOLEAN DEFAULT FALSE,
    living_wage_commitment BOOLEAN DEFAULT FALSE,
    staff_terms_conditions TEXT NULL,
    community_benefits TEXT NULL,
    environmental_commitments TEXT NULL,
    
    -- Operational details
    staffing_levels INT NULL,
    daytime_hours DECIMAL(10, 2) NULL,
    sleepover_hours DECIMAL(10, 2) NULL,
    languages_offered TEXT NULL,
    specialist_skills TEXT NULL,
    
    -- References
    previous_contracts TEXT NULL COMMENT 'References to existing contracts with this LA',
    other_references TEXT NULL,
    client_testimonials TEXT NULL,
    
    -- Status and tracking
    status VARCHAR(50) DEFAULT 'draft' COMMENT 'draft, submitted, under_review, awarded, lost, withdrawn',
    tender_reference VARCHAR(255) NULL COMMENT 'Reference number from LA tender portal',
    submission_deadline DATE NULL,
    submitted_at TIMESTAMP NULL,
    awarded_at TIMESTAMP NULL,
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (local_authority_id) REFERENCES local_authorities(id) ON DELETE CASCADE,
    FOREIGN KEY (contract_type_id) REFERENCES contract_types(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_organisation (organisation_id),
    INDEX idx_local_authority (local_authority_id),
    INDEX idx_status (status),
    INDEX idx_submission_deadline (submission_deadline),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for tender application documents (attachments)
CREATE TABLE IF NOT EXISTS tender_application_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tender_application_id INT NOT NULL,
    document_type VARCHAR(100) NOT NULL COMMENT 'policy, certificate, account, reference, other',
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NULL,
    mime_type VARCHAR(100) NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploaded_by INT,
    
    FOREIGN KEY (tender_application_id) REFERENCES tender_applications(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_tender_application (tender_application_id),
    INDEX idx_document_type (document_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

