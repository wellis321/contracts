<?php
/**
 * Teams Import Page
 * Allows importing team structure from CSV or JSON files
 * Supports importing team types, teams, and user assignments
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
RBAC::requireAdmin();

$organisationId = Auth::getOrganisationId();
$error = '';
$success = '';
$previewData = null;
$importResults = null;

// Handle file upload and preview
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'preview') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } elseif (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select a file to upload.';
    } else {
        $file = $_FILES['import_file'];
        $fileType = $_POST['file_type'] ?? 'csv';
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Validate file type
        if ($fileType === 'csv' && $extension !== 'csv') {
            $error = 'Please upload a CSV file.';
        } elseif ($fileType === 'json' && $extension !== 'json') {
            $error = 'Please upload a JSON file.';
        } else {
            try {
                $content = file_get_contents($file['tmp_name']);
                
                if ($fileType === 'csv') {
                    $previewData = parseCsvImport($content);
                } else {
                    $previewData = parseJsonImport($content);
                }
                
                if (empty($previewData)) {
                    $error = 'No data found in file.';
                }
            } catch (Exception $e) {
                $error = 'Error parsing file: ' . $e->getMessage();
            }
        }
    }
}

// Handle import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $importData = json_decode($_POST['import_data'], true);
        if (!$importData) {
            $error = 'Invalid import data.';
        } else {
            try {
                $importResults = performImport($organisationId, $importData);
                $success = 'Import completed successfully!';
            } catch (Exception $e) {
                $error = 'Error during import: ' . $e->getMessage();
            }
        }
    }
}

/**
 * Parse CSV import file
 */
function parseCsvImport($content) {
    $lines = explode("\n", trim($content));
    if (empty($lines)) {
        return null;
    }
    
    $headers = str_getcsv(array_shift($lines));
    $data = [
        'team_types' => [],
        'teams' => [],
        'user_assignments' => []
    ];
    
    foreach ($lines as $lineNum => $line) {
        if (empty(trim($line))) continue;
        
        $row = str_getcsv($line);
        if (count($row) !== count($headers)) {
            throw new Exception("Row " . ($lineNum + 2) . " has incorrect number of columns");
        }
        
        $rowData = array_combine($headers, $row);
        
        // Determine row type based on content
        if (isset($rowData['type']) && $rowData['type'] === 'team_type') {
            $data['team_types'][] = [
                'name' => $rowData['name'] ?? '',
                'description' => $rowData['description'] ?? '',
                'display_order' => intval($rowData['display_order'] ?? 0)
            ];
        } elseif (isset($rowData['type']) && $rowData['type'] === 'team') {
            $data['teams'][] = [
                'name' => $rowData['name'] ?? '',
                'team_type' => $rowData['team_type'] ?? '',
                'parent_team' => $rowData['parent_team'] ?? '',
                'description' => $rowData['description'] ?? ''
            ];
        } elseif (isset($rowData['type']) && $rowData['type'] === 'user_assignment') {
            $data['user_assignments'][] = [
                'user_email' => $rowData['user_email'] ?? '',
                'team_name' => $rowData['team_name'] ?? '',
                'role' => $rowData['role'] ?? 'member',
                'is_primary' => isset($rowData['is_primary']) && strtolower($rowData['is_primary']) === 'yes'
            ];
        }
    }
    
    return $data;
}

/**
 * Parse JSON import file
 */
function parseJsonImport($content) {
    $data = json_decode($content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }
    
    // Normalize structure
    $normalized = [
        'team_types' => $data['team_types'] ?? [],
        'teams' => $data['teams'] ?? [],
        'user_assignments' => $data['user_assignments'] ?? []
    ];
    
    return $normalized;
}

/**
 * Perform the import
 */
