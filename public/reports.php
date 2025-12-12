<?php
/**
 * Reports Page - Enhanced with contract values, comparisons, and local authority breakdowns
 */
require_once dirname(__DIR__) . '/config/config.php';

$isLoggedIn = Auth::isLoggedIn();
$organisationId = $isLoggedIn ? Auth::getOrganisationId() : null;

// If not logged in, show information page only
if (!$isLoggedIn) {
    $pageTitle = 'Reports Information';
    include INCLUDES_PATH . '/header.php';
    ?>
    <div class="card">
        <div class="card-header">
            <h2>Reports & Analytics</h2>
            <p>Comprehensive insights into your contracts, payments, and trends</p>
        </div>
        
        <div class="alert alert-info" style="margin-bottom: 2rem;">
            <i class="fas fa-info-circle"></i> <strong>Login Required:</strong> To view your actual reports, please <a href="<?php echo htmlspecialchars(url('login.php')); ?>" style="color: var(--primary-color); text-decoration: underline;">log in</a> to your account.
        </div>
        
        <div style="padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem; border-left: 4px solid var(--primary-color);">
            <h3 style="margin-top: 0;"><i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i> Report Information Guide</h3>
            <p style="padding-left: 1.5rem;">This report provides comprehensive insights into your contracts and financial performance. Here's what you can expect to see:</p>
            
            <h4 style="margin-top: 1.5rem;"><i class="fas fa-chart-line" style="margin-right: 0.5rem;"></i> Summary Cards</h4>
            <ul style="padding-left: 2rem;">
                <li style="margin-bottom: 0.5rem;"><strong>Active Contracts:</strong> Number of contracts active during the selected period, with comparison to the previous period</li>
                <li style="margin-bottom: 0.5rem;"><strong>Total Contract Value:</strong> Sum of all contract values (total_amount), showing whether you're gaining or losing value</li>
                <li style="margin-bottom: 0.5rem;"><strong>Total Payments Received:</strong> Actual payments received during the period</li>
                <li style="margin-bottom: 0.5rem;"><strong>New Contracts:</strong> Contracts that started during this period, with trend comparison</li>
                <li style="margin-bottom: 0.5rem;"><strong>Contracts Ending:</strong> Contracts ending during this period, helping identify renewal needs</li>
            </ul>
            
            <h4 style="margin-top: 1.5rem;"><i class="fas fa-building" style="margin-right: 0.5rem;"></i> Contracts by Local Authority</h4>
            <ul style="padding-left: 2rem;">
                <li style="margin-bottom: 0.5rem;">Breakdown showing number of contracts and total value per local authority</li>
                <li style="margin-bottom: 0.5rem;"><strong>Status Indicators:</strong>
                    <ul style="padding-left: 1.5rem; margin-top: 0.5rem;">
                        <li style="margin-bottom: 0.25rem;"><i class="fas fa-check-circle" style="color: var(--success-color); margin-right: 0.5rem;"></i> OK - No issues detected</li>
                        <li style="margin-bottom: 0.25rem;"><i class="fas fa-exclamation-triangle" style="color: var(--warning-color); margin-right: 0.5rem;"></i> Issues - Flags potential problems like contracts ending soon or inactive contracts</li>
                    </ul>
                </li>
                <li style="margin-bottom: 0.5rem;">Sorted by total value (highest first)</li>
            </ul>
            
            <h4 style="margin-top: 1.5rem;"><i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i> Issue Detection</h4>
            <p style="padding-left: 1.5rem;">The system automatically identifies potential problems:</p>
            <ul style="padding-left: 2rem;">
                <li style="margin-bottom: 0.5rem;"><strong>Contracts Ending Soon:</strong> Contracts expiring within the next 3 months (requires attention for renewals)</li>
                <li style="margin-bottom: 0.5rem;"><strong>Inactive Contracts:</strong> Contracts marked as inactive (may indicate problems or completed contracts)</li>
            </ul>
            
            <h4 style="margin-top: 1.5rem;"><i class="fas fa-chart-area" style="margin-right: 0.5rem;"></i> Contract Activity</h4>
            <ul style="padding-left: 2rem;">
                <li style="margin-bottom: 0.5rem;"><strong>New Contracts Started:</strong> List of contracts that began during the selected period, showing:
                    <ul style="padding-left: 1.5rem; margin-top: 0.5rem;">
                        <li style="margin-bottom: 0.25rem;">Contract title and number</li>
                        <li style="margin-bottom: 0.25rem;">Local authority</li>
                        <li style="margin-bottom: 0.25rem;">Start date</li>
                        <li style="margin-bottom: 0.25rem;">Contract value</li>
                    </ul>
                </li>
                <li style="margin-bottom: 0.5rem;"><strong>Contracts Ending:</strong> List of contracts ending during the period, helping you plan for renewals</li>
            </ul>
            
            <h4 style="margin-top: 1.5rem;"><i class="fas fa-money-bill-wave" style="margin-right: 0.5rem;"></i> Payment Information</h4>
            <ul style="padding-left: 2rem;">
                <li style="margin-bottom: 0.5rem;"><strong>Payments by Method:</strong> Breakdown of payments by type (Tender, Self-Directed Support, etc.) with percentages</li>
                <li style="margin-bottom: 0.5rem;"><strong>Recent Payments:</strong> Detailed list of individual payments received during the period</li>
            </ul>
            
            <h4 style="margin-top: 1.5rem;"><i class="fas fa-chart-bar" style="margin-right: 0.5rem;"></i> Period Comparison</h4>
            <p style="padding-left: 1.5rem;">All metrics include comparisons with the previous equivalent period, showing:</p>
            <ul style="padding-left: 2rem;">
                <li style="margin-bottom: 0.5rem;">Whether contract count is increasing or decreasing</li>
                <li style="margin-bottom: 0.5rem;">Whether total contract value is growing or shrinking</li>
                <li style="margin-bottom: 0.5rem;">Trends in new contract acquisition</li>
                <li style="margin-bottom: 0.5rem;">Trends in contract endings</li>
            </ul>
            
            <p style="margin-top: 1.5rem; margin-bottom: 0; padding-left: 1.5rem;"><strong><i class="fas fa-lightbulb" style="margin-right: 0.5rem;"></i> Tip:</strong> Select a date range that includes your active contracts to see the most relevant data. The report automatically filters contracts that overlap with your selected period.</p>
        </div>
        
        <div style="margin-top: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem;">
            <h3>How to Access Reports</h3>
            <ol>
                <li><a href="<?php echo htmlspecialchars(url('login.php')); ?>" style="color: var(--primary-color);">Log in</a> to your account</li>
                <li>Navigate to <strong>Contracts → Reports</strong></li>
                <li>Select your desired date range</li>
                <li>Click <strong>Generate Report</strong></li>
            </ol>
        </div>
    </div>
    <?php include INCLUDES_PATH . '/footer.php'; ?>
    <?php exit; }

/**
 * Financial Year Helper Functions
 * UK Financial Year: April 1 to March 31
 */

/**
 * Get the financial year for a given date
 * @param string|DateTime|null $date Date to check (defaults to today)
 * @return array ['start' => DateTime, 'end' => DateTime, 'year' => string]
 */
function getFinancialYear($date = null) {
    if ($date === null) {
        $date = new DateTime();
    } elseif (is_string($date)) {
        $date = new DateTime($date);
    }
    
    $year = (int)$date->format('Y');
    $month = (int)$date->format('n');
    
    // If date is before April, it's part of the previous financial year
    if ($month < 4) {
        $fyStart = new DateTime($year - 1 . '-04-01');
        $fyEnd = new DateTime($year . '-03-31');
        $fyYear = ($year - 1) . '/' . substr($year, 2);
    } else {
        $fyStart = new DateTime($year . '-04-01');
        $fyEnd = new DateTime(($year + 1) . '-03-31');
        $fyYear = $year . '/' . substr($year + 1, 2);
    }
    
    return [
        'start' => $fyStart,
        'end' => $fyEnd,
        'year' => $fyYear
    ];
}

/**
 * Get financial year dates for N years ago
 * @param int $yearsAgo Number of years ago (0 = current, 1 = last, etc.)
 * @return array ['start' => DateTime, 'end' => DateTime, 'year' => string]
 */
function getFinancialYearAgo($yearsAgo = 1) {
    $currentFY = getFinancialYear();
    $start = clone $currentFY['start'];
    $end = clone $currentFY['end'];
    
    if ($yearsAgo > 0) {
        $start->modify('-' . $yearsAgo . ' years');
        $end->modify('-' . $yearsAgo . ' years');
    }
    
    $startYear = (int)$start->format('Y');
    $endYear = (int)$end->format('Y');
    
    return [
        'start' => $start,
        'end' => $end,
        'year' => $startYear . '/' . substr($endYear, 2)
    ];
}

