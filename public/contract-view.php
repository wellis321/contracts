<?php
/**
 * Contract View/Edit Page
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
$organisationId = Auth::getOrganisationId();
$isAdmin = RBAC::isAdmin();
$error = '';
$success = '';

$id = $_GET['id'] ?? 0;
$contract = Contract::findById($id);

if (!$contract || !Contract::belongsToOrganisation($id, $organisationId)) {
    header('Location: ' . url('contracts.php?error=not_found'));
    exit;
}

// Check team-based access
if (!RBAC::canAccessContract($id)) {
    header('Location: ' . url('contracts.php?error=access_denied'));
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && RBAC::canManageContracts()) {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
            $data = [
                'team_id' => !empty($_POST['team_id']) ? intval($_POST['team_id']) : null,
                'contract_type_id' => $_POST['contract_type_id'] ?? 0,
                'local_authority_id' => $_POST['local_authority_id'] ?? 0,
                'person_id' => !empty($_POST['person_id']) ? intval($_POST['person_id']) : null,
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'stipulations' => trim($_POST['stipulations'] ?? ''),
            'contract_number' => trim($_POST['contract_number'] ?? ''),
            'procurement_route' => trim($_POST['procurement_route'] ?? ''),
            'tender_status' => trim($_POST['tender_status'] ?? ''),
            'framework_agreement_id' => trim($_POST['framework_agreement_id'] ?? ''),
            'evaluation_criteria' => trim($_POST['evaluation_criteria'] ?? ''),
            'quality_price_weighting' => trim($_POST['quality_price_weighting'] ?? ''),
            'start_date' => $_POST['start_date'] ?? '',
            'end_date' => $_POST['end_date'] ?? null,
            'contract_duration_months' => !empty($_POST['contract_duration_months']) ? intval($_POST['contract_duration_months']) : null,
            'extension_options' => trim($_POST['extension_options'] ?? ''),
            'price_review_mechanism' => trim($_POST['price_review_mechanism'] ?? ''),
            'inflation_indexation' => trim($_POST['inflation_indexation'] ?? ''),
            'fair_work_compliance' => isset($_POST['fair_work_compliance']),
            'community_benefits' => trim($_POST['community_benefits'] ?? ''),
            'is_single_person' => isset($_POST['is_single_person']) && $_POST['is_single_person'] === '1',
            'number_of_people' => $_POST['number_of_people'] ?? 1,
            'total_amount' => $_POST['total_amount'] ?? null,
            'daytime_hours' => $_POST['daytime_hours'] ?? null,
            'sleepover_hours' => $_POST['sleepover_hours'] ?? null,
            'number_of_staff' => $_POST['number_of_staff'] ?? null,
            'status' => $_POST['status'] ?? 'active'
        ];
        
        if (empty($data['title']) || empty($data['contract_type_id']) || empty($data['local_authority_id']) || empty($data['start_date'])) {
            $error = 'Please fill in all required fields.';
        } else {
            Contract::update($id, $data);
            $success = 'Contract updated successfully.';
            $contract = Contract::findById($id); // Refresh
        }
    }
}

// Get contract types and local authorities
$db = getDbConnection();
$contractTypes = ContractType::findByOrganisation($organisationId);
$stmt = $db->query("SELECT * FROM local_authorities ORDER BY name");
$localAuthorities = $stmt->fetchAll();

// Get procurement routes and tender statuses
$procurementRoutes = ProcurementRoute::findAll();
$tenderStatuses = TenderStatus::findAll();

// Get teams for organisation
$teams = Team::findByOrganisation($organisationId);
$userTeams = Team::getUserTeams(Auth::getUserId());
$canAccessAllTeams = false;
foreach ($userTeams as $team) {
    if (in_array($team['role_in_team'], ['finance', 'senior_manager'])) {
        $canAccessAllTeams = true;
        break;
    }
}
// Get accessible teams for dropdown
$assignableTeams = $canAccessAllTeams ? $teams : array_filter($teams, function($team) use ($userTeams) {
    $userTeamIds = array_column($userTeams, 'id');
    return in_array($team['id'], $userTeamIds) || Team::userHasAccessToTeam(Auth::getUserId(), $team['id']);
});

// Get people for single person contracts
$people = Person::findByOrganisation($organisationId);

// Deduplicate people (same logic as people.php)
$seenIds = [];
$seenPersonKeys = [];
$uniquePeople = [];
foreach ($people as $person) {
    $id = $person['id'] ?? null;
    $firstName = $person['first_name'] ?? '';
    $lastName = $person['last_name'] ?? '';
    $dateOfBirth = $person['date_of_birth'] ?? '';
    
    // First priority: skip if we've seen this exact person ID
    if ($id && in_array($id, $seenIds)) {
        continue;
    }
    
    // Second priority: if we've seen this exact combination of name and DOB, skip
    $personKey = md5($firstName . '|' . $lastName . '|' . $dateOfBirth);
    if (in_array($personKey, $seenPersonKeys)) {
        continue;
    }
    
    // Add to unique people
    if ($id) {
        $seenIds[] = $id;
    }
    $seenPersonKeys[] = $personKey;
    $uniquePeople[] = $person;
}
$people = $uniquePeople;

// Handle payment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    $paymentAction = $_POST['payment_action'] ?? '';
    
    if ($paymentAction === 'add' && !CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } elseif ($paymentAction === 'add') {
        $paymentMethodId = $_POST['payment_method_id'] ?? 0;
        $paymentFrequency = trim($_POST['payment_frequency'] ?? '');
        $amount = $_POST['amount'] ?? 0;
        $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');
        $description = trim($_POST['payment_description'] ?? '');
        
        if (empty($paymentMethodId) || empty($amount)) {
            $error = 'Payment method and amount are required.';
        } else {
            ContractPayment::create($id, $paymentMethodId, $amount, $paymentDate, $description, $paymentFrequency ?: null);
            $success = 'Payment added successfully.';
        }
    } elseif ($paymentAction === 'delete' && !CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } elseif ($paymentAction === 'delete') {
        $paymentId = $_POST['payment_id'] ?? 0;
        if ($paymentId && ContractPayment::belongsToOrganisation($paymentId, $organisationId)) {
            ContractPayment::delete($paymentId);
            $success = 'Payment deleted successfully.';
        } else {
            $error = 'Invalid payment.';
        }
    }
}

// Get payments for this contract
$payments = ContractPayment::findByContract($id);

// Get payment methods
$paymentMethods = PaymentMethod::findAll();

$pageTitle = 'View Contract';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-between">
            <div>
                <h2><?php echo htmlspecialchars($contract['title']); ?></h2>
                <?php if ($contract['contract_number']): ?>
                    <p style="color: var(--text-light);">Contract Number: <?php echo htmlspecialchars($contract['contract_number']); ?></p>
                <?php endif; ?>
            </div>
            <a href="<?php echo htmlspecialchars(url('contracts.php')); ?>" class="btn btn-secondary">Back to Contracts</a>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($isAdmin): ?>
        <form method="POST" action="">
            <?php echo CSRF::tokenField(); ?>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($contract['title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="contract_number">Contract Number</label>
                    <input type="text" id="contract_number" name="contract_number" class="form-control" value="<?php echo htmlspecialchars($contract['contract_number'] ?? ''); ?>" placeholder="Leave blank to auto-generate">
                    <small style="color: var(--text-light); font-size: 0.875rem;">If left blank, a contract number will be automatically generated based on your organisation prefix and the contract start date (e.g., HCS-2024-001)</small>
                </div>
            </div>
            
            <div class="form-group">
                <label for="contract_type_id">Contract Type *</label>
                <select id="contract_type_id" name="contract_type_id" class="form-control" required>
                    <?php foreach ($contractTypes as $type): ?>
                        <option value="<?php echo $type['id']; ?>" <?php echo $type['id'] == $contract['contract_type_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="local_authority_id">Local Authority *</label>
                <select id="local_authority_id" name="local_authority_id" class="form-control" required>
                    <?php foreach ($localAuthorities as $la): ?>
                        <option value="<?php echo $la['id']; ?>" <?php echo $la['id'] == $contract['local_authority_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($la['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3"><?php echo htmlspecialchars($contract['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="stipulations">Contract Stipulations & Requirements</label>
                <textarea id="stipulations" name="stipulations" class="form-control" rows="4" placeholder="Enter any contract-specific conditions, requirements, or stipulations (e.g., staff training requirements, location restrictions, compliance requirements, etc.)"><?php echo htmlspecialchars($contract['stipulations'] ?? ''); ?></textarea>
                <small style="color: var(--text-light);">Examples: Staff training requirements, location restrictions, compliance requirements, operational conditions, etc.</small>
            </div>
            
            <!-- Procurement Information -->
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid var(--border-color);">
                <h4 style="margin-bottom: 1rem;">Procurement Information</h4>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="procurement_route">Procurement Route</label>
                        <select id="procurement_route" name="procurement_route" class="form-control">
                            <option value="">Select...</option>
                            <?php foreach ($procurementRoutes as $route): ?>
                                <option value="<?php echo htmlspecialchars($route['name']); ?>" <?php echo ($contract['procurement_route'] ?? '') === $route['name'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($route['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="tender_status">Tender Status</label>
                        <select id="tender_status" name="tender_status" class="form-control">
                            <option value="">Select...</option>
                            <?php foreach ($tenderStatuses as $status): ?>
                                <option value="<?php echo htmlspecialchars($status['name']); ?>" <?php echo ($contract['tender_status'] ?? '') === $status['name'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($status['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group" style="margin-top: 1rem;">
                    <label for="framework_agreement_id">Framework Agreement ID</label>
                    <input type="text" id="framework_agreement_id" name="framework_agreement_id" class="form-control" value="<?php echo htmlspecialchars($contract['framework_agreement_id'] ?? ''); ?>">
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-top: 1rem;">
                    <div class="form-group">
                        <label for="quality_price_weighting">Quality:Price Weighting</label>
                        <input type="text" id="quality_price_weighting" name="quality_price_weighting" class="form-control" value="<?php echo htmlspecialchars($contract['quality_price_weighting'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="contract_duration_months">Contract Duration (Months)</label>
                        <input type="number" id="contract_duration_months" name="contract_duration_months" class="form-control" value="<?php echo $contract['contract_duration_months'] ?? ''; ?>">
                    </div>
                </div>
                
                <div class="form-group" style="margin-top: 1rem;">
                    <label for="evaluation_criteria">Evaluation Criteria</label>
                    <textarea id="evaluation_criteria" name="evaluation_criteria" class="form-control" rows="2"><?php echo htmlspecialchars($contract['evaluation_criteria'] ?? ''); ?></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-top: 1rem;">
                    <div class="form-group">
                        <label for="extension_options">Extension Options</label>
                        <input type="text" id="extension_options" name="extension_options" class="form-control" value="<?php echo htmlspecialchars($contract['extension_options'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="price_review_mechanism">Price Review Mechanism</label>
                        <input type="text" id="price_review_mechanism" name="price_review_mechanism" class="form-control" value="<?php echo htmlspecialchars($contract['price_review_mechanism'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="inflation_indexation">Inflation Indexation</label>
                        <input type="text" id="inflation_indexation" name="inflation_indexation" class="form-control" value="<?php echo htmlspecialchars($contract['inflation_indexation'] ?? ''); ?>">
                    </div>
                </div>
                
                <div style="margin-top: 1rem;">
                    <label>
                        <input type="checkbox" id="fair_work_compliance" name="fair_work_compliance" value="1" <?php echo ($contract['fair_work_compliance'] ?? 0) ? 'checked' : ''; ?>>
                        Fair Work Compliance Required
                    </label>
                </div>
                
                <div class="form-group" style="margin-top: 1rem;">
                    <label for="community_benefits">Community Benefits</label>
                    <textarea id="community_benefits" name="community_benefits" class="form-control" rows="2"><?php echo htmlspecialchars($contract['community_benefits'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1.5rem;">
                <div class="form-group">
                    <label for="start_date">Start Date *</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo $contract['start_date']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo $contract['end_date'] ?? ''; ?>">
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 1.5rem;">
                <label>
                    <input type="checkbox" id="is_single_person" name="is_single_person" value="1" <?php echo $contract['is_single_person'] ? 'checked' : ''; ?> onchange="toggleBulkFields()">
                    Single <?php echo ucfirst(htmlspecialchars(getPersonTerm(true))); ?> Contract
                </label>
            </div>
            
            <div id="singlePersonField" style="<?php echo $contract['is_single_person'] ? '' : 'display: none;'; ?> margin-top: 1rem;">
                <div class="form-group">
                    <label for="person_id"><?php echo ucfirst(htmlspecialchars(getPersonTerm(true))); ?> (Optional)</label>
                    <select id="person_id" name="person_id" class="form-control">
                        <option value="">Select <?php echo htmlspecialchars(getPersonTerm(true)); ?> or leave blank</option>
                        <?php foreach ($people as $person): ?>
                            <option value="<?php echo $person['id']; ?>" <?php echo ($contract['person_id'] ?? null) == $person['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div id="bulkFields" style="<?php echo $contract['is_single_person'] ? 'display: none;' : ''; ?>">
                <div class="form-group">
                    <label for="number_of_people">Number of <?php echo ucfirst(htmlspecialchars(getPersonTerm(false))); ?></label>
                    <input type="number" id="number_of_people" name="number_of_people" class="form-control" min="1" value="<?php echo $contract['number_of_people']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="number_of_staff">Number of Staff</label>
                    <input type="number" id="number_of_staff" name="number_of_staff" class="form-control" min="1" value="<?php echo $contract['number_of_staff'] ?? ''; ?>">
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="daytime_hours">Daytime Hours</label>
                        <input type="number" id="daytime_hours" name="daytime_hours" class="form-control" step="0.01" value="<?php echo $contract['daytime_hours'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="sleepover_hours">Sleepover Hours</label>
                        <input type="number" id="sleepover_hours" name="sleepover_hours" class="form-control" step="0.01" value="<?php echo $contract['sleepover_hours'] ?? ''; ?>">
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="total_amount">Total Amount</label>
                <input type="number" id="total_amount" name="total_amount" class="form-control" step="0.01" value="<?php echo $contract['total_amount'] ?? ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="active" <?php echo $contract['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="completed" <?php echo $contract['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="inactive" <?php echo $contract['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Contract</button>
                <a href="<?php echo htmlspecialchars(url('contracts.php')); ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    <?php else: ?>
        <!-- Read-only view for non-admins -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
            <div>
                <strong>Contract Type:</strong><br>
                <?php echo htmlspecialchars($contract['contract_type_name']); ?>
            </div>
            <div>
                <strong>Local Authority:</strong><br>
                <?php echo htmlspecialchars($contract['local_authority_name']); ?>
            </div>
            <?php if (!empty($contract['team_name'])): ?>
                <div>
                    <strong>Team:</strong><br>
                    <?php echo htmlspecialchars($contract['team_name']); ?>
                    <?php if (!empty($contract['team_type_name'])): ?>
                        <br><small style="color: var(--text-light);"><?php echo htmlspecialchars($contract['team_type_name']); ?></small>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($contract['procurement_route']): ?>
                <div>
                    <strong>Procurement Route:</strong><br>
                    <?php echo htmlspecialchars($contract['procurement_route']); ?>
                </div>
            <?php endif; ?>
            <?php if ($contract['tender_status']): ?>
                <div>
                    <strong>Tender Status:</strong><br>
                    <?php echo htmlspecialchars($contract['tender_status']); ?>
                </div>
            <?php endif; ?>
            <div>
                <strong>Start Date:</strong><br>
                <?php echo date(DATE_FORMAT, strtotime($contract['start_date'])); ?>
            </div>
            <?php if ($contract['end_date']): ?>
                <div>
                    <strong>End Date:</strong><br>
                    <?php echo date(DATE_FORMAT, strtotime($contract['end_date'])); ?>
                </div>
            <?php endif; ?>
            <div>
                <strong>Status:</strong><br>
                <?php echo ucfirst($contract['status']); ?>
            </div>
        </div>
        
        <?php if ($contract['description']): ?>
            <div style="margin-top: 1.5rem;">
                <strong>Description:</strong>
                <p><?php echo nl2br(htmlspecialchars($contract['description'])); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if ($contract['stipulations']): ?>
            <div style="margin-top: 1.5rem; padding: 1rem; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 0.375rem;">
                <strong style="display: block; margin-bottom: 0.5rem;">Contract Stipulations & Requirements:</strong>
                <p style="margin: 0; white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($contract['stipulations'])); ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- Payment Tracking Section -->
    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid var(--border-color);">
        <h3 style="margin-bottom: 1rem;">Payment Tracking</h3>
        
        <?php if ($isAdmin): ?>
            <!-- Add Payment Form -->
            <div class="card" style="background: var(--bg-light); margin-bottom: 1.5rem;">
                <h4 style="margin-top: 0;">Add Payment</h4>
                <form method="POST" action="">
                    <?php echo CSRF::tokenField(); ?>
                    <input type="hidden" name="payment_action" value="add">
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div class="form-group">
                            <label for="payment_method_id">Payment Method *</label>
                            <select id="payment_method_id" name="payment_method_id" class="form-control" required>
                                <option value="">Select...</option>
                                <?php foreach ($paymentMethods as $method): ?>
                                    <option value="<?php echo $method['id']; ?>">
                                        <?php echo htmlspecialchars($method['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_frequency">Payment Frequency</label>
                            <select id="payment_frequency" name="payment_frequency" class="form-control">
                                <option value="">Select...</option>
                                <option value="Weekly">Weekly</option>
                                <option value="Fortnightly">Fortnightly</option>
                                <option value="Monthly">Monthly</option>
                                <option value="Quarterly">Quarterly</option>
                                <option value="Annually">Annually</option>
                                <option value="One-off">One-off</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="amount">Amount (£) *</label>
                            <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_date">Payment Date</label>
                            <input type="date" id="payment_date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-top: 1rem;">
                        <label for="payment_description">Description (Optional)</label>
                        <textarea id="payment_description" name="payment_description" class="form-control" rows="2" placeholder="e.g., Weekly payment, Monthly invoice, etc."></textarea>
                    </div>
                    
                    <div class="form-group" style="margin-top: 1rem;">
                        <button type="submit" class="btn btn-primary">Add Payment</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
        
        <!-- Payment History -->
        <?php if (empty($payments)): ?>
            <p style="color: var(--text-light);">No payments recorded for this contract.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Payment Method</th>
                        <th>Frequency</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <?php if ($isAdmin): ?>
                            <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalAmount = 0;
                    foreach ($payments as $payment): 
                        $totalAmount += $payment['amount'];
                    ?>
                        <tr>
                            <td><?php echo $payment['payment_date'] ? date(DATE_FORMAT, strtotime($payment['payment_date'])) : '-'; ?></td>
                            <td><?php echo htmlspecialchars($payment['payment_method_name'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($payment['payment_frequency'] ?? '-'); ?></td>
                            <td>£<?php echo number_format($payment['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($payment['description'] ?? ''); ?></td>
                            <?php if ($isAdmin): ?>
                                <td>
                                    <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this payment?');">
                                        <?php echo CSRF::tokenField(); ?>
                                        <input type="hidden" name="payment_action" value="delete">
                                        <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                        <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;">Delete</button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="font-weight: bold; background: var(--bg-light);">
                        <td colspan="3"><strong>Total</strong></td>
                        <td><strong>£<?php echo number_format($totalAmount, 2); ?></strong></td>
                        <td colspan="<?php echo $isAdmin ? '2' : '1'; ?>"></td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleBulkFields() {
    const checkbox = document.getElementById('is_single_person');
    const bulkFields = document.getElementById('bulkFields');
    const singlePersonField = document.getElementById('singlePersonField');
    
    if (checkbox && bulkFields && singlePersonField) {
        if (checkbox.checked) {
            bulkFields.style.display = 'none';
            singlePersonField.style.display = 'block';
        } else {
            bulkFields.style.display = 'block';
            singlePersonField.style.display = 'none';
        }
    }
}
</script>

<script>
function toggleBulkFields() {
    const checkbox = document.getElementById('is_single_person');
    const bulkFields = document.getElementById('bulkFields');
    const singlePersonField = document.getElementById('singlePersonField');
    
    if (checkbox && bulkFields && singlePersonField) {
        if (checkbox.checked) {
            bulkFields.style.display = 'none';
            singlePersonField.style.display = 'block';
        } else {
            bulkFields.style.display = 'block';
            singlePersonField.style.display = 'none';
        }
    }
}
</script>

<?php include INCLUDES_PATH . '/footer.php'; ?>
