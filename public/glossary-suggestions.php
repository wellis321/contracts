<?php
/**
 * Glossary Suggestions Review
 * Admin page for reviewing and managing glossary term suggestions
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
RBAC::requireSuperAdmin(); // Only superadmins can review suggestions

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $action = $_POST['action'] ?? '';
        $suggestionId = intval($_POST['suggestion_id'] ?? 0);
        
        if ($action === 'approve') {
            // Approve suggestion and add to glossary
            $term = trim($_POST['term'] ?? '');
            $definition = trim($_POST['definition'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            
            if (empty($term) || empty($definition) || $suggestionId <= 0) {
                $error = 'Missing required information.';
            } else {
                try {
                    $db = getDbConnection();
                    
                    // Get suggestion details and user info
                    $stmt = $db->prepare("
                        SELECT gs.*, u.email, u.first_name 
                        FROM glossary_suggestions gs
                        LEFT JOIN users u ON gs.suggested_by = u.id
                        WHERE gs.id = ?
                    ");
                    $stmt->execute([$suggestionId]);
                    $suggestion = $stmt->fetch();
                    
                    if (!$suggestion) {
                        $error = 'Suggestion not found.';
                    } else {
                        $db->beginTransaction();
                        
                        // Update suggestion status
                        $stmt = $db->prepare("
                            UPDATE glossary_suggestions 
                            SET status = 'approved', 
                                reviewed_by = ?, 
                                reviewed_at = NOW(),
                                notes = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([Auth::getUserId(), $notes, $suggestionId]);
                        
                        // Add to glossary_terms table if it exists
                        try {
                            $letter = strtoupper(substr($term, 0, 1));
                            if (!preg_match('/^[A-Z]$/', $letter)) {
                                $letter = '#';
                            }
                            
                            $stmt = $db->prepare("
                                INSERT INTO glossary_terms (term, definition, letter, is_active)
                                VALUES (?, ?, ?, TRUE)
                                ON DUPLICATE KEY UPDATE 
                                    definition = VALUES(definition),
                                    letter = VALUES(letter),
                                    is_active = TRUE,
                                    removed_at = NULL,
                                    removed_by = NULL,
                                    removal_reason = NULL
                            ");
                            $stmt->execute([$term, $definition, $letter]);
                        } catch (PDOException $e) {
                            // Table might not exist yet, that's okay
                            // Just log it but don't fail the approval
                            error_log("Could not add to glossary_terms table: " . $e->getMessage());
                        }
                        
                        $db->commit();
                        
                        // Send approval email if user email exists
                        if ($suggestion['email']) {
                            $emailSent = Email::sendGlossaryApprovalEmail(
                                $suggestion['email'],
                                $suggestion['first_name'],
                                $term,
                                $notes
                            );
                            
                            if ($emailSent) {
                                $success = 'Suggestion approved, added to glossary, and approval email sent to the user.';
                            } else {
                                $success = 'Suggestion approved and added to glossary, but failed to send email notification.';
                            }
                        } else {
                            $success = 'Suggestion approved and added to glossary. (No email address found for user)';
                        }
                    }
                } catch (Exception $e) {
                    if (isset($db) && $db->inTransaction()) {
                        $db->rollBack();
                    }
                    $error = 'Error approving suggestion: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'reject') {
            $notes = trim($_POST['notes'] ?? '');
            
            if ($suggestionId <= 0) {
                $error = 'Invalid suggestion ID.';
            } elseif (empty($notes)) {
                $error = 'Please provide a reason for rejection. This will be sent to the user who suggested the term.';
            } else {
                try {
                    $db = getDbConnection();
                    
                    // Get suggestion details and user info
                    $stmt = $db->prepare("
                        SELECT gs.*, u.email, u.first_name 
                        FROM glossary_suggestions gs
                        LEFT JOIN users u ON gs.suggested_by = u.id
                        WHERE gs.id = ?
                    ");
                    $stmt->execute([$suggestionId]);
                    $suggestion = $stmt->fetch();
                    
                    if (!$suggestion) {
                        $error = 'Suggestion not found.';
                    } else {
                        // Update suggestion status
                        $stmt = $db->prepare("
                            UPDATE glossary_suggestions 
                            SET status = 'rejected', 
                                reviewed_by = ?, 
                                reviewed_at = NOW(),
                                notes = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([Auth::getUserId(), $notes, $suggestionId]);
                        
                        // Send rejection email if user email exists
                        if ($suggestion['email']) {
                            $emailSent = Email::sendGlossaryRejectionEmail(
                                $suggestion['email'],
                                $suggestion['first_name'],
                                $suggestion['term'],
                                $notes
                            );
                            
                            if ($emailSent) {
                                $success = 'Suggestion rejected and rejection email sent to the user.';
                            } else {
                                $success = 'Suggestion rejected, but failed to send email notification.';
                            }
                        } else {
                            $success = 'Suggestion rejected. (No email address found for user)';
                        }
                    }
                } catch (Exception $e) {
                    $error = 'Error rejecting suggestion: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'delete') {
            if ($suggestionId <= 0) {
                $error = 'Invalid suggestion ID.';
            } else {
                try {
                    $db = getDbConnection();
                    $stmt = $db->prepare("DELETE FROM glossary_suggestions WHERE id = ?");
                    $stmt->execute([$suggestionId]);
                    $success = 'Suggestion deleted.';
                } catch (Exception $e) {
                    $error = 'Error deleting suggestion: ' . $e->getMessage();
                }
            }
        }
    }
}

// Get suggestions
$db = getDbConnection();
$statusFilter = $_GET['status'] ?? 'pending';

$query = "
    SELECT gs.*, 
           u.first_name, 
           u.last_name, 
           u.email,
           reviewer.first_name as reviewer_first_name,
           reviewer.last_name as reviewer_last_name
    FROM glossary_suggestions gs
    LEFT JOIN users u ON gs.suggested_by = u.id
    LEFT JOIN users reviewer ON gs.reviewed_by = reviewer.id
";

if ($statusFilter !== 'all') {
    $query .= " WHERE gs.status = ?";
}

$query .= " ORDER BY gs.created_at DESC";

$stmt = $db->prepare($query);
if ($statusFilter !== 'all') {
    $stmt->execute([$statusFilter]);
} else {
    $stmt->execute();
}
$suggestions = $stmt->fetchAll();

// Count by status
$stmt = $db->query("
    SELECT status, COUNT(*) as count 
    FROM glossary_suggestions 
    GROUP BY status
");
$statusCounts = [];
while ($row = $stmt->fetch()) {
    $statusCounts[$row['status']] = $row['count'];
}

$pageTitle = 'Glossary Suggestions';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <h1>Glossary Suggestions Review</h1>
        <p style="color: var(--text-light); margin-top: 0.5rem;">
            Review and manage user-submitted glossary term suggestions
        </p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Status Filter -->
    <div style="margin-bottom: 2rem; display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
        <strong>Filter:</strong>
        <a href="?status=pending" class="btn <?php echo $statusFilter === 'pending' ? 'btn-primary' : 'btn-secondary'; ?>" style="text-decoration: none;">
            Pending 
            <?php if (isset($statusCounts['pending'])): ?>
                <span style="background: rgba(255,255,255,0.2); padding: 0.125rem 0.5rem; border-radius: 1rem; margin-left: 0.25rem;">
                    <?php echo $statusCounts['pending']; ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="?status=approved" class="btn <?php echo $statusFilter === 'approved' ? 'btn-primary' : 'btn-secondary'; ?>" style="text-decoration: none;">
            Approved
            <?php if (isset($statusCounts['approved'])): ?>
                <span style="background: rgba(255,255,255,0.2); padding: 0.125rem 0.5rem; border-radius: 1rem; margin-left: 0.25rem;">
                    <?php echo $statusCounts['approved']; ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="?status=rejected" class="btn <?php echo $statusFilter === 'rejected' ? 'btn-primary' : 'btn-secondary'; ?>" style="text-decoration: none;">
            Rejected
            <?php if (isset($statusCounts['rejected'])): ?>
                <span style="background: rgba(255,255,255,0.2); padding: 0.125rem 0.5rem; border-radius: 1rem; margin-left: 0.25rem;">
                    <?php echo $statusCounts['rejected']; ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="?status=all" class="btn <?php echo $statusFilter === 'all' ? 'btn-primary' : 'btn-secondary'; ?>" style="text-decoration: none;">
            All
        </a>
    </div>
    
    <!-- Suggestions List -->
    <?php if (empty($suggestions)): ?>
        <div class="alert" style="background: var(--bg-light); padding: 2rem; text-align: center;">
            <p style="margin: 0; color: var(--text-light);">
                <?php if ($statusFilter === 'pending'): ?>
                    No pending suggestions at the moment.
                <?php else: ?>
                    No suggestions found for this filter.
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <?php foreach ($suggestions as $suggestion): ?>
                <div class="card" style="border-left: 4px solid <?php 
                    echo $suggestion['status'] === 'approved' ? '#10b981' : 
                        ($suggestion['status'] === 'rejected' ? '#ef4444' : '#3b82f6'); 
                ?>; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 300px;">
                            <h3 style="margin-top: 0; color: var(--primary-color);">
                                <?php echo htmlspecialchars($suggestion['term']); ?>
                            </h3>
                            <p style="color: var(--text-color); line-height: 1.6; margin: 0.75rem 0;">
                                <?php echo nl2br(htmlspecialchars($suggestion['definition'])); ?>
                            </p>
                            
                            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color); font-size: 0.9rem; color: var(--text-light);">
                                <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                                    <div>
                                        <strong>Suggested by:</strong> 
                                        <?php 
                                        if ($suggestion['first_name']) {
                                            echo htmlspecialchars($suggestion['first_name'] . ' ' . $suggestion['last_name']);
                                            if ($suggestion['email']) {
                                                echo ' (' . htmlspecialchars($suggestion['email']) . ')';
                                            }
                                        } else {
                                            echo 'Unknown user';
                                        }
                                        ?>
                                    </div>
                                    <div>
                                        <strong>Submitted:</strong> 
                                        <?php echo date('d M Y, H:i', strtotime($suggestion['created_at'])); ?>
                                    </div>
                                    <div>
                                        <strong>Status:</strong> 
                                        <span style="
                                            padding: 0.25rem 0.5rem; 
                                            border-radius: 0.25rem; 
                                            background: <?php 
                                                echo $suggestion['status'] === 'approved' ? '#d1fae5' : 
                                                    ($suggestion['status'] === 'rejected' ? '#fee2e2' : '#dbeafe'); 
                                            ?>; 
                                            color: <?php 
                                                echo $suggestion['status'] === 'approved' ? '#065f46' : 
                                                    ($suggestion['status'] === 'rejected' ? '#991b1b' : '#1e40af'); 
                                            ?>;
                                        ">
                                            <?php echo ucfirst($suggestion['status']); ?>
                                        </span>
                                    </div>
                                    <?php if ($suggestion['reviewed_by']): ?>
                                        <div>
                                            <strong>Reviewed by:</strong> 
                                            <?php echo htmlspecialchars($suggestion['reviewer_first_name'] . ' ' . $suggestion['reviewer_last_name']); ?>
                                            <?php if ($suggestion['reviewed_at']): ?>
                                                on <?php echo date('d M Y, H:i', strtotime($suggestion['reviewed_at'])); ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($suggestion['notes']): ?>
                                    <div style="margin-top: 0.75rem; padding: 0.75rem; background: var(--bg-light); border-radius: 0.25rem;">
                                        <strong>Review Notes:</strong>
                                        <p style="margin: 0.25rem 0 0 0;"><?php echo nl2br(htmlspecialchars($suggestion['notes'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($suggestion['status'] === 'pending'): ?>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; min-width: 200px;">
                                <button 
                                    type="button"
                                    class="btn btn-primary approve-btn"
                                    style="width: 100%;"
                                    data-id="<?php echo htmlspecialchars($suggestion['id']); ?>"
                                    data-action="approve"
                                    data-term="<?php echo htmlspecialchars($suggestion['term']); ?>"
                                    data-definition="<?php echo htmlspecialchars($suggestion['definition']); ?>"
                                >
                                    <i class="fa-solid fa-check" style="margin-right: 0.5rem;"></i>
                                    Approve
                                </button>
                                <button 
                                    type="button"
                                    class="btn btn-secondary reject-btn"
                                    style="width: 100%;"
                                    data-id="<?php echo htmlspecialchars($suggestion['id']); ?>"
                                    data-action="reject"
                                    data-term="<?php echo htmlspecialchars($suggestion['term']); ?>"
                                    data-definition="<?php echo htmlspecialchars($suggestion['definition']); ?>"
                                >
                                    <i class="fa-solid fa-times" style="margin-right: 0.5rem;"></i>
                                    Reject
                                </button>
                                <button 
                                    onclick="if(confirm('Are you sure you want to delete this suggestion? This cannot be undone.')) { document.getElementById('delete_form_<?php echo $suggestion['id']; ?>').submit(); }"
                                    class="btn btn-secondary"
                                    style="width: 100%; background: #ef4444; color: white;"
                                >
                                    <i class="fa-solid fa-trash" style="margin-right: 0.5rem;"></i>
                                    Delete
                                </button>
                                
                                <form id="delete_form_<?php echo $suggestion['id']; ?>" method="POST" style="display: none;">
                                    <?php echo CSRF::tokenField(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="suggestion_id" value="<?php echo $suggestion['id']; ?>">
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Review Modal -->
<div id="reviewModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Review Suggestion</h3>
            <button class="modal-close" onclick="closeReviewModal()" aria-label="Close">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="modalPreview" style="margin-bottom: 1.5rem; padding: 1rem; background: var(--bg-light); border-radius: 0.25rem;">
                <p><strong>Term:</strong> <span id="previewTerm"></span></p>
                <p><strong>Definition:</strong></p>
                <p id="previewDefinition" style="margin-left: 1rem; color: var(--text-light);"></p>
            </div>
            
            <form method="POST" id="reviewForm" onsubmit="return validateReviewForm()">
                <?php echo CSRF::tokenField(); ?>
                <input type="hidden" name="action" id="reviewAction">
                <input type="hidden" name="suggestion_id" id="reviewSuggestionId">
                <input type="hidden" name="term" id="reviewTerm">
                <input type="hidden" name="definition" id="reviewDefinition">
                
                <div class="form-group">
                    <label for="reviewNotes" id="reviewNotesLabel">Review Notes</label>
                    <textarea 
                        id="reviewNotes" 
                        name="notes" 
                        class="form-control" 
                        rows="4" 
                        placeholder=""
                    ></textarea>
                    <small style="color: var(--text-light);" id="reviewNotesHelp">
                        <!-- Help text will be set by JavaScript based on action -->
                    </small>
                </div>
                
                <div class="form-group" style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeReviewModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitReviewBtn">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.modal-content {
    background-color: var(--bg-color);
    margin: 5% auto;
    padding: 0;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 10px 20px rgba(0, 0, 0, 0.15);
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.05), rgba(30, 64, 175, 0.05));
}

.modal-header h3 {
    margin: 0;
    color: var(--primary-color);
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: var(--text-light);
    cursor: pointer;
    padding: 0;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.25rem;
    transition: all 0.2s;
}

.modal-close:hover {
    background-color: var(--bg-light);
    color: var(--text-color);
}

.modal-body {
    padding: 1.5rem;
}
</style>

<script>
// Ensure functions are available globally
window.openReviewModal = function(id, action, term, definition) {
    console.log('openReviewModal called with:', {id, action, term, definition});
    try {
        const modal = document.getElementById('reviewModal');
        if (!modal) {
            console.error('Modal element not found');
            alert('Error: Modal not found. Please refresh the page.');
            return;
        }
        
        const form = document.getElementById('reviewForm');
        const title = document.getElementById('modalTitle');
        const submitBtn = document.getElementById('submitReviewBtn');
        const notesLabel = document.getElementById('reviewNotesLabel');
        const notesHelp = document.getElementById('reviewNotesHelp');
        const notesField = document.getElementById('reviewNotes');
        
        if (!form || !title || !submitBtn || !notesLabel || !notesHelp || !notesField) {
            console.error('Required modal elements not found');
            alert('Error: Some form elements are missing. Please refresh the page.');
            return;
        }
        
        // Set form values
        document.getElementById('reviewAction').value = action;
        document.getElementById('reviewSuggestionId').value = id;
        document.getElementById('reviewTerm').value = term;
        document.getElementById('reviewDefinition').value = definition;
        document.getElementById('previewTerm').textContent = term;
        document.getElementById('previewDefinition').textContent = definition;
        notesField.value = '';
        
        // Update UI based on action
        if (action === 'approve') {
            title.textContent = 'Approve Suggestion';
            submitBtn.textContent = 'Approve';
            submitBtn.className = 'btn btn-primary';
            notesLabel.innerHTML = 'Review Notes <span style="color: var(--text-light); font-weight: normal;">(optional)</span>';
            notesField.placeholder = 'Add any notes about this approval (optional)...';
            notesHelp.textContent = 'Optional: Add notes about where to add the term in the glossary or any other relevant information.';
            notesField.removeAttribute('required');
        } else {
            title.textContent = 'Reject Suggestion';
            submitBtn.textContent = 'Reject';
            submitBtn.className = 'btn btn-secondary';
            notesLabel.innerHTML = 'Rejection Reason <span style="color: #ef4444;">*</span>';
            notesField.placeholder = 'Please provide a reason for rejection. This will be sent to the user...';
            notesHelp.textContent = 'Required: This reason will be emailed to the user who suggested the term.';
            notesField.setAttribute('required', 'required');
        }
        
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        setTimeout(() => {
            notesField.focus();
        }, 100);
    } catch (error) {
        console.error('Error opening modal:', error);
        alert('Error opening review modal. Please check the browser console for details.');
    }
}

window.closeReviewModal = function() {
    const modal = document.getElementById('reviewModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('reviewModal');
    if (event.target === modal) {
        closeReviewModal();
    }
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeReviewModal();
    }
});

// Validate form before submission
window.validateReviewForm = function() {
    const action = document.getElementById('reviewAction').value;
    const notes = document.getElementById('reviewNotes').value.trim();
    
    if (action === 'reject' && !notes) {
        alert('Please provide a reason for rejection. This will be sent to the user who suggested the term.');
        document.getElementById('reviewNotes').focus();
        return false;
    }
    
    return true;
}

// Use event delegation for buttons
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('reviewModal');
    if (modal) {
        console.log('Review modal found and ready');
    } else {
        console.error('Review modal NOT found in DOM');
    }
    
    // Add click handlers to approve/reject buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.approve-btn') || e.target.closest('.reject-btn')) {
            const btn = e.target.closest('.approve-btn') || e.target.closest('.reject-btn');
            const id = btn.getAttribute('data-id');
            const action = btn.getAttribute('data-action');
            const term = btn.getAttribute('data-term');
            const definition = btn.getAttribute('data-definition');
            
            console.log('Button clicked:', {id, action, term, definition});
            openReviewModal(parseInt(id), action, term, definition);
        }
    });
});
</script>

<?php include INCLUDES_PATH . '/footer.php'; ?>
