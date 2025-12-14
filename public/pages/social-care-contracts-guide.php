<?php
/**
 * Social Care Contracts Guide
 * Comprehensive guide to understanding and working with social care contracts in Scotland
 */
require_once dirname(__DIR__, 2) . '/config/config.php';

// Allow public access to the guide
$isLoggedIn = Auth::isLoggedIn();

$pageTitle = 'Social Care Contracts Guide';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1>Social Care Contracts in Scotland</h1>
                <p style="color: var(--text-light); margin-top: 0.5rem;">
                    A comprehensive guide to understanding how social care contracts work and how to manage them effectively
                </p>
                <p style="margin-top: 0.5rem;">
                    <a href="<?php echo url('pages/glossary.php'); ?>" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem;">
                        <i class="fa-solid fa-book" style="margin-right: 0.25rem;"></i>View Glossary of Terms
                    </a>
                </p>
            </div>
            <div style="text-align: right;">
                <p style="font-size: 0.875rem; color: var(--text-light); margin: 0; line-height: 1.5;">
                    <i class="fa-solid fa-calendar" style="margin-right: 0.25rem;"></i>
                    Last updated: <?php echo date('F Y'); ?> • Based on legislation current as of January 2025
                </p>
            </div>
        </div>
    </div>
    
    <div style="display: flex; gap: 2rem; align-items: start;">
        <!-- Sticky Sidebar Navigation -->
        <aside id="guide-sidebar" style="width: 250px; flex-shrink: 0; position: sticky; top: 100px; max-height: calc(100vh - 120px); overflow-y: auto; background: var(--bg-light); padding: 1.5rem; border-radius: 0.5rem; border: 1px solid var(--border-color);">
            <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem;">Contents</h3>
            <nav style="display: flex; flex-direction: column; gap: 0.5rem;">
                <a href="#how-contracts-work" class="sidebar-nav-link" style="text-decoration: none; padding: 0.5rem 0.75rem; border-radius: 0.375rem; transition: all 0.2s; font-size: 0.9rem;">1. How Contracts Work</a>
                <a href="#procurement-routes" class="sidebar-nav-link" style="text-decoration: none; padding: 0.5rem 0.75rem; border-radius: 0.375rem; transition: all 0.2s; font-size: 0.9rem;">2. Procurement Routes</a>
                <a href="#tender-process" class="sidebar-nav-link" style="text-decoration: none; padding: 0.5rem 0.75rem; border-radius: 0.375rem; transition: all 0.2s; font-size: 0.9rem;">3. Tender Process</a>
                <a href="#rate-variations" class="sidebar-nav-link" style="text-decoration: none; padding: 0.5rem 0.75rem; border-radius: 0.375rem; transition: all 0.2s; font-size: 0.9rem;">4. Rate Variations</a>
                <a href="#tupe-quality" class="sidebar-nav-link" style="text-decoration: none; padding: 0.5rem 0.75rem; border-radius: 0.375rem; transition: all 0.2s; font-size: 0.9rem;">5. TUPE & Quality</a>
                <a href="#dispute-resolution" class="sidebar-nav-link" style="text-decoration: none; padding: 0.5rem 0.75rem; border-radius: 0.375rem; transition: all 0.2s; font-size: 0.9rem;">6. Dispute Resolution</a>
                <a href="#managing-contracts" class="sidebar-nav-link" style="text-decoration: none; padding: 0.5rem 0.75rem; border-radius: 0.375rem; transition: all 0.2s; font-size: 0.9rem;">7. Managing Contracts</a>
                <a href="#best-practices" class="sidebar-nav-link" style="text-decoration: none; padding: 0.5rem 0.75rem; border-radius: 0.375rem; transition: all 0.2s; font-size: 0.9rem;">8. Best Practices</a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <div style="flex: 1; min-width: 0;">
        
        <!-- Section 1: How Contracts Work -->
        <section id="how-contracts-work" style="margin-bottom: 3rem;">
            <h2>1. How Social Care Contracts Work</h2>
            
            <h3>What is a Social Care Contract?</h3>
            <p>A social care contract is a formal agreement between a local authority (or Integration Joint Board) and a care provider to deliver support services to people in need. These contracts define:</p>
            <div style="background: #fef3c7; border: 1px solid var(--warning-color); padding: 1rem; border-radius: 0.375rem; margin-top: 1rem;">
                <p style="margin: 0;"><strong>Important Note:</strong> While Integration Joint Boards (IJBs) create strategic commissioning plans, they <strong>cannot hold contracts themselves</strong>. The actual contract is always with the Local Authority or Health Board, even when the IJB has done the strategic planning.</p>
            </div>
            <ul style="margin-left: 2rem; margin-top: 1rem;">
                <li>The type and level of support to be provided</li>
                <li>The rates to be paid</li>
                <li>The duration of the contract</li>
                <li>Quality standards and monitoring requirements</li>
                <li>Terms and conditions</li>
            </ul>
            
            <h3 style="margin-top: 2rem;">The Contracting Process</h3>
            <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 0.5rem; margin-top: 1rem;">
                <ol style="margin-left: 1.5rem;">
                    <li style="margin-bottom: 1rem;">
                        <strong>Strategic Commissioning Plan</strong><br>
                        Local authorities assess what services are needed in their area
                    </li>
                    <li style="margin-bottom: 1rem;">
                        <strong>Needs Assessment</strong><br>
                        Determining how much capacity is required
                    </li>
                    <li style="margin-bottom: 1rem;">
                        <strong>Market Engagement</strong><br>
                        Local authorities talk to providers about what's possible
                    </li>
                    <li style="margin-bottom: 1rem;">
                        <strong>Procurement Route Decision</strong><br>
                        Choosing how to award the contract (tender, framework, direct award, etc.)
                    </li>
                    <li style="margin-bottom: 1rem;">
                        <strong>Evaluation</strong><br>
                        Assessing bids based on quality, price, and social value
                    </li>
                    <li style="margin-bottom: 1rem;">
                        <strong>Contract Award</strong><br>
                        Contract is awarded to the successful provider
                    </li>
                    <li style="margin-bottom: 1rem;">
                        <strong>Monitoring & Review</strong><br>
                        Ongoing monitoring of contract delivery and quality
                    </li>
                </ol>
            </div>
        </section>
        
        <!-- Section 2: Procurement Routes -->
        <section id="procurement-routes" style="margin-bottom: 3rem;">
            <h2>2. Procurement Routes</h2>
            <p>There are multiple ways local authorities can award contracts. Understanding these helps you navigate the system:</p>
            
            <div style="display: grid; gap: 1.5rem; margin-top: 1.5rem;">
                <div class="card" style="margin: 0;">
                    <h3>Competitive Tendering</h3>
                    <p><strong>Traditional approach:</strong> Local authorities advertise contracts, multiple providers submit bids, and contracts are awarded based on price AND quality (cannot be price alone since 2016).</p>
                    <p><strong>Types:</strong></p>
                    <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                        <li><strong>Open Procedure:</strong> Any provider can submit a tender</li>
                        <li><strong>Restricted Procedure:</strong> Two-stage process with pre-qualification</li>
                        <li><strong>Competitive Dialogue:</strong> For complex contracts where requirements may evolve</li>
                    </ul>
                    <p style="margin-top: 1rem; padding: 0.75rem; background: var(--bg-light); border-radius: 0.375rem;">
                        <strong>Note:</strong> Despite rules requiring quality consideration, tight budgets often mean price becomes the dominant factor.
                    </p>
                </div>
                
                <div class="card" style="margin: 0;">
                    <h3>Framework Agreements</h3>
                    <p><strong>Pre-qualified list:</strong> Providers bid to get onto a framework. Local authorities can then "call off" from the framework without re-tendering each time.</p>
                    <p><strong>Examples:</strong></p>
                    <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                        <li><strong>Scotland Excel:</strong> Care and Support Framework (2024-2030) has 152 providers. Specifically for local authorities - all 32 Scottish councils can use it.</li>
                        <li><strong>Individual Authority Frameworks:</strong> Many local authorities also maintain their own frameworks in addition to Scotland Excel.</li>
                    </ul>
                    <p style="margin-top: 0.75rem;"><strong>Benefits:</strong> Faster procurement, reduced administrative burden, but you must be on the framework first.</p>
                </div>
                
                <div class="card" style="margin: 0;">
                    <h3>Direct Awards</h3>
                    <p><strong>No competition:</strong> For individual placements where a service user chooses a specific provider under Self-Directed Support (SDS).</p>
                    <p><strong>SDS Context:</strong> This applies when someone is using <strong>SDS Option 1</strong> (direct payment) or <strong>SDS Option 2</strong> (directed support), not Option 3 (local authority arranged).</p>
                    <p>No competitive process needed - the person's choice drives the contract.</p>
                </div>
                
                <div class="card" style="margin: 0;">
                    <h3>Spot Purchasing</h3>
                    <p><strong>Emergency/urgent:</strong> For emergency or urgent placements. Negotiated rates, often higher than standard contracts.</p>
                </div>
                
                <div class="card" style="margin: 0;">
                    <h3>Block Contracts</h3>
                    <p><strong>Set capacity:</strong> Authority buys set capacity (e.g., 100 hours/week). Can be negotiated or tendered.</p>
                </div>
                
                <div class="card" style="margin: 0;">
                    <h3>Dynamic Purchasing Systems</h3>
                    <p><strong>Electronic system:</strong> An electronic system for repeat purchases where providers can join at any time. More flexible than traditional frameworks.</p>
                    <p><strong>Benefits:</strong> Authorities can quickly find providers, and providers can join without waiting for a new framework round.</p>
                </div>
                
                <div class="card" style="margin: 0;">
                    <h3>Public Social Partnerships</h3>
                    <p><strong>Alternative approach:</strong> Mentioned in guidance as an alternative to competitive tendering. Focuses on collaboration and innovation rather than price competition.</p>
                    <p><strong>Purpose:</strong> Encourages long-term partnerships between public bodies and social enterprises/third sector organisations.</p>
                </div>
            </div>
            
            <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 0.5rem; margin-top: 1.5rem;">
                <h3>Procurement Thresholds</h3>
                <p><strong>Light-touch regime:</strong> Applies to social care contracts over <strong>£663,540</strong>. This is a simplified procurement process compared to standard public procurement rules.</p>
                <p><strong>Under £50,000:</strong> Contracts under £50,000 are covered by the Procurement Reform (Scotland) Act 2014 only, not EU-derived regulations.</p>
                <p><strong>£50,000 - £663,540:</strong> Covered by Procurement Reform (Scotland) Act 2014 with sustainable procurement duty and community benefits requirements.</p>
            </div>
        </section>
        
        <!-- Section 3: Tender Process -->
        <section id="tender-process" style="margin-bottom: 3rem;">
            <h2>3. The Tender Process</h2>
            
            <h3>Tender Status Workflow</h3>
            <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 0.5rem; margin-top: 1rem;">
                <div style="display: grid; gap: 1rem;">
                    <div class="workflow-step" style="padding: 1rem; background: white; border-left: 3px solid #2563eb; border-radius: 0 0.375rem 0.375rem 0; box-shadow: none; border-top: none; border-bottom: none; border-right: none;">
                        <strong>1. Market Engagement</strong> - Initial discussions with local authority about requirements
                    </div>
                    <div class="workflow-step" style="padding: 1rem; background: white; border-left: 3px solid #2563eb; border-radius: 0 0.375rem 0.375rem 0; box-shadow: none; border-top: none; border-bottom: none; border-right: none;">
                        <strong>2. Pre-Qualification</strong> - Submitting pre-qualification questionnaire
                    </div>
                    <div class="workflow-step" style="padding: 1rem; background: white; border-left: 3px solid #2563eb; border-radius: 0 0.375rem 0.375rem 0; box-shadow: none; border-top: none; border-bottom: none; border-right: none;">
                        <strong>3. Tender Submitted</strong> - Tender submitted, awaiting evaluation
                    </div>
                    <div class="workflow-step" style="padding: 1rem; background: white; border-left: 3px solid #2563eb; border-radius: 0 0.375rem 0.375rem 0; box-shadow: none; border-top: none; border-bottom: none; border-right: none;">
                        <strong>4. Under Evaluation</strong> - Local authority evaluating tender
                    </div>
                    <div class="workflow-step" style="padding: 1rem; background: white; border-left: 3px solid #2563eb; border-radius: 0 0.375rem 0.375rem 0; box-shadow: none; border-top: none; border-bottom: none; border-right: none;">
                        <strong>5. Awarded/Lost</strong> - Contract awarded or awarded to another provider
                    </div>
                    <div class="workflow-step" style="padding: 1rem; background: white; border-left: 3px solid #2563eb; border-radius: 0 0.375rem 0.375rem 0; box-shadow: none; border-top: none; border-bottom: none; border-right: none;">
                        <strong>6. Contract Live</strong> - Contract is active and operational
                    </div>
                    <div class="workflow-step" style="padding: 1rem; background: white; border-left: 3px solid #2563eb; border-radius: 0 0.375rem 0.375rem 0; box-shadow: none; border-top: none; border-bottom: none; border-right: none;">
                        <strong>7. Extension/Retender</strong> - Negotiating extension or preparing for retender
                    </div>
                </div>
            </div>
            
            <h3 style="margin-top: 2rem;">Evaluation Criteria</h3>
            <p>Contracts must consider:</p>
            <ul style="margin-left: 2rem; margin-top: 1rem;">
                <li><strong>Quality (must be considered):</strong> Staff training, experience, Care Inspectorate ratings, policies, innovation</li>
                <li><strong>Price (cannot be sole factor):</strong> Despite guidance, tight budgets often make this dominant</li>
                <li><strong>Social Value/Community Benefits:</strong> Employment opportunities, training placements, local sourcing, environmental commitments</li>
            </ul>
            
            <div style="background: #fee2e2; border: 1px solid #ef4444; padding: 1rem; border-radius: 0.375rem; margin-top: 1.5rem;">
                <p style="margin: 0;"><strong>Important:</strong> The Feeley Review (2021) found that competitive tendering driven by price is undermining the sector. There's a push for "ethical commissioning" - collaborative not competitive approaches.</p>
            </div>
        </section>
        
        <!-- Section 4: Rate Variations -->
        <section id="rate-variations" style="margin-bottom: 3rem;">
            <h2>4. Understanding Rate Variations</h2>
            
            <h3>Why Rates Vary Across Scotland</h3>
            <p>Despite the national minimum worker pay (£12.60/hour), what local authorities actually <strong>pay providers</strong> varies enormously:</p>
            
            <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 0.5rem; margin-top: 1rem;">
                <h4>Key Factors:</h4>
                <ul style="margin-left: 2rem; margin-top: 1rem;">
                    <li><strong>No National Framework:</strong> Each local authority sets their own rates</li>
                    <li><strong>Budget Constraints:</strong> Different authorities have different budgets</li>
                    <li><strong>Local Cost of Living:</strong> Urban vs rural differences</li>
                    <li><strong>Contract Type:</strong> Framework agreements vs individually negotiated vs spot purchases</li>
                    <li><strong>Service Type:</strong> Care at home, supported living, residential care, specialist services</li>
                    <li><strong>Historic Contracts:</strong> Legacy rates from previous agreements</li>
                </ul>
            </div>
            
            <h3 style="margin-top: 2rem;">Rate Variations for the Same Provider</h3>
            <p>It's important to understand that the same provider might receive different rates from the same local authority depending on:</p>
            <div class="card" style="margin-top: 1rem;">
                <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                    <li><strong>When the contract was signed:</strong> Legacy rates from older contracts may differ from current rates</li>
                    <li><strong>Framework vs bespoke:</strong> Rates from framework call-offs may differ from individually negotiated contracts</li>
                    <li><strong>Service complexity/specialism:</strong> Specialist services (e.g., dementia care, learning disabilities) may command higher rates</li>
                    <li><strong>Geographic factors:</strong> Travel distances in rural areas may result in different rates</li>
                    <li><strong>Contract duration:</strong> Longer-term contracts may have different rate structures</li>
                </ul>
                <?php if ($isLoggedIn): ?>
                    <p style="margin-top: 1rem; padding: 0.75rem; background: var(--bg-light); border-radius: 0.375rem;">
                        <i class="fa-solid fa-info-circle" style="margin-right: 0.5rem; color: var(--primary-color);"></i>
                        <strong>In SCCM:</strong> Track rate variations using the <a href="<?php echo url('rates.php'); ?>" style="color: var(--primary-color);">Rates Management</a> page. Record different rates for the same local authority based on contract type and complexity.
                    </p>
                <?php endif; ?>
            </div>
            
            <h3 style="margin-top: 2rem;">Reference Rates</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-top: 1rem;">
                <div class="card" style="margin: 0;">
                    <h4>Worker Minimum</h4>
                    <p style="font-size: 1.5rem; font-weight: bold; color: var(--primary-color);">£12.60/hour</p>
                    <p style="font-size: 0.9rem; color: var(--text-light);">Mandated minimum for all social care workers (April 2025)</p>
                </div>
                <div class="card" style="margin: 0;">
                    <h4>Homecare Association Recommended</h4>
                    <p style="font-size: 1.5rem; font-weight: bold; color: var(--warning-color);">£32.88/hour</p>
                    <p style="font-size: 0.9rem; color: var(--text-light);">Minimum price to providers (covers wages, travel, on-costs)</p>
                </div>
                <div class="card" style="margin: 0;">
                    <h4>Actual LA Rates</h4>
                    <p style="font-size: 1.5rem; font-weight: bold; color: var(--text-light);">Varies</p>
                    <p style="font-size: 0.9rem; color: var(--text-light);">Somewhere in between, set by each authority</p>
                </div>
            </div>
            
            <p style="margin-top: 1.5rem; padding: 1rem; background: #fef3c7; border: 1px solid var(--warning-color); border-radius: 0.375rem;">
                <strong>Research Finding:</strong> Homecare Association research found only <strong>1% of homecare contracts</strong> with public bodies pay rates that enable compliance with National Living Wage, financial sustainability, and quality care delivery.
            </p>
        </section>
        
        <!-- Section 5: TUPE & Quality Monitoring -->
        <section id="tupe-quality" style="margin-bottom: 3rem;">
            <h2>5. TUPE & Quality Monitoring</h2>
            
            <h3>TUPE (Transfer of Undertakings Protection of Employment)</h3>
            <div class="card" style="margin-top: 1rem;">
                <p>When contracts change hands between providers, staff transfer under TUPE regulations. This is a critical consideration in contract management:</p>
                <ul style="margin-left: 2rem; margin-top: 1rem;">
                    <li><strong>Staff Transfer:</strong> Employees automatically transfer to the new provider with their existing terms and conditions</li>
                    <li><strong>Pension Rights:</strong> Staff retain their pension rights and continuity of service</li>
                    <li><strong>Consultation Requirements:</strong> Both outgoing and incoming providers must consult with affected staff</li>
                    <li><strong>Cost Implications:</strong> TUPE can affect contract pricing as new providers inherit existing staff costs</li>
                </ul>
                <p style="margin-top: 1rem; padding: 0.75rem; background: var(--bg-light); border-radius: 0.375rem;">
                    <strong>Important:</strong> When taking over a contract from another provider, factor in TUPE costs and obligations in your pricing and planning.
                </p>
            </div>
            
            <h3 style="margin-top: 2rem;">Quality Monitoring</h3>
            <div class="card" style="margin-top: 1rem;">
                <p>The <strong>Care Inspectorate</strong> plays a crucial role in quality standards:</p>
                <ul style="margin-left: 2rem; margin-top: 1rem;">
                    <li><strong>Inspection Ratings:</strong> Providers receive ratings (Excellent, Very Good, Good, Adequate, Weak, Unsatisfactory) which are publicly available</li>
                    <li><strong>Contract Impact:</strong> Care Inspectorate ratings are often used as evaluation criteria in tenders</li>
                    <li><strong>Ongoing Monitoring:</strong> Regular inspections ensure quality standards are maintained</li>
                    <li><strong>Enforcement:</strong> Poor ratings can result in enforcement action and may affect contract renewals</li>
                </ul>
                <p style="margin-top: 1rem;">
                    <strong>Best Practice:</strong> Maintain high Care Inspectorate ratings as they directly impact your ability to win and retain contracts. Many local authorities require minimum ratings (e.g., "Good" or above) to be eligible for contracts.
                </p>
            </div>
        </section>
        
        <!-- Section 6: Dispute Resolution -->
        <section id="dispute-resolution" style="margin-bottom: 3rem;">
            <h2>6. Dispute Resolution</h2>
            
            <p>Contract disputes can arise over various issues. Understanding the resolution process is important:</p>
            
            <div class="card" style="margin-top: 1rem;">
                <h4>Common Dispute Areas</h4>
                <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                    <li>Rate changes and price reviews</li>
                    <li>Service delivery standards</li>
                    <li>Contract variations</li>
                    <li>Payment disputes</li>
                    <li>Termination issues</li>
                </ul>
            </div>
            
            <div class="card" style="margin-top: 1rem;">
                <h4>Resolution Process</h4>
                <ol style="margin-left: 2rem; margin-top: 0.5rem;">
                    <li style="margin-bottom: 0.5rem;"><strong>Internal Discussion:</strong> First step is usually direct discussion with the local authority commissioner</li>
                    <li style="margin-bottom: 0.5rem;"><strong>Formal Complaint:</strong> If unresolved, follow the local authority's formal complaints procedure</li>
                    <li style="margin-bottom: 0.5rem;"><strong>Mediation:</strong> Some contracts include mediation clauses for dispute resolution</li>
                    <li style="margin-bottom: 0.5rem;"><strong>Legal Action:</strong> As a last resort, legal action may be necessary, though this is costly and time-consuming</li>
                </ol>
                <p style="margin-top: 1rem; padding: 0.75rem; background: #fee2e2; border: 1px solid #ef4444; border-radius: 0.375rem;">
                    <strong>Prevention is Better:</strong> Clear contract terms, regular communication, and documented agreements help prevent disputes. Keep detailed records of all contract communications and variations.
                </p>
            </div>
            
            <?php if ($isLoggedIn): ?>
                <div style="background: var(--bg-light); padding: 1rem; border-radius: 0.375rem; margin-top: 1rem;">
                    <p style="margin: 0;">
                        <i class="fa-solid fa-info-circle" style="margin-right: 0.5rem; color: var(--primary-color);"></i>
                        <strong>In SCCM:</strong> Document contract variations and communications in the contract description field. Use the <a href="<?php echo url('contracts.php'); ?>" style="color: var(--primary-color);">Contracts</a> page to track all contract details for reference during any disputes.
                    </p>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Section 7: Managing Contracts -->
        <section id="managing-contracts" style="margin-bottom: 3rem;">
            <h2>5. Managing Contracts in This System</h2>
            
            <h3>Contract Fields Explained</h3>
            <div style="margin-top: 1rem;">
                <div class="card" style="margin-bottom: 1rem;">
                    <h4>Basic Information</h4>
                    <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                        <li><strong>Title:</strong> Descriptive name for the contract</li>
                        <li><strong>Contract Number:</strong> Reference number from local authority</li>
                        <li><strong>Contract Type:</strong> Type of service (Waking Hours, Sleepover, Support, etc.)</li>
                        <li><strong>Local Authority:</strong> Which authority the contract is with</li>
                    </ul>
                </div>
                
                <div class="card" style="margin-bottom: 1rem;">
                    <h4>Procurement Information</h4>
                    <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                        <li><strong>Procurement Route:</strong> How the contract was awarded (tender, framework, direct award, etc.)</li>
                        <li><strong>Tender Status:</strong> Current stage in the contract lifecycle</li>
                        <li><strong>Framework Agreement ID:</strong> If awarded from a framework, the framework reference</li>
                    </ul>
                </div>
                
                <div class="card" style="margin-bottom: 1rem;">
                    <h4>Contract Terms</h4>
                    <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                        <li><strong>Start/End Dates:</strong> Contract duration</li>
                        <li><strong>Extension Options:</strong> Whether contract can be extended</li>
                        <li><strong>Price Review Mechanism:</strong> How rates can be reviewed</li>
                        <li><strong>Inflation Indexation:</strong> Whether rates increase with inflation</li>
                    </ul>
                </div>
            </div>
            
            <h3 style="margin-top: 2rem;">Workflow Tips</h3>
            <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 0.5rem; margin-top: 1rem;">
                <ol style="margin-left: 1.5rem;">
                    <li style="margin-bottom: 1rem;">
                        <strong>Track Tender Status:</strong> Update the tender status as you progress through the procurement process. This helps you see where each contract is in the pipeline.
                        <?php if ($isLoggedIn): ?>
                            Use the <a href="<?php echo url('contract-workflow.php'); ?>" style="color: var(--primary-color);">Workflow Dashboard</a> to view contracts by tender status.
                        <?php endif; ?>
                    </li>
                    <li style="margin-bottom: 1rem;">
                        <strong>Record Procurement Route:</strong> Understanding how you won the contract helps with future tenders and rate negotiations.
                        <?php if ($isLoggedIn): ?>
                            Set this when creating or editing contracts in the <a href="<?php echo url('contracts.php'); ?>" style="color: var(--primary-color);">Contracts</a> page.
                        <?php endif; ?>
                    </li>
                    <li style="margin-bottom: 1rem;">
                        <strong>Set Rate Reminders:</strong> Use the rates system to track when rates change and set reminders for price reviews.
                        <?php if ($isLoggedIn): ?>
                            Manage rates in the <a href="<?php echo url('rates.php'); ?>" style="color: var(--primary-color);">Rates</a> section.
                        <?php endif; ?>
                    </li>
                    <li style="margin-bottom: 1rem;">
                        <strong>Link People to Contracts:</strong> When creating contracts, link the people being supported. This helps track their journey across local authorities.
                        <?php if ($isLoggedIn): ?>
                            Add people in the <a href="<?php echo url('people.php'); ?>" style="color: var(--primary-color);">People</a> section, then link them when creating single-person contracts.
                        <?php endif; ?>
                    </li>
                    <li style="margin-bottom: 1rem;">
                        <strong>Monitor Contract End Dates:</strong> Set up alerts for contracts ending soon so you can prepare for extensions or retenders.
                        <?php if ($isLoggedIn): ?>
                            View contracts expiring in the next 6 months on the <a href="<?php echo url('contract-workflow.php'); ?>" style="color: var(--primary-color);">Workflow Dashboard</a>.
                        <?php endif; ?>
                    </li>
                </ol>
            </div>
        </section>
        
        <!-- Section 8: Best Practices -->
        <section id="best-practices" style="margin-bottom: 3rem;">
            <h2>8. Best Practices</h2>
            
            <h3>Contract Management</h3>
            <div style="display: grid; gap: 1rem; margin-top: 1rem;">
                <div class="card" style="margin: 0;">
                    <h4><i class="fa-solid fa-check-circle" style="color: #10b981; margin-right: 0.5rem;"></i>Do</h4>
                    <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                        <li>Keep detailed records of all contract terms</li>
                        <li>Track rate changes over time</li>
                        <li>Document any variations or amendments</li>
                        <li>Monitor contract end dates well in advance</li>
                        <li>Maintain relationships with local authority commissioners</li>
                        <li>Record evaluation criteria and quality scores</li>
                    </ul>
                </div>
                
                <div class="card" style="margin: 0;">
                    <h4>✗ Don't</h4>
                    <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                        <li>Accept rates that don't cover your costs</li>
                        <li>Ignore contract end dates</li>
                        <li>Forget to document rate reviews</li>
                        <li>Lose track of framework agreement renewals</li>
                        <li>Miss opportunities for contract extensions</li>
                    </ul>
                </div>
            </div>
            
            <h3 style="margin-top: 2rem;">Understanding Your Position</h3>
            <p>Use the <a href="<?php echo url('pages/local-authority-rates.php'); ?>" style="color: var(--primary-color);">Local Authority Rates Information</a> page to:</p>
            <ul style="margin-left: 2rem; margin-top: 1rem;">
                <li>Compare your rates against reference rates</li>
                <li>Understand minimum worker pay requirements</li>
                <li>See what other authorities are paying</li>
                <li>Stay informed about rate changes and updates</li>
            </ul>
        </section>
        
        <!-- Additional Resources -->
        <section style="margin-bottom: 2rem;">
            <h2>Additional Resources</h2>
            <div class="card" style="margin-top: 1rem;">
                <h3>Key Legislation & Guidance</h3>
                <ul style="margin-left: 2rem; margin-top: 1rem;">
                    <li><strong>Public Contracts (Scotland) Regulations 2015:</strong> "Light-touch regime" for social care over £663,540</li>
                    <li><strong>Procurement Reform (Scotland) Act 2014:</strong> Sustainable procurement duty, community benefits, Fair Work considerations</li>
                    <li><strong>Social Care (Self-directed Support) (Scotland) Act 2013:</strong> Requirement to offer choice and ensure range of providers</li>
                    <li><strong>Procurement of care and support services: best practice guidance</strong> (updated 2021) - Published on gov.scot</li>
                </ul>
                
                <h3 style="margin-top: 2rem;">Where to Find Opportunities</h3>
                <ul style="margin-left: 2rem; margin-top: 1rem;">
                    <li><a href="https://www.publiccontractsscotland.gov.uk" target="_blank">Public Contracts Scotland</a> - Main portal for tenders</li>
                    <li><a href="https://www.scotland-excel.org.uk" target="_blank">Scotland Excel</a> - Framework agreements</li>
                    <li>Individual Local Authority websites</li>
                </ul>
                
                <h3 style="margin-top: 2rem;">Current Changes</h3>
                <div style="background: var(--bg-light); padding: 1rem; border-radius: 0.375rem; margin-top: 1rem;">
                    <p><strong>National Care Service Bill:</strong> Going through Parliament (Stage 2 starting Feb 2025). <strong>Update (January 2025):</strong> The Bill has been significantly scaled back from original plans. It will not create a full National Care Service but will focus on standards and an Advisory Board instead. It will still set national ethical commissioning requirements.</p>
                    <p style="margin-top: 1rem;"><strong>Move Away from Competitive Tendering:</strong> Policy direction set for "ethical commissioning" - collaborative not competitive approaches. Implementation is patchy across different local authorities.</p>
                </div>
            </div>
        </section>
        </div>
    </div>
