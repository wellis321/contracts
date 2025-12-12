<?php
/**
 * Local Authority Detail View
 * Shows detailed information for a specific local authority
 */
require_once dirname(__DIR__) . '/config/config.php';

$isLoggedIn = Auth::isLoggedIn();
if (!$isLoggedIn) {
    header('Location: ' . url('login.php'));
    exit;
}

$organisationId = Auth::getOrganisationId();
$laId = !empty($_GET['id']) ? (int)$_GET['id'] : null;

if (!$laId) {
    header('Location: ' . url('reports.php'));
    exit;
}

$db = getDbConnection();

// Get local authority details
$stmt = $db->prepare("SELECT * FROM local_authorities WHERE id = ?");
$stmt->execute([$laId]);
$localAuthority = $stmt->fetch();

if (!$localAuthority) {
    header('Location: ' . url('reports.php'));
    exit;
}

// Get user's accessible team IDs (for filtering contracts)
$accessibleTeamIds = RBAC::getAccessibleTeamIds();

// Get contracts for this local authority
$contracts = Contract::findByOrganisation($organisationId, null, $accessibleTeamIds);
$laContracts = array_filter($contracts, function($contract) use ($laId) {
    return ($contract['local_authority_id'] ?? null) == $laId;
});

// Deduplicate contracts
$seenIds = [];
$seenContractKeys = [];
$uniqueContracts = [];

foreach ($laContracts as $contract) {
    $id = $contract['id'] ?? null;
    $contractNumber = $contract['contract_number'] ?? '';
    $title = $contract['title'] ?? '';
    $startDate = $contract['start_date'] ?? '';
    $endDate = $contract['end_date'] ?? '';
    
    if ($id && in_array($id, $seenIds)) {
        continue;
    }
    
    if ($contractNumber) {
        $contractKey = md5($contractNumber . '|' . $title . '|' . $startDate . '|' . $endDate);
        if (in_array($contractKey, $seenContractKeys)) {
            continue;
        }
        $seenContractKeys[] = $contractKey;
    }
    
    if ($id) {
        $seenIds[] = $id;
    }
    $uniqueContracts[] = $contract;
}

$laContracts = $uniqueContracts;

// Helper functions
function extractPersonName($title) {
    $patterns = [
        '/\s*-\s*(Enhanced\s+Support\s+Package|Support\s+Hours|Personal\s+Care|Complex\s+Support|Supported\s+Living|Waking\/Active\s+Hours|Sleepover\s+Hours|Daytime\s+Hours|Night\s+Hours|Respite\s+Care|Emergency\s+Support|Community\s+Support|Bulk\s+Support\s+Contract).*$/i',
        '/\s*-\s*.*$/i',
    ];
    
    foreach ($patterns as $pattern) {
        $title = preg_replace($pattern, '', $title);
    }
    
    return trim($title);
}

function abbreviateContractType($typeName) {
    $abbreviations = [
        'Complex Support Package' => 'CSP',
        'Support Hours' => 'SH',
        'Personal Care' => 'PC',
        'Waking/Active Hours' => 'WAH',
        'Sleepover Hours' => 'SOH',
        'Daytime Hours' => 'DH',
        'Night Hours' => 'NH',
        'Respite Care' => 'RC',
        'Emergency Support' => 'ES',
        'Community Support' => 'CS',
        'Bulk Support Contract' => 'BSC',
        'Supported Living' => 'SL'
    ];
    
    return $abbreviations[$typeName] ?? substr($typeName, 0, 3);
}

// Handle sorting
$sortField = $_GET['sort'] ?? 'title';
$sortOrder = $_GET['order'] ?? 'asc';

