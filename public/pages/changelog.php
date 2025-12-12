<?php
/**
 * Changelog Page
 */
require_once dirname(__DIR__, 2) . '/config/config.php';

$pageTitle = 'Changelog';
include INCLUDES_PATH . '/header.php';
?>

<style>
.changelog-container {
    max-width: 1000px;
    margin: 0 auto;
    padding-left: 2rem;
    padding-right: 2rem;
}

.version-card {
    background: var(--bg-color);
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    padding: 2rem;
    margin-bottom: 2.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: box-shadow 0.2s;
}

.version-card:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.version-card.current {
    border-left: 4px solid var(--primary-color);
    background: linear-gradient(to right, rgba(37, 99, 235, 0.02), var(--bg-color));
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
}

.version-card.previous {
    border-left: 4px solid var(--text-light);
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
}

.version-card.initial {
    border-left: 4px solid var(--success-color);
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
}

.version-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--border-color);
}

.version-title {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.version-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.version-badge.current {
    background: var(--primary-color);
    color: white;
}

.version-badge.previous {
    background: var(--text-light);
    color: white;
}

.version-badge.initial {
    background: var(--success-color);
    color: white;
}

.version-badge.upcoming {
    background: var(--warning-color);
    color: white;
}

.version-date {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-light);
    font-size: 0.9rem;
}

.version-section {
    margin-top: 1.5rem;
}

.version-section:first-of-type {
    margin-top: 0;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--text-color);
}

.section-title i {
    color: var(--primary-color);
    font-size: 1rem;
}

.version-list {
    list-style: none;
    padding-left: 0;
    margin-left: 0;
}

.version-list li {
    padding: 0.75rem 0;
    padding-left: 2rem;
    position: relative;
    border-bottom: 1px solid var(--bg-light);
    line-height: 1.7;
}

.version-list li:last-child {
    border-bottom: none;
}

.version-list li::before {
    content: "•";
    position: absolute;
    left: 0.5rem;
    color: var(--primary-color);
    font-weight: bold;
    font-size: 1.2rem;
    line-height: 1.5;
}

.version-list li strong {
    color: var(--text-color);
    font-weight: 600;
}

.upcoming-features {
    background: linear-gradient(to right, rgba(245, 158, 11, 0.05), var(--bg-color));
    border: 1px solid var(--border-color);
    border-left: 4px solid var(--warning-color);
    border-radius: 0.5rem;
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
    padding: 2rem;
    margin-top: 2rem;
}

.upcoming-intro {
    color: var(--text-light);
    margin-bottom: 1rem;
    font-size: 0.95rem;
}

.footer-note {
    margin-top: 3rem;
    padding-top: 1.5rem;
    border-top: 2px solid var(--border-color);
    color: var(--text-light);
    font-size: 0.9rem;
    text-align: center;
}

.footer-note a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
}

.footer-note a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .changelog-container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .version-card {
        padding: 1.5rem;
    }
    
    .version-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .version-list li {
        padding-left: 1.5rem;
    }
}
</style>

