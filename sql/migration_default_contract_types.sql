-- Migration: Add default/system contract types
-- These are standard rate types used across Scottish social care
-- Run this migration after the initial schema.sql has been executed

-- First, allow NULL organisation_id for system-wide default types (if not already done)
ALTER TABLE contract_types 
MODIFY COLUMN organisation_id INT NULL;

-- Add is_system_default column if it doesn't exist
ALTER TABLE contract_types 
ADD COLUMN IF NOT EXISTS is_system_default BOOLEAN DEFAULT FALSE AFTER organisation_id;

-- Add index if it doesn't exist
ALTER TABLE contract_types 
ADD INDEX IF NOT EXISTS idx_organisation_system (organisation_id, is_system_default);

-- Insert default contract types (system-wide, available to all organisations)
-- Using INSERT IGNORE to avoid errors if types already exist
INSERT IGNORE INTO contract_types (organisation_id, is_system_default, name, description, is_active) VALUES
(NULL, TRUE, 'Waking/Active Hours', 'Standard care hours where workers are actively providing support during waking hours', TRUE),
(NULL, TRUE, 'Waking Night Shifts', 'Overnight shifts where workers remain awake throughout the night to provide support', TRUE),
(NULL, TRUE, 'Sleepover Hours', 'Overnight shifts where workers stay overnight but can sleep, only intervening if needed. Must be paid at full hourly rate (£12.60/hour minimum from April 2025)', TRUE),
(NULL, TRUE, 'Support Hours', 'General assistance with daily activities and support tasks', TRUE),
(NULL, TRUE, 'Personal Care', 'Specific personal care tasks including washing, toileting, meal preparation, and personal hygiene', TRUE);
