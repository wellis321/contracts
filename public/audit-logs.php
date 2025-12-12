<?php
/**
 * Audit Logs Viewer
 * Displays comprehensive change history across the application
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
RBAC::requireAdmin(); // Only admins can view audit logs

$organisationId = Auth::getOrganisationId();
$error = '';
$success = '';

// Get filters from query string
$filters = [
    'entity_type' => $_GET['entity_type'] ?? '',
    'action' => $_GET['action'] ?? '',
    'user_id' => $_GET['user_id'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'search' => $_GET['search'] ?? ''
];

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Get audit logs
$auditLogs = AuditLog::findByOrganisation($organisationId, array_filter($filters), $perPage, $offset);
$totalCount = AuditLog::countByOrganisation($organisationId, array_filter($filters));
$totalPages = ceil($totalCount / $perPage);

// Get users for filter dropdown
$db = getDbConnection();
$stmt = $db->prepare("SELECT id, first_name, last_name, email FROM users WHERE organisation_id = ? ORDER BY last_name, first_name");
$stmt->execute([$organisationId]);
$users = $stmt->fetchAll();

// Get unique entity types for filter
$stmt = $db->query("SELECT DISTINCT entity_type FROM audit_logs WHERE organisation_id = $organisationId ORDER BY entity_type");
$entityTypes = array_column($stmt->fetchAll(), 'entity_type');

$pageTitle = 'Audit Logs';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Audit Logs</h2>
        <p>Comprehensive change history across the application</p>
    </div>
    
    <!-- Filters -->
    <form method="GET" action="" style="margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" class="form-control" 
                       value="<?php echo htmlspecialchars($filters['search']); ?>" 
                       placeholder="Search logs...">
            </div>
            
            <div class="form-group">
                <label for="entity_type">Entity Type</label>
                <select id="entity_type" name="entity_type" class="form-control">
                    <option value="">All Types</option>
                    <?php foreach ($entityTypes as $type): ?>
                        <option value="<?php echo htmlspecialchars($type); ?>" 
                                <?php echo $filters['entity_type'] === $type ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $type))); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="action">Action</label>
                <select id="action" name="action" class="form-control">
                    <option value="">All Actions</option>
                    <option value="create" <?php echo $filters['action'] === 'create' ? 'selected' : ''; ?>>Create</option>
                    <option value="update" <?php echo $filters['action'] === 'update' ? 'selected' : ''; ?>>Update</option>
                    <option value="delete" <?php echo $filters['action'] === 'delete' ? 'selected' : ''; ?>>Delete</option>
                    <option value="approve" <?php echo $filters['action'] === 'approve' ? 'selected' : ''; ?>>Approve</option>
                    <option value="reject" <?php echo $filters['action'] === 'reject' ? 'selected' : ''; ?>>Reject</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="user_id">User</label>
                <select id="user_id" name="user_id" class="form-control">
                    <option value="">All Users</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>" 
                                <?php echo $filters['user_id'] == $user['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="date_from">Date From</label>
                <input type="date" id="date_from" name="date_from" class="form-control" 
                       value="<?php echo htmlspecialchars($filters['date_from']); ?>">
            </div>
            
            <div class="form-group">
                <label for="date_to">Date To</label>
                <input type="date" id="date_to" name="date_to" class="form-control" 
                       value="<?php echo htmlspecialchars($filters['date_to']); ?>">
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="<?php echo url('audit-logs.php'); ?>" class="btn btn-secondary">Clear Filters</a>
        </div>
    </form>
    
    <!-- Results Summary -->
    <div style="margin-bottom: 1rem; color: var(--text-light);">
        Showing <?php echo count($auditLogs); ?> of <?php echo number_format($totalCount); ?> log entries
    </div>
    
    <!-- Audit Logs Table -->
    <?php if (empty($auditLogs)): ?>
        <div class="alert alert-info">
            <p>No audit logs found matching your criteria.</p>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>Field</th>
                        <th>Changes</th>
                        <th>Approval</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($auditLogs as $log): ?>
                        <tr>
                            <td>
                                <small><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></small>
                            </td>
                            <td>
                                <?php if ($log['first_name'] || $log['last_name']): ?>
                                    <?php echo htmlspecialchars(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? '')); ?>
                                    <br><small style="color: var(--text-light);"><?php echo htmlspecialchars($log['email'] ?? ''); ?></small>
                                <?php else: ?>
                                    <span style="color: var(--text-light); font-style: italic;">User Deleted</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo $log['action'] === 'create' ? 'success' : 
                                        ($log['action'] === 'update' ? 'secondary' : 
                                        ($log['action'] === 'delete' ? 'danger' : 'secondary')); 
                                ?>">
                                    <?php echo ucfirst($log['action']); ?>
                                </span>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $log['entity_type']))); ?></strong>
                                <br><small style="color: var(--text-light);">ID: <?php echo $log['entity_id']; ?></small>
                            </td>
                            <td>
                                <?php if ($log['field_name']): ?>
                                    <code><?php echo htmlspecialchars($log['field_name']); ?></code>
                                <?php else: ?>
                                    <span style="color: var(--text-light);">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="max-width: 300px;">
                                <?php if ($log['changes']): ?>
                                    <?php 
                                    $changes = json_decode($log['changes'], true);
                                    if (is_array($changes)): 
                                    ?>
                                        <details style="cursor: pointer;">
                                            <summary style="color: var(--primary-color); font-size: 0.9rem;">View Changes</summary>
                                            <div style="margin-top: 0.5rem; font-size: 0.85rem;">
                                                <?php foreach ($changes as $field => $change): ?>
                                                    <div style="margin-bottom: 0.5rem; padding: 0.5rem; background: var(--bg-light); border-radius: 0.25rem;">
                                                        <strong><?php echo htmlspecialchars($field); ?>:</strong><br>
                                                        <span style="color: var(--danger-color);">- <?php echo htmlspecialchars(is_array($change['old']) ? json_encode($change['old']) : ($change['old'] ?? 'null')); ?></span><br>
                                                        <span style="color: var(--success-color);">+ <?php echo htmlspecialchars(is_array($change['new']) ? json_encode($change['new']) : ($change['new'] ?? 'null')); ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </details>
                                    <?php else: ?>
                                        <small style="color: var(--text-light);"><?php echo htmlspecialchars(substr($log['changes'], 0, 50)); ?>...</small>
                                    <?php endif; ?>
                                <?php elseif ($log['old_value'] || $log['new_value']): ?>
                                    <details style="cursor: pointer;">
                                        <summary style="color: var(--primary-color); font-size: 0.9rem;">View Values</summary>
                                        <div style="margin-top: 0.5rem; font-size: 0.85rem;">
                                            <?php if ($log['old_value']): ?>
                                                <div style="color: var(--danger-color); margin-bottom: 0.25rem;">
                                                    Old: <?php echo htmlspecialchars(substr($log['old_value'], 0, 100)); ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($log['new_value']): ?>
                                                <div style="color: var(--success-color);">
                                                    New: <?php echo htmlspecialchars(substr($log['new_value'], 0, 100)); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </details>
                                <?php else: ?>
                                    <span style="color: var(--text-light);">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($log['approval_status']): ?>
                                    <span class="badge badge-<?php 
                                        echo $log['approval_status'] === 'approved' ? 'success' : 
                                            ($log['approval_status'] === 'rejected' ? 'danger' : 
                                            ($log['approval_status'] === 'pending' ? 'warning' : 'secondary')); 
                                    ?>">
                                        <?php echo ucfirst($log['approval_status']); ?>
                                    </span>
                                    <?php if ($log['approver_first_name']): ?>
                                        <br><small style="color: var(--text-light);">
                                            by <?php echo htmlspecialchars($log['approver_first_name'] . ' ' . $log['approver_last_name']); ?>
                                        </small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: var(--text-light);">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small style="color: var(--text-light);"><?php echo htmlspecialchars($log['ip_address'] ?? '-'); ?></small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($filters, ['page' => $page - 1])); ?>" class="btn btn-secondary">Previous</a>
                <?php endif; ?>
                
                <span style="padding: 0.5rem 1rem; display: flex; align-items: center;">
                    Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                </span>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?<?php echo http_build_query(array_merge($filters, ['page' => $page + 1])); ?>" class="btn btn-secondary">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