/**
 * Get date range for multiple financial years (from N years ago to current FY end)
 * @param int $numYears Number of financial years to include
 * @return array ['start' => DateTime, 'end' => DateTime, 'year' => string]
 */
function getFinancialYearsRange($numYears = 1) {
    $currentFY = getFinancialYear();
    $start = clone $currentFY['start'];
    $end = clone $currentFY['end'];
    
    if ($numYears > 1) {
        // Go back (numYears - 1) years from the start of current FY
        $start->modify('-' . ($numYears - 1) . ' years');
    }
    
    $startYear = (int)$start->format('Y');
    $endYear = (int)$end->format('Y');
    
    return [
        'start' => $start,
        'end' => $end,
        'year' => $startYear . '/' . substr($endYear, 2) . ' (' . $numYears . ' years)'
    ];
}

// Handle financial year quick-select
$fyRange = null;
if (!empty($_GET['fy_range'])) {
    switch ($_GET['fy_range']) {
        case 'current':
            $fyRange = getFinancialYear();
            break;
        case 'last':
            $fyRange = getFinancialYearAgo(1);
            break;
        case 'last5':
            $fyRange = getFinancialYearsRange(5);
            break;
        case 'custom':
            // Use custom dates from GET params
            break;
    }
}

// Get date range
if ($fyRange) {
    $startDate = $fyRange['start']->format('Y-m-d');
    $endDate = $fyRange['end']->format('Y-m-d');
} else {
    // Default to current financial year if no dates specified
    if (empty($_GET['start_date']) && empty($_GET['end_date'])) {
        $currentFY = getFinancialYear();
        $startDate = $currentFY['start']->format('Y-m-d');
        $endDate = $currentFY['end']->format('Y-m-d');
    } else {
        $startDate = !empty($_GET['start_date']) ? $_GET['start_date'] : null;
        $endDate = !empty($_GET['end_date']) ? $_GET['end_date'] : null;
        
        // If only one date is provided, use current FY for the other
        if ($startDate && !$endDate) {
            $currentFY = getFinancialYear($startDate);
            $endDate = $currentFY['end']->format('Y-m-d');
        } elseif (!$startDate && $endDate) {
            $currentFY = getFinancialYear($endDate);
            $startDate = $currentFY['start']->format('Y-m-d');
        }
    }
}

// Ensure dates are in correct format for HTML date inputs (YYYY-MM-DD)
if (!empty($startDate) && strtotime($startDate) !== false) {
    $startDate = date('Y-m-d', strtotime($startDate));
} else {
    $currentFY = getFinancialYear();
    $startDate = $currentFY['start']->format('Y-m-d');
}
if (!empty($endDate) && strtotime($endDate) !== false) {
    $endDate = date('Y-m-d', strtotime($endDate));
} else {
    $currentFY = getFinancialYear();
    $endDate = $currentFY['end']->format('Y-m-d');
}

$db = getDbConnection();

// Get user's accessible team IDs (for filtering contracts)
$accessibleTeamIds = RBAC::getAccessibleTeamIds();

// Calculate previous period for comparison
$rangeStart = new DateTime($startDate);
$rangeEnd = new DateTime($endDate);

// Check if the selected range is a financial year (April 1 to March 31)
$startFY = getFinancialYear($startDate);
$endFY = getFinancialYear($endDate);
$isFinancialYear = (
    $startDate === $startFY['start']->format('Y-m-d') &&
    $endDate === $endFY['end']->format('Y-m-d') &&
    $startFY['year'] === $endFY['year']
);

if ($isFinancialYear) {
    // Previous period is the previous financial year
    $prevFY = getFinancialYearAgo(1);
    $prevStart = clone $prevFY['start'];
    $prevEnd = clone $prevFY['end'];
} else {
    // Calculate previous period as same duration before the start date
    $daysDiff = $rangeStart->diff($rangeEnd)->days;
    $prevStart = clone $rangeStart;
    $prevStart->modify('-' . ($daysDiff + 1) . ' days');
    $prevEnd = clone $rangeStart;
    $prevEnd->modify('-1 day');
}

// Get contracts in date range (filtered by team access)
$contracts = Contract::findByOrganisation($organisationId, null, $accessibleTeamIds);

// Deduplicate contracts (same logic as contracts.php and dashboard)
$seenIds = [];
$seenContractKeys = [];
$uniqueContracts = [];

