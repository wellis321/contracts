<?php
/**
 * Local Authority Rate Information Model
 * Handles reference rate data and local authority updates
 */

class LocalAuthorityRateInfo {
    
    /**
     * Get all Real Living Wage history
     */
    public static function getRealLivingWageHistory() {
        $db = getDbConnection();
        $stmt = $db->query("SELECT * FROM real_living_wage_history ORDER BY effective_date DESC");
        return $stmt->fetchAll();
    }
    
    /**
     * Get current Real Living Wage
     */
    public static function getCurrentRealLivingWage() {
        $db = getDbConnection();
        $stmt = $db->query("
            SELECT * FROM real_living_wage_history 
            WHERE effective_date <= CURDATE() 
            ORDER BY effective_date DESC 
            LIMIT 1
        ");
        return $stmt->fetch();
    }
    
    /**
     * Get all Scottish Government mandated minimum rates
     */
    public static function getScotlandMandatedRates() {
        $db = getDbConnection();
        $stmt = $db->query("SELECT * FROM scotland_mandated_minimum_rates ORDER BY effective_date DESC");
        return $stmt->fetchAll();
    }
    
    /**
     * Get current Scottish Government mandated minimum rate
     */
    public static function getCurrentScotlandMandatedRate() {
        $db = getDbConnection();
        $stmt = $db->query("
            SELECT * FROM scotland_mandated_minimum_rates 
            WHERE effective_date <= CURDATE() 
            ORDER BY effective_date DESC 
            LIMIT 1
        ");
        return $stmt->fetch();
    }
    
    /**
     * Get all Homecare Association rates
     */
    public static function getHomecareAssociationRates() {
        $db = getDbConnection();
        $stmt = $db->query("SELECT * FROM homecare_association_rates ORDER BY year_from DESC");
        return $stmt->fetchAll();
    }
    
    /**
     * Get current Homecare Association rate
     */
    public static function getCurrentHomecareAssociationRate() {
        $db = getDbConnection();
        $stmt = $db->query("
            SELECT * FROM homecare_association_rates 
            WHERE year_from <= CURDATE() 
            AND (year_to IS NULL OR year_to >= CURDATE())
            ORDER BY year_from DESC 
            LIMIT 1
        ");
        return $stmt->fetch();
    }
    
    /**
     * Get rate updates for a specific local authority
     */
    public static function getUpdatesByLocalAuthority($localAuthorityId, $limit = null) {
        $db = getDbConnection();
        $sql = "
            SELECT lau.*, la.name as local_authority_name
            FROM local_authority_rate_updates lau
            LEFT JOIN local_authorities la ON lau.local_authority_id = la.id
            WHERE lau.local_authority_id = ? AND lau.is_active = 1
            ORDER BY lau.published_date DESC, lau.created_at DESC
        ";
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }
        $stmt = $db->prepare($sql);
        $stmt->execute([$localAuthorityId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all recent rate updates (across all local authorities)
     */
    public static function getAllRecentUpdates($limit = 20) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT lau.*, la.name as local_authority_name
            FROM local_authority_rate_updates lau
            LEFT JOIN local_authorities la ON lau.local_authority_id = la.id
            WHERE lau.is_active = 1
            ORDER BY lau.published_date DESC, lau.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get rate update by ID
     */
    public static function getUpdateById($id) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT lau.*, la.name as local_authority_name
            FROM local_authority_rate_updates lau
            LEFT JOIN local_authorities la ON lau.local_authority_id = la.id
            WHERE lau.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Create rate update
     */
    public static function createUpdate($data) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            INSERT INTO local_authority_rate_updates (
                local_authority_id, title, content, effective_date, 
                rate_change, rate_type, source_url, published_date, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['local_authority_id'] ?? null,
            $data['title'],
            $data['content'],
            $data['effective_date'] ?? null,
            $data['rate_change'] ?? null,
            $data['rate_type'] ?? null,
            $data['source_url'] ?? null,
            $data['published_date'] ?? date('Y-m-d'),
            $data['created_by'] ?? null
        ]);
        return $db->lastInsertId();
    }
    
    /**
     * Update rate update
     */
    public static function updateUpdate($id, $data) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            UPDATE local_authority_rate_updates SET
                local_authority_id = ?,
                title = ?,
                content = ?,
                effective_date = ?,
                rate_change = ?,
                rate_type = ?,
                source_url = ?,
                published_date = ?,
                is_active = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['local_authority_id'] ?? null,
            $data['title'],
            $data['content'],
            $data['effective_date'] ?? null,
            $data['rate_change'] ?? null,
            $data['rate_type'] ?? null,
            $data['source_url'] ?? null,
            $data['published_date'] ?? date('Y-m-d'),
            $data['is_active'] ?? true,
            $id
        ]);
    }
    
