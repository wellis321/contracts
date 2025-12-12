<?php
/**
 * Audit Service
 * Provides helper methods for audit logging and approval workflows
 */
class AuditService {
    
    /**
     * Check if an action requires approval and create approval request if needed
     */
    public static function checkAndRequestApproval($entityType, $action, $entityId, $auditLogId, $fieldName = null) {
        $requiresApproval = ApprovalRule::requiresApproval($entityType, $action, $fieldName);
        
        if (!$requiresApproval) {
            // Update audit log to indicate no approval needed
            $db = getDbConnection();
            $stmt = $db->prepare("
                UPDATE audit_logs 
                SET approval_status = 'not_required',
                    approval_required = 0
                WHERE id = ?
            ");
            $stmt->execute([$auditLogId]);
            return true; // No approval needed, proceed
        }
        
        // Get applicable approval rules
        $rules = ApprovalRule::getRulesForAction($entityType, $action, $fieldName);
        
        foreach ($rules as $rule) {
            if ($rule['approval_type'] === 'self') {
                continue; // Skip self-approval rules
            }
            
            // Determine approver based on rule type
            $approverType = null;
            $approverId = null;
            $approverRoleId = null;
            $approverRoleName = null;
            
            switch ($rule['approval_type']) {
                case 'manager':
                    // Find user's manager (simplified - would need team hierarchy)
                    $approverId = self::getUserManager(Auth::getUserId(), $rule['manager_level']);
                    $approverType = 'manager';
                    break;
                    
                case 'role':
                    $approverRoleId = $rule['required_role_id'];
                    $approverRoleName = $rule['required_role_name'];
                    $approverType = 'role';
                    break;
                    
                case 'custom':
                    // Future: implement custom approval logic
                    break;
            }
            
            if ($approverType) {
                // Create approval request
                $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days')); // Default 7 day expiry
                
                ApprovalRequest::create(
                    $auditLogId,
                    $rule['id'],
                    $approverType,
                    $approverId,
                    $approverRoleId,
                    $approverRoleName,
                    $expiresAt
                );
            }
        }
        
        // Update audit log to indicate approval required
        $db = getDbConnection();
        $stmt = $db->prepare("
            UPDATE audit_logs 
            SET approval_status = 'pending',
                approval_required = 1
            WHERE id = ?
        ");
        $stmt->execute([$auditLogId]);
        
        return false; // Approval required, action should be pending
    }
    
    /**
     * Get user's manager (simplified - would need proper team hierarchy)
     */
    private static function getUserManager($userId, $level = 1) {
        // TODO: Implement proper manager lookup based on team hierarchy
        // For now, return null - this would need team management structure
        return null;
    }
    
    /**
     * Log entity creation with approval check
     */
    public static function logCreate($entityType, $entityId, $data, $metadata = []) {
        $auditLogId = AuditLog::logCreate($entityType, $entityId, $data, $metadata);
        
        if ($auditLogId) {
            self::checkAndRequestApproval($entityType, 'create', $entityId, $auditLogId);
        }
        
        return $auditLogId;
    }
    
    /**
     * Log entity update with approval check
     */
    public static function logUpdate($entityType, $entityId, $oldData, $newData, $fieldName = null, $metadata = []) {
        $auditLogId = AuditLog::logUpdate($entityType, $entityId, $oldData, $newData, $fieldName, $metadata);
        
        if ($auditLogId) {
            $requiresApproval = self::checkAndRequestApproval($entityType, 'update', $entityId, $auditLogId, $fieldName);
            return ['audit_log_id' => $auditLogId, 'requires_approval' => !$requiresApproval];
        }
        
        return null;
    }
    
    /**
     * Log entity deletion with approval check
     */
    public static function logDelete($entityType, $entityId, $data, $metadata = []) {
        $auditLogId = AuditLog::logDelete($entityType, $entityId, $data, $metadata);
        
        if ($auditLogId) {
            $requiresApproval = self::checkAndRequestApproval($entityType, 'delete', $entityId, $auditLogId);
            return ['audit_log_id' => $auditLogId, 'requires_approval' => !$requiresApproval];
        }
        
        return null;
    }
    
    /**
     * Check if an action can proceed (no pending approvals)
     */
    public static function canProceed($entityType, $entityId, $action) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        
        $stmt = $db->prepare("
            SELECT COUNT(*) as count
            FROM audit_logs al
            INNER JOIN approval_requests ar ON al.id = ar.audit_log_id
            WHERE al.organisation_id = ?
            AND al.entity_type = ?
            AND al.entity_id = ?
            AND al.action = ?
            AND ar.status = 'pending'
        ");
        
        $stmt->execute([$organisationId, $entityType, $entityId, $action]);
        $result = $stmt->fetch();
        
        return ($result['count'] ?? 0) === 0;
    }
}

