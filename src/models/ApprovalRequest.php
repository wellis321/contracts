<?php
/**
 * Approval Request Model
 * Manages pending approval requests
 */
class ApprovalRequest {
    
    /**
     * Create an approval request
     */
    public static function create($auditLogId, $approvalRuleId, $approverType, $approverId = null, $approverRoleId = null, $approverRoleName = null, $expiresAt = null) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        $userId = Auth::getUserId();
        
        $stmt = $db->prepare("
            INSERT INTO approval_requests (
                organisation_id, audit_log_id, requested_by, approval_rule_id,
                approver_type, approver_id, approver_role_id, approver_role_name,
                status, expires_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)
        ");
        
        $stmt->execute([
            $organisationId,
            $auditLogId,
            $userId,
            $approvalRuleId,
            $approverType,
            $approverId,
            $approverRoleId,
            $approverRoleName,
            $expiresAt
        ]);
        
        return $db->lastInsertId();
    }
    
    /**
     * Get pending approval requests for current user
     */
    public static function getPendingForUser($userId) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        
        // Get user's roles
        $roleStmt = $db->prepare("
            SELECT role_id FROM user_roles WHERE user_id = ?
        ");
        $roleStmt->execute([$userId]);
        $userRoleIds = array_column($roleStmt->fetchAll(), 'role_id');
        
        $placeholders = '';
        $params = [$organisationId, $userId];
        
        if (!empty($userRoleIds)) {
            $placeholders = implode(',', array_fill(0, count($userRoleIds), '?'));
            $params = array_merge($params, $userRoleIds);
        }
        
        $sql = "
            SELECT ar.*, 
                   al.entity_type, al.entity_id, al.action, al.field_name,
                   al.old_value, al.new_value, al.changes,
                   requester.first_name as requester_first_name,
                   requester.last_name as requester_last_name,
                   requester.email as requester_email,
                   rule.entity_type as rule_entity_type,
                   rule.action as rule_action
            FROM approval_requests ar
            INNER JOIN audit_logs al ON ar.audit_log_id = al.id
            LEFT JOIN users requester ON ar.requested_by = requester.id
            LEFT JOIN approval_rules rule ON ar.approval_rule_id = rule.id
            WHERE ar.organisation_id = ?
            AND ar.status = 'pending'
            AND (
                (ar.approver_type = 'user' AND ar.approver_id = ?)
                " . (!empty($placeholders) ? "OR (ar.approver_type = 'role' AND ar.approver_role_id IN ($placeholders))" : "") . "
                OR (ar.approver_type = 'manager' AND ar.approver_id = ?)
            )
            AND (ar.expires_at IS NULL OR ar.expires_at > NOW())
            ORDER BY ar.created_at DESC
        ";
        
        if (!empty($placeholders)) {
            $params[] = $userId; // For manager check
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all pending approval requests for organisation
     */
    public static function getPendingForOrganisation($organisationId) {
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            SELECT ar.*, 
                   al.entity_type, al.entity_id, al.action, al.field_name,
                   al.old_value, al.new_value, al.changes,
                   requester.first_name as requester_first_name,
                   requester.last_name as requester_last_name,
                   requester.email as requester_email,
                   rule.entity_type as rule_entity_type,
                   rule.action as rule_action
            FROM approval_requests ar
            INNER JOIN audit_logs al ON ar.audit_log_id = al.id
            LEFT JOIN users requester ON ar.requested_by = requester.id
            LEFT JOIN approval_rules rule ON ar.approval_rule_id = rule.id
            WHERE ar.organisation_id = ?
            AND ar.status = 'pending'
            AND (ar.expires_at IS NULL OR ar.expires_at > NOW())
            ORDER BY ar.created_at DESC
        ");
        
        $stmt->execute([$organisationId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Approve a request
     */
    public static function approve($requestId, $rejectionReason = null) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        $userId = Auth::getUserId();
        
        // Get the request
        $stmt = $db->prepare("
            SELECT ar.*, al.entity_type, al.entity_id
            FROM approval_requests ar
            INNER JOIN audit_logs al ON ar.audit_log_id = al.id
            WHERE ar.id = ? AND ar.organisation_id = ?
        ");
        $stmt->execute([$requestId, $organisationId]);
        $request = $stmt->fetch();
        
        if (!$request || $request['status'] !== 'pending') {
            return false;
        }
        
        // Update approval request
        $stmt = $db->prepare("
            UPDATE approval_requests
            SET status = 'approved',
                approved_by = ?,
                approved_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$userId, $requestId]);
        
        // Update audit log
        $stmt = $db->prepare("
            UPDATE audit_logs
            SET approval_status = 'approved',
                approved_by = ?,
                approved_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$userId, $request['audit_log_id']]);
        
        // Log the approval
        AuditLog::logApproval($request['entity_type'], $request['entity_id'], 'approve', $userId);
        
        return true;
    }
    
    /**
     * Reject a request
     */
    public static function reject($requestId, $rejectionReason) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        $userId = Auth::getUserId();
        
        // Get the request
        $stmt = $db->prepare("
            SELECT ar.*, al.entity_type, al.entity_id
            FROM approval_requests ar
            INNER JOIN audit_logs al ON ar.audit_log_id = al.id
            WHERE ar.id = ? AND ar.organisation_id = ?
        ");
        $stmt->execute([$requestId, $organisationId]);
        $request = $stmt->fetch();
        
        if (!$request || $request['status'] !== 'pending') {
            return false;
        }
        
        // Update approval request
        $stmt = $db->prepare("
            UPDATE approval_requests
            SET status = 'rejected',
                approved_by = ?,
                approved_at = NOW(),
                rejection_reason = ?
            WHERE id = ?
        ");
        $stmt->execute([$userId, $rejectionReason, $requestId]);
        
        // Update audit log
        $stmt = $db->prepare("
            UPDATE audit_logs
            SET approval_status = 'rejected',
                approved_by = ?,
                approved_at = NOW(),
                rejection_reason = ?
            WHERE id = ?
        ");
        $stmt->execute([$userId, $rejectionReason, $request['audit_log_id']]);
        
        // Log the rejection
        AuditLog::logRejection($request['entity_type'], $request['entity_id'], $rejectionReason, $userId);
        
        return true;
    }
    
    /**
     * Get approval request by ID
     */
    public static function findById($id) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        
        $stmt = $db->prepare("
            SELECT ar.*, 
                   al.entity_type, al.entity_id, al.action, al.field_name,
                   al.old_value, al.new_value, al.changes,
                   requester.first_name as requester_first_name,
                   requester.last_name as requester_last_name,
                   requester.email as requester_email
            FROM approval_requests ar
            INNER JOIN audit_logs al ON ar.audit_log_id = al.id
            LEFT JOIN users requester ON ar.requested_by = requester.id
            WHERE ar.id = ? AND ar.organisation_id = ?
        ");
        
        $stmt->execute([$id, $organisationId]);
        return $stmt->fetch();
    }
}

