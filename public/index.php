<?php
/**
 * Home Page / Dashboard
 */
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Home';
$isLoggedIn = Auth::isLoggedIn();

// Allow logged-in users to view public home page with ?view=public parameter
$viewPublicHome = isset($_GET['view']) && $_GET['view'] === 'public';

// Get latest updates (available to all)
$recentUpdates = LocalAuthorityRateInfo::getAllRecentUpdates(5);

// Get current rates
$currentRLW = LocalAuthorityRateInfo::getCurrentRealLivingWage();
$currentScotlandRate = LocalAuthorityRateInfo::getCurrentScotlandMandatedRate();
$currentHCA = LocalAuthorityRateInfo::getCurrentHomecareAssociationRate();

if ($isLoggedIn && !$viewPublicHome) {
    // Dashboard view for logged-in users
    Auth::requireLogin();
    $user = Auth::getUser();
    $organisationId = Auth::getOrganisationId();
    
    // Get statistics
    $db = getDbConnection();
    
    // Get user's accessible team IDs (for filtering contracts)
    $accessibleTeamIds = RBAC::getAccessibleTeamIds();
    
    // Get all contracts (using same method as contracts.php to ensure consistency)
    $allContracts = Contract::findByOrganisation($organisationId, null, $accessibleTeamIds);
    
    // Deduplicate contracts (same logic as contracts.php)
    $seenIds = [];
    $seenContractKeys = [];
    $uniqueContracts = [];
    
    foreach ($allContracts as $contract) {
        $id = $contract['id'] ?? null;
        $contractNumber = $contract['contract_number'] ?? '';
        $title = $contract['title'] ?? '';
        $startDate = $contract['start_date'] ?? '';
        $endDate = $contract['end_date'] ?? '';
        
        // First priority: skip if we've seen this exact contract ID
        if ($id && in_array($id, $seenIds)) {
            continue;
        }
        
        // Second priority: if contract number exists and we've seen this exact combination before, skip
        if ($contractNumber) {
            $contractKey = md5($contractNumber . '|' . $title . '|' . $startDate . '|' . $endDate);
            if (in_array($contractKey, $seenContractKeys)) {
                continue;
            }
            $seenContractKeys[] = $contractKey;
        }
        
        // Add to unique contracts
        if ($id) {
            $seenIds[] = $id;
        }
        $uniqueContracts[] = $contract;
    }
    
    // Contract count (deduplicated)
    $contractCount = count($uniqueContracts);
    
    // Active contracts count (deduplicated, using effective status)
    $activeContractCount = count(array_filter($uniqueContracts, function($c) {
        $effectiveStatus = Contract::getEffectiveStatus($c);
        return $effectiveStatus === 'active';
    }));
    
    // Inactive/Expired contracts count (deduplicated)
    $inactiveContractCount = count(array_filter($uniqueContracts, function($c) {
        return ($c['status'] ?? '') === 'inactive';
    }));
    
    // Contract types count
    $stmt = $db->prepare("SELECT COUNT(DISTINCT id) as count FROM contract_types WHERE organisation_id = ? AND is_active = 1");
    $stmt->execute([$organisationId]);
    $contractTypeCount = $stmt->fetch()['count'];
    
    // Contracts expiring soon (filtered by team access)
    $expiringSoon = Contract::getExpiringSoon($organisationId, 6, $accessibleTeamIds);
    $expiringCount = count($expiringSoon);
    
    $isAdmin = RBAC::isAdmin();
    $isSuperAdmin = RBAC::isSuperAdmin();
    
    $pageTitle = 'Dashboard';
}

include INCLUDES_PATH . '/header.php';
?>