</div>

<style>
/* Sidebar Navigation Styles */
.sidebar-nav-link {
    display: block;
    color: var(--text-color);
}

.sidebar-nav-link:hover:not(.active) {
    background-color: rgba(37, 99, 235, 0.1);
    color: var(--primary-color);
}

.sidebar-nav-link.active {
    background-color: var(--primary-color) !important;
    color: #ffffff !important;
    font-weight: 500;
}

.sidebar-nav-link.active:hover {
    background-color: var(--secondary-color) !important;
    color: #ffffff !important;
}

/* Custom scrollbar for sidebar */
#guide-sidebar::-webkit-scrollbar {
    width: 6px;
}

#guide-sidebar::-webkit-scrollbar-track {
    background: transparent;
}

#guide-sidebar::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 3px;
}

#guide-sidebar::-webkit-scrollbar-thumb:hover {
    background: var(--text-light);
}

/* Smooth scrolling */
html {
    scroll-behavior: smooth;
}

/* Offset for anchor links to account for sticky header */
section[id] {
    scroll-margin-top: 120px;
}

/* Flat border style for workflow steps */
.workflow-step {
    border-left: 3px solid #2563eb !important;
    border-top: none !important;
    border-bottom: none !important;
    border-right: none !important;
    box-shadow: none !important;
    border-image: none !important;
    outline: none !important;
    border-radius: 0 0.375rem 0.375rem 0 !important;
}