foreach ($contracts as $contract) {
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

$filteredContracts = [];
$totalContractValue = 0;
$contractsByLA = [];
$newContracts = [];
$endingContracts = [];

foreach ($uniqueContracts as $contract) {
    // Update contract with effective status for display
    $contract['effective_status'] = Contract::getEffectiveStatus($contract);
    
    $contractStart = strtotime($contract['start_date']);
    $contractEnd = $contract['end_date'] ? strtotime($contract['end_date']) : null;
    $rangeStart = strtotime($startDate);
    $rangeEnd = strtotime($endDate);
    
    // Check if contract overlaps with date range
    if ($contractStart <= $rangeEnd && ($contractEnd === null || $contractEnd >= $rangeStart)) {
        $filteredContracts[] = $contract;
        
        // Calculate contract value (use total_amount if available, otherwise estimate)
        $contractValue = $contract['total_amount'] ?? 0;
        $totalContractValue += $contractValue;
        
        // Group by local authority (use ID as primary key to avoid duplicates from name variations)
        // Only include each contract once per local authority
        $laName = trim($contract['local_authority_name'] ?? 'Unknown');
        $laId = $contract['local_authority_id'] ?? null;
        
        // Use ID as key if available, otherwise use normalized name
        $laKey = $laId ? 'id_' . $laId : 'name_' . strtolower($laName);
        
        // Check if this contract is already in this local authority's contracts array
        $contractAlreadyAdded = false;
        if (isset($contractsByLA[$laKey])) {
            foreach ($contractsByLA[$laKey]['contracts'] as $existingContract) {
                if (($existingContract['id'] ?? null) === ($contract['id'] ?? null)) {
                    $contractAlreadyAdded = true;
                    break;
                }
            }
        }
        
        if (!$contractAlreadyAdded) {
            if (!isset($contractsByLA[$laKey])) {
                $contractsByLA[$laKey] = [
                    'id' => $laId,
                    'name' => $laName,
                    'count' => 0,
                    'value' => 0,
                    'contracts' => []
                ];
            }
            // Ensure name is set if not already (in case we have ID but no name)
            if (empty($contractsByLA[$laKey]['name']) && $laName !== 'Unknown') {
                $contractsByLA[$laKey]['name'] = $laName;
            }
            // Ensure ID is set if not already
            if (!$contractsByLA[$laKey]['id'] && $laId) {
                $contractsByLA[$laKey]['id'] = $laId;
            }
            $contractsByLA[$laKey]['count']++;
            $contractsByLA[$laKey]['value'] += $contractValue;
            $contractsByLA[$laKey]['contracts'][] = $contract;
        }
        
        // Track new contracts (started in this period)
        // If a contract both starts and ends in the period, only show it as "new"
        $isNewContract = ($contractStart >= $rangeStart && $contractStart <= $rangeEnd);
        $isEndingContract = ($contractEnd && $contractEnd >= $rangeStart && $contractEnd <= $rangeEnd);
        
        if ($isNewContract) {
            $newContracts[] = $contract;
        } elseif ($isEndingContract) {
            // Only add to ending contracts if it didn't start in this period
            $endingContracts[] = $contract;
        }
    }
}

// Get previous period contracts for comparison
$prevContracts = Contract::findByOrganisation($organisationId, null, $accessibleTeamIds);

// Deduplicate previous period contracts (same logic)
$prevSeenIds = [];
$prevSeenContractKeys = [];
$prevUniqueContracts = [];

foreach ($prevContracts as $contract) {
    $id = $contract['id'] ?? null;
    $contractNumber = $contract['contract_number'] ?? '';
    $title = $contract['title'] ?? '';
    $startDate = $contract['start_date'] ?? '';
    $endDate = $contract['end_date'] ?? '';
    
    // First priority: skip if we've seen this exact contract ID
    if ($id && in_array($id, $prevSeenIds)) {
        continue;
    }
    
    // Second priority: if contract number exists and we've seen this exact combination before, skip
    if ($contractNumber) {
        $contractKey = md5($contractNumber . '|' . $title . '|' . $startDate . '|' . $endDate);
        if (in_array($contractKey, $prevSeenContractKeys)) {
            continue;
        }
        $prevSeenContractKeys[] = $contractKey;
    }
    
    // Add to unique contracts
    if ($id) {
        $prevSeenIds[] = $id;
    }
    $prevUniqueContracts[] = $contract;
}

$prevFilteredContracts = [];
$prevTotalValue = 0;
$prevNewContracts = 0;
$prevEndingContracts = 0;

foreach ($prevUniqueContracts as $contract) {
    // Update contract with effective status for display
    $contract['effective_status'] = Contract::getEffectiveStatus($contract);
    
    $contractStart = strtotime($contract['start_date']);
    $contractEnd = $contract['end_date'] ? strtotime($contract['end_date']) : null;
    $prevRangeStart = strtotime($prevStart->format('Y-m-d'));
    $prevRangeEnd = strtotime($prevEnd->format('Y-m-d'));
    
    if ($contractStart <= $prevRangeEnd && ($contractEnd === null || $contractEnd >= $prevRangeStart)) {
        $prevFilteredContracts[] = $contract;
        $prevTotalValue += $contract['total_amount'] ?? 0;
        
        if ($contractStart >= $prevRangeStart && $contractStart <= $prevRangeEnd) {
            $prevNewContracts++;
        }
        if ($contractEnd && $contractEnd >= $prevRangeStart && $contractEnd <= $prevRangeEnd) {
            $prevEndingContracts++;
        }
    }
}

// Calculate trends (using active contracts only)
$activeInPeriod = count(array_filter($filteredContracts, function($c) {
    $effectiveStatus = Contract::getEffectiveStatus($c);
    return $effectiveStatus === 'active';
}));
$prevActiveInPeriod = count(array_filter($prevFilteredContracts, function($c) {
    $effectiveStatus = Contract::getEffectiveStatus($c);
    return $effectiveStatus === 'active';
}));
$contractCountChange = $activeInPeriod - $prevActiveInPeriod;
$contractValueChange = $totalContractValue - $prevTotalValue;
$contractValueChangePercent = $prevTotalValue > 0 ? ($contractValueChange / $prevTotalValue) * 100 : 0;
$newContractsChange = count($newContracts) - $prevNewContracts;
$endingContractsChange = count($endingContracts) - $prevEndingContracts;

// Get payments in date range - optimized query
$payments = [];
$totalPayments = 0;
if (!empty($filteredContracts)) {
    $contractIds = array_column($filteredContracts, 'id');
    $placeholders = implode(',', array_fill(0, count($contractIds), '?'));
    $stmt = $db->prepare("
        SELECT cp.*, pm.name as payment_method_name
        FROM contract_payments cp
        LEFT JOIN payment_methods pm ON cp.payment_method_id = pm.id
        WHERE cp.contract_id IN ($placeholders)
        AND cp.payment_date BETWEEN ? AND ?
        ORDER BY cp.payment_date DESC
    ");
    $params = array_merge($contractIds, [$startDate, $endDate]);
    $stmt->execute($params);
    $payments = $stmt->fetchAll();
    $totalPayments = array_sum(array_column($payments, 'amount'));
}

// Payment by method
$paymentByMethod = [];
foreach ($payments as $payment) {
    $methodName = $payment['payment_method_name'] ?? 'Unknown';
    if (!isset($paymentByMethod[$methodName])) {
        $paymentByMethod[$methodName] = 0;
    }
    $paymentByMethod[$methodName] += $payment['amount'];
}

// Calculate income by local authority (from payments) - optimized single query
$incomeByLA = [];
if (!empty($payments)) {
    $paymentIds = array_column($payments, 'id');
    $placeholders = implode(',', array_fill(0, count($paymentIds), '?'));
    $stmt = $db->prepare("
        SELECT cp.id, cp.amount, la.name as local_authority_name
        FROM contract_payments cp
        JOIN contracts c ON cp.contract_id = c.id
        LEFT JOIN local_authorities la ON c.local_authority_id = la.id
        WHERE cp.id IN ($placeholders)
    ");
    $stmt->execute($paymentIds);
    $paymentDetails = $stmt->fetchAll();
    
    foreach ($paymentDetails as $detail) {
        if ($detail['local_authority_name']) {
            $laName = $detail['local_authority_name'];
            if (!isset($incomeByLA[$laName])) {
                $incomeByLA[$laName] = 0;
            }
            $incomeByLA[$laName] += $detail['amount'];
        }
    }
}

// Calculate income by contract type (from payments) - optimized single query
$incomeByContractType = [];
if (!empty($payments)) {
    $paymentIds = array_column($payments, 'id');
    $placeholders = implode(',', array_fill(0, count($paymentIds), '?'));
    $stmt = $db->prepare("
        SELECT cp.id, cp.amount, ct.name as contract_type_name
        FROM contract_payments cp
        JOIN contracts c ON cp.contract_id = c.id
        LEFT JOIN contract_types ct ON c.contract_type_id = ct.id
        WHERE cp.id IN ($placeholders)
    ");
    $stmt->execute($paymentIds);
    $paymentDetails = $stmt->fetchAll();
    
    foreach ($paymentDetails as $detail) {
        if ($detail['contract_type_name']) {
            $typeName = $detail['contract_type_name'];
            if (!isset($incomeByContractType[$typeName])) {
                $incomeByContractType[$typeName] = 0;
            }
            $incomeByContractType[$typeName] += $detail['amount'];
        }
    }
}

// Identify local authorities with potential issues
$laIssues = [];
foreach ($contractsByLA as $laKey => $data) {
    $laName = $data['name'];
    $issues = [];
    
    // Check for contracts ending soon (within 3 months) - only active contracts (using effective status)
    $endingSoon = 0;
    foreach ($data['contracts'] as $contract) {
        $effectiveStatus = Contract::getEffectiveStatus($contract);
        if ($effectiveStatus === 'active' && $contract['end_date']) {
            $contractEndDate = strtotime($contract['end_date']);
            $threeMonths = strtotime('+3 months');
            if ($contractEndDate <= $threeMonths && $contractEndDate > time()) {
                $endingSoon++;
            }
        }
    }
    if ($endingSoon > 0) {
        $issues[] = "$endingSoon contract(s) ending within 3 months";
    }
    
    // Check for inactive contracts that should still be active (future end date or no end date)
    // Don't flag contracts that have already ended - those are normal
    $inactive = 0;
    foreach ($data['contracts'] as $contract) {
        $effectiveStatus = Contract::getEffectiveStatus($contract);
        if ($effectiveStatus === 'inactive') {
            $endDate = $contract['end_date'] ? strtotime($contract['end_date']) : null;
            $today = time();
            
            // Only flag if contract has no end date or end date is in the future
            if (!$endDate || $endDate > $today) {
                $inactive++;
            }
        }
    }
    if ($inactive > 0) {
        $issues[] = "$inactive inactive contract(s) that should be active";
    }
    
    if (!empty($issues)) {
        $laIssues[$laName] = [
            'id' => $data['id'] ?? null,
            'issues' => $issues
        ];
    }
}

$pageTitle = 'Reports';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-between" style="align-items: start;">
            <div>
                <h2>Reports</h2>
                <p>View reports on contracts, payments, and trends</p>
            </div>
            <button onclick="toggleHelp()" class="btn btn-secondary" style="margin-top: 0.5rem;"><i class="fas fa-info-circle"></i> What's Included?</button>
        </div>
    </div>
    
    <!-- Help Section -->
    <div id="helpSection" style="display: none; margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem; border-left: 4px solid var(--primary-color);">
        <h3 style="margin-top: 0;"><i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i> Report Information Guide</h3>
        <p style="padding-left: 1.5rem;">This report provides comprehensive insights into your contracts and financial performance. Here's what you can expect to see:</p>
        
        <h4 style="margin-top: 1.5rem;"><i class="fas fa-chart-line" style="margin-right: 0.5rem;"></i> Summary Cards</h4>
        <ul style="padding-left: 2rem;">
            <li style="margin-bottom: 0.5rem;"><strong>Active Contracts:</strong> Number of contracts active during the selected period, with comparison to the previous period</li>
            <li style="margin-bottom: 0.5rem;"><strong>Total Contract Value:</strong> Sum of all contract values (total_amount), showing whether you're gaining or losing value</li>
            <li style="margin-bottom: 0.5rem;"><strong>Total Payments Received:</strong> Actual payments received during the period</li>
            <li style="margin-bottom: 0.5rem;"><strong>New Contracts:</strong> Contracts that started during this period, with trend comparison</li>
            <li style="margin-bottom: 0.5rem;"><strong>Contracts Ending:</strong> Contracts ending during this period, helping identify renewal needs</li>
        </ul>
        
        <h4 style="margin-top: 1.5rem;"><i class="fas fa-building" style="margin-right: 0.5rem;"></i> Contracts by Local Authority</h4>
        <ul style="padding-left: 2rem;">
            <li style="margin-bottom: 0.5rem;">Breakdown showing number of contracts and total value per local authority</li>
            <li style="margin-bottom: 0.5rem;"><strong>Status Indicators:</strong>
                <ul style="padding-left: 1.5rem; margin-top: 0.5rem;">
                    <li style="margin-bottom: 0.25rem;"><i class="fas fa-check-circle" style="color: var(--success-color); margin-right: 0.5rem;"></i> OK - No issues detected</li>
                    <li style="margin-bottom: 0.25rem;"><i class="fas fa-exclamation-triangle" style="color: var(--warning-color); margin-right: 0.5rem;"></i> Issues - Flags potential problems like contracts ending soon or inactive contracts</li>
                </ul>
            </li>
            <li style="margin-bottom: 0.5rem;">Sorted by total value (highest first)</li>
        </ul>
        
        <h4 style="margin-top: 1.5rem;"><i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i> Issue Detection</h4>
        <p style="padding-left: 1.5rem;">The system automatically identifies potential problems:</p>
        <ul style="padding-left: 2rem;">
            <li style="margin-bottom: 0.5rem;"><strong>Contracts Ending Soon:</strong> Contracts expiring within the next 3 months (requires attention for renewals)</li>
            <li style="margin-bottom: 0.5rem;"><strong>Inactive Contracts:</strong> Contracts marked as inactive (may indicate problems or completed contracts)</li>
        </ul>
        
        <h4 style="margin-top: 1.5rem;"><i class="fas fa-chart-area" style="margin-right: 0.5rem;"></i> Contract Activity</h4>
        <ul style="padding-left: 2rem;">
            <li style="margin-bottom: 0.5rem;"><strong>New Contracts Started:</strong> List of contracts that began during the selected period, showing:
                <ul style="padding-left: 1.5rem; margin-top: 0.5rem;">
                    <li style="margin-bottom: 0.25rem;">Contract title and number</li>
                    <li style="margin-bottom: 0.25rem;">Local authority</li>
                    <li style="margin-bottom: 0.25rem;">Start date</li>
                    <li style="margin-bottom: 0.25rem;">Contract value</li>
                </ul>
            </li>
            <li style="margin-bottom: 0.5rem;"><strong>Contracts Ending:</strong> List of contracts ending during the period, helping you plan for renewals</li>
        </ul>
        
        <h4 style="margin-top: 1.5rem;"><i class="fas fa-money-bill-wave" style="margin-right: 0.5rem;"></i> Payment Information</h4>
        <ul style="padding-left: 2rem;">
            <li style="margin-bottom: 0.5rem;"><strong>Payments by Method:</strong> Breakdown of payments by type (Tender, Self-Directed Support, etc.) with percentages</li>
            <li style="margin-bottom: 0.5rem;"><strong>Recent Payments:</strong> Detailed list of individual payments received during the period</li>
        </ul>
        
        <h4 style="margin-top: 1.5rem;"><i class="fas fa-chart-bar" style="margin-right: 0.5rem;"></i> Period Comparison</h4>
        <p style="padding-left: 1.5rem;">All metrics include comparisons with the previous equivalent period, showing:</p>
        <ul style="padding-left: 2rem;">
            <li style="margin-bottom: 0.5rem;">Whether contract count is increasing or decreasing</li>
            <li style="margin-bottom: 0.5rem;">Whether total contract value is growing or shrinking</li>
            <li style="margin-bottom: 0.5rem;">Trends in new contract acquisition</li>
            <li style="margin-bottom: 0.5rem;">Trends in contract endings</li>
        </ul>
        
        <p style="margin-top: 1rem; margin-bottom: 0; padding-left: 1.5rem;"><strong><i class="fas fa-lightbulb" style="margin-right: 0.5rem;"></i> Tip:</strong> Select a date range that includes your active contracts to see the most relevant data. The report automatically filters contracts that overlap with your selected period.</p>
    </div>
    
    <!-- Date Range Filter -->
    <form method="GET" action="" style="margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem;">
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.75rem; font-weight: 600; color: var(--text-color);">Financial Year Quick Select</label>
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                <?php
                $currentFY = getFinancialYear();
                $lastFY = getFinancialYearAgo(1);
                $last5FY = getFinancialYearsRange(5);
                $selectedRange = $_GET['fy_range'] ?? 'current';
                ?>
                <a href="?fy_range=current" 
                   class="btn <?php echo $selectedRange === 'current' ? 'btn-primary' : 'btn-secondary'; ?>"
                   style="text-decoration: none;">
                    Current FY (<?php echo htmlspecialchars($currentFY['year']); ?>)
                </a>
                <a href="?fy_range=last" 
                   class="btn <?php echo $selectedRange === 'last' ? 'btn-primary' : 'btn-secondary'; ?>"
                   style="text-decoration: none;">
                    Last FY (<?php echo htmlspecialchars($lastFY['year']); ?>)
                </a>
                <a href="?fy_range=last5" 
                   class="btn <?php echo $selectedRange === 'last5' ? 'btn-primary' : 'btn-secondary'; ?>"
                   style="text-decoration: none;">
                    Last 5 FYs
                </a>
                <a href="?fy_range=custom" 
                   class="btn <?php echo $selectedRange === 'custom' || (!isset($_GET['fy_range']) && !empty($_GET['start_date'])) ? 'btn-primary' : 'btn-secondary'; ?>"
                   style="text-decoration: none;">
                    Custom Range
                </a>
            </div>
            <?php
            // Display selected financial year info
            $selectedFY = getFinancialYear($startDate);
            $displayStart = date('d/m/Y', strtotime($startDate));
            $displayEnd = date('d/m/Y', strtotime($endDate));
            $isFYRange = ($startDate === $selectedFY['start']->format('Y-m-d') && $endDate === $selectedFY['end']->format('Y-m-d'));
            ?>
            <div style="margin-top: 1rem; padding: 0.75rem; background: white; border-radius: 0.25rem; border: 1px solid var(--border-color);">
                <strong>Selected Period:</strong> 
                <?php if ($isFYRange): ?>
                    Financial Year <?php echo htmlspecialchars($selectedFY['year']); ?> 
                    (<?php echo htmlspecialchars($displayStart); ?> - <?php echo htmlspecialchars($displayEnd); ?>)
                <?php else: ?>
                    <?php echo htmlspecialchars($displayStart); ?> - <?php echo htmlspecialchars($displayEnd); ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>">
            </div>
            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>">
            </div>
        </div>
        <div class="form-group" style="margin-top: 1rem;">
            <button type="submit" class="btn btn-primary">Generate Report</button>
            <?php if (isset($_GET['fy_range']) && $_GET['fy_range'] !== 'custom'): ?>
                <input type="hidden" name="fy_range" value="<?php echo htmlspecialchars($_GET['fy_range']); ?>">
            <?php endif; ?>
        </div>
    </form>
    
    <?php if (empty($filteredContracts) && empty($payments)): ?>
        <div class="alert alert-info">
            <p><strong>No data available for the selected date range.</strong></p>
            <p>Try selecting a different date range, or ensure contracts exist for this period.</p>
        </div>
    <?php else: ?>
    
    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white;">
            <h3 style="color: white; margin: 0;"><?php echo $activeInPeriod; ?></h3>
            <p style="color: rgba(255,255,255,0.9); margin: 0.5rem 0 0 0;">
                Active Contracts
                <?php if ($contractCountChange != 0): ?>
                    <br><small style="font-size: 0.85rem;">
                        <?php 
                        $color = $contractCountChange > 0 ? 'rgba(255,255,255,0.8)' : 'rgba(255,200,200,0.9)';
                        $sign = $contractCountChange > 0 ? '+' : '';
                        echo '<span style="color: ' . $color . ';">' . $sign . $contractCountChange . ' vs previous period</span>';
                        ?>
                    </small>
                <?php endif; ?>
            </p>
        </div>
        <div class="card" style="background: linear-gradient(135deg, var(--success-color), #059669); color: white;">
            <h3 style="color: white; margin: 0;">£<?php echo number_format($totalContractValue, 2); ?></h3>
            <p style="color: rgba(255,255,255,0.9); margin: 0.5rem 0 0 0;">
                Total Contract Value
                <?php if ($contractValueChange != 0): ?>
                    <br><small style="font-size: 0.85rem;">
                        <?php 
                        $color = $contractValueChange > 0 ? 'rgba(255,255,255,0.8)' : 'rgba(255,200,200,0.9)';
                        $sign = $contractValueChange > 0 ? '+' : '';
                        echo '<span style="color: ' . $color . ';">' . $sign . '£' . number_format(abs($contractValueChange), 2) . ' (' . $sign . number_format(abs($contractValueChangePercent), 1) . '%)</span>';
                        ?>
                    </small>
                <?php endif; ?>
            </p>
        </div>
        <div class="card" style="background: linear-gradient(135deg, #3b82f6, #2563eb); color: white;">
            <h3 style="color: white; margin: 0;">£<?php echo number_format($totalPayments, 2); ?></h3>
            <p style="color: rgba(255,255,255,0.9); margin: 0.5rem 0 0 0;">Total Payments Received</p>
        </div>
        <div class="card" style="background: linear-gradient(135deg, var(--warning-color), #d97706); color: white;">
            <h3 style="color: white; margin: 0;"><?php echo count($newContracts); ?></h3>
            <p style="color: rgba(255,255,255,0.9); margin: 0.5rem 0 0 0;">
                New Contracts
                <?php if ($newContractsChange != 0): ?>
                    <br><small style="font-size: 0.85rem;">
                        <?php 
                        $color = $newContractsChange > 0 ? 'rgba(255,255,255,0.8)' : 'rgba(255,200,200,0.9)';
                        $sign = $newContractsChange > 0 ? '+' : '';
                        echo '<span style="color: ' . $color . ';">' . $sign . $newContractsChange . ' vs previous</span>';
                        ?>
                    </small>
                <?php endif; ?>
            </p>
        </div>
        <div class="card" style="background: linear-gradient(135deg, #ef4444, #dc2626); color: white;">
            <h3 style="color: white; margin: 0;"><?php echo count($endingContracts); ?></h3>
            <p style="color: rgba(255,255,255,0.9); margin: 0.5rem 0 0 0;">
                Contracts Ending
                <?php if ($endingContractsChange != 0): ?>
                    <br><small style="font-size: 0.85rem;">
                        <?php 
                        $color = $endingContractsChange < 0 ? 'rgba(255,255,255,0.8)' : 'rgba(255,200,200,0.9)';
                        $sign = $endingContractsChange > 0 ? '+' : '';
                        echo '<span style="color: ' . $color . ';">' . $sign . $endingContractsChange . ' vs previous</span>';
                        ?>
                    </small>
                <?php endif; ?>
            </p>
        </div>
    </div>
    
    <!-- Export Button -->
    <?php if (!empty($filteredContracts) || !empty($payments)): ?>
        <div style="margin-bottom: 2rem; text-align: right;">
            <button onclick="exportToCSV()" class="btn btn-primary">
                <i class="fas fa-download" style="margin-right: 0.5rem;"></i> Export Data to CSV
            </button>
        </div>
    <?php endif; ?>
    
    <!-- Contract Value Breakdown Charts -->
    <?php if ($totalContractValue > 0): ?>
        <div class="report-section" style="margin-bottom: 2rem;">
            <div class="report-section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; cursor: pointer;" onclick="toggleSection('contractValueCharts')">
                <h3 style="margin: 0;"><i class="fas fa-chart-pie" style="margin-right: 0.5rem;"></i> Contract Value Breakdown Charts</h3>
                <button class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.9rem;" onclick="event.stopPropagation(); toggleSection('contractValueCharts')">
                    <i class="fas fa-chevron-down" id="icon-contractValueCharts"></i>
                </button>
            </div>
            <div id="section-contractValueCharts" class="report-section-content" style="display: block;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
            <?php if (!empty($contractsByLA)): ?>
                <div class="card">
                    <h3 style="margin-top: 0;"><i class="fas fa-chart-pie" style="margin-right: 0.5rem;"></i> Contract Value by Local Authority</h3>
                    <canvas id="contractValueByLAChart" style="max-height: 400px;"></canvas>
                    <div style="margin-top: 1rem; font-size: 0.9rem; color: var(--text-light);">
                        <strong>Total Contract Value:</strong> £<?php echo number_format($totalContractValue, 2); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php 
            // Calculate contract value by contract type
            $contractValueByType = [];
            foreach ($filteredContracts as $contract) {
                $typeName = $contract['contract_type_name'] ?? 'Unknown';
                $value = $contract['total_amount'] ?? 0;
                if (!isset($contractValueByType[$typeName])) {
                    $contractValueByType[$typeName] = 0;
                }
                $contractValueByType[$typeName] += $value;
            }
            
            // Calculate contract value by contract type AND local authority
            $contractValueByTypeAndLA = [];
            foreach ($filteredContracts as $contract) {
                $typeName = $contract['contract_type_name'] ?? 'Unknown';
                $laName = $contract['local_authority_name'] ?? 'Unknown';
                $value = $contract['total_amount'] ?? 0;
                
                if (!isset($contractValueByTypeAndLA[$laName])) {
                    $contractValueByTypeAndLA[$laName] = [];
                }
                if (!isset($contractValueByTypeAndLA[$laName][$typeName])) {
                    $contractValueByTypeAndLA[$laName][$typeName] = 0;
                }
                $contractValueByTypeAndLA[$laName][$typeName] += $value;
            }
            ?>
            <?php if (!empty($contractValueByType)): ?>
                <div class="card">
                    <h3 style="margin-top: 0;"><i class="fas fa-chart-pie" style="margin-right: 0.5rem;"></i> Contract Value by Contract Type</h3>
                    <canvas id="contractValueByTypeChart" style="max-height: 400px;"></canvas>
                    <div style="margin-top: 1rem; font-size: 0.9rem; color: var(--text-light);">
                        <strong>Total Contract Value:</strong> £<?php echo number_format($totalContractValue, 2); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Contract Value by Type and Local Authority -->
        <?php if (!empty($contractValueByTypeAndLA) && count($contractValueByTypeAndLA) > 0): ?>
            <div class="card" style="margin-bottom: 2rem;">
                <h3 style="margin-top: 0;"><i class="fas fa-chart-bar" style="margin-right: 0.5rem;"></i> Contract Value by Contract Type and Local Authority</h3>
                <p style="font-size: 0.85rem; color: var(--text-light); margin-top: 0;">Breakdown showing contract types within each local authority</p>
                <canvas id="contractValueByTypeAndLAChart" style="max-height: 500px;"></canvas>
                <div style="margin-top: 1rem; font-size: 0.9rem; color: var(--text-light);">
                    <strong>Total Contract Value:</strong> £<?php echo number_format($totalContractValue, 2); ?>
                </div>
            </div>
        <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Payment Income Breakdown Charts -->
    <?php if ($totalPayments > 0): ?>
        <div class="report-section" style="margin-bottom: 2rem;">
            <div class="report-section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; cursor: pointer;" onclick="toggleSection('paymentIncomeCharts')">
                <h3 style="margin: 0;"><i class="fas fa-money-bill-wave" style="margin-right: 0.5rem;"></i> Payment Income Breakdown Charts</h3>
                <button class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.9rem;" onclick="event.stopPropagation(); toggleSection('paymentIncomeCharts')">
                    <i class="fas fa-chevron-down" id="icon-paymentIncomeCharts"></i>
                </button>
            </div>
            <div id="section-paymentIncomeCharts" class="report-section-content" style="display: block;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
            <?php if (!empty($incomeByLA)): ?>
                <div class="card">
                    <h3 style="margin-top: 0;"><i class="fas fa-chart-pie" style="margin-right: 0.5rem;"></i> Payment Income by Local Authority</h3>
                    <p style="font-size: 0.85rem; color: var(--text-light); margin-top: 0;">Actual payments received during this period</p>
                    <canvas id="incomeByLAChart" style="max-height: 400px;"></canvas>
                    <div style="margin-top: 1rem; font-size: 0.9rem; color: var(--text-light);">
                        <strong>Total Payments:</strong> £<?php echo number_format($totalPayments, 2); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($incomeByContractType)): ?>
                <div class="card">
                    <h3 style="margin-top: 0;"><i class="fas fa-chart-pie" style="margin-right: 0.5rem;"></i> Income by Contract Type</h3>
                    <canvas id="incomeByContractTypeChart" style="max-height: 400px;"></canvas>
                    <div style="margin-top: 1rem; font-size: 0.9rem; color: var(--text-light);">
                        <strong>Total:</strong> £<?php echo number_format($totalPayments, 2); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($paymentByMethod)): ?>
                <div class="card">
                    <h3 style="margin-top: 0;"><i class="fas fa-chart-pie" style="margin-right: 0.5rem;"></i> Income by Payment Method</h3>
                    <canvas id="incomeByMethodChart" style="max-height: 400px;"></canvas>
                    <div style="margin-top: 1rem; font-size: 0.9rem; color: var(--text-light);">
                        <strong>Total:</strong> £<?php echo number_format($totalPayments, 2); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Local Authority Breakdown -->
    <?php if (!empty($contractsByLA)): ?>
        <div class="report-section" style="margin-bottom: 2rem;">
            <div class="report-section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; cursor: pointer;" onclick="toggleSection('localAuthorityBreakdown')">
                <h3 style="margin: 0;"><i class="fas fa-building" style="margin-right: 0.5rem;"></i> Contracts by Local Authority</h3>
                <button class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.9rem;" onclick="event.stopPropagation(); toggleSection('localAuthorityBreakdown')">
                    <i class="fas fa-chevron-down" id="icon-localAuthorityBreakdown"></i>
                </button>
            </div>
            <div id="section-localAuthorityBreakdown" class="report-section-content" style="display: block;">
        <div style="overflow-x: auto; margin-bottom: 2rem;">
            <table class="table" style="min-width: 600px;">
                <thead>
                    <tr>
                        <th>Local Authority</th>
                        <th>Number of Contracts</th>
                        <th>Total Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Sort by value descending
                    uasort($contractsByLA, function($a, $b) {
                        return $b['value'] <=> $a['value'];
                    });
                    foreach ($contractsByLA as $laKey => $data):
                        $laName = $data['name'];
                    ?>
                        <tr>
                            <td>
                                <?php if (!empty($data['id'])): ?>
                                    <strong><a href="<?php echo htmlspecialchars(url('local-authority-view.php?id=' . $data['id'])); ?>" style="color: var(--primary-color); text-decoration: none;" onmouseover="this.style.textDecoration='underline'; this.style.color='var(--secondary-color)';" onmouseout="this.style.textDecoration='none'; this.style.color='var(--primary-color)';"><?php echo htmlspecialchars($laName); ?></a></strong>
                                <?php else: ?>
                                    <strong><?php echo htmlspecialchars($laName); ?></strong>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $data['count']; ?></td>
                            <td>£<?php echo number_format($data['value'], 2); ?></td>
                            <td>
                                <?php if (isset($laIssues[$laName])): 
                                    $issueData = $laIssues[$laName];
                                    $issues = $issueData['issues'] ?? [];
                                    $issuesText = implode(', ', $issues);
                                ?>
                                    <span style="color: var(--warning-color);" title="<?php echo htmlspecialchars($issuesText); ?>">
                                        <i class="fas fa-exclamation-triangle"></i> Issues
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--success-color);"><i class="fas fa-check-circle"></i> OK</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (!empty($laIssues)): ?>
            <div class="alert alert-warning" style="margin-bottom: 2rem; padding-left: 2rem; padding-right: 2rem;">
                <h4 style="margin-top: 0; margin-bottom: 1rem;"><i class="fas fa-exclamation-triangle"></i> Local Authority Issues Detected</h4>
                <div style="margin-bottom: 0;">
                    <?php foreach ($laIssues as $laName => $issueData): 
                        $laId = $issueData['id'] ?? null;
                        $issues = $issueData['issues'] ?? [];
                        $issuesText = implode(', ', $issues);
                    ?>
                        <div style="margin-bottom: 0.75rem; padding-left: 0;">
                            <?php if ($laId): ?>
                                <a href="<?php echo htmlspecialchars(url('local-authority-view.php?id=' . $laId)); ?>" style="text-decoration: none; color: inherit; display: inline-block; padding: 0.25rem 0.5rem; border-radius: 0.25rem; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='rgba(0,0,0,0.05)'; this.style.textDecoration='underline';" onmouseout="this.style.backgroundColor=''; this.style.textDecoration='none';">
                                    <strong style="color: var(--warning-color);"><?php echo htmlspecialchars($laName); ?>:</strong>
                                    <span style="margin-left: 0.5rem;"><?php echo htmlspecialchars($issuesText); ?></span>
                                    <i class="fas fa-external-link-alt" style="margin-left: 0.5rem; font-size: 0.85rem; opacity: 0.7;"></i>
                                </a>
                            <?php else: ?>
                                <strong style="color: var(--warning-color);"><?php echo htmlspecialchars($laName); ?>:</strong>
                                <span style="margin-left: 0.5rem;"><?php echo htmlspecialchars($issuesText); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Contract Activity -->
    <?php if (!empty($newContracts) || !empty($endingContracts)): ?>
        <div class="report-section" style="margin-bottom: 2rem;">
            <div class="report-section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; cursor: pointer;" onclick="toggleSection('contractActivity')">
                <h3 style="margin: 0;"><i class="fas fa-chart-area" style="margin-right: 0.5rem;"></i> Contract Activity</h3>
                <button class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.9rem;" onclick="event.stopPropagation(); toggleSection('contractActivity')">
                    <i class="fas fa-chevron-down" id="icon-contractActivity"></i>
                </button>
            </div>
            <div id="section-contractActivity" class="report-section-content" style="display: block;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <?php if (!empty($newContracts)): ?>
                <div class="card" style="border-left: 4px solid var(--success-color);">
                    <h4 style="color: var(--success-color); margin-top: 0;"><i class="fas fa-plus-circle"></i> New Contracts Started</h4>
                    <ul style="margin-bottom: 0; list-style: none; padding: 0;">
                        <?php foreach (array_slice($newContracts, 0, 10) as $contract): ?>
                            <li onclick="window.location.href='<?php echo htmlspecialchars(url('contract-view.php?id=' . $contract['id'])); ?>'" style="cursor: pointer; padding: 0.75rem; margin-bottom: 0.5rem; background: var(--bg-light); border-radius: 0.375rem; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='rgba(59, 130, 246, 0.1)';" onmouseout="this.style.backgroundColor='var(--bg-light)';">
                                <strong><?php echo htmlspecialchars($contract['title']); ?></strong>
                                <?php if ($contract['contract_number']): ?>
                                    <br><small style="color: var(--text-light);"><?php echo htmlspecialchars($contract['contract_number']); ?></small>
                                <?php endif; ?>
                                <br><small style="color: var(--text-light);">
                                    <?php echo htmlspecialchars($contract['local_authority_name'] ?? 'Unknown'); ?> - 
                                    Started: <?php echo date(DATE_FORMAT, strtotime($contract['start_date'])); ?>
                                    <?php if ($contract['total_amount']): ?>
                                        - Value: £<?php echo number_format($contract['total_amount'], 2); ?>
                                    <?php endif; ?>
                                </small>
                            </li>
                        <?php endforeach; ?>
                        <?php if (count($newContracts) > 10): ?>
                            <li style="padding: 0.5rem; color: var(--text-light);"><em>... and <?php echo count($newContracts) - 10; ?> more</em></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($endingContracts)): ?>
                <div class="card" style="border-left: 4px solid var(--danger-color);">
                    <h4 style="color: var(--danger-color); margin-top: 0;"><i class="fas fa-calendar-times"></i> Contracts Ending</h4>
                    <ul style="margin-bottom: 0; list-style: none; padding: 0;">
                        <?php foreach (array_slice($endingContracts, 0, 10) as $contract): ?>
                            <li onclick="window.location.href='<?php echo htmlspecialchars(url('contract-view.php?id=' . $contract['id'])); ?>'" style="cursor: pointer; padding: 0.75rem; margin-bottom: 0.5rem; background: var(--bg-light); border-radius: 0.375rem; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='rgba(59, 130, 246, 0.1)';" onmouseout="this.style.backgroundColor='var(--bg-light)';">
                                <strong><?php echo htmlspecialchars($contract['title']); ?></strong>
                                <?php if ($contract['contract_number']): ?>
                                    <br><small style="color: var(--text-light);"><?php echo htmlspecialchars($contract['contract_number']); ?></small>
                                <?php endif; ?>
                                <br><small style="color: var(--text-light);">
                                    <?php echo htmlspecialchars($contract['local_authority_name'] ?? 'Unknown'); ?> - 
                                    Ends: <?php echo date(DATE_FORMAT, strtotime($contract['end_date'])); ?>
                                    <?php if ($contract['total_amount']): ?>
                                        - Value: £<?php echo number_format($contract['total_amount'], 2); ?>
                                    <?php endif; ?>
                                </small>
                            </li>
                        <?php endforeach; ?>
                        <?php if (count($endingContracts) > 10): ?>
                            <li><em>... and <?php echo count($endingContracts) - 10; ?> more</em></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Payments by Method -->
    <?php if (!empty($paymentByMethod)): ?>
        <div class="report-section" style="margin-bottom: 2rem;">
            <div class="report-section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; cursor: pointer;" onclick="toggleSection('paymentsByMethod')">
                <h3 style="margin: 0;"><i class="fas fa-money-bill-wave" style="margin-right: 0.5rem;"></i> Payments by Method</h3>
                <button class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.9rem;" onclick="event.stopPropagation(); toggleSection('paymentsByMethod')">
                    <i class="fas fa-chevron-down" id="icon-paymentsByMethod"></i>
                </button>
            </div>
            <div id="section-paymentsByMethod" class="report-section-content" style="display: block;">
        <div style="overflow-x: auto; margin-bottom: 2rem;">
            <table class="table" style="min-width: 400px;">
                <thead>
                    <tr>
                        <th>Payment Method</th>
                        <th>Total Amount</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paymentByMethod as $method => $amount): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($method); ?></td>
                            <td>£<?php echo number_format($amount, 2); ?></td>
                            <td><?php echo $totalPayments > 0 ? number_format(($amount / $totalPayments) * 100, 1) : 0; ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Recent Payments -->
    <?php if (!empty($payments)): ?>
        <div class="report-section" style="margin-bottom: 2rem;">
            <div class="report-section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; cursor: pointer;" onclick="toggleSection('recentPayments')">
                <h3 style="margin: 0;"><i class="fas fa-receipt" style="margin-right: 0.5rem;"></i> Recent Payments</h3>
                <button class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.9rem;" onclick="event.stopPropagation(); toggleSection('recentPayments')">
                    <i class="fas fa-chevron-down" id="icon-recentPayments"></i>
                </button>
            </div>
            <div id="section-recentPayments" class="report-section-content" style="display: block;">
        <div style="overflow-x: auto; margin-bottom: 2rem;">
            <table class="table" style="min-width: 600px;">
                <thead>
                    <tr>
                        <th>Payment Method</th>
                        <th>Amount</th>
                        <th>Payment Date</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($payments, 0, 20) as $payment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['payment_method_name'] ?? 'Unknown'); ?></td>
                            <td>£<?php echo number_format($payment['amount'], 2); ?></td>
                            <td><?php echo $payment['payment_date'] ? date(DATE_FORMAT, strtotime($payment['payment_date'])) : 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($payment['description'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
            </div>
        </div>
    <?php endif; ?>
    
    <?php endif; ?>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
function toggleHelp() {
    const helpSection = document.getElementById('helpSection');
    if (helpSection.style.display === 'none') {
        helpSection.style.display = 'block';
    } else {
        helpSection.style.display = 'none';
    }
}

// Toggle report sections
function toggleSection(sectionId) {
    const section = document.getElementById('section-' + sectionId);
    const icon = document.getElementById('icon-' + sectionId);
    
    if (!section || !icon) return;
    
    const isHidden = section.style.display === 'none';
    section.style.display = isHidden ? 'block' : 'none';
    icon.className = isHidden ? 'fas fa-chevron-down' : 'fas fa-chevron-right';
    
    // Save preference to localStorage
    localStorage.setItem('reportSection_' + sectionId, isHidden ? 'shown' : 'hidden');
}

// Restore section visibility preferences on page load
function restoreSectionPreferences() {
    const sections = [
        'contractValueCharts',
        'paymentIncomeCharts',
        'localAuthorityBreakdown',
        'contractActivity',
        'paymentsByMethod',
        'recentPayments'
    ];
    
    sections.forEach(sectionId => {
        const savedState = localStorage.getItem('reportSection_' + sectionId);
        if (savedState === 'hidden') {
            const section = document.getElementById('section-' + sectionId);
            const icon = document.getElementById('icon-' + sectionId);
            if (section && icon) {
                section.style.display = 'none';
                icon.className = 'fas fa-chevron-right';
            }
        }
    });
}

// Export data to CSV
function exportToCSV() {
    const data = {
        summary: {
            activeContracts: <?php echo $activeInPeriod; ?>,
            totalContractValue: <?php echo $totalContractValue; ?>,
            totalPayments: <?php echo $totalPayments; ?>,
            newContracts: <?php echo count($newContracts); ?>,
            endingContracts: <?php echo count($endingContracts); ?>
        },
        contractsByLA: <?php echo json_encode($contractsByLA); ?>,
        contractValueByTypeAndLA: <?php echo json_encode($contractValueByTypeAndLA ?? []); ?>,
        incomeByLA: <?php echo json_encode($incomeByLA); ?>,
        incomeByContractType: <?php echo json_encode($incomeByContractType); ?>,
        paymentByMethod: <?php echo json_encode($paymentByMethod); ?>,
        payments: <?php echo json_encode(array_map(function($p) {
            return [
                'date' => $p['payment_date'],
                'amount' => $p['amount'],
                'method' => $p['payment_method_name'] ?? 'Unknown',
                'description' => $p['description'] ?? ''
            ];
        }, $payments)); ?>
    };
    
    // Create CSV content
    let csv = 'Report Data Export\n';
    csv += 'Date Range: <?php echo htmlspecialchars($startDate); ?> to <?php echo htmlspecialchars($endDate); ?>\n';
    csv += 'Generated: <?php echo date('Y-m-d H:i:s'); ?>\n\n';
    
    // Summary
    csv += 'SUMMARY\n';
    csv += 'Active Contracts,' + data.summary.activeContracts + '\n';
    csv += 'Total Contract Value,£' + data.summary.totalContractValue.toFixed(2) + '\n';
    csv += 'Total Payments,£' + data.summary.totalPayments.toFixed(2) + '\n';
    csv += 'New Contracts,' + data.summary.newContracts + '\n';
    csv += 'Ending Contracts,' + data.summary.endingContracts + '\n\n';
    
    // Contract Value by Local Authority
    if (Object.keys(data.contractsByLA).length > 0) {
        csv += 'CONTRACT VALUE BY LOCAL AUTHORITY\n';
        csv += 'Local Authority,Number of Contracts,Total Value\n';
        Object.entries(data.contractsByLA).sort((a, b) => b[1].value - a[1].value).forEach(([la, data]) => {
            csv += '"' + la + '",' + data.count + ',£' + data.value.toFixed(2) + '\n';
        });
        csv += '\n';
    }
    
    // Contract Value by Contract Type and Local Authority
    if (data.contractValueByTypeAndLA && Object.keys(data.contractValueByTypeAndLA).length > 0) {
        csv += 'CONTRACT VALUE BY CONTRACT TYPE AND LOCAL AUTHORITY\n';
        
        // Get all unique contract types
        const allTypes = new Set();
        Object.values(data.contractValueByTypeAndLA).forEach(laData => {
            Object.keys(laData).forEach(type => allTypes.add(type));
        });
        const typeLabels = Array.from(allTypes).sort();
        
        // Header row
        csv += 'Local Authority,' + typeLabels.map(t => '"' + t + '"').join(',') + ',Total\n';
        
        // Data rows
        Object.keys(data.contractValueByTypeAndLA).sort((a, b) => {
            const totalA = Object.values(data.contractValueByTypeAndLA[a]).reduce((sum, val) => sum + val, 0);
            const totalB = Object.values(data.contractValueByTypeAndLA[b]).reduce((sum, val) => sum + val, 0);
            return totalB - totalA;
        }).forEach(la => {
            const laData = data.contractValueByTypeAndLA[la];
            const values = typeLabels.map(type => laData[type] || 0);
            const total = values.reduce((sum, val) => sum + val, 0);
            csv += '"' + la + '",' + values.map(v => '£' + v.toFixed(2)).join(',') + ',£' + total.toFixed(2) + '\n';
        });
        csv += '\n';
    }
    
    // Payment Income by Local Authority
    if (Object.keys(data.incomeByLA).length > 0) {
        csv += 'PAYMENT INCOME BY LOCAL AUTHORITY\n';
        csv += 'Local Authority,Amount,Percentage\n';
        const laTotal = Object.values(data.incomeByLA).reduce((a, b) => a + b, 0);
        Object.entries(data.incomeByLA).sort((a, b) => b[1] - a[1]).forEach(([la, amount]) => {
            const percent = laTotal > 0 ? ((amount / laTotal) * 100).toFixed(2) : 0;
            csv += '"' + la + '",£' + amount.toFixed(2) + ',' + percent + '%\n';
        });
        csv += '\n';
    }
    
    // Income by Contract Type
    if (Object.keys(data.incomeByContractType).length > 0) {
        csv += 'INCOME BY CONTRACT TYPE\n';
        csv += 'Contract Type,Amount,Percentage\n';
        const typeTotal = Object.values(data.incomeByContractType).reduce((a, b) => a + b, 0);
        Object.entries(data.incomeByContractType).sort((a, b) => b[1] - a[1]).forEach(([type, amount]) => {
            const percent = typeTotal > 0 ? ((amount / typeTotal) * 100).toFixed(2) : 0;
            csv += '"' + type + '",£' + amount.toFixed(2) + ',' + percent + '%\n';
        });
        csv += '\n';
    }
    
    // Payments by Method
    if (Object.keys(data.paymentByMethod).length > 0) {
        csv += 'PAYMENTS BY METHOD\n';
        csv += 'Payment Method,Amount,Percentage\n';
        const methodTotal = Object.values(data.paymentByMethod).reduce((a, b) => a + b, 0);
        Object.entries(data.paymentByMethod).sort((a, b) => b[1] - a[1]).forEach(([method, amount]) => {
            const percent = methodTotal > 0 ? ((amount / methodTotal) * 100).toFixed(2) : 0;
            csv += '"' + method + '",£' + amount.toFixed(2) + ',' + percent + '%\n';
        });
        csv += '\n';
    }
    
    // Recent Payments
    if (data.payments.length > 0) {
        csv += 'RECENT PAYMENTS\n';
        csv += 'Date,Amount,Method,Description\n';
        data.payments.forEach(p => {
            csv += '"' + (p.date || 'N/A') + '",£' + p.amount.toFixed(2) + ',"' + (p.method || 'Unknown') + '","' + (p.description || '') + '"\n';
        });
    }
    
    // Download CSV
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'reports_<?php echo date('Y-m-d'); ?>.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Restore section visibility preferences
    restoreSectionPreferences();
    
    // Contract Value by Local Authority Chart (matches the table)
    <?php if (!empty($contractsByLA)): ?>
    const contractValueLACtx = document.getElementById('contractValueByLAChart');
    if (contractValueLACtx) {
        const laLabels = <?php echo json_encode(array_column($contractsByLA, 'name')); ?>;
        const laData = <?php echo json_encode(array_column($contractsByLA, 'value')); ?>;
        const laColors = generateColors(laLabels.length);
        
        new Chart(contractValueLACtx, {
            type: 'pie',
            data: {
                labels: laLabels,
                datasets: [{
                    data: laData,
                    backgroundColor: laColors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percent = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': £' + value.toFixed(2) + ' (' + percent + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>
    
    // Contract Value by Contract Type Chart
    <?php if (!empty($contractValueByType)): ?>
    const contractValueTypeCtx = document.getElementById('contractValueByTypeChart');
    if (contractValueTypeCtx) {
        const typeLabels = <?php echo json_encode(array_keys($contractValueByType)); ?>;
        const typeData = <?php echo json_encode(array_values($contractValueByType)); ?>;
        const typeColors = generateColors(typeLabels.length);
        
        new Chart(contractValueTypeCtx, {
            type: 'pie',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeData,
                    backgroundColor: typeColors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percent = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': £' + value.toFixed(2) + ' (' + percent + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>
    
    // Contract Value by Contract Type and Local Authority Chart
    <?php if (!empty($contractValueByTypeAndLA)): ?>
    const typeAndLACtx = document.getElementById('contractValueByTypeAndLAChart');
    if (typeAndLACtx) {
        const typeAndLAData = <?php echo json_encode($contractValueByTypeAndLA); ?>;
        
        // Get all unique contract types across all local authorities
        const allTypes = new Set();
        Object.values(typeAndLAData).forEach(laData => {
            Object.keys(laData).forEach(type => allTypes.add(type));
        });
        const typeLabels = Array.from(allTypes).sort();
        
        // Get all local authorities, sorted by total value
        const laLabels = Object.keys(typeAndLAData).sort((a, b) => {
            const totalA = Object.values(typeAndLAData[a]).reduce((sum, val) => sum + val, 0);
            const totalB = Object.values(typeAndLAData[b]).reduce((sum, val) => sum + val, 0);
            return totalB - totalA;
        });
        
        // Create datasets for each contract type
        const colors = generateColors(typeLabels.length);
        const datasets = typeLabels.map((type, index) => {
            const data = laLabels.map(la => typeAndLAData[la][type] || 0);
            return {
                label: type,
                data: data,
                backgroundColor: colors[index],
                borderWidth: 1,
                borderColor: '#fff'
            };
        });
        
        new Chart(typeAndLACtx, {
            type: 'bar',
            data: {
                labels: laLabels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    x: {
                        stacked: true,
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '£' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.dataset.label || '';
                                const value = context.parsed.y || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const laTotal = laLabels.reduce((sum, la) => {
                                    return sum + (typeAndLAData[la][label] || 0);
                                }, 0);
                                const percent = laTotal > 0 ? ((value / laTotal) * 100).toFixed(1) : 0;
                                return label + ': £' + value.toLocaleString('en-GB', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' (' + percent + '% of ' + label + ')';
                            },
                            footer: function(tooltipItems) {
                                const laIndex = tooltipItems[0].dataIndex;
                                const laName = laLabels[laIndex];
                                const laTotal = Object.values(typeAndLAData[laName]).reduce((sum, val) => sum + val, 0);
                                return 'Total for ' + laName + ': £' + laTotal.toLocaleString('en-GB', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>
    
    // Payment Income by Local Authority Chart (actual payments received)
    <?php if (!empty($incomeByLA)): ?>
    const laCtx = document.getElementById('incomeByLAChart');
    if (laCtx) {
        const laLabels = <?php echo json_encode(array_keys($incomeByLA)); ?>;
        const laData = <?php echo json_encode(array_values($incomeByLA)); ?>;
        const laColors = generateColors(laLabels.length);
        
        new Chart(laCtx, {
            type: 'pie',
            data: {
                labels: laLabels,
                datasets: [{
                    data: laData,
                    backgroundColor: laColors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percent = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': £' + value.toFixed(2) + ' (' + percent + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>
    
    // Income by Contract Type Chart
    <?php if (!empty($incomeByContractType)): ?>
    const typeCtx = document.getElementById('incomeByContractTypeChart');
    if (typeCtx) {
        const typeLabels = <?php echo json_encode(array_keys($incomeByContractType)); ?>;
        const typeData = <?php echo json_encode(array_values($incomeByContractType)); ?>;
        const typeColors = generateColors(typeLabels.length);
        
        new Chart(typeCtx, {
            type: 'pie',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeData,
                    backgroundColor: typeColors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percent = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': £' + value.toFixed(2) + ' (' + percent + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>
    
    // Income by Payment Method Chart
    <?php if (!empty($paymentByMethod)): ?>
    const methodCtx = document.getElementById('incomeByMethodChart');
    if (methodCtx) {
        const methodLabels = <?php echo json_encode(array_keys($paymentByMethod)); ?>;
        const methodData = <?php echo json_encode(array_values($paymentByMethod)); ?>;
        const methodColors = generateColors(methodLabels.length);
        
        new Chart(methodCtx, {
            type: 'pie',
            data: {
                labels: methodLabels,
                datasets: [{
                    data: methodData,
                    backgroundColor: methodColors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percent = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': £' + value.toFixed(2) + ' (' + percent + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>
});

// Generate color palette for charts
function generateColors(count) {
    const colors = [
        'rgba(37, 99, 235, 0.8)',   // Blue
        'rgba(16, 185, 129, 0.8)',   // Green
        'rgba(245, 158, 11, 0.8)',   // Orange
        'rgba(239, 68, 68, 0.8)',    // Red
        'rgba(139, 92, 246, 0.8)',   // Purple
        'rgba(236, 72, 153, 0.8)',   // Pink
        'rgba(59, 130, 246, 0.8)',   // Light Blue
        'rgba(34, 197, 94, 0.8)',    // Light Green
        'rgba(251, 146, 60, 0.8)',   // Light Orange
        'rgba(168, 85, 247, 0.8)'    // Light Purple
    ];
    
    const result = [];
    for (let i = 0; i < count; i++) {
        result.push(colors[i % colors.length]);
    }
    return result;
}
</script>

<?php include INCLUDES_PATH . '/footer.php'; ?>
