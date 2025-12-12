-- Migration: Tender Opportunity Monitoring System
-- Allows organizations to set up automated monitoring for new tender opportunities

-- Tender Monitoring Preferences table
CREATE TABLE IF NOT EXISTS tender_monitoring_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT NOT NULL,
    user_id INT NOT NULL COMMENT 'User who set up the monitoring',
    
    -- Monitoring criteria
    keywords TEXT NULL COMMENT 'Comma-separated keywords to search for',
    local_authority_ids JSON NULL COMMENT 'Array of local authority IDs to monitor',
    contract_type_ids JSON NULL COMMENT 'Array of contract type IDs to monitor',
    cpv_codes JSON NULL COMMENT 'CPV codes to monitor (e.g., 85000000 for health/social care)',
    min_value DECIMAL(12, 2) NULL COMMENT 'Minimum contract value',
    max_value DECIMAL(12, 2) NULL COMMENT 'Maximum contract value',
    
    -- Notification settings
    notification_method VARCHAR(50) DEFAULT 'email' COMMENT 'email, in_app, both',
    email_address VARCHAR(255) NULL COMMENT 'Email to send notifications to',
    notify_immediately BOOLEAN DEFAULT TRUE COMMENT 'Notify as soon as opportunity found',
    notify_daily_summary BOOLEAN DEFAULT FALSE COMMENT 'Send daily summary email',
    notify_weekly_summary BOOLEAN DEFAULT FALSE COMMENT 'Send weekly summary email',
    
    -- Monitoring status
    is_active BOOLEAN DEFAULT TRUE,
    last_checked_at TIMESTAMP NULL COMMENT 'Last time we checked for new opportunities',
    opportunities_found INT DEFAULT 0 COMMENT 'Total opportunities found by this monitor',
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_organisation (organisation_id),
    INDEX idx_active (is_active, last_checked_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tender Notifications table
CREATE TABLE IF NOT EXISTS tender_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT NOT NULL,
    user_id INT NOT NULL COMMENT 'User to notify',
    monitoring_preference_id INT NULL COMMENT 'Which monitor found this',
    tender_opportunity_id INT NULL COMMENT 'Link to imported opportunity',
    
    -- Notification details
    notification_type VARCHAR(50) DEFAULT 'new_opportunity' COMMENT 'new_opportunity, deadline_reminder, etc.',
    title VARCHAR(255) NOT NULL,
    message TEXT,
    opportunity_data JSON NULL COMMENT 'Full opportunity data from API',
    
    -- Status
    is_read BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP NULL COMMENT 'When notification was sent',
    read_at TIMESTAMP NULL,
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (monitoring_preference_id) REFERENCES tender_monitoring_preferences(id) ON DELETE SET NULL,
    FOREIGN KEY (tender_opportunity_id) REFERENCES tender_opportunities(id) ON DELETE SET NULL,
    INDEX idx_organisation (organisation_id),
    INDEX idx_user (user_id, is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