<div class="card">
    <div class="card-header">
        <h1>Changelog</h1>
        <p style="color: var(--text-light); margin-top: 0.5rem;">Version history and feature updates</p>
    </div>
    
    <div class="changelog-container">
        <!-- Version 1.5.0 - Current -->
        <div class="version-card current">
            <div class="version-header">
                <div class="version-title">
                    <span class="version-badge current">
                        <i class="fa-solid fa-star"></i>
                        Current Release
                    </span>
                    <h2 style="margin: 0; font-size: 1.5rem;">Version 1.5.0</h2>
                </div>
                <div class="version-date">
                    <i class="fa-solid fa-calendar"></i>
                    <span><?php echo date('F Y'); ?></span>
                </div>
            </div>
            
            <div class="version-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-sparkles"></i>
                    New Features
                </h3>
                <ul class="version-list">
                    <li><strong>Automated Tender Monitoring:</strong> Set up automated monitoring for new tender opportunities from Public Contracts Scotland API with instant notifications</li>
                    <li><strong>Tender Opportunity Tracking:</strong> Track available tender opportunities with filtering, search, and quick actions to create applications</li>
                    <li><strong>URL Import for Tenders:</strong> Import tender opportunities directly from Public Contracts Scotland URLs with automatic data extraction</li>
                    <li><strong>Comprehensive Audit Logging:</strong> Complete audit trail of all changes across the system with user, IP, timestamp, and change details</li>
                    <li><strong>Approval Workflow System:</strong> Configurable approval rules requiring manager or role-based approval for sensitive changes</li>
                    <li><strong>Enhanced Table Design:</strong> Consistent clickable table rows across the site with improved sorting, filtering, and visual indicators</li>
                    <li><strong>Improved Reports:</strong> Enhanced reports page with collapsible sections, better date handling, and new visualizations</li>
                    <li><strong>Local Authority Detail Pages:</strong> Comprehensive local authority pages showing contracts, rates, updates, and tender applications</li>
                </ul>
            </div>
            
            <div class="version-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-arrow-up"></i>
                    Improvements
                </h3>
                <ul class="version-list">
                    <li>Enhanced form field visibility with thicker borders and better visual definition</li>
                    <li>Improved contract and person deduplication logic to prevent duplicate entries</li>
                    <li>Better status formatting throughout the system (e.g., "under_review" → "Under Review")</li>
                    <li>Added contract type abbreviations with tooltips and legends for better table readability</li>
                    <li>Improved scroll position preservation when sorting tables</li>
                    <li>Enhanced local authority issue detection with specific contract details and links</li>
                    <li>Better separation between sections on detail pages with visual borders and spacing</li>
                    <li>Added diagnostic information to tender monitoring for easier troubleshooting</li>
                </ul>
            </div>
        </div>
        
        <!-- Version 1.4.0 -->
        <div class="version-card previous">
            <div class="version-header">
                <div class="version-title">
                    <span class="version-badge previous">
                        <i class="fa-solid fa-clock"></i>
                        Previous Release
                    </span>
                    <h2 style="margin: 0; font-size: 1.5rem;">Version 1.4.0</h2>
                </div>
                <div class="version-date">
                    <i class="fa-solid fa-calendar"></i>
                    <span><?php echo date('F Y', strtotime('-1 month')); ?></span>
                </div>
            </div>
            
            <div class="version-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-sparkles"></i>
                    New Features
                </h3>
                <ul class="version-list">
                    <li><strong>Tender Application System:</strong> Create and manage tender applications with automatic pre-filling from organisation profile and existing contracts</li>
                    <li><strong>Organisation Profile:</strong> Comprehensive profile system storing legal, financial, quality, and service information for reuse in tender applications</li>
                    <li><strong>Pre-filled Tender Forms:</strong> Automatically populate tender applications with organisation details, rates, previous contracts, and compliance information</li>
                    <li><strong>Tender Application Tracking:</strong> Track all tender applications with status filtering, deadline alerts, and submission history</li>
                    <li><strong>Contract Type Deduplication:</strong> Fixed duplicate contract types in dropdowns by prioritizing organisation-specific types over system defaults</li>
                </ul>
            </div>
            
            <div class="version-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-arrow-up"></i>
                    Improvements
                </h3>
                <ul class="version-list">
                    <li>Enhanced organisation settings page with comprehensive profile form</li>
                    <li>Improved data consistency by reusing organisation information across tender applications</li>
                    <li>Better user experience with pre-filled forms reducing manual data entry</li>
                    <li>Added tender applications link to Contracts navigation menu</li>
                </ul>
            </div>
        </div>
        
        <!-- Version 1.3.0 -->
        <div class="version-card previous">
            <div class="version-header">
                <div class="version-title">
                    <span class="version-badge previous">
                        <i class="fa-solid fa-clock"></i>
                        Previous Release
                    </span>
                    <h2 style="margin: 0; font-size: 1.5rem;">Version 1.3.0</h2>
                </div>
                <div class="version-date">
                    <i class="fa-solid fa-calendar"></i>
                    <span><?php echo date('F Y', strtotime('-1 month')); ?></span>
                </div>
            </div>
            
            <div class="version-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-sparkles"></i>
                    New Features
                </h3>
                <ul class="version-list">
                    <li><strong>Teams Hierarchy System:</strong> Organisations can now create hierarchical team structures (Teams → Areas → Regions) with custom team types</li>
                    <li><strong>Custom Team Types:</strong> Organisations can define their own team types (e.g., Department, Division, Unit) instead of using predefined types</li>
                    <li><strong>Team-Based Access Control:</strong> Team managers can only manage contracts assigned to their team, while finance and senior managers can access all contracts</li>
                    <li><strong>Team Import:</strong> Bulk import team structure from CSV or JSON files, perfect for importing from Entra ID or other systems</li>
                    <li><strong>User Team Assignments:</strong> Assign users to teams with specific roles (manager, member, finance, senior_manager, admin)</li>
                    <li><strong>Contract Team Assignment:</strong> Assign contracts to teams for better organisation and access control</li>
                    <li><strong>Payment Frequency Tracking:</strong> Added payment frequency field to track weekly, monthly, quarterly, and other payment schedules</li>
                </ul>
            </div>
            
            <div class="version-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-arrow-up"></i>
                    Improvements
                </h3>
                <ul class="version-list">
                    <li>Enhanced contract filtering based on team access permissions</li>
                    <li>Improved dashboard to show only contracts accessible to the user's teams</li>
                    <li>Better organisation of contract management with team-based views</li>
                    <li>Updated documentation with comprehensive teams management guide</li>
                </ul>
            </div>
        </div>
        
        <!-- Version 1.2.0 -->
        <div class="version-card previous">
            <div class="version-header">
                <div class="version-title">
                    <span class="version-badge previous">
                        <i class="fa-solid fa-clock"></i>
                        Previous Release
                    </span>
                    <h2 style="margin: 0; font-size: 1.5rem;">Version 1.2.0</h2>
                </div>
                <div class="version-date">
                    <i class="fa-solid fa-calendar"></i>
                    <span><?php echo date('F Y', strtotime('-1 month')); ?></span>
                </div>
            </div>
            
            <div class="version-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-sparkles"></i>
                    New Features
                </h3>
                <ul class="version-list">
                    <li><strong>Terminology Preferences:</strong> Organisation administrators can now customise how people are referred to throughout the system (e.g., "person we support", "service user", "client")</li>
                    <li><strong>Enhanced Navigation:</strong> Reorganised navigation menu with "Learn" and "News & Updates" sections for better content organisation</li>
                    <li><strong>Legal Pages:</strong> Added Privacy Policy, Terms of Service, and Cookie Policy pages</li>
                    <li><strong>Cookie Consent:</strong> Implemented cookie consent banner with user preferences</li>
                    <li><strong>Improved Mobile Experience:</strong> Enhanced responsive design with better touch targets and mobile-optimised layouts</li>
                </ul>
            </div>
            
            <div class="version-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-arrow-up"></i>
                    Improvements
                </h3>
                <ul class="version-list">
                    <li>Enhanced documentation with comprehensive system information</li>
                    <li>Expanded how-to guides with detailed step-by-step instructions</li>
                    <li>Updated FAQ with questions about all system features</li>
                    <li>Improved card styling and hover effects</li>
                    <li>Better spacing and typography throughout the site</li>
                </ul>
            </div>
        </div>
        
        <!-- Version 1.1.0 -->
        <div class="version-card previous">
            <div class="version-header">
                <div class="version-title">
                    <span class="version-badge previous">
                        <i class="fa-solid fa-clock"></i>
                        Previous Release
                    </span>
                    <h2 style="margin: 0; font-size: 1.5rem;">Version 1.1.0</h2>
                </div>
                <div class="version-date">
                    <i class="fa-solid fa-calendar"></i>
                    <span><?php echo date('F Y', strtotime('-1 month')); ?></span>
                </div>
            </div>
            
            <div class="version-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-sparkles"></i>
                    New Features
                </h3>
                <ul class="version-list">
        <p><strong>Release Date:</strong> <?php echo date('F Y', strtotime('-1 month')); ?></p>
        
        <h3>New Features</h3>
        <ul>
            <li><strong>Glossary System:</strong> Comprehensive glossary of social care and contract terms with search functionality</li>
            <li><strong>Glossary Suggestions:</strong> Users can suggest new terms for the glossary</li>
            <li><strong>Glossary Management:</strong> Super administrators can review, approve, reject, and manage glossary terms</li>
            <li><strong>Email Notifications:</strong> Email notifications for glossary suggestion approvals and rejections</li>
            <li><strong>Contracts Guide:</strong> Comprehensive guide to social care contracts in Scotland with sticky sidebar navigation</li>
            <li><strong>Local Authority Rates Information:</strong> Public-facing page displaying rate information and local authority positions</li>
            <li><strong>Workflow Dashboard:</strong> Visual overview of all contracts organised by tender status</li>
        </ul>
        
                </ul>
            </div>
            
            <div class="version-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-users"></i>
                    Person Tracking Enhancements
                </h3>
                <ul class="version-list">
            <li>Enhanced person detail pages with complete contract and payment history</li>
            <li>Cross-authority tracking for people moving between local authorities</li>
            <li>Improved identifier management (CHI, SWIS, NI, Organisation-specific)</li>
            <li>Payment history filtering by date range</li>
        </ul>
        
                </ul>
            </div>
            
            <div class="version-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-file-contract"></i>
                    Contract Management Improvements
                </h3>
                <ul class="version-list">
            <li>Procurement route tracking (Competitive Tender, Framework Agreement, Direct Award, etc.)</li>
            <li>Tender status workflow tracking (Pre-tender, Tender Submitted, Awarded, etc.)</li>
            <li>Enhanced contract forms with better field organisation</li>
            <li>Improved contract viewing with all relevant information displayed</li>
        </ul>
        
                </ul>
            </div>
            
            <div class="version-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-paint-brush"></i>
                    UI/UX Improvements
                </h3>
                <ul class="version-list">
            <li>Redesigned home page with more informative cards and sections</li>
            <li>Enhanced "How Social Care Contracts Work in Scotland" section with better layout</li>
            <li>Improved card styling with better visual separation</li>
            <li>Grouped quick action buttons by colour for better organisation</li>
            <li>Removed card hover movement for better user experience</li>
            <li>Better icon spacing and consistency throughout</li>
        </ul>
        
                </ul>
            </div>
        </div>
        
        <!-- Version 1.0.0 - Initial -->
        <div class="version-card initial">
            <div class="version-header">
                <div class="version-title">
                    <span class="version-badge initial">
                        <i class="fa-solid fa-rocket"></i>
                        Initial Release
                    </span>
                    <h2 style="margin: 0; font-size: 1.5rem;">Version 1.0.0</h2>
                </div>
                <div class="version-date">
                    <i class="fa-solid fa-calendar"></i>
                    <span><?php echo date('F Y', strtotime('-2 months')); ?></span>
                </div>
            </div>
            
            <div class="version-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-cube"></i>
                    Core Features
                </h3>
                <ul class="version-list">
            <li>Multi-organisation support with domain-based registration</li>
            <li>Automatic organisation detection from email domain</li>
            <li>Email verification for new accounts</li>
            <li>Strong password requirements (12+ characters with complexity)</li>
            <li>Role-based access control (superadmin, organisation admin, staff)</li>
            <li>Contract management for single person and bulk contracts</li>
            <li>Customisable contract types per organisation</li>
            <li>System default contract types</li>
            <li>Rate management with historical tracking</li>
            <li>Payment method and frequency tracking</li>
            <li>Reporting on payments and rate changes</li>
            <li>Support for all Scottish Local Authorities</li>
            <li>Person tracking with multiple identifier support</li>
            <li>Responsive design for mobile and desktop</li>
        </ul>
        
                </ul>
            </div>
            
            <div class="version-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-shield-halved"></i>
                    Security Features
                </h3>
                <ul class="version-list">
            <li>Password hashing using bcrypt</li>
            <li>CSRF protection on all forms</li>
            <li>Organisation data isolation</li>
            <li>SQL injection prevention with prepared statements</li>
            <li>Secure session management</li>
            <li>Email verification tokens with expiry</li>
        </ul>
        
                </ul>
            </div>
            
            <div class="version-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-code"></i>
                    Technical Implementation
                </h3>
                <ul class="version-list">
            <li>Dynamic base URL calculation for flexible deployment</li>
            <li>Global helper functions for URL generation</li>
            <li>Database migrations for schema updates</li>
            <li>Model-based data access layer</li>
            <li>Autoloading for classes and models</li>
                    <li>Environment-based configuration</li>
                </ul>
            </div>
        </div>
        
        <!-- Upcoming Features -->
        <div class="upcoming-features">
            <div class="version-header">
                <div class="version-title">
                    <span class="version-badge upcoming">
                        <i class="fa-solid fa-hourglass-half"></i>
                        Coming Soon
                    </span>
                    <h2 style="margin: 0; font-size: 1.5rem;">Upcoming Features</h2>
                </div>
            </div>
            
            <p class="upcoming-intro">We're continuously working to improve the system. Planned features include:</p>
            
            <ul class="version-list">
                <li>Microsoft Graph API integration for Teams notifications</li>
                <li>Browser extension for real-time tender alerts</li>
                <li>Desktop application for tender monitoring</li>
                <li>Report export functionality (CSV, PDF)</li>
                <li>Advanced filtering and search capabilities</li>
                <li>Contract expiry notifications</li>
                <li>Dashboard analytics and visualisations</li>
                <li>Bulk import/export functionality</li>
                <li>API access for integrations</li>
                <li>Enhanced reporting with custom date ranges and filters</li>
            </ul>
        </div>
        
        <p class="footer-note">
            For the latest updates and announcements, check the <a href="<?php echo htmlspecialchars(url('pages/updates.php')); ?>">Updates</a> page.
        </p>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
