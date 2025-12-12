<?php
/**
 * Contracts Management Page
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
$organisationId = Auth::getOrganisationId();
$isAdmin = RBAC::isAdmin();
$error = '';
$success = '';

// Get filter
$statusFilter = $_GET['status'] ?? null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && RBAC::canManageContracts()) {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create' || $action === 'update') {
            $data = [
                'organisation_id' => $organisationId,
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
                'status' => $_POST['status'] ?? 'active',
                'created_by' => Auth::getUserId()
            ];
            
            // Validation
            if (empty($data['title']) || empty($data['contract_type_id']) || empty($data['local_authority_id']) || empty($data['start_date'])) {
                $error = 'Please fill in all required fields.';
            } else {
                try {
                    if ($action === 'create') {
                        Contract::create($data);
                        $success = 'Contract created successfully.';
                    } else {
                        $id = $_POST['id'] ?? 0;
                        if (!RBAC::canAccessContract($id)) {
                            $error = 'Unauthorized access.';
                        } else {
                            Contract::update($id, $data);
                            $success = 'Contract updated successfully.';
                        }
                    }
                } catch (Exception $e) {
                    $error = 'Error saving contract: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'delete' && $isAdmin) {
            $id = $_POST['id'] ?? 0;
            if (!RBAC::canAccessContract($id)) {
                $error = 'Unauthorized access.';
            } else {
                Contract::delete($id);
                $success = 'Contract deleted successfully.';
            }
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
// Get accessible teams for dropdown (only show teams user can assign to)
$assignableTeams = $canAccessAllTeams ? $teams : array_filter($teams, function($team) use ($userTeams) {
    $userTeamIds = array_column($userTeams, 'id');
    return in_array($team['id'], $userTeamIds) || Team::userHasAccessToTeam(Auth::getUserId(), $team['id']);
});

// Get people for single person contracts
$people = Person::findByOrganisation($organisationId);

// Get user's accessible team IDs (for filtering contracts)
$accessibleTeamIds = RBAC::getAccessibleTeamIds();

// Get contracts (filtered by team access)
$contracts = Contract::findByOrganisation($organisationId, $statusFilter, $accessibleTeamIds);

// Deduplicate contracts
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

$contracts = $uniqueContracts;

// Helper function to extract person name from title (remove contract type)
function extractPersonName($title) {
    $patterns = [
        '/\s*-\s*(Enhanced\s+Support\s+Package|Support\s+Hours|Personal\s+Care|Complex\s+Support|Supported\s+Living|Waking\/Active\s+Hours|Sleepover\s+Hours|Daytime\s+Hours|Night\s+Hours|Respite\s+Care|Emergency\s+Support|Community\s+Support|Bulk\s+Support\s+Contract).*$/i',
        '/\s*-\s*.*$/i', // Fallback: remove everything after " - "
    ];
    
    foreach ($patterns as $pattern) {
        $title = preg_replace($pattern, '', $title);
    }
    
    return trim($title);
}

// Helper function to abbreviate contract type names
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
usort($contracts, function($a, $b) use ($sortField, $sortOrder) {
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
        case 'local_authority':
            $result = strcasecmp($a['local_authority_name'] ?? '', $b['local_authority_name'] ?? '');
            break;
        case 'start_date':
            $aDate = $a['start_date'] ? strtotime($a['start_date']) : 0;
            $bDate = $b['start_date'] ? strtotime($b['start_date']) : 0;
            $result = $aDate <=> $bDate;
            break;
        case 'end_date':
            $aDate = $a['end_date'] ? strtotime($a['end_date']) : PHP_INT_MAX;
            $bDate = $b['end_date'] ? strtotime($b['end_date']) : PHP_INT_MAX;
            $result = $aDate <=> $bDate;
            break;
        case 'value':
            $aValue = $a['total_amount'] ?? 0;
            $bValue = $b['total_amount'] ?? 0;
            $result = $aValue <=> $bValue;
            break;
        case 'status':
            $result = strcasecmp($a['status'] ?? '', $b['status'] ?? '');
            break;
        default:
            $result = 0;
    }
    
    return $sortOrder === 'desc' ? -$result : $result;
});

// Helper functions for sorting
function getSortUrl($field) {
    $currentSort = $_GET['sort'] ?? 'title';
    $currentOrder = $_GET['order'] ?? 'asc';
    
    if ($currentSort === $field) {
        // Toggle order if clicking same field
        $newOrder = $currentOrder === 'asc' ? 'desc' : 'asc';
    } else {
        // Default to ascending for new field
        $newOrder = 'asc';
    }
    
    $params = $_GET;
    $params['sort'] = $field;
    $params['order'] = $newOrder;
    
    return url('contracts.php?' . http_build_query($params));
}

function getSortIcon($field) {
    $currentSort = $_GET['sort'] ?? 'title';
    $currentOrder = $_GET['order'] ?? 'asc';
    
    if ($currentSort !== $field) {
        return 'fa-sort';
    }
    
    return $currentOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
}

$pageTitle = 'Contracts';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-between">
            <div>
                <h2>Contracts</h2>
                <p>Manage your organisation's contracts</p>
            </div>
            <?php if (RBAC::canManageContracts()): ?>
                <button onclick="document.getElementById('createForm').style.display='block'" class="btn btn-primary">
                    Add Contract
                </button>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error']) && $_GET['error'] === 'access_denied'): ?>
        <div class="alert alert-error">You do not have permission to access that contract.</div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Filters -->
    <div style="margin-bottom: 1.5rem;">
        <a href="?status=" class="btn <?php echo $statusFilter === null ? 'btn-primary' : 'btn-secondary'; ?>">All</a>
        <a href="?status=active" class="btn <?php echo $statusFilter === 'active' ? 'btn-primary' : 'btn-secondary'; ?>">Active</a>
        <a href="?status=completed" class="btn <?php echo $statusFilter === 'completed' ? 'btn-primary' : 'btn-secondary'; ?>">Completed</a>
        <a href="?status=inactive" class="btn <?php echo $statusFilter === 'inactive' ? 'btn-primary' : 'btn-secondary'; ?>">Inactive</a>
    </div>
    
    <!-- Create Form -->
    <?php if (RBAC::canManageContracts()): ?>
    <div id="createForm" style="display: none; margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem;">
        <h3>Create New Contract</h3>
        <form method="POST" action="">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="create">
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="contract_number">Contract Number</label>
                    <input type="text" id="contract_number" name="contract_number" class="form-control">
                </div>
            </div>
            
            <div class="form-group">
                <label for="contract_type_id">Contract Type *</label>
                <select id="contract_type_id" name="contract_type_id" class="form-control" required>
                    <option value="">Select...</option>
                    <?php foreach ($contractTypes as $type): ?>
                        <option value="<?php echo $type['id']; ?>"><?php echo htmlspecialchars($type['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="local_authority_id">Local Authority *</label>
                <select id="local_authority_id" name="local_authority_id" class="form-control" required>
                    <option value="">Select...</option>
                    <?php foreach ($localAuthorities as $la): ?>
                        <option value="<?php echo $la['id']; ?>"><?php echo htmlspecialchars($la['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <?php if (!empty($assignableTeams)): ?>
                <div class="form-group">
                    <label for="team_id">Team (Optional)</label>
                    <select id="team_id" name="team_id" class="form-control">
                        <option value="">No Team Assigned</option>
                        <?php 
                        // Group teams by type for better display
                        $teamsByType = [];
                        foreach ($assignableTeams as $team) {
                            $typeName = $team['team_type_name'] ?? 'No Type';
                            if (!isset($teamsByType[$typeName])) {
                                $teamsByType[$typeName] = [];
                            }
                            $teamsByType[$typeName][] = $team;
                        }
                        // Sort by team type display order
                        ksort($teamsByType);
                        foreach ($teamsByType as $typeName => $teamsOfType):
                        ?>
                            <optgroup label="<?php echo htmlspecialchars($typeName); ?>">
                                <?php foreach ($teamsOfType as $team): ?>
                                    <option value="<?php echo $team['id']; ?>">
                                        <?php echo htmlspecialchars(Team::getHierarchyPath($team['id'])); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: var(--text-light);">Assign this contract to a team. Team managers can only manage contracts assigned to their team.</small>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3"></textarea>
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
                                <option value="<?php echo htmlspecialchars($route['name']); ?>"><?php echo htmlspecialchars($route['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color: var(--text-light);">How was this contract awarded?</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="tender_status">Tender Status</label>
                        <select id="tender_status" name="tender_status" class="form-control">
                            <option value="">Select...</option>
                            <?php foreach ($tenderStatuses as $status): ?>
                                <option value="<?php echo htmlspecialchars($status['name']); ?>"><?php echo htmlspecialchars($status['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color: var(--text-light);">Current stage in contract lifecycle</small>
                    </div>
                </div>
                
                <div class="form-group" style="margin-top: 1rem;">
                    <label for="framework_agreement_id">Framework Agreement ID</label>
                    <input type="text" id="framework_agreement_id" name="framework_agreement_id" class="form-control" placeholder="e.g., Scotland Excel 2024-2030">
                    <small style="color: var(--text-light);">If awarded from a framework, enter the framework reference</small>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-top: 1rem;">
                    <div class="form-group">
                        <label for="quality_price_weighting">Quality:Price Weighting</label>
                        <input type="text" id="quality_price_weighting" name="quality_price_weighting" class="form-control" placeholder="e.g., 70:30">
                        <small style="color: var(--text-light);">How was the contract evaluated?</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="contract_duration_months">Contract Duration (Months)</label>
                        <input type="number" id="contract_duration_months" name="contract_duration_months" class="form-control" min="1">
                    </div>
                </div>
                
                <div class="form-group" style="margin-top: 1rem;">
                    <label for="evaluation_criteria">Evaluation Criteria</label>
                    <textarea id="evaluation_criteria" name="evaluation_criteria" class="form-control" rows="2" placeholder="Key criteria used in evaluation (quality, price, social value, etc.)"></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-top: 1rem;">
                    <div class="form-group">
                        <label for="extension_options">Extension Options</label>
                        <input type="text" id="extension_options" name="extension_options" class="form-control" placeholder="e.g., 2 x 12 months">
                    </div>
                    
                    <div class="form-group">
                        <label for="price_review_mechanism">Price Review Mechanism</label>
                        <input type="text" id="price_review_mechanism" name="price_review_mechanism" class="form-control" placeholder="e.g., Annual review, CPI linked">
                    </div>
                    
                    <div class="form-group">
                        <label for="inflation_indexation">Inflation Indexation</label>
                        <input type="text" id="inflation_indexation" name="inflation_indexation" class="form-control" placeholder="e.g., CPI, RPI, Fixed %">
                    </div>
                </div>
                
                <div style="margin-top: 1rem;">
                    <label>
                        <input type="checkbox" id="fair_work_compliance" name="fair_work_compliance" value="1">
                        Fair Work Compliance Required
                    </label>
                </div>
                
                <div class="form-group" style="margin-top: 1rem;">
                    <label for="community_benefits">Community Benefits</label>
                    <textarea id="community_benefits" name="community_benefits" class="form-control" rows="2" placeholder="Community benefits committed (employment, training, local sourcing, etc.)"></textarea>
                </div>
            </div>
            
            <!-- Single Person Contract -->
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid var(--border-color);">
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="is_single_person" name="is_single_person" value="1" checked onchange="toggleBulkFields()">
                        Single <?php echo ucfirst(htmlspecialchars(getPersonTerm(true))); ?> Contract
                    </label>
                </div>
                
                <div id="singlePersonField" style="margin-top: 1rem;">
                    <div class="form-group">
                        <label for="person_id"><?php echo ucfirst(htmlspecialchars(getPersonTerm(true))); ?> (Optional)</label>
                        <select id="person_id" name="person_id" class="form-control">
                            <option value="">Select <?php echo htmlspecialchars(getPersonTerm(true)); ?> or leave blank</option>
                            <?php foreach ($people as $person): ?>
                                <option value="<?php echo $person['id']; ?>">
                                    <?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color: var(--text-light);">Link this contract to a <?php echo htmlspecialchars(getPersonTerm(true)); ?> to track them across local authorities</small>
                    </div>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1.5rem;">
                <div class="form-group">
                    <label for="start_date">Start Date *</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control">
                </div>
            </div>
            
            <div id="bulkFields" style="display: none;">
                <div class="form-group">
                    <label for="number_of_people">Number of <?php echo ucfirst(htmlspecialchars(getPersonTerm(false))); ?></label>
                    <input type="number" id="number_of_people" name="number_of_people" class="form-control" min="1" value="1">
                </div>
                
                <div class="form-group">
                    <label for="number_of_staff">Number of Staff</label>
                    <input type="number" id="number_of_staff" name="number_of_staff" class="form-control" min="1">
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="daytime_hours">Daytime Hours</label>
                        <input type="number" id="daytime_hours" name="daytime_hours" class="form-control" step="0.01">
                    </div>
                    
                    <div class="form-group">
                        <label for="sleepover_hours">Sleepover Hours</label>
                        <input type="number" id="sleepover_hours" name="sleepover_hours" class="form-control" step="0.01">
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="total_amount">Total Amount</label>
                <input type="number" id="total_amount" name="total_amount" class="form-control" step="0.01">
            </div>
            
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('createForm').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
    <?php endif; ?>
    
    <!-- Contracts List -->
    <?php if (empty($contracts)): ?>
        <p>No contracts found. <?php if (RBAC::canManageContracts()): ?>Create your first contract above.<?php endif; ?></p>
    <?php else: ?>
        <?php
        // Build contract type legend
        $contractTypeLegend = [];
        foreach ($contracts as $contract) {
            $typeName = $contract['contract_type_name'] ?? 'Unknown';
            if ($typeName !== 'Unknown' && !isset($contractTypeLegend[$typeName])) {
                $contractTypeLegend[$typeName] = abbreviateContractType($typeName);
            }
        }
        ?>
        <?php if (!empty($contractTypeLegend)): ?>
            <div style="margin-bottom: 1rem; padding: 0.75rem; background: var(--bg-light); border-radius: 0.375rem; font-size: 0.9rem;">
                <strong>Contract Type Abbreviations:</strong>
                <?php foreach ($contractTypeLegend as $fullName => $abbr): ?>
                    <span style="margin-left: 1rem;">
                        <strong><?php echo htmlspecialchars($abbr); ?>:</strong> <?php echo htmlspecialchars($fullName); ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <style>
            .contract-active {
                background-color: rgba(16, 185, 129, 0.05);
            }
            .contract-inactive {
                background-color: rgba(107, 114, 128, 0.05);
            }
            .contract-active:hover {
                background-color: rgba(16, 185, 129, 0.15) !important;
            }
            .contract-inactive:hover {
                background-color: rgba(107, 114, 128, 0.15) !important;
            }
            .sortable-header {
                position: relative;
                cursor: pointer;
                user-select: none;
                padding-right: 1.5rem !important;
            }
            .sortable-header:hover {
                background-color: rgba(59, 130, 246, 0.1);
            }
            .sortable-header.active {
                color: var(--primary-color);
            }
            .sort-icon {
                position: absolute;
                right: 0.5rem;
                top: 50%;
                transform: translateY(-50%);
                font-size: 0.75rem;
                opacity: 0.5;
                transition: opacity 0.2s;
            }
            .sortable-header:hover .sort-icon,
            .sortable-header.active .sort-icon {
                opacity: 1;
                color: var(--primary-color);
            }
        </style>
        <div style="overflow-x: auto;">
            <table class="table" style="min-width: 800px;">
                <thead>
                    <tr>
                        <th class="sortable-header <?php echo $sortField === 'title' ? 'active' : ''; ?>" onclick="event.stopPropagation(); sortTable('<?php echo htmlspecialchars(getSortUrl('title')); ?>')">
                            Title
                            <i class="fas <?php echo getSortIcon('title'); ?> sort-icon"></i>
                        </th>
                        <th class="sortable-header <?php echo $sortField === 'contract_number' ? 'active' : ''; ?>" onclick="event.stopPropagation(); sortTable('<?php echo htmlspecialchars(getSortUrl('contract_number')); ?>')">
                            Contract Number
                            <i class="fas <?php echo getSortIcon('contract_number'); ?> sort-icon"></i>
                        </th>
                        <th class="sortable-header <?php echo $sortField === 'type' ? 'active' : ''; ?>" onclick="event.stopPropagation(); sortTable('<?php echo htmlspecialchars(getSortUrl('type')); ?>')">
                            Type
                            <i class="fas <?php echo getSortIcon('type'); ?> sort-icon"></i>
                        </th>
                        <th class="sortable-header <?php echo $sortField === 'local_authority' ? 'active' : ''; ?>" onclick="event.stopPropagation(); sortTable('<?php echo htmlspecialchars(getSortUrl('local_authority')); ?>')">
                            Local Authority
                            <i class="fas <?php echo getSortIcon('local_authority'); ?> sort-icon"></i>
                        </th>
                        <th class="sortable-header <?php echo $sortField === 'start_date' ? 'active' : ''; ?>" onclick="event.stopPropagation(); sortTable('<?php echo htmlspecialchars(getSortUrl('start_date')); ?>')">
                            Start Date
                            <i class="fas <?php echo getSortIcon('start_date'); ?> sort-icon"></i>
                        </th>
                        <th class="sortable-header <?php echo $sortField === 'end_date' ? 'active' : ''; ?>" onclick="event.stopPropagation(); sortTable('<?php echo htmlspecialchars(getSortUrl('end_date')); ?>')">
                            End Date
                            <i class="fas <?php echo getSortIcon('end_date'); ?> sort-icon"></i>
                        </th>
                        <th class="sortable-header <?php echo $sortField === 'value' ? 'active' : ''; ?>" onclick="event.stopPropagation(); sortTable('<?php echo htmlspecialchars(getSortUrl('value')); ?>')">
                            Value
                            <i class="fas <?php echo getSortIcon('value'); ?> sort-icon"></i>
                        </th>
                        <th class="sortable-header <?php echo $sortField === 'status' ? 'active' : ''; ?>" onclick="event.stopPropagation(); sortTable('<?php echo htmlspecialchars(getSortUrl('status')); ?>')">
                            Status
                            <i class="fas <?php echo getSortIcon('status'); ?> sort-icon"></i>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contracts as $contract): ?>
                        <?php 
                        $effectiveStatus = Contract::getEffectiveStatus($contract);
                        ?>
                        <tr class="contract-<?php echo $effectiveStatus === 'active' ? 'active' : 'inactive'; ?>" 
                            onclick="if (!event.target.closest('.sortable-header')) { window.location.href='<?php echo htmlspecialchars(url('contract-view.php?id=' . $contract['id'])); ?>'; }" 
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
                            <td><?php echo htmlspecialchars($contract['local_authority_name'] ?? '-'); ?></td>
                            <td><?php echo $contract['start_date'] ? date(DATE_FORMAT, strtotime($contract['start_date'])) : '-'; ?></td>
                            <td><?php echo $contract['end_date'] ? date(DATE_FORMAT, strtotime($contract['end_date'])) : '<span style="color: var(--text-light);">Ongoing</span>'; ?></td>
                            <td>£<?php echo number_format($contract['total_amount'] ?? 0, 2); ?></td>
                            <td>
                                <?php 
                                $effectiveStatus = Contract::getEffectiveStatus($contract);
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
        </div>
    <?php endif; ?>
</div>

<script>
function toggleBulkFields() {
    const checkbox = document.getElementById('is_single_person');
    const bulkFields = document.getElementById('bulkFields');
    bulkFields.style.display = checkbox.checked ? 'none' : 'block';
}

// Function to handle table sorting with scroll preservation
function sortTable(url) {
    // Store scroll position
    sessionStorage.setItem('scrollToContracts', 'true');
    window.location.href = url;
}

// Restore scroll position after sorting
document.addEventListener('DOMContentLoaded', function() {
    if (sessionStorage.getItem('scrollToContracts') === 'true') {
        sessionStorage.removeItem('scrollToContracts');
        // Scroll to contracts table
        setTimeout(function() {
            const table = document.querySelector('.table');
            if (table) {
                table.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 100);
    }
});
</script>

<?php include INCLUDES_PATH . '/footer.php'; ?>
