<?php
/**
 * Reference Rate Monitoring Dashboard
 * Allows admins to monitor and validate reference rates
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
RBAC::requireAdmin();

// Handle warning dismissal
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $action = $_POST['action'];
        if ($action === 'dismiss_warning') {
            $rateType = $_POST['rate_type'] ?? '';
            $warningKey = $_POST['warning_key'] ?? '';
            $expiresInDays = isset($_POST['expires_in_days']) ? intval($_POST['expires_in_days']) : 30;
            
            if ($rateType && $warningKey) {
                if (LocalAuthorityRateInfo::dismissWarning($rateType, $warningKey, $expiresInDays)) {
                    $success = 'Warning dismissed. It will reappear after ' . $expiresInDays . ' days if the issue persists.';
                } else {
                    $error = 'Failed to dismiss warning.';
                }
            }
        } elseif ($action === 'undismiss_warning') {
            $dismissalId = $_POST['dismissal_id'] ?? 0;
            if ($dismissalId && LocalAuthorityRateInfo::undismissWarning($dismissalId)) {
                $success = 'Warning restored.';
            } else {
                $error = 'Failed to restore warning.';
            }
        }
    }
}

$monitoringStatus = LocalAuthorityRateInfo::getRateMonitoringStatus();
$validationSummary = LocalAuthorityRateInfo::getRateValidationSummary();

$pageTitle = 'Reference Rate Monitoring';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Reference Rate Monitoring</h2>
        <p style="color: var(--text-light); margin-top: 0.5rem;">
            Monitor and validate current reference rates to ensure accuracy and currency
        </p>
    </div>
    
    <!-- Overall Status Alert -->
    <?php if ($monitoringStatus['overall_status'] === 'error'): ?>
        <div class="alert alert-error">
            <strong><i class="fa-solid fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>Critical Issues Found</strong>
            <ul style="margin: 0.5rem 0 0 1.5rem;">
                <?php foreach ($monitoringStatus['errors'] as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php elseif ($monitoringStatus['overall_status'] === 'warning'): ?>
        <div class="alert alert-warning">
            <div style="display: flex; justify-content: space-between; align-items: start; gap: 1rem;">
                <div style="flex: 1;">
                    <strong><i class="fa-solid fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>Warnings</strong>
                    <ul style="margin: 0.5rem 0 0 1.5rem;">
                        <?php foreach ($monitoringStatus['warnings'] as $warning): 
                            $warningMessage = is_array($warning) ? $warning['message'] : $warning;
                            $rateType = is_array($warning) ? $warning['rate_type'] : '';
                            $warningKey = is_array($warning) ? $warning['warning_key'] : '';
                        ?>
                            <li style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <span><?php echo htmlspecialchars($warningMessage); ?></span>
                                <?php if ($rateType && $warningKey): ?>
                                    <form method="POST" action="" style="display: inline; margin-left: 0.5rem;">
                                        <?php echo CSRF::tokenField(); ?>
                                        <input type="hidden" name="action" value="dismiss_warning">
                                        <input type="hidden" name="rate_type" value="<?php echo htmlspecialchars($rateType); ?>">
                                        <input type="hidden" name="warning_key" value="<?php echo htmlspecialchars($warningKey); ?>">
                                        <input type="hidden" name="expires_in_days" value="30">
                                        <button type="submit" class="btn btn-sm" style="padding: 0.25rem 0.75rem; font-size: 0.85rem; background: var(--warning-color); color: white; border: none; border-radius: 0.25rem; cursor: pointer;">
                                            Dismiss (30 days)
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-success">
            <strong><i class="fa-solid fa-check-circle" style="margin-right: 0.5rem; color: #10b981;"></i>All rates are current and valid</strong>
        </div>
    <?php endif; ?>
    
    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 2rem 0;">
        <div class="card" style="text-align: center; margin: 0;">
            <h3 style="margin: 0; color: var(--primary-color); font-size: 2rem;"><?php echo $validationSummary['current_rates']; ?>/<?php echo $validationSummary['total_rates']; ?></h3>
            <p style="margin: 0.5rem 0 0 0; color: var(--text-light);">Current Rates</p>
        </div>
        
        <div class="card" style="text-align: center; margin: 0;">
            <h3 style="margin: 0; color: <?php echo $validationSummary['outdated_rates'] > 0 ? 'var(--warning-color)' : 'var(--success-color)'; ?>; font-size: 2rem;">
                <?php echo $validationSummary['outdated_rates']; ?>
            </h3>
            <p style="margin: 0.5rem 0 0 0; color: var(--text-light);">Need Review</p>
        </div>
        
        <div class="card" style="text-align: center; margin: 0;">
            <h3 style="margin: 0; color: <?php echo $validationSummary['missing_rates'] > 0 ? 'var(--danger-color)' : 'var(--success-color)'; ?>; font-size: 2rem;">
                <?php echo $validationSummary['missing_rates']; ?>
            </h3>
            <p style="margin: 0.5rem 0 0 0; color: var(--text-light);">Missing</p>
        </div>
        
        <?php if ($validationSummary['last_updated']): ?>
            <div class="card" style="text-align: center; margin: 0;">
                <h3 style="margin: 0; color: var(--text-color); font-size: 1.25rem;">
                    <?php echo date('d M Y', strtotime($validationSummary['last_updated'])); ?>
                </h3>
                <p style="margin: 0.5rem 0 0 0; color: var(--text-light);">Last Updated</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Detailed Rate Status -->
    <h3 style="margin: 2rem 0 1rem;">Rate Status Details</h3>
    
    <div style="display: grid; gap: 1.5rem;">
        <!-- Scotland Mandated Rate -->
        <div class="card" style="margin: 0;">
            <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 1rem;">
                <div style="flex: 1;">
                    <h4 style="margin: 0 0 0.5rem 0; display: flex; align-items: center; gap: 0.5rem;">
                        <span style="
                            display: inline-block;
                            width: 12px;
                            height: 12px;
                            border-radius: 50%;
                            background-color: <?php 
                                echo $monitoringStatus['scotland_rate']['status'] === 'error' ? 'var(--danger-color)' : 
                                    ($monitoringStatus['scotland_rate']['status'] === 'warning' ? 'var(--warning-color)' : 'var(--success-color)'); 
                            ?>;
                        "></span>
                        Scotland Mandated Minimum Rate
                    </h4>
                    <?php if ($monitoringStatus['scotland_rate']['current']): ?>
                        <p style="margin: 0.5rem 0; font-size: 1.5rem; font-weight: bold; color: var(--primary-color);">
                            £<?php echo number_format($monitoringStatus['scotland_rate']['current']['rate'], 2); ?>/hr
                        </p>
                        <p style="margin: 0.25rem 0; color: var(--text-light); font-size: 0.9rem;">
                            Effective: <?php echo date('d M Y', strtotime($monitoringStatus['scotland_rate']['current']['effective_date'])); ?>
                        </p>
                        <?php if (!empty($monitoringStatus['scotland_rate']['current']['applies_to'])): ?>
                            <p style="margin: 0.25rem 0; color: var(--text-light); font-size: 0.9rem;">
                                Applies to: <?php echo htmlspecialchars($monitoringStatus['scotland_rate']['current']['applies_to']); ?>
                            </p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p style="color: var(--danger-color); margin: 0.5rem 0;">No rate found</p>
                    <?php endif; ?>
                    <?php if ($monitoringStatus['scotland_rate']['message']): ?>
                        <div style="margin: 0.5rem 0 0 0; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            <p style="margin: 0; color: <?php 
                                echo $monitoringStatus['scotland_rate']['status'] === 'error' ? 'var(--danger-color)' : 'var(--warning-color)'; 
                            ?>; font-size: 0.9rem; flex: 1;">
                                <?php echo htmlspecialchars($monitoringStatus['scotland_rate']['message']); ?>
                            </p>
                            <?php if ($monitoringStatus['scotland_rate']['status'] === 'warning' && !isset($monitoringStatus['scotland_rate']['dismissed'])): 
                                $current = $monitoringStatus['scotland_rate']['current'] ?? null;
                                $rateId = $current['id'] ?? 0;
                                $message = $monitoringStatus['scotland_rate']['message'] ?? '';
                                $warningKey = md5('scotland_rate' . '|' . $rateId . '|' . $message);
                            ?>
                                <form method="POST" action="" style="display: inline;">
                                    <?php echo CSRF::tokenField(); ?>
                                    <input type="hidden" name="action" value="dismiss_warning">
                                    <input type="hidden" name="rate_type" value="scotland_rate">
                                    <input type="hidden" name="warning_key" value="<?php echo htmlspecialchars($warningKey); ?>">
                                    <input type="hidden" name="expires_in_days" value="30">
                                    <button type="submit" class="btn btn-sm" style="padding: 0.25rem 0.75rem; font-size: 0.85rem; background: var(--warning-color); color: white; border: none; border-radius: 0.25rem; cursor: pointer; white-space: nowrap;">
                                        Dismiss
                                    </button>
                                </form>
                            <?php elseif (isset($monitoringStatus['scotland_rate']['dismissed']) && isset($monitoringStatus['scotland_rate']['dismissal_id'])): ?>
                                <form method="POST" action="" style="display: inline;">
                                    <?php echo CSRF::tokenField(); ?>
                                    <input type="hidden" name="action" value="undismiss_warning">
                                    <input type="hidden" name="dismissal_id" value="<?php echo $monitoringStatus['scotland_rate']['dismissal_id']; ?>">
                                    <button type="submit" class="btn btn-sm" style="padding: 0.25rem 0.75rem; font-size: 0.85rem; background: var(--text-light); color: white; border: none; border-radius: 0.25rem; cursor: pointer; white-space: nowrap;">
                                        Show Again
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <a href="<?php echo htmlspecialchars(url('pages/local-authority-rates.php')); ?>" class="btn btn-secondary">
                        View Details
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Real Living Wage -->
        <div class="card" style="margin: 0;">
            <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 1rem;">
                <div style="flex: 1;">
                    <h4 style="margin: 0 0 0.5rem 0; display: flex; align-items: center; gap: 0.5rem;">
                        <span style="
                            display: inline-block;
                            width: 12px;
                            height: 12px;
                            border-radius: 50%;
                            background-color: <?php 
                                echo $monitoringStatus['rlw_rate']['status'] === 'error' ? 'var(--danger-color)' : 
                                    ($monitoringStatus['rlw_rate']['status'] === 'warning' ? 'var(--warning-color)' : 'var(--success-color)'); 
                            ?>;
                        "></span>
                        Real Living Wage (UK)
                    </h4>
                    <?php if ($monitoringStatus['rlw_rate']['current']): ?>
                        <p style="margin: 0.5rem 0; font-size: 1.5rem; font-weight: bold; color: var(--success-color);">
                            £<?php echo number_format($monitoringStatus['rlw_rate']['current']['uk_rate'], 2); ?>/hr
                        </p>
                        <p style="margin: 0.25rem 0; color: var(--text-light); font-size: 0.9rem;">
                            Effective: <?php echo date('d M Y', strtotime($monitoringStatus['rlw_rate']['current']['effective_date'])); ?>
                        </p>
                        <?php if ($monitoringStatus['rlw_rate']['current']['scotland_rate']): ?>
                            <p style="margin: 0.25rem 0; color: var(--text-light); font-size: 0.9rem;">
                                Scotland Rate: £<?php echo number_format($monitoringStatus['rlw_rate']['current']['scotland_rate'], 2); ?>/hr
                            </p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p style="color: var(--danger-color); margin: 0.5rem 0;">No rate found</p>
                    <?php endif; ?>
                    <?php if ($monitoringStatus['rlw_rate']['message']): ?>
                        <div style="margin: 0.5rem 0 0 0; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            <p style="margin: 0; color: <?php 
                                echo $monitoringStatus['rlw_rate']['status'] === 'error' ? 'var(--danger-color)' : 'var(--warning-color)'; 
                            ?>; font-size: 0.9rem; flex: 1;">
                                <?php echo htmlspecialchars($monitoringStatus['rlw_rate']['message']); ?>
                            </p>
                            <?php if ($monitoringStatus['rlw_rate']['status'] === 'warning' && !isset($monitoringStatus['rlw_rate']['dismissed'])): 
                                $current = $monitoringStatus['rlw_rate']['current'] ?? null;
                                $rateId = $current['id'] ?? 0;
                                $message = $monitoringStatus['rlw_rate']['message'] ?? '';
                                $warningKey = md5('rlw_rate' . '|' . $rateId . '|' . $message);
                            ?>
                                <form method="POST" action="" style="display: inline;">
                                    <?php echo CSRF::tokenField(); ?>
                                    <input type="hidden" name="action" value="dismiss_warning">
                                    <input type="hidden" name="rate_type" value="rlw_rate">
                                    <input type="hidden" name="warning_key" value="<?php echo htmlspecialchars($warningKey); ?>">
                                    <input type="hidden" name="expires_in_days" value="30">
                                    <button type="submit" class="btn btn-sm" style="padding: 0.25rem 0.75rem; font-size: 0.85rem; background: var(--warning-color); color: white; border: none; border-radius: 0.25rem; cursor: pointer; white-space: nowrap;">
                                        Dismiss
                                    </button>
                                </form>
                            <?php elseif (isset($monitoringStatus['rlw_rate']['dismissed']) && isset($monitoringStatus['rlw_rate']['dismissal_id'])): ?>
                                <form method="POST" action="" style="display: inline;">
                                    <?php echo CSRF::tokenField(); ?>
                                    <input type="hidden" name="action" value="undismiss_warning">
                                    <input type="hidden" name="dismissal_id" value="<?php echo $monitoringStatus['rlw_rate']['dismissal_id']; ?>">
                                    <button type="submit" class="btn btn-sm" style="padding: 0.25rem 0.75rem; font-size: 0.85rem; background: var(--text-light); color: white; border: none; border-radius: 0.25rem; cursor: pointer; white-space: nowrap;">
                                        Show Again
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <a href="<?php echo htmlspecialchars(url('pages/local-authority-rates.php')); ?>" class="btn btn-secondary">
                        View Details
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Homecare Association Rate -->
        <div class="card" style="margin: 0;">
            <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 1rem;">
                <div style="flex: 1;">
                    <h4 style="margin: 0 0 0.5rem 0; display: flex; align-items: center; gap: 0.5rem;">
                        <span style="
                            display: inline-block;
                            width: 12px;
                            height: 12px;
                            border-radius: 50%;
                            background-color: <?php 
                                echo $monitoringStatus['hca_rate']['status'] === 'error' ? 'var(--danger-color)' : 
                                    ($monitoringStatus['hca_rate']['status'] === 'warning' ? 'var(--warning-color)' : 'var(--success-color)'); 
                            ?>;
                        "></span>
                        Homecare Association Rate
                    </h4>
                    <?php if ($monitoringStatus['hca_rate']['current']): ?>
                        <p style="margin: 0.5rem 0; font-size: 1.5rem; font-weight: bold; color: var(--primary-color);">
                            £<?php echo number_format($monitoringStatus['hca_rate']['current']['scotland_rate'], 2); ?>/hr
                        </p>
                        <p style="margin: 0.25rem 0; color: var(--text-light); font-size: 0.9rem;">
                            Period: <?php echo date('d M Y', strtotime($monitoringStatus['hca_rate']['current']['year_from'])); ?>
                            <?php if ($monitoringStatus['hca_rate']['current']['year_to']): ?>
                                - <?php echo date('d M Y', strtotime($monitoringStatus['hca_rate']['current']['year_to'])); ?>
                            <?php else: ?>
                                onwards
                            <?php endif; ?>
                        </p>
                    <?php else: ?>
                        <p style="color: var(--text-light); margin: 0.5rem 0;">No rate found (optional)</p>
                    <?php endif; ?>
                    <?php if ($monitoringStatus['hca_rate']['message']): ?>
                        <div style="margin: 0.5rem 0 0 0; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            <p style="margin: 0; color: <?php 
                                echo $monitoringStatus['hca_rate']['status'] === 'error' ? 'var(--danger-color)' : 'var(--warning-color)'; 
                            ?>; font-size: 0.9rem; flex: 1;">
                                <?php echo htmlspecialchars($monitoringStatus['hca_rate']['message']); ?>
                            </p>
                            <?php if ($monitoringStatus['hca_rate']['status'] === 'warning' && !isset($monitoringStatus['hca_rate']['dismissed'])): 
                                $current = $monitoringStatus['hca_rate']['current'] ?? null;
                                $rateId = $current['id'] ?? 0;
                                $message = $monitoringStatus['hca_rate']['message'] ?? '';
                                $warningKey = md5('hca_rate' . '|' . $rateId . '|' . $message);
                            ?>
                                <form method="POST" action="" style="display: inline;">
                                    <?php echo CSRF::tokenField(); ?>
                                    <input type="hidden" name="action" value="dismiss_warning">
                                    <input type="hidden" name="rate_type" value="hca_rate">
                                    <input type="hidden" name="warning_key" value="<?php echo htmlspecialchars($warningKey); ?>">
                                    <input type="hidden" name="expires_in_days" value="30">
                                    <button type="submit" class="btn btn-sm" style="padding: 0.25rem 0.75rem; font-size: 0.85rem; background: var(--warning-color); color: white; border: none; border-radius: 0.25rem; cursor: pointer; white-space: nowrap;">
                                        Dismiss
                                    </button>
                                </form>
                            <?php elseif (isset($monitoringStatus['hca_rate']['dismissed']) && isset($monitoringStatus['hca_rate']['dismissal_id'])): ?>
                                <form method="POST" action="" style="display: inline;">
                                    <?php echo CSRF::tokenField(); ?>
                                    <input type="hidden" name="action" value="undismiss_warning">
                                    <input type="hidden" name="dismissal_id" value="<?php echo $monitoringStatus['hca_rate']['dismissal_id']; ?>">
                                    <button type="submit" class="btn btn-sm" style="padding: 0.25rem 0.75rem; font-size: 0.85rem; background: var(--text-light); color: white; border: none; border-radius: 0.25rem; cursor: pointer; white-space: nowrap;">
                                        Show Again
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <a href="<?php echo htmlspecialchars(url('pages/local-authority-rates.php')); ?>" class="btn btn-secondary">
                        View Details
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
        <h3 style="margin-bottom: 1rem;">Quick Actions</h3>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="<?php echo htmlspecialchars(url('pages/local-authority-rates.php')); ?>" class="btn btn-primary">
                View All Rates
            </a>
            <a href="<?php echo htmlspecialchars(url('local-authority-updates.php')); ?>" class="btn btn-secondary">
                Manage Rate Updates
            </a>
            <?php if (RBAC::isSuperAdmin()): ?>
                <a href="<?php echo htmlspecialchars(url('superadmin.php')); ?>" class="btn btn-secondary">
                    Super Admin Panel
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Information Section -->
    <div style="margin-top: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem;">
        <h4 style="margin: 0 0 1rem 0;">About Rate Monitoring</h4>
        <ul style="margin: 0; padding-left: 1.5rem; color: var(--text-light);">
            <li><strong>Status Indicators:</strong> Green = Current, Yellow = Needs Review, Red = Missing/Critical</li>
            <li><strong>Scotland Mandated Rate:</strong> Should be updated when Scottish Government announces changes</li>
            <li><strong>Real Living Wage:</strong> Typically updated annually in November by the Living Wage Foundation</li>
            <li><strong>Homecare Association Rate:</strong> Optional benchmark rate, typically updated annually</li>
            <li>Rates are automatically checked for currency and validity</li>
            <li>Check this page regularly to ensure rates remain accurate</li>
        </ul>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

