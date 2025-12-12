#!/usr/bin/env php
<?php
/**
 * Tender Monitoring Script
 * Run this via cron to automatically check for new tender opportunities
 * 
 * Cron example (check every 6 hours):
 * 0 */6 * * * /usr/bin/php /path/to/contracts/scripts/check-tenders.php
 */

// Set up paths
require_once dirname(__DIR__) . '/config/config.php';

// Run monitoring check
try {
    $results = TenderMonitor::runMonitoringCheck();
    
    $totalFound = 0;
    foreach ($results as $result) {
        if (isset($result['opportunities_found'])) {
            $totalFound += $result['opportunities_found'];
        }
    }
    
    echo "Monitoring check completed at " . date('Y-m-d H:i:s') . "\n";
    echo "Found $totalFound new opportunity(ies)\n";
    
    if ($totalFound > 0) {
        foreach ($results as $monitorId => $result) {
            if (isset($result['opportunities_found']) && $result['opportunities_found'] > 0) {
                echo "  Monitor $monitorId: {$result['opportunities_found']} opportunities\n";
            }
        }
    }
    
    exit(0);
} catch (Exception $e) {
    error_log("Tender monitoring error: " . $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

