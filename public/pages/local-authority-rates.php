<?php
/**
 * Local Authority Rates Information Page
 * Shows reference rates, historical data, and local authority updates
 */
require_once dirname(__DIR__, 2) . '/config/config.php';

// Allow public access to rate information
$isLoggedIn = Auth::isLoggedIn();

$pageTitle = 'Local Authority Rates Information';
include INCLUDES_PATH . '/header.php';

// Get reference rate data
$currentRLW = LocalAuthorityRateInfo::getCurrentRealLivingWage();
$rlwHistory = LocalAuthorityRateInfo::getRealLivingWageHistory();
$currentScotlandRate = LocalAuthorityRateInfo::getCurrentScotlandMandatedRate();
$scotlandRates = LocalAuthorityRateInfo::getScotlandMandatedRates();
$currentHCA = LocalAuthorityRateInfo::getCurrentHomecareAssociationRate();
$hcaRates = LocalAuthorityRateInfo::getHomecareAssociationRates();
$recentUpdates = LocalAuthorityRateInfo::getAllRecentUpdates(10);

// Get all local authorities for filtering
$db = getDbConnection();
$stmt = $db->query("SELECT * FROM local_authorities ORDER BY name");
$localAuthorities = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1>Local Authority Rates Information</h1>
                <p style="color: var(--text-light); margin-top: 0.5rem;">
                    Reference rates, historical data, and updates from Scottish local authorities
                </p>
            </div>
            <?php if ($isLoggedIn && RBAC::isAdmin()): ?>
                <?php
                $monitoringStatus = LocalAuthorityRateInfo::getRateMonitoringStatus();
                if ($monitoringStatus['overall_status'] !== 'good'):
                ?>
                    <a href="<?php echo htmlspecialchars(url('rate-monitoring.php')); ?>" class="btn <?php echo $monitoringStatus['overall_status'] === 'error' ? 'btn-danger' : 'btn-secondary'; ?>" style="white-space: nowrap;">
                        <i class="fa-solid fa-triangle-exclamation" style="margin-right: 0.5rem;"></i>
                        Rate Monitoring
                    </a>
                <?php else: ?>
                    <a href="<?php echo htmlspecialchars(url('rate-monitoring.php')); ?>" class="btn btn-secondary" style="white-space: nowrap;">
                        <i class="fa-solid fa-chart-line" style="margin-right: 0.5rem;"></i>
                        Rate Monitoring
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($isLoggedIn && RBAC::isAdmin()): ?>
        <?php
        $monitoringStatus = LocalAuthorityRateInfo::getRateMonitoringStatus();
        if ($monitoringStatus['overall_status'] !== 'good'):
        ?>
            <div class="alert <?php echo $monitoringStatus['overall_status'] === 'error' ? 'alert-error' : 'alert-warning'; ?>" style="margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: start; gap: 1rem;">
                    <div style="flex: 1;">
                        <strong><i class="fa-solid fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>Rate Monitoring Alert</strong>
                        <p style="margin: 0.5rem 0 0 0;">
                            <?php 
                            $firstWarning = null;
                            $firstWarningType = null;
                            $firstWarningKey = null;
                            
                            if (!empty($monitoringStatus['errors'])) {
                                echo htmlspecialchars($monitoringStatus['errors'][0]);
                            } elseif (!empty($monitoringStatus['warnings'])) {
                                // Find the first non-dismissed warning
                                foreach (['scotland_rate', 'rlw_rate', 'hca_rate'] as $rateType) {
                                    if (isset($monitoringStatus[$rateType]['status']) && 
                                        $monitoringStatus[$rateType]['status'] === 'warning' && 
                                        !isset($monitoringStatus[$rateType]['dismissed'])) {
                                        $firstWarning = $monitoringStatus[$rateType]['message'];
                                        $current = $monitoringStatus[$rateType]['current'] ?? null;
                                        $rateId = $current['id'] ?? 0;
                                        $firstWarningKey = md5($rateType . '|' . $rateId . '|' . $firstWarning);
                                        $firstWarningType = $rateType;
                                        break;
                                    }
                                }
                                
                                if ($firstWarning) {
                                    echo htmlspecialchars($firstWarning);
                                } elseif (!empty($monitoringStatus['warnings'])) {
                                    $warning = $monitoringStatus['warnings'][0];
                                    echo htmlspecialchars(is_array($warning) ? $warning['message'] : $warning);
                                }
                            }
                            ?>
                            <a href="<?php echo htmlspecialchars(url('rate-monitoring.php')); ?>" style="margin-left: 0.5rem; text-decoration: underline;">View monitoring dashboard</a>
                        </p>
                    </div>
                    <?php if ($monitoringStatus['overall_status'] === 'warning' && $firstWarning && $firstWarningType && $firstWarningKey): ?>
                        <form method="POST" action="<?php echo htmlspecialchars(url('rate-monitoring.php')); ?>" style="display: inline;">
                            <?php echo CSRF::tokenField(); ?>
                            <input type="hidden" name="action" value="dismiss_warning">
                            <input type="hidden" name="rate_type" value="<?php echo htmlspecialchars($firstWarningType); ?>">
                            <input type="hidden" name="warning_key" value="<?php echo htmlspecialchars($firstWarningKey); ?>">
                            <input type="hidden" name="expires_in_days" value="30">
                            <button type="submit" class="btn btn-sm" style="padding: 0.5rem 1rem; font-size: 0.9rem; background: var(--warning-color); color: white; border: none; border-radius: 0.25rem; cursor: pointer; white-space: nowrap;">
                                Dismiss (30 days)
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- Current Rates Summary -->
    <div style="margin-bottom: 2rem;">
        <h2>Current Reference Rates</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 1rem; padding: 0 0.5rem;">
            <?php if ($currentScotlandRate): ?>
                <div class="card" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; margin: 0;">
                    <h3 style="color: white; margin-bottom: 0.5rem; font-size: 1.1rem;">Scottish Government Minimum</h3>
                    <p style="font-size: 2rem; font-weight: bold; margin: 0.5rem 0;">£<?php echo number_format($currentScotlandRate['rate'], 2); ?></p>
                    <p style="color: rgba(255,255,255,0.9); font-size: 0.9rem; margin: 0;">
                        Effective: <?php echo date(DATE_FORMAT, strtotime($currentScotlandRate['effective_date'])); ?>
                    </p>
                    <?php if ($currentScotlandRate['applies_to']): ?>
                        <p style="color: rgba(255,255,255,0.8); font-size: 0.85rem; margin-top: 0.25rem;">
                            Applies to: <?php echo htmlspecialchars($currentScotlandRate['applies_to']); ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($currentRLW): ?>
                <div class="card" style="background: linear-gradient(135deg, var(--success-color), #059669); color: white; margin: 0;">
                    <h3 style="color: white; margin-bottom: 0.5rem; font-size: 1.1rem;">Real Living Wage (Scotland)</h3>
                    <p style="font-size: 2rem; font-weight: bold; margin: 0.5rem 0;">£<?php echo number_format($currentRLW['scotland_rate'] ?? $currentRLW['uk_rate'], 2); ?></p>
                    <p style="color: rgba(255,255,255,0.9); font-size: 0.9rem; margin: 0;">
                        Effective: <?php echo date(DATE_FORMAT, strtotime($currentRLW['effective_date'])); ?>
                    </p>
                    <p style="color: rgba(255,255,255,0.8); font-size: 0.85rem; margin-top: 0.25rem;">
                        (Voluntary rate)
                    </p>
                </div>
            <?php endif; ?>
            
            <?php if ($currentHCA): ?>
                <div class="card" style="background: linear-gradient(135deg, var(--warning-color), #d97706); color: white; margin: 0;">
                    <h3 style="color: white; margin-bottom: 0.5rem; font-size: 1.1rem;">Homecare Association Recommended</h3>
                    <p style="font-size: 2rem; font-weight: bold; margin: 0.5rem 0;">£<?php echo number_format($currentHCA['scotland_rate'], 2); ?></p>
                    <p style="color: rgba(255,255,255,0.9); font-size: 0.9rem; margin: 0;">
                        Year: <?php echo date('Y', strtotime($currentHCA['year_from'])); ?>-<?php echo $currentHCA['year_to'] ? date('y', strtotime($currentHCA['year_to'])) : date('y'); ?>
                    </p>
                    <p style="color: rgba(255,255,255,0.8); font-size: 0.85rem; margin-top: 0.25rem;">
                        (Minimum price to providers)
                    </p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Rate Comparison Chart -->
        <?php if (!empty($scotlandRates) || !empty($rlwHistory) || !empty($hcaRates)): ?>
            <div style="margin-top: 2rem;">
                <h3>Rate Trends Over Time - Comparing All Reference Rates</h3>
                <p style="color: var(--text-light); margin-bottom: 1rem; font-size: 0.95rem;">
                    This chart shows how all three reference rates have changed over time. Note that the Homecare Association rate is typically higher as it includes operational costs beyond wages (travel, mileage, training, etc.).
                </p>
                <div class="card" style="margin-top: 1rem; padding: 1.5rem;">
                    <canvas id="rateTrendsChart" style="max-height: 400px;"></canvas>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Recent Local Authority Updates -->
    <?php if (!empty($recentUpdates)): ?>
        <div style="margin-bottom: 2rem;">
            <h2>Recent Local Authority Updates</h2>
            <div style="margin-top: 1rem;">
                <?php foreach ($recentUpdates as $update): ?>
                    <div class="card" style="margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                            <div>
                                <h3 style="margin: 0 0 0.5rem 0;"><?php echo htmlspecialchars($update['title']); ?></h3>
                                <?php if ($update['local_authority_name']): ?>
                                    <p style="color: var(--text-light); margin: 0; font-size: 0.9rem;">
                                        <strong>Local Authority:</strong> <?php echo htmlspecialchars($update['local_authority_name']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div style="text-align: right;">
                                <?php if ($update['published_date']): ?>
                                    <p style="color: var(--text-light); margin: 0; font-size: 0.9rem;">
                                        <?php echo date(DATE_FORMAT, strtotime($update['published_date'])); ?>
                                    </p>
                                <?php endif; ?>
                                <?php if ($update['effective_date']): ?>
                                    <p style="color: var(--text-light); margin: 0.25rem 0 0 0; font-size: 0.85rem;">
                                        Effective: <?php echo date(DATE_FORMAT, strtotime($update['effective_date'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="margin-top: 1rem;">
                            <?php echo nl2br(htmlspecialchars($update['content'])); ?>
                        </div>
                        <?php if ($update['rate_change']): ?>
                            <div style="margin-top: 1rem; padding: 0.75rem; background: var(--bg-light); border-radius: 0.375rem;">
                                <strong>Rate Change:</strong> £<?php echo number_format($update['rate_change'], 2); ?>
                                <?php if ($update['rate_type']): ?>
                                    <span style="color: var(--text-light);">(<?php echo htmlspecialchars($update['rate_type']); ?>)</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($update['source_url']): ?>
                            <div style="margin-top: 0.5rem;">
                                <a href="<?php echo htmlspecialchars($update['source_url']); ?>" target="_blank" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                    View Source
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Historical Rates -->
    <div style="margin-bottom: 2rem;">
        <h2>Historical Reference Rates</h2>
        
        <!-- Scottish Government Mandated Rates -->
        <div style="margin-top: 1.5rem;">
            <h3>Scottish Government Mandated Minimum Rates</h3>
            <p style="color: var(--text-light); margin-bottom: 1rem;">
                Historical progression of minimum rates for social care workers in commissioned services
            </p>
            <table class="table">
                <thead>
                    <tr>
                        <th>Effective Date</th>
                        <th>Rate</th>
                        <th>Applies To</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($scotlandRates as $rate): ?>
                        <tr>
                            <td><?php echo date(DATE_FORMAT, strtotime($rate['effective_date'])); ?></td>
                            <td><strong>£<?php echo number_format($rate['rate'], 2); ?></strong></td>
                            <td><?php echo htmlspecialchars($rate['applies_to']); ?></td>
                            <td><?php echo htmlspecialchars($rate['notes'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Real Living Wage History -->
        <div style="margin-top: 2rem;">
            <h3>Real Living Wage History</h3>
            <p style="color: var(--text-light); margin-bottom: 1rem;">
                Independently calculated Real Living Wage rates (voluntary, not government-mandated)
            </p>
            
            <?php if (!empty($rlwHistory)): ?>
                <!-- Real Living Wage Chart -->
                <div class="card" style="margin-bottom: 1.5rem; padding: 1.5rem;">
                    <canvas id="rlwHistoryChart" style="max-height: 400px;"></canvas>
                </div>
            <?php endif; ?>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Effective Date</th>
                        <th>UK Rate</th>
                        <th>London Rate</th>
                        <th>Scotland Rate</th>
                        <th>Announced</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rlwHistory as $rlw): ?>
                        <tr>
                            <td><?php echo date(DATE_FORMAT, strtotime($rlw['effective_date'])); ?></td>
                            <td><strong>£<?php echo number_format($rlw['uk_rate'], 2); ?></strong></td>
                            <td>£<?php echo number_format($rlw['london_rate'] ?? 0, 2); ?></td>
                            <td><strong>£<?php echo number_format($rlw['scotland_rate'] ?? $rlw['uk_rate'], 2); ?></strong></td>
                            <td><?php echo $rlw['announced_date'] ? date(DATE_FORMAT, strtotime($rlw['announced_date'])) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Homecare Association Rates -->
        <div style="margin-top: 2rem;">
            <h3>Homecare Association Minimum Price Recommendations</h3>
            <p style="color: var(--text-light); margin-bottom: 1rem;">
                Recommended minimum prices local authorities should pay to providers (covers wages, travel, mileage, and on-costs)
            </p>
            
            <?php if (!empty($hcaRates)): ?>
                <!-- Homecare Association Chart -->
                <div class="card" style="margin-bottom: 1.5rem; padding: 1.5rem;">
                    <canvas id="hcaRatesChart" style="max-height: 400px;"></canvas>
                </div>
            <?php endif; ?>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Scotland Rate</th>
                        <th>Report</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hcaRates as $hca): ?>
                        <tr>
                            <td>
                                <?php echo date('Y', strtotime($hca['year_from'])); ?>
                                <?php if ($hca['year_to']): ?>
                                    - <?php echo date('Y', strtotime($hca['year_to'])); ?>
                                <?php endif; ?>
                            </td>
                            <td><strong>£<?php echo number_format($hca['scotland_rate'], 2); ?></strong> per hour</td>
                            <td>
                                <?php if ($hca['report_url']): ?>
                                    <a href="<?php echo htmlspecialchars($hca['report_url']); ?>" target="_blank" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                        View Report
                                    </a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Rate Relationships Explanation -->
        <div style="margin-top: 3rem; padding: 1.5rem; background: var(--bg-light); border-left: 4px solid var(--primary-color); border-radius: 0.375rem;">
            <h3 style="margin-top: 0;">Understanding the Relationship Between Reference Rates</h3>
            <p style="margin-bottom: 1rem;">
                These three reference rates serve different purposes in the social care sector and are often used together to inform contract pricing:
            </p>
            <ul style="margin-left: 1.5rem; line-height: 1.8;">
                <li><strong>Scottish Government Minimum:</strong> The legally mandated minimum hourly rate for social care workers in commissioned services. This is the baseline that all providers must meet.</li>
                <li><strong>Real Living Wage:</strong> A voluntary rate independently calculated based on the cost of living. Many providers choose to pay this rate (or higher) to demonstrate their commitment to fair pay and attract quality staff.</li>
                <li><strong>Homecare Association Recommended:</strong> The minimum price local authorities should pay to providers per hour. This rate is typically <strong>higher</strong> than wage rates because it includes not just wages, but also travel time, mileage costs, training, supervision, and other operational overheads that providers incur.</li>
            </ul>
            <p style="margin-top: 1rem; margin-bottom: 0;">
                <strong>Why compare them?</strong> Understanding all three rates helps providers and commissioners negotiate fair contracts. The HCA rate being higher than wage rates reflects the true cost of delivering quality care, including all the necessary support costs beyond direct wages.
            </p>
        </div>
    </div>
    
    <!-- Information Sources -->
    <div>
        <h2>Data Sources & Monitoring</h2>
        <div class="card" style="margin-top: 1rem;">
            <h3>Annual Data Sources to Monitor</h3>
            <ul style="margin-top: 1rem; padding-left: 1.5rem;">
                <li style="margin-bottom: 0.75rem;">
                    <strong><a href="https://www.livingwage.org.uk/" target="_blank" style="color: var(--primary-color); text-decoration: none;">Living Wage Foundation</a></strong> (November each year): New Real Living Wage announced
                </li>
                <li style="margin-bottom: 0.75rem;">
                    <strong><a href="https://www.homecareassociation.org.uk/about-us/research-and-reports.html" target="_blank" style="color: var(--primary-color); text-decoration: none;">Homecare Association</a></strong> (December/January each year): New Minimum Price for Homecare reports published
                </li>
                <li style="margin-bottom: 0.75rem;">
                    <strong><a href="https://www.gov.scot/policies/social-care/" target="_blank" style="color: var(--primary-color); text-decoration: none;">Scottish Government</a></strong> (March/April each year): Budget announcements for social care worker minimum pay
                </li>
                <li style="margin-bottom: 0.75rem;">
                    <strong><a href="https://www.cosla.gov.uk/" target="_blank" style="color: var(--primary-color); text-decoration: none;">COSLA</a></strong>: Guidance updates on charging policies
                </li>
            </ul>
            
            <h3 style="margin-top: 1.5rem;">Useful Links</h3>
            <ul style="margin-top: 1rem; padding-left: 1.5rem;">
                <li style="margin-bottom: 0.75rem;">
                    <a href="https://www.homecareassociation.org.uk/about-us/research-and-reports.html" target="_blank">
                        Homecare Association Research & Reports
                    </a>
                </li>
                <li style="margin-bottom: 0.75rem;">
                    <a href="https://www.gov.scot/publications/assessing-impact-increase-pay-adult-social-care-labour-supply-scotland/" target="_blank">
                        Scottish Government: Adult Social Care Labour Supply Impact Assessment
                    </a>
                </li>
                <li style="margin-bottom: 0.75rem;">
                    <a href="https://www.livingwage.org.uk/" target="_blank">
                        Living Wage Foundation
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prepare data for rate trends chart
    const scotlandRates = <?php echo json_encode(array_map(function($r) {
        return ['date' => $r['effective_date'], 'rate' => floatval($r['rate'])];
    }, $scotlandRates)); ?>;
    
    const rlwRates = <?php echo json_encode(array_map(function($r) {
        return ['date' => $r['effective_date'], 'rate' => floatval($r['scotland_rate'] ?? $r['uk_rate'])];
    }, $rlwHistory)); ?>;
    
    const hcaRates = <?php echo json_encode(array_map(function($r) {
        return ['date' => $r['year_from'], 'rate' => floatval($r['scotland_rate'])];
    }, $hcaRates)); ?>;
    
    // Get all unique dates and sort them chronologically (oldest first)
    const allDates = new Set();
    scotlandRates.forEach(r => allDates.add(r.date));
    rlwRates.forEach(r => allDates.add(r.date));
    hcaRates.forEach(r => allDates.add(r.date));
    const sortedDates = Array.from(allDates).sort((a, b) => new Date(a) - new Date(b));
    
    // Create datasets
    const scotlandData = sortedDates.map(date => {
        const rate = scotlandRates.find(r => r.date === date);
        return rate ? rate.rate : null;
    });
    
    const rlwData = sortedDates.map(date => {
        const rate = rlwRates.find(r => r.date === date);
        return rate ? rate.rate : null;
    });
    
    const hcaData = sortedDates.map(date => {
        const rate = hcaRates.find(r => r.date === date);
        return rate ? rate.rate : null;
    });
    
    // Format dates for display (already in chronological order)
    const formattedDates = sortedDates.map(date => {
        const d = new Date(date);
        return d.toLocaleDateString('en-GB', { month: 'short', year: 'numeric' });
    });
    
    // Create main rate trends chart
    const ctx = document.getElementById('rateTrendsChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: formattedDates,
                datasets: [
                    <?php if (!empty($scotlandRates)): ?>
                    {
                        label: 'Scottish Government Minimum',
                        data: scotlandData,
                        borderColor: 'rgb(37, 99, 235)',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        tension: 0.1,
                        spanGaps: true
                    },
                    <?php endif; ?>
                    <?php if (!empty($rlwHistory)): ?>
                    {
                        label: 'Real Living Wage (Scotland)',
                        data: rlwData,
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.1,
                        spanGaps: true
                    },
                    <?php endif; ?>
                    <?php if (!empty($hcaRates)): ?>
                    {
                        label: 'Homecare Association Recommended',
                        data: hcaData,
                        borderColor: 'rgb(245, 158, 11)',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.1,
                        spanGaps: true
                    }
                    <?php endif; ?>
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Reference Rate Trends Over Time',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': £' + context.parsed.y.toFixed(2) + '/hr';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        title: {
                            display: true,
                            text: 'Rate (£/hour)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '£' + value.toFixed(2);
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    }
    
    // Create Real Living Wage History chart
    const rlwCtx = document.getElementById('rlwHistoryChart');
    if (rlwCtx) {
        const rlwFullData = <?php echo json_encode($rlwHistory); ?>;
        
        if (rlwFullData.length > 0) {
            // Sort data chronologically (oldest first)
            const sortedRlwData = [...rlwFullData].sort((a, b) => {
                return new Date(a.effective_date) - new Date(b.effective_date);
            });
            
            const rlwDates = sortedRlwData.map(r => {
                const d = new Date(r.effective_date);
                return d.toLocaleDateString('en-GB', { month: 'short', year: 'numeric' });
            });
            
            const ukRates = sortedRlwData.map(r => parseFloat(r.uk_rate) || null);
            const scotlandRatesData = sortedRlwData.map(r => parseFloat(r.scotland_rate || r.uk_rate) || null);
            const londonRates = sortedRlwData.map(r => r.london_rate ? parseFloat(r.london_rate) : null);
            
            const hasLondonData = londonRates.some(r => r !== null);
            
            new Chart(rlwCtx, {
                type: 'line',
                data: {
                    labels: rlwDates,
                    datasets: [
                        {
                            label: 'UK Rate',
                            data: ukRates,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.1,
                            borderWidth: 2
                        },
                        {
                            label: 'Scotland Rate',
                            data: scotlandRatesData,
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.1,
                            borderWidth: 2
                        }<?php if (array_filter($rlwHistory, function($r) { return !empty($r['london_rate']); })): ?>,
                        {
                            label: 'London Rate',
                            data: londonRates,
                            borderColor: 'rgb(139, 92, 246)',
                            backgroundColor: 'rgba(139, 92, 246, 0.1)',
                            tension: 0.1,
                            borderWidth: 2,
                            spanGaps: true
                        }
                        <?php endif; ?>
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Real Living Wage Rates Over Time',
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    if (context.parsed.y !== null) {
                                        return context.dataset.label + ': £' + context.parsed.y.toFixed(2) + '/hr';
                                    }
                                    return null;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            title: {
                                display: true,
                                text: 'Rate (£/hour)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return '£' + value.toFixed(2);
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Effective Date'
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        }
    }
    
    // Create Homecare Association Rates chart
    const hcaCtx = document.getElementById('hcaRatesChart');
    if (hcaCtx) {
        const hcaFullData = <?php echo json_encode($hcaRates); ?>;
        
        if (hcaFullData.length > 0) {
            // Sort data chronologically (oldest first)
            const sortedHcaData = [...hcaFullData].sort((a, b) => {
                return new Date(a.year_from) - new Date(b.year_from);
            });
            
            const hcaLabels = sortedHcaData.map(h => {
                const yearFrom = new Date(h.year_from).getFullYear();
                if (h.year_to) {
                    const yearTo = new Date(h.year_to).getFullYear();
                    return yearFrom === yearTo ? yearFrom.toString() : yearFrom + '-' + yearTo;
                }
                return yearFrom.toString();
            });
            
            const hcaRatesData = sortedHcaData.map(h => parseFloat(h.scotland_rate) || null);
            
            new Chart(hcaCtx, {
                type: 'line',
                data: {
                    labels: hcaLabels,
                    datasets: [
                        {
                            label: 'Scotland Rate',
                            data: hcaRatesData,
                            borderColor: 'rgb(245, 158, 11)',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            tension: 0.1,
                            borderWidth: 2,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Homecare Association Minimum Price Recommendations Over Time',
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    if (context.parsed.y !== null) {
                                        return context.dataset.label + ': £' + context.parsed.y.toFixed(2) + '/hr';
                                    }
                                    return null;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            title: {
                                display: true,
                                text: 'Rate (£/hour)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return '£' + value.toFixed(2);
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Year'
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        }
    }
});
</script>

<?php include INCLUDES_PATH . '/footer.php'; ?>