// Sort contracts
usort($laContracts, function($a, $b) use ($sortField, $sortOrder) {
    $result = 0;
    
    switch ($sortField) {
        case 'title':
            $result = strcasecmp(extractPersonName($a['title'] ?? ''), extractPersonName($b['title'] ?? ''));
            break;
        case 'contract_number':
            $result = strcasecmp($a['contract_number'] ?? '', $b['contract_number'] ?? '');
            break;
        case 'type':
            $result = strcasecmp($a['contract_type_name'] ?? '', $b['contract_type_name'] ?? '');
            break;
        case 'start_date':
            $result = strtotime($a['start_date'] ?? '1970-01-01') - strtotime($b['start_date'] ?? '1970-01-01');
            break;
        case 'end_date':
            $aEnd = $a['end_date'] ? strtotime($a['end_date']) : PHP_INT_MAX;
            $bEnd = $b['end_date'] ? strtotime($b['end_date']) : PHP_INT_MAX;
            $result = $aEnd - $bEnd;
            break;
        case 'value':
            $result = ($a['total_amount'] ?? 0) - ($b['total_amount'] ?? 0);
            break;
        case 'status':
            $aStatus = Contract::getEffectiveStatus($a);
            $bStatus = Contract::getEffectiveStatus($b);
            $result = strcasecmp($aStatus, $bStatus);
            break;
        default:
            $result = 0;
    }
    
    return $sortOrder === 'desc' ? -$result : $result;
});

// Detect issues
$inactiveContracts = [];
$endingSoonContracts = [];
$today = time();
$threeMonthsFromNow = strtotime('+3 months');

foreach ($laContracts as $contract) {
    $effectiveStatus = Contract::getEffectiveStatus($contract);
    $endDate = $contract['end_date'] ? strtotime($contract['end_date']) : null;
    
    // Inactive contracts that should be active (future end date or no end date)
    if ($effectiveStatus === 'inactive' && (!$endDate || $endDate > $today)) {
        $inactiveContracts[] = $contract;
    }
    
    // Contracts ending soon (within 3 months)
    if ($effectiveStatus === 'active' && $endDate && $endDate >= $today && $endDate <= $threeMonthsFromNow) {
        $endingSoonContracts[] = $contract;
    }
}

