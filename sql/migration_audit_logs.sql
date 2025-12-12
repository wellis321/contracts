-- Migration: Comprehensive Audit Logging System
-- Tracks all changes across the application for compliance and accountability

-- Audit Logs table - Comprehensive change tracking
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT NOT NULL,
    user_id INT NULL COMMENT 'NULL if user was deleted',
    entity_type VARCHAR(100) NOT NULL COMMENT 'e.g., contract, rate, person, payment',
    entity_id INT NOT NULL COMMENT 'ID of the affected entity',
    action VARCHAR(50) NOT NULL COMMENT 'create, update, delete, approve, reject',
    field_name VARCHAR(100) NULL COMMENT 'Specific field changed (for updates)',
    old_value TEXT NULL COMMENT 'Previous value (for updates/deletes)',
    new_value TEXT NULL COMMENT 'New value (for creates/updates)',
    changes JSON NULL COMMENT 'Full change set for complex updates',
    ip_address VARCHAR(45) NULL COMMENT 'IPv4 or IPv6 address',
    user_agent TEXT NULL COMMENT 'Browser/client information',
    request_url TEXT NULL COMMENT 'URL where change was made',
    approval_required BOOLEAN DEFAULT FALSE COMMENT 'Whether approval was required',
    approval_status VARCHAR(50) NULL COMMENT 'pending, approved, rejected, not_required',
    approved_by INT NULL COMMENT 'User who approved (if applicable)',
    approved_at TIMESTAMP NULL COMMENT 'When approval was given',
    rejection_reason TEXT NULL COMMENT 'Reason for rejection (if applicable)',
    metadata JSON NULL COMMENT 'Additional context (session info, etc.)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_organisation (organisation_id),
    INDEX idx_user (user_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    INDEX idx_approval_status (approval_status),
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Approval Rules table - Configurable approval requirements
CREATE TABLE IF NOT EXISTS approval_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT NOT NULL,
    entity_type VARCHAR(100) NOT NULL COMMENT 'contract, rate, person, payment, etc.',
    action VARCHAR(50) NOT NULL COMMENT 'create, update, delete, or * for all',
    field_name VARCHAR(100) NULL COMMENT 'Specific field (NULL for all fields)',
    approval_type VARCHAR(50) NOT NULL DEFAULT 'self' COMMENT 'self, manager, role, custom',
    required_role_id INT NULL COMMENT 'Required role for approval (if approval_type = role)',
    required_role_name VARCHAR(100) NULL COMMENT 'Role name (for reference)',
    manager_level INT DEFAULT 1 COMMENT 'Manager level required (1 = direct manager, 2 = manager\'s manager, etc.)',
    custom_condition JSON NULL COMMENT 'Custom approval logic (future extensibility)',
    is_active BOOLEAN DEFAULT TRUE,
    priority INT DEFAULT 0 COMMENT 'Higher priority rules checked first',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (required_role_id) REFERENCES roles(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_organisation (organisation_id),
    INDEX idx_entity_action (entity_type, action),
    INDEX idx_active (is_active, priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Approval Requests table - Tracks pending approvals
CREATE TABLE IF NOT EXISTS approval_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT NOT NULL,
    audit_log_id INT NOT NULL COMMENT 'Link to the audit log entry',
    requested_by INT NOT NULL COMMENT 'User who made the change',
    approval_rule_id INT NULL COMMENT 'Rule that triggered this approval',
    approver_type VARCHAR(50) NOT NULL COMMENT 'manager, role, user',
    approver_id INT NULL COMMENT 'Specific user who should approve (if approver_type = user)',
    approver_role_id INT NULL COMMENT 'Role that should approve (if approver_type = role)',
    approver_role_name VARCHAR(100) NULL COMMENT 'Role name (for reference)',
    status VARCHAR(50) DEFAULT 'pending' COMMENT 'pending, approved, rejected, expired',
    approved_by INT NULL COMMENT 'User who approved/rejected',
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    expires_at TIMESTAMP NULL COMMENT 'When approval request expires',
    notification_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (audit_log_id) REFERENCES audit_logs(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approval_rule_id) REFERENCES approval_rules(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approver_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approver_role_id) REFERENCES roles(id) ON DELETE SET NULL,
    INDEX idx_organisation (organisation_id),
    INDEX idx_status (status),
    INDEX idx_approver (approver_type, approver_id, approver_role_id),
    INDEX idx_expires (expires_at),
    INDEX idx_audit_log (audit_log_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

