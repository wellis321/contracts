-- Seed Data: Test Data for Development and Demonstration
-- This file creates realistic test data representing what a social care organisation might enter
-- Run this AFTER running all migrations and schema setup

-- Note: This assumes:
-- 1. Local authorities already exist (from schema.sql)
-- 2. System default contract types exist
-- 3. Roles exist (superadmin, organisation_admin, staff)
-- 4. Payment methods exist

-- ============================================
-- TEST ORGANISATION: Highland Care Services
-- ============================================

-- Create test organisation
INSERT INTO organisations (name, domain, seats_allocated, seats_used, person_singular, person_plural,
    company_registration_number, care_inspectorate_registration, charity_number, vat_number,
    registered_address, trading_address, phone, website,
    care_inspectorate_rating, last_inspection_date,
    main_contact_name, main_contact_email, main_contact_phone,
    geographic_coverage, service_types, languages_spoken, specialist_expertise)
VALUES (
    'Highland Care Services',
    'highlandcare.test',
    15,
    0, -- Will be updated after users are created
    'person we support',
    'people we support',
    'SC123456',
    'CS987654321',
    'SC012345',
    'GB123456789',
    '123 Main Street, Inverness, IV1 1AA',
    '123 Main Street, Inverness, IV1 1AA',
    '01463 123456',
    'https://www.highlandcare.test',
    'Very Good',
    '2024-06-15',
    'Sarah MacLeod',
    'sarah.macleod@highlandcare.test',
    '01463 123457',
    'Highland, Moray, Aberdeenshire',
    'Supported Living, Care at Home, Respite Care, Day Services',
    'English, Gaelic, Polish',
    'Learning Disabilities, Autism, Mental Health Support, Complex Needs'
) ON DUPLICATE KEY UPDATE name=VALUES(name);

SET @org_id = LAST_INSERT_ID();
-- If organisation already exists, get its ID
SET @org_id = IF(@org_id = 0, (SELECT id FROM organisations WHERE domain = 'highlandcare.test'), @org_id);

-- ============================================
-- USERS/STAFF
-- ============================================

-- Organisation Admin
INSERT INTO users (organisation_id, email, password_hash, first_name, last_name, is_active, email_verified)
VALUES (
    @org_id,
    'admin@highlandcare.test',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: "password"
    'Sarah',
    'MacLeod',
    TRUE,
    TRUE
) ON DUPLICATE KEY UPDATE organisation_id=VALUES(organisation_id);

SET @admin_user_id = LAST_INSERT_ID();
SET @admin_user_id = IF(@admin_user_id = 0, (SELECT id FROM users WHERE email = 'admin@highlandcare.test'), @admin_user_id);

-- Finance Manager
INSERT INTO users (organisation_id, email, password_hash, first_name, last_name, is_active, email_verified)
VALUES (
    @org_id,
    'finance@highlandcare.test',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'James',
    'Campbell',
    TRUE,
    TRUE
) ON DUPLICATE KEY UPDATE organisation_id=VALUES(organisation_id);

SET @finance_user_id = LAST_INSERT_ID();
SET @finance_user_id = IF(@finance_user_id = 0, (SELECT id FROM users WHERE email = 'finance@highlandcare.test'), @finance_user_id);

-- Team Manager - Inverness Team
INSERT INTO users (organisation_id, email, password_hash, first_name, last_name, is_active, email_verified)
VALUES (
    @org_id,
    'manager.inverness@highlandcare.test',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Fiona',
    'MacDonald',
    TRUE,
    TRUE
) ON DUPLICATE KEY UPDATE organisation_id=VALUES(organisation_id);

SET @manager_inverness_id = LAST_INSERT_ID();
SET @manager_inverness_id = IF(@manager_inverness_id = 0, (SELECT id FROM users WHERE email = 'manager.inverness@highlandcare.test'), @manager_inverness_id);

-- Team Manager - Aberdeen Team
INSERT INTO users (organisation_id, email, password_hash, first_name, last_name, is_active, email_verified)
VALUES (
    @org_id,
    'manager.aberdeen@highlandcare.test',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'David',
    'Stewart',
    TRUE,
    TRUE
) ON DUPLICATE KEY UPDATE organisation_id=VALUES(organisation_id);

SET @manager_aberdeen_id = LAST_INSERT_ID();
SET @manager_aberdeen_id = IF(@manager_aberdeen_id = 0, (SELECT id FROM users WHERE email = 'manager.aberdeen@highlandcare.test'), @manager_aberdeen_id);

-- Support Worker
INSERT INTO users (organisation_id, email, password_hash, first_name, last_name, is_active, email_verified)
VALUES (
    @org_id,
    'support.worker@highlandcare.test',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Emma',
    'Robertson',
    TRUE,
    TRUE
) ON DUPLICATE KEY UPDATE organisation_id=VALUES(organisation_id);

SET @support_worker_id = LAST_INSERT_ID();
SET @support_worker_id = IF(@support_worker_id = 0, (SELECT id FROM users WHERE email = 'support.worker@highlandcare.test'), @support_worker_id);

-- Assign roles
INSERT INTO user_roles (user_id, role_id, assigned_by)
SELECT @admin_user_id, id, @admin_user_id FROM roles WHERE name = 'organisation_admin'
ON DUPLICATE KEY UPDATE user_id=user_id;

