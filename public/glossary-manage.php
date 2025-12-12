<?php
/**
 * Glossary Terms Management
 * Admin page for managing glossary terms (remove with reason tracking)
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
RBAC::requireSuperAdmin(); // Only superadmins can manage glossary terms

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'remove_term') {
            $termId = intval($_POST['term_id'] ?? 0);
            $removalReason = trim($_POST['removal_reason'] ?? '');
            
            if ($termId <= 0) {
                $error = 'Invalid term ID.';
            } elseif (empty($removalReason)) {
                $error = 'Please provide a reason for removing this term.';
            } else {
                try {
                    $db = getDbConnection();
                    $stmt = $db->prepare("
                        UPDATE glossary_terms 
                        SET is_active = FALSE,
                            removed_at = NOW(),
                            removed_by = ?,
                            removal_reason = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([Auth::getUserId(), $removalReason, $termId]);
                    $success = 'Glossary term removed successfully.';
                } catch (Exception $e) {
                    $error = 'Error removing term: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'restore_term') {
            $termId = intval($_POST['term_id'] ?? 0);
            
            if ($termId <= 0) {
                $error = 'Invalid term ID.';
            } else {
                try {
                    $db = getDbConnection();
                    $stmt = $db->prepare("
                        UPDATE glossary_terms 
                        SET is_active = TRUE,
                            removed_at = NULL,
                            removed_by = NULL,
                            removal_reason = NULL
                        WHERE id = ?
                    ");
                    $stmt->execute([$termId]);
                    $success = 'Glossary term restored successfully.';
                } catch (Exception $e) {
                    $error = 'Error restoring term: ' . $e->getMessage();
                }
            }
        }
    }
}

// Get glossary terms
$db = getDbConnection();
$statusFilter = $_GET['status'] ?? 'active';

$query = "
    SELECT gt.*, 
           remover.first_name as remover_first_name,
           remover.last_name as remover_last_name
    FROM glossary_terms gt
    LEFT JOIN users remover ON gt.removed_by = remover.id
";

if ($statusFilter === 'active') {
    $query .= " WHERE gt.is_active = TRUE";
} elseif ($statusFilter === 'removed') {
    $query .= " WHERE gt.is_active = FALSE";
}

$query .= " ORDER BY gt.letter, gt.term";

$stmt = $db->prepare($query);
$stmt->execute();
$terms = $stmt->fetchAll();

// Count by status
$stmt = $db->query("
    SELECT 
        SUM(CASE WHEN is_active = TRUE THEN 1 ELSE 0 END) as active_count,
        SUM(CASE WHEN is_active = FALSE THEN 1 ELSE 0 END) as removed_count
    FROM glossary_terms
");
$counts = $stmt->fetch();

$pageTitle = 'Manage Glossary Terms';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <h1>Manage Glossary Terms</h1>
        <p style="color: var(--text-light); margin-top: 0.5rem;">
            Manage glossary terms, including removing terms with reason tracking
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
        <a href="?status=active" class="btn <?php echo $statusFilter === 'active' ? 'btn-primary' : 'btn-secondary'; ?>" style="text-decoration: none;">
            Active Terms
            <?php if (isset($counts['active_count'])): ?>
                <span style="background: rgba(255,255,255,0.2); padding: 0.125rem 0.5rem; border-radius: 1rem; margin-left: 0.25rem;">
                    <?php echo $counts['active_count']; ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="?status=removed" class="btn <?php echo $statusFilter === 'removed' ? 'btn-primary' : 'btn-secondary'; ?>" style="text-decoration: none;">
            Removed Terms
            <?php if (isset($counts['removed_count'])): ?>
                <span style="background: rgba(255,255,255,0.2); padding: 0.125rem 0.5rem; border-radius: 1rem; margin-left: 0.25rem;">
                    <?php echo $counts['removed_count']; ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="?status=all" class="btn <?php echo $statusFilter === 'all' ? 'btn-primary' : 'btn-secondary'; ?>" style="text-decoration: none;">
            All Terms
        </a>
    </div>
    
    <!-- Terms List -->
    <?php if (empty($terms)): ?>
        <div class="alert" style="background: var(--bg-light); padding: 2rem; text-align: center;">
            <p style="margin: 0; color: var(--text-light);">
                <?php if ($statusFilter === 'active'): ?>
                    No active glossary terms found. Terms will appear here once suggestions are approved.
                <?php else: ?>
                    No terms found for this filter.
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <?php 
            $currentLetter = '';
            foreach ($terms as $term): 
                if ($currentLetter !== $term['letter']):
                    if ($currentLetter !== ''):
                        echo '</section>';
                    endif;
                    $currentLetter = $term['letter'];
                    echo '<section style="margin-bottom: 2rem;">';
                    echo '<h2 style="color: var(--primary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem; margin-bottom: 1rem;">' . htmlspecialchars($currentLetter) . '</h2>';
                endif;
            ?>
                <div class="card" style="border-left: 4px solid <?php echo $term['is_active'] ? '#10b981' : '#ef4444'; ?>; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 300px;">
                            <h3 style="margin-top: 0; color: var(--primary-color);">
                                <?php echo htmlspecialchars($term['term']); ?>
                            </h3>
                            <p style="color: var(--text-color); line-height: 1.6; margin: 0.75rem 0;">
                                <?php echo nl2br(htmlspecialchars($term['definition'])); ?>
                            </p>
                            
                            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color); font-size: 0.9rem; color: var(--text-light);">
                                <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                                    <div>
                                        <strong>Status:</strong> 
                                        <span style="
                                            padding: 0.25rem 0.5rem; 
                                            border-radius: 0.25rem; 
                                            background: <?php echo $term['is_active'] ? '#d1fae5' : '#fee2e2'; ?>; 
                                            color: <?php echo $term['is_active'] ? '#065f46' : '#991b1b'; ?>;
                                        ">
                                            <?php echo $term['is_active'] ? 'Active' : 'Removed'; ?>
                                        </span>
                                    </div>
                                    <div>
                                        <strong>Created:</strong> 
                                        <?php echo date('d M Y', strtotime($term['created_at'])); ?>
                                    </div>
                                    <?php if (!$term['is_active'] && $term['removed_at']): ?>
                                        <div>
                                            <strong>Removed:</strong> 
                                            <?php echo date('d M Y, H:i', strtotime($term['removed_at'])); ?>
                                        </div>
                                        <?php if ($term['remover_first_name']): ?>
                                            <div>
                                                <strong>Removed by:</strong> 
                                                <?php echo htmlspecialchars($term['remover_first_name'] . ' ' . $term['remover_last_name']); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!$term['is_active'] && $term['removal_reason']): ?>
                                    <div style="margin-top: 0.75rem; padding: 0.75rem; background: #fee2e2; border-radius: 0 0.25rem 0.25rem 0; border-left: 4px solid #ef4444;">
                                        <strong>Removal Reason:</strong>
                                        <p style="margin: 0.25rem 0 0 0;"><?php echo nl2br(htmlspecialchars($term['removal_reason'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($term['is_active']): ?>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; min-width: 200px;">
                                <button 
                                    onclick="openRemoveModal(<?php echo $term['id']; ?>, <?php echo json_encode($term['term']); ?>)"
                                    class="btn btn-secondary"
                                    style="width: 100%; background: #ef4444; color: white;"
                                >
                                    <i class="fa-solid fa-trash" style="margin-right: 0.5rem;"></i>
                                    Remove Term
                                </button>
                            </div>
                        <?php else: ?>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; min-width: 200px;">
                                <form method="POST" style="margin: 0;">
                                    <?php echo CSRF::tokenField(); ?>
                                    <input type="hidden" name="action" value="restore_term">
                                    <input type="hidden" name="term_id" value="<?php echo $term['id']; ?>">
                                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                                        <i class="fa-solid fa-rotate-left" style="margin-right: 0.5rem;"></i>
                                        Restore Term
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php 
            endforeach;
            if ($currentLetter !== ''):
                echo '</section>';
            endif;
            ?>
        </div>
    <?php endif; ?>
</div>

<!-- Remove Term Modal -->
<div id="removeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Remove Glossary Term</h3>
            <button class="modal-close" onclick="closeRemoveModal()" aria-label="Close">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="removePreview" style="margin-bottom: 1.5rem; padding: 1rem; background: var(--bg-light); border-radius: 0.25rem;">
                <p><strong>Term:</strong> <span id="removeTermName"></span></p>
            </div>
            
            <form method="POST" id="removeForm">
                <?php echo CSRF::tokenField(); ?>
                <input type="hidden" name="action" value="remove_term">
                <input type="hidden" name="term_id" id="removeTermId">
                
                <div class="form-group">
                    <label for="removalReason">Removal Reason <span style="color: #ef4444;">*</span></label>
                    <textarea 
                        id="removalReason" 
                        name="removal_reason" 
                        class="form-control" 
                        rows="4" 
                        required
                        placeholder="Please provide a reason for removing this term from the glossary..."
                    ></textarea>
                    <small style="color: var(--text-light);">
                        This reason will be recorded for audit purposes and to help understand why terms were removed.
                    </small>
                </div>
                
                <div class="form-group" style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeRemoveModal()">Cancel</button>
                    <button type="submit" class="btn btn-secondary" style="background: #ef4444; color: white;">Remove Term</button>
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
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.05), rgba(220, 38, 38, 0.05));
}

.modal-header h3 {
    margin: 0;
    color: #ef4444;
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
function openRemoveModal(id, term) {
    const modal = document.getElementById('removeModal');
    document.getElementById('removeTermId').value = id;
    document.getElementById('removeTermName').textContent = term;
    document.getElementById('removalReason').value = '';
    
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    setTimeout(() => {
        document.getElementById('removalReason').focus();
    }, 100);
}

function closeRemoveModal() {
    const modal = document.getElementById('removeModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('removeModal');
    if (event.target === modal) {
        closeRemoveModal();
    }
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRemoveModal();
    }
});
</script>

<?php include INCLUDES_PATH . '/footer.php'; ?>
