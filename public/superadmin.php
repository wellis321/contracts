<?php
/**
 * Super Admin Panel
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
RBAC::requireSuperAdmin();

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create_organisation') {
            $name = trim($_POST['name'] ?? '');
            $domain = trim($_POST['domain'] ?? '');
            $seatsAllocated = intval($_POST['seats_allocated'] ?? 0);
            
            if (empty($name) || empty($domain) || $seatsAllocated <= 0) {
                $error = 'Please fill in all required fields.';
            } else {
                try {
                    Organisation::create($name, $domain, $seatsAllocated);
                    $success = 'Organisation created successfully.';
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) {
                        $error = 'An organisation with this domain already exists.';
                    } else {
                        $error = 'Error creating organisation.';
                    }
                }
            }
        } elseif ($action === 'update_seats') {
            $id = $_POST['id'] ?? 0;
            $seatsAllocated = intval($_POST['seats_allocated'] ?? 0);
            
            if ($seatsAllocated < 0) {
                $error = 'Seats allocated cannot be negative.';
            } else {
                Organisation::updateSeats($id, $seatsAllocated);
                $success = 'Seats updated successfully.';
            }
        }
    }
}

// Get all organisations
$organisations = Organisation::findAll();

// Get pending seat requests count
$pendingSeatRequestsCount = SeatChangeRequest::getPendingCount();

$pageTitle = 'Super Admin Panel';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Super Admin Panel</h2>
        <p>Manage organisations, domains, and seat allocations</p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($pendingSeatRequestsCount > 0): ?>
        <div class="alert alert-warning" style="margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <strong><i class="fa-solid fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>Pending Seat Change Requests</strong>
                    <p style="margin: 0.5rem 0 0 0;">
                        You have <?php echo $pendingSeatRequestsCount; ?> pending seat change request<?php echo $pendingSeatRequestsCount > 1 ? 's' : ''; ?> waiting for review.
                    </p>
                </div>
                <a href="<?php echo htmlspecialchars(url('seat-requests.php')); ?>" class="btn btn-warning" style="white-space: nowrap;">
                    Review Requests
                </a>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Create Organisation Form -->
    <div style="margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem;">
        <h3>Create New Organisation</h3>
        <form method="POST" action="">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="create_organisation">
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label for="name">Organisation Name *</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="domain">Domain *</label>
                    <input type="text" id="domain" name="domain" class="form-control" 
                           placeholder="e.g., example.com" required>
                    <small style="color: var(--text-light);">Users will register using this domain</small>
                </div>
                
                <div class="form-group">
                    <label for="seats_allocated">Seats Allocated *</label>
                    <input type="number" id="seats_allocated" name="seats_allocated" class="form-control" 
                           min="1" value="10" required>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create Organisation</button>
            </div>
        </form>
    </div>
    
    <!-- Organisations List -->
    <h3>Organisations</h3>
    <?php if (empty($organisations)): ?>
        <p>No organisations found. Create your first organisation above.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Domain</th>
                    <th>Seats Allocated</th>
                    <th>Seats Used</th>
                    <th>Available Seats</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($organisations as $org): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($org['name']); ?></td>
                        <td><?php echo htmlspecialchars($org['domain']); ?></td>
                        <td><?php echo $org['seats_allocated']; ?></td>
                        <td><?php echo $org['seats_used']; ?></td>
                        <td>
                            <?php 
                            $available = $org['seats_allocated'] - $org['seats_used'];
                            $color = $available > 0 ? 'var(--success-color)' : 'var(--danger-color)';
                            ?>
                            <span style="color: <?php echo $color; ?>;"><?php echo $available; ?></span>
                        </td>
                        <td>
                            <button onclick="editSeats(<?php echo htmlspecialchars(json_encode($org)); ?>)" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Update Seats</button>
                            <a href="<?php echo htmlspecialchars(url('organisation-users.php?org_id=' . $org['id'])); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Manage Users</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Edit Seats Modal -->
<div id="editSeatsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="max-width: 400px;">
        <div class="card-header">
            <h3>Update Seats</h3>
        </div>
        <form method="POST" action="" id="editSeatsForm">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="update_seats">
            <input type="hidden" name="id" id="edit_org_id">
            
            <div class="form-group">
                <label for="edit_seats">Seats Allocated</label>
                <input type="number" id="edit_seats" name="seats_allocated" class="form-control" min="0" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editSeatsModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editSeats(org) {
    document.getElementById('edit_org_id').value = org.id;
    document.getElementById('edit_seats').value = org.seats_allocated;
    document.getElementById('editSeatsModal').style.display = 'flex';
}
</script>

<?php include INCLUDES_PATH . '/footer.php'; ?>