function performImport($organisationId, $data) {
    $results = [
        'team_types' => ['created' => 0, 'skipped' => 0, 'errors' => []],
        'teams' => ['created' => 0, 'skipped' => 0, 'errors' => []],
        'user_assignments' => ['created' => 0, 'skipped' => 0, 'errors' => []]
    ];
    
    $db = getDbConnection();
    
    // Import team types
    $teamTypeMap = []; // Map original name to ID
    foreach ($data['team_types'] as $typeData) {
        if (empty($typeData['name'])) continue;
        
        try {
            // Check if exists
            $stmt = $db->prepare("SELECT id FROM team_types WHERE organisation_id = ? AND name = ?");
            $stmt->execute([$organisationId, $typeData['name']]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                $teamTypeMap[$typeData['name']] = $existing['id'];
                $results['team_types']['skipped']++;
            } else {
                TeamType::create(
                    $organisationId,
                    $typeData['name'],
                    $typeData['description'] ?? null,
                    $typeData['display_order'] ?? 0
                );
                $teamTypeId = $db->lastInsertId();
                $teamTypeMap[$typeData['name']] = $teamTypeId;
                $results['team_types']['created']++;
            }
        } catch (Exception $e) {
            $results['team_types']['errors'][] = $typeData['name'] . ': ' . $e->getMessage();
        }
    }
    
    // Import teams (need to do in order respecting hierarchy)
    $teamMap = []; // Map original name to ID
    $teamsToProcess = $data['teams'];
    
    // Sort teams: those without parents first
    usort($teamsToProcess, function($a, $b) {
        $aHasParent = !empty($a['parent_team']);
        $bHasParent = !empty($b['parent_team']);
        if ($aHasParent === $bHasParent) return 0;
        return $aHasParent ? 1 : -1;
    });
    
    foreach ($teamsToProcess as $teamData) {
        if (empty($teamData['name'])) continue;
        
        try {
            // Check if exists
            $stmt = $db->prepare("SELECT id FROM teams WHERE organisation_id = ? AND name = ?");
            $stmt->execute([$organisationId, $teamData['name']]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                $teamMap[$teamData['name']] = $existing['id'];
                $results['teams']['skipped']++;
                continue;
            }
            
            // Get team type ID
            $teamTypeId = null;
            if (!empty($teamData['team_type']) && isset($teamTypeMap[$teamData['team_type']])) {
                $teamTypeId = $teamTypeMap[$teamData['team_type']];
            }
            
            // Get parent team ID
            $parentTeamId = null;
            if (!empty($teamData['parent_team']) && isset($teamMap[$teamData['parent_team']])) {
                $parentTeamId = $teamMap[$teamData['parent_team']];
            }
            
            Team::create(
                $organisationId,
                $teamData['name'],
                $teamTypeId,
                $parentTeamId,
                $teamData['description'] ?? null
            );
            $teamId = $db->lastInsertId();
            $teamMap[$teamData['name']] = $teamId;
            $results['teams']['created']++;
        } catch (Exception $e) {
            $results['teams']['errors'][] = $teamData['name'] . ': ' . $e->getMessage();
        }
    }
    
    // Import user assignments
    foreach ($data['user_assignments'] as $assignmentData) {
        if (empty($assignmentData['user_email']) || empty($assignmentData['team_name'])) {
            continue;
        }
        
        try {
            // Find user by email
            $stmt = $db->prepare("SELECT id FROM users WHERE organisation_id = ? AND email = ?");
            $stmt->execute([$organisationId, $assignmentData['user_email']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $results['user_assignments']['errors'][] = $assignmentData['user_email'] . ': User not found';
                continue;
            }
            
            // Find team
            if (!isset($teamMap[$assignmentData['team_name']])) {
                $results['user_assignments']['errors'][] = $assignmentData['team_name'] . ': Team not found';
                continue;
            }
            
            $teamId = $teamMap[$assignmentData['team_name']];
            $role = $assignmentData['role'] ?? 'member';
            $isPrimary = $assignmentData['is_primary'] ?? false;
            
            // Check if assignment exists
            $stmt = $db->prepare("SELECT id FROM user_teams WHERE user_id = ? AND team_id = ?");
            $stmt->execute([$user['id'], $teamId]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                $results['user_assignments']['skipped']++;
            } else {
                Team::assignUserToTeam($user['id'], $teamId, $role, $isPrimary);
                $results['user_assignments']['created']++;
            }
        } catch (Exception $e) {
            $results['user_assignments']['errors'][] = $assignmentData['user_email'] . ': ' . $e->getMessage();
        }
    }
    
    return $results;
}

$pageTitle = 'Import Teams';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Import Teams Structure</h2>
        <p style="color: var(--text-light); margin-top: 0.5rem;">
            Import team types, teams, and user assignments from CSV or JSON files. Useful for bulk imports from Entra ID or other systems.
        </p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Import Results -->
    <?php if ($importResults): ?>
        <div style="margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem;">
            <h3>Import Results</h3>
            
            <div style="margin-bottom: 1rem;">
                <h4>Team Types</h4>
                <p>Created: <?php echo $importResults['team_types']['created']; ?> | 
                   Skipped: <?php echo $importResults['team_types']['skipped']; ?></p>
                <?php if (!empty($importResults['team_types']['errors'])): ?>
                    <div style="color: var(--danger-color);">
                        <strong>Errors:</strong>
                        <ul>
                            <?php foreach ($importResults['team_types']['errors'] as $err): ?>
                                <li><?php echo htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <h4>Teams</h4>
                <p>Created: <?php echo $importResults['teams']['created']; ?> | 
                   Skipped: <?php echo $importResults['teams']['skipped']; ?></p>
                <?php if (!empty($importResults['teams']['errors'])): ?>
                    <div style="color: var(--danger-color);">
                        <strong>Errors:</strong>
                        <ul>
                            <?php foreach ($importResults['teams']['errors'] as $err): ?>
                                <li><?php echo htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
            
            <div>
                <h4>User Assignments</h4>
                <p>Created: <?php echo $importResults['user_assignments']['created']; ?> | 
                   Skipped: <?php echo $importResults['user_assignments']['skipped']; ?></p>
                <?php if (!empty($importResults['user_assignments']['errors'])): ?>
                    <div style="color: var(--danger-color);">
                        <strong>Errors:</strong>
                        <ul>
                            <?php foreach ($importResults['user_assignments']['errors'] as $err): ?>
                                <li><?php echo htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Upload Form -->
    <div style="margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem;">
        <h3>Upload File</h3>
        <form method="POST" action="" enctype="multipart/form-data">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="preview">
            
            <div class="form-group">
                <label for="file_type">File Type</label>
                <select id="file_type" name="file_type" class="form-control" required>
                    <option value="csv">CSV</option>
                    <option value="json">JSON</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="import_file">Select File</label>
                <input type="file" id="import_file" name="import_file" class="form-control" accept=".csv,.json" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Preview Import</button>
            </div>
        </form>
    </div>
    
    <!-- Preview -->
    <?php if ($previewData): ?>
        <div style="margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem;">
            <h3>Preview Import</h3>
            
            <div style="margin-bottom: 1rem;">
                <h4>Team Types (<?php echo count($previewData['team_types']); ?>)</h4>
                <?php if (empty($previewData['team_types'])): ?>
                    <p style="color: var(--text-light);">No team types found</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Display Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($previewData['team_types'] as $type): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($type['name']); ?></td>
                                    <td><?php echo htmlspecialchars($type['description'] ?? '-'); ?></td>
                                    <td><?php echo $type['display_order'] ?? 0; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <h4>Teams (<?php echo count($previewData['teams']); ?>)</h4>
                <?php if (empty($previewData['teams'])): ?>
                    <p style="color: var(--text-light);">No teams found</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Team Type</th>
                                <th>Parent Team</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($previewData['teams'] as $team): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($team['name']); ?></td>
                                    <td><?php echo htmlspecialchars($team['team_type'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($team['parent_team'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($team['description'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <div>
                <h4>User Assignments (<?php echo count($previewData['user_assignments']); ?>)</h4>
                <?php if (empty($previewData['user_assignments'])): ?>
                    <p style="color: var(--text-light);">No user assignments found</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>User Email</th>
                                <th>Team Name</th>
                                <th>Role</th>
                                <th>Primary</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($previewData['user_assignments'] as $assignment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($assignment['user_email']); ?></td>
                                    <td><?php echo htmlspecialchars($assignment['team_name']); ?></td>
                                    <td><?php echo htmlspecialchars($assignment['role'] ?? 'member'); ?></td>
                                    <td><?php echo ($assignment['is_primary'] ?? false) ? 'Yes' : 'No'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <form method="POST" action="" style="margin-top: 1.5rem;">
                <?php echo CSRF::tokenField(); ?>
                <input type="hidden" name="action" value="import">
                <input type="hidden" name="import_data" value="<?php echo htmlspecialchars(json_encode($previewData)); ?>">
                <button type="submit" class="btn btn-primary">Confirm Import</button>
                <a href="<?php echo url('teams-import.php'); ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    <?php endif; ?>
    
    <!-- Format Documentation -->
    <div style="margin-top: 3rem; padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem; border-top: 2px solid var(--primary-color);">
        <h3>File Format Documentation</h3>
        
        <div style="margin-bottom: 2rem;">
            <h4>CSV Format</h4>
            <p>The CSV file should have the following columns:</p>
            <table class="table">
                <thead>
                    <tr>
                        <th>Column</th>
                        <th>Required</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>type</code></td>
                        <td>Yes</td>
                        <td>One of: <code>team_type</code>, <code>team</code>, <code>user_assignment</code></td>
                    </tr>
                    <tr>
                        <td><code>name</code></td>
                        <td>Yes*</td>
                        <td>Team type or team name (*required for team_type and team)</td>
                    </tr>
                    <tr>
                        <td><code>team_type</code></td>
                        <td>No</td>
                        <td>Team type name (for teams)</td>
                    </tr>
                    <tr>
                        <td><code>parent_team</code></td>
                        <td>No</td>
                        <td>Parent team name (for hierarchical teams)</td>
                    </tr>
                    <tr>
                        <td><code>description</code></td>
                        <td>No</td>
                        <td>Description text</td>
                    </tr>
                    <tr>
                        <td><code>display_order</code></td>
                        <td>No</td>
                        <td>Display order number (for team types)</td>
                    </tr>
                    <tr>
                        <td><code>user_email</code></td>
                        <td>Yes*</td>
                        <td>User email address (*required for user_assignment)</td>
                    </tr>
                    <tr>
                        <td><code>team_name</code></td>
                        <td>Yes*</td>
                        <td>Team name (*required for user_assignment)</td>
                    </tr>
                    <tr>
                        <td><code>role</code></td>
                        <td>No</td>
                        <td>Role: member, manager, admin, finance, senior_manager (default: member)</td>
                    </tr>
                    <tr>
                        <td><code>is_primary</code></td>
                        <td>No</td>
                        <td>Yes/No - Set as primary team (default: No)</td>
                    </tr>
                </tbody>
            </table>
            
            <h5>CSV Example:</h5>
            <pre style="background: white; padding: 1rem; border-radius: 0.375rem; overflow-x: auto;"><code>type,name,description,display_order
team_type,Department,Organizational department,1
team_type,Division,Organizational division,2
type,name,team_type,parent_team,description
team,Finance,Department,,Finance department
team,HR,Department,,Human Resources
team,North Region,Division,,
team,North Team A,Department,North Region,North region team A
type,user_email,team_name,role,is_primary
user_assignment,john@example.com,Finance,manager,Yes
user_assignment,jane@example.com,North Team A,member,No</code></pre>
        </div>
        
        <div>
            <h4>JSON Format</h4>
            <p>The JSON file should have the following structure:</p>
            <pre style="background: white; padding: 1rem; border-radius: 0.375rem; overflow-x: auto;"><code>{
  "team_types": [
    {
      "name": "Department",
      "description": "Organizational department",
      "display_order": 1
    }
  ],
  "teams": [
    {
      "name": "Finance",
      "team_type": "Department",
      "parent_team": "",
      "description": "Finance department"
    }
  ],
  "user_assignments": [
    {
      "user_email": "john@example.com",
      "team_name": "Finance",
      "role": "manager",
      "is_primary": true
    }
  ]
}</code></pre>
        </div>
        
        <div style="margin-top: 1.5rem; padding: 1rem; background: #fff3cd; border-radius: 0.375rem;">
            <strong>Note:</strong> When importing, existing team types and teams with the same name will be skipped. User assignments that already exist will also be skipped. This allows you to re-run imports safely.
        </div>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

