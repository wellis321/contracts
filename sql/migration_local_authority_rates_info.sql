-- Migration: Add tables for local authority rates information and updates
-- This stores reference data, historical rates, and news/updates from local authorities

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

-- Insert historical Real Living Wage data
INSERT IGNORE INTO real_living_wage_history (effective_date, uk_rate, london_rate, scotland_rate, announced_date, source) VALUES
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
('2025-11-01', 13.45, 14.80, 13.45, '2025-11-01', 'Living Wage Foundation');

-- Insert historical Scottish Government mandated minimum rates
INSERT IGNORE INTO scotland_mandated_minimum_rates (effective_date, rate, applies_to, source, notes) VALUES
('2016-10-01', 8.25, 'all hours', 'Scottish Government', 'Real Living Wage implementation began'),
('2017-05-01', 8.45, 'all hours', 'Scottish Government', ''),
('2018-01-01', 8.45, 'all hours including sleepover', 'Scottish Government', 'Extended to sleepover hours'),
('2020-03-01', 9.30, 'all hours', 'Scottish Government', 'COVID-19 immediate uplift'),
('2020-11-01', 9.50, 'all hours', 'Scottish Government', ''),
('2021-01-01', 9.90, 'all hours', 'Scottish Government', ''),
('2022-09-01', 10.90, 'all hours', 'Scottish Government', ''),
('2023-04-01', 10.90, 'all hours', 'Scottish Government', ''),
('2024-04-01', 12.00, 'all hours', 'Scottish Government', ''),
('2025-04-01', 12.60, 'all hours', 'Scottish Government', 'Current rate for commissioned services');

-- Insert Homecare Association benchmark rates
INSERT IGNORE INTO homecare_association_rates (year_from, year_to, scotland_rate, england_rate, report_url) VALUES
('2018-04-01', '2019-03-31', 16.54, NULL, 'https://www.homecareassociation.org.uk/about-us/research-and-reports.html'),
('2023-04-01', '2024-03-31', 26.50, NULL, 'https://www.homecareassociation.org.uk/about-us/research-and-reports.html'),
('2024-04-01', '2025-03-31', 29.35, NULL, 'https://www.homecareassociation.org.uk/about-us/research-and-reports.html'),
('2025-04-01', '2026-03-31', 32.88, NULL, 'https://www.homecareassociation.org.uk/about-us/research-and-reports.html');
