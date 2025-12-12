<?php
/**
 * Contract Types Management Page
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
RBAC::requireAdmin(); // Only admins can manage contract types

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
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            if (empty($name)) {
                $error = 'Contract type name is required.';
            } else {
                try {
                    ContractType::create($organisationId, $name, $description);
                    $success = 'Contract type created successfully.';
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) {
                        $error = 'A contract type with this name already exists.';
                    } else {
                        $error = 'Error creating contract type.';
                    }
                }
            }
        } elseif ($action === 'update') {
            $id = $_POST['id'] ?? 0;
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            
            if (empty($name)) {
                $error = 'Contract type name is required.';
            } elseif (!ContractType::belongsToOrganisation($id, $organisationId)) {
                $error = 'Unauthorized access.';
            } elseif (ContractType::isSystemDefault($id)) {
                $error = 'Cannot modify system default contract types. You can only modify your custom contract types.';
            } else {
                ContractType::update($id, $name, $description, $isActive);
                $success = 'Contract type updated successfully.';
            }
        } elseif ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            
            if (!ContractType::belongsToOrganisation($id, $organisationId)) {
                $error = 'Unauthorized access.';
            } elseif (ContractType::isSystemDefault($id)) {
                $error = 'Cannot delete system default contract types.';
            } else {
                ContractType::delete($id);
                $success = 'Contract type deleted successfully.';
            }
        }
    }
}

// Get all contract types
$contractTypes = ContractType::findByOrganisation($organisationId, true);

$pageTitle = 'Contract Types';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-between">
            <div>
                <h2>Contract Types</h2>
                <p>System default types are available to all organisations. You can also create custom contract types for your organisation.</p>
            </div>
            <button onclick="document.getElementById('createForm').style.display='block'" class="btn btn-primary" style="white-space: normal; line-height: 1.3; padding: 0.75rem 1.5rem;">
                Add Custom<br>Contract Type
            </button>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Create Form -->
    <div id="createForm" style="display: none; margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem;">
        <h3>Create New Custom Contract Type</h3>
        <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 1rem;">
            System default types (Waking/Active Hours, Waking Night Shifts, Sleepover Hours, Support Hours, Personal Care) are already available. Create custom types for your organisation-specific needs.
        </p>
        <form method="POST" action="">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="create">
            
            <div class="form-group">
                <label for="name">Name *</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control"></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('createForm').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
    
    <!-- Contract Types List -->
    <?php 
    // Separate system defaults from custom types
    $systemTypes = [];
    $customTypes = [];
    foreach ($contractTypes as $type) {
        if ($type['is_system_default'] == 1 || $type['organisation_id'] === null) {
            $systemTypes[] = $type;
        } else {
            $customTypes[] = $type;
        }
    }
    ?>
    
    <?php if (empty($systemTypes) && empty($customTypes)): ?>
        <p>No contract types found. System default types should be available automatically.</p>
    <?php else: ?>
        <?php if (!empty($systemTypes)): ?>
            <div style="margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem;">System Default Contract Types</h3>
                <p style="color: var(--text-light); margin-bottom: 1rem; font-size: 0.9rem;">
                    These standard contract types are available to all organisations and cannot be modified or deleted.
                </p>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($systemTypes as $type): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($type['name']); ?></strong>
                                    <span style="color: var(--text-light); font-size: 0.85rem; margin-left: 0.5rem;">(System Default)</span>
                                </td>
                                <td><?php echo htmlspecialchars($type['description'] ?? '-'); ?></td>
                                <td>
                                    <?php if ($type['is_active']): ?>
                                        <span style="color: var(--success-color);">Active</span>
                                    <?php else: ?>
                                        <span style="color: var(--text-light);">Inactive</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($customTypes)): ?>
            <div style="margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem;">Your Custom Contract Types</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customTypes as $type): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($type['name']); ?></td>
                                <td><?php echo htmlspecialchars($type['description'] ?? '-'); ?></td>
                                <td>
                                    <?php if ($type['is_active']): ?>
                                        <span style="color: var(--success-color);">Active</span>
                                    <?php else: ?>
                                        <span style="color: var(--text-light);">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button onclick="editType(<?php echo htmlspecialchars(json_encode($type)); ?>)" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Edit</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="max-width: 500px; max-height: 90vh; overflow-y: auto;">
        <div class="card-header">
            <h3>Edit Contract Type</h3>
        </div>
        <form method="POST" action="" id="editForm">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="form-group">
                <label for="edit_name">Name *</label>
                <input type="text" id="edit_name" name="name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="edit_description">Description</label>
                <textarea id="edit_description" name="description" class="form-control"></textarea>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="edit_is_active" name="is_active" value="1"> Active
                </label>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-danger" name="action" value="delete" onclick="return confirm('Are you sure you want to delete this contract type?');">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
function editType(type) {
    document.getElementById('edit_id').value = type.id;
    document.getElementById('edit_name').value = type.name;
    document.getElementById('edit_description').value = type.description || '';
    document.getElementById('edit_is_active').checked = type.is_active == 1;
    document.getElementById('editModal').style.display = 'flex';
}
</script>

<?php include INCLUDES_PATH . '/footer.php'; ?>