/* Responsive: Hide sidebar on mobile */
@media (max-width: 1024px) {
    #guide-sidebar {
        display: none;
    }
    
    div[style*="display: flex"] {
        display: block !important;
    }
    
    /* Improve content readability on mobile */
    section {
        margin-bottom: 2rem !important;
    }
    
    h2 {
        font-size: 1.5rem;
        line-height: 1.3;
        margin-top: 1.5rem;
    }
    
    h3 {
        font-size: 1.25rem;
        line-height: 1.4;
        margin-top: 1.25rem;
    }
    
    p {
        line-height: 1.7;
        margin-bottom: 1rem;
    }
    
    ul, ol {
        margin-left: 1.5rem;
        line-height: 1.7;
    }
    
    li {
        margin-bottom: 0.5rem;
    }
}

/* Mobile menu toggle button */
.mobile-toc-toggle {
    display: none;
}

@media (max-width: 1024px) {
    .mobile-toc-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 50%;
        width: 56px;
        height: 56px;
        font-size: 1.5rem;
        cursor: pointer;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        z-index: 1000;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .mobile-toc-toggle:hover {
        transform: scale(1.1);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    
    #guide-sidebar.mobile-open {
        display: block;
        position: fixed;
        top: 0;
        left: 0;
        width: 280px;
        height: 100vh;
        z-index: 1001;
        max-height: 100vh;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }
    
    .mobile-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
    }
    
    .mobile-overlay.active {
        display: block;
    }
}
</style>

