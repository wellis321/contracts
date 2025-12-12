<?php
/**
 * Glossary of Terms
 * Definitions of technical terms related to social care contracts in Scotland
 */
require_once dirname(__DIR__, 2) . '/config/config.php';

// Allow public access to the glossary
$isLoggedIn = Auth::isLoggedIn();
$error = '';
$success = '';

// Handle suggestion submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLoggedIn) {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $term = trim($_POST['term'] ?? '');
        $definition = trim($_POST['definition'] ?? '');
        
        if (empty($term) || empty($definition)) {
            $error = 'Please fill in both term and definition.';
        } else {
            try {
                $db = getDbConnection();
                $stmt = $db->prepare("
                    INSERT INTO glossary_suggestions (term, definition, suggested_by, status) 
                    VALUES (?, ?, ?, 'pending')
                ");
                $stmt->execute([$term, $definition, Auth::getUserId()]);
                $success = 'Thank you! Your suggestion has been submitted and will be reviewed.';
                // Clear form
                $_POST = [];
            } catch (Exception $e) {
                $error = 'Error submitting suggestion: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Glossary';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1>Glossary of Terms</h1>
            <p style="color: var(--text-light); margin-top: 0.5rem;">
                Definitions of technical terms and acronyms used in social care contracts and procurement in Scotland
            </p>
        </div>
        <?php if ($isLoggedIn): ?>
            <button onclick="openSuggestionModal()" class="btn btn-secondary" style="white-space: nowrap;">
                <i class="fa-solid fa-lightbulb" style="margin-right: 0.5rem;"></i>
                Suggest a Term
            </button>
        <?php endif; ?>
    </div>
    
    <div style="max-width: 900px; margin: 0 auto;">
        <!-- Search Box -->
        <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem;">
            <div style="position: relative;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                <input type="text" id="glossarySearch" placeholder="Search for a term..." style="width: 100%; padding: 0.75rem 1rem 0.75rem 3rem; border: 1px solid var(--border-color); border-radius: 0.375rem; font-size: 1rem;" onkeyup="searchGlossary()">
            </div>
            <div id="searchResults" style="margin-top: 1rem; display: none;">
                <p style="font-size: 0.9rem; color: var(--text-light); margin: 0;">
                    <span id="resultCount">0</span> term(s) found
                </p>
            </div>
        </div>
        
        <!-- Quick Navigation -->
        <div style="background: var(--bg-light); padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem; display: flex; flex-wrap: wrap; gap: 0.5rem;">
            <a href="#A" style="color: var(--primary-color); text-decoration: none; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">A</a>
            <a href="#B" style="color: var(--primary-color); text-decoration: none; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">B</a>
            <a href="#C" style="color: var(--primary-color); text-decoration: none; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">C</a>
            <a href="#D" style="color: var(--primary-color); text-decoration: none; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">D</a>
            <a href="#E" style="color: var(--primary-color); text-decoration: none; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">E</a>
            <a href="#F" style="color: var(--primary-color); text-decoration: none; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">F</a>
            <a href="#H" style="color: var(--primary-color); text-decoration: none; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">H</a>
            <a href="#I" style="color: var(--primary-color); text-decoration: none; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">I</a>
            <a href="#L" style="color: var(--primary-color); text-decoration: none; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">L</a>
            <a href="#M" style="color: var(--primary-color); text-decoration: none; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">M</a>
            <a href="#P" style="color: var(--primary-color); text-decoration: none; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">P</a>
            <a href="#Q" style="color: var(--primary-color); text-decoration: none; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">Q</a>
            <a href="#R" style="color: var(--primary-color); text-decoration: none; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">R</a>
            <a href="#S" style="color: var(--primary-color); text-decoration: none; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">S</a>
            <a href="#T" style="color: var(--primary-color); text-decoration: none; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">T</a>
        </div>
        
        <!-- A -->
        <section id="A" class="glossary-section" style="margin-bottom: 2rem;">
            <h2 style="color: var(--primary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">A</h2>
            
            <div class="card glossary-term" style="margin-top: 1rem;" data-term="awarded">
                <h3 style="margin-top: 0;">Awarded</h3>
                <p>A tender status indicating that a contract has been awarded to your organisation. The contract is ready to go live.</p>
            </div>
        </section>
        
        <!-- B -->
        <section id="B" class="glossary-section" style="margin-bottom: 2rem;">
            <h2 style="color: var(--primary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">B</h2>
            
            <div class="card glossary-term" style="margin-top: 1rem;" data-term="block contract">
                <h3 style="margin-top: 0;">Block Contract</h3>
                <p>A type of contract where a local authority buys a set capacity (e.g., 100 hours per week) rather than individual placements. Can be negotiated or tendered.</p>
            </div>
        </section>
        
        <!-- C -->
        <section id="C" class="glossary-section" style="margin-bottom: 2rem;">
            <h2 style="color: var(--primary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">C</h2>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Care Inspectorate</h3>
                <p>The independent scrutiny and improvement body for social care and social work services in Scotland. They inspect and rate care services, and these ratings are often used as evaluation criteria in tenders.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">CHI Number</h3>
                <p>Community Health Index number - a unique identifier used in Scotland for health and social care records. Used to track individuals across services.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Clarification Requested</h3>
                <p>A tender status indicating that the local authority has requested additional information or clarification about your tender submission.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Commissioning</h3>
                <p>The process by which local authorities plan, procure, and manage social care services. Strategic commissioning involves assessing needs and planning service delivery.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Competitive Dialogue</h3>
                <p>A procurement procedure used for complex contracts where requirements may evolve during the process. Allows for dialogue between the authority and providers.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Competitive Tender</h3>
                <p>A procurement process where multiple providers submit bids for a contract. Can be "open" (any provider can bid) or "restricted" (pre-qualification required).</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Contract Award</h3>
                <p>The formal decision by a local authority to award a contract to a provider. This follows evaluation of tenders.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Contract Live</h3>
                <p>A tender status indicating that a contract is active and operational, with services being delivered.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Contract Type</h3>
                <p>The category of service being provided, such as "Waking Hours", "Sleepover Hours", "Support Hours", or "Personal Care". Each type may have different rates.</p>
            </div>
        </section>
        
        <!-- D -->
        <section id="D" class="glossary-section" style="margin-bottom: 2rem;">
            <h2 style="color: var(--primary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">D</h2>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Direct Award</h3>
                <p>A procurement route where a contract is awarded directly to a provider without a competitive process. Common for Self-Directed Support (SDS) Option 1 or 2, or specialist services.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Dynamic Purchasing System (DPS)</h3>
                <p>An electronic system for repeat purchases where providers can join at any time. More flexible than traditional frameworks as new providers can be added throughout the system's lifetime.</p>
            </div>
        </section>
        
        <!-- E -->
        <section id="E" class="glossary-section" style="margin-bottom: 2rem;">
            <h2 style="color: var(--primary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">E</h2>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Ethical Commissioning</h3>
                <p>An approach to commissioning that focuses on collaboration, quality, and social value rather than price competition. Promoted by the Feeley Review (2021) as an alternative to competitive tendering.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Evaluation Criteria</h3>
                <p>The factors used to assess tenders, typically including quality (training, experience, Care Inspectorate ratings), price, and social value (community benefits, Fair Work compliance).</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Extension Negotiation</h3>
                <p>A tender status indicating that you are negotiating an extension to an existing contract before it expires.</p>
            </div>
        </section>
        
        <!-- F -->
        <section id="F" class="glossary-section" style="margin-bottom: 2rem;">
            <h2 style="color: var(--primary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">F</h2>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Fair Work Compliance</h3>
                <p>Meeting requirements for fair employment practices, including paying at least the Real Living Wage, providing secure contracts, and recognising trade unions. Often a requirement in social care contracts.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Feeley Review</h3>
                <p>The Independent Review of Adult Social Care in Scotland (2021), led by Derek Feeley. Found that competitive tendering driven by price is undermining the sector and recommended a move to ethical commissioning.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Framework Agreement</h3>
                <p>A pre-qualified list of approved providers. Local authorities can "call off" contracts from the framework without re-tendering each time. Example: Scotland Excel Care and Support Framework.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Framework Agreement Call-Off</h3>
                <p>A procurement route where a contract is awarded from a pre-qualified framework without going through a new tender process. Faster than competitive tendering but requires being on the framework first.</p>
            </div>
        </section>
        
        <!-- H -->
        <section id="H" class="glossary-section" style="margin-bottom: 2rem;">
            <h2 style="color: var(--primary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">H</h2>
            
            <div class="card glossary-term" style="margin-top: 1rem;" data-term="hca homecare association">
                <h3 style="margin-top: 0;">HCA (Homecare Association)</h3>
                <p>The UK Homecare Association, a professional body representing homecare providers. They publish annual "Minimum Price for Homecare" reports recommending minimum prices that local authorities should pay to providers. These rates are typically higher than wage rates because they include operational costs such as travel time, mileage, training, supervision, and other overheads beyond direct wages.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;" data-term="homecare association rate">
                <h3 style="margin-top: 0;">Homecare Association Recommended Rate</h3>
                <p>The minimum price per hour that the Homecare Association recommends local authorities should pay to providers. This rate accounts for wages, travel time, mileage costs, training, supervision, and operational overheads. It is typically higher than wage-only rates (such as the Scottish Government Minimum or Real Living Wage) because it reflects the true cost of delivering quality homecare services.</p>
            </div>
        </section>
        
        <!-- I -->
        <section id="I" class="glossary-section" style="margin-bottom: 2rem;">
            <h2 style="color: var(--primary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">I</h2>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">IJB (Integration Joint Board)</h3>
                <p>A board that brings together local authorities and health boards to plan and commission integrated health and social care services. IJBs create strategic commissioning plans but <strong>cannot hold contracts themselves</strong> - contracts are held by the local authority or health board.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Inflation Indexation</h3>
                <p>A mechanism in contracts that allows rates to increase with inflation (e.g., CPI, RPI, or a fixed percentage). Helps protect providers from rising costs over the contract period.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Innovation Partnership</h3>
                <p>A procurement procedure for developing innovative solutions. Allows for collaboration between the authority and provider to develop new approaches.</p>
            </div>
        </section>
        
        <!-- L -->
        <section id="L" class="glossary-section" style="margin-bottom: 2rem;">
            <h2 style="color: var(--primary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">L</h2>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">LA (Local Authority)</h3>
                <p>One of the 32 councils in Scotland responsible for providing social care services. They commission and contract with care providers.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Legacy Rates</h3>
                <p>Rates from older contracts that may differ from current rates. The same provider might receive different rates from the same local authority depending on when the contract was signed.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Light-Touch Regime</h3>
                <p>A simplified procurement process for social care contracts over £663,540 under the Public Contracts (Scotland) Regulations 2015. More flexible than standard public procurement rules.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Lost</h3>
                <p>A tender status indicating that the contract was awarded to another provider, not your organisation.</p>
            </div>
        </section>
        
        <!-- M -->
        <section id="M" class="glossary-section" style="margin-bottom: 2rem;">
            <h2 style="color: var(--primary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">M</h2>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Market Engagement</h3>
                <p>A tender status and procurement stage where local authorities discuss requirements with potential providers before formal tendering begins. Helps authorities understand market capacity and provider capabilities.</p>
            </div>
        </section>
        
        <!-- P -->
        <section id="P" class="glossary-section" style="margin-bottom: 2rem;">
            <h2 style="color: var(--primary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">P</h2>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Pre-Qualification</h3>
                <p>The first stage of a restricted tender process where providers submit information to demonstrate they meet basic requirements. Only pre-qualified providers are invited to submit full tenders.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Price Review Mechanism</h3>
                <p>The process defined in a contract for reviewing and potentially adjusting rates. May be annual, linked to inflation, or triggered by specific events.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Procurement</h3>
                <p>The process of acquiring goods or services. In social care, this refers to how local authorities find and contract with care providers.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Procurement Route</h3>
                <p>The method used to award a contract, such as competitive tender, framework call-off, direct award, spot purchase, or block contract.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Public Contracts (Scotland) Regulations 2015</h3>
                <p>Legislation governing public procurement in Scotland. Includes a "light-touch regime" for social care contracts over £663,540.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Public Social Partnership (PSP)</h3>
                <p>An alternative procurement approach mentioned in guidance that focuses on collaboration and innovation rather than price competition. Encourages long-term partnerships between public bodies and social enterprises.</p>
            </div>
        </section>
        
        <!-- Q -->
        <section id="Q" class="glossary-section" style="margin-bottom: 2rem;">
            <h2 style="color: var(--primary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">Q</h2>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Quality:Price Weighting</h3>
                <p>The ratio used to evaluate tenders, showing how much weight is given to quality versus price. For example, 70:30 means 70% quality, 30% price. Contracts cannot be awarded on price alone since 2016.</p>
            </div>
        </section>
        
        <!-- R -->
        <section id="R" class="glossary-section" style="margin-bottom: 2rem;">
            <h2 style="color: var(--primary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">R</h2>
            
            <div class="card glossary-term" style="margin-top: 1rem;" data-term="rate monitoring">
                <h3 style="margin-top: 0;">Rate Monitoring</h3>
                <p>A feature available to administrators that automatically validates and monitors reference rates (Scotland Mandated Minimum, Real Living Wage, and Homecare Association rates) to ensure they are current and accurate. The system checks for outdated rates, missing data, and provides visual status indicators (green = current, yellow = needs review, red = critical). Administrators can access the Rate Monitoring dashboard from the Admin menu.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;" data-term="reference rate">
                <h3 style="margin-top: 0;">Reference Rate</h3>
                <p>Standard rates used as benchmarks in the social care sector. The three main reference rates are: Scottish Government Mandated Minimum (legally required baseline), Real Living Wage (voluntary rate based on cost of living), and Homecare Association Recommended (minimum price including operational costs). These rates help providers and commissioners negotiate fair contracts and understand market standards.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;" data-term="real living wage">
                <h3 style="margin-top: 0;">Real Living Wage</h3>
                <p>A voluntary wage rate calculated independently by the Living Wage Foundation based on the cost of living. Higher than the National Living Wage. Many providers choose to pay this rate (or higher) to demonstrate their commitment to fair pay and attract quality staff. The rate is typically announced in November each year and takes effect the following April.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Retender Pending</h3>
                <p>A tender status indicating that a contract is ending and the retender process is starting. Time to prepare a new bid.</p>
            </div>
        </section>
        
        <!-- S -->
        <section id="S" class="glossary-section" style="margin-bottom: 2rem;">
            <h2 style="color: var(--primary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">S</h2>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">SDS (Self-Directed Support)</h3>
                <p>A system giving people choice and control over their social care support. Has four options:</p>
                <ul style="margin-left: 2rem; margin-top: 0.5rem;">
                    <li><strong>Option 1:</strong> Direct payment - person receives money to arrange their own support</li>
                    <li><strong>Option 2:</strong> Directed support - person chooses provider, LA arranges and pays</li>
                    <li><strong>Option 3:</strong> LA arranged - local authority arranges support</li>
                    <li><strong>Option 4:</strong> Mix of the above</li>
                </ul>
                <p style="margin-top: 0.5rem;">Options 1 and 2 allow for direct awards without competitive tendering.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;" data-term="scotland excel">
                <h3 style="margin-top: 0;">Scotland Excel</h3>
                <p>A procurement organisation that manages framework agreements for all 32 Scottish local authorities. The Care and Support Framework (2024-2030) has 152 providers.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;" data-term="scottish government minimum scotland mandated">
                <h3 style="margin-top: 0;">Scottish Government Mandated Minimum Rate</h3>
                <p>The legally mandated minimum hourly rate for social care workers in commissioned services. Set by the Scottish Government and applies to all providers delivering commissioned social care services. This is the baseline rate that all providers must meet, typically announced as part of the annual budget process.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Social Value</h3>
                <p>Benefits to the community beyond the direct service delivery, such as employment opportunities, training placements, local sourcing, and environmental commitments. Often part of evaluation criteria.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Spot Purchase</h3>
                <p>A procurement route for emergency or urgent placements. Negotiated rates, often higher than standard contracts due to the urgent nature.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">SWIS Number</h3>
                <p>Scottish Wide Information System number - a unique identifier used in social care in Scotland. Used to track individuals across services and local authorities.</p>
            </div>
        </section>
        
        <!-- T -->
        <section id="T" class="glossary-section" style="margin-bottom: 2rem;">
            <h2 style="color: var(--primary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">T</h2>
            
            <div class="card glossary-term" style="margin-top: 1rem;" data-term="team">
                <h3 style="margin-top: 0;">Team</h3>
                <p>An organisational unit within your organisation. Teams can be organised hierarchically (e.g., Region → Area → Team) and can have custom types (e.g., Department, Division, Unit). Teams are used to organise contracts and control access - team managers can only manage contracts assigned to their team.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;" data-term="team type team-type">
                <h3 style="margin-top: 0;">Team Type</h3>
                <p>A custom category for teams defined by your organisation. Examples include Department, Division, Region, Unit, or any other organisational structure. Team types help categorise and organise your teams.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;" data-term="team hierarchy hierarchical">
                <h3 style="margin-top: 0;">Team Hierarchy</h3>
                <p>The parent-child relationship between teams. For example, a Region can contain Areas, and Areas can contain Teams. This hierarchical structure allows team managers to access contracts in their team and all child teams.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;" data-term="team manager">
                <h3 style="margin-top: 0;">Team Manager</h3>
                <p>A user role within a team that allows the user to create, edit, and manage contracts assigned to that team (and any child teams). Team managers have restricted access compared to finance or senior managers who can access all contracts.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;" data-term="tender">
                <h3 style="margin-top: 0;">Tender</h3>
                <p>A formal offer to provide services at a specified price. Part of the competitive tendering process.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;" data-term="tender status">
                <h3 style="margin-top: 0;">Tender Status</h3>
                <p>The current stage of a contract in the procurement lifecycle, such as "Market Engagement", "Tender Submitted", "Under Evaluation", "Awarded", "Contract Live", etc.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;" data-term="tender submitted">
                <h3 style="margin-top: 0;">Tender Submitted</h3>
                <p>A tender status indicating that your tender has been submitted and is awaiting evaluation by the local authority.</p>
            </div>
            
            <div class="card glossary-term" style="margin-top: 1rem;" data-term="tupe">
                <h3 style="margin-top: 0;">TUPE (Transfer of Undertakings Protection of Employment)</h3>
                <p>Regulations that protect employees when a business or contract transfers to a new provider. Staff automatically transfer with their existing terms and conditions, pension rights, and continuity of service.</p>
            </div>
        </section>
        
        <!-- U -->
        <section id="U" class="glossary-section" style="margin-bottom: 2rem;">
            <h2 style="color: var(--primary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">U</h2>
            
            <div class="card glossary-term" style="margin-top: 1rem;">
                <h3 style="margin-top: 0;">Under Evaluation</h3>
                <p>A tender status indicating that the local authority is currently evaluating your tender submission.</p>
            </div>
        </section>
        
        <!-- Back to top -->
        <div style="text-align: center; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
            <a href="#top" style="color: var(--primary-color); text-decoration: none;">
                <i class="fa-solid fa-arrow-up" style="margin-right: 0.5rem;"></i>Back to Top
            </a>
        </div>
    </div>
</div>

<style>
/* Smooth scrolling for anchor links */
html {
    scroll-behavior: smooth;
}

section[id] {
    scroll-margin-top: 100px;
}

/* Letter section headers */
section h2 {
    margin-top: 0;
}

/* Search highlighting */
.highlight {
    background-color: #fef3c7;
    padding: 0.1rem 0.2rem;
    border-radius: 0.2rem;
}

/* Hidden terms when searching */
.glossary-term.hidden {
    display: none;
}

/* Show/hide sections based on search */
.glossary-section.hidden {
    display: none;
}

/* Mobile Responsive Styles */
@media (max-width: 768px) {
    .card-header {
        flex-direction: column !important;
        align-items: flex-start !important;
    }
    
    .card-header button {
        width: 100%;
        margin-top: 1rem;
    }
    
    /* Search box */
    #glossarySearch {
        font-size: 16px !important; /* Prevents zoom on iOS */
    }
    
    /* Quick navigation */
    div[style*="display: flex"][style*="flex-wrap"] {
        gap: 0.375rem !important;
    }
    
    div[style*="display: flex"][style*="flex-wrap"] a {
        padding: 0.375rem 0.625rem !important;
        font-size: 0.875rem !important;
    }
    
    /* Content readability */
    section {
        margin-bottom: 1.5rem !important;
    }
    
    .glossary-term {
        padding: 1rem !important;
    }
    
    .glossary-term h3 {
        font-size: 1.125rem !important;
        line-height: 1.4 !important;
    }
    
    .glossary-term p {
        line-height: 1.7 !important;
        font-size: 0.95rem !important;
    }
    
    /* Modal adjustments */
    .modal-content {
        width: 95% !important;
        margin: 10% auto !important;
    }
    
    .modal-header,
    .modal-body {
        padding: 1rem !important;
    }
}

@media (max-width: 480px) {
    h1 {
        font-size: 1.5rem !important;
    }
    
    section h2 {
        font-size: 1.25rem !important;
    }
    
    .glossary-term {
        padding: 0.875rem !important;
    }
    
    .modal-content {
        width: 98% !important;
        margin: 5% auto !important;
    }
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.modal-content {
    background-color: var(--bg-color);
    margin: 5% auto;
    padding: 0;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 10px 20px rgba(0, 0, 0, 0.15);
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.05), rgba(30, 64, 175, 0.05));
}

.modal-header h3 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--primary-color);
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: var(--text-light);
    cursor: pointer;
    padding: 0;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.25rem;
    transition: all 0.2s;
}

