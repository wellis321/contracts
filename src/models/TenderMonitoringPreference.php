<?php
/**
 * Tender Monitoring Preference Model
 */
class TenderMonitoringPreference {
    
    /**
     * Create monitoring preference
     */
    public static function create($data) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        $userId = Auth::getUserId();
        
        $stmt = $db->prepare("
            INSERT INTO tender_monitoring_preferences (
                organisation_id, user_id, keywords, local_authority_ids,
                contract_type_ids, cpv_codes, min_value, max_value,
                notification_method, email_address, notify_immediately,
                notify_daily_summary, notify_weekly_summary, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $organisationId,
            $userId,
            $data['keywords'] ?? null,
            isset($data['local_authority_ids']) ? json_encode($data['local_authority_ids']) : null,
            isset($data['contract_type_ids']) ? json_encode($data['contract_type_ids']) : null,
            isset($data['cpv_codes']) ? json_encode($data['cpv_codes']) : null,
            $data['min_value'] ?? null,
            $data['max_value'] ?? null,
            $data['notification_method'] ?? 'email',
            $data['email_address'] ?? null,
            $data['notify_immediately'] ?? true ? 1 : 0,
            $data['notify_daily_summary'] ?? false ? 1 : 0,
            $data['notify_weekly_summary'] ?? false ? 1 : 0,
            $data['is_active'] ?? true ? 1 : 0
        ]);
        
        return $db->lastInsertId();
    }
    
    /**
     * Get all monitoring preferences for organisation
     */
    public static function findByOrganisation($organisationId) {
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            SELECT tm.*, u.first_name, u.last_name, u.email as user_email
            FROM tender_monitoring_preferences tm
            LEFT JOIN users u ON tm.user_id = u.id
            WHERE tm.organisation_id = ?
            ORDER BY tm.created_at DESC
        ");
        
        $stmt->execute([$organisationId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get monitoring preference by ID
     */
    public static function findById($id) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        
        $stmt = $db->prepare("
            SELECT tm.*, u.first_name, u.last_name, u.email as user_email
            FROM tender_monitoring_preferences tm
            LEFT JOIN users u ON tm.user_id = u.id
            WHERE tm.id = ? AND tm.organisation_id = ?
        ");
        
        $stmt->execute([$id, $organisationId]);
        return $stmt->fetch();
    }
    
    /**
     * Update monitoring preference
     */
    public static function update($id, $data) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        
        $updates = [];
        $params = [];
        
        $allowedFields = [
            'keywords', 'local_authority_ids', 'contract_type_ids', 'cpv_codes',
            'min_value', 'max_value', 'notification_method', 'email_address',
            'notify_immediately', 'notify_daily_summary', 'notify_weekly_summary', 'is_active'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if (in_array($field, ['local_authority_ids', 'contract_type_ids', 'cpv_codes'])) {
                    $updates[] = "$field = ?";
                    $params[] = json_encode($data[$field]);
                } elseif (in_array($field, ['notify_immediately', 'notify_daily_summary', 'notify_weekly_summary', 'is_active'])) {
                    $updates[] = "$field = ?";
                    $params[] = $data[$field] ? 1 : 0;
                } else {
                    $updates[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $params[] = $id;
        $params[] = $organisationId;
        
        $sql = "UPDATE tender_monitoring_preferences SET " . implode(', ', $updates) . 
               " WHERE id = ? AND organisation_id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Delete monitoring preference
     */
    public static function delete($id) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        
        $stmt = $db->prepare("
            DELETE FROM tender_monitoring_preferences 
            WHERE id = ? AND organisation_id = ?
        ");
        return $stmt->execute([$id, $organisationId]);
    }
}

