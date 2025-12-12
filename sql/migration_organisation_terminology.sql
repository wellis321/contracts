-- Migration: Add terminology preferences to organisations table
-- Allows organisations to customise how "people" are referred to throughout the site

ALTER TABLE organisations 
ADD COLUMN person_singular VARCHAR(100) DEFAULT 'person' AFTER seats_used,
ADD COLUMN person_plural VARCHAR(100) DEFAULT 'people' AFTER person_singular;

-- Update existing organisations to use defaults
UPDATE organisations 
SET person_singular = 'person', person_plural = 'people' 
WHERE person_singular IS NULL OR person_plural IS NULL;
