<?php
/**
 * FAQ Page
 */
require_once dirname(__DIR__, 2) . '/config/config.php';

$pageTitle = 'FAQ';
include INCLUDES_PATH . '/header.php';
?>

<style>
.faq-sidebar {
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

.faq-sidebar h3 {
    margin-top: 0;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.faq-sidebar nav {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.faq-sidebar-link {
    text-decoration: none;
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    transition: all 0.2s;
    font-size: 0.9rem;
    color: var(--text-color);
    display: block;
}

.faq-sidebar-link:hover {
    background: rgba(37, 99, 235, 0.1);
    color: var(--primary-color);
}

.faq-sidebar-link.active {
    background: var(--primary-color);
    color: #ffffff !important;
}

.faq-content-wrapper {
    display: flex;
    gap: 2rem;
    align-items: start;
}

.faq-main-content {
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
    .faq-sidebar {
        display: none;
    }
    .faq-content-wrapper {
        display: block;
    }
}
</style>

<div class="card">
    <div class="card-header">
        <h1>Frequently Asked Questions</h1>
        <p style="color: var(--text-light); margin-top: 0.5rem;">Common questions and answers about the system</p>
    </div>
    
    <div class="faq-content-wrapper">
        <!-- Sticky Sidebar Navigation -->
        <aside class="faq-sidebar">
            <h3>Contents</h3>
            <nav>
                <a href="#registration-account" class="faq-sidebar-link">Registration & Account</a>
                <a href="#terminology" class="faq-sidebar-link">Terminology</a>
                <a href="#contracts" class="faq-sidebar-link">Contracts</a>
                <a href="#tender-applications" class="faq-sidebar-link">Tender Applications</a>
                <a href="#tender-monitoring" class="faq-sidebar-link">Tender Monitoring</a>
                <a href="#audit-logging" class="faq-sidebar-link">Audit Logging</a>
                <a href="#person-tracking" class="faq-sidebar-link">Person Tracking</a>
                <a href="#rates" class="faq-sidebar-link">Rates</a>
                <a href="#contract-types" class="faq-sidebar-link">Contract Types</a>
                <a href="#reports-analytics" class="faq-sidebar-link">Reports & Analytics</a>
                <a href="#glossary" class="faq-sidebar-link">Glossary</a>
                <a href="#user-roles-permissions" class="faq-sidebar-link">User Roles & Permissions</a>
                <a href="#mobile-access" class="faq-sidebar-link">Mobile Access</a>
                <a href="#privacy-security" class="faq-sidebar-link">Privacy & Security</a>
                <a href="#getting-help" class="faq-sidebar-link">Getting Help</a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <div class="faq-main-content">
            <div class="content-container" style="max-width: 900px; margin: 0 auto; padding-left: 2rem; padding-right: 2rem;">
        <h2 id="registration-account">Registration & Account</h2>
        
        <h3>How do I register for an account?</h3>
        <p>Click the "Register" link in the navigation menu. Enter your email address (which must match your organisation's domain), your name, and create a strong password. You'll receive an email verification link to activate your account.</p>
        
        <h3>Why do I need to verify my email?</h3>
        <p>Email verification ensures that you have access to the email address you registered with and helps protect account security. You must click the verification link in the email before you can log in.</p>
        
        <h3>What are the password requirements?</h3>
        <p>Passwords must be at least 12 characters long and include:
            <ul>
                <li>At least one uppercase letter</li>
                <li>At least one lowercase letter</li>
                <li>At least one number</li>
                <li>At least one special character</li>
            </ul>
        </p>
        
        <h3>I don't know my organisation's domain. What should I do?</h3>
        <p>The system automatically detects your organisation based on your email domain. If you're unsure, contact your organisation administrator or check with your IT department.</p>
        
        <h3>Can I access data from other organisations?</h3>
        <p>No, each organisation's data is completely isolated. You can only see and manage data for your own organisation. This ensures data privacy and security.</p>
        
        <h2 id="terminology">Terminology</h2>
        
        <h3>Can I change how people are referred to in the system?</h3>
        <p>Yes! Organisation administrators can set custom terminology preferences in "Admin" → "Organisation". You can choose terms like "person we support", "service user", "client", "patient", or any other terminology that aligns with your organisation's values. These preferences apply throughout the entire system.</p>
        
        <h3>Who can change terminology preferences?</h3>
        <p>Only organisation administrators can change terminology preferences. Once set, all users in your organisation will see the custom terms throughout the system.</p>
        
        <h2 id="contracts">Contracts</h2>
        
        <h3>What's the difference between single person and bulk contracts?</h3>
        <p>Single person contracts are for supporting one individual and can optionally be linked to a person record for tracking. Bulk contracts are for supporting multiple people and include specifications for staff numbers, hours, and the number of people being supported.</p>
        
        <h3>What are procurement routes?</h3>
        <p>Procurement routes describe how a contract was awarded. Common routes include Competitive Tender, Framework Agreement, Direct Award (SDS), Spot Purchase, and Block Contract. Tracking this helps understand the contract's origin and process.</p>
        
        <h3>What is tender status?</h3>
        <p>Tender status indicates where a contract is in the procurement lifecycle. Statuses include Pre-tender, Tender Submitted, Under Evaluation, Awarded, Active, Expired, and more. Keeping this updated helps track contract progress.</p>
        
        <h3>Can I delete a contract?</h3>
        <p>Only organisation administrators can delete contracts. Contact your administrator if you need a contract removed. In most cases, it's better to mark a contract as "Inactive" rather than deleting it, as this preserves historical records.</p>
        
        <h3>What is the workflow dashboard?</h3>
        <p>The workflow dashboard provides a visual overview of all your contracts, organised by tender status. It helps you quickly see which contracts need attention, what stage they're at, and their procurement routes.</p>
        
        <h2 id="tender-applications">Tender Applications</h2>
        
        <h3>What is the Tender Application feature?</h3>
        <p>The Tender Application feature helps you create and manage tender submissions for new contracts. It automatically pre-fills information from your organisation profile and existing contracts, saving time and ensuring consistency across all your applications.</p>
        
        <h3>How does pre-filling work?</h3>
        <p>When creating a new tender application, the system automatically fills in:</p>
        <ul>
            <li>Your organisation details (name, registration numbers, addresses, contact information)</li>
            <li>Care Inspectorate rating and registration information</li>
            <li>Current rates for contract types</li>
            <li>Previous contracts with the same local authority</li>
            <li>Fair work compliance status</li>
            <li>Geographic coverage, service types, languages, and specialist expertise</li>
        </ul>
        <p>You can review and adjust any pre-filled information before submitting.</p>
        
        <h3>How do I set up my organisation profile?</h3>
        <p>Go to "Admin" → "Organisation" and scroll down to the "Organisation Profile" section. Complete all relevant fields including:</p>
        <ul>
            <li>Legal & Registration (company registration, Care Inspectorate registration, charity number, VAT number)</li>
            <li>Addresses & Contact (registered address, trading address, phone, website, main contact details)</li>
            <li>Quality & Compliance (Care Inspectorate rating, last inspection date)</li>
            <li>Service Information (geographic coverage, service types, languages spoken, specialist expertise)</li>
        </ul>
        <p>This information will be reused for all future tender applications, so you only need to enter it once.</p>
        
        <h3>Can I save a tender application as a draft?</h3>
        <p>Yes! You can save tender applications as drafts and come back to edit them later. Use the "Save Draft" button to save your progress. When you're ready to submit, click "Submit Application".</p>
        
        <h3>Can I edit a submitted tender application?</h3>
        <p>Once submitted, tender applications are locked to prevent accidental changes. If you need to make changes after submission, contact your organisation administrator or create a new application.</p>
        
        <h3>How do I track my tender applications?</h3>
        <p>Go to "Contracts" → "Tender Applications" to see all your applications. You can filter by status (draft, submitted, under review, awarded, lost) and see submission deadlines with color-coded alerts for urgent deadlines.</p>
        
        <h3>Does the system submit tenders automatically?</h3>
        <p>No, the system helps you prepare tender applications but does not automatically submit them to local authority portals. You'll need to export or copy the information and submit through the local authority's tender portal or Public Contracts Scotland.</p>
        
        <h2 id="tender-monitoring">Tender Monitoring</h2>
        
        <h3>What is Tender Monitoring?</h3>
        <p>Tender Monitoring automatically checks Public Contracts Scotland for new tender opportunities matching your criteria. When new opportunities are found, you'll receive instant notifications and they'll be automatically imported into your system.</p>
        
        <h3>How do I set up monitoring?</h3>
        <p>Go to "Contracts" → "Tender Monitoring" (admin only). Click "New Monitor" and configure:</p>
        <ul>
            <li><strong>Keywords:</strong> Terms to search for (e.g., "social care", "supported living")</li>
            <li><strong>CPV Codes:</strong> Procurement category codes (e.g., 85000000 for health and social work services)</li>
            <li><strong>Local Authorities:</strong> Optionally filter by specific authorities</li>
            <li><strong>Notification Method:</strong> Choose email, in-app, or both</li>
        </ul>
        <p>Once configured, the system will automatically check for new opportunities. You can also click "Check Now" to manually trigger a check.</p>
        
        <h3>How often does the system check for new opportunities?</h3>
        <p>By default, you need to set up a cron job to run automatic checks. The recommended frequency is every 6 hours. You can also manually trigger checks from the monitoring page. See the monitoring page for cron job setup instructions.</p>
        
        <h3>Can I import opportunities from URLs?</h3>
        <p>Yes! On the Tender Opportunities page, click "Import from URL" and paste a Public Contracts Scotland tender notice URL. The system will automatically extract key details like title, description, deadlines, and estimated value.</p>
        
        <h3>What if monitoring finds 0 opportunities?</h3>
        <p>This could mean:</p>
        <ul>
            <li>No new opportunities match your criteria in the last 30 days</li>
            <li>All matching opportunities have already been imported (duplicates are skipped)</li>
            <li>The API might not be responding (check the diagnostic section on the monitoring page)</li>
            <li>Your keywords/CPV codes might be too specific</li>
        </ul>
        <p>Check the diagnostic information on the monitoring page and your server error logs for more details.</p>
        
        <h2 id="audit-logging">Audit Logging</h2>
        
        <h3>What is Audit Logging?</h3>
        <p>Audit Logging tracks every change made across the system, including who made it, when, what changed, and from where. This provides a complete audit trail for compliance and accountability.</p>
        
        <h3>What information is logged?</h3>
        <p>For each change, the system logs:</p>
        <ul>
            <li>User who made the change</li>
            <li>Timestamp</li>
            <li>Entity type and ID (e.g., contract, rate, person)</li>
            <li>Action (create, update, delete)</li>
            <li>Old and new values</li>
            <li>IP address and user agent</li>
            <li>URL where the change was made</li>
        </ul>
        
        <h3>Who can view audit logs?</h3>
        <p>Audit logs are available to organisation administrators. Go to "Admin" → "Audit Logs" to view all changes. You can filter by entity type, action, user, and date range.</p>
        
        <h3>What is the Approval Workflow?</h3>
        <p>The Approval Workflow allows organisations to require manager or role-based approval for sensitive changes. You can configure rules such as:</p>
        <ul>
            <li>Self-approval (default - users can make changes themselves)</li>
            <li>Manager approval required</li>
            <li>Role-based approval (e.g., only finance managers can approve payment changes)</li>
            <li>Field-specific rules (e.g., contract value changes require approval)</li>
        </ul>
        <p>Go to "Admin" → "Approval Rules" to configure these settings.</p>
        
        <h2 id="person-tracking">Person Tracking</h2>
        
        <h3>What identifiers can I use for people?</h3>
        <p>You can use multiple identifiers including:
            <ul>
                <li>CHI Number (Community Health Index)</li>
                <li>SWIS Number (Social Work Information System)</li>
                <li>National Insurance Number</li>
                <li>Organisation-specific identifier</li>
            </ul>
        You must select one as the primary identifier for unique identification.</p>
        
        <h3>Why should I link a person to a contract?</h3>
        <p>Linking a person to a contract allows you to track their complete history across multiple contracts and local authorities. This is essential for maintaining continuity of care records and generating comprehensive reports.</p>
        
        <h3>What happens when a person moves between local authorities?</h3>
        <p>The system maintains their complete history. You can view all contracts associated with a person, track payments across different authorities, and generate reports showing continuity of care regardless of which local authority they're currently with.</p>
        
        <h3>Can I track a person across multiple contracts?</h3>
        <p>Yes! When you view a person's detail page, you'll see all contracts associated with them, their payment history, and their local authority history. This provides a complete picture of the support they've received.</p>
        
        <h2 id="rates">Rates</h2>
        
        <h3>How are rate changes tracked?</h3>
        <p>When you set a new rate for a contract type and local authority, the previous rate is automatically archived in the rate history. This allows you to track how rates have changed over time and maintain a complete historical record.</p>
        
        <h3>Can I set different rates for different local authorities?</h3>
        <p>Yes, rates are set per contract type and local authority combination, allowing you to have different rates for each authority. This reflects the reality that different local authorities may have different rate structures.</p>
        
        <h3>What are reference rates?</h3>
        <p>Reference rates include the Real Living Wage history, Scotland Mandated Minimum Rates, and Homecare Association recommended rates. These provide benchmarks to help you understand the broader rate context when setting your own rates.</p>
        
        <h3>Where can I find local authority rate updates?</h3>
        <p>Navigate to "Learn" → "Local Authority Rates" or "News & Updates" → "Local Authority Rates" to see rate information, positions, and updates from local authorities.</p>
        
        <h3>How do I know if reference rates are up to date?</h3>
        <p>Administrators can access the <strong>Rate Monitoring</strong> dashboard (Admin → Rate Monitoring) which automatically validates all reference rates. The system checks:</p>
        <ul>
            <li>Whether rates exist and are current</li>
            <li>If rates are outdated (more than 6 months for Scotland rates, 1 year for Real Living Wage)</li>
            <li>If rate periods have expired</li>
            <li>If effective dates are valid</li>
        </ul>
        <p>The monitoring dashboard shows visual status indicators and alerts administrators when action is needed. You'll also see alerts on your dashboard if there are any rate monitoring issues.</p>
        
        <h3>What happens if a reference rate is outdated?</h3>
        <p>The Rate Monitoring system will flag outdated rates with a warning status. Administrators will see alerts on their dashboard and can access the monitoring page to review details. The system provides guidance on when rates typically need updating (e.g., Real Living Wage updates annually in November).</p>
        
        <h2 id="contract-types">Contract Types</h2>
        
        <h3>Can I create my own contract types?</h3>
        <p>Yes, organisation administrators can create custom contract types specific to their organisation. You can also use system default types if available. Contract types can be marked as active or inactive.</p>
        
        <h3>What are system default contract types?</h3>
        <p>System default contract types are pre-defined types available to all organisations. Your organisation can also create its own custom types that are specific to your needs.</p>
        
        <h2 id="reports-analytics">Reports & Analytics</h2>
        
        <h3>What reports can I generate?</h3>
        <p>You can generate reports on:
            <ul>
                <li>Payments over specified date ranges</li>
                <li>Rate changes and trends</li>
                <li>Person payment history</li>
                <li>Contracts by status, type, or local authority</li>
                <li>Contract expiry monitoring</li>
            </ul>
        </p>
        
        <h3>Can I export reports?</h3>
        <p>Currently, reports are viewable within the system. Export functionality may be added in future updates.</p>
        
        <h2 id="glossary">Glossary</h2>
        
        <h3>Can I suggest new terms for the glossary?</h3>
        <p>Yes! Logged-in users can suggest new terms by clicking "Suggest a Term" on the Glossary page. Your suggestion will be reviewed by administrators, and you'll receive an email notification when it's approved or rejected.</p>
        
        <h3>How do I search the glossary?</h3>
        <p>Use the search box at the top of the Glossary page. Results filter automatically as you type, making it easy to find the term you're looking for.</p>
        
        <h2 id="user-roles-permissions">User Roles & Permissions</h2>
        
        <h3>What can staff members do?</h3>
        <p>Staff members can view contracts, reports, and people records but cannot create or edit contracts, rates, or person records. They have read-only access to most features.</p>
        
        <h3>What can organisation administrators do?</h3>
        <p>Organisation administrators have full access to their organisation's data. They can:
            <ul>
                <li>Create and edit contracts, rates, and person records</li>
                <li>Manage contract types</li>
                <li>Manage users and assign roles</li>
                <li>Configure organisation settings including terminology preferences</li>
                <li>Generate and view all reports</li>
            </ul>
        </p>
        
        <h3>How do I become an organisation administrator?</h3>
        <p>Contact your super administrator to be assigned the organisation administrator role. Only super administrators can change user roles.</p>
        
        <h3>What is a super administrator?</h3>
        <p>Super administrators have system-wide access. They can manage organisations, create super admin users, review glossary suggestions, and access system-wide settings.</p>
        
        <h2 id="mobile-access">Mobile Access</h2>
        
        <h3>Can I use the system on my mobile phone?</h3>
        <p>Yes! The system is fully responsive and can be accessed on desktop computers, tablets, and mobile phones. All features are accessible on mobile devices with optimised layouts for smaller screens.</p>
        
        <h2 id="privacy-security">Privacy & Security</h2>
        
        <h3>How is my data protected?</h3>
        <p>The system includes comprehensive security measures:
            <ul>
                <li>Password hashing with bcrypt</li>
                <li>CSRF protection on all forms</li>
                <li>Organisation data isolation</li>
                <li>SQL injection prevention</li>
                <li>Email verification for new accounts</li>
                <li>Secure session management</li>
            </ul>
        See our <a href="<?php echo htmlspecialchars(url('pages/privacy-policy.php')); ?>">Privacy Policy</a> for more details.</p>
        
        <h3>What cookies does the system use?</h3>
        <p>The system uses essential cookies for site functionality and session management. See our <a href="<?php echo htmlspecialchars(url('pages/cookie-policy.php')); ?>">Cookie Policy</a> for complete details.</p>
        
        <h2 id="getting-help">Getting Help</h2>
        
        <h3>Where can I find more detailed instructions?</h3>
        <p>Check the <a href="<?php echo htmlspecialchars(url('pages/how-tos.php')); ?>">How-to Guides</a> for step-by-step instructions on common tasks, or the <a href="<?php echo htmlspecialchars(url('pages/documentation.php')); ?>">Documentation</a> for comprehensive system information.</p>
        
        <h3>I need help understanding Scottish social care contracts</h3>
        <p>Visit the <a href="<?php echo htmlspecialchars(url('pages/social-care-contracts-guide.php')); ?>">Contracts Guide</a> for detailed information about social care contracts in Scotland, procurement routes, rate variations, and the contract lifecycle.</p>
        
        <h3>Who should I contact if I have problems?</h3>
        <p>Contact your organisation administrator for help with account access, permissions, or organisation-specific questions. For technical issues, they can escalate to the system administrators.</p>
        </div>
        </div>
    </div>
</div>

<script>
// Update active sidebar link based on scroll position
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('.content-container h2[id]');
    const sidebarLinks = document.querySelectorAll('.faq-sidebar-link');
    
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
    
    // Update on scroll
    window.addEventListener('scroll', updateActiveLink);
    
    // Update on page load
    updateActiveLink();
    
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
