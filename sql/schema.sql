-- Social Care Contracts Management Application Database Schema
-- UK English spelling used throughout

-- Create database (uncomment if needed)
-- CREATE DATABASE IF NOT EXISTS social_care_contracts CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE social_care_contracts;

-- Organisations table - Multi-tenant organisations
CREATE TABLE IF NOT EXISTS organisations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    domain VARCHAR(255) NOT NULL UNIQUE,
    seats_allocated INT NOT NULL DEFAULT 0,
    seats_used INT NOT NULL DEFAULT 0,
    person_singular VARCHAR(100) DEFAULT 'person',
    person_plural VARCHAR(100) DEFAULT 'people',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_domain (domain)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Roles table - Role definitions
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users table - User accounts with organisation association
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_email (email),
    INDEX idx_organisation (organisation_id),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User roles table - User-role assignments
CREATE TABLE IF NOT EXISTS user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_role (user_id, role_id),
    INDEX idx_user (user_id),
    INDEX idx_role (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Local Authorities table
CREATE TABLE IF NOT EXISTS local_authorities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    code VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contract Types table - Configurable contract types per organisation
-- organisation_id can be NULL for system-wide default types
CREATE TABLE IF NOT EXISTS contract_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT NULL,
    is_system_default BOOLEAN DEFAULT FALSE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_org_type (organisation_id, name),
    INDEX idx_organisation (organisation_id),
    INDEX idx_organisation_system (organisation_id, is_system_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rates table - Current rates for contract types
CREATE TABLE IF NOT EXISTS rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contract_type_id INT NOT NULL,
    local_authority_id INT NOT NULL,
    rate_amount DECIMAL(10, 2) NOT NULL,
    effective_from DATE NOT NULL,
    effective_to DATE NULL,
    is_current BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (contract_type_id) REFERENCES contract_types(id) ON DELETE CASCADE,
    FOREIGN KEY (local_authority_id) REFERENCES local_authorities(id) ON DELETE CASCADE,
    INDEX idx_contract_type (contract_type_id),
    INDEX idx_local_authority (local_authority_id),
    INDEX idx_effective_dates (effective_from, effective_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rate History table - Historical rate changes for reporting
CREATE TABLE IF NOT EXISTS rate_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rate_id INT NOT NULL,
    contract_type_id INT NOT NULL,
    local_authority_id INT NOT NULL,
    previous_rate DECIMAL(10, 2),
    new_rate DECIMAL(10, 2) NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changed_by INT,
    change_reason TEXT,
    FOREIGN KEY (rate_id) REFERENCES rates(id) ON DELETE CASCADE,
    FOREIGN KEY (contract_type_id) REFERENCES contract_types(id) ON DELETE CASCADE,
    FOREIGN KEY (local_authority_id) REFERENCES local_authorities(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_rate (rate_id),
    INDEX idx_contract_type (contract_type_id),
    INDEX idx_changed_at (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment Methods table - Payment types (tender, self-directed support, etc.)
CREATE TABLE IF NOT EXISTS payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- People/Individuals table - Core person information
-- Tracks individuals across contracts and local authorities
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

-- Contracts table - Contract records (single person or bulk)
CREATE TABLE IF NOT EXISTS contracts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT NOT NULL,
    contract_type_id INT NOT NULL,
    local_authority_id INT NOT NULL,
    person_id INT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    contract_number VARCHAR(100),
    procurement_route VARCHAR(100) NULL,
    tender_status VARCHAR(100) NULL,
    framework_agreement_id VARCHAR(255) NULL,
    evaluation_criteria TEXT NULL,
    quality_price_weighting VARCHAR(50) NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    contract_duration_months INT NULL,
    extension_options TEXT NULL,
    price_review_mechanism VARCHAR(255) NULL,
    inflation_indexation VARCHAR(100) NULL,
    fair_work_compliance BOOLEAN DEFAULT FALSE,
    community_benefits TEXT NULL,
    is_single_person BOOLEAN DEFAULT TRUE,
    number_of_people INT DEFAULT 1,
    total_amount DECIMAL(12, 2),
    daytime_hours DECIMAL(10, 2),
    sleepover_hours DECIMAL(10, 2),
    number_of_staff INT,
    status VARCHAR(50) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (contract_type_id) REFERENCES contract_types(id) ON DELETE CASCADE,
    FOREIGN KEY (local_authority_id) REFERENCES local_authorities(id) ON DELETE CASCADE,
    FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_organisation (organisation_id),
    INDEX idx_contract_type (contract_type_id),
    INDEX idx_local_authority (local_authority_id),
    INDEX idx_person (person_id),
    INDEX idx_procurement_route (procurement_route),
    INDEX idx_tender_status (tender_status),
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Procurement Routes reference table
CREATE TABLE IF NOT EXISTS procurement_routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tender Statuses reference table
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

-- Contract People junction table - Links contracts to people
-- This allows tracking which people are in which contracts across local authorities
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

-- Contract Payments table - Payment records linking contracts to payment methods
CREATE TABLE IF NOT EXISTS contract_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contract_id INT NOT NULL,
    payment_method_id INT NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    payment_date DATE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id) ON DELETE CASCADE,
    INDEX idx_contract (contract_id),
    INDEX idx_payment_method (payment_method_id),
    INDEX idx_payment_date (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin Costs table - Additional costs on top of rates
CREATE TABLE IF NOT EXISTS admin_costs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contract_id INT NOT NULL,
    local_authority_id INT NOT NULL,
    cost_amount DECIMAL(10, 2) NOT NULL,
    cost_type VARCHAR(100),
    description TEXT,
    effective_from DATE NOT NULL,
    effective_to DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE,
    FOREIGN KEY (local_authority_id) REFERENCES local_authorities(id) ON DELETE CASCADE,
    INDEX idx_contract (contract_id),
    INDEX idx_local_authority (local_authority_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default roles
INSERT INTO roles (name, description) VALUES
('superadmin', 'Super administrator with full system access'),
('organisation_admin', 'Organisation administrator with full CRUD access to their organisation data'),
('staff', 'Staff member with role-based read/edit permissions')
ON DUPLICATE KEY UPDATE description=VALUES(description);

-- Insert default payment methods
INSERT INTO payment_methods (name, description) VALUES
('Tender', 'Payment through local authority tender process'),
('Self-Directed Support', 'Payment made by individuals for their own support'),
('Admin Costs', 'Additional administrative costs provided by local authority')
ON DUPLICATE KEY UPDATE description=VALUES(description);

-- Insert default contract types (system-wide, available to all organisations)
-- These are standard rate types used across Scottish social care
INSERT INTO contract_types (organisation_id, is_system_default, name, description, is_active) VALUES
(NULL, TRUE, 'Waking/Active Hours', 'Standard care hours where workers are actively providing support during waking hours', TRUE),
(NULL, TRUE, 'Waking Night Shifts', 'Overnight shifts where workers remain awake throughout the night to provide support', TRUE),
(NULL, TRUE, 'Sleepover Hours', 'Overnight shifts where workers stay overnight but can sleep, only intervening if needed. Must be paid at full hourly rate (£12.60/hour minimum from April 2025)', TRUE),
(NULL, TRUE, 'Support Hours', 'General assistance with daily activities and support tasks', TRUE),
(NULL, TRUE, 'Personal Care', 'Specific personal care tasks including washing, toileting, meal preparation, and personal hygiene', TRUE)
ON DUPLICATE KEY UPDATE description=VALUES(description);

-- Reference Rate History Tables
CREATE TABLE IF NOT EXISTS real_living_wage_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    effective_date DATE NOT NULL,
    uk_rate DECIMAL(5,2) NOT NULL,
    london_rate DECIMAL(5,2),
    scotland_rate DECIMAL(5,2),
    announced_date DATE,
    source VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_effective_date (effective_date),
    INDEX idx_effective_date (effective_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS scotland_mandated_minimum_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    effective_date DATE NOT NULL,
    rate DECIMAL(5,2) NOT NULL,
    applies_to VARCHAR(255) DEFAULT 'all hours',
    source VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_effective_date (effective_date),
    INDEX idx_effective_date (effective_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS homecare_association_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year_from DATE NOT NULL,
    year_to DATE,
    scotland_rate DECIMAL(5,2),
    england_rate DECIMAL(5,2),
    wales_rate DECIMAL(5,2),
    northern_ireland_rate DECIMAL(5,2),
    report_url VARCHAR(500),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_year_from (year_from)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Local Authority Rate Updates/News
CREATE TABLE IF NOT EXISTS local_authority_rate_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    local_authority_id INT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    effective_date DATE,
    rate_change DECIMAL(5,2),
    rate_type VARCHAR(100),
    source_url VARCHAR(500),
    published_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (local_authority_id) REFERENCES local_authorities(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_local_authority (local_authority_id),
    INDEX idx_published_date (published_date),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some common Scottish Local Authorities
INSERT INTO local_authorities (name, code) VALUES
('Aberdeen City Council', 'ABE'),
('Aberdeenshire Council', 'ABD'),
('Angus Council', 'ANS'),
('Argyll and Bute Council', 'AGB'),
('City of Edinburgh Council', 'EDH'),
('Clackmannanshire Council', 'CLK'),
('Comhairle nan Eilean Siar', 'ELS'),
('Dumfries and Galloway Council', 'DGY'),
('Dundee City Council', 'DND'),
('East Ayrshire Council', 'EAY'),
('East Dunbartonshire Council', 'EDU'),
('East Lothian Council', 'ELN'),
('East Renfrewshire Council', 'ERW'),
('Falkirk Council', 'FAL'),
('Fife Council', 'FIF'),
('Glasgow City Council', 'GLG'),
('Highland Council', 'HLD'),
('Inverclyde Council', 'IVC'),
('Midlothian Council', 'MLN'),
('Moray Council', 'MRY'),
('North Ayrshire Council', 'NAY'),
('North Lanarkshire Council', 'NLK'),
('Orkney Islands Council', 'ORK'),
('Perth and Kinross Council', 'PKN'),
('Renfrewshire Council', 'RFW'),
('Scottish Borders Council', 'SCB'),
('Shetland Islands Council', 'SHF'),
('South Ayrshire Council', 'SAY'),
('South Lanarkshire Council', 'SLK'),
('Stirling Council', 'STG'),
('West Dunbartonshire Council', 'WDU'),
('West Lothian Council', 'WLN')
ON DUPLICATE KEY UPDATE code=VALUES(code);

-- Insert historical Real Living Wage data
INSERT INTO real_living_wage_history (effective_date, uk_rate, london_rate, scotland_rate, announced_date, source) VALUES
('2016-11-01', 8.25, 9.40, 8.25, '2016-11-01', 'Living Wage Foundation'),
('2017-11-01', 8.45, 9.75, 8.45, '2017-11-01', 'Living Wage Foundation'),
('2018-11-01', 8.75, 10.20, 8.75, '2018-11-01', 'Living Wage Foundation'),
('2019-11-01', 9.00, 10.55, 9.00, '2019-11-01', 'Living Wage Foundation'),
('2020-11-01', 9.30, 10.75, 9.30, '2020-11-01', 'Living Wage Foundation'),
('2020-11-09', 9.50, 10.85, 9.50, '2020-11-09', 'Living Wage Foundation'),
('2021-11-01', 9.90, 11.05, 9.90, '2021-11-01', 'Living Wage Foundation'),
('2022-09-01', 10.90, 11.95, 10.90, '2022-09-01', 'Living Wage Foundation'),
('2023-11-01', 10.90, 11.95, 10.90, '2023-11-01', 'Living Wage Foundation'),
('2024-11-01', 12.00, 13.15, 12.00, '2024-11-01', 'Living Wage Foundation'),
('2024-11-15', 12.60, 13.85, 12.60, '2024-11-15', 'Living Wage Foundation'),
('2025-11-01', 13.45, 14.80, 13.45, '2025-11-01', 'Living Wage Foundation')
ON DUPLICATE KEY UPDATE uk_rate=VALUES(uk_rate), london_rate=VALUES(london_rate), scotland_rate=VALUES(scotland_rate);

-- Insert historical Scottish Government mandated minimum rates
INSERT INTO scotland_mandated_minimum_rates (effective_date, rate, applies_to, source, notes) VALUES
('2016-10-01', 8.25, 'all hours', 'Scottish Government', 'Real Living Wage implementation began'),
('2017-05-01', 8.45, 'all hours', 'Scottish Government', ''),
('2018-01-01', 8.45, 'all hours including sleepover', 'Scottish Government', 'Extended to sleepover hours'),
('2020-03-01', 9.30, 'all hours', 'Scottish Government', 'COVID-19 immediate uplift'),
('2020-11-01', 9.50, 'all hours', 'Scottish Government', ''),
('2021-01-01', 9.90, 'all hours', 'Scottish Government', ''),
('2022-09-01', 10.90, 'all hours', 'Scottish Government', ''),
('2023-04-01', 10.90, 'all hours', 'Scottish Government', ''),
('2024-04-01', 12.00, 'all hours', 'Scottish Government', ''),
('2025-04-01', 12.60, 'all hours', 'Scottish Government', 'Current rate for commissioned services')
ON DUPLICATE KEY UPDATE rate=VALUES(rate), applies_to=VALUES(applies_to), notes=VALUES(notes);

-- Insert Homecare Association benchmark rates
INSERT INTO homecare_association_rates (year_from, year_to, scotland_rate, england_rate, report_url) VALUES
('2018-04-01', '2019-03-31', 16.54, NULL, 'https://www.homecareassociation.org.uk/about-us/research-and-reports.html'),
('2023-04-01', '2024-03-31', 26.50, NULL, 'https://www.homecareassociation.org.uk/about-us/research-and-reports.html'),
('2024-04-01', '2025-03-31', 29.35, NULL, 'https://www.homecareassociation.org.uk/about-us/research-and-reports.html'),
('2025-04-01', '2026-03-31', 32.88, NULL, 'https://www.homecareassociation.org.uk/about-us/research-and-reports.html')
ON DUPLICATE KEY UPDATE scotland_rate=VALUES(scotland_rate), report_url=VALUES(report_url);

-- Glossary Suggestions table
CREATE TABLE IF NOT EXISTS glossary_suggestions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    term VARCHAR(255) NOT NULL,
    definition TEXT NOT NULL,
    suggested_by INT,
    status VARCHAR(50) DEFAULT 'pending',
    reviewed_by INT NULL,
    reviewed_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (suggested_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_suggested_by (suggested_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Glossary Terms table for dynamic glossary management
CREATE TABLE IF NOT EXISTS glossary_terms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    term VARCHAR(255) NOT NULL,
    definition TEXT NOT NULL,
    letter CHAR(1) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    removed_at TIMESTAMP NULL,
    removed_by INT NULL,
    removal_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (removed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_letter (letter),
    INDEX idx_is_active (is_active),
    UNIQUE KEY unique_term (term)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
