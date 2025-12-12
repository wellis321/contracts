<?php
/**
 * Documentation Page
 */
require_once dirname(__DIR__, 2) . '/config/config.php';

$pageTitle = 'Documentation';
include INCLUDES_PATH . '/header.php';
?>

<style>
.docs-sidebar {
    width: 250px;
    flex-shrink: 0;
    position: sticky;
    top: 100px;
    max-height: calc(100vh - 120px);
    overflow-y: auto;
    background: var(--bg-light);
    padding: 1.5rem;
    border-radius: 0.5rem;
    border: 1px solid var(--border-color);
}

.docs-sidebar h3 {
    margin-top: 0;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.docs-sidebar nav {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.docs-sidebar-link {
    text-decoration: none;
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    transition: all 0.2s;
    font-size: 0.9rem;
    color: var(--text-color);
    display: block;
}

.docs-sidebar-link:hover {
    background: rgba(37, 99, 235, 0.1);
    color: var(--primary-color);
}

.docs-sidebar-link.active {
    background: var(--primary-color);
    color: #ffffff !important;
}

.docs-content-wrapper {
    display: flex;
    gap: 2rem;
    align-items: start;
}

.docs-main-content {
    flex: 1;
    min-width: 0;
}

.content-container ul,
.content-container ol {
    list-style: disc;
    margin-left: 1.5rem;
    margin-bottom: 1rem;
    line-height: 1.8;
    padding-left: 0.5rem;
}

.content-container ol {
    list-style: decimal;
}

.content-container ul ul,
.content-container ol ul {
    list-style: circle;
    margin-left: 1.5rem;
    margin-top: 0.5rem;
}

.content-container li {
    margin-bottom: 0.5rem;
}

.content-container h2 {
    scroll-margin-top: 100px;
    padding-top: 1rem;
}

.content-container h3 {
    scroll-margin-top: 100px;
    padding-top: 0.5rem;
}

@media (max-width: 1024px) {
    .docs-sidebar {
        display: none;
    }
    .docs-content-wrapper {
        display: block;
    }
}
</style>

<div class="card">
    <div class="card-header">
        <h1>Documentation</h1>
        <p style="color: var(--text-light); margin-top: 0.5rem;">Complete guide to using the Social Care Contracts Management system</p>
    </div>
    
    <div class="docs-content-wrapper">
        <!-- Sticky Sidebar Navigation -->
        <aside class="docs-sidebar">
            <h3>Contents</h3>
            <nav>
                <a href="#getting-started" class="docs-sidebar-link">Getting Started</a>
                <a href="#contract-management" class="docs-sidebar-link">Contract Management</a>
                <a href="#person-tracking" class="docs-sidebar-link">Person Tracking</a>
                <a href="#rate-management" class="docs-sidebar-link">Rate Management</a>
                <a href="#payment-tracking" class="docs-sidebar-link">Payment Tracking</a>
                <a href="#reports-analytics" class="docs-sidebar-link">Reports & Analytics</a>
                <a href="#workflow-dashboard" class="docs-sidebar-link">Workflow Dashboard</a>
                <a href="#tender-applications" class="docs-sidebar-link">Tender Applications</a>
                <a href="#tender-monitoring" class="docs-sidebar-link">Tender Monitoring</a>
                <a href="#audit-logging" class="docs-sidebar-link">Audit Logging</a>
                <a href="#teams-management" class="docs-sidebar-link">Teams Management</a>
                <a href="#resources-guides" class="docs-sidebar-link">Resources & Guides</a>
                <a href="#user-roles" class="docs-sidebar-link">User Roles & Permissions</a>
                <a href="#organisation-settings" class="docs-sidebar-link">Organisation Settings</a>
                <a href="#security-privacy" class="docs-sidebar-link">Security & Privacy</a>
                <a href="#mobile-access" class="docs-sidebar-link">Mobile Access</a>
                <a href="#getting-help" class="docs-sidebar-link">Getting Help</a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <div class="docs-main-content">
            <div class="content-container" style="max-width: 900px; margin: 0 auto; padding-left: 2rem; padding-right: 2rem;">
        <h2 id="getting-started">Getting Started</h2>
        <p>Welcome to the Social Care Contracts Management Application. This system helps organisations manage social care contracts, track rates, monitor payments, and maintain comprehensive records for people receiving support across Scottish local authorities.</p>
        
        <h3>Registration & Account Setup</h3>
        <p>To register for an account:</p>
        <ul>
            <li>You'll need an email address that matches your organisation's domain</li>
            <li>The system automatically detects your organisation based on your email domain</li>
            <li>You'll receive an email verification link to activate your account</li>
            <li>Passwords must be at least 12 characters with uppercase, lowercase, numbers, and special characters</li>
            <li>Once verified, you can log in and access your organisation's data</li>
        </ul>
        
        <h3>Terminology Preferences</h3>
        <p>Organisation administrators can customise how people are referred to throughout the system. This allows your organisation to use terminology that aligns with your values (e.g., "person we support", "service user", "client", "patient"). These preferences apply site-wide once set.</p>
        
        <h2 id="contract-management">Contract Management</h2>
        <p>Contracts are the core of the system. They represent agreements between your organisation and local authorities for providing social care services.</p>
        
        <h3>Creating Contracts</h3>
        <p>Contracts can be created for:</p>
        <ul>
            <li><strong>Single Person Contracts:</strong> For supporting one individual, with optional linking to a person record</li>
            <li><strong>Bulk Contracts:</strong> For supporting multiple people, with specifications for staff numbers and hours</li>
        </ul>
        <p>Each contract includes:</p>
        <ul>
            <li>Contract type (e.g., Sleepovers, Support, Waking Hours)</li>
            <li>Local authority</li>
            <li>Procurement route (Competitive Tender, Framework Agreement, Direct Award, etc.)</li>
            <li>Tender status (Pre-tender, Tender Submitted, Awarded, etc.)</li>
            <li>Start and end dates</li>
            <li>Payment details (method, frequency, amounts)</li>
        </ul>
        
        <h3>Contract Types</h3>
        <p>Each organisation can define their own contract types. Organisation administrators can:</p>
        <ul>
            <li>Create custom contract types specific to their organisation</li>
            <li>Use system default types (if available)</li>
            <li>Manage active/inactive status of contract types</li>
        </ul>
        
        <h3>Procurement Routes & Tender Status</h3>
        <p>The system tracks the procurement process through:</p>
        <ul>
            <li><strong>Procurement Routes:</strong> How the contract was awarded (Competitive Tender, Framework Agreement, Direct Award/SDS, Spot Purchase, Block Contract)</li>
            <li><strong>Tender Status:</strong> Current stage in the tender lifecycle (Pre-tender, Tender Submitted, Under Evaluation, Awarded, etc.)</li>
            <li><strong>Workflow Dashboard:</strong> Visual overview of all contracts and their current status</li>
        </ul>
        
        <h2 id="person-tracking">Person Tracking</h2>
        <p>The system allows you to track individuals across multiple contracts and local authorities, maintaining continuity of care records.</p>
        
        <h3>Person Records</h3>
        <p>Each person record can include:</p>
        <ul>
            <li>Basic information (name, date of birth)</li>
            <li>Multiple identifiers (CHI number, SWIS number, National Insurance number, Organisation-specific identifier)</li>
            <li>One primary identifier for unique identification</li>
            <li>Contract history across all local authorities</li>
            <li>Payment history over time</li>
        </ul>
        
        <h3>Cross-Authority Tracking</h3>
        <p>When a person moves between local authorities, the system maintains their complete history, allowing you to:</p>
        <ul>
            <li>View all contracts associated with a person</li>
            <li>Track payments across different authorities</li>
            <li>Generate reports showing continuity of care</li>
        </ul>
        
        <h2 id="rate-management">Rate Management</h2>
        <p>Rates are central to contract management and financial planning.</p>
        
        <h3>Setting Rates</h3>
        <p>Rates are set per contract type and local authority combination:</p>
        <ul>
            <li>Each rate has an effective date</li>
            <li>Previous rates are automatically archived</li>
            <li>Rate history is maintained for historical analysis</li>
            <li>Current rates are clearly marked</li>
        </ul>
        
        <h3>Reference Rates</h3>
        <p>The system provides access to:</p>
        <ul>
            <li>Real Living Wage history</li>
            <li>Scotland Mandated Minimum Rates</li>
            <li>Homecare Association recommended rates</li>
            <li>Local authority rate updates and positions</li>
        </ul>
        
        <h3>Rate Monitoring</h3>
        <p>Administrators can monitor and validate reference rates to ensure accuracy and currency:</p>
        <ul>
            <li><strong>Automatic Validation:</strong> The system automatically checks if rates are current, valid, and not outdated</li>
            <li><strong>Status Indicators:</strong> Visual indicators show which rates need attention (green = current, yellow = needs review, red = missing/critical)</li>
            <li><strong>Alerts:</strong> Dashboard alerts notify administrators when rates need updating</li>
            <li><strong>Monitoring Dashboard:</strong> Access the Rate Monitoring page from the Admin menu to view detailed status of all reference rates</li>
            <li><strong>Currency Checks:</strong> The system warns when rates are more than 6 months old (Scotland rates) or 1 year old (Real Living Wage)</li>
            <li><strong>Missing Rate Detection:</strong> Alerts when critical rates are missing from the database</li>
        </ul>
        <p>To access rate monitoring, navigate to <strong>Admin → Rate Monitoring</strong> (available to organisation administrators and super administrators).</p>
        
        <h2 id="payment-tracking">Payment Tracking</h2>
        <p>Track payments associated with contracts:</p>
        <ul>
            <li>Payment methods (BACS, Cheque, Direct Debit, etc.)</li>
            <li>Payment frequency (Weekly, Monthly, Quarterly, etc.)</li>
            <li>Payment amounts and dates</li>
            <li>Payment history per person or contract</li>
        </ul>
        
        <h2 id="reports-analytics">Reports & Analytics</h2>
        <p>The Reports page provides comprehensive insights into your contracts, payments, and trends. Navigate to <strong>Contracts → Reports</strong> to access this feature.</p>
        
        <h3>What Information is Included?</h3>
        <p>The reports page displays the following information for any selected date range:</p>
        
        <h4>Summary Cards</h4>
        <ul>
            <li><strong>Active Contracts:</strong> Number of contracts active during the selected period, with comparison to the previous equivalent period</li>
            <li><strong>Total Contract Value:</strong> Sum of all contract values (total_amount field), showing whether you're gaining or losing value compared to the previous period</li>
            <li><strong>Total Payments Received:</strong> Actual payments received during the period</li>
            <li><strong>New Contracts:</strong> Contracts that started during this period, with trend comparison</li>
            <li><strong>Contracts Ending:</strong> Contracts ending during this period, helping identify renewal needs</li>
        </ul>
        
        <h4>Contracts by Local Authority</h4>
        <ul>
            <li>Breakdown showing number of contracts and total value per local authority</li>
            <li>Status indicators (✓ OK or ⚠️ Issues) to quickly identify potential problems</li>
            <li>Sorted by total value (highest first) for easy prioritization</li>
        </ul>
        
        <h4>Issue Detection</h4>
        <p>The system automatically identifies potential problems:</p>
        <ul>
            <li><strong>Contracts Ending Soon:</strong> Contracts expiring within the next 3 months (requires attention for renewals)</li>
            <li><strong>Inactive Contracts:</strong> Contracts marked as inactive (may indicate problems or completed contracts)</li>
        </ul>
        <p>These issues are flagged in the Local Authority breakdown and shown in a dedicated "Local Authority Issues Detected" alert section.</p>
        
        <h4>Contract Activity</h4>
        <ul>
            <li><strong>New Contracts Started:</strong> Detailed list of contracts that began during the selected period, including contract title, number, local authority, start date, and value</li>
            <li><strong>Contracts Ending:</strong> List of contracts ending during the period, helping you plan for renewals and identify potential revenue loss</li>
        </ul>
        
        <h4>Payment Information</h4>
        <ul>
            <li><strong>Payments by Method:</strong> Breakdown of payments by type (Tender, Self-Directed Support, Admin Costs, etc.) with total amounts and percentages</li>
            <li><strong>Recent Payments:</strong> Detailed list of individual payments received during the period (up to 20 most recent)</li>
        </ul>
        
        <h4>Period Comparison</h4>
        <p>All metrics include automatic comparisons with the previous equivalent period, showing:</p>
        <ul>
            <li>Whether contract count is increasing or decreasing</li>
            <li>Whether total contract value is growing or shrinking (with percentage change)</li>
            <li>Trends in new contract acquisition</li>
            <li>Trends in contract endings</li>
        </ul>
        <p>This helps you understand if your organisation is growing, maintaining, or losing contracts and value.</p>
        
        <h3>How to Use Reports</h3>
        <ol>
            <li>Navigate to <strong>Contracts → Reports</strong></li>
            <li>Select your desired date range using the Start Date and End Date fields</li>
            <li>Click <strong>Generate Report</strong></li>
            <li>Review the summary cards for quick insights</li>
            <li>Check the Local Authority breakdown to identify areas needing attention</li>
            <li>Review contract activity to see new contracts and those ending</li>
            <li>Examine payment information to understand cash flow</li>
        </ol>
        
        <h3>Tips for Effective Reporting</h3>
        <ul>
            <li>Select date ranges that include your active contracts to see the most relevant data</li>
            <li>The report automatically includes contracts that overlap with your selected period (not just those starting/ending in the period)</li>
            <li>Use the period comparison features to track growth trends over time</li>
            <li>Pay attention to the issue detection alerts to proactively address potential problems</li>
            <li>Review contracts ending soon to plan for renewals and avoid revenue gaps</li>
        </ul>
        
        <p>Generate comprehensive reports on:
        <ul>
            <li>Contracts by status, type, or local authority</li>
            <li>Payments over specified date ranges</li>
            <li>Rate changes and trends</li>
            <li>Person payment history</li>
            <li>Contract expiry monitoring</li>
        </ul>
        
        <h2 id="workflow-dashboard">Workflow Dashboard</h2>
        <p>The workflow dashboard provides a visual overview of:</p>
        <ul>
            <li>All contracts and their current tender status</li>
            <li>Procurement routes</li>
            <li>Upcoming contract expiries</li>
            <li>Contracts requiring attention</li>
        </ul>
        
        <h2 id="tender-applications">Tender Applications</h2>
        <p>The Tender Application feature streamlines the process of creating and submitting tender applications for new contracts. It automatically pre-fills information from your organisation profile and existing contracts, reducing duplication and ensuring consistency.</p>
        
        <h3>Organisation Profile</h3>
        <p>Before creating tender applications, complete your organisation profile in <strong>Admin → Organisation</strong>. The profile includes:</p>
        <ul>
            <li><strong>Legal & Registration:</strong> Company registration number, Care Inspectorate registration, charity number, VAT number</li>
            <li><strong>Addresses & Contact:</strong> Registered address, trading address, phone, website, main contact details</li>
            <li><strong>Quality & Compliance:</strong> Care Inspectorate rating, last inspection date</li>
            <li><strong>Service Information:</strong> Geographic coverage, service types, languages spoken, specialist expertise</li>
        </ul>
        <p>This information is stored once and automatically reused for all future tender applications.</p>
        
        <h3>Creating Tender Applications</h3>
        <p>To create a new tender application:</p>
        <ol>
            <li>Navigate to <strong>Contracts → Tender Applications</strong></li>
            <li>Click <strong>"New Application"</strong></li>
            <li>The form will automatically pre-fill with:
                <ul>
                    <li>Your organisation details from your profile</li>
                    <li>Current rates for contract types</li>
                    <li>Previous contracts with the selected local authority</li>
                    <li>Fair work compliance status</li>
                    <li>Geographic coverage, languages, and specialist expertise</li>
                </ul>
            </li>
            <li>Review and adjust the pre-filled information as needed</li>
            <li>Complete tender-specific details (service description, pricing, operational details)</li>
            <li>Save as draft or submit when ready</li>
        </ol>
        
        <h3>Pre-filled Information</h3>
        <p>The system intelligently pre-fills tender applications from:</p>
        <ul>
            <li><strong>Organisation Profile:</strong> All legal, contact, and service information</li>
            <li><strong>Existing Contracts:</strong> Previous contracts with the same local authority are referenced</li>
            <li><strong>Current Rates:</strong> Rates for contract types are pulled from your current rate settings</li>
            <li><strong>Compliance Status:</strong> Fair work compliance is determined from your existing contracts</li>
        </ul>
        <p>This eliminates the need to re-enter the same information for each tender application.</p>
        
        <h3>Managing Tender Applications</h3>
        <p>The Tender Applications page allows you to:</p>
        <ul>
            <li>View all your tender applications in one place</li>
            <li>Filter by status (draft, submitted, under review, awarded, lost)</li>
            <li>Track submission deadlines with color-coded alerts</li>
            <li>Edit draft applications</li>
            <li>View submission history</li>
        </ul>
        
        <h3>Benefits</h3>
        <ul>
            <li><strong>Time Saving:</strong> Pre-filled forms significantly reduce the time needed to create applications</li>
            <li><strong>Consistency:</strong> Ensures the same information is used across all applications</li>
            <li><strong>Accuracy:</strong> Reduces errors from manual data entry</li>
            <li><strong>Historical Context:</strong> Automatically references previous successful contracts</li>
        </ul>
        
        <h2 id="tender-monitoring">Tender Monitoring</h2>
        <p>The Tender Monitoring system automatically tracks new tender opportunities from Public Contracts Scotland, helping you stay ahead of new contract opportunities.</p>
        
        <h3>Automated Monitoring</h3>
        <p>The system can automatically check Public Contracts Scotland for new opportunities matching your criteria:</p>
        <ul>
            <li><strong>API Integration:</strong> Uses the official Public Contracts Scotland API</li>
            <li><strong>Customizable Criteria:</strong> Set keywords, CPV codes, local authorities, and value ranges</li>
            <li><strong>Instant Notifications:</strong> Get notified immediately when new opportunities are found</li>
            <li><strong>Auto-Import:</strong> Opportunities are automatically imported into your system</li>
            <li><strong>Scheduled Checking:</strong> Set up cron jobs to check automatically (recommended: every 6 hours)</li>
        </ul>
        
        <h3>Manual Import</h3>
        <p>You can also manually import opportunities from URLs:</p>
        <ul>
            <li>Paste a Public Contracts Scotland tender notice URL</li>
            <li>The system extracts key details automatically</li>
            <li>Review and adjust the extracted data</li>
            <li>Save the opportunity to your system</li>
        </ul>
        
        <h3>Monitoring Configuration</h3>
        <p>Configure monitoring preferences including:</p>
        <ul>
            <li>Search keywords (e.g., "social care", "supported living")</li>
            <li>CPV codes (procurement categories)</li>
            <li>Local authorities to monitor</li>
            <li>Contract value ranges</li>
            <li>Notification methods (email, in-app, or both)</li>
        </ul>
        
        <h2 id="audit-logging">Audit Logging</h2>
        <p>The system maintains comprehensive audit logs of all changes for compliance and accountability.</p>
        
        <h3>What's Logged</h3>
        <p>Every change is logged with:</p>
        <ul>
            <li>User who made the change</li>
            <li>Timestamp</li>
            <li>Entity type and ID</li>
            <li>Action (create, update, delete)</li>
            <li>Field name (for updates)</li>
            <li>Old and new values</li>
            <li>IP address and user agent</li>
            <li>URL where change was made</li>
        </ul>
        
        <h3>Approval Workflows</h3>
        <p>Organisations can configure approval rules requiring manager or role-based approval for sensitive changes:</p>
        <ul>
            <li><strong>Self-Approval:</strong> Default - users can make changes themselves</li>
            <li><strong>Manager Approval:</strong> Changes require manager approval</li>
            <li><strong>Role-Based Approval:</strong> Specific roles must approve certain changes</li>
            <li><strong>Field-Specific Rules:</strong> Different approval requirements for different fields</li>
        </ul>
        
        <h3>Viewing Audit Logs</h3>
        <p>Administrators can view audit logs with filtering by:</p>
        <ul>
            <li>Entity type</li>
            <li>Action type</li>
            <li>User</li>
            <li>Date range</li>
        </ul>
        
        <h2 id="resources-guides">Resources & Guides</h2>
        <p>The system includes comprehensive resources:</p>
        <ul>
            <li><strong>Contracts Guide:</strong> Detailed explanation of social care contracts in Scotland, procurement routes, and processes</li>
            <li><strong>Glossary:</strong> Definitions of technical terms related to social care and contracts</li>
            <li><strong>Local Authority Rates:</strong> Information on rates, positions, and updates from local authorities</li>
            <li><strong>How-to Guides:</strong> Step-by-step instructions for common tasks</li>
            <li><strong>Documentation:</strong> This comprehensive guide</li>
        </ul>
        
        <h2 id="teams-management">Teams Management</h2>
        <p>The system supports hierarchical team structures with role-based access control, allowing organisations to organise their staff and control contract access.</p>
        
        <h3>Team Structure</h3>
        <p>Organisations can create a flexible team hierarchy:</p>
        <ul>
            <li><strong>Custom Team Types:</strong> Define your own team types (e.g., Department, Division, Region, Unit) instead of using predefined types</li>
            <li><strong>Hierarchical Teams:</strong> Create parent-child relationships between teams (e.g., Region → Area → Team)</li>
            <li><strong>Team Assignment:</strong> Assign contracts to specific teams for better organisation</li>
        </ul>
        
        <h3>Team Roles</h3>
        <p>Users can be assigned to teams with specific roles:</p>
        <ul>
            <li><strong>Manager:</strong> Can manage contracts assigned to their team (and child teams)</li>
            <li><strong>Admin:</strong> Can manage contracts assigned to their team (and child teams)</li>
            <li><strong>Finance:</strong> Can view and edit all contracts in the organisation</li>
            <li><strong>Senior Manager:</strong> Can view and edit all contracts in the organisation</li>
            <li><strong>Member:</strong> Basic team membership (view access)</li>
        </ul>
        
        <h3>Access Control</h3>
        <p>The system enforces team-based access control:</p>
        <ul>
            <li>Team managers can only see and manage contracts assigned to their team or child teams</li>
            <li>Finance and senior managers can access all contracts in the organisation</li>
            <li>Organisation administrators can manage all teams and contracts</li>
            <li>Contracts can be assigned to teams when created or edited</li>
        </ul>
        
        <h3>Team Import</h3>
        <p>Bulk import your team structure from CSV or JSON files:</p>
        <ul>
            <li>Import team types, teams, and user assignments in one operation</li>
            <li>Perfect for importing from Entra ID (Azure AD) or other organisational systems</li>
            <li>Preview before importing to verify data</li>
            <li>Safe to re-run - existing items are skipped</li>
        </ul>
        <p>To import teams, navigate to <strong>Admin → Import Teams</strong> and follow the instructions.</p>
        
        <h2 id="user-roles">User Roles & Permissions</h2>
        <p>The system uses role-based access control with team-based permissions:</p>
        <ul>
            <li><strong>Super Administrator:</strong> System-wide access, manages organisations, users, and system settings</li>
            <li><strong>Organisation Administrator:</strong> Full access to their organisation's data, can manage users, contracts, rates, teams, and settings</li>
            <li><strong>Team Manager/Admin:</strong> Can manage contracts assigned to their team(s) and child teams</li>
            <li><strong>Finance/Senior Manager:</strong> Can view and edit all contracts in the organisation</li>
            <li><strong>Staff:</strong> View-only access to contracts, reports, and people records (based on team assignments)</li>
        </ul>
        
        <h2 id="organisation-settings">Organisation Settings</h2>
        <p>Organisation administrators can configure:</p>
        <ul>
            <li>Terminology preferences (how people are referred to)</li>
            <li>View organisation information (name, domain, seats)</li>
            <li>Manage organisation-specific contract types</li>
        </ul>
        
        <h2 id="security-privacy">Security & Privacy</h2>
        <p>The system includes comprehensive security measures:</p>
        <ul>
            <li>Password hashing with bcrypt</li>
            <li>CSRF protection on all forms</li>
            <li>Organisation data isolation</li>
            <li>SQL injection prevention</li>
            <li>Email verification for new accounts</li>
            <li>Session management</li>
        </ul>
        <p>See our <a href="<?php echo htmlspecialchars(url('pages/privacy-policy.php')); ?>">Privacy Policy</a> and <a href="<?php echo htmlspecialchars(url('pages/terms.php')); ?>">Terms of Service</a> for more information.</p>
        
        <h2 id="mobile-access">Mobile Access</h2>
        <p>The system is fully responsive and can be accessed on:</p>
        <ul>
            <li>Desktop computers</li>
            <li>Tablets</li>
            <li>Mobile phones</li>
        </ul>
        <p>All features are accessible on mobile devices with optimised layouts for smaller screens.</p>
        
        <h2 id="getting-help">Getting Help</h2>
        <p>If you need assistance:</p>
        <ul>
            <li>Check the <a href="<?php echo htmlspecialchars(url('pages/how-tos.php')); ?>">How-to Guides</a> for step-by-step instructions</li>
            <li>Review the <a href="<?php echo htmlspecialchars(url('pages/faq.php')); ?>">FAQ</a> for common questions</li>
            <li>Contact your organisation administrator</li>
            <li>Refer to the <a href="<?php echo htmlspecialchars(url('pages/social-care-contracts-guide.php')); ?>">Contracts Guide</a> for information about Scottish social care contracts</li>
        </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Highlight active section in sidebar based on scroll position
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('.content-container h2[id]');
    const sidebarLinks = document.querySelectorAll('.docs-sidebar-link');
    
    function updateActiveLink() {
        let current = '';
        const scrollPosition = window.scrollY + 150;
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                current = section.getAttribute('id');
            }
        });
        
        sidebarLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
    }
    
    window.addEventListener('scroll', updateActiveLink);
    updateActiveLink(); // Initial call
    
    // Smooth scrolling for anchor links
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href.startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    const offsetTop = target.offsetTop - 100;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
});
</script>

<?php include INCLUDES_PATH . '/footer.php'; ?>
