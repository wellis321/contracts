-- Migration: Add Teams Hierarchy System
-- Supports: Custom team types and hierarchical structure
-- Allows team managers to only manage their team's contracts
-- Finance and senior managers can view/edit all contracts

-- Team Types table - Organizations can create their own team types
CREATE TABLE IF NOT EXISTS team_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_org_team_type (organisation_id, name),
    INDEX idx_organisation (organisation_id),
    INDEX idx_active (is_active),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Teams table with hierarchical structure
CREATE TABLE IF NOT EXISTS teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT NOT NULL,
    parent_team_id INT NULL,
    team_type_id INT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_team_id) REFERENCES teams(id) ON DELETE SET NULL,
    FOREIGN KEY (team_type_id) REFERENCES team_types(id) ON DELETE SET NULL,
    INDEX idx_organisation (organisation_id),
    INDEX idx_parent_team (parent_team_id),
    INDEX idx_team_type (team_type_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Teams junction table - Links users to teams
-- Users can be in multiple teams (e.g., finance in multiple areas)
CREATE TABLE IF NOT EXISTS user_teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    team_id INT NOT NULL,
    role_in_team ENUM('manager', 'member', 'finance', 'senior_manager', 'admin') NOT NULL DEFAULT 'member',
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_team (user_id, team_id),
    INDEX idx_user (user_id),
    INDEX idx_team (team_id),
    INDEX idx_role (role_in_team)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add team_id to contracts table (if it doesn't already exist)
-- Note: If you get "Duplicate column name" error, the column already exists and you can skip this section

-- Check and add column if it doesn't exist
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'contracts' 
    AND COLUMN_NAME = 'team_id'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE contracts ADD COLUMN team_id INT NULL AFTER organisation_id',
    'SELECT "Column team_id already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key if it doesn't exist
SET @fk_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'contracts' 
    AND CONSTRAINT_NAME = 'fk_contract_team'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE contracts ADD FOREIGN KEY fk_contract_team (team_id) REFERENCES teams(id) ON DELETE SET NULL',
    'SELECT "Foreign key fk_contract_team already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index if it doesn't exist
SET @idx_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'contracts' 
    AND INDEX_NAME = 'idx_team'
);

SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE contracts ADD INDEX idx_team (team_id)',
    'SELECT "Index idx_team already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add team_type_id to teams table (if it doesn't already exist)
-- This is needed if the teams table was created before team_type_id was added

-- Check and add team_type_id column if it doesn't exist
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'teams' 
    AND COLUMN_NAME = 'team_type_id'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE teams ADD COLUMN team_type_id INT NULL AFTER parent_team_id',
    'SELECT "Column team_type_id already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key for team_type_id if it doesn't exist
SET @fk_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'teams' 
    AND CONSTRAINT_NAME = 'fk_team_team_type'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE teams ADD FOREIGN KEY fk_team_team_type (team_type_id) REFERENCES team_types(id) ON DELETE SET NULL',
    'SELECT "Foreign key fk_team_team_type already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index for team_type_id if it doesn't exist
SET @idx_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'teams' 
    AND INDEX_NAME = 'idx_team_type'
);

SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE teams ADD INDEX idx_team_type (team_type_id)',
    'SELECT "Index idx_team_type already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add notes about team structure
-- Teams can be nested: Team → Area → Region
-- Head office teams are separate and can access all contracts
-- Team managers can only manage contracts assigned to their team (or child teams)
-- Finance and senior managers can access all contracts in their organisation

