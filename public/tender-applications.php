<?php
/**
 * Tender Applications List
 * View and manage all tender applications
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
RBAC::requireOrganisationAdmin();

$organisationId = Auth::getOrganisationId();
$statusFilter = $_GET['status'] ?? null;

// Get tender applications
$tenderApplications = TenderApplication::findByOrganisation($organisationId, $statusFilter);

$pageTitle = 'Tender Applications';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-between">
            <div>
                <h2>Tender Applications</h2>
                <p>Manage your organisation's tender applications</p>
            </div>
            <a href="<?php echo url('tender-application.php'); ?>" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> New Application
            </a>
        </div>
    </div>
    
    <!-- Filters -->
    <div style="margin-bottom: 1.5rem;">
        <a href="<?php echo url('tender-applications.php'); ?>" 
           class="btn <?php echo !$statusFilter ? 'btn-primary' : 'btn-secondary'; ?>">
            All
        </a>
        <a href="<?php echo url('tender-applications.php?status=draft'); ?>" 
           class="btn <?php echo $statusFilter === 'draft' ? 'btn-primary' : 'btn-secondary'; ?>">
            Draft
        </a>
        <a href="<?php echo url('tender-applications.php?status=submitted'); ?>" 
           class="btn <?php echo $statusFilter === 'submitted' ? 'btn-primary' : 'btn-secondary'; ?>">
            Submitted
        </a>
        <a href="<?php echo url('tender-applications.php?status=under_review'); ?>" 
           class="btn <?php echo $statusFilter === 'under_review' ? 'btn-primary' : 'btn-secondary'; ?>">
            Under Review
        </a>
        <a href="<?php echo url('tender-applications.php?status=awarded'); ?>" 
           class="btn <?php echo $statusFilter === 'awarded' ? 'btn-primary' : 'btn-secondary'; ?>">
            Awarded
        </a>
    </div>
    
    <?php if (empty($tenderApplications)): ?>
        <div style="padding: 3rem; text-align: center; color: var(--text-light);">
            <i class="fa-solid fa-file-lines" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
            <p style="font-size: 1.1rem; margin-bottom: 0.5rem;">No tender applications found</p>
            <p style="margin-bottom: 1.5rem;">Create your first tender application to get started.</p>
            <a href="<?php echo url('tender-application.php'); ?>" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> New Application
            </a>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Local Authority</th>
                        <th>Contract Type</th>
                        <th>Status</th>
                        <th>Submission Deadline</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tenderApplications as $app): ?>
                        <tr onclick="window.location.href='<?php echo htmlspecialchars(url('tender-application.php?id=' . $app['id'])); ?>'" style="cursor: pointer; transition: background-color 0.2s;">
                            <td>
                                <strong><?php echo htmlspecialchars($app['title']); ?></strong>
                                <?php if ($app['tender_reference']): ?>
                                    <br><small style="color: var(--text-light);">Ref: <?php echo htmlspecialchars($app['tender_reference']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($app['local_authority_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($app['contract_type_name'] ?? 'N/A'); ?></td>
                            <td>
                                <?php
                                $statusColors = [
                                    'draft' => 'var(--text-light)',
                                    'submitted' => 'var(--info-color)',
                                    'under_review' => 'var(--warning-color)',
                                    'awarded' => 'var(--success-color)',
                                    'lost' => 'var(--danger-color)',
                                    'withdrawn' => 'var(--text-light)'
                                ];
                                $color = $statusColors[$app['status']] ?? 'var(--text-light)';
                                ?>
                                <span style="color: <?php echo $color; ?>; font-weight: 600;">
                                    <?php echo ucwords(str_replace('_', ' ', strtolower($app['status'] ?? ''))); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($app['submission_deadline']): ?>
                                    <?php 
                                    $deadline = new DateTime($app['submission_deadline']);
                                    $now = new DateTime();
                                    $diff = $now->diff($deadline);
                                    $isPast = $deadline < $now;
                                    $color = $isPast ? 'var(--danger-color)' : ($diff->days < 7 ? 'var(--warning-color)' : 'var(--text-color)');
                                    ?>
                                    <span style="color: <?php echo $color; ?>;">
                                        <?php echo date('d M Y', strtotime($app['submission_deadline'])); ?>
                                        <?php if (!$isPast && $diff->days < 7): ?>
                                            <br><small>(<?php echo $diff->days; ?> days left)</small>
                                        <?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--text-light);">Not set</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($app['submitted_at']): ?>
                                    <?php echo date('d M Y', strtotime($app['submitted_at'])); ?>
                                <?php else: ?>
                                    <span style="color: var(--text-light);">Not submitted</span>
                                <?php endif; ?>
                            </td>
                            <td onclick="event.stopPropagation();">
                                <?php if ($app['status'] === 'draft'): ?>
                                    <form method="POST" action="<?php echo url('tender-application.php?id=' . $app['id']); ?>" 
                                          style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to delete this tender application?');">
                                        <?php echo CSRF::tokenField(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: var(--text-light);">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

