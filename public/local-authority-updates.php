<?php
/**
 * Local Authority Rate Updates Management (Admin)
 * Allows admins to add and manage rate updates from local authorities
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
RBAC::requireAdmin();

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
            $data = [
                'local_authority_id' => !empty($_POST['local_authority_id']) ? intval($_POST['local_authority_id']) : null,
                'title' => trim($_POST['title'] ?? ''),
                'content' => trim($_POST['content'] ?? ''),
                'effective_date' => $_POST['effective_date'] ?? null,
                'rate_change' => !empty($_POST['rate_change']) ? floatval($_POST['rate_change']) : null,
                'rate_type' => trim($_POST['rate_type'] ?? ''),
                'source_url' => trim($_POST['source_url'] ?? ''),
                'published_date' => $_POST['published_date'] ?? date('Y-m-d'),
                'created_by' => Auth::getUserId()
            ];
            
            if (empty($data['title']) || empty($data['content'])) {
                $error = 'Title and content are required.';
            } else {
                try {
                    LocalAuthorityRateInfo::createUpdate($data);
                    $success = 'Rate update created successfully.';
                } catch (Exception $e) {
                    $error = 'Error creating update: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'update') {
            $id = $_POST['id'] ?? 0;
            $data = [
                'local_authority_id' => !empty($_POST['local_authority_id']) ? intval($_POST['local_authority_id']) : null,
                'title' => trim($_POST['title'] ?? ''),
                'content' => trim($_POST['content'] ?? ''),
                'effective_date' => $_POST['effective_date'] ?? null,
                'rate_change' => !empty($_POST['rate_change']) ? floatval($_POST['rate_change']) : null,
                'rate_type' => trim($_POST['rate_type'] ?? ''),
                'source_url' => trim($_POST['source_url'] ?? ''),
                'published_date' => $_POST['published_date'] ?? date('Y-m-d'),
                'is_active' => isset($_POST['is_active'])
            ];
            
            if (empty($data['title']) || empty($data['content'])) {
                $error = 'Title and content are required.';
            } else {
                try {
                    LocalAuthorityRateInfo::updateUpdate($id, $data);
                    $success = 'Rate update updated successfully.';
                } catch (Exception $e) {
                    $error = 'Error updating: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            try {
                LocalAuthorityRateInfo::deleteUpdate($id);
                $success = 'Rate update deleted successfully.';
            } catch (Exception $e) {
                $error = 'Error deleting: ' . $e->getMessage();
            }
        }
    }
}

// Get all updates
$allUpdates = LocalAuthorityRateInfo::getAllRecentUpdates(100);

// Get local authorities
$db = getDbConnection();
$stmt = $db->query("SELECT * FROM local_authorities ORDER BY name");
$localAuthorities = $stmt->fetchAll();

$pageTitle = 'Local Authority Rate Updates';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2>Local Authority Rate Updates</h2>
                <p style="color: var(--text-light); margin-top: 0.5rem;">
                    Manage rate updates and news from local authorities
                </p>
            </div>
            <button onclick="document.getElementById('createForm').style.display='block'" class="btn btn-primary">
                Add Update
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
        <h3>Add Local Authority Rate Update</h3>
        <form method="POST" action="">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="create">
            
            <div class="form-group">
                <label for="local_authority_id">Local Authority (Optional)</label>
                <select id="local_authority_id" name="local_authority_id" class="form-control">
                    <option value="">All Local Authorities</option>
                    <?php foreach ($localAuthorities as $la): ?>
                        <option value="<?php echo $la['id']; ?>"><?php echo htmlspecialchars($la['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <small style="color: var(--text-light);">Leave blank for general updates affecting all authorities</small>
            </div>
            
            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="content">Content *</label>
                <textarea id="content" name="content" class="form-control" rows="6" required></textarea>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label for="published_date">Published Date</label>
                    <input type="date" id="published_date" name="published_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="effective_date">Effective Date (Optional)</label>
                    <input type="date" id="effective_date" name="effective_date" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="rate_change">Rate Change (Optional)</label>
                    <input type="number" id="rate_change" name="rate_change" class="form-control" step="0.01" min="0">
                    <small style="color: var(--text-light);">New rate amount in £</small>
                </div>
                
                <div class="form-group">
                    <label for="rate_type">Rate Type (Optional)</label>
                    <input type="text" id="rate_type" name="rate_type" class="form-control" placeholder="e.g., Waking Hours, Sleepover">
                </div>
            </div>
            
            <div class="form-group">
                <label for="source_url">Source URL (Optional)</label>
                <input type="url" id="source_url" name="source_url" class="form-control" placeholder="https://...">
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create Update</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('createForm').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
    
    <!-- Updates List -->
    <?php if (empty($allUpdates)): ?>
        <p>No rate updates found. Add your first update above.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Local Authority</th>
                    <th>Published</th>
                    <th>Effective</th>
                    <th>Rate Change</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allUpdates as $update): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($update['title']); ?></strong>
                            <?php if (strlen($update['content']) > 100): ?>
                                <br><small style="color: var(--text-light);"><?php echo htmlspecialchars(substr($update['content'], 0, 100)) . '...'; ?></small>
                            <?php else: ?>
                                <br><small style="color: var(--text-light);"><?php echo htmlspecialchars($update['content']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($update['local_authority_name'] ?? 'All'); ?></td>
                        <td><?php echo $update['published_date'] ? date(DATE_FORMAT, strtotime($update['published_date'])) : '-'; ?></td>
                        <td><?php echo $update['effective_date'] ? date(DATE_FORMAT, strtotime($update['effective_date'])) : '-'; ?></td>
                        <td>
                            <?php if ($update['rate_change']): ?>
                                £<?php echo number_format($update['rate_change'], 2); ?>
                                <?php if ($update['rate_type']): ?>
                                    <br><small style="color: var(--text-light);"><?php echo htmlspecialchars($update['rate_type']); ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($update['is_active']): ?>
                                <span style="color: var(--success-color);">Active</span>
                            <?php else: ?>
                                <span style="color: var(--text-light);">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button onclick="editUpdate(<?php echo htmlspecialchars(json_encode($update)); ?>)" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Edit</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; overflow-y: auto;">
    <div class="card" style="max-width: 700px; max-height: 90vh; overflow-y: auto; margin: 2rem;">
        <div class="card-header">
            <h3>Edit Rate Update</h3>
        </div>
        <form method="POST" action="" id="editForm">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="form-group">
                <label for="edit_local_authority_id">Local Authority</label>
                <select id="edit_local_authority_id" name="local_authority_id" class="form-control">
                    <option value="">All Local Authorities</option>
                    <?php foreach ($localAuthorities as $la): ?>
                        <option value="<?php echo $la['id']; ?>"><?php echo htmlspecialchars($la['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="edit_title">Title *</label>
                <input type="text" id="edit_title" name="title" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="edit_content">Content *</label>
                <textarea id="edit_content" name="content" class="form-control" rows="6" required></textarea>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label for="edit_published_date">Published Date</label>
                    <input type="date" id="edit_published_date" name="published_date" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="edit_effective_date">Effective Date</label>
                    <input type="date" id="edit_effective_date" name="effective_date" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="edit_rate_change">Rate Change</label>
                    <input type="number" id="edit_rate_change" name="rate_change" class="form-control" step="0.01" min="0">
                </div>
                
                <div class="form-group">
                    <label for="edit_rate_type">Rate Type</label>
                    <input type="text" id="edit_rate_type" name="rate_type" class="form-control">
                </div>
            </div>
            
            <div class="form-group">
                <label for="edit_source_url">Source URL</label>
                <input type="url" id="edit_source_url" name="source_url" class="form-control">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="edit_is_active" name="is_active" value="1"> Active
                </label>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-danger" name="action" value="delete" onclick="return confirm('Are you sure you want to delete this update?');">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
function editUpdate(update) {
    document.getElementById('edit_id').value = update.id;
    document.getElementById('edit_local_authority_id').value = update.local_authority_id || '';
    document.getElementById('edit_title').value = update.title || '';
    document.getElementById('edit_content').value = update.content || '';
    document.getElementById('edit_published_date').value = update.published_date || '';
    document.getElementById('edit_effective_date').value = update.effective_date || '';
    document.getElementById('edit_rate_change').value = update.rate_change || '';
    document.getElementById('edit_rate_type').value = update.rate_type || '';
    document.getElementById('edit_source_url').value = update.source_url || '';
    document.getElementById('edit_is_active').checked = update.is_active == 1;
    document.getElementById('editModal').style.display = 'flex';
}
</script>

<?php include INCLUDES_PATH . '/footer.php'; ?>
