<?php
/**
 * Teams Management Page
 * Allows admins to create and manage teams, assign users, and set up hierarchy
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
RBAC::requireAdmin();

$organisationId = Auth::getOrganisationId();
$isSuperAdmin = RBAC::isSuperAdmin();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create_team_type') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $displayOrder = !empty($_POST['display_order']) ? intval($_POST['display_order']) : 0;
            
            if (empty($name)) {
                $error = 'Team type name is required.';
            } else {
                try {
                    TeamType::create($organisationId, $name, $description, $displayOrder);
                    $success = 'Team type created successfully.';
                } catch (Exception $e) {
                    $error = 'Error creating team type: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'update_team_type') {
            $id = $_POST['id'] ?? 0;
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'display_order' => !empty($_POST['display_order']) ? intval($_POST['display_order']) : 0,
                'is_active' => isset($_POST['is_active'])
            ];
            
            if (empty($data['name'])) {
                $error = 'Team type name is required.';
            } else {
                try {
                    if (!TeamType::belongsToOrganisation($id, $organisationId)) {
                        $error = 'Unauthorized access.';
                    } else {
                        TeamType::update($id, $data);
                        $success = 'Team type updated successfully.';
                    }
                } catch (Exception $e) {
                    $error = 'Error updating team type: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'delete_team_type') {
            $id = $_POST['id'] ?? 0;
            try {
                if (!TeamType::belongsToOrganisation($id, $organisationId)) {
                    $error = 'Unauthorized access.';
                } else {
                    TeamType::delete($id);
                    $success = 'Team type deleted successfully.';
                }
            } catch (Exception $e) {
                $error = 'Error deleting team type: ' . $e->getMessage();
            }
        } elseif ($action === 'create_team') {
            $name = trim($_POST['name'] ?? '');
            $teamTypeId = !empty($_POST['team_type_id']) ? intval($_POST['team_type_id']) : null;
            $parentTeamId = !empty($_POST['parent_team_id']) ? intval($_POST['parent_team_id']) : null;
            $description = trim($_POST['description'] ?? '');
            
            if (empty($name)) {
                $error = 'Team name is required.';
            } else {
                try {
                    Team::create($organisationId, $name, $teamTypeId, $parentTeamId, $description);
                    $success = 'Team created successfully.';
                } catch (Exception $e) {
                    $error = 'Error creating team: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'update_team') {
            $id = $_POST['id'] ?? 0;
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'team_type_id' => !empty($_POST['team_type_id']) ? intval($_POST['team_type_id']) : null,
                'parent_team_id' => !empty($_POST['parent_team_id']) ? intval($_POST['parent_team_id']) : null,
                'description' => trim($_POST['description'] ?? ''),
                'is_active' => isset($_POST['is_active'])
            ];
            
            if (empty($data['name'])) {
                $error = 'Team name is required.';
            } else {
                try {
                    Team::update($id, $data);
                    $success = 'Team updated successfully.';
                } catch (Exception $e) {
                    $error = 'Error updating team: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'create_team_role') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $accessLevel = $_POST['access_level'] ?? 'team';
            $displayOrder = !empty($_POST['display_order']) ? intval($_POST['display_order']) : 0;
            
            if (empty($name)) {
                $error = 'Team role name is required.';
            } else {
                try {
                    TeamRole::initializeDefaults($organisationId); // Ensure defaults exist
                    TeamRole::create($organisationId, $name, $description, $accessLevel, $displayOrder);
                    $success = 'Team role created successfully.';
                } catch (Exception $e) {
                    $error = 'Error creating team role: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'update_team_role') {
            $id = $_POST['id'] ?? 0;
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'access_level' => $_POST['access_level'] ?? 'team',
                'display_order' => !empty($_POST['display_order']) ? intval($_POST['display_order']) : 0,
                'is_active' => isset($_POST['is_active'])
            ];
            
            if (empty($data['name'])) {
                $error = 'Team role name is required.';
            } else {
                try {
                    if (!TeamRole::belongsToOrganisation($id, $organisationId)) {
                        $error = 'Unauthorized access.';
                    } else {
                        TeamRole::update($id, $data);
                        $success = 'Team role updated successfully.';
                    }
                } catch (Exception $e) {
                    $error = 'Error updating team role: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'delete_team_role') {
            $id = $_POST['id'] ?? 0;
            try {
                if (!TeamRole::belongsToOrganisation($id, $organisationId)) {
                    $error = 'Unauthorized access.';
                } else {
                    TeamRole::delete($id);
                    $success = 'Team role deleted successfully.';
                }
            } catch (Exception $e) {
                $error = 'Error deleting team role: ' . $e->getMessage();
            }
        } elseif ($action === 'assign_user') {
            $userId = $_POST['user_id'] ?? 0;
            $teamId = $_POST['team_id'] ?? 0;
            $teamRoleId = !empty($_POST['team_role_id']) ? intval($_POST['team_role_id']) : null;
            $isPrimary = isset($_POST['is_primary']);
            
            if (empty($userId) || empty($teamId) || empty($teamRoleId)) {
                $error = 'User, team, and role are required.';
            } else {
                try {
                    // Verify role belongs to organisation
                    if (!TeamRole::belongsToOrganisation($teamRoleId, $organisationId)) {
                        $error = 'Invalid team role.';
                    } else {
                        Team::assignUserToTeam($userId, $teamId, $teamRoleId, $isPrimary);
                        $success = 'User assigned to team successfully.';
                    }
                } catch (Exception $e) {
                    $error = 'Error assigning user: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'remove_user') {
            $userId = $_POST['user_id'] ?? 0;
            $teamId = $_POST['team_id'] ?? 0;
            
            if (empty($userId) || empty($teamId)) {
                $error = 'User and team are required.';
            } else {
                try {
                    Team::removeUserFromTeam($userId, $teamId);
                    $success = 'User removed from team successfully.';
                } catch (Exception $e) {
                    $error = 'Error removing user: ' . $e->getMessage();
                }
            }
        }
    }
}

// Initialize default team roles if they don't exist
TeamRole::initializeDefaults($organisationId);

// Get all team types for organisation
$teamTypes = TeamType::findByOrganisation($organisationId, true);

// Get all team roles for organisation
$teamRoles = TeamRole::findByOrganisation($organisationId, true);

// Get all teams for organisation
$teams = Team::findByOrganisation($organisationId, true);
$teamTree = Team::getTeamTree($organisationId);

// Get all users for organisation
$db = getDbConnection();
$stmt = $db->prepare("SELECT * FROM users WHERE organisation_id = ? ORDER BY first_name, last_name");
$stmt->execute([$organisationId]);
$users = $stmt->fetchAll();

$pageTitle = 'Teams Management';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2>Teams Management</h2>
                <p style="color: var(--text-light); margin-top: 0.5rem;">
                    Create and manage teams, assign users, and set up hierarchical structure (Teams → Areas → Regions)
                </p>
            </div>
            <a href="<?php echo url('teams-import.php'); ?>" class="btn btn-primary">Import Teams</a>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Team Types Management -->
    <div style="margin-bottom: 3rem; padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem; border: 2px solid var(--primary-color);">
        <h3>Team Types</h3>
        <p style="color: var(--text-light); margin-bottom: 1rem;">
            Create custom team types for your organization (e.g., "Department", "Division", "Unit", etc.)
        </p>
        
        <!-- Create Team Type Form -->
        <div style="margin-bottom: 2rem; padding: 1rem; background: white; border-radius: 0.375rem;">
            <h4>Create New Team Type</h4>
            <form method="POST" action="">
                <?php echo CSRF::tokenField(); ?>
                <input type="hidden" name="action" value="create_team_type">
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="team_type_name">Team Type Name *</label>
                        <input type="text" id="team_type_name" name="name" class="form-control" required placeholder="e.g., Department, Division, Unit">
                    </div>
                    
                    <div class="form-group">
                        <label for="team_type_display_order">Display Order</label>
                        <input type="number" id="team_type_display_order" name="display_order" class="form-control" value="0" min="0">
                        <small style="color: var(--text-light);">Lower numbers appear first</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="team_type_description">Description (Optional)</label>
                    <textarea id="team_type_description" name="description" class="form-control" rows="2" placeholder="Describe this team type"></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Create Team Type</button>
                </div>
            </form>
        </div>
        
        <!-- Team Types List -->
        <?php if (empty($teamTypes)): ?>
            <p style="color: var(--text-light);">No team types created yet. Create your first team type above.</p>
        <?php else: ?>
            <h4>Existing Team Types</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Display Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teamTypes as $type): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($type['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($type['description'] ?? '-'); ?></td>
                            <td><?php echo $type['display_order']; ?></td>
                            <td>
                                <?php if ($type['is_active']): ?>
                                    <span style="color: var(--success-color);">Active</span>
                                <?php else: ?>
                                    <span style="color: var(--text-light);">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button onclick="editTeamType(<?php echo htmlspecialchars(json_encode($type)); ?>)" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Edit</button>
                                <?php
                                // Check if any teams use this type
                                $db = getDbConnection();
                                $stmt = $db->prepare("SELECT COUNT(*) as count FROM teams WHERE team_type_id = ?");
                                $stmt->execute([$type['id']]);
                                $teamCount = $stmt->fetch()['count'];
                                ?>
                                <?php if ($teamCount == 0): ?>
                                    <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Delete this team type?');">
                                        <?php echo CSRF::tokenField(); ?>
                                        <input type="hidden" name="action" value="delete_team_type">
                                        <input type="hidden" name="id" value="<?php echo $type['id']; ?>">
                                        <button type="submit" class="btn btn-danger" style="padding: 0.5rem 1rem;">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <small style="color: var(--text-light);"><?php echo $teamCount; ?> team(s) use this type</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Create Team Form -->
    <div style="margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem;">
        <h3>Create New Team</h3>
        <form method="POST" action="">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="create_team">
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label for="name">Team Name *</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="team_type_id">Team Type (Optional)</label>
                    <select id="team_type_id" name="team_type_id" class="form-control">
                        <option value="">No Type</option>
                        <?php foreach ($teamTypes as $type): ?>
                            <?php if ($type['is_active']): ?>
                                <option value="<?php echo $type['id']; ?>">
                                    <?php echo htmlspecialchars($type['name']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: var(--text-light);">Select a team type, or create one above if needed</small>
                </div>
                
                <div class="form-group">
                    <label for="parent_team_id">Parent Team (Optional)</label>
                    <select id="parent_team_id" name="parent_team_id" class="form-control">
                        <option value="">No Parent (Top Level)</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?php echo $team['id']; ?>">
                                <?php echo htmlspecialchars(Team::getHierarchyPath($team['id'])); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: var(--text-light);">Select a parent team to create hierarchy</small>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Description (Optional)</label>
                <textarea id="description" name="description" class="form-control" rows="2"></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create Team</button>
            </div>
        </form>
    </div>
    
    <!-- Teams Tree View -->
    <h3>Teams Structure</h3>
    <?php if (empty($teams)): ?>
        <p style="color: var(--text-light);">No teams created yet. Create your first team above.</p>
    <?php else: ?>
        <div style="margin-bottom: 2rem;">
            <?php
            function renderTeamTree($tree, $level = 0) {
                $indent = $level * 2;
                $html = '';
                foreach ($tree as $teamData) {
                    $team = $teamData['team'];
                    $children = $teamData['children'];
                    $marginLeft = $indent . 'rem';
                    $html .= '<div style="margin-left: ' . $marginLeft . '; margin-bottom: 0.5rem; padding: 0.75rem; background: var(--bg-light); border-radius: 0.375rem; border-left: 3px solid var(--primary-color);">';
            $html .= '<strong>' . htmlspecialchars($team['name']) . '</strong>';
            if ($team['team_type_name']) {
                $html .= ' <span style="color: var(--text-light); font-size: 0.875rem;">(' . htmlspecialchars($team['team_type_name']) . ')</span>';
            }
                    if ($team['description']) {
                        $html .= '<br><small style="color: var(--text-light);">' . htmlspecialchars($team['description']) . '</small>';
                    }
                    $html .= '</div>';
                    if (!empty($children)) {
                        $html .= renderTeamTree($children, $level + 1);
                    }
                }
                return $html;
            }
            echo renderTeamTree($teamTree);
            ?>
        </div>
    <?php endif; ?>
    
    <!-- Teams List -->
    <h3>All Teams</h3>
    <?php if (empty($teams)): ?>
        <p style="color: var(--text-light);">No teams found.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Team Name</th>
                    <th>Type</th>
                    <th>Hierarchy Path</th>
                    <th>Parent</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teams as $team): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($team['name']); ?></strong>
                            <?php if ($team['description']): ?>
                                <br><small style="color: var(--text-light);"><?php echo htmlspecialchars($team['description']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($team['team_type_name'] ?? 'No Type'); ?></td>
                        <td><?php echo htmlspecialchars(Team::getHierarchyPath($team['id'])); ?></td>
                        <td>
                            <?php if ($team['parent_team_id']): ?>
                                <?php 
                                $parent = Team::findById($team['parent_team_id']);
                                echo $parent ? htmlspecialchars($parent['name']) : '-';
                                ?>
                            <?php else: ?>
                                <span style="color: var(--text-light);">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($team['is_active']): ?>
                                <span style="color: var(--success-color);">Active</span>
                            <?php else: ?>
                                <span style="color: var(--text-light);">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button onclick="editTeam(<?php echo htmlspecialchars(json_encode($team)); ?>)" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Edit</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <!-- Team Roles Management -->
    <div style="margin-bottom: 3rem; padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem; border: 2px solid var(--primary-color);">
        <h3>Team Roles</h3>
        <p style="color: var(--text-light); margin-bottom: 1rem;">
            Create custom roles for team members (e.g., "Deputy Manager", "Coordinator", "Supervisor", etc.)
        </p>
        
        <!-- Create Team Role Form -->
        <div style="margin-bottom: 2rem; padding: 1rem; background: white; border-radius: 0.375rem;">
            <h4>Create New Team Role</h4>
            <form method="POST" action="">
                <?php echo CSRF::tokenField(); ?>
                <input type="hidden" name="action" value="create_team_role">
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="team_role_name">Role Name *</label>
                        <input type="text" id="team_role_name" name="name" class="form-control" required placeholder="e.g., Deputy Manager, Coordinator">
                    </div>
                    
                    <div class="form-group">
                        <label for="team_role_access_level">Access Level *</label>
                        <select id="team_role_access_level" name="access_level" class="form-control" required>
                            <option value="team">Team Access (can only access assigned team(s) and child teams)</option>
                            <option value="organisation">Organisation Access (can access all contracts)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="team_role_display_order">Display Order</label>
                        <input type="number" id="team_role_display_order" name="display_order" class="form-control" value="0" min="0">
                        <small style="color: var(--text-light);">Lower numbers appear first</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="team_role_description">Description (Optional)</label>
                    <textarea id="team_role_description" name="description" class="form-control" rows="2" placeholder="Describe this role's responsibilities"></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Create Team Role</button>
                </div>
            </form>
        </div>
        
        <!-- Team Roles List -->
        <?php if (empty($teamRoles)): ?>
            <p style="color: var(--text-light);">No team roles created yet. Default roles will be created automatically.</p>
        <?php else: ?>
            <h4>Existing Team Roles</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Access Level</th>
                        <th>Display Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teamRoles as $role): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($role['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($role['description'] ?? '-'); ?></td>
                            <td>
                                <span style="text-transform: capitalize;">
                                    <?php echo htmlspecialchars($role['access_level']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($role['display_order']); ?></td>
                            <td>
                                <?php if ($role['is_active']): ?>
                                    <span style="color: var(--success-color);">Active</span>
                                <?php else: ?>
                                    <span style="color: var(--text-light);">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button onclick="editTeamRole(<?php echo htmlspecialchars(json_encode($role)); ?>)" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Edit</button>
                                <?php if ($role['name'] !== 'Member' && $role['name'] !== 'Manager' && $role['name'] !== 'Admin' && $role['name'] !== 'Finance' && $role['name'] !== 'Senior Manager'): ?>
                                    <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this team role?');">
                                        <?php echo CSRF::tokenField(); ?>
                                        <input type="hidden" name="action" value="delete_team_role">
                                        <input type="hidden" name="id" value="<?php echo $role['id']; ?>">
                                        <button type="submit" class="btn btn-danger" style="padding: 0.5rem 1rem;">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- User Assignment Section -->
    <div style="margin-top: 3rem; padding-top: 2rem; border-top: 2px solid var(--border-color);">
        <h3>Assign Users to Teams</h3>
        
        <div style="padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem; margin-bottom: 1.5rem;">
            <form method="POST" action="">
                <?php echo CSRF::tokenField(); ?>
                <input type="hidden" name="action" value="assign_user">
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="assign_user_id">User *</label>
                        <select id="assign_user_id" name="user_id" class="form-control" required>
                            <option value="">Select...</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['email'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="assign_team_id">Team *</label>
                        <select id="assign_team_id" name="team_id" class="form-control" required>
                            <option value="">Select...</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?php echo $team['id']; ?>">
                                    <?php echo htmlspecialchars(Team::getHierarchyPath($team['id'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="team_role_id">Role in Team *</label>
                        <select id="team_role_id" name="team_role_id" class="form-control" required>
                            <option value="">Select a role...</option>
                            <?php foreach ($teamRoles as $role): ?>
                                <?php if ($role['is_active']): ?>
                                    <option value="<?php echo $role['id']; ?>" data-access-level="<?php echo htmlspecialchars($role['access_level']); ?>">
                                        <?php echo htmlspecialchars($role['name']); ?>
                                        <?php if ($role['description']): ?>
                                            - <?php echo htmlspecialchars($role['description']); ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group" style="margin-top: 1rem;">
                    <label>
                        <input type="checkbox" name="is_primary" value="1">
                        Set as Primary Team
                    </label>
                </div>
                
                <div class="form-group" style="margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary">Assign User</button>
                </div>
            </form>
        </div>
        
        <!-- User Team Assignments -->
        <h4>Current User Assignments</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Teams</th>
                    <th>Roles</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <?php
                    $userTeamAssignments = Team::getUserTeams($user['id']);
                    if (!empty($userTeamAssignments)):
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                <br><small style="color: var(--text-light);"><?php echo htmlspecialchars($user['email']); ?></small>
                            </td>
                            <td>
                                <?php foreach ($userTeamAssignments as $assignment): ?>
                                    <div style="margin-bottom: 0.25rem;">
                                        <?php echo htmlspecialchars(Team::getHierarchyPath($assignment['id'])); ?>
                                        <?php if ($assignment['is_primary']): ?>
                                            <span style="color: var(--primary-color); font-size: 0.875rem;">(Primary)</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php foreach ($userTeamAssignments as $assignment): ?>
                                    <div style="margin-bottom: 0.25rem;">
                                        <?php echo htmlspecialchars($assignment['role_name'] ?? 'Member'); ?>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php foreach ($userTeamAssignments as $assignment): ?>
                                    <form method="POST" action="" style="display: inline; margin-right: 0.5rem;" onsubmit="return confirm('Remove user from this team?');">
                                        <?php echo CSRF::tokenField(); ?>
                                        <input type="hidden" name="action" value="remove_user">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="team_id" value="<?php echo $assignment['id']; ?>">
                                        <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;">Remove</button>
                                    </form>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Team Modal -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; overflow-y: auto;">
    <div class="card" style="max-width: 600px; max-height: 90vh; overflow-y: auto; margin: 2rem;">
        <div class="card-header">
            <h3>Edit Team</h3>
        </div>
        <form method="POST" action="" id="editForm">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="update_team">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="form-group">
                <label for="edit_name">Team Name *</label>
                <input type="text" id="edit_name" name="name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="edit_team_type_id">Team Type</label>
                <select id="edit_team_type_id" name="team_type_id" class="form-control">
                    <option value="">No Type</option>
                    <?php foreach ($teamTypes as $type): ?>
                        <?php if ($type['is_active']): ?>
                            <option value="<?php echo $type['id']; ?>">
                                <?php echo htmlspecialchars($type['name']); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="edit_parent_team_id">Parent Team</label>
                <select id="edit_parent_team_id" name="parent_team_id" class="form-control">
                    <option value="">No Parent (Top Level)</option>
                    <?php foreach ($teams as $team): ?>
                        <option value="<?php echo $team['id']; ?>">
                            <?php echo htmlspecialchars(Team::getHierarchyPath($team['id'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="edit_description">Description</label>
                <textarea id="edit_description" name="description" class="form-control" rows="2"></textarea>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="edit_is_active" name="is_active" value="1">
                    Active
                </label>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Team</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Team Type Modal -->
<div id="editTeamTypeModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; overflow-y: auto;">
    <div class="card" style="max-width: 600px; max-height: 90vh; overflow-y: auto; margin: 2rem;">
        <div class="card-header">
            <h3>Edit Team Type</h3>
        </div>
        <form method="POST" action="" id="editTeamTypeForm">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="update_team_type">
            <input type="hidden" name="id" id="edit_team_type_id_field">
            
            <div class="form-group">
                <label for="edit_team_type_name">Team Type Name *</label>
                <input type="text" id="edit_team_type_name" name="name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="edit_team_type_description">Description</label>
                <textarea id="edit_team_type_description" name="description" class="form-control" rows="2"></textarea>
            </div>
            
            <div class="form-group">
                <label for="edit_team_type_display_order">Display Order</label>
                <input type="number" id="edit_team_type_display_order" name="display_order" class="form-control" min="0">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="edit_team_type_is_active" name="is_active" value="1">
                    Active
                </label>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Team Type</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editTeamTypeModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Team Role Modal -->
<div id="editTeamRoleModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; overflow-y: auto;">
    <div class="card" style="max-width: 600px; max-height: 90vh; overflow-y: auto; margin: 2rem;">
        <div class="card-header">
            <h3>Edit Team Role</h3>
        </div>
        <form method="POST" action="" id="editTeamRoleForm">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="update_team_role">
            <input type="hidden" name="id" id="edit_team_role_id_field">
            
            <div class="form-group">
                <label for="edit_team_role_name">Role Name *</label>
                <input type="text" id="edit_team_role_name" name="name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="edit_team_role_access_level">Access Level *</label>
                <select id="edit_team_role_access_level" name="access_level" class="form-control" required>
                    <option value="team">Team Access (can only access assigned team(s) and child teams)</option>
                    <option value="organisation">Organisation Access (can access all contracts)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="edit_team_role_description">Description</label>
                <textarea id="edit_team_role_description" name="description" class="form-control" rows="2"></textarea>
            </div>
            
            <div class="form-group">
                <label for="edit_team_role_display_order">Display Order</label>
                <input type="number" id="edit_team_role_display_order" name="display_order" class="form-control" min="0">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="edit_team_role_is_active" name="is_active" value="1">
                    Active
                </label>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Team Role</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editTeamRoleModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editTeam(team) {
    document.getElementById('edit_id').value = team.id;
    document.getElementById('edit_name').value = team.name || '';
    document.getElementById('edit_team_type_id').value = team.team_type_id || '';
    document.getElementById('edit_parent_team_id').value = team.parent_team_id || '';
    document.getElementById('edit_description').value = team.description || '';
    document.getElementById('edit_is_active').checked = team.is_active == 1;
    document.getElementById('editModal').style.display = 'flex';
}

function editTeamType(type) {
    document.getElementById('edit_team_type_id_field').value = type.id;
    document.getElementById('edit_team_type_name').value = type.name || '';
    document.getElementById('edit_team_type_description').value = type.description || '';
    document.getElementById('edit_team_type_display_order').value = type.display_order || 0;
    document.getElementById('edit_team_type_is_active').checked = type.is_active == 1;
    document.getElementById('editTeamTypeModal').style.display = 'flex';
}

function editTeamRole(role) {
    document.getElementById('edit_team_role_id_field').value = role.id;
    document.getElementById('edit_team_role_name').value = role.name || '';
    document.getElementById('edit_team_role_access_level').value = role.access_level || 'team';
    document.getElementById('edit_team_role_description').value = role.description || '';
    document.getElementById('edit_team_role_display_order').value = role.display_order || 0;
    document.getElementById('edit_team_role_is_active').checked = role.is_active == 1;
    document.getElementById('editTeamRoleModal').style.display = 'flex';
}
</script>

<?php include INCLUDES_PATH . '/footer.php'; ?>

