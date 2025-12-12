-- Migration: Rate Warning Dismissals
-- Allows users to dismiss/acknowledge rate warnings that they've reviewed

CREATE TABLE IF NOT EXISTS rate_warning_dismissals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT NOT NULL,
    user_id INT NOT NULL COMMENT 'User who dismissed the warning',
    warning_type VARCHAR(50) NOT NULL COMMENT 'scotland_rate, rlw_rate, hca_rate',
    warning_key VARCHAR(255) NOT NULL COMMENT 'Unique identifier for this specific warning (e.g., rate_id + message hash)',
    dismissed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL COMMENT 'When to show warning again (e.g., after 30 days)',
    notes TEXT NULL COMMENT 'Optional notes about why dismissed',
    
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_organisation (organisation_id),
    INDEX idx_warning (warning_type, warning_key),
    INDEX idx_expires (expires_at),
    UNIQUE KEY unique_dismissal (organisation_id, warning_type, warning_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