INSERT INTO user_roles (user_id, role_id, assigned_by)
SELECT @finance_user_id, id, @admin_user_id FROM roles WHERE name = 'organisation_admin'
ON DUPLICATE KEY UPDATE user_id=user_id;

INSERT INTO user_roles (user_id, role_id, assigned_by)
SELECT @manager_inverness_id, id, @admin_user_id FROM roles WHERE name = 'staff'
ON DUPLICATE KEY UPDATE user_id=user_id;

INSERT INTO user_roles (user_id, role_id, assigned_by)
SELECT @manager_aberdeen_id, id, @admin_user_id FROM roles WHERE name = 'staff'
ON DUPLICATE KEY UPDATE user_id=user_id;

INSERT INTO user_roles (user_id, role_id, assigned_by)
SELECT @support_worker_id, id, @admin_user_id FROM roles WHERE name = 'staff'
ON DUPLICATE KEY UPDATE user_id=user_id;

-- ============================================
-- TEAM STRUCTURE
-- ============================================

-- Ensure default team roles exist (they should be created by migration, but create if missing)
INSERT INTO team_roles (organisation_id, name, description, access_level, display_order)
SELECT @org_id, 'Member', 'Basic team membership with view access', 'team', 1
WHERE NOT EXISTS (SELECT 1 FROM team_roles WHERE organisation_id = @org_id AND name = 'Member');

INSERT INTO team_roles (organisation_id, name, description, access_level, display_order)
SELECT @org_id, 'Manager', 'Can manage contracts assigned to their team and child teams', 'team', 2
WHERE NOT EXISTS (SELECT 1 FROM team_roles WHERE organisation_id = @org_id AND name = 'Manager');

INSERT INTO team_roles (organisation_id, name, description, access_level, display_order)
SELECT @org_id, 'Finance', 'Can view and edit all contracts in the organisation', 'organisation', 4
WHERE NOT EXISTS (SELECT 1 FROM team_roles WHERE organisation_id = @org_id AND name = 'Finance');

-- Get team role IDs
SET @team_manager_role_id = (SELECT id FROM team_roles WHERE organisation_id = @org_id AND name = 'Manager' LIMIT 1);
SET @team_member_role_id = (SELECT id FROM team_roles WHERE organisation_id = @org_id AND name = 'Member' LIMIT 1);
SET @team_finance_role_id = (SELECT id FROM team_roles WHERE organisation_id = @org_id AND name = 'Finance' LIMIT 1);

-- Create team types
INSERT INTO team_types (organisation_id, name, description, display_order)
VALUES
    (@org_id, 'Area', 'Geographic area covering multiple teams', 1),
    (@org_id, 'Team', 'Local support team', 2)
ON DUPLICATE KEY UPDATE name=VALUES(name);

SET @area_type_id = (SELECT id FROM team_types WHERE organisation_id = @org_id AND name = 'Area' LIMIT 1);
SET @team_type_id = (SELECT id FROM team_types WHERE organisation_id = @org_id AND name = 'Team' LIMIT 1);

-- Create areas (parent teams)
INSERT INTO teams (organisation_id, parent_team_id, team_type_id, name, description)
VALUES
    (@org_id, NULL, @area_type_id, 'Highland Area', 'Highland region operations'),
    (@org_id, NULL, @area_type_id, 'Aberdeen Area', 'Aberdeen and Aberdeenshire operations')
ON DUPLICATE KEY UPDATE name=VALUES(name);

SET @highland_area_id = (SELECT id FROM teams WHERE organisation_id = @org_id AND name = 'Highland Area' LIMIT 1);
SET @aberdeen_area_id = (SELECT id FROM teams WHERE organisation_id = @org_id AND name = 'Aberdeen Area' LIMIT 1);

-- Create teams (child teams)
INSERT INTO teams (organisation_id, parent_team_id, team_type_id, name, description)
VALUES
    (@org_id, @highland_area_id, @team_type_id, 'Inverness Support Team', 'Inverness city support team'),
    (@org_id, @highland_area_id, @team_type_id, 'Nairn Support Team', 'Nairn and surrounding areas'),
    (@org_id, @aberdeen_area_id, @team_type_id, 'Aberdeen City Team', 'Aberdeen city support team'),
    (@org_id, @aberdeen_area_id, @team_type_id, 'Aberdeenshire Rural Team', 'Rural Aberdeenshire support')
ON DUPLICATE KEY UPDATE name=VALUES(name);

SET @inverness_team_id = (SELECT id FROM teams WHERE organisation_id = @org_id AND name = 'Inverness Support Team' LIMIT 1);
SET @nairn_team_id = (SELECT id FROM teams WHERE organisation_id = @org_id AND name = 'Nairn Support Team' LIMIT 1);
SET @aberdeen_team_id = (SELECT id FROM teams WHERE organisation_id = @org_id AND name = 'Aberdeen City Team' LIMIT 1);
SET @aberdeenshire_team_id = (SELECT id FROM teams WHERE organisation_id = @org_id AND name = 'Aberdeenshire Rural Team' LIMIT 1);