.modal-close:hover {
    background-color: var(--bg-light);
    color: var(--text-color);
}

.modal-body {
    padding: 1.5rem;
}

@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        margin: 10% auto;
    }
    
    .modal-header {
        padding: 1rem;
    }
    
    .modal-body {
        padding: 1rem;
    }
}
</style>

<script>
function searchGlossary() {
    const searchTerm = document.getElementById('glossarySearch').value.toLowerCase().trim();
    const resultsDiv = document.getElementById('searchResults');
    const resultCountSpan = document.getElementById('resultCount');
    let matchCount = 0;
    
    // Get all glossary term cards (exclude suggestion form)
    const termCards = document.querySelectorAll('.glossary-term');
    const sections = document.querySelectorAll('.glossary-section');
    
    if (searchTerm === '') {
        // Show all terms
        termCards.forEach(card => {
            card.classList.remove('hidden');
            card.style.display = '';
            // Remove highlights
            const highlights = card.querySelectorAll('.highlight');
            highlights.forEach(el => {
                const parent = el.parentNode;
                parent.replaceChild(document.createTextNode(el.textContent), el);
                parent.normalize();
            });
        });
        
        sections.forEach(section => {
            section.classList.remove('hidden');
        });
        
        resultsDiv.style.display = 'none';
        return;
    }
    
    // Search through terms
    termCards.forEach(card => {
        const heading = card.querySelector('h3');
        const definition = card.querySelector('p');
        
        if (!heading) return;
        
        const termText = heading.textContent.toLowerCase();
        const defText = definition ? definition.textContent.toLowerCase() : '';
        const matches = termText.includes(searchTerm) || defText.includes(searchTerm);
        
        if (matches) {
            card.classList.remove('hidden');
            card.style.display = '';
            matchCount++;
            
            // Highlight search term in heading
            if (termText.includes(searchTerm)) {
                highlightText(heading, searchTerm);
            }
            // Highlight in definition
            if (defText.includes(searchTerm) && definition) {
                highlightText(definition, searchTerm);
            }
        } else {
            card.classList.add('hidden');
            card.style.display = 'none';
        }
    });
    
    // Show/hide sections based on whether they have visible terms
    sections.forEach(section => {
        const visibleTerms = section.querySelectorAll('.glossary-term:not(.hidden)');
        if (visibleTerms.length === 0) {
            section.classList.add('hidden');
        } else {
            section.classList.remove('hidden');
        }
    });
    
    // Update result count
    resultCountSpan.textContent = matchCount;
    resultsDiv.style.display = matchCount > 0 ? 'block' : 'none';
    
    if (matchCount === 0 && searchTerm !== '') {
        resultCountSpan.textContent = 'No';
        resultsDiv.style.display = 'block';
    }
}

