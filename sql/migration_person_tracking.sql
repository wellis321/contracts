-- Migration: Add person/individual tracking system
-- This allows tracking people across contracts and local authorities over time

-- People/Individuals table - Core person information
CREATE TABLE IF NOT EXISTS people (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    INDEX idx_organisation (organisation_id),
    INDEX idx_name (last_name, first_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Person Identifiers table - Multiple identifiers per person
-- Supports CHI number, SWIS number, NI number, and organisation-specific identifiers
CREATE TABLE IF NOT EXISTS person_identifiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    person_id INT NOT NULL,
    identifier_type VARCHAR(50) NOT NULL,
    identifier_value VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    verified BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE,
    UNIQUE KEY unique_identifier (identifier_type, identifier_value),
    INDEX idx_person (person_id),
    INDEX idx_type_value (identifier_type, identifier_value),
    INDEX idx_primary (person_id, is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contract People junction table - Links contracts to people
-- This allows tracking which people are in which contracts
CREATE TABLE IF NOT EXISTS contract_people (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contract_id INT NOT NULL,
    person_id INT NOT NULL,
    local_authority_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE,
    FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE,
    FOREIGN KEY (local_authority_id) REFERENCES local_authorities(id) ON DELETE CASCADE,
    UNIQUE KEY unique_contract_person (contract_id, person_id),
    INDEX idx_contract (contract_id),
    INDEX idx_person (person_id),
    INDEX idx_local_authority (local_authority_id),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add person_id to contracts table for single person contracts (optional, for quick lookup)
ALTER TABLE contracts 
ADD COLUMN person_id INT NULL AFTER local_authority_id,
ADD FOREIGN KEY fk_contract_person (person_id) REFERENCES people(id) ON DELETE SET NULL,
ADD INDEX idx_person (person_id);

-- Note: For single person contracts, you can use person_id directly
-- For bulk contracts, use the contract_people junction table