-- Assign users to teams
INSERT INTO user_teams (user_id, team_id, team_role_id, is_primary)
VALUES
    (@manager_inverness_id, @inverness_team_id, @team_manager_role_id, TRUE),
    (@manager_aberdeen_id, @aberdeen_team_id, @team_manager_role_id, TRUE),
    (@support_worker_id, @inverness_team_id, @team_member_role_id, TRUE),
    (@finance_user_id, @highland_area_id, @team_finance_role_id, FALSE),
    (@finance_user_id, @aberdeen_area_id, @team_finance_role_id, FALSE)
ON DUPLICATE KEY UPDATE team_role_id=VALUES(team_role_id);

-- ============================================
-- PEOPLE BEING SUPPORTED
-- ============================================

-- Get local authority IDs (matching exact names from schema)
SET @highland_la_id = (SELECT id FROM local_authorities WHERE name = 'Highland Council' LIMIT 1);
SET @aberdeen_la_id = (SELECT id FROM local_authorities WHERE name = 'Aberdeen City Council' LIMIT 1);
SET @aberdeenshire_la_id = (SELECT id FROM local_authorities WHERE name = 'Aberdeenshire Council' LIMIT 1);

-- If local authorities don't exist, use first available (fallback)
SET @highland_la_id = IF(@highland_la_id IS NULL, (SELECT id FROM local_authorities LIMIT 1), @highland_la_id);
SET @aberdeen_la_id = IF(@aberdeen_la_id IS NULL, @highland_la_id, @aberdeen_la_id);
SET @aberdeenshire_la_id = IF(@aberdeenshire_la_id IS NULL, @highland_la_id, @aberdeenshire_la_id);

-- Person 1: John Smith - Multiple contracts over time
INSERT INTO people (organisation_id, first_name, last_name, date_of_birth)
VALUES (
    @org_id,
    'John',
    'Smith',
    '1985-03-15'
) ON DUPLICATE KEY UPDATE first_name=VALUES(first_name);

SET @person1_id = LAST_INSERT_ID();
SET @person1_id = IF(@person1_id = 0, (SELECT id FROM people WHERE first_name = 'John' AND last_name = 'Smith' AND organisation_id = @org_id LIMIT 1), @person1_id);

-- Add identifier for Person 1
INSERT INTO person_identifiers (person_id, identifier_type, identifier_value, is_primary, verified)
VALUES (@person1_id, 'CHI', '0101011234', TRUE, TRUE)
ON DUPLICATE KEY UPDATE is_primary=VALUES(is_primary);

-- Person 2: Mary Johnson - Single ongoing contract
INSERT INTO people (organisation_id, first_name, last_name, date_of_birth)
VALUES (
    @org_id,
    'Mary',
    'Johnson',
    '1992-07-22'
) ON DUPLICATE KEY UPDATE first_name=VALUES(first_name);

SET @person2_id = LAST_INSERT_ID();
SET @person2_id = IF(@person2_id = 0, (SELECT id FROM people WHERE first_name = 'Mary' AND last_name = 'Johnson' AND organisation_id = @org_id LIMIT 1), @person2_id);

-- Add identifier for Person 2
INSERT INTO person_identifiers (person_id, identifier_type, identifier_value, is_primary, verified)
VALUES (@person2_id, 'CHI', '0202022345', TRUE, TRUE)
ON DUPLICATE KEY UPDATE is_primary=VALUES(is_primary);

-- Person 3: Robert Brown - Recently moved to Aberdeen
INSERT INTO people (organisation_id, first_name, last_name, date_of_birth)
VALUES (
    @org_id,
    'Robert',
    'Brown',
    '1988-11-30'
) ON DUPLICATE KEY UPDATE first_name=VALUES(first_name);

SET @person3_id = LAST_INSERT_ID();
SET @person3_id = IF(@person3_id = 0, (SELECT id FROM people WHERE first_name = 'Robert' AND last_name = 'Brown' AND organisation_id = @org_id LIMIT 1), @person3_id);

-- Add identifier for Person 3
INSERT INTO person_identifiers (person_id, identifier_type, identifier_value, is_primary, verified)
VALUES (@person3_id, 'SWIS', 'SW123456', TRUE, TRUE)
ON DUPLICATE KEY UPDATE is_primary=VALUES(is_primary);

-- Person 4: Sarah Williams - Learning disabilities support
INSERT INTO people (organisation_id, first_name, last_name, date_of_birth)
VALUES (
    @org_id,
    'Sarah',
    'Williams',
    '1995-02-10'
) ON DUPLICATE KEY UPDATE first_name=VALUES(first_name);

SET @person4_id = LAST_INSERT_ID();
SET @person4_id = IF(@person4_id = 0, (SELECT id FROM people WHERE first_name = 'Sarah' AND last_name = 'Williams' AND organisation_id = @org_id LIMIT 1), @person4_id);

-- Add identifier for Person 4
INSERT INTO person_identifiers (person_id, identifier_type, identifier_value, is_primary, verified)
VALUES (@person4_id, 'CHI', '0303033456', TRUE, TRUE)
ON DUPLICATE KEY UPDATE is_primary=VALUES(is_primary);

-- Person 5: Michael Taylor - Complex needs
INSERT INTO people (organisation_id, first_name, last_name, date_of_birth)
VALUES (
    @org_id,
    'Michael',
    'Taylor',
    '1990-09-05'
) ON DUPLICATE KEY UPDATE first_name=VALUES(first_name);

