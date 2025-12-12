<?php
/**
 * Seat Change Requests Management (Super Admin)
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
        
        if ($action === 'approve_request') {
            $requestId = intval($_POST['request_id'] ?? 0);
            $reviewNotes = trim($_POST['review_notes'] ?? '');
            
            try {
                SeatChangeRequest::approve($requestId, Auth::getUserId(), $reviewNotes);
                $success = 'Seat change request approved and seats updated successfully.';
            } catch (Exception $e) {
                $error = 'Error approving request: ' . $e->getMessage();
            }
        } elseif ($action === 'reject_request') {
            $requestId = intval($_POST['request_id'] ?? 0);
            $reviewNotes = trim($_POST['review_notes'] ?? '');
            
            try {
                SeatChangeRequest::reject($requestId, Auth::getUserId(), $reviewNotes);
                $success = 'Seat change request rejected.';
            } catch (Exception $e) {
                $error = 'Error rejecting request: ' . $e->getMessage();
            }
        }
    }
}

// Get all requests
$pendingRequests = SeatChangeRequest::getPendingRequests();
$allRequests = SeatChangeRequest::getPendingRequests(); // For now, just show pending

$pageTitle = 'Seat Change Requests';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Seat Change Requests</h2>
        <p>Review and manage seat change requests from organisation administrators</p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if (empty($pendingRequests)): ?>
        <p style="color: var(--text-light);">No pending seat change requests.</p>
    <?php else: ?>
        <div style="display: grid; gap: 1.5rem;">
            <?php foreach ($pendingRequests as $request): ?>
                <div class="card" style="border-left: 4px solid var(--warning-color);">
                    <div style="display: grid; grid-template-columns: 1fr auto; gap: 1rem; align-items: start;">
                        <div>
                            <h3 style="margin-top: 0;">
                                <?php echo htmlspecialchars($request['organisation_name']); ?>
                            </h3>
                            <p style="margin: 0.5rem 0; color: var(--text-light);">
                                <strong>Requested by:</strong> <?php echo htmlspecialchars($request['requester_first_name'] . ' ' . $request['requester_last_name']); ?>
                                (<?php echo htmlspecialchars($request['requester_email']); ?>)
                            </p>
                            <p style="margin: 0.5rem 0; font-size: 1.1rem;">
                                <strong>Seat Change:</strong> 
                                <span style="color: var(--text-light);"><?php echo $request['current_seats']; ?></span>
                                → 
                                <strong style="color: var(--primary-color);"><?php echo $request['requested_seats']; ?></strong>
                                seats
                            </p>
                            <?php if ($request['message']): ?>
                                <div style="margin-top: 1rem; padding: 1rem; background: var(--bg-light); border-radius: 0.375rem;">
                                    <strong>Message:</strong>
                                    <p style="margin: 0.5rem 0 0 0; color: var(--text-color);">
                                        <?php echo nl2br(htmlspecialchars($request['message'])); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            <p style="margin-top: 1rem; margin-bottom: 0; font-size: 0.9rem; color: var(--text-light);">
                                <strong>Submitted:</strong> <?php echo date('d M Y, H:i', strtotime($request['created_at'])); ?>
                            </p>
                        </div>
                        
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Approve this seat change request? The organisation will be updated immediately.');">
                                <?php echo CSRF::tokenField(); ?>
                                <input type="hidden" name="action" value="approve_request">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <div class="form-group" style="margin-bottom: 0.5rem;">
                                    <label for="approve_notes_<?php echo $request['id']; ?>" style="font-size: 0.9rem;">Notes (Optional)</label>
                                    <textarea 
                                        id="approve_notes_<?php echo $request['id']; ?>" 
                                        name="review_notes" 
                                        class="form-control" 
                                        rows="2"
                                        placeholder="Add notes..."
                                    ></textarea>
                                </div>
                                <button type="submit" class="btn btn-success" style="width: 100%;">Approve</button>
                            </form>
                            
                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Reject this seat change request?');">
                                <?php echo CSRF::tokenField(); ?>
                                <input type="hidden" name="action" value="reject_request">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <div class="form-group" style="margin-bottom: 0.5rem;">
                                    <label for="reject_notes_<?php echo $request['id']; ?>" style="font-size: 0.9rem;">Reason (Optional)</label>
                                    <textarea 
                                        id="reject_notes_<?php echo $request['id']; ?>" 
                                        name="review_notes" 
                                        class="form-control" 
                                        rows="2"
                                        placeholder="Reason for rejection..."
                                    ></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger" style="width: 100%;">Reject</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

