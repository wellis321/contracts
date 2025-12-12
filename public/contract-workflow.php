<?php
/**
 * Contract Workflow Dashboard
 * Helps users manage contracts within the procurement framework
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
$organisationId = Auth::getOrganisationId();

// Get user's accessible team IDs (for filtering contracts)
$accessibleTeamIds = RBAC::getAccessibleTeamIds();

// Get contracts expiring soon (filtered by team access)
$expiringSoon = Contract::getExpiringSoon($organisationId, 6, $accessibleTeamIds);

// Get contracts by tender status (filtered by team access)
$db = getDbConnection();
$tenderStatuses = TenderStatus::findAll();
$contractsByStatus = [];
foreach ($tenderStatuses as $status) {
    $contracts = Contract::findByTenderStatus($organisationId, $status['name'], $accessibleTeamIds);
    if (!empty($contracts)) {
        $contractsByStatus[$status['name']] = $contracts;
    }
}

// Get contracts without tender status
$stmt = $db->prepare("
    SELECT COUNT(*) as count 
    FROM contracts 
    WHERE organisation_id = ? 
    AND (tender_status IS NULL OR tender_status = '')
    AND status = 'active'
");
$stmt->execute([$organisationId]);
$noStatusCount = $stmt->fetch()['count'];

$pageTitle = 'Contract Workflow';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Contract Workflow Dashboard</h2>
        <p style="color: var(--text-light); margin-top: 0.5rem;">
            Manage your contracts through the procurement lifecycle
        </p>
    </div>
    
    <!-- Quick Links -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <a href="<?php echo url('contracts.php'); ?>" class="card" style="text-decoration: none; padding: 1.5rem; text-align: center; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white;">
            <h3 style="color: white; margin: 0 0 0.5rem 0;">All Contracts</h3>
            <p style="color: rgba(255,255,255,0.9); margin: 0;">View and manage all contracts</p>
        </a>
        
        <a href="<?php echo url('pages/social-care-contracts-guide.php'); ?>" class="card" style="text-decoration: none; padding: 1.5rem; text-align: center; background: linear-gradient(135deg, var(--success-color), #059669); color: white;">
            <h3 style="color: white; margin: 0 0 0.5rem 0;">Contracts Guide</h3>
            <p style="color: rgba(255,255,255,0.9); margin: 0;">Learn about the process</p>
        </a>
        
        <a href="<?php echo url('pages/local-authority-rates.php'); ?>" class="card" style="text-decoration: none; padding: 1.5rem; text-align: center; background: linear-gradient(135deg, var(--warning-color), #d97706); color: white;">
            <h3 style="color: white; margin: 0 0 0.5rem 0;">Rate Information</h3>
            <p style="color: rgba(255,255,255,0.9); margin: 0;">Reference rates & updates</p>
        </a>
    </div>
    
    <!-- Contracts Expiring Soon -->
    <?php if (!empty($expiringSoon)): ?>
        <div style="margin-bottom: 2rem;">
            <h3><i class="fa-solid fa-triangle-exclamation" style="margin-right: 0.5rem; color: var(--warning-color);"></i>Contracts Expiring Soon (Next 6 Months)</h3>
            <p style="color: var(--text-light); margin-bottom: 1rem;">
                These contracts are ending soon. Consider extension negotiations or prepare for retender.
            </p>
            <table class="table">
                    <thead>
                        <tr>
                            <th>Contract</th>
                            <th>Local Authority</th>
                            <th>End Date</th>
                            <th>Days Remaining</th>
                            <th>Tender Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expiringSoon as $contract): 
                            $endDate = strtotime($contract['end_date']);
                            $daysRemaining = floor(($endDate - time()) / (60 * 60 * 24));
                            $isUrgent = $daysRemaining < 90;
                        ?>
                            <tr onclick="window.location.href='<?php echo htmlspecialchars(url('contract-view.php?id=' . $contract['id'])); ?>'" style="cursor: pointer; transition: background-color 0.2s; <?php echo $isUrgent ? 'background-color: #fee2e2;' : ''; ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($contract['title']); ?></strong>
                                    <?php if ($contract['contract_number']): ?>
                                        <br><small style="color: var(--text-light);"><?php echo htmlspecialchars($contract['contract_number']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($contract['local_authority_name']); ?></td>
                                <td><?php echo date(DATE_FORMAT, strtotime($contract['end_date'])); ?></td>
                                <td>
                                    <strong style="color: <?php echo $isUrgent ? 'var(--danger-color)' : 'var(--warning-color)'; ?>;">
                                        <?php echo $daysRemaining; ?> days
                                    </strong>
                                </td>
                                <td>
                                    <?php if ($contract['tender_status']): ?>
                                        <?php echo htmlspecialchars($contract['tender_status']); ?>
                                    <?php else: ?>
                                        <span style="color: var(--text-light);">Not set</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <!-- Contracts by Tender Status -->
    <?php if (!empty($contractsByStatus)): ?>
        <div style="margin-bottom: 2rem;">
            <h3>Contracts by Tender Status</h3>
            <p style="color: var(--text-light); margin-bottom: 1rem;">
                Track where your contracts are in the procurement lifecycle
            </p>
            
            <?php foreach ($contractsByStatus as $statusName => $contracts): ?>
                <div style="margin-bottom: 2rem;">
                    <h4><?php echo htmlspecialchars($statusName); ?> (<?php echo count($contracts); ?>)</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Contract</th>
                                <th>Local Authority</th>
                                <th>Procurement Route</th>
                                <th>Start Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contracts as $contract): ?>
                                <tr onclick="window.location.href='<?php echo htmlspecialchars(url('contract-view.php?id=' . $contract['id'])); ?>'" style="cursor: pointer; transition: background-color 0.2s;">
                                    <td>
                                        <strong><?php echo htmlspecialchars($contract['title']); ?></strong>
                                        <?php if ($contract['contract_number']): ?>
                                            <br><small style="color: var(--text-light);"><?php echo htmlspecialchars($contract['contract_number']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($contract['local_authority_name']); ?></td>
                                    <td>
                                        <?php if ($contract['procurement_route']): ?>
                                            <?php echo htmlspecialchars($contract['procurement_route']); ?>
                                        <?php else: ?>
                                            <span style="color: var(--text-light);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date(DATE_FORMAT, strtotime($contract['start_date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Contracts Without Tender Status -->
    <?php if ($noStatusCount > 0): ?>
        <div class="card" style="background: #fef3c7; border: 1px solid var(--warning-color);">
            <h3><i class="fa-solid fa-triangle-exclamation" style="margin-right: 0.5rem; color: var(--warning-color);"></i>Contracts Missing Tender Status</h3>
            <p>You have <?php echo $noStatusCount; ?> active contract(s) without a tender status set. 
            <a href="<?php echo url('contracts.php'); ?>" style="color: var(--primary-color);">Update them</a> to better track your contract pipeline.</p>
        </div>
    <?php endif; ?>
    
    <!-- Workflow Tips -->
    <div style="margin-top: 2rem;">
        <h3>Workflow Tips</h3>
        <div class="card" style="margin-top: 1rem;">
            <ul style="margin-left: 2rem;">
                <li style="margin-bottom: 0.75rem;">
                    <strong>Set Tender Status:</strong> Update the tender status as contracts progress through the procurement process. This helps you see your pipeline at a glance.
                </li>
                <li style="margin-bottom: 0.75rem;">
                    <strong>Track Expiring Contracts:</strong> Review contracts expiring in the next 6 months regularly. Start extension negotiations or retender preparations early.
                </li>
                <li style="margin-bottom: 0.75rem;">
                    <strong>Record Procurement Route:</strong> Understanding how you won contracts helps with future tenders and rate negotiations.
                </li>
                <li style="margin-bottom: 0.75rem;">
                    <strong>Monitor Rate Changes:</strong> Use the <a href="<?php echo url('pages/local-authority-rates.php'); ?>" style="color: var(--primary-color);">Local Authority Rates</a> page to stay informed about rate updates.
                </li>
                <li style="margin-bottom: 0.75rem;">
                    <strong>Link People:</strong> For single person contracts, link the person being supported to track their journey across local authorities.
                </li>
            </ul>
        </div>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