SET @person5_id = LAST_INSERT_ID();
SET @person5_id = IF(@person5_id = 0, (SELECT id FROM people WHERE first_name = 'Michael' AND last_name = 'Taylor' AND organisation_id = @org_id LIMIT 1), @person5_id);

-- Add identifier for Person 5
INSERT INTO person_identifiers (person_id, identifier_type, identifier_value, is_primary, verified)
VALUES (@person5_id, 'CHI', '0404044567', TRUE, TRUE)
ON DUPLICATE KEY UPDATE is_primary=VALUES(is_primary);

-- ============================================
-- CONTRACT TYPES & RATES
-- ============================================

-- Ensure system default contract types exist (create if missing)
INSERT INTO contract_types (organisation_id, is_system_default, name, description, is_active)
SELECT NULL, TRUE, 'Waking/Active Hours', 'Standard care hours where workers are actively providing support during waking hours', TRUE
WHERE NOT EXISTS (SELECT 1 FROM contract_types WHERE name = 'Waking/Active Hours' AND (is_system_default = 1 OR organisation_id IS NULL));

INSERT INTO contract_types (organisation_id, is_system_default, name, description, is_active)
SELECT NULL, TRUE, 'Sleepover Hours', 'Overnight shifts where workers stay overnight but can sleep, only intervening if needed. Must be paid at full hourly rate (£12.60/hour minimum from April 2025)', TRUE
WHERE NOT EXISTS (SELECT 1 FROM contract_types WHERE name = 'Sleepover Hours' AND (is_system_default = 1 OR organisation_id IS NULL));

INSERT INTO contract_types (organisation_id, is_system_default, name, description, is_active)
SELECT NULL, TRUE, 'Support Hours', 'General assistance with daily activities and support tasks', TRUE
WHERE NOT EXISTS (SELECT 1 FROM contract_types WHERE name = 'Support Hours' AND (is_system_default = 1 OR organisation_id IS NULL));

INSERT INTO contract_types (organisation_id, is_system_default, name, description, is_active)
SELECT NULL, TRUE, 'Personal Care', 'Specific personal care tasks including washing, toileting, meal preparation, and personal hygiene', TRUE
WHERE NOT EXISTS (SELECT 1 FROM contract_types WHERE name = 'Personal Care' AND (is_system_default = 1 OR organisation_id IS NULL));

-- Get system default contract types
SET @waking_hours_id = (SELECT id FROM contract_types WHERE name = 'Waking/Active Hours' AND (is_system_default = 1 OR organisation_id IS NULL) LIMIT 1);
SET @sleepover_id = (SELECT id FROM contract_types WHERE name = 'Sleepover Hours' AND (is_system_default = 1 OR organisation_id IS NULL) LIMIT 1);
SET @support_hours_id = (SELECT id FROM contract_types WHERE name = 'Support Hours' AND (is_system_default = 1 OR organisation_id IS NULL) LIMIT 1);
SET @personal_care_id = (SELECT id FROM contract_types WHERE name = 'Personal Care' AND (is_system_default = 1 OR organisation_id IS NULL) LIMIT 1);

