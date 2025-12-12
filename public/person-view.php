<?php
/**
 * Person Detail View
 * Shows person's contract and payment history across all local authorities
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
RBAC::requireAdmin();

$organisationId = Auth::getOrganisationId();
$personId = $_GET['id'] ?? 0;

$person = Person::findById($personId);

if (!$person || !Person::belongsToOrganisation($personId, $organisationId)) {
    header('Location: ' . url('people.php?error=not_found'));
    exit;
}

$identifiers = Person::getIdentifiers($personId);
$contracts = Person::getContracts($personId);
$laHistory = Person::getLocalAuthorityHistory($personId);

// Get date range for payment history
$startDate = $_GET['start_date'] ?? date('Y-m-01', strtotime('-12 months'));
$endDate = $_GET['end_date'] ?? date('Y-m-t');
$paymentHistory = Person::getPaymentHistory($personId, $startDate, $endDate);

$totalPayments = array_sum(array_column($paymentHistory, 'amount'));

$personSingular = getPersonTerm(true);
$personPlural = getPersonTerm(false);

$pageTitle = ucfirst($personSingular) . ' Details';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2><?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_name']); ?></h2>
                <p style="color: var(--text-light); margin-top: 0.5rem;">
                    Complete history across all local authorities
                </p>
            </div>
            <a href="<?php echo url('people.php'); ?>" class="btn btn-secondary">Back to <?php echo ucfirst(htmlspecialchars($personPlural)); ?></a>
        </div>
    </div>
    
    <!-- Person Information -->
    <div style="margin-bottom: 2rem;">
        <h3>Personal Information</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
            <div>
                <p><strong>Date of Birth:</strong> 
                    <?php echo $person['date_of_birth'] ? date(DATE_FORMAT, strtotime($person['date_of_birth'])) : 'Not provided'; ?>
                </p>
            </div>
            <div>
                <p><strong>Organisation:</strong> <?php echo htmlspecialchars($person['organisation_name']); ?></p>
            </div>
        </div>
        
        <div style="margin-top: 1rem;">
            <h4>Identifiers</h4>
            <?php if (empty($identifiers)): ?>
                <p style="color: var(--text-light);">No identifiers recorded.</p>
            <?php else: ?>
                <table class="table" style="margin-top: 0.5rem;">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($identifiers as $identifier): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($identifier['identifier_type']); ?></td>
                                <td><?php echo htmlspecialchars($identifier['identifier_value']); ?></td>
                                <td>
                                    <?php if ($identifier['is_primary']): ?>
                                        <span style="color: var(--success-color);">Primary</span>
                                    <?php endif; ?>
                                    <?php if ($identifier['verified']): ?>
                                        <span style="color: var(--success-color);">Verified</span>
                                    <?php else: ?>
                                        <span style="color: var(--text-light);">Unverified</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Local Authority History -->
    <div style="margin-bottom: 2rem;">
        <h3>Local Authority History</h3>
        <?php if (empty($laHistory)): ?>
            <p style="color: var(--text-light);">No local authority history recorded.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Local Authority</th>
                        <th>First Contact</th>
                        <th>Last Contact</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($laHistory as $la): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($la['local_authority_name']); ?></td>
                            <td><?php echo date(DATE_FORMAT, strtotime($la['first_contact_date'])); ?></td>
                            <td><?php echo date(DATE_FORMAT, strtotime($la['last_contact_date'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Contract History -->
    <div style="margin-bottom: 2rem;">
        <h3>Contract History</h3>
        <?php if (empty($contracts)): ?>
            <p style="color: var(--text-light);">No contracts recorded.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Contract</th>
                        <th>Type</th>
                        <th>Local Authority</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
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
                            <td><?php echo htmlspecialchars($contract['contract_type_name']); ?></td>
                            <td><?php echo htmlspecialchars($contract['local_authority_name']); ?></td>
                            <td><?php echo date(DATE_FORMAT, strtotime($contract['person_start_date'])); ?></td>
                            <td><?php echo $contract['person_end_date'] ? date(DATE_FORMAT, strtotime($contract['person_end_date'])) : 'Ongoing'; ?></td>
                            <td>
                                <?php
                                $statusColors = [
                                    'active' => 'var(--success-color)',
                                    'completed' => 'var(--text-light)',
                                    'inactive' => 'var(--danger-color)'
                                ];
                                $color = $statusColors[$contract['status']] ?? 'var(--text-light)';
                                ?>
                                <span style="color: <?php echo $color; ?>;"><?php echo ucfirst($contract['status']); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Payment History -->
    <div>
        <h3>Payment History</h3>
        
        <!-- Date Range Filter -->
        <form method="GET" action="" style="margin-bottom: 1rem;">
            <input type="hidden" name="id" value="<?php echo $personId; ?>">
            <div style="display: flex; gap: 1rem; align-items: end; flex-wrap: wrap;">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>">
                </div>
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-secondary">Filter</button>
                </div>
            </div>
        </form>
        
        <?php if (empty($paymentHistory)): ?>
            <p style="color: var(--text-light);">No payments recorded for this date range.</p>
        <?php else: ?>
            <div style="margin-bottom: 1rem;">
                <p><strong>Total Payments:</strong> £<?php echo number_format($totalPayments, 2); ?></p>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Contract</th>
                        <th>Local Authority</th>
                        <th>Payment Method</th>
                        <th>Frequency</th>
                        <th>Amount</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paymentHistory as $payment): ?>
                        <tr>
                            <td><?php echo $payment['payment_date'] ? date(DATE_FORMAT, strtotime($payment['payment_date'])) : '-'; ?></td>
                            <td>
                                <?php echo htmlspecialchars($payment['contract_title']); ?>
                                <?php if ($payment['contract_number']): ?>
                                    <br><small style="color: var(--text-light);"><?php echo htmlspecialchars($payment['contract_number']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($payment['local_authority_name']); ?></td>
                            <td><?php echo htmlspecialchars($payment['payment_method_name'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($payment['payment_frequency'] ?? '-'); ?></td>
                            <td>£<?php echo number_format($payment['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($payment['description'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