<button class="mobile-toc-toggle" id="mobileTocToggle" style="display: none;" onclick="toggleMobileSidebar()">
    <i class="fa-solid fa-list"></i>
</button>

<div class="mobile-overlay" id="mobileOverlay" onclick="toggleMobileSidebar()"></div>

<script>
// Highlight active section in sidebar
function updateActiveSection() {
    const sections = document.querySelectorAll('section[id]');
    const sidebarLinks = document.querySelectorAll('.sidebar-nav-link');
    
    let currentSection = '';
    sections.forEach(section => {
        const rect = section.getBoundingClientRect();
        if (rect.top <= 150 && rect.bottom >= 150) {
            currentSection = section.id;
        }
    });
    
    sidebarLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === '#' + currentSection) {
            link.classList.add('active');
        }
    });
}

// Update on scroll
window.addEventListener('scroll', updateActiveSection);
updateActiveSection(); // Initial check

// Mobile sidebar toggle
function toggleMobileSidebar() {
    const sidebar = document.getElementById('guide-sidebar');
    const overlay = document.getElementById('mobileOverlay');
    sidebar.classList.toggle('mobile-open');
    overlay.classList.toggle('active');
}

// Show mobile toggle button on small screens
function checkMobileView() {
    if (window.innerWidth <= 1024) {
        document.getElementById('mobileTocToggle').style.display = 'flex';
    } else {
        document.getElementById('mobileTocToggle').style.display = 'none';
        document.getElementById('guide-sidebar').classList.remove('mobile-open');
        document.getElementById('mobileOverlay').classList.remove('active');
    }
}

checkMobileView();
window.addEventListener('resize', checkMobileView);
</script>

<?php include INCLUDES_PATH . '/footer.php'; ?>
