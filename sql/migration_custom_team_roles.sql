-- Migration: Add Custom Team Roles
-- Allows organizations to create their own team roles instead of using fixed ENUM values

-- Team Roles table - Organizations can create their own team roles
CREATE TABLE IF NOT EXISTS team_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    access_level ENUM('team', 'organisation') NOT NULL DEFAULT 'team',
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_org_role_name (organisation_id, name),
    INDEX idx_organisation (organisation_id),
    INDEX idx_active (is_active),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default roles for existing organizations
-- These will be created per organization when they first access teams
INSERT INTO team_roles (organisation_id, name, description, access_level, display_order)
SELECT 
    o.id,
    'Member',
    'Basic team membership with view access',
    'team',
    1
FROM organisations o
WHERE NOT EXISTS (
    SELECT 1 FROM team_roles tr WHERE tr.organisation_id = o.id AND tr.name = 'Member'
);

INSERT INTO team_roles (organisation_id, name, description, access_level, display_order)
SELECT 
    o.id,
    'Manager',
    'Can manage contracts assigned to their team and child teams',
    'team',
    2
FROM organisations o
WHERE NOT EXISTS (
    SELECT 1 FROM team_roles tr WHERE tr.organisation_id = o.id AND tr.name = 'Manager'
);

INSERT INTO team_roles (organisation_id, name, description, access_level, display_order)
SELECT 
    o.id,
    'Admin',
    'Can manage contracts assigned to their team and child teams',
    'team',
    3
FROM organisations o
WHERE NOT EXISTS (
    SELECT 1 FROM team_roles tr WHERE tr.organisation_id = o.id AND tr.name = 'Admin'
);

INSERT INTO team_roles (organisation_id, name, description, access_level, display_order)
SELECT 
    o.id,
    'Finance',
    'Can view and edit all contracts in the organisation',
    'organisation',
    4
FROM organisations o
WHERE NOT EXISTS (
    SELECT 1 FROM team_roles tr WHERE tr.organisation_id = o.id AND tr.name = 'Finance'
);

INSERT INTO team_roles (organisation_id, name, description, access_level, display_order)
SELECT 
    o.id,
    'Senior Manager',
    'Can view and edit all contracts in the organisation',
    'organisation',
    5
FROM organisations o
WHERE NOT EXISTS (
    SELECT 1 FROM team_roles tr WHERE tr.organisation_id = o.id AND tr.name = 'Senior Manager'
);

-- Update user_teams table to use team_role_id instead of ENUM
-- First, add the new column
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'user_teams' 
    AND COLUMN_NAME = 'team_role_id'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE user_teams ADD COLUMN team_role_id INT NULL AFTER role_in_team',
    'SELECT "Column team_role_id already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key for team_role_id
SET @fk_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'user_teams' 
    AND CONSTRAINT_NAME = 'fk_user_team_role'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE user_teams ADD FOREIGN KEY fk_user_team_role (team_role_id) REFERENCES team_roles(id) ON DELETE SET NULL',
    'SELECT "Foreign key fk_user_team_role already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Migrate existing role_in_team values to team_role_id
-- This maps the old ENUM values to the new team_role_id
UPDATE user_teams ut
INNER JOIN teams t ON ut.team_id = t.id
INNER JOIN team_roles tr ON tr.organisation_id = t.organisation_id 
    AND tr.name = CASE 
        WHEN ut.role_in_team = 'member' THEN 'Member'
        WHEN ut.role_in_team = 'manager' THEN 'Manager'
        WHEN ut.role_in_team = 'admin' THEN 'Admin'
        WHEN ut.role_in_team = 'finance' THEN 'Finance'
        WHEN ut.role_in_team = 'senior_manager' THEN 'Senior Manager'
        ELSE 'Member'
    END
SET ut.team_role_id = tr.id
WHERE ut.team_role_id IS NULL;

-- Add index for team_role_id
SET @idx_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'user_teams' 
    AND INDEX_NAME = 'idx_team_role'
);

SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE user_teams ADD INDEX idx_team_role (team_role_id)',
    'SELECT "Index idx_team_role already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