function highlightText(element, searchTerm) {
    // Remove existing highlights first
    const existingHighlights = element.querySelectorAll('.highlight');
    existingHighlights.forEach(highlight => {
        const parent = highlight.parentNode;
        parent.replaceChild(document.createTextNode(highlight.textContent), highlight);
        parent.normalize();
    });
    
    const text = element.textContent;
    const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    const highlighted = text.replace(regex, '<span class="highlight">$1</span>');
    element.innerHTML = highlighted;
}

// Clear search on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const searchInput = document.getElementById('glossarySearch');
        if (searchInput && searchInput.value) {
            searchInput.value = '';
            searchGlossary();
            searchInput.focus();
        }
        // Close modal if open
        closeSuggestionModal();
    }
});

// Modal Functions
function openSuggestionModal() {
    const modal = document.getElementById('suggestionModal');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        // Focus on first input
        setTimeout(() => {
            const firstInput = document.getElementById('suggested_term');
            if (firstInput) firstInput.focus();
        }, 100);
    }
}

function closeSuggestionModal() {
    const modal = document.getElementById('suggestionModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('suggestionModal');
    if (event.target === modal) {
        closeSuggestionModal();
    }
}

// Auto-open modal if there's an error or success message (after form submission)
<?php if ($error || $success): ?>
document.addEventListener('DOMContentLoaded', function() {
    openSuggestionModal();
});
<?php endif; ?>
</script>

<!-- Suggestion Modal -->
<?php if ($isLoggedIn): ?>
<div id="suggestionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>
                <i class="fa-solid fa-lightbulb"></i>
                Suggest a Term
            </h3>
            <button class="modal-close" onclick="closeSuggestionModal()" aria-label="Close">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 1.5rem;">
                Found a term that's not in the glossary? Suggest it below and we'll review it for inclusion.
            </p>
            
            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom: 1rem;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" style="margin-bottom: 1rem;"><?php echo htmlspecialchars($success); ?></div>
                <script>
                    // Clear form after successful submission
                    setTimeout(function() {
                        document.getElementById('suggestionForm').reset();
                        // Close modal after 2 seconds
                        setTimeout(function() {
                            closeSuggestionModal();
                        }, 2000);
                    }, 100);
                </script>
            <?php endif; ?>
            
            <form method="POST" action="" id="suggestionForm">
                <?php echo CSRF::tokenField(); ?>
                <div class="form-group">
                    <label for="suggested_term">Term *</label>
                    <input type="text" id="suggested_term" name="term" class="form-control" required placeholder="e.g., National Care Service" value="<?php echo htmlspecialchars($_POST['term'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="suggested_definition">Definition *</label>
                    <textarea id="suggested_definition" name="definition" class="form-control" rows="4" required placeholder="Provide a clear definition of the term..."><?php echo htmlspecialchars($_POST['definition'] ?? ''); ?></textarea>
                </div>
                <div class="form-group" style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeSuggestionModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Suggestion</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include INCLUDES_PATH . '/footer.php'; ?>
