<?php
/**
 * Approval Rules Configuration
 * Allows organizations to configure who can approve changes
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
RBAC::requireAdmin(); // Only admins can configure approval rules

$organisationId = Auth::getOrganisationId();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create') {
            try {
                ApprovalRule::create([
                    'entity_type' => $_POST['entity_type'],
                    'action' => $_POST['action_type'] ?? '*',
                    'field_name' => !empty($_POST['field_name']) ? $_POST['field_name'] : null,
                    'approval_type' => $_POST['approval_type'],
                    'required_role_name' => !empty($_POST['required_role_name']) ? $_POST['required_role_name'] : null,
                    'manager_level' => intval($_POST['manager_level'] ?? 1),
                    'is_active' => isset($_POST['is_active']),
                    'priority' => intval($_POST['priority'] ?? 0)
                ]);
                $success = 'Approval rule created successfully.';
            } catch (Exception $e) {
                $error = 'Error creating rule: ' . $e->getMessage();
            }
        } elseif ($action === 'update') {
            try {
                $updateData = [];
                if (isset($_POST['is_active'])) {
                    $updateData['is_active'] = true;
                } else {
                    $updateData['is_active'] = false;
                }
                if (isset($_POST['priority'])) {
                    $updateData['priority'] = intval($_POST['priority']);
                }
                
                ApprovalRule::update($_POST['rule_id'], $updateData);
                $success = 'Approval rule updated successfully.';
            } catch (Exception $e) {
                $error = 'Error updating rule: ' . $e->getMessage();
            }
        } elseif ($action === 'delete') {
            try {
                ApprovalRule::delete($_POST['rule_id']);
                $success = 'Approval rule deleted successfully.';
            } catch (Exception $e) {
                $error = 'Error deleting rule: ' . $e->getMessage();
            }
        }
    }
}

// Get all approval rules
$rules = ApprovalRule::findByOrganisation($organisationId);

// Get roles for dropdown
$db = getDbConnection();
$stmt = $db->query("SELECT * FROM roles ORDER BY name");
$roles = $stmt->fetchAll();

// Entity types
$entityTypes = ['contract', 'rate', 'person', 'payment', 'tender_application', 'contract_type', 'team'];

$pageTitle = 'Approval Rules';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Approval Rules Configuration</h2>
        <p>Configure who can approve changes to different entities. Default is self-approval (no approval required).</p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Create New Rule Form -->
    <div style="margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem;">
        <h3 style="margin-top: 0;">Create New Approval Rule</h3>
        <form method="POST" action="">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="create">
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label for="entity_type">Entity Type <span style="color: var(--danger-color);">*</span></label>
                    <select id="entity_type" name="entity_type" class="form-control" required>
                        <option value="">Select...</option>
                        <?php foreach ($entityTypes as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>">
                                <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $type))); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="action_type">Action <span style="color: var(--danger-color);">*</span></label>
                    <select id="action_type" name="action_type" class="form-control" required>
                        <option value="*">All Actions</option>
                        <option value="create">Create</option>
                        <option value="update">Update</option>
                        <option value="delete">Delete</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="field_name">Field Name (optional)</label>
                    <input type="text" id="field_name" name="field_name" class="form-control" 
                           placeholder="Leave blank for all fields">
                    <small style="color: var(--text-light);">Specific field that requires approval (e.g., total_amount)</small>
                </div>
                
                <div class="form-group">
                    <label for="approval_type">Approval Type <span style="color: var(--danger-color);">*</span></label>
                    <select id="approval_type" name="approval_type" class="form-control" required onchange="toggleApprovalFields()">
                        <option value="self">Self (No Approval Required)</option>
                        <option value="manager">Manager Approval</option>
                        <option value="role">Role-Based Approval</option>
                        <option value="custom">Custom (Future)</option>
                    </select>
                </div>
                
                <div class="form-group" id="role_field" style="display: none;">
                    <label for="required_role_name">Required Role</label>
                    <select id="required_role_name" name="required_role_name" class="form-control">
                        <option value="">Select Role...</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo htmlspecialchars($role['name']); ?>">
                                <?php echo htmlspecialchars($role['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group" id="manager_level_field" style="display: none;">
                    <label for="manager_level">Manager Level</label>
                    <input type="number" id="manager_level" name="manager_level" class="form-control" 
                           value="1" min="1" max="5">
                    <small style="color: var(--text-light);">1 = Direct manager, 2 = Manager's manager, etc.</small>
                </div>
                
                <div class="form-group">
                    <label for="priority">Priority</label>
                    <input type="number" id="priority" name="priority" class="form-control" 
                           value="0" min="0" max="100">
                    <small style="color: var(--text-light);">Higher priority rules are checked first</small>
                </div>
                
                <div class="form-group" style="display: flex; align-items: center; padding-top: 1.5rem;">
                    <label style="margin: 0; margin-right: 0.5rem;">
                        <input type="checkbox" name="is_active" checked> Active
                    </label>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Create Rule</button>
        </form>
    </div>
    
    <!-- Existing Rules -->
    <h3>Existing Approval Rules</h3>
    <?php if (empty($rules)): ?>
        <div class="alert alert-info">
            <p>No approval rules configured. All changes are self-approved by default.</p>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Entity Type</th>
                        <th>Action</th>
                        <th>Field</th>
                        <th>Approval Type</th>
                        <th>Requirement</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rules as $rule): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $rule['entity_type']))); ?></strong></td>
                            <td>
                                <?php if ($rule['action'] === '*'): ?>
                                    <span style="color: var(--text-light);">All</span>
                                <?php else: ?>
                                    <?php echo htmlspecialchars(ucfirst($rule['action'])); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($rule['field_name']): ?>
                                    <code><?php echo htmlspecialchars($rule['field_name']); ?></code>
                                <?php else: ?>
                                    <span style="color: var(--text-light);">All Fields</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $approvalTypeLabels = [
                                    'self' => 'Self (No Approval)',
                                    'manager' => 'Manager',
                                    'role' => 'Role-Based',
                                    'custom' => 'Custom'
                                ];
                                echo htmlspecialchars($approvalTypeLabels[$rule['approval_type']] ?? $rule['approval_type']);
                                ?>
                            </td>
                            <td>
                                <?php if ($rule['approval_type'] === 'role' && $rule['role_name']): ?>
                                    <span class="badge badge-secondary"><?php echo htmlspecialchars($rule['role_name']); ?></span>
                                <?php elseif ($rule['approval_type'] === 'manager'): ?>
                                    <span>Level <?php echo $rule['manager_level']; ?> Manager</span>
                                <?php else: ?>
                                    <span style="color: var(--text-light);">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $rule['priority']; ?></td>
                            <td>
                                <?php if ($rule['is_active']): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" action="" style="display: inline;" 
                                      onsubmit="return confirm('Are you sure you want to delete this rule?');">
                                    <?php echo CSRF::tokenField(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="rule_id" value="<?php echo $rule['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
function toggleApprovalFields() {
    const approvalType = document.getElementById('approval_type').value;
    const roleField = document.getElementById('role_field');
    const managerLevelField = document.getElementById('manager_level_field');
    
    if (approvalType === 'role') {
        roleField.style.display = 'block';
        managerLevelField.style.display = 'none';
    } else if (approvalType === 'manager') {
        roleField.style.display = 'none';
        managerLevelField.style.display = 'block';
    } else {
        roleField.style.display = 'none';
        managerLevelField.style.display = 'none';
    }
}
</script>

<?php include INCLUDES_PATH . '/footer.php'; ?>

