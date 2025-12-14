<?php
/**
 * Why Use This System - Promotional Page
 * Highlights the transformation from chaos to organised workflow
 */
require_once dirname(__DIR__, 2) . '/config/config.php';

$pageTitle = 'Why Use This System';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header" style="text-align: center;">
        <h1>Transform Contract Management from Chaos to Control</h1>
        <p style="color: var(--text-light); margin-top: 0.5rem; font-size: 1.1rem;">
            Stop juggling spreadsheets, calendar reminders, and mental notes.<br>
            Get a clear, organised workflow that works for you.
        </p>
    </div>
    
    <!-- The Workflow Dashboard -->
    <div style="margin: 3rem 0;">
        <div style="max-width: 1000px; margin: 0 auto;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center;">
                <div>
                    <h2 style="margin-top: 0; margin-bottom: 1.5rem;">The Workflow Dashboard</h2>
                    <p style="color: var(--text-color); line-height: 1.8; margin-bottom: 1.5rem;">
                        When you open the Contract Workflow dashboard, we present everything you need to know at a glance. At the top, you'll find quick links to your contracts, guides, and rate information - everything you need is just one click away.
                    </p>
                    <p style="color: var(--text-color); line-height: 1.8; margin-bottom: 1.5rem;">
                        The most important section is "Contracts Expiring Soon" - we highlight contracts ending in the next 6 months with colour-coded alerts. Red means urgent (less than 90 days), orange means attention needed (less than 180 days). This way, you can immediately see what needs your focus without having to dig through spreadsheets or calendars.
                    </p>
                    <p style="color: var(--text-color); line-height: 1.8; margin-bottom: 1.5rem;">
                        Below that, we organise all your contracts by their tender status. Whether you're waiting to hear back on a submission, negotiating an extension, or managing live contracts, everything is grouped logically so you can track your entire pipeline from one place.
                    </p>
                    <p style="color: var(--text-color); line-height: 1.8;">
                        We also keep an eye on data quality for you - if contracts are missing status information, we'll let you know so your pipeline view stays complete and accurate. It's like having a personal assistant that never forgets a deadline or misses an opportunity.
                    </p>
                </div>
                <div>
                    <img src="<?php echo url('assets/images/workflo-dashboard.jpeg'); ?>" alt="Contract Workflow Dashboard" style="width: 100%; height: auto; border-radius: 0.5rem; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); display: block;">
                </div>
            </div>
        </div>
    </div>
    
    <!-- What Does It Show? -->
    <div style="margin: 3rem 0;">
        <h2 style="text-align: center; margin-bottom: 2rem;">What Does the Workflow Dashboard Show?</h2>
        <p style="text-align: center; color: var(--text-light); margin-bottom: 2rem; max-width: 800px; margin-left: auto; margin-right: auto;">
            We provide a procurement pipeline dashboard that gives you a bird's-eye view of ALL your contracts and where they are in the procurement lifecycle. Think of it as your "command centre" for managing contracts.
        </p>
        
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem; margin-top: 2rem;">
            <div class="card">
                <h3 style="margin-top: 0; display: flex; align-items: center;">
                    <i class="fa-solid fa-exclamation-triangle" style="margin-right: 0.75rem; color: #ef4444; font-size: 1.5rem;"></i>
                    Contracts Expiring Soon
                </h3>
                <p style="color: var(--text-light); line-height: 1.7;">
                    We show you contracts ending in the next <strong>6 months</strong> with:
                </p>
                <ul style="margin-left: 1.5rem; color: var(--text-color);">
                    <li>Contract name and number</li>
                    <li>Which local authority</li>
                    <li>Exact end date</li>
                    <li><strong>Days remaining</strong> (colour-coded: red if &lt; 90 days, orange if &lt; 180 days)</li>
                    <li>Current tender status</li>
                </ul>
                <p style="color: var(--text-light); margin-top: 1rem; font-style: italic;">
                    You need to start extension negotiations or retender preparation <strong>6-12 months</strong> before a contract ends. We ensure nothing sneaks up on you.
                </p>
            </div>
            
            <div class="card">
                <h3 style="margin-top: 0; display: flex; align-items: center;">
                    <i class="fa-solid fa-list-check" style="margin-right: 0.75rem; color: var(--primary-color); font-size: 1.5rem;"></i>
                    Contracts by Tender Status
                </h3>
                <p style="color: var(--text-light); line-height: 1.7;">
                    We group your contracts into <strong>11 workflow stages</strong> so you can see:
                </p>
                <ul style="margin-left: 1.5rem; color: var(--text-color);">
                    <li>How many contracts are at each stage (e.g., "Tender Submitted (3)")</li>
                    <li>Contract details, local authority, procurement route, start date</li>
                    <li>Clickable rows to view full contract details</li>
                </ul>
                <p style="color: var(--text-light); margin-top: 1rem; font-style: italic;">
                    Track your entire tender pipeline like a sales dashboard.
                </p>
            </div>
            
            <div class="card">
                <h3 style="margin-top: 0; display: flex; align-items: center;">
                    <i class="fa-solid fa-link" style="margin-right: 0.75rem; color: var(--primary-color); font-size: 1.5rem;"></i>
                    Quick Links
                </h3>
                <p style="color: var(--text-light); line-height: 1.7;">
                    Prominent quick links to:
                </p>
                <ul style="margin-left: 1.5rem; color: var(--text-color);">
                    <li><strong>All Contracts</strong> - View/manage all your contracts</li>
                    <li><strong>Contracts Guide</strong> - Learn about social care procurement process</li>
                    <li><strong>Rate Information</strong> - See current local authority rates</li>
                </ul>
            </div>
            
            <div class="card">
                <h3 style="margin-top: 0; display: flex; align-items: center;">
                    <i class="fa-solid fa-exclamation-circle" style="margin-right: 0.75rem; color: #f59e0b; font-size: 1.5rem;"></i>
                    Contracts Missing Tender Status
                </h3>
                <p style="color: var(--text-light); line-height: 1.7;">
                    We send you notifications when active contracts don't have a status set, so you can update them and keep your pipeline complete.
                </p>
            </div>
        </div>
    </div>
    
    <!-- The 11 Tender Statuses Explained -->
    <div style="background: #f8fafc; border: 2px solid var(--border-color); border-radius: 0.5rem; padding: 2rem; margin: 3rem 0;">
        <h2 style="text-align: center; margin: 0 0 1rem 0;">The 11 Tender Statuses Explained</h2>
        <p style="text-align: center; color: var(--text-light); margin-bottom: 2rem; max-width: 700px; margin-left: auto; margin-right: auto;">
            Think of it like a job application process.<br><br>
            You don't need to use all of them - just the ones that apply to your situation.
        </p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; max-width: 1200px; margin: 0 auto;">
            <div class="card" style="background: white;">
                <h4 style="margin-top: 0; color: var(--primary-color);">1. Market Engagement</h4>
                <p style="color: var(--text-light); margin: 0; font-size: 0.95rem;">Heard about the job, initial chat</p>
            </div>
            <div class="card" style="background: white;">
                <h4 style="margin-top: 0; color: var(--primary-color);">2. Pre-Qualification</h4>
                <p style="color: var(--text-light); margin: 0; font-size: 0.95rem;">Submitting your CV/application</p>
            </div>
            <div class="card" style="background: white;">
                <h4 style="margin-top: 0; color: var(--primary-color);">3. Tender Submitted</h4>
                <p style="color: var(--text-light); margin: 0; font-size: 0.95rem;">Full application sent, waiting</p>
            </div>
            <div class="card" style="background: white;">
                <h4 style="margin-top: 0; color: var(--primary-color);">4. Under Evaluation</h4>
                <p style="color: var(--text-light); margin: 0; font-size: 0.95rem;">They're reviewing your application</p>
            </div>
            <div class="card" style="background: white;">
                <h4 style="margin-top: 0; color: var(--primary-color);">5. Clarification Requested</h4>
                <p style="color: var(--text-light); margin: 0; font-size: 0.95rem;">They have questions</p>
            </div>
            <div class="card" style="background: white;">
                <h4 style="margin-top: 0; color: var(--success-color);">6. Awarded</h4>
                <p style="color: var(--text-light); margin: 0; font-size: 0.95rem;">You got the job! <i class="fa-solid fa-check-circle" style="color: #10b981;"></i></p>
            </div>
            <div class="card" style="background: white;">
                <h4 style="margin-top: 0; color: var(--danger-color);">7. Lost</h4>
                <p style="color: var(--text-light); margin: 0; font-size: 0.95rem;">Someone else got it</p>
            </div>
            <div class="card" style="background: white;">
                <h4 style="margin-top: 0; color: var(--success-color);">8. Contract Live</h4>
                <p style="color: var(--text-light); margin: 0; font-size: 0.95rem;">You're working there now</p>
            </div>
            <div class="card" style="background: white;">
                <h4 style="margin-top: 0; color: var(--primary-color);">9. Extension Negotiation</h4>
                <p style="color: var(--text-light); margin: 0; font-size: 0.95rem;">Discussing staying longer</p>
            </div>
            <div class="card" style="background: white;">
                <h4 style="margin-top: 0; color: var(--warning-color);">10. Retender Pending</h4>
                <p style="color: var(--text-light); margin: 0; font-size: 0.95rem;">Contract ending, reapplying needed</p>
            </div>
            <div class="card" style="background: white;">
                <h4 style="margin-top: 0; color: var(--text-light);">11. Contract Ended</h4>
                <p style="color: var(--text-light); margin: 0; font-size: 0.95rem;">No longer working there</p>
            </div>
        </div>
    </div>
    
    <!-- Common Questions -->
    <div style="background: #f8fafc; border: 2px solid var(--border-color); border-radius: 0.5rem; padding: 2rem; margin: 3rem 0;">
        <h2 style="text-align: center; margin: 0 0 2rem 0;">Common Questions</h2>
        <div style="max-width: 800px; margin: 0 auto;">
            <div style="margin-bottom: 2rem;">
                <h3 style="color: var(--text-color); margin-bottom: 0.75rem; display: flex; align-items: start;">
                    <i class="fa-solid fa-question-circle" style="color: var(--primary-color); margin-right: 0.75rem; margin-top: 0.25rem; flex-shrink: 0;"></i>
                    <span>"I have 50 contracts - isn't this overwhelming?"</span>
                </h3>
                <div style="background: white; padding: 1.5rem; border-radius: 0.5rem; border-left: 4px solid var(--primary-color);">
                    <p style="color: var(--text-color); margin: 0; line-height: 1.7;">
                        <strong>No!</strong> We <strong>organise</strong> them so you only see what matters:
                    </p>
                    <ul style="margin: 1rem 0 0 1.5rem; color: var(--text-color);">
                        <li>Most will be "Contract Live" (just running normally)</li>
                        <li>You only focus on what needs action (expiring, under evaluation, etc.)</li>
                    </ul>
                </div>
            </div>
            
            <div style="margin-bottom: 2rem;">
                <h3 style="color: var(--text-color); margin-bottom: 0.75rem; display: flex; align-items: start;">
                    <i class="fa-solid fa-question-circle" style="color: var(--primary-color); margin-right: 0.75rem; margin-top: 0.25rem; flex-shrink: 0;"></i>
                    <span>"Do I need to update statuses constantly?"</span>
                </h3>
                <div style="background: white; padding: 1.5rem; border-radius: 0.5rem; border-left: 4px solid var(--primary-color);">
                    <p style="color: var(--text-color); margin: 0; line-height: 1.7;">
                        <strong>No!</strong> Only when things change:
                    </p>
                    <ul style="margin: 1rem 0 0 1.5rem; color: var(--text-color);">
                        <li>Submit a tender → Set to "Tender Submitted"</li>
                        <li>Win a contract → Set to "Awarded" then "Contract Live"</li>
                        <li>Contract nearing end → Set to "Extension Negotiation" or "Retender Pending"</li>
                    </ul>
                </div>
            </div>
            
            <div>
                <h3 style="color: var(--text-color); margin-bottom: 0.75rem; display: flex; align-items: start;">
                    <i class="fa-solid fa-question-circle" style="color: var(--primary-color); margin-right: 0.75rem; margin-top: 0.25rem; flex-shrink: 0;"></i>
                    <span>"What if I don't know the tender status?"</span>
                </h3>
                <div style="background: white; padding: 1.5rem; border-radius: 0.5rem; border-left: 4px solid var(--primary-color);">
                    <p style="color: var(--text-color); margin: 0; line-height: 1.7;">
                        Use these simple defaults:
                    </p>
                    <ul style="margin: 1rem 0 0 1.5rem; color: var(--text-color);">
                        <li>Active contracts → "Contract Live"</li>
                        <li>Submitted tenders → "Tender Submitted"</li>
                        <li>Ending contracts → "Extension Negotiation"</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Time Savings Comparison -->
    <div style="margin: 3rem 0;">
        <h2 style="text-align: center; margin: 0 0 2rem 0;">Time Savings: Before vs After</h2>
        <div style="max-width: 900px; margin: 0 auto;">
            <table class="table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="text-align: left; width: 30%;">Task</th>
                        <th style="text-align: center; width: 20%; white-space: nowrap;">Without System</th>
                        <th style="text-align: center; width: 20%; white-space: nowrap;">With System</th>
                        <th style="text-align: center; width: 30%; white-space: nowrap;">Time Saved</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-weight: 600;">Find expiring contracts</td>
                        <td style="text-align: center; color: #991b1b;">30 min</td>
                        <td style="text-align: center; color: #065f46;">10 seconds</td>
                        <td style="text-align: center; font-weight: 600; color: #065f46;">29 min 50 sec</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600;">Check tender pipeline</td>
                        <td style="text-align: center; color: #991b1b;">20 min</td>
                        <td style="text-align: center; color: #065f46;">5 seconds</td>
                        <td style="text-align: center; font-weight: 600; color: #065f46;">19 min 55 sec</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600;">Prepare team meeting</td>
                        <td style="text-align: center; color: #991b1b;">45 min</td>
                        <td style="text-align: center; color: #065f46;">10 min</td>
                        <td style="text-align: center; font-weight: 600; color: #065f46;">35 minutes</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600;">Answer pipeline question</td>
                        <td style="text-align: center; color: #991b1b;">30 min</td>
                        <td style="text-align: center; color: #065f46;">2 min</td>
                        <td style="text-align: center; font-weight: 600; color: #065f46;">28 minutes</td>
                    </tr>
                    <tr style="background: #f0fdf4; border-top: 2px solid #10b981;">
                        <td style="font-weight: 700; font-size: 1.1rem;">Total Time Saved Per Week</td>
                        <td style="text-align: center; font-weight: 700; color: #991b1b;">~2 hours/week</td>
                        <td style="text-align: center; font-weight: 700; color: #065f46;">~5 min/day</td>
                        <td style="text-align: center; font-weight: 700; font-size: 1.1rem; color: #065f46;">~7+ hours/week</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Call to Action -->
    <div style="background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%); color: white; padding: 3rem 2rem; margin: 3rem 0; border-radius: 0.5rem; text-align: center;">
        <h2 style="color: white; margin: 0 0 1rem 0;">Ready to Transform Your Contract Management?</h2>
        <p style="color: rgba(255,255,255,0.9); font-size: 1.1rem; margin-bottom: 2rem;">
            Get your organisation set up and start saving 7+ hours per week.<br>
            Transform from reactive crisis management to proactive planning.
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="<?php echo url('index.php#contact-organisation'); ?>" class="btn" style="background: #10b981; color: white; padding: 1rem 2.5rem; font-weight: 600; text-decoration: none; border-radius: 0.5rem; display: inline-block; font-size: 1.1rem;">
                Get Your Organisation Set Up
            </a>
            <?php if (Auth::isLoggedIn()): ?>
                <a href="<?php echo url('contract-workflow.php'); ?>" class="btn" style="background: rgba(255,255,255,0.2); color: white; padding: 1rem 2.5rem; font-weight: 600; text-decoration: none; border: 2px solid rgba(255,255,255,0.3); border-radius: 0.5rem; display: inline-block; font-size: 1.1rem;">
                    View Workflow Dashboard
                </a>
            <?php else: ?>
                <a href="<?php echo url('pages/social-care-contracts-guide.php'); ?>" class="btn" style="background: rgba(255,255,255,0.2); color: white; padding: 1rem 2.5rem; font-weight: 600; text-decoration: none; border: 2px solid rgba(255,255,255,0.3); border-radius: 0.5rem; display: inline-block; font-size: 1.1rem;">
                    Learn More About Contracts
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    div[style*="grid-template-columns: 1fr 1fr"],
    div[style*="grid-template-columns: repeat(2, 1fr)"],
    div[style*="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr))"] {
        grid-template-columns: 1fr !important;
    }
    
    table {
        font-size: 0.9rem;
    }
    
    table th,
    table td {
        padding: 0.75rem 0.5rem !important;
    }
}
</style>

<?php include INCLUDES_PATH . '/footer.php'; ?>
