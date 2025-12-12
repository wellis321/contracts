-- Migration: Add glossary_terms table for dynamic glossary management
-- Allows terms to be managed in the database instead of hardcoded

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
