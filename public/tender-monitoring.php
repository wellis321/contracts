<?php
/**
 * Tender Monitoring Configuration
 * Set up automated monitoring for new tender opportunities
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
$organisationId = Auth::getOrganisationId();
$isAdmin = RBAC::isAdmin();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create') {
            try {
                TenderMonitoringPreference::create([
                    'keywords' => trim($_POST['keywords'] ?? ''),
                    'local_authority_ids' => !empty($_POST['local_authority_ids']) ? $_POST['local_authority_ids'] : null,
                    'contract_type_ids' => !empty($_POST['contract_type_ids']) ? $_POST['contract_type_ids'] : null,
                    'cpv_codes' => !empty($_POST['cpv_codes']) ? explode(',', $_POST['cpv_codes']) : ['85000000'],
                    'min_value' => !empty($_POST['min_value']) ? $_POST['min_value'] : null,
                    'max_value' => !empty($_POST['max_value']) ? $_POST['max_value'] : null,
                    'notification_method' => $_POST['notification_method'] ?? 'email',
                    'email_address' => trim($_POST['email_address'] ?? ''),
                    'notify_immediately' => isset($_POST['notify_immediately']),
                    'notify_daily_summary' => isset($_POST['notify_daily_summary']),
                    'notify_weekly_summary' => isset($_POST['notify_weekly_summary']),
                    'is_active' => isset($_POST['is_active'])
                ]);
                $success = 'Monitoring preference created successfully. The system will check for new opportunities automatically.';
            } catch (Exception $e) {
                $error = 'Error creating monitor: ' . $e->getMessage();
            }
        } elseif ($action === 'update') {
            try {
                TenderMonitoringPreference::update($_POST['id'], [
                    'is_active' => isset($_POST['is_active'])
                ]);
                $success = 'Monitoring preference updated successfully.';
            } catch (Exception $e) {
                $error = 'Error updating monitor: ' . $e->getMessage();
            }
        } elseif ($action === 'delete') {
            try {
                TenderMonitoringPreference::delete($_POST['id']);
                $success = 'Monitoring preference deleted successfully.';
            } catch (Exception $e) {
                $error = 'Error deleting monitor: ' . $e->getMessage();
            }
        } elseif ($action === 'test_check') {
            try {
                // Manually trigger a check
                $results = TenderMonitor::runMonitoringCheck();
                $totalFound = array_sum(array_column($results, 'opportunities_found'));
                
                $details = [];
                foreach ($results as $monitorId => $result) {
                    if (isset($result['error'])) {
                        $details[] = "Monitor $monitorId: Error - " . $result['error'];
                    } elseif (isset($result['api_response_received']) && !$result['api_response_received']) {
                        $details[] = "Monitor $monitorId: No API response (check server logs)";
                    } else {
                        $found = $result['opportunities_found'] ?? 0;
                        $details[] = "Monitor $monitorId: Found $found opportunity(ies)";
                    }
                }
                
                if (empty($results)) {
                    $success = "Monitoring check completed. No active monitors configured. <a href='#createForm' onclick='document.getElementById(\"createForm\").style.display=\"block\"'>Create one now</a>.";
                } else {
                    $success = "Monitoring check completed. Found $totalFound new opportunity(ies).<br><small style='color: var(--text-light);'>" . implode('<br>', $details) . "</small>";
                }
            } catch (Exception $e) {
                $error = 'Error running check: ' . $e->getMessage();
            }
        }
    }
}

// Get monitoring preferences
$monitors = TenderMonitoringPreference::findByOrganisation($organisationId);

// Get local authorities and contract types
$db = getDbConnection();
$stmt = $db->query("SELECT * FROM local_authorities ORDER BY name");
$localAuthorities = $stmt->fetchAll();
$contractTypes = ContractType::findByOrganisation($organisationId);

$pageTitle = 'Tender Monitoring';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2>Tender Opportunity Monitoring</h2>
                <p>Set up automated monitoring for new tender opportunities from Public Contracts Scotland</p>
            </div>
            <?php if ($isAdmin): ?>
                <div style="display: flex; gap: 0.5rem;">
                    <form method="POST" action="" style="display: inline;">
                        <?php echo CSRF::tokenField(); ?>
                        <input type="hidden" name="action" value="test_check">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-sync"></i> Check Now
                        </button>
                    </form>
                    <button onclick="document.getElementById('createForm').style.display='block'" class="btn btn-primary">
                        <i class="fa-solid fa-plus"></i> New Monitor
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Info Box -->
    <div style="margin-bottom: 2rem; padding: 1.5rem; background: #e0f2fe; border-left: 4px solid #0ea5e9; border-radius: 0.5rem;">
        <h3 style="margin-top: 0;"><i class="fas fa-info-circle"></i> How It Works</h3>
        <p>The system automatically monitors Public Contracts Scotland for new tender opportunities matching your criteria.</p>
        <ul style="margin: 0.5rem 0 0 1.5rem;">
            <li><strong>Automated Checking:</strong> The system checks the PCS API regularly (can be set up as a cron job)</li>
            <li><strong>Instant Notifications:</strong> Get notified immediately when new opportunities are found</li>
            <li><strong>Auto-Import:</strong> Opportunities are automatically imported into your system</li>
            <li><strong>Email Alerts:</strong> Receive email notifications for new opportunities</li>
        </ul>
        <p style="margin-top: 1rem; margin-bottom: 0;">
            <strong>Note:</strong> To enable automatic checking, set up a cron job to run: 
            <code style="background: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">php <?php echo ROOT_PATH; ?>/scripts/check-tenders.php</code>
        </p>
    </div>
    
    <!-- Diagnostic Info -->
    <?php if ($isAdmin): ?>
    <div style="margin-bottom: 2rem; padding: 1.5rem; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 0.5rem;">
        <h3 style="margin-top: 0;"><i class="fas fa-bug"></i> Diagnostic Information</h3>
        <p><strong>Active Monitors:</strong> <?php echo count($monitors); ?></p>
        <?php if (empty($monitors)): ?>
            <p style="color: var(--danger-color);"><strong>⚠️ No active monitors configured.</strong> Create a monitor above to start tracking opportunities.</p>
        <?php else: ?>
            <p><strong>API Status:</strong> 
                <?php
                // Test API connectivity
                try {
                    $testData = TenderMonitor::checkPCSAPI('social care', '85000000');
                    if ($testData) {
                        echo '<span style="color: green;">✓ API accessible</span>';
                        $releasesCount = 0;
                        if (isset($testData['releases'])) {
                            $releasesCount = count($testData['releases']);
                        } elseif (isset($testData['data'])) {
                            $releasesCount = is_array($testData['data']) ? count($testData['data']) : 0;
                        } elseif (is_array($testData) && isset($testData[0])) {
                            $releasesCount = count($testData);
                        }
                        echo " (Found $releasesCount opportunities in test query)";
                    } else {
                        echo '<span style="color: red;">✗ API not responding (check server logs)</span>';
                    }
                } catch (Exception $e) {
                    echo '<span style="color: red;">✗ API Error: ' . htmlspecialchars($e->getMessage()) . '</span>';
                }
                ?>
            </p>
            <p><small style="color: var(--text-light);">
                <strong>Tip:</strong> If you're getting 0 opportunities, it could mean:
                <ul style="margin: 0.5rem 0 0 1.5rem;">
                    <li>No new opportunities match your criteria in the last 30 days</li>
                    <li>The API response format may have changed (check server error logs)</li>
                    <li>Your keywords/CPV codes might be too specific</li>
                    <li>All matching opportunities have already been imported</li>
                </ul>
                Check your server error logs for detailed debugging information.
            </small></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Create Form -->
    <?php if ($isAdmin): ?>
    <div id="createForm" style="display: none; margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem;">
        <h3 style="margin-top: 0;">Create New Monitoring Preference</h3>
        <form method="POST" action="">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="create">
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label for="keywords">Keywords</label>
                    <input type="text" id="keywords" name="keywords" class="form-control" 
                           placeholder="e.g., social care, supported living">
                    <small style="color: var(--text-light);">Comma-separated keywords to search for</small>
                </div>
                
                <div class="form-group">
                    <label for="cpv_codes">CPV Codes</label>
                    <input type="text" id="cpv_codes" name="cpv_codes" class="form-control" 
                           value="85000000" placeholder="85000000, 85320000">
                    <small style="color: var(--text-light);">85000000 = Health and social work services</small>
                </div>
                
                <div class="form-group">
                    <label for="notification_method">Notification Method</label>
                    <select id="notification_method" name="notification_method" class="form-control">
                        <option value="email">Email Only</option>
                        <option value="in_app">In-App Only</option>
                        <option value="both">Both Email and In-App</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="email_address">Email Address</label>
                    <input type="email" id="email_address" name="email_address" class="form-control" 
                           placeholder="notifications@example.com">
                    <small style="color: var(--text-light);">Leave blank to use your account email</small>
                </div>
            </div>
            
            <div class="form-group">
                <label>Local Authorities to Monitor (optional)</label>
                <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border-color); padding: 0.5rem; border-radius: 0.25rem;">
                    <?php foreach ($localAuthorities as $la): ?>
                        <label style="display: block; padding: 0.25rem 0;">
                            <input type="checkbox" name="local_authority_ids[]" value="<?php echo $la['id']; ?>">
                            <?php echo htmlspecialchars($la['name']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <small style="color: var(--text-light);">Leave unchecked to monitor all authorities</small>
            </div>
            
            <div style="margin-top: 1rem;">
                <label>
                    <input type="checkbox" name="notify_immediately" checked> Notify immediately when found
                </label>
            </div>
            
            <div style="margin-top: 1rem;">
                <label>
                    <input type="checkbox" name="is_active" checked> Active (start monitoring)
                </label>
            </div>
            
            <div class="form-group" style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">Create Monitor</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('createForm').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
    <?php endif; ?>
    
    <!-- Existing Monitors -->
    <h3>Active Monitoring Preferences</h3>
    <?php if (empty($monitors)): ?>
        <div class="alert alert-info">
            <p>No monitoring preferences configured. Create one above to start automatically finding tender opportunities.</p>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Keywords</th>
                        <th>Local Authorities</th>
                        <th>Notification</th>
                        <th>Status</th>
                        <th>Last Checked</th>
                        <th>Found</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($monitors as $monitor): 
                        $laIds = json_decode($monitor['local_authority_ids'] ?? '[]', true);
                        $keywords = $monitor['keywords'] ?? 'None';
                    ?>
                        <tr>
                            <td>
                                <?php if ($keywords): ?>
                                    <code><?php echo htmlspecialchars($keywords); ?></code>
                                <?php else: ?>
                                    <span style="color: var(--text-light);">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($laIds)): ?>
                                    <?php 
                                    $laNames = [];
                                    foreach ($localAuthorities as $la) {
                                        if (in_array($la['id'], $laIds)) {
                                            $laNames[] = $la['name'];
                                        }
                                    }
                                    echo htmlspecialchars(implode(', ', array_slice($laNames, 0, 3)));
                                    if (count($laNames) > 3) {
                                        echo ' <small style="color: var(--text-light);">+' . (count($laNames) - 3) . ' more</small>';
                                    }
                                    ?>
                                <?php else: ?>
                                    <span style="color: var(--text-light);">All</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo ucwords(str_replace('_', ' ', $monitor['notification_method'])); ?>
                                <?php if ($monitor['email_address']): ?>
                                    <br><small style="color: var(--text-light);"><?php echo htmlspecialchars($monitor['email_address']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($monitor['is_active']): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($monitor['last_checked_at']): ?>
                                    <small><?php echo date(DATE_FORMAT . ' H:i', strtotime($monitor['last_checked_at'])); ?></small>
                                <?php else: ?>
                                    <span style="color: var(--text-light);">Never</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo $monitor['opportunities_found']; ?></strong> found
                            </td>
                            <td>
                                <?php if ($isAdmin): ?>
                                    <form method="POST" action="" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to delete this monitor?');">
                                        <?php echo CSRF::tokenField(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $monitor['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
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

