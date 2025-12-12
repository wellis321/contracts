<?php
/**
 * Tender Opportunities Page
 * View and manage available tender opportunities
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
$organisationId = Auth::getOrganisationId();
$isAdmin = RBAC::isAdmin();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create') {
            try {
                TenderOpportunity::create([
                    'local_authority_id' => $_POST['local_authority_id'],
                    'contract_type_id' => !empty($_POST['contract_type_id']) ? $_POST['contract_type_id'] : null,
                    'title' => trim($_POST['title']),
                    'description' => trim($_POST['description'] ?? ''),
                    'tender_reference' => trim($_POST['tender_reference'] ?? ''),
                    'source' => $_POST['source'] ?? 'manual',
                    'source_url' => trim($_POST['source_url'] ?? ''),
                    'published_date' => !empty($_POST['published_date']) ? $_POST['published_date'] : null,
                    'submission_deadline' => $_POST['submission_deadline'],
                    'clarification_deadline' => !empty($_POST['clarification_deadline']) ? $_POST['clarification_deadline'] : null,
                    'award_date_expected' => !empty($_POST['award_date_expected']) ? $_POST['award_date_expected'] : null,
                    'estimated_value' => !empty($_POST['estimated_value']) ? $_POST['estimated_value'] : null,
                    'contract_duration_months' => !empty($_POST['contract_duration_months']) ? intval($_POST['contract_duration_months']) : null,
                    'number_of_people' => !empty($_POST['number_of_people']) ? intval($_POST['number_of_people']) : null,
                    'geographic_coverage' => trim($_POST['geographic_coverage'] ?? ''),
                    'status' => $_POST['status'] ?? 'open',
                    'interest_level' => $_POST['interest_level'] ?? null,
                    'notes' => trim($_POST['notes'] ?? '')
                ]);
                $success = 'Tender opportunity added successfully.';
            } catch (Exception $e) {
                $error = 'Error creating opportunity: ' . $e->getMessage();
            }
        } elseif ($action === 'update') {
            try {
                TenderOpportunity::update($_POST['id'], [
                    'status' => $_POST['status'],
                    'interest_level' => $_POST['interest_level'] ?? null,
                    'notes' => trim($_POST['notes'] ?? '')
                ]);
                $success = 'Opportunity updated successfully.';
            } catch (Exception $e) {
                $error = 'Error updating opportunity: ' . $e->getMessage();
            }
        } elseif ($action === 'delete') {
            try {
                TenderOpportunity::delete($_POST['id']);
                $success = 'Opportunity deleted successfully.';
            } catch (Exception $e) {
                $error = 'Error deleting opportunity: ' . $e->getMessage();
            }
        } elseif ($action === 'import_from_url') {
            try {
                $url = trim($_POST['import_url'] ?? '');
                if (empty($url)) {
                    $error = 'Please provide a URL.';
                } else {
                    // Import from URL
                    $importedData = TenderImporter::importFromPCS($url);
                    
                    // Store in session to pre-fill form
                    $_SESSION['imported_opportunity_data'] = $importedData;
                    $_SESSION['imported_opportunity_url'] = $url;
                    
                    // Redirect to show import results and allow editing
                    header('Location: ' . url('tender-opportunities.php?imported=1'));
                    exit;
                }
            } catch (Exception $e) {
                $error = 'Error importing opportunity: ' . $e->getMessage() . 
                        ' You can still add it manually using the form below.';
            }
        } elseif ($action === 'save_imported') {
            try {
                // Get imported data from session
                $importedData = $_SESSION['imported_opportunity_data'] ?? [];
                
                // Merge with form data (form data takes precedence)
                $data = array_merge($importedData, [
                    'local_authority_id' => $_POST['local_authority_id'],
                    'contract_type_id' => !empty($_POST['contract_type_id']) ? $_POST['contract_type_id'] : null,
                    'title' => trim($_POST['title']),
                    'description' => trim($_POST['description'] ?? ''),
                    'tender_reference' => trim($_POST['tender_reference'] ?? ''),
                    'source' => $_POST['source'] ?? 'public_contracts_scotland',
                    'source_url' => $_POST['source_url'] ?? $importedData['source_url'] ?? '',
                    'published_date' => !empty($_POST['published_date']) ? $_POST['published_date'] : null,
                    'submission_deadline' => $_POST['submission_deadline'],
                    'estimated_value' => !empty($_POST['estimated_value']) ? $_POST['estimated_value'] : null,
                    'status' => $_POST['status'] ?? 'open',
                    'interest_level' => $_POST['interest_level'] ?? null,
                    'notes' => trim($_POST['notes'] ?? '')
                ]);
                
                TenderOpportunity::create($data);
                
                // Clear session
                unset($_SESSION['imported_opportunity_data']);
                unset($_SESSION['imported_opportunity_url']);
                
                $success = 'Tender opportunity saved successfully.';
            } catch (Exception $e) {
                $error = 'Error saving opportunity: ' . $e->getMessage();
            }
        }
    }
}

// Get filters
$filters = [
    'status' => $_GET['status'] ?? '',
    'local_authority_id' => $_GET['local_authority_id'] ?? '',
    'contract_type_id' => $_GET['contract_type_id'] ?? '',
    'source' => $_GET['source'] ?? '',
    'upcoming_only' => isset($_GET['upcoming_only']),
    'search' => $_GET['search'] ?? ''
];

// Get tender opportunities
$opportunities = TenderOpportunity::findAll(array_filter($filters));

// Get local authorities and contract types for filters
$db = getDbConnection();
$stmt = $db->query("SELECT * FROM local_authorities ORDER BY name");
$localAuthorities = $stmt->fetchAll();
$contractTypes = ContractType::findByOrganisation($organisationId);

// Check if we have imported data to show
$importedData = null;
$importedUrl = null;
if (isset($_GET['imported']) && isset($_SESSION['imported_opportunity_data'])) {
    $importedData = $_SESSION['imported_opportunity_data'];
    $importedUrl = $_SESSION['imported_opportunity_url'] ?? '';
}

$pageTitle = 'Tender Opportunities';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2>Tender Opportunities</h2>
                <p>Track and manage available tender opportunities</p>
            </div>
            <?php if ($isAdmin): ?>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <button onclick="document.getElementById('importForm').style.display='block'" class="btn btn-primary">
                        <i class="fas fa-download"></i> Import from URL
                    </button>
                    <button onclick="document.getElementById('createForm').style.display='block'" class="btn btn-secondary">
                        <i class="fa-solid fa-plus"></i> Add Manually
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Import Form -->
    <?php if ($isAdmin): ?>
    <div id="importForm" style="display: <?php echo $importedData ? 'block' : 'none'; ?>; margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem;">
        <h3 style="margin-top: 0;">
            <?php if ($importedData): ?>
                Review Imported Opportunity
            <?php else: ?>
                Import Tender Opportunity from URL
            <?php endif; ?>
        </h3>
        
        <?php if ($importedData): ?>
            <div class="alert alert-success" style="margin-bottom: 1rem;">
                <strong><i class="fas fa-check-circle"></i> Data extracted from URL!</strong> 
                Please review and complete the fields below, then save.
            </div>
            <form method="POST" action="">
                <?php echo CSRF::tokenField(); ?>
                <input type="hidden" name="action" value="save_imported">
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="import_title">Title <span style="color: var(--danger-color);">*</span></label>
                        <input type="text" id="import_title" name="title" class="form-control" required
                               value="<?php echo htmlspecialchars($importedData['title'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="import_local_authority_id">Local Authority <span style="color: var(--danger-color);">*</span></label>
                        <select id="import_local_authority_id" name="local_authority_id" class="form-control" required>
                            <option value="">Select...</option>
                            <?php foreach ($localAuthorities as $la): ?>
                                <option value="<?php echo $la['id']; ?>"
                                        <?php echo ($importedData['local_authority_id'] ?? '') == $la['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($la['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="import_tender_reference">Tender Reference</label>
                        <input type="text" id="import_tender_reference" name="tender_reference" class="form-control"
                               value="<?php echo htmlspecialchars($importedData['tender_reference'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="import_submission_deadline">Submission Deadline <span style="color: var(--danger-color);">*</span></label>
                        <input type="date" id="import_submission_deadline" name="submission_deadline" class="form-control" required
                               value="<?php echo htmlspecialchars($importedData['submission_deadline'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="import_source_url">Source URL</label>
                        <input type="url" id="import_source_url" name="source_url" class="form-control"
                               value="<?php echo htmlspecialchars($importedUrl ?? $importedData['source_url'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="import_estimated_value">Estimated Value</label>
                        <input type="number" step="0.01" id="import_estimated_value" name="estimated_value" class="form-control"
                               value="<?php echo htmlspecialchars($importedData['estimated_value'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="import_description">Description</label>
                    <textarea id="import_description" name="description" class="form-control" rows="3"><?php echo htmlspecialchars($importedData['description'] ?? ''); ?></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Save Opportunity</button>
                    <button type="button" class="btn btn-secondary" onclick="cancelImport()">Cancel</button>
                </div>
            </form>
        <?php else: ?>
            <p style="color: var(--text-light); margin-bottom: 1rem; font-size: 0.9rem;">
                Paste a URL from Public Contracts Scotland or another tender portal to automatically import the opportunity details.
            </p>
            <form method="POST" action="">
                <?php echo CSRF::tokenField(); ?>
                <input type="hidden" name="action" value="import_from_url">
                
                <div class="form-group">
                    <label for="import_url">Tender Notice URL <span style="color: var(--danger-color);">*</span></label>
                    <input type="url" id="import_url" name="import_url" class="form-control" required
                           placeholder="https://www.publiccontractsscotland.gov.uk/...">
                    <small style="color: var(--text-light);">
                        Paste the full URL of the tender notice page. The system will attempt to extract key information automatically.
                    </small>
                </div>
                
                <div style="margin-top: 1rem; padding: 1rem; background: #e0f2fe; border-left: 4px solid #0ea5e9; border-radius: 0.25rem;">
                    <strong><i class="fas fa-info-circle"></i> Tips:</strong>
                    <ul style="margin: 0.5rem 0 0 1.5rem; padding: 0;">
                        <li>Works best with Public Contracts Scotland notice pages</li>
                        <li>You can review and edit the imported data before saving</li>
                        <li>Some fields may need manual completion if not found on the page</li>
                        <li><strong>Example URL format:</strong> <code style="font-size: 0.85rem;">https://www.publiccontractsscotland.gov.uk/search/show/search_view.aspx?ID=...</code></li>
                    </ul>
                </div>
                
                <div style="margin-top: 1rem; padding: 1rem; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 0.25rem;">
                    <strong><i class="fas fa-lightbulb"></i> How to find a URL:</strong>
                    <ol style="margin: 0.5rem 0 0 1.5rem; padding: 0;">
                        <li>Go to <a href="https://www.publiccontractsscotland.gov.uk" target="_blank" style="color: #d97706; text-decoration: underline;">Public Contracts Scotland</a></li>
                        <li>Search for "social care" or "care services"</li>
                        <li>Click on a tender notice to view details</li>
                        <li>Copy the full URL from your browser's address bar</li>
                        <li>Paste it into the field above</li>
                    </ol>
                </div>
                
                <div class="form-group" style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Import Opportunity</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('importForm').style.display='none'">Cancel</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <!-- Create Form -->
    <div id="createForm" style="display: none; margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem;">
        <h3 style="margin-top: 0;">Add New Tender Opportunity</h3>
        <form method="POST" action="">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="create">
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label for="title">Title <span style="color: var(--danger-color);">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="local_authority_id">Local Authority <span style="color: var(--danger-color);">*</span></label>
                    <select id="local_authority_id" name="local_authority_id" class="form-control" required>
                        <option value="">Select...</option>
                        <?php foreach ($localAuthorities as $la): ?>
                            <option value="<?php echo $la['id']; ?>"><?php echo htmlspecialchars($la['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="contract_type_id">Contract Type</label>
                    <select id="contract_type_id" name="contract_type_id" class="form-control">
                        <option value="">Select...</option>
                        <?php foreach ($contractTypes as $type): ?>
                            <option value="<?php echo $type['id']; ?>"><?php echo htmlspecialchars($type['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="tender_reference">Tender Reference</label>
                    <input type="text" id="tender_reference" name="tender_reference" class="form-control" 
                           placeholder="e.g., PCS-2024-12345">
                </div>
                
                <div class="form-group">
                    <label for="source">Source</label>
                    <select id="source" name="source" class="form-control">
                        <option value="manual">Manual Entry</option>
                        <option value="public_contracts_scotland">Public Contracts Scotland</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="source_url">Source URL</label>
                    <input type="url" id="source_url" name="source_url" class="form-control" 
                           placeholder="https://...">
                </div>
                
                <div class="form-group">
                    <label for="submission_deadline">Submission Deadline <span style="color: var(--danger-color);">*</span></label>
                    <input type="date" id="submission_deadline" name="submission_deadline" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="published_date">Published Date</label>
                    <input type="date" id="published_date" name="published_date" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="estimated_value">Estimated Value</label>
                    <input type="number" id="estimated_value" name="estimated_value" class="form-control" 
                           step="0.01" placeholder="0.00">
                </div>
                
                <div class="form-group">
                    <label for="interest_level">Interest Level</label>
                    <select id="interest_level" name="interest_level" class="form-control">
                        <option value="">Not Set</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label for="notes">Internal Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="2" 
                          placeholder="Internal notes about this opportunity"></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Add Opportunity</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('createForm').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
    <?php endif; ?>
    
    <!-- Filters -->
    <form method="GET" action="" style="margin-bottom: 1.5rem; padding: 1rem; background: var(--bg-light); border-radius: 0.5rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" class="form-control" 
                       value="<?php echo htmlspecialchars($filters['search']); ?>" 
                       placeholder="Search opportunities...">
            </div>
            
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="open" <?php echo $filters['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                    <option value="interested" <?php echo $filters['status'] === 'interested' ? 'selected' : ''; ?>>Interested</option>
                    <option value="applied" <?php echo $filters['status'] === 'applied' ? 'selected' : ''; ?>>Applied</option>
                    <option value="closed" <?php echo $filters['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="local_authority_id">Local Authority</label>
                <select id="local_authority_id" name="local_authority_id" class="form-control">
                    <option value="">All Authorities</option>
                    <?php foreach ($localAuthorities as $la): ?>
                        <option value="<?php echo $la['id']; ?>" 
                                <?php echo $filters['local_authority_id'] == $la['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($la['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="source">Source</label>
                <select id="source" name="source" class="form-control">
                    <option value="">All Sources</option>
                    <option value="manual" <?php echo $filters['source'] === 'manual' ? 'selected' : ''; ?>>Manual</option>
                    <option value="public_contracts_scotland" <?php echo $filters['source'] === 'public_contracts_scotland' ? 'selected' : ''; ?>>Public Contracts Scotland</option>
                    <option value="other" <?php echo $filters['source'] === 'other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem; align-items: center;">
            <label style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="upcoming_only" value="1" 
                       <?php echo $filters['upcoming_only'] ? 'checked' : ''; ?>>
                Upcoming deadlines only
            </label>
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="<?php echo url('tender-opportunities.php'); ?>" class="btn btn-secondary">Clear</a>
            <a href="<?php echo url('tender-monitoring.php'); ?>" class="btn btn-secondary">
                <i class="fas fa-bell"></i> Set Up Monitoring
            </a>
            <a href="<?php echo htmlspecialchars(TenderImporter::getPCSSearchUrl('social care')); ?>" 
               target="_blank" class="btn btn-secondary" style="margin-left: 0.5rem;">
                <i class="fas fa-external-link-alt"></i> Search Public Contracts Scotland
            </a>
        </div>
    </form>
    
    <!-- Opportunities List -->
    <?php if (empty($opportunities)): ?>
        <div class="alert alert-info">
            <p>No tender opportunities found matching your criteria.</p>
            <?php if ($isAdmin): ?>
                <p>Add a new opportunity using the button above.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="table" style="min-width: 1000px;">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Local Authority</th>
                        <th>Contract Type</th>
                        <th>Deadline</th>
                        <th>Estimated Value</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Interest</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($opportunities as $opp): 
                        $deadline = strtotime($opp['submission_deadline']);
                        $daysRemaining = floor(($deadline - time()) / (60 * 60 * 24));
                        $isUrgent = $daysRemaining < 7 && $daysRemaining >= 0;
                        $isPast = $daysRemaining < 0;
                    ?>
                        <tr style="<?php echo $isUrgent ? 'background-color: #fef3c7;' : ($isPast ? 'opacity: 0.6;' : ''); ?>">
                            <td>
                                <strong><?php echo htmlspecialchars($opp['title']); ?></strong>
                                <?php if ($opp['tender_reference']): ?>
                                    <br><small style="color: var(--text-light);">Ref: <?php echo htmlspecialchars($opp['tender_reference']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($opp['local_authority_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($opp['contract_type_name'] ?? '-'); ?></td>
                            <td>
                                <?php echo date(DATE_FORMAT, $deadline); ?>
                                <br>
                                <small style="color: <?php echo $isPast ? 'var(--danger-color)' : ($isUrgent ? 'var(--warning-color)' : 'var(--text-light)'); ?>;">
                                    <?php if ($isPast): ?>
                                        Past deadline
                                    <?php elseif ($isUrgent): ?>
                                        <?php echo $daysRemaining; ?> days left
                                    <?php else: ?>
                                        <?php echo $daysRemaining; ?> days remaining
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td>
                                <?php if ($opp['estimated_value']): ?>
                                    £<?php echo number_format($opp['estimated_value'], 2); ?>
                                <?php else: ?>
                                    <span style="color: var(--text-light);">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($opp['source_url']): ?>
                                    <a href="<?php echo htmlspecialchars($opp['source_url']); ?>" target="_blank" 
                                       style="color: var(--primary-color); text-decoration: none;">
                                        <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $opp['source']))); ?>
                                        <i class="fas fa-external-link-alt" style="font-size: 0.75rem; margin-left: 0.25rem;"></i>
                                    </a>
                                <?php else: ?>
                                    <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $opp['source']))); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo $opp['status'] === 'applied' ? 'success' : 
                                        ($opp['status'] === 'interested' ? 'warning' : 
                                        ($opp['status'] === 'closed' ? 'secondary' : 'primary')); 
                                ?>">
                                    <?php echo ucwords($opp['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($opp['interest_level']): ?>
                                    <span class="badge badge-<?php 
                                        echo $opp['interest_level'] === 'high' ? 'danger' : 
                                            ($opp['interest_level'] === 'medium' ? 'warning' : 'secondary'); 
                                    ?>">
                                        <?php echo ucfirst($opp['interest_level']); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--text-light);">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <?php if ($opp['status'] !== 'applied'): ?>
                                        <a href="<?php echo url('tender-application.php?opportunity_id=' . $opp['id']); ?>" 
                                           class="btn btn-sm btn-primary" title="Create Application">
                                            <i class="fas fa-file-alt"></i> Apply
                                        </a>
                                    <?php elseif ($opp['tender_application_id']): ?>
                                        <a href="<?php echo url('tender-application.php?id=' . $opp['tender_application_id']); ?>" 
                                           class="btn btn-sm btn-secondary" title="View Application">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($isAdmin): ?>
                                        <button onclick="showEditModal(<?php echo htmlspecialchars(json_encode($opp)); ?>)" 
                                                class="btn btn-sm btn-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Edit Modal -->
<?php if ($isAdmin): ?>
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 0.5rem; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <h3 style="margin-top: 0;">Edit Opportunity</h3>
        <form method="POST" action="" id="editForm">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="form-group">
                <label for="edit_status">Status</label>
                <select id="edit_status" name="status" class="form-control" required>
                    <option value="open">Open</option>
                    <option value="interested">Interested</option>
                    <option value="applied">Applied</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="edit_interest_level">Interest Level</label>
                <select id="edit_interest_level" name="interest_level" class="form-control">
                    <option value="">Not Set</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="edit_notes">Notes</label>
                <textarea id="edit_notes" name="notes" class="form-control" rows="3"></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showEditModal(opp) {
    document.getElementById('edit_id').value = opp.id;
    document.getElementById('edit_status').value = opp.status;
    document.getElementById('edit_interest_level').value = opp.interest_level || '';
    document.getElementById('edit_notes').value = opp.notes || '';
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function cancelImport() {
    if (confirm('Cancel import? Any unsaved data will be lost.')) {
        window.location.href = '<?php echo url('tender-opportunities.php'); ?>';
    }
}

// Close modal on outside click
document.getElementById('editModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});
</script>
<?php endif; ?>

<?php include INCLUDES_PATH . '/footer.php'; ?>