// Get rates for this local authority
$stmt = $db->prepare("
    SELECT r.*, ct.name as contract_type_name
    FROM rates r
    LEFT JOIN contract_types ct ON r.contract_type_id = ct.id
    WHERE r.local_authority_id = ? AND r.is_current = 1
    ORDER BY ct.name
");
$stmt->execute([$laId]);
$rates = $stmt->fetchAll();

// Deduplicate rates
$seenRateIds = [];
$seenRateKeys = [];
$uniqueRates = [];
foreach ($rates as $rate) {
    $id = $rate['id'] ?? null;
    if ($id && in_array($id, $seenRateIds)) {
        continue;
    }
    
    $rateKey = md5(($rate['contract_type_name'] ?? '') . '|' . ($rate['rate_amount'] ?? '') . '|' . ($rate['effective_from'] ?? ''));
    if (in_array($rateKey, $seenRateKeys)) {
        continue;
    }
    $seenRateKeys[] = $rateKey;
    
    if ($id) {
        $seenRateIds[] = $id;
    }
    $uniqueRates[] = $rate;
}
$rates = $uniqueRates;

// Get recent updates
$updates = LocalAuthorityRateInfo::getUpdatesByLocalAuthority($laId, 5);

// Get tender applications
$stmt = $db->prepare("
    SELECT ta.*
    FROM tender_applications ta
    WHERE ta.organisation_id = ? AND ta.local_authority_id = ?
    ORDER BY ta.created_at DESC
    LIMIT 10
");
$stmt->execute([$organisationId, $laId]);
$tenderApplications = $stmt->fetchAll();

// Get recent payments
$stmt = $db->prepare("
    SELECT cp.*, c.title as contract_title, c.id as contract_id, pm.name as payment_method_name
    FROM contract_payments cp
    INNER JOIN contracts c ON cp.contract_id = c.id
    LEFT JOIN payment_methods pm ON cp.payment_method_id = pm.id
    WHERE c.organisation_id = ? AND c.local_authority_id = ?
    ORDER BY cp.payment_date DESC
    LIMIT 10
");
$stmt->execute([$organisationId, $laId]);
$payments = $stmt->fetchAll();

// Deduplicate payments
$seenPaymentIds = [];
$seenPaymentKeys = [];
$uniquePayments = [];
foreach ($payments as $payment) {
    $id = $payment['id'] ?? null;
    if ($id && in_array($id, $seenPaymentIds)) {
        continue;
    }
    
    $paymentKey = md5(($payment['contract_title'] ?? '') . '|' . ($payment['amount'] ?? '') . '|' . ($payment['payment_date'] ?? '') . '|' . ($payment['payment_method_name'] ?? ''));
    if (in_array($paymentKey, $seenPaymentKeys)) {
        continue;
    }
    $seenPaymentKeys[] = $paymentKey;
    
    if ($id) {
        $seenPaymentIds[] = $id;
    }
    $uniquePayments[] = $payment;
}
$payments = $uniquePayments;

// Group contracts by type
$contractsByType = [];
foreach ($laContracts as $contract) {
    $typeName = $contract['contract_type_name'] ?? 'Unknown';
    if (!isset($contractsByType[$typeName])) {
        $contractsByType[$typeName] = 0;
    }
    $contractsByType[$typeName]++;
}

// Handle contract type filter
$typeFilter = !empty($_GET['type']) ? $_GET['type'] : null;
$filteredContracts = $laContracts;
if ($typeFilter) {
    $filteredContracts = array_filter($laContracts, function($contract) use ($typeFilter) {
        return ($contract['contract_type_name'] ?? 'Unknown') === $typeFilter;
    });
}

$pageTitle = 'Local Authority: ' . htmlspecialchars($localAuthority['name']);
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-between" style="align-items: start;">
            <div>
                <h2><?php echo htmlspecialchars($localAuthority['name']); ?></h2>
                <p>Detailed information about contracts, rates, and activity</p>
            </div>
            <a href="<?php echo htmlspecialchars(url('reports.php')); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>
    
    <?php if (!empty($inactiveContracts) || !empty($endingSoonContracts)): ?>
        <div class="alert alert-warning" style="margin: 1.5rem; padding: 1rem 1.5rem;">
            <h4 style="margin-top: 0;"><i class="fas fa-exclamation-triangle"></i> Issues Detected</h4>
            <?php if (!empty($inactiveContracts)): ?>
                <div style="margin-bottom: 0.75rem;">
                    <strong>Inactive Contract(s):</strong>
                    <ul style="margin: 0.5rem 0 0 1.5rem; padding: 0;">
                        <?php foreach ($inactiveContracts as $contract): ?>
                            <li>
                                <?php echo htmlspecialchars(extractPersonName($contract['title'])); ?>
                                (<?php echo htmlspecialchars($contract['contract_number'] ?? 'N/A'); ?>)
                                <?php if ($contract['end_date']): ?>
                                    - End date: <?php echo date(DATE_FORMAT, strtotime($contract['end_date'])); ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if (!empty($endingSoonContracts)): ?>
                <div>
                    <strong>Contract(s) Ending Soon:</strong>
                    <ul style="margin: 0.5rem 0 0 1.5rem; padding: 0;">
                        <?php foreach ($endingSoonContracts as $contract): ?>
                            <li>
                                <?php echo htmlspecialchars(extractPersonName($contract['title'])); ?>
                                (<?php echo htmlspecialchars($contract['contract_number'] ?? 'N/A'); ?>)
                                - End date: <?php echo date(DATE_FORMAT, strtotime($contract['end_date'])); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Contracts by Type -->
    <?php if (!empty($contractsByType)): ?>
        <div style="margin: 1.5rem; padding: 1rem; background: var(--bg-light); border-radius: 0.5rem;">
            <h3 style="margin-top: 0;">Contracts by Type</h3>
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                <?php if ($typeFilter): ?>
                    <a href="?id=<?php echo $laId; ?>" class="btn btn-secondary" style="text-decoration: none;">
                        Show All
                    </a>
                <?php endif; ?>
                <?php foreach ($contractsByType as $typeName => $count): ?>
                    <a href="?id=<?php echo $laId; ?>&type=<?php echo urlencode($typeName); ?>" 
                       class="btn <?php echo $typeFilter === $typeName ? 'btn-primary' : 'btn-secondary'; ?>"
                       style="text-decoration: none;"
                       onclick="filterContractsByType('<?php echo htmlspecialchars($typeName); ?>'); return false;">
                        <?php echo htmlspecialchars($typeName); ?> (<?php echo $count; ?>)
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if ($typeFilter): ?>
                <div style="margin-top: 0.75rem; padding: 0.5rem; background: white; border-radius: 0.25rem; border: 1px solid var(--border-color);">
                    <strong>Filtered by:</strong> <?php echo htmlspecialchars($typeFilter); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Contracts Table -->
    <div id="contracts-section" style="margin: 1.5rem;">
        <h3>Contracts (<?php echo count($filteredContracts); ?>)</h3>
        <?php if (empty($filteredContracts)): ?>
            <p style="color: var(--text-light);">No contracts found for this local authority.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th class="sortable-header" onclick="sortTable('title')" style="cursor: pointer;">
                            Title
                            <?php if ($sortField === 'title'): ?>
                                <i class="fas fa-sort-<?php echo $sortOrder === 'asc' ? 'up' : 'down'; ?>" style="margin-left: 0.5rem;"></i>
                            <?php else: ?>
                                <i class="fas fa-sort" style="margin-left: 0.5rem; opacity: 0.3;"></i>
                            <?php endif; ?>
                        </th>
                        <th class="sortable-header" onclick="sortTable('contract_number')" style="cursor: pointer;">
                            Contract #
                            <?php if ($sortField === 'contract_number'): ?>
                                <i class="fas fa-sort-<?php echo $sortOrder === 'asc' ? 'up' : 'down'; ?>" style="margin-left: 0.5rem;"></i>
                            <?php else: ?>
                                <i class="fas fa-sort" style="margin-left: 0.5rem; opacity: 0.3;"></i>
                            <?php endif; ?>
                        </th>
                        <th class="sortable-header" onclick="sortTable('type')" style="cursor: pointer;">
                            Type
                            <?php if ($sortField === 'type'): ?>
                                <i class="fas fa-sort-<?php echo $sortOrder === 'asc' ? 'up' : 'down'; ?>" style="margin-left: 0.5rem;"></i>
                            <?php else: ?>
                                <i class="fas fa-sort" style="margin-left: 0.5rem; opacity: 0.3;"></i>
                            <?php endif; ?>
                        </th>
                        <th class="sortable-header" onclick="sortTable('start_date')" style="cursor: pointer;">
                            Start Date
                            <?php if ($sortField === 'start_date'): ?>
                                <i class="fas fa-sort-<?php echo $sortOrder === 'asc' ? 'up' : 'down'; ?>" style="margin-left: 0.5rem;"></i>
                            <?php else: ?>
                                <i class="fas fa-sort" style="margin-left: 0.5rem; opacity: 0.3;"></i>
                            <?php endif; ?>
                        </th>
                        <th class="sortable-header" onclick="sortTable('end_date')" style="cursor: pointer;">
                            End Date
                            <?php if ($sortField === 'end_date'): ?>
                                <i class="fas fa-sort-<?php echo $sortOrder === 'asc' ? 'up' : 'down'; ?>" style="margin-left: 0.5rem;"></i>
                            <?php else: ?>
                                <i class="fas fa-sort" style="margin-left: 0.5rem; opacity: 0.3;"></i>
                            <?php endif; ?>
                        </th>
                        <th class="sortable-header" onclick="sortTable('value')" style="cursor: pointer;">
                            Value
                            <?php if ($sortField === 'value'): ?>
                                <i class="fas fa-sort-<?php echo $sortOrder === 'asc' ? 'up' : 'down'; ?>" style="margin-left: 0.5rem;"></i>
                            <?php else: ?>
                                <i class="fas fa-sort" style="margin-left: 0.5rem; opacity: 0.3;"></i>
                            <?php endif; ?>
                        </th>
                        <th class="sortable-header" onclick="sortTable('status')" style="cursor: pointer;">
                            Status
                            <?php if ($sortField === 'status'): ?>
                                <i class="fas fa-sort-<?php echo $sortOrder === 'asc' ? 'up' : 'down'; ?>" style="margin-left: 0.5rem;"></i>
                            <?php else: ?>
                                <i class="fas fa-sort" style="margin-left: 0.5rem; opacity: 0.3;"></i>
                            <?php endif; ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filteredContracts as $contract): ?>
                        <?php 
                        $effectiveStatus = Contract::getEffectiveStatus($contract);
                        ?>
                        <tr class="contract-<?php echo $effectiveStatus === 'active' ? 'active' : 'inactive'; ?>" 
                            onclick="window.location.href='<?php echo htmlspecialchars(url('contract-view.php?id=' . $contract['id'])); ?>';" 
                            style="cursor: pointer; transition: background-color 0.2s;">
                            <td><strong><?php echo htmlspecialchars(extractPersonName($contract['title'])); ?></strong></td>
                            <td><?php echo htmlspecialchars($contract['contract_number'] ?? '-'); ?></td>
                            <td>
                                <?php 
                                $typeName = $contract['contract_type_name'] ?? 'Unknown';
                                $abbr = $typeName !== 'Unknown' ? abbreviateContractType($typeName) : 'Unknown';
                                ?>
                                <span title="<?php echo htmlspecialchars($typeName); ?>" style="cursor: help; border-bottom: 1px dotted var(--text-light);">
                                    <?php echo htmlspecialchars($abbr); ?>
                                </span>
                            </td>
                            <td><?php echo $contract['start_date'] ? date(DATE_FORMAT, strtotime($contract['start_date'])) : '-'; ?></td>
                            <td><?php echo $contract['end_date'] ? date(DATE_FORMAT, strtotime($contract['end_date'])) : '<span style="color: var(--text-light);">Ongoing</span>'; ?></td>
                            <td>£<?php echo number_format($contract['total_amount'] ?? 0, 2); ?></td>
                            <td>
                                <?php 
                                $statusClass = $effectiveStatus === 'active' ? 'success' : ($effectiveStatus === 'inactive' ? 'danger' : 'secondary');
                                ?>
                                <span class="badge badge-<?php echo $statusClass; ?>">
                                    <?php echo ucfirst($effectiveStatus); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Contract Type Legend -->
            <div style="margin-top: 1rem; padding: 0.75rem; background: var(--bg-light); border-radius: 0.25rem; font-size: 0.9rem;">
                <strong>Contract Type Abbreviations:</strong>
                <?php
                $allTypes = array_unique(array_column($laContracts, 'contract_type_name'));
                $allTypes = array_filter($allTypes);
                $legend = [];
                foreach ($allTypes as $typeName) {
                    if ($typeName && $typeName !== 'Unknown') {
                        $legend[] = abbreviateContractType($typeName) . ' = ' . $typeName;
                    }
                }
                echo implode(' | ', $legend);
                ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Current Rates -->
    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid var(--border-color); margin-left: 1.5rem; margin-right: 1.5rem;">
        <h3>Current Rates</h3>
        <?php if (empty($rates)): ?>
            <p style="color: var(--text-light);">No current rates set for this local authority.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Contract Type</th>
                        <th>Rate</th>
                        <th>Effective From</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rates as $rate): ?>
                        <tr <?php if (RBAC::isAdmin()): ?>onclick="window.location.href='<?php echo htmlspecialchars(url('rates.php?contract_type_id=' . $rate['contract_type_id'])); ?>';" style="cursor: pointer; transition: background-color 0.2s;"<?php endif; ?>>
                            <td><?php echo htmlspecialchars($rate['contract_type_name'] ?? 'Unknown'); ?></td>
                            <td>£<?php echo number_format($rate['rate_amount'] ?? 0, 2); ?></td>
                            <td><?php echo $rate['effective_from'] ? date(DATE_FORMAT, strtotime($rate['effective_from'])) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Recent Updates -->
    <?php if (!empty($updates)): ?>
        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid var(--border-color); margin-left: 1.5rem; margin-right: 1.5rem; padding-left: 1rem; border-left: 3px solid var(--primary-color);">
            <h3>Recent Updates</h3>
            <ul style="list-style: none; padding: 0;">
                <?php foreach ($updates as $update): ?>
                    <li style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                        <strong><?php echo htmlspecialchars($update['title']); ?></strong>
                        <br>
                        <small style="color: var(--text-light);">
                            <?php echo $update['published_date'] ? date(DATE_FORMAT, strtotime($update['published_date'])) : 'N/A'; ?>
                        </small>
                        <?php if ($update['content']): ?>
                            <p style="margin-top: 0.5rem; margin-bottom: 0;"><?php echo nl2br(htmlspecialchars(substr($update['content'], 0, 200))); ?><?php echo strlen($update['content']) > 200 ? '...' : ''; ?></p>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <!-- Tender Applications -->
    <?php if (!empty($tenderApplications)): ?>
        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid var(--border-color); margin-left: 1.5rem; margin-right: 1.5rem;">
            <h3>Tender Applications</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tenderApplications as $tender): ?>
                        <tr onclick="window.location.href='<?php echo htmlspecialchars(url('tender-application.php?id=' . $tender['id'])); ?>';" style="cursor: pointer; transition: background-color 0.2s;">
                            <td><strong><?php echo htmlspecialchars($tender['title'] ?? 'N/A'); ?></strong></td>
                            <td>
                                <?php 
                                $status = ucwords(str_replace('_', ' ', strtolower($tender['status'] ?? 'N/A')));
                                $statusClass = 'secondary';
                                if (in_array(strtolower($tender['status'] ?? ''), ['awarded', 'submitted'])) {
                                    $statusClass = 'success';
                                } elseif (in_array(strtolower($tender['status'] ?? ''), ['lost', 'withdrawn'])) {
                                    $statusClass = 'danger';
                                } elseif (strtolower($tender['status'] ?? '') === 'under_review') {
                                    $statusClass = 'warning';
                                }
                                ?>
                                <span class="badge badge-<?php echo $statusClass; ?>"><?php echo htmlspecialchars($status); ?></span>
                            </td>
                            <td><?php echo $tender['created_at'] ? date(DATE_FORMAT, strtotime($tender['created_at'])) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <!-- Recent Payments -->
    <?php if (!empty($payments)): ?>
        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid var(--border-color); margin-left: 1.5rem; margin-right: 1.5rem;">
            <h3>Recent Payments</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Contract</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Method</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr <?php if ($payment['contract_id']): ?>onclick="window.location.href='<?php echo htmlspecialchars(url('contract-view.php?id=' . $payment['contract_id'])); ?>';" style="cursor: pointer; transition: background-color 0.2s;"<?php endif; ?>>
                            <td><strong><?php echo htmlspecialchars(extractPersonName($payment['contract_title'] ?? 'N/A')); ?></strong></td>
                            <td>£<?php echo number_format($payment['amount'] ?? 0, 2); ?></td>
                            <td><?php echo $payment['payment_date'] ? date(DATE_FORMAT, strtotime($payment['payment_date'])) : '-'; ?></td>
                            <td><?php echo htmlspecialchars($payment['payment_method_name'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
.contract-active {
    background-color: rgba(34, 197, 94, 0.05);
}

.contract-inactive {
    background-color: rgba(239, 68, 68, 0.05);
}

tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.05) !important;
}

