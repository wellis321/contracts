<?php
/**
 * Rates Management Page
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
RBAC::requireAdmin(); // Only admins can manage rates

$organisationId = Auth::getOrganisationId();
$error = '';
$success = '';

// Get contract types
$contractTypes = ContractType::findByOrganisation($organisationId);

if (empty($contractTypes)) {
    $error = 'Please create contract types before managing rates.';
}

// Get selected contract type
$selectedContractTypeId = $_GET['contract_type_id'] ?? ($contractTypes[0]['id'] ?? null);
$selectedContractType = null;

if ($selectedContractTypeId) {
    foreach ($contractTypes as $type) {
        if ($type['id'] == $selectedContractTypeId) {
            $selectedContractType = $type;
            break;
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $selectedContractType) {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $localAuthorityId = $_POST['local_authority_id'] ?? 0;
        $rateAmount = $_POST['rate_amount'] ?? 0;
        $effectiveFrom = $_POST['effective_from'] ?? date('Y-m-d');
        
        if (empty($localAuthorityId) || empty($rateAmount) || empty($effectiveFrom)) {
            $error = 'Please fill in all required fields.';
        } else {
            try {
                Rate::setRate($selectedContractTypeId, $localAuthorityId, $rateAmount, $effectiveFrom, Auth::getUserId());
                $success = 'Rate set successfully.';
            } catch (Exception $e) {
                $error = 'Error setting rate: ' . $e->getMessage();
            }
        }
    }
}

// Get local authorities
$db = getDbConnection();
$stmt = $db->query("SELECT * FROM local_authorities ORDER BY name");
$localAuthorities = $stmt->fetchAll();

// Get current rates for selected contract type
$currentRates = [];
$rateHistory = [];
if ($selectedContractType) {
    $rates = Rate::findByContractType($selectedContractTypeId);
    foreach ($rates as $rate) {
        if ($rate['is_current']) {
            $currentRates[$rate['local_authority_id']] = $rate;
        }
    }
    $rateHistory = Rate::getHistory($selectedContractTypeId);
}

$pageTitle = 'Rates';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Rates Management</h2>
        <p>Set and manage rates for contract types by local authority</p>
    </div>
    
    <?php if ($error && strpos($error, 'create contract types') === false): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if (empty($contractTypes)): ?>
        <div class="alert alert-warning">
            <p>No contract types found. Please <a href="<?php echo htmlspecialchars(url('contract-types.php')); ?>">create contract types</a> first.</p>
        </div>
    <?php else: ?>
        <!-- Contract Type Selector -->
        <div class="form-group">
            <label for="contract_type_select">Select Contract Type</label>
            <select id="contract_type_select" class="form-control" onchange="window.location.href='?contract_type_id=' + this.value">
                <?php foreach ($contractTypes as $type): ?>
                    <option value="<?php echo $type['id']; ?>" <?php echo $selectedContractTypeId == $type['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($type['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <?php if ($selectedContractType): ?>
            <h3 style="margin: 2rem 0 1rem;">Set Rate for: <?php echo htmlspecialchars($selectedContractType['name']); ?></h3>
            
            <!-- Set Rate Form -->
            <div style="padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem; margin-bottom: 2rem;">
                <form method="POST" action="">
                    <?php echo CSRF::tokenField(); ?>
                    <input type="hidden" name="contract_type_id" value="<?php echo $selectedContractTypeId; ?>">
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                        <div class="form-group">
                            <label for="local_authority_id">Local Authority *</label>
                            <select id="local_authority_id" name="local_authority_id" class="form-control" required>
                                <option value="">Select...</option>
                                <?php foreach ($localAuthorities as $la): ?>
                                    <option value="<?php echo $la['id']; ?>">
                                        <?php echo htmlspecialchars($la['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="rate_amount">Rate Amount (£) *</label>
                            <input type="number" id="rate_amount" name="rate_amount" class="form-control" step="0.01" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="effective_from">Effective From *</label>
                            <input type="date" id="effective_from" name="effective_from" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Set Rate</button>
                    </div>
                </form>
            </div>
            
            <!-- Current Rates -->
            <h3 style="margin: 2rem 0 1rem;">Current Rates</h3>
            <?php if (empty($currentRates)): ?>
                <p>No rates set for this contract type yet.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Local Authority</th>
                            <th>Rate Amount</th>
                            <th>Effective From</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($currentRates as $rate): ?>
                            <tr>
                                <td>
                                    <?php
                                    $laName = 'Unknown';
                                    foreach ($localAuthorities as $la) {
                                        if ($la['id'] == $rate['local_authority_id']) {
                                            $laName = $la['name'];
                                            break;
                                        }
                                    }
                                    echo htmlspecialchars($laName);
                                    ?>
                                </td>
                                <td>£<?php echo number_format($rate['rate_amount'], 2); ?></td>
                                <td><?php echo date(DATE_FORMAT, strtotime($rate['effective_from'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <!-- Rate History -->
            <h3 style="margin: 2rem 0 1rem;">Rate History</h3>
            <?php if (empty($rateHistory)): ?>
                <p>No rate history available.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Local Authority</th>
                            <th>Previous Rate</th>
                            <th>New Rate</th>
                            <th>Changed At</th>
                            <th>Changed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rateHistory as $history): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($history['local_authority_name']); ?></td>
                                <td>
                                    <?php echo $history['previous_rate'] !== null ? '£' . number_format($history['previous_rate'], 2) : 'N/A'; ?>
                                </td>
                                <td>£<?php echo number_format($history['new_rate'], 2); ?></td>
                                <td><?php echo date(DATETIME_FORMAT, strtotime($history['changed_at'])); ?></td>
                                <td>
                                    <?php
                                    if ($history['first_name']) {
                                        echo htmlspecialchars($history['first_name'] . ' ' . $history['last_name']);
                                    } else {
                                        echo 'System';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
