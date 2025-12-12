<?php
/**
 * Organisation Users Management (Super Admin)
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
RBAC::requireSuperAdmin();

$organisationId = $_GET['org_id'] ?? 0;
$organisation = Organisation::findById($organisationId);

if (!$organisation) {
    header('Location: ' . url('superadmin.php?error=not_found'));
    exit;
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'assign_admin') {
            $userId = $_POST['user_id'] ?? 0;
            $db = getDbConnection();
            
            // Get organisation_admin role ID
            $stmt = $db->prepare("SELECT id FROM roles WHERE name = 'organisation_admin'");
            $stmt->execute();
            $role = $stmt->fetch();
            
            if ($role) {
                // Check if user belongs to organisation
                $stmt = $db->prepare("SELECT id FROM users WHERE id = ? AND organisation_id = ?");
                $stmt->execute([$userId, $organisationId]);
                if ($stmt->fetch()) {
                    // Assign role
                    $stmt = $db->prepare("INSERT IGNORE INTO user_roles (user_id, role_id, assigned_by) VALUES (?, ?, ?)");
                    $stmt->execute([$userId, $role['id'], Auth::getUserId()]);
                    $success = 'Organisation admin role assigned successfully.';
                } else {
                    $error = 'User does not belong to this organisation.';
                }
            }
        } elseif ($action === 'remove_admin') {
            $userId = $_POST['user_id'] ?? 0;
            $db = getDbConnection();
            
            $stmt = $db->prepare("SELECT id FROM roles WHERE name = 'organisation_admin'");
            $stmt->execute();
            $role = $stmt->fetch();
            
            if ($role) {
                $stmt = $db->prepare("DELETE FROM user_roles WHERE user_id = ? AND role_id = ?");
                $stmt->execute([$userId, $role['id']]);
                $success = 'Organisation admin role removed successfully.';
            }
        }
    }
}

// Get users for this organisation
$db = getDbConnection();
$stmt = $db->prepare("
    SELECT u.*, 
           GROUP_CONCAT(r.name) as roles
    FROM users u
    LEFT JOIN user_roles ur ON u.id = ur.user_id
    LEFT JOIN roles r ON ur.role_id = r.id
    WHERE u.organisation_id = ?
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$stmt->execute([$organisationId]);
$users = $stmt->fetchAll();

$pageTitle = 'Manage Users - ' . htmlspecialchars($organisation['name']);
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-between">
            <div>
                <h2>Manage Users: <?php echo htmlspecialchars($organisation['name']); ?></h2>
                <p>Assign organisation administrators</p>
            </div>
            <a href="<?php echo htmlspecialchars(url('superadmin.php')); ?>" class="btn btn-secondary">Back to Super Admin</a>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Users List -->
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Roles</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <?php
                        $userRoles = $user['roles'] ? explode(',', $user['roles']) : [];
                        $isAdmin = in_array('organisation_admin', $userRoles);
                        ?>
                        <?php if (empty($userRoles)): ?>
                            <span style="color: var(--text-light);">No roles</span>
                        <?php else: ?>
                            <?php echo htmlspecialchars(implode(', ', $userRoles)); ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($user['is_active']): ?>
                            <span style="color: var(--success-color);">Active</span>
                        <?php else: ?>
                            <span style="color: var(--text-light);">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!$isAdmin): ?>
                            <form method="POST" action="" style="display: inline;">
                                <?php echo CSRF::tokenField(); ?>
                                <input type="hidden" name="action" value="assign_admin">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Make Admin</button>
                            </form>
                        <?php else: ?>
                            <form method="POST" action="" style="display: inline;">
                                <?php echo CSRF::tokenField(); ?>
                                <input type="hidden" name="action" value="remove_admin">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="btn btn-danger" style="padding: 0.5rem 1rem;" onclick="return confirm('Are you sure you want to remove organisation admin role?');">Remove Admin</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php if (empty($users)): ?>
        <p>No users found for this organisation.</p>
    <?php endif; ?>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