<?php if ($isLoggedIn && !$viewPublicHome): ?>
    <!-- Dashboard for logged-in users -->
    <div class="card">
        <div class="card-header">
            <h1>Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!</p>
        </div>
        
        <?php if (isset($_GET['error']) && $_GET['error'] === 'access_denied'): ?>
            <div class="alert alert-error">You do not have permission to access that resource.</div>
        <?php endif; ?>
        
        <?php if ($isAdmin): ?>
            <?php
            $monitoringStatus = LocalAuthorityRateInfo::getRateMonitoringStatus();
            if ($monitoringStatus['overall_status'] !== 'good'):
            ?>
                <div class="alert <?php echo $monitoringStatus['overall_status'] === 'error' ? 'alert-error' : 'alert-warning'; ?>" style="margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 1rem;">
                        <div style="flex: 1;">
                            <strong><i class="fa-solid fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>Reference Rate Monitoring Alert</strong>
                            <?php if (!empty($monitoringStatus['errors'])): ?>
                                <ul style="margin: 0.5rem 0 0 1.5rem;">
                                    <?php foreach ($monitoringStatus['errors'] as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <?php if (!empty($monitoringStatus['warnings'])): ?>
                                <ul style="margin: 0.5rem 0 0 1.5rem;">
                                    <?php foreach ($monitoringStatus['warnings'] as $warning): ?>
                                        <li><?php echo htmlspecialchars(is_array($warning) ? $warning['message'] : $warning); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <a href="<?php echo htmlspecialchars(url('rate-monitoring.php')); ?>" class="btn <?php echo $monitoringStatus['overall_status'] === 'error' ? 'btn-danger' : 'btn-secondary'; ?>" style="white-space: nowrap;">
                            View Monitoring
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
            <a href="<?php echo url('contracts.php'); ?>" class="card" style="text-decoration: none; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white;">
                <h2 style="color: white; margin-bottom: 0.5rem;"><?php echo $contractCount; ?></h2>
                <p style="color: rgba(255,255,255,0.9); margin: 0;">Total Contracts</p>
            </a>
            
            <a href="<?php echo url('contracts.php?status=active'); ?>" class="card" style="text-decoration: none; background: linear-gradient(135deg, var(--success-color), #059669); color: white;">
                <h2 style="color: white; margin-bottom: 0.5rem;"><?php echo $activeContractCount; ?></h2>
                <p style="color: rgba(255,255,255,0.9); margin: 0;">Active Contracts</p>
            </a>
            
            <a href="<?php echo url('contract-types.php'); ?>" class="card" style="text-decoration: none; background: linear-gradient(135deg, var(--warning-color), #d97706); color: white;">
                <h2 style="color: white; margin-bottom: 0.5rem;"><?php echo $contractTypeCount; ?></h2>
                <p style="color: rgba(255,255,255,0.9); margin: 0;">Contract Types</p>
            </a>
            
            <?php if ($inactiveContractCount > 0): ?>
                <a href="<?php echo url('contracts.php?status=inactive'); ?>" class="card" style="text-decoration: none; background: linear-gradient(135deg, #ef4444, #dc2626); color: white;">
                    <h2 style="color: white; margin-bottom: 0.5rem;"><?php echo $inactiveContractCount; ?></h2>
                    <p style="color: rgba(255,255,255,0.9); margin: 0;">Expired Contracts</p>
                </a>
            <?php elseif ($expiringCount > 0): ?>
                <a href="<?php echo url('contract-workflow.php'); ?>" class="card" style="text-decoration: none; background: linear-gradient(135deg, #ef4444, #dc2626); color: white;">
                    <h2 style="color: white; margin-bottom: 0.5rem;"><?php echo $expiringCount; ?></h2>
                    <p style="color: rgba(255,255,255,0.9); margin: 0;">Expiring Soon</p>
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Contract Process Information -->
        <div style="margin-top: 3rem;">
            <h2>Understanding Social Care Contracts in Scotland</h2>
            <p style="color: var(--text-light); margin-bottom: 1.5rem;">
                Social care contracts in Scotland follow a structured procurement process. Understanding this helps you manage your contracts effectively.
            </p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                <div class="card" style="display: flex; flex-direction: column;">
                    <h3 style="margin-top: 0; display: flex; align-items: center;">
                        <i class="fa-solid fa-rotate" style="margin-right: 0.75rem; color: var(--primary-color); font-size: 1.25rem;"></i>
                        Contract Lifecycle
                    </h3>
                    <p style="flex-grow: 0;">Track contracts through stages:</p>
                    <ul style="margin-left: 1.5rem; margin-top: 0.5rem; flex-grow: 1;">
                        <li>Market Engagement</li>
                        <li>Tender Submission</li>
                        <li>Evaluation</li>
                        <li>Contract Award</li>
                        <li>Monitoring & Review</li>
                    </ul>
                    <a href="<?php echo url('contract-workflow.php'); ?>" class="btn btn-secondary" style="margin-top: auto; width: 100%;">View Workflow</a>
                </div>
                
                <div class="card" style="display: flex; flex-direction: column;">
                    <h3 style="margin-top: 0; display: flex; align-items: center;">
                        <i class="fa-solid fa-chart-line" style="margin-right: 0.75rem; color: var(--primary-color); font-size: 1.25rem;"></i>
                        Rate Information
                    </h3>
                    <p style="flex-grow: 0;">Stay informed about rates:</p>
                    <ul style="margin-left: 1.5rem; margin-top: 0.5rem; flex-grow: 1;">
                        <li>Current minimum rates</li>
                        <li>Local authority updates</li>
                        <li>Historical rate data</li>
                        <li>Reference rates</li>
                    </ul>
                    <a href="<?php echo url('pages/local-authority-rates.php'); ?>" class="btn btn-secondary" style="margin-top: auto; width: 100%;">View Rates</a>
                </div>
                
                <?php if ($isLoggedIn && RBAC::isAdmin()): ?>
                <div class="card" style="display: flex; flex-direction: column;">
                    <h3 style="margin-top: 0; display: flex; align-items: center;">
                        <i class="fa-solid fa-bell" style="margin-right: 0.75rem; color: var(--primary-color); font-size: 1.25rem;"></i>
                        Tender Monitoring
                    </h3>
                    <p style="flex-grow: 0;">Automatically find new opportunities:</p>
                    <ul style="margin-left: 1.5rem; margin-top: 0.5rem; flex-grow: 1;">
                        <li>Monitor Public Contracts Scotland</li>
                        <li>Get instant notifications</li>
                        <li>Auto-import opportunities</li>
                        <li>Track tender applications</li>
                    </ul>
                    <a href="<?php echo url('tender-monitoring.php'); ?>" class="btn btn-secondary" style="margin-top: auto; width: 100%;">Set Up Monitoring</a>
                </div>
                
                <div class="card" style="display: flex; flex-direction: column;">
                    <h3 style="margin-top: 0; display: flex; align-items: center;">
                        <i class="fa-solid fa-users" style="margin-right: 0.75rem; color: var(--primary-color); font-size: 1.25rem;"></i>
                        Teams Management
                    </h3>
                    <p style="flex-grow: 0;">Organise your organisation with teams:</p>
                    <ul style="margin-left: 1.5rem; margin-top: 0.5rem; flex-grow: 1;">
                        <li>Create hierarchical team structures</li>
                        <li>Assign contracts to teams</li>
                        <li>Control access by team</li>
                        <li>Import from CSV/JSON</li>
                    </ul>
                    <a href="<?php echo url('teams.php'); ?>" class="btn btn-secondary" style="margin-top: auto; width: 100%;">Manage Teams</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Current Rate Information -->
        <div style="margin-top: 3rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
                <h2 style="margin: 0;">Current Reference Rates</h2>
                <?php if ($isLoggedIn && RBAC::isAdmin()): ?>
                    <a href="<?php echo htmlspecialchars(url('rate-monitoring.php')); ?>" class="btn btn-secondary" style="white-space: nowrap; font-size: 0.9rem;">
                        <i class="fa-solid fa-chart-line" style="margin-right: 0.5rem;"></i>
                        Monitor Rates
                    </a>
                <?php endif; ?>
            </div>
            <?php if ($isLoggedIn && RBAC::isAdmin()): ?>
                <?php
                $monitoringStatus = LocalAuthorityRateInfo::getRateMonitoringStatus();
                $hasIssues = $monitoringStatus['overall_status'] !== 'good';
                ?>
                <?php if ($hasIssues): ?>
                    <div class="alert <?php echo $monitoringStatus['overall_status'] === 'error' ? 'alert-error' : 'alert-warning'; ?>" style="margin-bottom: 1.5rem;">
                        <strong><i class="fa-solid fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>Rate Monitoring:</strong> Some reference rates need attention. 
                        <a href="<?php echo htmlspecialchars(url('rate-monitoring.php')); ?>" style="margin-left: 0.5rem; text-decoration: underline;">View details</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1.5rem;">
                <?php if ($currentScotlandRate): ?>
                    <?php
                    if ($isLoggedIn && RBAC::isAdmin()) {
                        $scotlandStatus = $monitoringStatus['scotland_rate']['status'] ?? 'good';
                        $statusColor = $scotlandStatus === 'error' ? 'var(--danger-color)' : ($scotlandStatus === 'warning' ? 'var(--warning-color)' : 'var(--success-color)');
                    }
                    ?>
                    <div class="card" style="text-align: center; position: relative;">
                        <?php if ($isLoggedIn && RBAC::isAdmin() && isset($scotlandStatus) && $scotlandStatus !== 'good'): ?>
                            <span style="
                                position: absolute;
                                top: 0.5rem;
                                right: 0.5rem;
                                display: inline-block;
                                width: 12px;
                                height: 12px;
                                border-radius: 50%;
                                background-color: <?php echo $statusColor; ?>;
                                border: 2px solid var(--bg-color);
                            " title="<?php echo htmlspecialchars($monitoringStatus['scotland_rate']['message'] ?? ''); ?>"></span>
                        <?php endif; ?>
                        <h3 style="margin-top: 0; color: var(--primary-color); font-size: 1.5rem; font-weight: bold;">£<?php echo number_format($currentScotlandRate['rate'], 2); ?>/hr</h3>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: var(--text-light);">Scotland Mandated Minimum</p>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: var(--text-light);">From <?php echo date('d M Y', strtotime($currentScotlandRate['effective_date'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ($currentRLW): ?>
                    <?php
                    if ($isLoggedIn && RBAC::isAdmin()) {
                        $rlwStatus = $monitoringStatus['rlw_rate']['status'] ?? 'good';
                        $rlwStatusColor = $rlwStatus === 'error' ? 'var(--danger-color)' : ($rlwStatus === 'warning' ? 'var(--warning-color)' : 'var(--success-color)');
                    }
                    ?>
                    <div class="card" style="text-align: center; position: relative;">
                        <?php if ($isLoggedIn && RBAC::isAdmin() && isset($rlwStatus) && $rlwStatus !== 'good'): ?>
                            <span style="
                                position: absolute;
                                top: 0.5rem;
                                right: 0.5rem;
                                display: inline-block;
                                width: 12px;
                                height: 12px;
                                border-radius: 50%;
                                background-color: <?php echo $rlwStatusColor; ?>;
                                border: 2px solid var(--bg-color);
                            " title="<?php echo htmlspecialchars($monitoringStatus['rlw_rate']['message'] ?? ''); ?>"></span>
                        <?php endif; ?>
                        <h3 style="margin-top: 0; color: var(--success-color); font-size: 1.5rem; font-weight: bold;">£<?php echo number_format($currentRLW['uk_rate'], 2); ?>/hr</h3>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: var(--text-light);">Real Living Wage (UK)</p>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: var(--text-light);">From <?php echo date('d M Y', strtotime($currentRLW['effective_date'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ($currentHCA): ?>
                    <?php
                    if ($isLoggedIn && RBAC::isAdmin()) {
                        $hcaStatus = $monitoringStatus['hca_rate']['status'] ?? 'good';
                        $hcaStatusColor = $hcaStatus === 'error' ? 'var(--danger-color)' : ($hcaStatus === 'warning' ? 'var(--warning-color)' : 'var(--success-color)');
                    }
                    ?>
                    <div class="card" style="text-align: center; position: relative;">
                        <?php if ($isLoggedIn && RBAC::isAdmin() && isset($hcaStatus) && $hcaStatus !== 'good'): ?>
                            <span style="
                                position: absolute;
                                top: 0.5rem;
                                right: 0.5rem;
                                display: inline-block;
                                width: 12px;
                                height: 12px;
                                border-radius: 50%;
                                background-color: <?php echo $hcaStatusColor; ?>;
                                border: 2px solid var(--bg-color);
                            " title="<?php echo htmlspecialchars($monitoringStatus['hca_rate']['message'] ?? ''); ?>"></span>
                        <?php endif; ?>
                        <h3 style="margin-top: 0; color: var(--warning-color); font-size: 1.5rem; font-weight: bold;">£<?php echo number_format($currentHCA['scotland_rate'], 2); ?>/hr</h3>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: var(--text-light);">Homecare Association Recommended</p>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: var(--text-light);">Scotland Rate</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Latest Updates -->
        <?php if (!empty($recentUpdates)): ?>
            <div style="margin-top: 3rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h2 style="margin: 0;">Latest Updates & News</h2>
                    <a href="<?php echo url('pages/local-authority-rates.php'); ?>" class="btn btn-secondary">View All</a>
                </div>
                <div style="display: grid; gap: 1rem; margin-top: 1rem;">
                    <?php foreach ($recentUpdates as $update): ?>
                        <div class="card" style="padding: 1.5rem;">
                            <div style="display: flex; justify-content: space-between; align-items: start; gap: 1rem;">
                                <div style="flex: 1;">
                                    <h3 style="margin-top: 0; margin-bottom: 0.5rem;">
                                        <?php echo htmlspecialchars($update['title']); ?>
                                    </h3>
                                    <?php if ($update['local_authority_name']): ?>
                                        <p style="margin: 0 0 0.5rem 0; color: var(--primary-color); font-weight: 600;">
                                            <?php echo htmlspecialchars($update['local_authority_name']); ?>
                                        </p>
                                    <?php endif; ?>
                                    <p style="margin: 0 0 0.75rem 0; color: var(--text-light);">
                                        <?php echo htmlspecialchars(substr($update['content'], 0, 200)); ?>
                                        <?php echo strlen($update['content']) > 200 ? '...' : ''; ?>
                                    </p>
                                    <div style="display: flex; gap: 1rem; flex-wrap: wrap; font-size: 0.9rem; color: var(--text-light);">
                                        <?php if ($update['published_date']): ?>
                                            <span><i class="fa-solid fa-calendar" style="margin-right: 0.25rem; color: var(--text-light);"></i><?php echo date('d M Y', strtotime($update['published_date'])); ?></span>
                                        <?php endif; ?>
                                        <?php if ($update['rate_change']): ?>
                                            <span><i class="fa-solid fa-pound-sign" style="margin-right: 0.25rem; color: var(--success-color);"></i>Rate Change: £<?php echo number_format($update['rate_change'], 2); ?>/hr</span>
                                        <?php endif; ?>
                                        <?php if ($update['source_url']): ?>
                                            <a href="<?php echo htmlspecialchars($update['source_url']); ?>" target="_blank" style="color: var(--primary-color);">Read More →</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Quick Actions -->
        <div style="margin-top: 3rem;">
            <h2>Quick Actions</h2>
            
            <!-- Primary Actions (Blue) -->
            <div style="margin-top: 1rem; margin-bottom: 1.5rem;">
                <h3 style="font-size: 0.9rem; color: var(--text-light); margin-bottom: 0.75rem; font-weight: 500;">Main Actions</h3>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="<?php echo htmlspecialchars(url('contracts.php')); ?>" class="btn btn-primary">View Contracts</a>
                    <a href="<?php echo htmlspecialchars(url('contract-workflow.php')); ?>" class="btn btn-primary">Workflow Dashboard</a>
                    <a href="<?php echo htmlspecialchars(url('contract-types.php')); ?>" class="btn btn-primary">Contract Types</a>
                    <a href="<?php echo htmlspecialchars(url('rates.php')); ?>" class="btn btn-primary">Manage Rates</a>
                </div>
            </div>
            
            <!-- Secondary Actions (Gray) -->
            <div style="margin-top: 1rem;">
                <h3 style="font-size: 0.9rem; color: var(--text-light); margin-bottom: 0.75rem; font-weight: 500;">Resources & Settings</h3>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="<?php echo htmlspecialchars(url('pages/social-care-contracts-guide.php')); ?>" class="btn btn-secondary">Contracts Guide</a>
                    <?php if ($isAdmin): ?>
                        <a href="<?php echo htmlspecialchars(url('organisation.php')); ?>" class="btn btn-secondary">Organisation Settings</a>
                        <a href="<?php echo htmlspecialchars(url('users.php')); ?>" class="btn btn-secondary">Manage Users</a>
                    <?php endif; ?>
                    <?php if ($isSuperAdmin): ?>
                        <a href="<?php echo htmlspecialchars(url('superadmin.php')); ?>" class="btn btn-secondary">Super Admin Panel</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Home page for non-logged-in users or when viewing public home -->
    <div class="card">
        <div class="card-header" style="text-align: center;">
            <h1><?php echo APP_NAME; ?></h1>
            <p style="color: var(--text-light); margin-top: 0.5rem;">
                Social Care Contract Management System for Scotland
            </p>
        </div>
        
        <!-- Hero Section - Full Width -->
        <div style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-top: 2px solid #90caf9; border-bottom: 2px solid #90caf9; padding: 3rem 2rem; margin: 2rem 0 3rem 0; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">
            <div style="max-width: 1400px; margin: 0 auto;">
                <h2 style="text-align: center; margin: 0 0 1.5rem 0; color: var(--text-color);">Welcome</h2>
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: stretch;" class="hero-content-grid">
                    <div style="display: flex; flex-direction: column; justify-content: center;">
                        <p style="font-size: 1.2rem; color: var(--text-color); margin: 0 0 1.5rem 0; line-height: 1.8; font-weight: 500;">
                            A comprehensive platform for social care providers in Scotland to manage contracts, track procurement processes, 
                            monitor rates, and stay informed about the latest developments in social care commissioning and funding.
                        </p>
                        <ul style="font-size: 1rem; color: var(--text-light); margin: 0 0 2rem 0; line-height: 1.7; padding-left: 1.5rem;">
                            <li style="margin-bottom: 0.75rem;">Streamline your contract management</li>
                            <li style="margin-bottom: 0.75rem;">Access real-time rate information</li>
                            <li style="margin-bottom: 0.75rem;">Maintain compliance with our integrated tools designed specifically for the Scottish social care sector</li>
                        </ul>
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                            <a href="#contact-organisation" class="btn btn-primary" style="display: inline-flex; align-items: center; padding: 0.875rem 2rem; font-weight: 600; text-decoration: none; white-space: nowrap; flex-shrink: 0; background-color: #6b8e23; border-color: #556b2f; color: white;">Get Your Organisation Set Up</a>
                            <a href="<?php echo url('pages/social-care-contracts-guide.php'); ?>" class="btn btn-secondary" style="display: inline-flex; align-items: center; padding: 0.875rem 2rem; font-weight: 600; text-decoration: none; white-space: nowrap; flex-shrink: 0; background-color: #8b6f47; border-color: #6b5638; color: white;">Learn More</a>
                        </div>
                    </div>
                    <div style="display: flex; align-items: stretch; height: 100%;">
                        <img src="<?php echo url('assets/images/happy-highland-cow.jpeg'); ?>" alt="Highland cow" style="width: 100%; height: 100%; border-radius: 0.5rem; object-fit: cover; object-position: center; display: block;" />
                    </div>
                </div>
            </div>
        </div>
        
        <div style="max-width: 1200px; margin: 0 auto;">
            <!-- Transformation: From Chaos to Organised Workflow -->
            <div style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; padding: 4rem 2rem; margin: 3rem 0; border-radius: 0.75rem; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);">
                <div style="max-width: 1100px; margin: 0 auto;">
                    <div style="text-align: center; margin-bottom: 3rem;">
                        <h2 style="color: white; margin: 0 0 1rem 0; font-size: 2.5rem; font-weight: 700;">Transform Contract Management from Chaos to Control</h2>
                        <p style="color: rgba(255,255,255,0.9); font-size: 1.25rem; margin: 0; line-height: 1.6;">
                            Stop juggling spreadsheets, calendar reminders, and mental notes. Get a clear, organised workflow that works for you.
                        </p>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 3rem;">
                        <!-- Before: The Chaos -->
                        <div style="background: rgba(239, 68, 68, 0.1); border: 2px solid rgba(239, 68, 68, 0.3); border-radius: 0.5rem; padding: 2rem;">
                            <div style="display: flex; align-items: center; margin-bottom: 1.5rem;">
                                <div style="background: #ef4444; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1rem; flex-shrink: 0;">
                                    <i class="fa-solid fa-xmark" style="font-size: 1.5rem; color: white;"></i>
                                </div>
                                <h3 style="color: white; margin: 0; font-size: 1.5rem;">The Old Way: Chaos</h3>
                            </div>
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                <li style="padding: 0.75rem 0; color: rgba(255,255,255,0.9); border-bottom: 1px solid rgba(255,255,255,0.1);">
                                    <i class="fa-solid fa-times-circle" style="color: #ef4444; margin-right: 0.75rem;"></i>
                                    Excel spreadsheets with end dates scattered everywhere
                                </li>
                                <li style="padding: 0.75rem 0; color: rgba(255,255,255,0.9); border-bottom: 1px solid rgba(255,255,255,0.1);">
                                    <i class="fa-solid fa-times-circle" style="color: #ef4444; margin-right: 0.75rem;"></i>
                                    Calendar reminders for each contract (easy to miss)
                                </li>
                                <li style="padding: 0.75rem 0; color: rgba(255,255,255,0.9); border-bottom: 1px solid rgba(255,255,255,0.1);">
                                    <i class="fa-solid fa-times-circle" style="color: #ef4444; margin-right: 0.75rem;"></i>
                                    "I think we bid on something in Aberdeen?" - no visibility
                                </li>
                                <li style="padding: 0.75rem 0; color: rgba(255,255,255,0.9); border-bottom: 1px solid rgba(255,255,255,0.1);">
                                    <i class="fa-solid fa-times-circle" style="color: #ef4444; margin-right: 0.75rem;"></i>
                                    Panic when contracts expire unexpectedly
                                </li>
                                <li style="padding: 0.75rem 0; color: rgba(255,255,255,0.9);">
                                    <i class="fa-solid fa-times-circle" style="color: #ef4444; margin-right: 0.75rem;"></i>
                                    ~2 hours/week maintaining spreadsheets
                                </li>
                            </ul>
                        </div>
                        
                        <!-- After: The Solution -->
                        <div style="background: rgba(16, 185, 129, 0.1); border: 2px solid rgba(16, 185, 129, 0.3); border-radius: 0.5rem; padding: 2rem;">
                            <div style="display: flex; align-items: center; margin-bottom: 1.5rem;">
                                <div style="background: #10b981; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1rem; flex-shrink: 0;">
                                    <i class="fa-solid fa-check" style="font-size: 1.5rem; color: white;"></i>
                                </div>
                                <h3 style="color: white; margin: 0; font-size: 1.5rem;">The New Way: Organised</h3>
                            </div>
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                <li style="padding: 0.75rem 0; color: rgba(255,255,255,0.9); border-bottom: 1px solid rgba(255,255,255,0.1);">
                                    <i class="fa-solid fa-check-circle" style="color: #10b981; margin-right: 0.75rem;"></i>
                                    One dashboard shows everything - contracts, tenders, deadlines
                                </li>
                                <li style="padding: 0.75rem 0; color: rgba(255,255,255,0.9); border-bottom: 1px solid rgba(255,255,255,0.1);">
                                    <i class="fa-solid fa-check-circle" style="color: #10b981; margin-right: 0.75rem;"></i>
                                    Automatic alerts for contracts expiring in 6 months
                                </li>
                                <li style="padding: 0.75rem 0; color: rgba(255,255,255,0.9); border-bottom: 1px solid rgba(255,255,255,0.1);">
                                    <i class="fa-solid fa-check-circle" style="color: #10b981; margin-right: 0.75rem;"></i>
                                    See your entire tender pipeline at a glance
                                </li>
                                <li style="padding: 0.75rem 0; color: rgba(255,255,255,0.9); border-bottom: 1px solid rgba(255,255,255,0.1);">
                                    <i class="fa-solid fa-check-circle" style="color: #10b981; margin-right: 0.75rem;"></i>
                                    Proactive planning instead of reactive crises
                                </li>
                                <li style="padding: 0.75rem 0; color: rgba(255,255,255,0.9);">
                                    <i class="fa-solid fa-check-circle" style="color: #10b981; margin-right: 0.75rem;"></i>
                                    ~5 minutes/day reviewing dashboard (saves 7+ hours/week!)
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Key Benefits Grid -->
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 3rem;">
                        <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; padding: 1.5rem; text-align: center;">
                            <div style="font-size: 3rem; margin-bottom: 0.5rem; color: #fbbf24;">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <h4 style="color: white; margin: 0 0 0.5rem 0;">Never Miss Deadlines</h4>
                            <p style="color: rgba(255,255,255,0.8); margin: 0; font-size: 0.95rem; line-height: 1.5;">
                                See contracts ending in next 6 months with color-coded alerts. Red if < 90 days, orange if < 180 days.
                            </p>
                        </div>
                        <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; padding: 1.5rem; text-align: center;">
                            <div style="font-size: 3rem; margin-bottom: 0.5rem; color: #3b82f6;">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                            <h4 style="color: white; margin: 0 0 0.5rem 0;">See Your Sales Pipeline</h4>
                            <p style="color: rgba(255,255,255,0.8); margin: 0; font-size: 0.95rem; line-height: 1.5;">
                                Track tender opportunities like a sales dashboard. Know exactly what's pending, under evaluation, or awarded.
                            </p>
                        </div>
                        <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; padding: 1.5rem; text-align: center;">
                            <div style="font-size: 3rem; margin-bottom: 0.5rem; color: #9333ea;">
                                <i class="fa-solid fa-bullseye"></i>
                            </div>
                            <h4 style="color: white; margin: 0 0 0.5rem 0;">Prioritize Your Work</h4>
                            <p style="color: rgba(255,255,255,0.8); margin: 0; font-size: 0.95rem; line-height: 1.5;">
                                Everything organised by priority. Urgent items highlighted, grouped by workflow stage, warnings for missing data.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Call to Action -->
                    <div style="text-align: center;">
                        <p style="color: rgba(255,255,255,0.9); font-size: 1.1rem; margin-bottom: 1.5rem;">
                            <strong>Ready to transform your contract management?</strong><br>
                            Get your organisation set up and start saving 7+ hours per week.
                        </p>
                        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                            <a href="#contact-organisation" class="btn" style="background: #10b981; color: white; padding: 1rem 2.5rem; font-weight: 600; text-decoration: none; border-radius: 0.5rem; display: inline-block; font-size: 1.1rem;">
                                Get Your Organisation Set Up
                            </a>
                            <?php if ($isLoggedIn): ?>
                                <a href="<?php echo url('contract-workflow.php'); ?>" class="btn" style="background: rgba(255,255,255,0.2); color: white; padding: 1rem 2.5rem; font-weight: 600; text-decoration: none; border: 2px solid rgba(255,255,255,0.3); border-radius: 0.5rem; display: inline-block; font-size: 1.1rem;">
                                    View Workflow Dashboard
                                </a>
                            <?php else: ?>
                                <a href="<?php echo url('pages/why-use-this-system.php'); ?>" class="btn" style="background: rgba(255,255,255,0.2); color: white; padding: 1rem 2.5rem; font-weight: 600; text-decoration: none; border: 2px solid rgba(255,255,255,0.3); border-radius: 0.5rem; display: inline-block; font-size: 1.1rem;">
                                    See How It Works
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Current Rates - Moved Up -->
            <div style="margin-top: 3rem; margin-bottom: 3rem; padding: 0 1rem;">
                <h2 style="text-align: center; margin-bottom: 1.5rem;">Current Reference Rates</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; max-width: 900px; margin: 0 auto;">
                    <?php if ($currentScotlandRate): ?>
                        <div style="background: #eff6ff; border: 2px solid #3b82f6; padding: 2rem; text-align: center;">
                            <h3 style="margin-top: 0; color: #1e40af; font-size: 2rem; font-weight: 700;">£<?php echo number_format($currentScotlandRate['rate'], 2); ?>/hr</h3>
                            <p style="margin: 0.5rem 0 0 0; font-weight: 600; color: #1e3a8a;">Scotland Mandated Minimum</p>
                            <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: var(--text-light);">Effective from <?php echo date('d M Y', strtotime($currentScotlandRate['effective_date'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($currentRLW): ?>
                        <div style="background: #f0fdf4; border: 2px solid #10b981; padding: 2rem; text-align: center;">
                            <h3 style="margin-top: 0; color: #047857; font-size: 2rem; font-weight: 700;">£<?php echo number_format($currentRLW['uk_rate'], 2); ?>/hr</h3>
                            <p style="margin: 0.5rem 0 0 0; font-weight: 600; color: #065f46;">Real Living Wage (UK)</p>
                            <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: var(--text-light);">Effective from <?php echo date('d M Y', strtotime($currentRLW['effective_date'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($currentHCA): ?>
                        <div style="background: #fffbeb; border: 2px solid #f59e0b; padding: 2rem; text-align: center;">
                            <h3 style="margin-top: 0; color: #92400e; font-size: 2rem; font-weight: 700;">£<?php echo number_format($currentHCA['scotland_rate'], 2); ?>/hr</h3>
                            <p style="margin: 0.5rem 0 0 0; font-weight: 600; color: #78350f;">Homecare Association Recommended</p>
                            <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: var(--text-light);">Scotland Rate</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="<?php echo url('pages/local-authority-rates.php'); ?>" class="btn btn-secondary">View Detailed Rate Information</a>
                </div>
            </div>
            
            <!-- Dynamic Content Section 1 -->
            <div style="background: #1e293b; color: white; padding: 3rem 1rem; margin: 3rem 0;">
                <div style="max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center; padding: 2rem 0;">
                    <div>
                        <img src="<?php echo htmlspecialchars(url('assets/images/Supporting Social Care.jpeg')); ?>" alt="Supporting Social Care Providers" style="width: 100%; height: auto; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);">
                    </div>
                    <div>
                        <h2 style="color: white; margin-top: 0;">Supporting Social Care Providers Across Scotland</h2>
                        <p style="color: rgba(255,255,255,0.9); line-height: 1.7; margin-bottom: 1.5rem;">
                            Our platform helps organisations manage contracts efficiently, track rates accurately, and maintain comprehensive records. 
                            Join providers who are streamlining their contract management processes.
                        </p>
                        <a href="#contact-organisation" class="btn" style="background: white; color: #1e293b; padding: 0.75rem 1.5rem; font-weight: 600; text-decoration: none; display: inline-block; margin-top: 0.5rem;">Get Your Organisation Set Up</a>
                    </div>
                </div>
            </div>
            
            <!-- Feature Cards (Replaced Slider) -->
            <div style="margin: 3rem 0; padding: 0 1rem;">
                <h2 style="text-align: center; margin-bottom: 1.5rem;">Key Features</h2>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem; max-width: 1000px; margin: 0 auto;">
                    <!-- Rate Tracking -->
                    <div class="card" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #10b981;">
                        <div style="display: flex; align-items: start; gap: 1rem; margin-bottom: 1rem;">
                            <div style="background: #10b981; color: white; width: 60px; height: 60px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fa-solid fa-chart-line" style="font-size: 2rem;"></i>
                            </div>
                            <div style="flex: 1;">
                                <h3 style="margin-top: 0; color: var(--text-color);">Rate Tracking & Information</h3>
                            </div>
                        </div>
                        <p style="color: var(--text-light); line-height: 1.7; margin-bottom: 1rem;">
                            Monitor rates for different contract types and local authorities. Access reference rates, 
                            historical data, and stay informed about rate changes and updates from local authorities.
                        </p>
                        <ul style="margin-left: 1.5rem; margin-top: 0.75rem; font-size: 0.95rem; color: var(--text-color);">
                            <li>Current reference rates</li>
                            <li>Historical rate data</li>
                            <li>Local authority updates</li>
                            <li>Rate change tracking</li>
                        </ul>
                    </div>
                    
                    <!-- Payment Tracking -->
                    <div class="card" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #f59e0b;">
                        <div style="display: flex; align-items: start; gap: 1rem; margin-bottom: 1rem;">
                            <div style="background: #f59e0b; color: white; width: 60px; height: 60px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fa-solid fa-money-bill-wave" style="font-size: 2rem;"></i>
                            </div>
                            <div style="flex: 1;">
                                <h3 style="margin-top: 0; color: var(--text-color);">Payment Tracking</h3>
                            </div>
                        </div>
                        <p style="color: var(--text-light); line-height: 1.7; margin-bottom: 1rem;">
                            Track payments associated with contracts including payment methods, frequencies, amounts, and dates. 
                            Maintain comprehensive payment history per person or contract for financial reporting.
                        </p>
                        <ul style="margin-left: 1.5rem; margin-top: 0.75rem; font-size: 0.95rem; color: var(--text-color);">
                            <li>Multiple payment methods</li>
                            <li>Payment frequency tracking</li>
                            <li>Payment history per contract</li>
                            <li>Payment history per person</li>
                        </ul>
                    </div>
                    
                    <!-- Tender Monitoring -->
                    <div class="card" style="background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border: 2px solid #2563eb;">
                        <div style="display: flex; align-items: start; gap: 1rem; margin-bottom: 1rem;">
                            <div style="background: #2563eb; color: white; width: 60px; height: 60px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fa-solid fa-bell" style="font-size: 2rem;"></i>
                            </div>
                            <div style="flex: 1;">
                                <h3 style="margin-top: 0; color: var(--text-color);">Automated Tender Monitoring</h3>
                            </div>
                        </div>
                        <p style="color: var(--text-light); line-height: 1.7; margin-bottom: 1rem;">
                            Automatically monitor Public Contracts Scotland for new tender opportunities. Get instant notifications 
                            when opportunities matching your criteria are found, and import them directly into your system.
                        </p>
                        <ul style="margin-left: 1.5rem; margin-top: 0.75rem; font-size: 0.95rem; color: var(--text-color);">
                            <li>Automated opportunity detection</li>
                            <li>Instant email notifications</li>
                            <li>URL import functionality</li>
                            <li>Customizable search criteria</li>
                        </ul>
                    </div>
                    
                    <!-- Audit Logging -->
                    <div class="card" style="background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); border: 2px solid #9333ea;">
                        <div style="display: flex; align-items: start; gap: 1rem; margin-bottom: 1rem;">
                            <div style="background: #9333ea; color: white; width: 60px; height: 60px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fa-solid fa-clipboard-check" style="font-size: 2rem; color: white;"></i>
                            </div>
                            <div style="flex: 1;">
                                <h3 style="margin-top: 0; color: var(--text-color);">Comprehensive Audit Logging</h3>
                            </div>
                        </div>
                        <p style="color: var(--text-light); line-height: 1.7; margin-bottom: 1rem;">
                            Track every change made across the system with complete audit trails. Maintain compliance with 
                            detailed logs of who made changes, when, and what changed, including approval workflows.
                        </p>
                        <ul style="margin-left: 1.5rem; margin-top: 0.75rem; font-size: 0.95rem; color: var(--text-color);">
                            <li>Complete change tracking</li>
                            <li>User and timestamp logging</li>
                            <li>Approval workflow system</li>
                            <li>Compliance reporting</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <style>
            @media (max-width: 768px) {
                .card[style*="grid-template-columns"] {
                    grid-template-columns: 1fr !important;
                }
            }
            </style>
            
            <!-- Contract Management Section with Image -->
            <div style="background: #f8fafc; border-top: 3px solid var(--primary-color); border-bottom: 3px solid var(--primary-color); padding: 3rem 1rem; margin: 3rem 0;">
                <div style="max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: 2fr 1fr; gap: 3rem; align-items: center;">
                    <div>
                        <h2 style="margin-top: 0; color: var(--text-color); display: flex; align-items: center;">
                            <i class="fa-solid fa-file-contract" style="margin-right: 0.75rem; color: var(--primary-color); font-size: 2rem;"></i>
                            Contract Management
                        </h2>
                        <p style="color: var(--text-light); line-height: 1.7; margin-bottom: 1.5rem;">
                            Create and manage contracts for <?php echo htmlspecialchars(getPersonTerm(true)); ?> or bulk support services. Track contract types, 
                            local authorities, procurement routes, and tender statuses throughout the contract lifecycle.
                        </p>
                        <ul style="list-style: none; padding: 0; margin: 0 0 1.5rem 0;">
                            <li style="padding: 0.5rem 0; color: var(--text-color);">
                                <i class="fa-solid fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                                Single <?php echo htmlspecialchars(getPersonTerm(true)); ?> & bulk contracts
                            </li>
                            <li style="padding: 0.5rem 0; color: var(--text-color);">
                                <i class="fa-solid fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                                Procurement route tracking
                            </li>
                            <li style="padding: 0.5rem 0; color: var(--text-color);">
                                <i class="fa-solid fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                                Tender status workflow
                            </li>
                            <li style="padding: 0.5rem 0; color: var(--text-color);">
                                <i class="fa-solid fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                                Contract expiry monitoring
                            </li>
                        </ul>
                        <a href="<?php echo url('pages/social-care-contracts-guide.php'); ?>" class="btn btn-primary" style="margin-top: 0.5rem; display: inline-block;">Learn About Contract Management</a>
                    </div>
                    <div>
                        <img src="<?php echo htmlspecialchars(url('assets/images/contract-management.jpeg')); ?>" alt="Contract Management" style="width: 310px; max-width: 100%; height: auto; border-radius: 0.5rem; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); object-fit: cover;">
                    </div>
                </div>
            </div>
            
            <!-- Core Features (remaining as cards) -->
            <div style="margin: 3rem 0; padding: 0 1rem;">
                <h2 style="text-align: center; margin-bottom: 1.5rem;">Additional Features</h2>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; max-width: 1200px; margin: 0 auto;">
                    <div class="card">
                        <h3 style="margin-top: 0;"><i class="fa-solid fa-diagram-project" style="margin-right: 0.75rem; color: var(--primary-color);"></i>Procurement Workflow</h3>
                        <p>Navigate the Scottish social care procurement process with confidence. Understand procurement routes, 
                        track contracts through the tender lifecycle, and manage contract extensions and retenders.</p>
                        <ul style="margin-left: 1.5rem; margin-top: 0.75rem; font-size: 0.95rem;">
                            <li>Procurement process guidance</li>
                            <li>Contract lifecycle tracking</li>
                            <li>Workflow dashboards</li>
                            <li>Best practice resources</li>
                        </ul>
                    </div>
                    
                    <div class="card">
                        <h3 style="margin-top: 0;"><i class="fa-solid fa-file-pen" style="margin-right: 0.75rem; color: var(--primary-color);"></i>Tender Applications</h3>
                        <p>Streamline your tender submission process with pre-filled applications. Complete your organisation profile once, 
                        and automatically reuse information across all tender applications, saving time and ensuring consistency.</p>
                        <ul style="margin-left: 1.5rem; margin-top: 0.75rem; font-size: 0.95rem;">
                            <li>Pre-filled application forms</li>
                            <li>Organisation profile management</li>
                            <li>Tender tracking and status</li>
                            <li>Deadline alerts and reminders</li>
                        </ul>
                    </div>
                    
                    <div class="card">
                        <h3 style="margin-top: 0;"><i class="fa-solid fa-users" style="margin-right: 0.75rem; color: var(--primary-color);"></i><?php echo ucfirst(htmlspecialchars(getPersonTerm(false))); ?> Tracking</h3>
                        <p>Track individuals across multiple contracts and local authorities. Link <?php echo htmlspecialchars(getPersonTerm(false)); ?> to contracts using 
                        various identifiers (CHI, SWIS, NI number) to maintain continuity of care records.</p>
                        <ul style="margin-left: 1.5rem; margin-top: 0.75rem; font-size: 0.95rem;">
                            <li>Multi-identifier support</li>
                            <li>Cross-authority tracking</li>
                            <li>Payment history</li>
                            <li>Contract history</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <style>
            @media (max-width: 1024px) {
                div[style*="grid-template-columns: repeat(3, 1fr)"] {
                    grid-template-columns: repeat(2, 1fr) !important;
                }
            }
            @media (max-width: 768px) {
                div[style*="grid-template-columns: repeat(3, 1fr)"],
                div[style*="grid-template-columns: repeat(2, 1fr)"] {
                    grid-template-columns: 1fr !important;
                }
            }
            </style>
            
            <!-- Financial & Rate Management -->
            <div style="margin: 3rem 0; padding: 0 1rem;">
                <h2 style="text-align: center; margin-bottom: 1.5rem;">Financial & Rate Management</h2>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; max-width: 1200px; margin: 0 auto;">
                    <div class="card">
                        <h3 style="margin-top: 0;"><i class="fa-solid fa-bell" style="margin-right: 0.75rem; color: var(--warning-color);"></i>Rate Monitoring</h3>
                        <p>Automated monitoring and validation of reference rates. Get alerts when rates need updating, 
                        track rate currency, and ensure your organisation stays compliant with current rate requirements.</p>
                        <ul style="margin-left: 1.5rem; margin-top: 0.75rem; font-size: 0.95rem;">
                            <li>Automatic rate validation</li>
                            <li>Currency alerts</li>
                            <li>Status indicators</li>
                            <li>Monitoring dashboard</li>
                        </ul>
                    </div>
                    
                    <div class="card">
                        <h3 style="margin-top: 0;"><i class="fa-solid fa-chart-bar" style="margin-right: 0.75rem; color: var(--primary-color);"></i>Reporting & Analytics</h3>
                        <p>Generate comprehensive reports on contracts, payments, and <?php echo htmlspecialchars(getPersonTerm(false)); ?>. Track trends over time 
                        and export data for analysis and compliance reporting.</p>
                        <ul style="margin-left: 1.5rem; margin-top: 0.75rem; font-size: 0.95rem;">
                            <li>Contract reports</li>
                            <li>Payment history</li>
                            <li>Rate analysis</li>
                            <li>Export capabilities</li>
                        </ul>
                    </div>
                    
                    <div class="card">
                        <h3 style="margin-top: 0;"><i class="fa-solid fa-calculator" style="margin-right: 0.75rem; color: var(--primary-color);"></i>Contract Value Management</h3>
                        <p>Track total contract values, monitor budget allocations, and manage financial aspects of contracts. 
                        Calculate contract worth, track amounts across contract types, and maintain financial oversight.</p>
                        <ul style="margin-left: 1.5rem; margin-top: 0.75rem; font-size: 0.95rem;">
                            <li>Total contract values</li>
                            <li>Budget tracking</li>
                            <li>Financial summaries</li>
                            <li>Value by contract type</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Organisation & Access Management -->
            <div style="margin: 3rem 0; padding: 0 1rem;">
                <h2 style="text-align: center; margin-bottom: 1.5rem;">Organisation & Access Management</h2>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; max-width: 1200px; margin: 0 auto;">
                    <div class="card">
                        <h3 style="margin-top: 0;"><i class="fa-solid fa-building" style="margin-right: 0.75rem; color: var(--primary-color);"></i>Multi-Organisation Support</h3>
                        <p>Each organisation has its own secure space with role-based access control. Organisations can 
                        customise their contract types, manage users, and maintain data independently.</p>
                        <ul style="margin-left: 1.5rem; margin-top: 0.75rem; font-size: 0.95rem;">
                            <li>Role-based access</li>
                            <li>Organisation isolation</li>
                            <li>Custom contract types</li>
                            <li>User management</li>
                        </ul>
                    </div>
                    
                    <div class="card">
                        <h3 style="margin-top: 0;"><i class="fa-solid fa-users-gear" style="margin-right: 0.75rem; color: var(--primary-color);"></i>Teams & Access Control</h3>
                        <p>Organise your staff into hierarchical teams with custom team types. Control contract access by team, 
                        allowing team managers to manage only their team's contracts while finance and senior managers oversee all contracts.</p>
                        <ul style="margin-left: 1.5rem; margin-top: 0.75rem; font-size: 0.95rem;">
                            <li>Hierarchical team structures</li>
                            <li>Custom team types</li>
                            <li>Team-based access control</li>
                            <li>Bulk import from CSV/JSON</li>
                        </ul>
                    </div>
                    
                    <div class="card">
                        <h3 style="margin-top: 0;"><i class="fa-solid fa-clipboard-check" style="margin-right: 0.75rem; color: var(--success-color);"></i>Compliance & Auditing</h3>
                        <p>Maintain comprehensive records for compliance and auditing purposes. Track contract compliance, 
                        maintain audit trails, and generate reports for regulatory requirements and internal reviews.</p>
                        <ul style="margin-left: 1.5rem; margin-top: 0.75rem; font-size: 0.95rem;">
                            <li>Audit trail maintenance</li>
                            <li>Compliance tracking</li>
                            <li>Record keeping</li>
                            <li>Regulatory reporting</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Dynamic Content Section 2 -->
            <div style="background: #f8fafc; border-top: 3px solid var(--primary-color); border-bottom: 3px solid var(--primary-color); padding: 3rem 1rem; margin: 3rem 0;">
                <div style="max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center; padding: 2rem 0;">
                    <div>
                        <h2 style="margin-top: 0; color: var(--text-color);">Stay Informed with Latest Updates</h2>
                        <p style="color: var(--text-light); line-height: 1.7; margin-bottom: 1.5rem;">
                            Get real-time updates on rate changes, policy announcements, and local authority positions. 
                            Our system aggregates information from across Scotland to keep you informed.
                        </p>
                        <ul style="list-style: none; padding: 0; margin: 0 0 1.5rem 0;">
                            <li style="padding: 0.5rem 0; color: var(--text-color);">
                                <i class="fa-solid fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                                Rate change notifications
                            </li>
                            <li style="padding: 0.5rem 0; color: var(--text-color);">
                                <i class="fa-solid fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                                Policy and legislation updates
                            </li>
                            <li style="padding: 0.5rem 0; color: var(--text-color);">
                                <i class="fa-solid fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                                Local authority announcements
                            </li>
                        </ul>
                        <a href="<?php echo url('pages/local-authority-rates.php'); ?>" class="btn btn-primary" style="margin-top: 0.5rem; display: inline-block;">View Updates</a>
                    </div>
                    <div>
                        <img src="<?php echo htmlspecialchars(url('assets/images/latest-updates.jpeg')); ?>" alt="Latest Updates and News" style="width: 100%; height: auto; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);">
                    </div>
                </div>
            </div>
            
            <!-- Contract Process Flow -->
            <div style="margin-top: 3rem; padding: 0 1rem;">
                <h2 style="text-align: center;">How Social Care Contracts Work in Scotland</h2>
                <div style="background: var(--bg-light); padding: 2rem; margin-top: 1.5rem;">
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;">
                        <div style="background: white; border: 2px solid var(--border-color); text-align: left; padding: 1.5rem; display: flex; flex-direction: column;">
                            <div style="width: 50px; height: 50px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; font-size: 1.25rem; font-weight: bold; flex-shrink: 0;">1</div>
                            <h4 style="margin: 0 0 0.75rem 0; font-size: 1.1rem; color: var(--text-color);">Strategic Planning</h4>
                            <p style="font-size: 0.9rem; color: var(--text-light); margin: 0 0 0.75rem 0; font-weight: 500;">Local authorities assess service needs</p>
                            <p style="font-size: 0.875rem; color: var(--text-light); margin: 0; line-height: 1.6; flex: 1;">Integration Joint Boards create strategic commissioning plans based on local population needs, budget constraints, and policy priorities.</p>
                        </div>
                        <div style="background: white; border: 2px solid var(--border-color); text-align: left; padding: 1.5rem; display: flex; flex-direction: column;">
                            <div style="width: 50px; height: 50px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; font-size: 1.25rem; font-weight: bold; flex-shrink: 0;">2</div>
                            <h4 style="margin: 0 0 0.75rem 0; font-size: 1.1rem; color: var(--text-color);">Market Engagement</h4>
                            <p style="font-size: 0.9rem; color: var(--text-light); margin: 0 0 0.75rem 0; font-weight: 500;">Discussions with providers</p>
                            <p style="font-size: 0.875rem; color: var(--text-light); margin: 0; line-height: 1.6; flex: 1;">Authorities engage with care providers to understand capacity, capabilities, and market conditions before formal procurement begins.</p>
                        </div>
                        <div style="background: white; border: 2px solid var(--border-color); text-align: left; padding: 1.5rem; display: flex; flex-direction: column;">
                            <div style="width: 50px; height: 50px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; font-size: 1.25rem; font-weight: bold; flex-shrink: 0;">3</div>
                            <h4 style="margin: 0 0 0.75rem 0; font-size: 1.1rem; color: var(--text-color);">Procurement</h4>
                            <p style="font-size: 0.9rem; color: var(--text-light); margin: 0 0 0.75rem 0; font-weight: 500;">Tender, framework, or direct award</p>
                            <p style="font-size: 0.875rem; color: var(--text-light); margin: 0; line-height: 1.6; flex: 1;">Contracts are awarded through competitive tendering, framework call-offs, direct awards (e.g., SDS Option 1), or spot purchases depending on circumstances.</p>
                        </div>
                        <div style="background: white; border: 2px solid var(--border-color); text-align: left; padding: 1.5rem; display: flex; flex-direction: column;">
                            <div style="width: 50px; height: 50px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; font-size: 1.25rem; font-weight: bold; flex-shrink: 0;">4</div>
                            <h4 style="margin: 0 0 0.75rem 0; font-size: 1.1rem; color: var(--text-color);">Evaluation</h4>
                            <p style="font-size: 0.9rem; color: var(--text-light); margin: 0 0 0.75rem 0; font-weight: 500;">Quality, price & social value</p>
                            <p style="font-size: 0.875rem; color: var(--text-light); margin: 0; line-height: 1.6; flex: 1;">Bids are evaluated against quality criteria (training, experience, Care Inspectorate ratings), price, and social value (community benefits, Fair Work compliance).</p>
                        </div>
                        <div style="background: white; border: 2px solid var(--border-color); text-align: left; padding: 1.5rem; display: flex; flex-direction: column;">
                            <div style="width: 50px; height: 50px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; font-size: 1.25rem; font-weight: bold; flex-shrink: 0;">5</div>
                            <h4 style="margin: 0 0 0.75rem 0; font-size: 1.1rem; color: var(--text-color);">Contract Award</h4>
                            <p style="font-size: 0.9rem; color: var(--text-light); margin: 0 0 0.75rem 0; font-weight: 500;">Contract goes live</p>
                            <p style="font-size: 0.875rem; color: var(--text-light); margin: 0; line-height: 1.6; flex: 1;">Successful provider receives contract award with defined terms, rates, duration, and service specifications. Contract becomes operational.</p>
                        </div>
                        <div style="background: white; border: 2px solid var(--border-color); text-align: left; padding: 1.5rem; display: flex; flex-direction: column;">
                            <div style="width: 50px; height: 50px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; font-size: 1.25rem; font-weight: bold; flex-shrink: 0;">6</div>
                            <h4 style="margin: 0 0 0.75rem 0; font-size: 1.1rem; color: var(--text-color);">Monitoring</h4>
                            <p style="font-size: 0.9rem; color: var(--text-light); margin: 0 0 0.75rem 0; font-weight: 500;">Ongoing review & management</p>
                            <p style="font-size: 0.875rem; color: var(--text-light); margin: 0; line-height: 1.6; flex: 1;">Authorities monitor service delivery, quality standards, and contract compliance. Regular reviews ensure value for money and prepare for extensions or retenders.</p>
                        </div>
                    </div>
                    <div style="text-align: center; margin-top: 2rem;">
                        <a href="<?php echo url('pages/social-care-contracts-guide.php'); ?>" class="btn btn-primary">Learn More About Contracts</a>
                    </div>
                </div>
            </div>
            <!-- Dynamic Content Section 3 -->
            <div style="background: #0f172a; color: white; padding: 3rem 1rem; margin: 3rem 0;">
                <div style="max-width: 1000px; margin: 0 auto;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center; padding: 2rem 0;">
                        <div>
                            <img src="<?php echo htmlspecialchars(url('assets/images/report.jpeg')); ?>" alt="Comprehensive Contract Management and Reporting" style="width: 100%; height: auto; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);">
                        </div>
                        <div style="display: flex; flex-direction: column; justify-content: center; min-height: 100%;">
                            <h3 style="color: white; margin-top: 0; margin-bottom: 1.5rem;">Comprehensive Contract Management</h3>
                            <p style="color: rgba(255,255,255,0.9); line-height: 1.7; margin-bottom: 2rem;">
                                Track every aspect of your contracts from procurement through to completion. 
                                Monitor tender statuses, manage rates, and maintain detailed records for compliance and reporting.
                            </p>
                            <ul style="list-style: none; padding: 0; margin: 0 0 2rem 0;">
                                <li style="padding: 0.5rem 0; color: rgba(255,255,255,0.9);">
                                    <i class="fa-solid fa-check" style="color: #10b981; margin-right: 0.5rem;"></i>
                                    Procurement tracking
                                </li>
                                <li style="padding: 0.5rem 0; color: rgba(255,255,255,0.9);">
                                    <i class="fa-solid fa-check" style="color: #10b981; margin-right: 0.5rem;"></i>
                                    Tender status monitoring
                                </li>
                                <li style="padding: 0.5rem 0; color: rgba(255,255,255,0.9);">
                                    <i class="fa-solid fa-check" style="color: #10b981; margin-right: 0.5rem;"></i>
                                    Compliance reporting
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Latest Updates -->
            <?php if (!empty($recentUpdates)): ?>
                <div style="margin-top: 3rem; padding: 0 1rem;">
                    <h2 style="text-align: center;">Latest Updates & News</h2>
                    <p style="text-align: center; color: var(--text-light); margin-bottom: 1.5rem;">
                        Stay informed about rate changes, policy updates, and news from local authorities
                    </p>
                    <div style="display: grid; gap: 1.5rem; margin-top: 1.5rem; max-width: 900px; margin-left: auto; margin-right: auto;">
                        <?php foreach (array_slice($recentUpdates, 0, 3) as $update): ?>
                            <div style="background: white; border: 2px solid var(--border-color); padding: 1.5rem; display: grid; grid-template-columns: 120px 1fr; gap: 1.5rem;">
                                <div style="background: #f1f5f9; border: 1px solid var(--border-color); min-height: 100px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fa-solid fa-image" style="font-size: 1.5rem; color: var(--text-light); opacity: 0.3;"></i>
                                </div>
                                <div>
                                    <h3 style="margin-top: 0; margin-bottom: 0.5rem;">
                                        <?php echo htmlspecialchars($update['title']); ?>
                                    </h3>
                                    <?php if ($update['local_authority_name']): ?>
                                        <p style="margin: 0 0 0.5rem 0; color: var(--primary-color); font-weight: 600;">
                                            <?php echo htmlspecialchars($update['local_authority_name']); ?>
                                        </p>
                                    <?php endif; ?>
                                    <p style="margin: 0 0 0.75rem 0; color: var(--text-light); line-height: 1.6;">
                                        <?php echo htmlspecialchars(substr($update['content'], 0, 200)); ?>
                                        <?php echo strlen($update['content']) > 200 ? '...' : ''; ?>
                                    </p>
                                    <div style="display: flex; gap: 1rem; flex-wrap: wrap; font-size: 0.9rem; color: var(--text-light);">
                                        <?php if ($update['published_date']): ?>
                                            <span><i class="fa-solid fa-calendar" style="margin-right: 0.25rem;"></i><?php echo date('d M Y', strtotime($update['published_date'])); ?></span>
                                        <?php endif; ?>
                                        <?php if ($update['rate_change']): ?>
                                            <span><i class="fa-solid fa-pound-sign" style="margin-right: 0.25rem; color: var(--success-color);"></i>Rate Change: £<?php echo number_format($update['rate_change'], 2); ?>/hr</span>
                                        <?php endif; ?>
                                        <?php if ($update['source_url']): ?>
                                            <a href="<?php echo htmlspecialchars($update['source_url']); ?>" target="_blank" style="color: var(--primary-color); text-decoration: none;">Read More →</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="text-align: center; margin-top: 1.5rem;">
                        <a href="<?php echo url('pages/local-authority-rates.php'); ?>" class="btn btn-secondary">View All Updates</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Key Resources -->
            <div style="margin-top: 3rem; padding: 0 1rem;">
                <h2 style="text-align: center;">Key Resources</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-top: 1.5rem; max-width: 900px; margin-left: auto; margin-right: auto;">
                    <a href="<?php echo url('pages/social-care-contracts-guide.php'); ?>" style="background: white; border: 2px solid var(--border-color); padding: 1.5rem; text-decoration: none; text-align: center; display: block;">
                        <h3 style="margin-top: 0; color: var(--text-color);"><i class="fa-solid fa-book" style="margin-right: 0.5rem; color: var(--primary-color);"></i>Contracts Guide</h3>
                        <p style="color: var(--text-light); margin: 0.5rem 0 0 0;">Comprehensive guide to social care contracts in Scotland</p>
                    </a>
                    
                    <a href="<?php echo url('pages/local-authority-rates.php'); ?>" style="background: white; border: 2px solid var(--border-color); padding: 1.5rem; text-decoration: none; text-align: center; display: block;">
                        <h3 style="margin-top: 0; color: var(--text-color);"><i class="fa-solid fa-chart-line" style="margin-right: 0.5rem; color: var(--success-color);"></i>Rate Information</h3>
                        <p style="color: var(--text-light); margin: 0.5rem 0 0 0;">Reference rates and local authority updates</p>
                    </a>
                    
                    <a href="<?php echo url('pages/documentation.php'); ?>" style="background: white; border: 2px solid var(--border-color); padding: 1.5rem; text-decoration: none; text-align: center; display: block;">
                        <h3 style="margin-top: 0; color: var(--text-color);"><i class="fa-solid fa-file-lines" style="margin-right: 0.5rem; color: var(--primary-color);"></i>Documentation</h3>
                        <p style="color: var(--text-light); margin: 0.5rem 0 0 0;">Complete system documentation including teams management</p>
                    </a>
                </div>
            </div>
            
            <!-- Dynamic Content Section 4 -->
            <div style="background: #fef3c7; border-top: 3px solid #f59e0b; border-bottom: 3px solid #f59e0b; padding: 3rem 1rem; margin: 3rem 0;">
                <div style="max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center; padding: 2rem 0;">
                    <div>
                        <img src="<?php echo htmlspecialchars(url('assets/images/documentatiuion.jpeg')); ?>" alt="Expert Guidance and Documentation" style="width: 100%; height: auto; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);">
                    </div>
                    <div style="display: flex; flex-direction: column; justify-content: center; min-height: 100%;">
                        <h3 style="margin-top: 0; margin-bottom: 1.5rem; color: #78350f;">Expert Guidance & Support</h3>
                        <p style="color: #92400e; line-height: 1.7; margin-bottom: 1.5rem;">
                            Access comprehensive documentation, how-to guides, and FAQs to help you make the most of the platform. 
                            Our resources are designed to support both new and experienced users.
                        </p>
                        <ul style="list-style: none; padding: 0; margin: 0 0 2rem 0;">
                            <li style="padding: 0.5rem 0; color: #78350f;">
                                <i class="fa-solid fa-check" style="color: #10b981; margin-right: 0.5rem;"></i>
                                Step-by-step guides
                            </li>
                            <li style="padding: 0.5rem 0; color: #78350f;">
                                <i class="fa-solid fa-check" style="color: #10b981; margin-right: 0.5rem;"></i>
                                Comprehensive documentation
                            </li>
                            <li style="padding: 0.5rem 0; color: #78350f;">
                                <i class="fa-solid fa-check" style="color: #10b981; margin-right: 0.5rem;"></i>
                                Teams management & access control guides
                            </li>
                            <li style="padding: 0.5rem 0; color: #78350f;">
                                <i class="fa-solid fa-check" style="color: #10b981; margin-right: 0.5rem;"></i>
                                Frequently asked questions
                            </li>
                        </ul>
                        <div style="display: flex; gap: 1rem; margin-top: 0; flex-wrap: wrap;">
                            <a href="<?php echo url('pages/documentation.php'); ?>" class="btn" style="background: #78350f; color: white; padding: 0.75rem 1.5rem; text-decoration: none; display: inline-block; font-weight: 600;">Documentation</a>
                            <a href="<?php echo url('pages/how-tos.php'); ?>" class="btn" style="background: white; color: #78350f; border: 2px solid #78350f; padding: 0.75rem 1.5rem; text-decoration: none; display: inline-block; font-weight: 600;">How-to Guides</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact for Organisation Setup -->
            <div id="contact-organisation" style="text-align: center; margin-top: 4rem; padding: 3rem 2rem; background: #1e40af; color: white; padding: 0 1rem;">
                <div style="max-width: 800px; margin: 0 auto; padding: 3rem 0;">
                    <h2 style="color: white; margin-bottom: 1rem;">Is Your Organisation Ready to Get Started?</h2>
                    <p style="color: rgba(255,255,255,0.9); margin-bottom: 0.5rem; font-size: 1.1rem;">
                        To use this platform, your organisation needs to be set up first.
                    </p>
                    <p style="color: rgba(255,255,255,0.9); margin-bottom: 1rem; font-size: 1.1rem;">
                        Contact us to get your organisation registered and seats allocated.
                    </p>
                    <p style="color: rgba(255,255,255,0.8); margin-bottom: 2rem; font-size: 1rem;">
                        Once your organisation is set up, your staff can register and log in using their organisation email addresses.
                    </p>
                    <div style="background: rgba(255,255,255,0.1); border: 2px solid rgba(255,255,255,0.2); border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 2rem; text-align: left; max-width: 500px; margin-left: auto; margin-right: auto;">
                        <p style="margin: 0 0 0.75rem 0; color: white; font-weight: 600;">Contact us to set up your organisation:</p>
                        <p style="margin: 0; color: rgba(255,255,255,0.9);">
                            <i class="fa-solid fa-envelope" style="margin-right: 0.5rem;"></i>
                            <a href="mailto:socialcarecontracts@outlook.com?subject=Organisation Setup Request" style="color: white; text-decoration: underline;">Send us an email</a>
                        </p>
                        <p style="margin: 0.75rem 0 0 0; color: rgba(255,255,255,0.8); font-size: 0.95rem;">
                            Please include your organisation name and the number of staff members who will need access.
                        </p>
                    </div>
                    <a href="mailto:socialcarecontracts@outlook.com?subject=Organisation Setup Request" class="btn" style="background: white; color: #1e40af; padding: 0.875rem 2rem; font-weight: 600; text-decoration: none; display: inline-block;">Contact Us to Get Started</a>
                </div>
            </div>
            
            <!-- Staff Login/Register Section -->
            <div style="text-align: center; margin-top: 3rem; padding: 2rem 1rem; background: #f8fafc; border-top: 3px solid var(--primary-color);">
                <div style="max-width: 600px; margin: 0 auto;">
                    <h2 style="margin-bottom: 1rem; color: var(--text-color);">If your org is already set up?</h2>
                    <p style="color: var(--text-light); margin-bottom: 1.5rem;">
                        You can register or log in using your organisation email address.
                    </p>
                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="<?php echo htmlspecialchars(url('register.php')); ?>" class="btn btn-primary" style="padding: 0.75rem 2rem; font-weight: 600; text-decoration: none; display: inline-block;">Register</a>
                        <a href="<?php echo htmlspecialchars(url('login.php')); ?>" class="btn btn-secondary" style="padding: 0.75rem 2rem; font-weight: 600; text-decoration: none; display: inline-block;">Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
@media (max-width: 768px) {
    .hero-content-grid {
        grid-template-columns: 1fr !important;
    }
    
    .hero-content-grid > div:first-child {
        min-height: auto !important;
    }
    
    .hero-content-grid .btn {
        width: 100%;
        justify-content: center;
        margin-left: 0 !important;
    }
    
    .hero-content-grid img {
        max-height: 500px;
        height: auto !important;
        object-fit: cover;
    }
}
</style>

<?php include INCLUDES_PATH . '/footer.php'; ?>

