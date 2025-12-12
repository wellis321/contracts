<?php
/**
 * How-to Guides Page
 */
require_once dirname(__DIR__, 2) . '/config/config.php';

$pageTitle = 'How-to Guides';
include INCLUDES_PATH . '/header.php';
?>

<style>
.howtos-sidebar {
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

.howtos-sidebar h3 {
    margin-top: 0;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.howtos-sidebar nav {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.howtos-sidebar-link {
    text-decoration: none;
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    transition: all 0.2s;
    font-size: 0.9rem;
    color: var(--text-color);
    display: block;
}

.howtos-sidebar-link:hover {
    background: rgba(37, 99, 235, 0.1);
    color: var(--primary-color);
}

.howtos-sidebar-link.active {
    background: var(--primary-color);
    color: #ffffff !important;
}

.howtos-content-wrapper {
    display: flex;
    gap: 2rem;
    align-items: start;
}

.howtos-main-content {
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
    .howtos-sidebar {
        display: none;
    }
    .howtos-content-wrapper {
        display: block;
    }
}
</style>

<div class="card">
    <div class="card-header">
        <h1>How-to Guides</h1>
        <p style="color: var(--text-light); margin-top: 0.5rem;">Step-by-step instructions for common tasks</p>
    </div>
    
    <div class="howtos-content-wrapper">
        <!-- Sticky Sidebar Navigation -->
        <aside class="howtos-sidebar">
            <h3>Contents</h3>
            <nav>
                <a href="#getting-started" class="howtos-sidebar-link">Getting Started</a>
                <a href="#contract-management" class="howtos-sidebar-link">Contract Management</a>
                <a href="#person-management" class="howtos-sidebar-link">Person Management</a>
                <a href="#rate-management" class="howtos-sidebar-link">Rate Management</a>
                <a href="#contract-types" class="howtos-sidebar-link">Contract Types</a>
                <a href="#reports-analytics" class="howtos-sidebar-link">Reports & Analytics</a>
                <a href="#workflow-management" class="howtos-sidebar-link">Workflow Management</a>
                <a href="#tender-monitoring" class="howtos-sidebar-link">Tender Monitoring</a>
                <a href="#audit-logging" class="howtos-sidebar-link">Audit Logging</a>
                <a href="#glossary" class="howtos-sidebar-link">Glossary</a>
                <a href="#user-management" class="howtos-sidebar-link">User Management</a>
                <a href="#teams-management" class="howtos-sidebar-link">Teams Management</a>
                <a href="#tips-best-practices" class="howtos-sidebar-link">Tips & Best Practices</a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <div class="howtos-main-content">
            <div class="content-container" style="max-width: 900px; margin: 0 auto; padding-left: 2rem; padding-right: 2rem;">
        <h2 id="getting-started">Getting Started</h2>
        
        <h3>Registering for an Account</h3>
        <ol>
            <li>Click "Register" in the navigation menu</li>
            <li>Enter your email address (must match your organisation's domain)</li>
            <li>Enter your first and last name</li>
            <li>Create a strong password (minimum 12 characters with uppercase, lowercase, numbers, and special characters)</li>
            <li>Confirm your password</li>
            <li>Click "Register"</li>
            <li>Check your email for a verification link</li>
            <li>Click the verification link to activate your account</li>
            <li>Log in with your credentials</li>
        </ol>
        
        <h3>Setting Up Terminology Preferences</h3>
        <ol>
            <li>Log in as an organisation administrator</li>
            <li>Navigate to "Admin" → "Organisation"</li>
            <li>Scroll to the "Terminology Preferences" section</li>
            <li>Enter your preferred singular term (e.g., "person we support", "service user", "client")</li>
            <li>Enter your preferred plural term (e.g., "people we support", "service users", "clients")</li>
            <li>Click "Update Terminology"</li>
            <li>The terms will immediately apply throughout the entire system</li>
        </ol>
        
        <h2 id="contract-management">Contract Management</h2>
        
        <h3>Creating Your First Contract</h3>
        <ol>
            <li>Ensure you have contract types set up (ask your organisation administrator if needed)</li>
            <li>Navigate to "Contracts" → "View Contracts" from the main menu</li>
            <li>Click "Add Contract"</li>
            <li>Fill in the required fields:
                <ul>
                    <li>Title: A descriptive name for the contract</li>
                    <li>Contract Type: Select from your organisation's contract types</li>
                    <li>Local Authority: Choose the relevant local authority</li>
                    <li>Start Date: When the contract begins</li>
                </ul>
            </li>
            <li>Optionally add a contract number</li>
            <li>Select the procurement route (Competitive Tender, Framework Agreement, Direct Award, etc.)</li>
            <li>Select the tender status (Pre-tender, Tender Submitted, Awarded, etc.)</li>
            <li>Select whether it's a single person or bulk contract</li>
            <li>If single person: Optionally link to a person record</li>
            <li>If bulk: Specify the number of people, staff, and hours</li>
            <li>Add payment details (method, frequency, amounts)</li>
            <li>Click "Create" to save</li>
        </ol>
        
        <h3>Editing a Contract</h3>
        <ol>
            <li>Navigate to "Contracts" → "View Contracts"</li>
            <li>Click on the contract title to view details</li>
            <li>Click "Edit Contract"</li>
            <li>Update any fields as needed</li>
            <li>Click "Update Contract" to save changes</li>
        </ol>
        
        <h3>Updating Tender Status</h3>
        <ol>
            <li>Open the contract you want to update</li>
            <li>Click "Edit Contract"</li>
            <li>Update the "Tender Status" field to reflect the current stage</li>
            <li>Save the changes</li>
            <li>The workflow dashboard will automatically reflect the new status</li>
        </ol>
        
        <h2 id="person-management">Person Management</h2>
        
        <h3>Adding a New Person</h3>
        <ol>
            <li>Navigate to the "<?php echo ucfirst(htmlspecialchars(getPersonTerm(false))); ?>" page from the main menu</li>
            <li>Click "Add <?php echo ucfirst(htmlspecialchars(getPersonTerm(true))); ?>"</li>
            <li>Enter the person's first name and last name</li>
            <li>Optionally enter their date of birth</li>
            <li>Add at least one identifier:
                <ul>
                    <li>CHI Number (Community Health Index)</li>
                    <li>SWIS Number (Social Work Information System)</li>
                    <li>National Insurance Number</li>
                    <li>Organisation-specific identifier</li>
                </ul>
            </li>
            <li>Select one identifier as the primary identifier</li>
            <li>Click "Create <?php echo ucfirst(htmlspecialchars(getPersonTerm(true))); ?>"</li>
        </ol>
        
        <h3>Linking a Person to a Contract</h3>
        <ol>
            <li>When creating or editing a contract, check "Single <?php echo ucfirst(htmlspecialchars(getPersonTerm(true))); ?> Contract"</li>
            <li>In the "<?php echo ucfirst(htmlspecialchars(getPersonTerm(true))); ?> (Optional)" dropdown, select the person</li>
            <li>This links the contract to the person record for tracking</li>
            <li>Save the contract</li>
        </ol>
        
        <h3>Viewing a Person's History</h3>
        <ol>
            <li>Navigate to the "<?php echo ucfirst(htmlspecialchars(getPersonTerm(false))); ?>" page</li>
            <li>Click "View Details" on the person you want to see</li>
            <li>View their contract history across all local authorities</li>
            <li>View their payment history by selecting a date range</li>
            <li>See all identifiers associated with the person</li>
        </ol>
        
        <h2 id="rate-management">Rate Management</h2>
        
        <h3>Setting Rates</h3>
        <ol>
            <li>Navigate to "Contracts" → "Rates" from the main menu</li>
            <li>Select the contract type you want to set rates for</li>
            <li>Choose the local authority</li>
            <li>Enter the rate amount</li>
            <li>Set the effective date (when this rate becomes active)</li>
            <li>Click "Set Rate"</li>
            <li>Previous rates will be automatically archived in the rate history</li>
        </ol>
        
        <h3>Viewing Rate History</h3>
        <ol>
            <li>Navigate to "Contracts" → "Rates"</li>
            <li>Select a contract type and local authority</li>
            <li>View the rate history table showing all past and current rates</li>
            <li>See effective dates and when rates changed</li>
        </ol>
        
        <h3>Accessing Reference Rates</h3>
        <ol>
            <li>Navigate to "Learn" → "Local Authority Rates" or "News & Updates" → "Local Authority Rates"</li>
            <li>View current reference rates including:
                <ul>
                    <li>Real Living Wage history</li>
                    <li>Scotland Mandated Minimum Rates</li>
                    <li>Homecare Association recommended rates</li>
                </ul>
            </li>
            <li>Check local authority rate updates and positions</li>
        </ol>
        
        <h3>Monitoring Reference Rates (Administrators)</h3>
        <ol>
            <li>Navigate to "Admin" → "Rate Monitoring" (available to organisation administrators and super administrators)</li>
            <li>View the monitoring dashboard which shows:
                <ul>
                    <li>Overall status of all reference rates</li>
                    <li>Visual indicators (green = current, yellow = needs review, red = critical)</li>
                    <li>Summary statistics (current rates, outdated rates, missing rates)</li>
                    <li>Detailed status for each rate type</li>
                </ul>
            </li>
            <li>Review any warnings or errors displayed</li>
            <li>Take action if rates need updating (e.g., add new rates, update existing ones)</li>
            <li>The system automatically checks:
                <ul>
                    <li>If rates are outdated (Scotland rates >6 months, Real Living Wage >1 year)</li>
                    <li>If rate periods have expired</li>
                    <li>If effective dates are valid</li>
                    <li>If critical rates are missing</li>
                </ul>
            </li>
            <li>You'll also see alerts on your dashboard if there are rate monitoring issues</li>
        </ol>
        
        <h2 id="contract-types">Contract Types</h2>
        
        <h3>Creating a Custom Contract Type</h3>
        <ol>
            <li>Navigate to "Contracts" → "Contract Types"</li>
            <li>Click "Add Contract Type"</li>
            <li>Enter a name (e.g., "Sleepovers", "Support", "Waking Hours")</li>
            <li>Optionally add a description</li>
            <li>Click "Create"</li>
            <li>The new type will be available when creating contracts</li>
        </ol>
        
        <h2 id="reports-analytics">Reports & Analytics</h2>
        
        <h3>Generating a Payment Report</h3>
        <ol>
            <li>Navigate to "Contracts" → "Reports"</li>
            <li>Select a start date and end date for your report</li>
            <li>Click "Generate Report"</li>
            <li>View summaries of:
                <ul>
                    <li>Total payments by method</li>
                    <li>Payment frequency breakdown</li>
                    <li>Contracts with payments</li>
                </ul>
            </li>
        </ol>
        
        <h3>Viewing Person Payment History</h3>
        <ol>
            <li>Navigate to a person's detail page</li>
            <li>Select a date range for the payment history</li>
            <li>Click "View Payment History"</li>
            <li>See all payments for that person across all contracts and local authorities</li>
        </ol>
        
        <h2 id="workflow-management">Workflow Management</h2>
        
        <h3>Using the Workflow Dashboard</h3>
        <ol>
            <li>Navigate to "Contracts" → "Workflow Dashboard"</li>
            <li>View all contracts organised by tender status</li>
            <li>See procurement routes for each contract</li>
            <li>Identify contracts that need attention</li>
        </ol>
        
        <h2 id="tender-monitoring">Tender Monitoring</h2>
        
        <h3>Setting Up Automated Tender Monitoring</h3>
        <ol>
            <li>Navigate to "Contracts" → "Tender Monitoring" (admin only)</li>
            <li>Click "New Monitor"</li>
            <li>Configure your monitoring criteria:
                <ul>
                    <li><strong>Keywords:</strong> Enter search terms like "social care", "supported living" (comma-separated)</li>
                    <li><strong>CPV Codes:</strong> Enter procurement codes like "85000000" for health and social work services</li>
                    <li><strong>Local Authorities:</strong> Optionally select specific authorities to monitor</li>
                    <li><strong>Notification Method:</strong> Choose email, in-app, or both</li>
                    <li><strong>Email Address:</strong> Enter where to send notifications (or leave blank to use account email)</li>
                </ul>
            </li>
            <li>Check "Active" to start monitoring immediately</li>
            <li>Click "Create Monitor"</li>
            <li>The system will automatically check for new opportunities</li>
        </ol>
        
        <h3>Setting Up Automated Checking (Cron Job)</h3>
        <ol>
            <li>Access your server's cron configuration</li>
            <li>Add a cron job to run the monitoring script:
                <ul>
                    <li>For every 6 hours: <code>0 */6 * * * /usr/bin/php /path/to/contracts/scripts/check-tenders.php</code></li>
                    <li>For every hour: <code>0 * * * * /usr/bin/php /path/to/contracts/scripts/check-tenders.php</code></li>
                    <li>For daily at 9 AM: <code>0 9 * * * /usr/bin/php /path/to/contracts/scripts/check-tenders.php</code></li>
                </ul>
            </li>
            <li>Replace <code>/path/to/contracts</code> with your actual application path</li>
            <li>Test the script manually first: <code>php scripts/check-tenders.php</code></li>
        </ol>
        
        <h3>Manually Checking for Opportunities</h3>
        <ol>
            <li>Go to "Contracts" → "Tender Monitoring"</li>
            <li>Click "Check Now" button</li>
            <li>View the results showing how many opportunities were found</li>
            <li>Check the diagnostic section if no opportunities are found</li>
        </ol>
        
        <h3>Importing Opportunities from URLs</h3>
        <ol>
            <li>Go to "Contracts" → "Tender Opportunities"</li>
            <li>Click "Import from URL"</li>
            <li>Paste a Public Contracts Scotland tender notice URL</li>
            <li>Click "Import Opportunity"</li>
            <li>Review the extracted data in the form</li>
            <li>Make any necessary corrections</li>
            <li>Select the correct Local Authority and Contract Type</li>
            <li>Click "Save Imported Opportunity"</li>
        </ol>
        
        <h2 id="audit-logging">Audit Logging</h2>
        
        <h3>Viewing Audit Logs</h3>
        <ol>
            <li>Navigate to "Admin" → "Audit Logs" (admin only)</li>
            <li>Use filters to find specific changes:
                <ul>
                    <li>Filter by entity type (contract, rate, person, payment)</li>
                    <li>Filter by action (create, update, delete)</li>
                    <li>Filter by user</li>
                    <li>Filter by date range</li>
                </ul>
            </li>
            <li>Click on any log entry to see full details including old/new values</li>
            <li>Export logs if needed for compliance reporting</li>
        </ol>
        
        <h3>Configuring Approval Rules</h3>
        <ol>
            <li>Navigate to "Admin" → "Approval Rules" (admin only)</li>
            <li>Click "Create Approval Rule"</li>
            <li>Configure the rule:
                <ul>
                    <li><strong>Entity Type:</strong> What type of record (contract, rate, person, etc.)</li>
                    <li><strong>Action:</strong> What action requires approval (create, update, delete)</li>
                    <li><strong>Field:</strong> Optional - specific field that requires approval</li>
                    <li><strong>Approval Type:</strong> Choose self, manager, or role-based</li>
                    <li><strong>Required Role:</strong> If role-based, select which role can approve</li>
                </ul>
            </li>
            <li>Click "Create Rule"</li>
            <li>Rules are applied automatically when users make changes</li>
        </ol>
        
        <h3>Understanding Audit Log Information</h3>
        <p>Each audit log entry contains:</p>
        <ul>
            <li><strong>User:</strong> Who made the change (or "User Deleted" if user account was removed)</li>
            <li><strong>Timestamp:</strong> When the change was made</li>
            <li><strong>Entity:</strong> What was changed (e.g., Contract #123)</li>
            <li><strong>Action:</strong> What action was performed (create, update, delete)</li>
            <li><strong>Field:</strong> Which specific field changed (for updates)</li>
            <li><strong>Old/New Values:</strong> What the value was before and after</li>
            <li><strong>IP Address:</strong> Where the change was made from</li>
            <li><strong>URL:</strong> Which page the change was made on</li>
        </ul>
            <li>Click on any contract to view or edit details</li>
        </ol>
        
        <h2 id="glossary">Glossary</h2>
        
        <h3>Suggesting a New Glossary Term</h3>
        <ol>
            <li>Navigate to "Learn" → "Glossary"</li>
            <li>Click "Suggest a Term" button</li>
            <li>Enter the term you'd like to add</li>
            <li>Provide a clear definition</li>
            <li>Click "Submit Suggestion"</li>
            <li>Your suggestion will be reviewed by administrators</li>
            <li>You'll receive an email notification when it's approved or rejected</li>
        </ol>
        
        <h3>Searching the Glossary</h3>
        <ol>
            <li>Navigate to "Learn" → "Glossary"</li>
            <li>Type your search term in the search box</li>
            <li>Results will filter automatically as you type</li>
            <li>Click on any term to see its definition</li>
        </ol>
        
        <h2 id="user-management">User Management (Organisation Administrators)</h2>
        
        <h3>Adding a New User</h3>
        <ol>
            <li>Navigate to "Admin" → "Users"</li>
            <li>Click "Add User"</li>
            <li>Enter the user's email address (must match your organisation's domain)</li>
            <li>Enter their first and last name</li>
            <li>Set their role (Staff or Organisation Admin)</li>
            <li>Click "Create User"</li>
            <li>The user will receive an email with instructions to set their password</li>
        </ol>
        
        <h3>Managing User Roles</h3>
        <ol>
            <li>Navigate to "Admin" → "Users"</li>
            <li>Find the user you want to update</li>
            <li>Click "Edit" next to their name</li>
            <li>Update their role as needed</li>
            <li>Save the changes</li>
        </ol>
        
        <h2 id="teams-management">Teams Management (Organisation Administrators)</h2>
        
        <h3>Creating Custom Team Types</h3>
        <ol>
            <li>Navigate to "Admin" → "Teams"</li>
            <li>Scroll to the "Team Types" section</li>
            <li>Click "Add Team Type"</li>
            <li>Enter a name for your team type (e.g., "Department", "Region", "Area", "Division")</li>
            <li>Optionally add a description to explain what this team type represents</li>
            <li>Set the display order (lower numbers appear first in dropdowns)</li>
            <li>Click "Create Team Type"</li>
            <li>The new team type will be available when creating teams</li>
        </ol>
        
        <h3>Creating a Team</h3>
        <ol>
            <li>Navigate to "Admin" → "Teams"</li>
            <li>Scroll to the "Teams" section</li>
            <li>Click "Add Team"</li>
            <li>Enter a name for your team (e.g., "North Region", "Finance Department", "Support Team A")</li>
            <li>Select a team type from the dropdown (create team types first if needed)</li>
            <li>Optionally select a parent team if this team should be part of a hierarchy (e.g., "Team A" under "North Region")</li>
            <li>Optionally add a description</li>
            <li>Click "Create Team"</li>
            <li>The team will appear in your team list and can be assigned to contracts</li>
        </ol>
        
        <h3>Setting Up Team Hierarchy</h3>
        <ol>
            <li>Create parent teams first (teams without a parent)</li>
            <li>Then create child teams, selecting the parent team from the dropdown</li>
            <li>Example structure:
                <ul>
                    <li>Region: "North Region" (no parent)</li>
                    <li>Area: "Area 1" (parent: North Region)</li>
                    <li>Team: "Support Team A" (parent: Area 1)</li>
                </ul>
            </li>
            <li>Team managers can access their team and all child teams</li>
            <li>Finance and senior managers can access all teams regardless of hierarchy</li>
        </ol>
        
        <h3>Assigning Staff to Teams</h3>
        <ol>
            <li>Navigate to "Admin" → "Teams"</li>
            <li>Scroll to the "Assign Users to Teams" section</li>
            <li>Select a user from the dropdown (users must already exist in your organisation)</li>
            <li>Select the team to assign them to</li>
            <li>Choose their role in the team:
                <ul>
                    <li><strong>Member:</strong> Basic team membership (view access)</li>
                    <li><strong>Manager:</strong> Can manage contracts assigned to their team and child teams</li>
                    <li><strong>Admin:</strong> Can manage contracts assigned to their team and child teams</li>
                    <li><strong>Finance:</strong> Can view and edit all contracts in the organisation</li>
                    <li><strong>Senior Manager:</strong> Can view and edit all contracts in the organisation</li>
                </ul>
            </li>
            <li>Optionally check "Set as Primary Team" if this is the user's main team</li>
            <li>Click "Assign User"</li>
            <li>Users can be assigned to multiple teams with different roles</li>
        </ol>
        
        <h3>Creating Custom Team Roles</h3>
        <ol>
            <li>Navigate to "Admin" → "Teams"</li>
            <li>Scroll to the "Team Roles" section</li>
            <li>Click "Add Team Role"</li>
            <li>Enter a name for the role (e.g., "Deputy Manager", "Coordinator", "Supervisor")</li>
            <li>Enter a description explaining the role's responsibilities</li>
            <li>Select the access level:
                <ul>
                    <li><strong>Team Access:</strong> Can only access contracts in their assigned team(s) and child teams</li>
                    <li><strong>Organisation Access:</strong> Can access all contracts in the organisation</li>
                </ul>
            </li>
            <li>Click "Create Team Role"</li>
            <li>The new role will be available when assigning users to teams</li>
        </ol>
        
        <h3>Importing Teams from CSV or JSON</h3>
        <ol>
            <li>Navigate to "Admin" → "Import Teams"</li>
            <li>Download the sample CSV template if needed</li>
            <li>Prepare your file with:
                <ul>
                    <li>Team types (name, description, display_order)</li>
                    <li>Teams (name, team_type, parent_team, description)</li>
                    <li>User assignments (email, team_name, role_in_team, is_primary)</li>
                </ul>
            </li>
            <li>Click "Choose File" and select your CSV or JSON file</li>
            <li>Click "Preview Import" to review what will be imported</li>
            <li>Review the preview carefully, checking for any errors or warnings</li>
            <li>Click "Confirm Import" to proceed</li>
            <li>The system will create team types, teams, and assign users</li>
            <li>Existing items are skipped (safe to re-run)</li>
        </ol>
        
        <h3>Assigning Contracts to Teams</h3>
        <ol>
            <li>When creating or editing a contract, you'll see a "Team" dropdown</li>
            <li>Select the team that should manage this contract</li>
            <li>Only users with access to that team (or finance/senior managers) will be able to view and edit it</li>
            <li>Team managers can only see contracts assigned to their team or child teams</li>
            <li>Finance and senior managers can see all contracts regardless of team assignment</li>
        </ol>
        
        <h2 id="tips-best-practices">Tips & Best Practices</h2>
        
        <h3>Organising Your Contracts</h3>
        <ul>
            <li>Use descriptive contract titles that include the local authority and service type</li>
            <li>Keep tender statuses up to date to maintain accurate workflow visibility</li>
            <li>Link single person contracts to person records for better tracking</li>
            <li>Set contract end dates to enable expiry monitoring</li>
        </ul>
        
        <h3>Managing Person Records</h3>
        <ul>
            <li>Always add at least one identifier to ensure unique identification</li>
            <li>Use CHI or SWIS numbers as primary identifiers when available</li>
            <li>Link contracts to person records to maintain continuity of care</li>
            <li>Review person payment history regularly for accuracy</li>
        </ul>
        
        <h3>Rate Management</h3>
        <ul>
            <li>Set effective dates accurately when rates change</li>
            <li>Review rate history regularly to track trends</li>
            <li>Reference local authority rate information when setting new rates</li>
            <li>Keep rates current to ensure accurate reporting</li>
        </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Update active sidebar link based on scroll position
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('.content-container h2[id]');
    const sidebarLinks = document.querySelectorAll('.howtos-sidebar-link');
    
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