-- Create organisation-specific contract type
INSERT INTO contract_types (organisation_id, is_system_default, name, description, is_active)
VALUES (
    @org_id,
    FALSE,
    'Complex Support Package',
    'Comprehensive support package for people with complex needs including personal care, support hours, and sleepovers',
    TRUE
) ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Get the complex support contract type ID (always query the table, don't rely on LAST_INSERT_ID)
SET @complex_support_id = (SELECT id FROM contract_types WHERE organisation_id = @org_id AND name = 'Complex Support Package' LIMIT 1);
-- Ensure it's a valid ID (not NULL and not 0)
SET @complex_support_id = IF(@complex_support_id IS NULL OR @complex_support_id = 0, NULL, @complex_support_id);

-- Set rates for Highland (only if contract types exist)
INSERT INTO rates (contract_type_id, local_authority_id, rate_amount, effective_from, is_current)
SELECT @waking_hours_id, @highland_la_id, 28.50, '2024-04-01', TRUE
WHERE @waking_hours_id IS NOT NULL
ON DUPLICATE KEY UPDATE rate_amount=VALUES(rate_amount);

INSERT INTO rates (contract_type_id, local_authority_id, rate_amount, effective_from, is_current)
SELECT @sleepover_id, @highland_la_id, 12.60, '2024-04-01', TRUE
WHERE @sleepover_id IS NOT NULL
ON DUPLICATE KEY UPDATE rate_amount=VALUES(rate_amount);

INSERT INTO rates (contract_type_id, local_authority_id, rate_amount, effective_from, is_current)
SELECT @support_hours_id, @highland_la_id, 26.00, '2024-04-01', TRUE
WHERE @support_hours_id IS NOT NULL
ON DUPLICATE KEY UPDATE rate_amount=VALUES(rate_amount);

INSERT INTO rates (contract_type_id, local_authority_id, rate_amount, effective_from, is_current)
SELECT @personal_care_id, @highland_la_id, 30.00, '2024-04-01', TRUE
WHERE @personal_care_id IS NOT NULL
ON DUPLICATE KEY UPDATE rate_amount=VALUES(rate_amount);

INSERT INTO rates (contract_type_id, local_authority_id, rate_amount, effective_from, is_current)
SELECT @complex_support_id, @highland_la_id, 32.00, '2024-04-01', TRUE
WHERE @complex_support_id IS NOT NULL
ON DUPLICATE KEY UPDATE rate_amount=VALUES(rate_amount);

-- Historical rate (rate increase)
INSERT INTO rates (contract_type_id, local_authority_id, rate_amount, effective_from, effective_to, is_current)
SELECT @waking_hours_id, @highland_la_id, 27.00, '2023-04-01', '2024-03-31', FALSE
WHERE NOT EXISTS (
    SELECT 1 FROM rates 
    WHERE contract_type_id = @waking_hours_id 
    AND local_authority_id = @highland_la_id 
    AND effective_from = '2023-04-01'
);

-- Set rates for Aberdeen
INSERT INTO rates (contract_type_id, local_authority_id, rate_amount, effective_from, is_current)
SELECT @waking_hours_id, @aberdeen_la_id, 29.00, '2024-04-01', TRUE
WHERE @waking_hours_id IS NOT NULL
AND NOT EXISTS (
    SELECT 1 FROM rates 
    WHERE contract_type_id = @waking_hours_id 
    AND local_authority_id = @aberdeen_la_id 
    AND is_current = 1
);

INSERT INTO rates (contract_type_id, local_authority_id, rate_amount, effective_from, is_current)
SELECT @sleepover_id, @aberdeen_la_id, 12.60, '2024-04-01', TRUE
WHERE @sleepover_id IS NOT NULL
AND NOT EXISTS (
    SELECT 1 FROM rates 
    WHERE contract_type_id = @sleepover_id 
    AND local_authority_id = @aberdeen_la_id 
    AND is_current = 1
);

-- Set rates for Aberdeenshire
INSERT INTO rates (contract_type_id, local_authority_id, rate_amount, effective_from, is_current)
SELECT @waking_hours_id, @aberdeenshire_la_id, 28.75, '2024-04-01', TRUE
WHERE @waking_hours_id IS NOT NULL
AND NOT EXISTS (
    SELECT 1 FROM rates 
    WHERE contract_type_id = @waking_hours_id 
    AND local_authority_id = @aberdeenshire_la_id 
    AND is_current = 1
);

-- ============================================
-- CONTRACTS (showing progression over time)
-- ============================================

-- Contract 1: John Smith - Initial contract (ended, replaced)
INSERT INTO contracts (
    organisation_id, team_id, contract_type_id, local_authority_id,
    title, description, contract_number, procurement_route, tender_status,
    start_date, end_date, contract_duration_months,
    is_single_person, number_of_people, total_amount,
    daytime_hours, sleepover_hours, number_of_staff,
    status, fair_work_compliance, created_by
)
VALUES (
    @org_id, @inverness_team_id, @waking_hours_id, @highland_la_id,
    'John Smith - Supported Living',
    'Initial supported living contract for John Smith',
    'HCS-2022-001',
    'Competitive Tender - Open',
    'Contract Ended',
    '2022-04-01',
    '2024-03-31',
    24,
    TRUE, 1, 45000.00,
    35.0, 7.0, 2,
    'inactive',
    TRUE,
    @admin_user_id
) ON DUPLICATE KEY UPDATE title=VALUES(title);

SET @contract1_id = LAST_INSERT_ID();
SET @contract1_id = IF(@contract1_id = 0, (SELECT id FROM contracts WHERE contract_number = 'HCS-2022-001' AND organisation_id = @org_id LIMIT 1), @contract1_id);

-- Contract 2: John Smith - Current contract (extension/renewal)
INSERT INTO contracts (
    organisation_id, team_id, contract_type_id, local_authority_id,
    title, description, contract_number, procurement_route, tender_status,
    start_date, end_date, contract_duration_months,
    is_single_person, number_of_people, total_amount,
    daytime_hours, sleepover_hours, number_of_staff,
    status, fair_work_compliance, community_benefits, created_by
)
VALUES (
    @org_id, @inverness_team_id, @complex_support_id, @highland_la_id,
    'John Smith - Enhanced Support Package',
    'Renewed contract with enhanced support package including complex needs support',
    'HCS-2024-001',
    'Framework Agreement Call-Off',
    'Contract Live',
    '2024-04-01',
    '2026-03-31',
    24,
    TRUE, 1, 52000.00,
    40.0, 7.0, 3,
    'active',
    TRUE,
    'Provides employment opportunities for local people, supports local suppliers',
    @admin_user_id
) ON DUPLICATE KEY UPDATE title=VALUES(title);

SET @contract2_id = LAST_INSERT_ID();
SET @contract2_id = IF(@contract2_id = 0, (SELECT id FROM contracts WHERE contract_number = 'HCS-2024-001' AND organisation_id = @org_id LIMIT 1), @contract2_id);

-- Contract 3: Mary Johnson - Ongoing contract
INSERT INTO contracts (
    organisation_id, team_id, contract_type_id, local_authority_id,
    title, description, contract_number, procurement_route, tender_status,
    start_date, end_date, contract_duration_months,
    is_single_person, number_of_people, total_amount,
    daytime_hours, sleepover_hours, number_of_staff,
    status, fair_work_compliance, created_by
)
VALUES (
    @org_id, @inverness_team_id, @support_hours_id, @highland_la_id,
    'Mary Johnson - Support Hours',
    'Ongoing support hours contract for daily living assistance',
    'HCS-2023-002',
    'Direct Award',
    'Contract Live',
    '2023-06-01',
    '2025-05-31',
    24,
    TRUE, 1, 38000.00,
    25.0, 0, 1,
    'active',
    TRUE,
    @manager_inverness_id
) ON DUPLICATE KEY UPDATE title=VALUES(title);

SET @contract3_id = LAST_INSERT_ID();
SET @contract3_id = IF(@contract3_id = 0, (SELECT id FROM contracts WHERE contract_number = 'HCS-2023-002' AND organisation_id = @org_id LIMIT 1), @contract3_id);

-- Contract 4: Robert Brown - New contract in Aberdeen (recently moved)
INSERT INTO contracts (
    organisation_id, team_id, contract_type_id, local_authority_id,
    title, description, contract_number, procurement_route, tender_status,
    start_date, end_date, contract_duration_months,
    is_single_person, number_of_people, total_amount,
    daytime_hours, sleepover_hours, number_of_staff,
    status, fair_work_compliance, created_by
)
VALUES (
    @org_id, @aberdeen_team_id, @waking_hours_id, @aberdeen_la_id,
    'Robert Brown - Supported Living Aberdeen',
    'New contract following move from Highland to Aberdeen',
    'HCS-2024-003',
    'Framework Agreement Call-Off',
    'Contract Live',
    '2024-09-01',
    '2026-08-31',
    24,
    TRUE, 1, 48000.00,
    35.0, 7.0, 2,
    'active',
    TRUE,
    @manager_aberdeen_id
) ON DUPLICATE KEY UPDATE title=VALUES(title);

SET @contract4_id = LAST_INSERT_ID();
SET @contract4_id = IF(@contract4_id = 0, (SELECT id FROM contracts WHERE contract_number = 'HCS-2024-003' AND organisation_id = @org_id LIMIT 1), @contract4_id);

-- Contract 5: Bulk contract - Multiple people
INSERT INTO contracts (
    organisation_id, team_id, contract_type_id, local_authority_id,
    title, description, contract_number, procurement_route, tender_status,
    start_date, end_date, contract_duration_months,
    is_single_person, number_of_people, total_amount,
    daytime_hours, sleepover_hours, number_of_staff,
    status, fair_work_compliance, created_by
)
VALUES (
    @org_id, @nairn_team_id, @support_hours_id, @highland_la_id,
    'Nairn Area - Bulk Support Contract',
    'Bulk contract for 8 people requiring support hours in Nairn area',
    'HCS-2023-010',
    'Competitive Tender - Restricted',
    'Contract Live',
    '2023-09-01',
    '2025-08-31',
    24,
    FALSE, 8, 280000.00,
    200.0, 56.0, 12,
    'active',
    TRUE,
    @admin_user_id
) ON DUPLICATE KEY UPDATE title=VALUES(title);

SET @contract5_id = LAST_INSERT_ID();
SET @contract5_id = IF(@contract5_id = 0, (SELECT id FROM contracts WHERE contract_number = 'HCS-2023-010' AND organisation_id = @org_id LIMIT 1), @contract5_id);

-- Contract 6: Sarah Williams - Learning disabilities
INSERT INTO contracts (
    organisation_id, team_id, contract_type_id, local_authority_id,
    title, description, contract_number, procurement_route, tender_status,
    start_date, end_date, contract_duration_months,
    is_single_person, number_of_people, total_amount,
    daytime_hours, sleepover_hours, number_of_staff,
    status, fair_work_compliance, created_by
)
VALUES (
    @org_id, @aberdeenshire_team_id, @personal_care_id, @aberdeenshire_la_id,
    'Sarah Williams - Personal Care',
    'Personal care contract for learning disabilities support',
    'HCS-2024-005',
    'Direct Award',
    'Contract Live',
    '2024-01-15',
    '2025-01-14',
    12,
    TRUE, 1, 42000.00,
    30.0, 0, 2,
    'active',
    TRUE,
    @manager_aberdeen_id
) ON DUPLICATE KEY UPDATE title=VALUES(title);

SET @contract6_id = LAST_INSERT_ID();
SET @contract6_id = IF(@contract6_id = 0, (SELECT id FROM contracts WHERE contract_number = 'HCS-2024-005' AND organisation_id = @org_id LIMIT 1), @contract6_id);

-- Contract 7: Michael Taylor - Complex needs, expiring soon
INSERT INTO contracts (
    organisation_id, team_id, contract_type_id, local_authority_id,
    title, description, contract_number, procurement_route, tender_status,
    start_date, end_date, contract_duration_months,
    is_single_person, number_of_people, total_amount,
    daytime_hours, sleepover_hours, number_of_staff,
    status, fair_work_compliance, created_by
)
VALUES (
    @org_id, @inverness_team_id, @complex_support_id, @highland_la_id,
    'Michael Taylor - Complex Support',
    'Complex needs support contract expiring soon - retender pending',
    'HCS-2022-008',
    'Competitive Tender - Open',
    'Retender Pending',
    '2022-10-01',
    '2025-03-31',
    30,
    TRUE, 1, 55000.00,
    45.0, 7.0, 3,
    'active',
    TRUE,
    @admin_user_id
) ON DUPLICATE KEY UPDATE title=VALUES(title);

SET @contract7_id = LAST_INSERT_ID();
SET @contract7_id = IF(@contract7_id = 0, (SELECT id FROM contracts WHERE contract_number = 'HCS-2022-008' AND organisation_id = @org_id LIMIT 1), @contract7_id);

-- ============================================
-- CONTRACT-PEOPLE RELATIONSHIPS
-- ============================================

-- Link people to contracts via contract_people junction table
-- This is important for tracking people across contracts and local authorities

-- Contract 1: John Smith (ended contract)
INSERT INTO contract_people (contract_id, person_id, local_authority_id, start_date, end_date)
VALUES (@contract1_id, @person1_id, @highland_la_id, '2022-04-01', '2024-03-31')
ON DUPLICATE KEY UPDATE start_date=VALUES(start_date);

-- Contract 2: John Smith (current contract)
INSERT INTO contract_people (contract_id, person_id, local_authority_id, start_date, end_date)
VALUES (@contract2_id, @person1_id, @highland_la_id, '2024-04-01', '2026-03-31')
ON DUPLICATE KEY UPDATE start_date=VALUES(start_date);

-- Contract 3: Mary Johnson
INSERT INTO contract_people (contract_id, person_id, local_authority_id, start_date, end_date)
VALUES (@contract3_id, @person2_id, @highland_la_id, '2023-06-01', '2025-05-31')
ON DUPLICATE KEY UPDATE start_date=VALUES(start_date);

-- Contract 4: Robert Brown
INSERT INTO contract_people (contract_id, person_id, local_authority_id, start_date, end_date)
VALUES (@contract4_id, @person3_id, @aberdeen_la_id, '2024-09-01', '2026-08-31')
ON DUPLICATE KEY UPDATE start_date=VALUES(start_date);

-- Contract 5: Bulk contract - Link multiple people (using person 4 and 5 as examples)
-- Note: In a real scenario, you'd link all 8 people, but for seed data we'll link 2
INSERT INTO contract_people (contract_id, person_id, local_authority_id, start_date, end_date)
VALUES 
    (@contract5_id, @person4_id, @highland_la_id, '2023-09-01', '2025-08-31'),
    (@contract5_id, @person5_id, @highland_la_id, '2023-09-01', '2025-08-31')
ON DUPLICATE KEY UPDATE start_date=VALUES(start_date);

-- Contract 6: Sarah Williams
INSERT INTO contract_people (contract_id, person_id, local_authority_id, start_date, end_date)
VALUES (@contract6_id, @person4_id, @aberdeenshire_la_id, '2024-01-15', '2025-01-14')
ON DUPLICATE KEY UPDATE start_date=VALUES(start_date);

-- Contract 7: Michael Taylor
INSERT INTO contract_people (contract_id, person_id, local_authority_id, start_date, end_date)
VALUES (@contract7_id, @person5_id, @highland_la_id, '2022-10-01', '2025-03-31')
ON DUPLICATE KEY UPDATE start_date=VALUES(start_date);

-- ============================================
-- PAYMENTS
-- ============================================

SET @payment_method_tender_id = (SELECT id FROM payment_methods WHERE name = 'Tender' LIMIT 1);
SET @payment_method_sds_id = (SELECT id FROM payment_methods WHERE name = 'Self-Directed Support' LIMIT 1);

-- Payments for Contract 2 (John Smith - current)
INSERT INTO contract_payments (contract_id, payment_method_id, payment_date, amount, description)
VALUES
    (@contract2_id, @payment_method_tender_id, '2024-04-30', 2166.67, 'Monthly payment April 2024'),
    (@contract2_id, @payment_method_tender_id, '2024-05-31', 2166.67, 'Monthly payment May 2024'),
    (@contract2_id, @payment_method_tender_id, '2024-06-30', 2166.67, 'Monthly payment June 2024'),
    (@contract2_id, @payment_method_tender_id, '2024-07-31', 2166.67, 'Monthly payment July 2024'),
    (@contract2_id, @payment_method_tender_id, '2024-08-31', 2166.67, 'Monthly payment August 2024'),
    (@contract2_id, @payment_method_tender_id, '2024-09-30', 2166.67, 'Monthly payment September 2024'),
    (@contract2_id, @payment_method_tender_id, '2024-10-31', 2166.67, 'Monthly payment October 2024'),
    (@contract2_id, @payment_method_tender_id, '2024-11-30', 2166.67, 'Monthly payment November 2024'),
    (@contract2_id, @payment_method_tender_id, '2024-12-31', 2166.67, 'Monthly payment December 2024')
ON DUPLICATE KEY UPDATE amount=VALUES(amount);

-- Payments for Contract 3 (Mary Johnson)
INSERT INTO contract_payments (contract_id, payment_method_id, payment_date, amount, description)
VALUES
    (@contract3_id, @payment_method_sds_id, '2024-11-01', 1583.33, 'Monthly payment November 2024'),
    (@contract3_id, @payment_method_sds_id, '2024-10-01', 1583.33, 'Monthly payment October 2024'),
    (@contract3_id, @payment_method_sds_id, '2024-09-01', 1583.33, 'Monthly payment September 2024')
ON DUPLICATE KEY UPDATE amount=VALUES(amount);

-- Payments for Contract 4 (Robert Brown)
INSERT INTO contract_payments (contract_id, payment_method_id, payment_date, amount, description)
VALUES
    (@contract4_id, @payment_method_tender_id, '2024-09-30', 2000.00, 'Monthly payment September 2024'),
    (@contract4_id, @payment_method_tender_id, '2024-10-31', 2000.00, 'Monthly payment October 2024'),
    (@contract4_id, @payment_method_tender_id, '2024-11-30', 2000.00, 'Monthly payment November 2024')
ON DUPLICATE KEY UPDATE amount=VALUES(amount);

-- ============================================
-- TENDER APPLICATIONS
-- ============================================

-- Tender Application 1: Under review - New contract opportunity
INSERT INTO tender_applications (
    organisation_id, local_authority_id, procurement_route, contract_type_id,
    title, description, service_description,
    number_of_people, geographic_coverage,
    rates_json, total_contract_value, payment_terms,
    care_inspectorate_rating, fair_work_compliance, living_wage_commitment,
    staffing_levels, daytime_hours, sleepover_hours,
    status, tender_reference, submission_deadline, submitted_at, created_by
)
VALUES (
    @org_id, @highland_la_id, 'Competitive Tender - Open', @complex_support_id,
    'Highland Complex Needs Support - 2025',
    'Tender for complex needs support services in Highland region',
    'Providing comprehensive support for people with complex needs including personal care, support hours, and sleepover support. Our team has extensive experience in this area.',
    5,
    'Highland region - Inverness, Nairn, and surrounding areas',
    JSON_OBJECT(@complex_support_id, 32.00, @waking_hours_id, 28.50, @sleepover_id, 12.60),
    450000.00,
    'Monthly in arrears',
    'Very Good',
    TRUE,
    TRUE,
    8,
    140.0,
    35.0,
    'under_review',
    'HIG-2025-COMPLEX-001',
    '2025-01-15',
    '2024-12-20',
    @admin_user_id
) ON DUPLICATE KEY UPDATE title=VALUES(title);

-- Tender Application 2: Draft - Preparing for submission
INSERT INTO tender_applications (
    organisation_id, local_authority_id, procurement_route, contract_type_id,
    title, description, service_description,
    number_of_people, geographic_coverage,
    rates_json, total_contract_value,
    care_inspectorate_rating, fair_work_compliance, living_wage_commitment,
    status, tender_reference, submission_deadline, created_by
)
VALUES (
    @org_id, @aberdeen_la_id, 'Framework Agreement Call-Off', @support_hours_id,
    'Aberdeen Support Hours - Framework Call-Off',
    'Framework call-off for support hours in Aberdeen',
    'Support hours for daily living assistance and community participation',
    3,
    'Aberdeen City',
    JSON_OBJECT(@support_hours_id, 26.00),
    180000.00,
    'Very Good',
    TRUE,
    TRUE,
    'draft',
    'ABD-FW-2025-001',
    '2025-02-28',
    @manager_aberdeen_id
) ON DUPLICATE KEY UPDATE title=VALUES(title);

-- Tender Application 3: Submitted - Awaiting response
INSERT INTO tender_applications (
    organisation_id, local_authority_id, procurement_route, contract_type_id,
    title, description, service_description,
    number_of_people,
    rates_json, total_contract_value,
    care_inspectorate_rating, fair_work_compliance,
    status, tender_reference, submission_deadline, submitted_at, created_by
)
VALUES (
    @org_id, @aberdeenshire_la_id, 'Competitive Tender - Restricted', @personal_care_id,
    'Aberdeenshire Personal Care Services',
    'Tender for personal care services in Aberdeenshire',
    'Personal care services including washing, toileting, meal preparation',
    4,
    JSON_OBJECT(@personal_care_id, 30.00),
    320000.00,
    'Very Good',
    TRUE,
    'submitted',
    'ABD-2025-PC-002',
    '2024-12-10',
    '2024-12-05',
    @admin_user_id
) ON DUPLICATE KEY UPDATE title=VALUES(title);

-- ============================================
-- UPDATE SEATS USED
-- ============================================

UPDATE organisations 
SET seats_used = (
    SELECT COUNT(*) 
    FROM users 
    WHERE organisation_id = @org_id 
    AND email_verified = TRUE 
    AND is_active = TRUE
)
WHERE id = @org_id;

-- ============================================
-- SUMMARY
-- ============================================

-- Display summary
SELECT 
    'Test Data Summary' AS summary,
    (SELECT COUNT(*) FROM users WHERE organisation_id = @org_id) AS users_created,
    (SELECT COUNT(*) FROM teams WHERE organisation_id = @org_id) AS teams_created,
    (SELECT COUNT(*) FROM people WHERE organisation_id = @org_id) AS people_created,
    (SELECT COUNT(*) FROM contracts WHERE organisation_id = @org_id) AS contracts_created,
    (SELECT COUNT(*) FROM tender_applications WHERE organisation_id = @org_id) AS tender_applications_created,
    (SELECT COUNT(*) FROM contract_payments cp 
     JOIN contracts c ON cp.contract_id = c.id 
     WHERE c.organisation_id = @org_id) AS payments_created;

