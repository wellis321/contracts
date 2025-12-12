<?php
/**
 * Audit Log Model
 * Tracks all changes across the application
 */
class AuditLog {
    
    /**
     * Log a create action
     */
    public static function logCreate($entityType, $entityId, $data, $metadata = []) {
        return self::log('create', $entityType, $entityId, null, $data, null, $metadata);
    }
    
    /**
     * Log an update action
     */
    public static function logUpdate($entityType, $entityId, $oldData, $newData, $fieldName = null, $metadata = []) {
        // Calculate changes
        $changes = [];
        if ($fieldName) {
            // Single field update
            $changes[$fieldName] = [
                'old' => $oldData,
                'new' => $newData
            ];
        } else {
            // Multiple field update - compare arrays
            foreach ($newData as $key => $newValue) {
                $oldValue = $oldData[$key] ?? null;
                if ($oldValue != $newValue) {
                    $changes[$key] = [
                        'old' => $oldValue,
                        'new' => $newValue
                    ];
                }
            }
        }
        
        return self::log('update', $entityType, $entityId, $oldData, $newData, $fieldName, array_merge($metadata, ['changes' => $changes]));
    }
    
    /**
     * Log a delete action
     */
    public static function logDelete($entityType, $entityId, $data, $metadata = []) {
        return self::log('delete', $entityType, $entityId, $data, null, null, $metadata);
    }
    
    /**
     * Log an approval action
     */
    public static function logApproval($entityType, $entityId, $action, $approvedBy, $metadata = []) {
        return self::log('approve', $entityType, $entityId, null, ['approved_by' => $approvedBy], null, $metadata);
    }
    
    /**
     * Log a rejection action
     */
    public static function logRejection($entityType, $entityId, $rejectionReason, $rejectedBy, $metadata = []) {
        return self::log('reject', $entityType, $entityId, null, ['rejected_by' => $rejectedBy, 'reason' => $rejectionReason], null, $metadata);
    }
    
    /**
     * Core logging method
     */
    private static function log($action, $entityType, $entityId, $oldValue, $newValue, $fieldName = null, $metadata = []) {
        if (!Auth::isLoggedIn()) {
            return null; // Can't log without a user
        }
        
        $db = getDbConnection();
        $userId = Auth::getUserId();
        $organisationId = Auth::getOrganisationId();
        
        // Get request information
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $requestUrl = $_SERVER['REQUEST_URI'] ?? null;
        
        // Serialize values for storage
        $oldValueSerialized = $oldValue ? (is_array($oldValue) ? json_encode($oldValue) : $oldValue) : null;
        $newValueSerialized = $newValue ? (is_array($newValue) ? json_encode($newValue) : $newValue) : null;
        
        // Extract changes from metadata if present
        $changes = $metadata['changes'] ?? null;
        if ($changes && is_array($changes)) {
            $changes = json_encode($changes);
        }
        
        // Build metadata JSON
        $metadataJson = !empty($metadata) ? json_encode($metadata) : null;
        
        try {
            $stmt = $db->prepare("
                INSERT INTO audit_logs (
                    organisation_id, user_id, entity_type, entity_id, action,
                    field_name, old_value, new_value, changes,
                    ip_address, user_agent, request_url, metadata
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $organisationId,
                $userId,
                $entityType,
                $entityId,
                $action,
                $fieldName,
                $oldValueSerialized,
                $newValueSerialized,
                $changes,
                $ipAddress,
                $userAgent,
                $requestUrl,
                $metadataJson
            ]);
            
            return $db->lastInsertId();
        } catch (Exception $e) {
            // Log error but don't break the application
            error_log("Audit log error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get audit logs for an entity
     */
    public static function findByEntity($entityType, $entityId, $limit = 100) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        
        $stmt = $db->prepare("
            SELECT al.*, 
                   u.first_name, u.last_name, u.email,
                   approver.first_name as approver_first_name,
                   approver.last_name as approver_last_name
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            LEFT JOIN users approver ON al.approved_by = approver.id
            WHERE al.organisation_id = ? 
            AND al.entity_type = ? 
            AND al.entity_id = ?
            ORDER BY al.created_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$organisationId, $entityType, $entityId, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get audit logs for an organisation with filtering
     */
    public static function findByOrganisation($organisationId, $filters = [], $limit = 100, $offset = 0) {
        $db = getDbConnection();
        
        $sql = "
            SELECT al.*, 
                   u.first_name, u.last_name, u.email,
                   approver.first_name as approver_first_name,
                   approver.last_name as approver_last_name
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            LEFT JOIN users approver ON al.approved_by = approver.id
            WHERE al.organisation_id = ?
        ";
        
        $params = [$organisationId];
        
        // Apply filters
        if (!empty($filters['entity_type'])) {
            $sql .= " AND al.entity_type = ?";
            $params[] = $filters['entity_type'];
        }
        
        if (!empty($filters['action'])) {
            $sql .= " AND al.action = ?";
            $params[] = $filters['action'];
        }
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND al.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND al.created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (al.entity_type LIKE ? OR al.field_name LIKE ? OR al.old_value LIKE ? OR al.new_value LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get count of audit logs for an organisation
     */
    public static function countByOrganisation($organisationId, $filters = []) {
        $db = getDbConnection();
        
        $sql = "SELECT COUNT(*) as count FROM audit_logs WHERE organisation_id = ?";
        $params = [$organisationId];
        
        // Apply same filters as findByOrganisation
        if (!empty($filters['entity_type'])) {
            $sql .= " AND entity_type = ?";
            $params[] = $filters['entity_type'];
        }
        
        if (!empty($filters['action'])) {
            $sql .= " AND action = ?";
            $params[] = $filters['action'];
        }
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (entity_type LIKE ? OR field_name LIKE ? OR old_value LIKE ? OR new_value LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get audit log by ID
     */
    public static function findById($id) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        
        $stmt = $db->prepare("
            SELECT al.*, 
                   u.first_name, u.last_name, u.email,
                   approver.first_name as approver_first_name,
                   approver.last_name as approver_last_name
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            LEFT JOIN users approver ON al.approved_by = approver.id
            WHERE al.id = ? AND al.organisation_id = ?
        ");
        
        $stmt->execute([$id, $organisationId]);
        return $stmt->fetch();
    }
}