    /**
     * Delete rate update (soft delete)
     */
    public static function deleteUpdate($id) {
        $db = getDbConnection();
        $stmt = $db->prepare("UPDATE local_authority_rate_updates SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Get rate monitoring status - checks if rates are current and valid
     */
    public static function getRateMonitoringStatus() {
        $status = [
            'scotland_rate' => self::checkScotlandRateStatus(),
            'rlw_rate' => self::checkRLWStatus(),
            'hca_rate' => self::checkHCAStatus(),
            'overall_status' => 'good',
            'warnings' => [],
            'errors' => []
        ];
        
        // Check for dismissed warnings
        $dismissedWarnings = self::getDismissedWarnings();
        
        // Collect warnings and errors (excluding dismissed ones)
        foreach (['scotland_rate', 'rlw_rate', 'hca_rate'] as $rateType) {
            $warningKey = self::generateWarningKey($rateType, $status[$rateType]);
            
            // Skip if this warning has been dismissed and hasn't expired
            if (isset($dismissedWarnings[$warningKey]) && 
                (!$dismissedWarnings[$warningKey]['expires_at'] || 
                 strtotime($dismissedWarnings[$warningKey]['expires_at']) > time())) {
                // Mark as dismissed but keep the status for reference
                $status[$rateType]['dismissed'] = true;
                $status[$rateType]['dismissal_id'] = $dismissedWarnings[$warningKey]['id'];
                $status[$rateType]['warning_key'] = $warningKey;
                continue;
            }
            
            // Store warning key for dismiss functionality
            $status[$rateType]['warning_key'] = $warningKey;
            
            if ($status[$rateType]['status'] === 'warning') {
                $status['warnings'][] = [
                    'message' => $status[$rateType]['message'],
                    'rate_type' => $rateType,
                    'warning_key' => $warningKey
                ];
                $status['overall_status'] = 'warning';
            } elseif ($status[$rateType]['status'] === 'error') {
                $status['errors'][] = $status[$rateType]['message'];
                $status['overall_status'] = 'error';
            }
        }
        
        return $status;
    }
    
    /**
     * Generate a unique key for a warning
     */
    private static function generateWarningKey($rateType, $status) {
        $current = $status['current'] ?? null;
        $rateId = $current['id'] ?? 0;
        $message = $status['message'] ?? '';
        return md5($rateType . '|' . $rateId . '|' . $message);
    }
    
    /**
     * Get dismissed warnings for current organisation
     */
    private static function getDismissedWarnings() {
        if (!Auth::isLoggedIn()) {
            return [];
        }
        
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        
        try {
            $stmt = $db->prepare("
                SELECT warning_type, warning_key, id, expires_at
                FROM rate_warning_dismissals
                WHERE organisation_id = ?
                AND (expires_at IS NULL OR expires_at > NOW())
            ");
            $stmt->execute([$organisationId]);
            $dismissals = $stmt->fetchAll();
            
            $result = [];
            foreach ($dismissals as $dismissal) {
                $result[$dismissal['warning_key']] = $dismissal;
            }
            return $result;
        } catch (Exception $e) {
            // Table might not exist yet
            return [];
        }
    }
    
    /**
     * Dismiss a rate warning
     */
    public static function dismissWarning($rateType, $warningKey, $expiresInDays = 30) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        $userId = Auth::getUserId();
        
        $expiresAt = null;
        if ($expiresInDays > 0) {
            $expiresAt = date('Y-m-d H:i:s', strtotime("+$expiresInDays days"));
        }
        
        try {
            $stmt = $db->prepare("
                INSERT INTO rate_warning_dismissals 
                (organisation_id, user_id, warning_type, warning_key, expires_at)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    user_id = VALUES(user_id),
                    dismissed_at = NOW(),
                    expires_at = VALUES(expires_at)
            ");
            $stmt->execute([$organisationId, $userId, $rateType, $warningKey, $expiresAt]);
            return true;
        } catch (Exception $e) {
            error_log("Error dismissing warning: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Undismiss a warning (show it again)
     */
    public static function undismissWarning($dismissalId) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        
        try {
            $stmt = $db->prepare("
                DELETE FROM rate_warning_dismissals
                WHERE id = ? AND organisation_id = ?
            ");
            $stmt->execute([$dismissalId, $organisationId]);
            return true;
        } catch (Exception $e) {
            error_log("Error undismissing warning: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check Scotland Mandated Rate status
     */
    private static function checkScotlandRateStatus() {
        $current = self::getCurrentScotlandMandatedRate();
        $status = ['status' => 'good', 'message' => '', 'current' => $current];
        
        if (!$current) {
            $status['status'] = 'error';
            $status['message'] = 'No current Scotland Mandated Minimum Rate found. Please add a rate.';
            return $status;
        }
        
        $effectiveDate = new DateTime($current['effective_date']);
        $today = new DateTime();
        $daysSinceEffective = $today->diff($effectiveDate)->days;
        
        // Check if rate is more than 6 months old
        if ($daysSinceEffective > 180) {
            $status['status'] = 'warning';
            $status['message'] = "Scotland Mandated Rate is " . round($daysSinceEffective / 30) . " months old. Consider checking for updates.";
        }
        
        // Check if rate is in the future (shouldn't happen but validate)
        if ($effectiveDate > $today) {
            $status['status'] = 'warning';
            $status['message'] = "Scotland Mandated Rate effective date is in the future.";
        }
        
        return $status;
    }
    
    /**
     * Check Real Living Wage status
     */
    private static function checkRLWStatus() {
        $current = self::getCurrentRealLivingWage();
        $status = ['status' => 'good', 'message' => '', 'current' => $current];
        
        if (!$current) {
            $status['status'] = 'error';
            $status['message'] = 'No current Real Living Wage found. Please add a rate.';
            return $status;
        }
        
        $effectiveDate = new DateTime($current['effective_date']);
        $today = new DateTime();
        $daysSinceEffective = $today->diff($effectiveDate)->days;
        
        // Real Living Wage typically updates annually in November
        // Check if we're past November and rate is more than 1 year old
        $currentMonth = (int)$today->format('n');
        if ($currentMonth >= 11 && $daysSinceEffective > 365) {
            $status['status'] = 'warning';
            $status['message'] = "Real Living Wage is over 1 year old. New rates are typically announced in November.";
        } elseif ($daysSinceEffective > 400) {
            $status['status'] = 'warning';
            $status['message'] = "Real Living Wage is " . round($daysSinceEffective / 30) . " months old. Consider checking for updates.";
        }
        
        // Check if rate is in the future
        if ($effectiveDate > $today) {
            $status['status'] = 'warning';
            $status['message'] = "Real Living Wage effective date is in the future.";
        }
        
        return $status;
    }
    
    /**
     * Check Homecare Association Rate status
     */
    private static function checkHCAStatus() {
        $current = self::getCurrentHomecareAssociationRate();
        $status = ['status' => 'good', 'message' => '', 'current' => $current];
        
        if (!$current) {
            $status['status'] = 'warning';
            $status['message'] = 'No current Homecare Association Rate found. This is optional but recommended.';
            return $status;
        }
        
        $yearFrom = new DateTime($current['year_from']);
        $today = new DateTime();
        $daysSinceEffective = $today->diff($yearFrom)->days;
        
        // HCA rates are typically annual, check if more than 1 year old
        if ($daysSinceEffective > 400) {
            $status['status'] = 'warning';
            $status['message'] = "Homecare Association Rate is " . round($daysSinceEffective / 30) . " months old. Consider checking for updates.";
        }
        
        // Check if rate period has ended
        if ($current['year_to']) {
            $yearTo = new DateTime($current['year_to']);
            if ($yearTo < $today) {
                $status['status'] = 'warning';
                $status['message'] = "Homecare Association Rate period ended on " . date('d M Y', strtotime($current['year_to'])) . ". A new rate may be available.";
            }
        }
        
        return $status;
    }
    
    /**
     * Get rate validation summary for display
     */
    public static function getRateValidationSummary() {
        $monitoring = self::getRateMonitoringStatus();
        
        $summary = [
            'total_rates' => 0,
            'current_rates' => 0,
            'outdated_rates' => 0,
            'missing_rates' => 0,
            'last_updated' => null
        ];
        
        foreach (['scotland_rate', 'rlw_rate', 'hca_rate'] as $rateType) {
            $rate = $monitoring[$rateType]['current'];
            $summary['total_rates']++;
            
            if ($rate) {
                $summary['current_rates']++;
                
                if ($monitoring[$rateType]['status'] === 'warning' || $monitoring[$rateType]['status'] === 'error') {
                    $summary['outdated_rates']++;
                }
                
                // Track most recent update
                $updateDate = isset($rate['created_at']) ? $rate['created_at'] : 
                             (isset($rate['effective_date']) ? $rate['effective_date'] : null);
                if ($updateDate && (!$summary['last_updated'] || $updateDate > $summary['last_updated'])) {
                    $summary['last_updated'] = $updateDate;
                }
            } else {
                $summary['missing_rates']++;
            }
        }
        
        return $summary;
    }
}