.sortable-header {
    position: relative;
    user-select: none;
}

.sortable-header i {
    position: absolute;
    right: 0.5rem;
    font-size: 0.85rem;
}
</style>

<script>
function sortTable(field) {
    const url = new URL(window.location);
    const currentSort = url.searchParams.get('sort');
    const currentOrder = url.searchParams.get('order');
    
    let newOrder = 'asc';
    if (currentSort === field && currentOrder === 'asc') {
        newOrder = 'desc';
    }
    
    url.searchParams.set('sort', field);
    url.searchParams.set('order', newOrder);
    
    // Preserve scroll position
    sessionStorage.setItem('scrollPosition', window.scrollY);
    
    window.location.href = url.toString();
}

function filterContractsByType(typeName) {
    const url = new URL(window.location);
    url.searchParams.set('type', typeName);
    
    // Preserve scroll position
    sessionStorage.setItem('scrollPosition', window.scrollY);
    
    window.location.href = url.toString();
}

// Restore scroll position after page load
document.addEventListener('DOMContentLoaded', function() {
    const scrollPosition = sessionStorage.getItem('scrollPosition');
    if (scrollPosition) {
        setTimeout(function() {
            window.scrollTo(0, parseInt(scrollPosition));
            sessionStorage.removeItem('scrollPosition');
            
            // Scroll to contracts section if filtered
            const typeFilter = new URLSearchParams(window.location.search).get('type');
            if (typeFilter) {
                const contractsSection = document.getElementById('contracts-section');
                if (contractsSection) {
                    contractsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        }, 100);
    }
});
</script>

<?php include INCLUDES_PATH . '/footer.php'; ?>

