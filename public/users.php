<?php
/**
 * Users Management (Organisation Admin)
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
RBAC::requireAdmin(); // Organisation admins can manage users

$organisationId = Auth::getOrganisationId();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'toggle_active') {
            $userId = $_POST['user_id'] ?? 0;
            $db = getDbConnection();
            
            // Verify user belongs to organisation
            $stmt = $db->prepare("SELECT is_active, email_verified FROM users WHERE id = ? AND organisation_id = ?");
            $stmt->execute([$userId, $organisationId]);
            $user = $stmt->fetch();
            
            if ($user) {
                $newStatus = $user['is_active'] ? 0 : 1;
                $db->beginTransaction();
                
                try {
                    $stmt = $db->prepare("UPDATE users SET is_active = ? WHERE id = ?");
                    $stmt->execute([$newStatus, $userId]);
                    
                    // Update seats_used count (only verified and active users count)
                    $stmt = $db->prepare("
                        UPDATE organisations 
                        SET seats_used = (
                            SELECT COUNT(*) 
                            FROM users 
                            WHERE organisation_id = ? AND email_verified = TRUE AND is_active = TRUE
                        )
                        WHERE id = ?
                    ");
                    $stmt->execute([$organisationId, $organisationId]);
                    
                    $db->commit();
                    $success = 'User status updated successfully.';
                } catch (Exception $e) {
                    $db->rollBack();
                    $error = 'Error updating user status: ' . $e->getMessage();
                }
            } else {
                $error = 'User not found.';
            }
        } elseif ($action === 'remove_user') {
            $userId = $_POST['user_id'] ?? 0;
            $db = getDbConnection();
            
            // Verify user belongs to organisation and is not the current user
            $stmt = $db->prepare("SELECT id, email_verified, is_active FROM users WHERE id = ? AND organisation_id = ?");
            $stmt->execute([$userId, $organisationId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $error = 'User not found.';
            } elseif ($userId == Auth::getUserId()) {
                $error = 'You cannot remove your own account.';
            } else {
                $db->beginTransaction();
                
                try {
                    // Remove user from all teams
                    $stmt = $db->prepare("DELETE FROM user_teams WHERE user_id = ?");
                    $stmt->execute([$userId]);
                    
                    // Remove user roles
                    $stmt = $db->prepare("DELETE FROM user_roles WHERE user_id = ?");
                    $stmt->execute([$userId]);
                    
                    // Delete user
                    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    
                    // Update seats_used count (only verified and active users count)
                    $stmt = $db->prepare("
                        UPDATE organisations 
                        SET seats_used = (
                            SELECT COUNT(*) 
                            FROM users 
                            WHERE organisation_id = ? AND email_verified = TRUE AND is_active = TRUE
                        )
                        WHERE id = ?
                    ");
                    $stmt->execute([$organisationId, $organisationId]);
                    
                    $db->commit();
                    $success = 'User removed from organisation successfully.';
                } catch (Exception $e) {
                    $db->rollBack();
                    $error = 'Error removing user: ' . $e->getMessage();
                }
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

// Get current user ID to prevent self-removal
$currentUserId = Auth::getUserId();

$pageTitle = 'Users';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Users</h2>
        <p>Manage users in your organisation</p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Users List -->
    <?php if (empty($users)): ?>
        <p>No users found.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Email Verified</th>
                    <th>Roles</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php if ($user['email_verified']): ?>
                                <span style="color: var(--success-color);"><i class="fa-solid fa-check-circle" style="margin-right: 0.5rem;"></i>Verified</span>
                            <?php else: ?>
                                <span style="color: var(--warning-color);">✗ Not Verified</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $userRoles = $user['roles'] ? explode(',', $user['roles']) : [];
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
                                <span style="color: var(--text-light);">Suspended</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo $user['last_login'] ? date(DATETIME_FORMAT, strtotime($user['last_login'])) : 'Never'; ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <form method="POST" action="" style="display: inline;">
                                    <?php echo CSRF::tokenField(); ?>
                                    <input type="hidden" name="action" value="toggle_active">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-secondary" style="padding: 0.5rem 1rem;">
                                        <?php echo $user['is_active'] ? 'Suspend' : 'Activate'; ?>
                                    </button>
                                </form>
                                <?php if ($user['id'] != $currentUserId): ?>
                                    <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to permanently remove this user from your organisation? This action cannot be undone.');">
                                        <?php echo CSRF::tokenField(); ?>
                                        <input type="hidden" name="action" value="remove_user">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-danger" style="padding: 0.5rem 1rem;">Remove</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
