<?php
/**
 * Approval Rule Model
 * Manages configurable approval requirements
 */
class ApprovalRule {
    
    /**
     * Get approval rules for an entity type and action
     */
    public static function getRulesForAction($entityType, $action, $fieldName = null) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        
        $sql = "
            SELECT * FROM approval_rules
            WHERE organisation_id = ?
            AND entity_type = ?
            AND is_active = 1
            AND (action = ? OR action = '*')
        ";
        
        $params = [$organisationId, $entityType, $action];
        
        if ($fieldName) {
            $sql .= " AND (field_name = ? OR field_name IS NULL)";
            $params[] = $fieldName;
        } else {
            $sql .= " AND field_name IS NULL";
        }
        
        $sql .= " ORDER BY priority DESC, id ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all approval rules for an organisation
     */
    public static function findByOrganisation($organisationId) {
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            SELECT ar.*, r.name as role_name
            FROM approval_rules ar
            LEFT JOIN roles r ON ar.required_role_id = r.id
            WHERE ar.organisation_id = ?
            ORDER BY ar.entity_type, ar.action, ar.priority DESC
        ");
        
        $stmt->execute([$organisationId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get approval rule by ID
     */
    public static function findById($id) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        
        $stmt = $db->prepare("
            SELECT ar.*, r.name as role_name
            FROM approval_rules ar
            LEFT JOIN roles r ON ar.required_role_id = r.id
            WHERE ar.id = ? AND ar.organisation_id = ?
        ");
        
        $stmt->execute([$id, $organisationId]);
        return $stmt->fetch();
    }
    
    /**
     * Create a new approval rule
     */
    public static function create($data) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        $userId = Auth::getUserId();
        
        // Get role ID if role name provided
        $roleId = null;
        if (!empty($data['required_role_name'])) {
            $roleStmt = $db->prepare("SELECT id FROM roles WHERE name = ?");
            $roleStmt->execute([$data['required_role_name']]);
            $role = $roleStmt->fetch();
            $roleId = $role['id'] ?? null;
        } elseif (!empty($data['required_role_id'])) {
            $roleId = $data['required_role_id'];
        }
        
        $stmt = $db->prepare("
            INSERT INTO approval_rules (
                organisation_id, entity_type, action, field_name,
                approval_type, required_role_id, required_role_name,
                manager_level, custom_condition, is_active, priority, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $organisationId,
            $data['entity_type'],
            $data['action'] ?? '*',
            $data['field_name'] ?? null,
            $data['approval_type'] ?? 'self',
            $roleId,
            $data['required_role_name'] ?? null,
            $data['manager_level'] ?? 1,
            $data['custom_condition'] ? json_encode($data['custom_condition']) : null,
            $data['is_active'] ?? true ? 1 : 0,
            $data['priority'] ?? 0,
            $userId
        ]);
        
        return $db->lastInsertId();
    }
    
    /**
     * Update an approval rule
     */
    public static function update($id, $data) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        
        // Get role ID if role name provided
        $roleId = null;
        if (!empty($data['required_role_name'])) {
            $roleStmt = $db->prepare("SELECT id FROM roles WHERE name = ?");
            $roleStmt->execute([$data['required_role_name']]);
            $role = $roleStmt->fetch();
            $roleId = $role['id'] ?? null;
        } elseif (!empty($data['required_role_id'])) {
            $roleId = $data['required_role_id'];
        }
        
        $updates = [];
        $params = [];
        
        if (isset($data['entity_type'])) {
            $updates[] = "entity_type = ?";
            $params[] = $data['entity_type'];
        }
        
        if (isset($data['action'])) {
            $updates[] = "action = ?";
            $params[] = $data['action'];
        }
        
        if (isset($data['field_name'])) {
            $updates[] = "field_name = ?";
            $params[] = $data['field_name'];
        }
        
        if (isset($data['approval_type'])) {
            $updates[] = "approval_type = ?";
            $params[] = $data['approval_type'];
        }
        
        if (isset($roleId)) {
            $updates[] = "required_role_id = ?";
            $params[] = $roleId;
        }
        
        if (isset($data['required_role_name'])) {
            $updates[] = "required_role_name = ?";
            $params[] = $data['required_role_name'];
        }
        
        if (isset($data['manager_level'])) {
            $updates[] = "manager_level = ?";
            $params[] = $data['manager_level'];
        }
        
        if (isset($data['custom_condition'])) {
            $updates[] = "custom_condition = ?";
            $params[] = json_encode($data['custom_condition']);
        }
        
        if (isset($data['is_active'])) {
            $updates[] = "is_active = ?";
            $params[] = $data['is_active'] ? 1 : 0;
        }
        
        if (isset($data['priority'])) {
            $updates[] = "priority = ?";
            $params[] = $data['priority'];
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $params[] = $id;
        $params[] = $organisationId;
        
        $sql = "UPDATE approval_rules SET " . implode(', ', $updates) . " WHERE id = ? AND organisation_id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Delete an approval rule
     */
    public static function delete($id) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        
        $stmt = $db->prepare("DELETE FROM approval_rules WHERE id = ? AND organisation_id = ?");
        return $stmt->execute([$id, $organisationId]);
    }
    
    /**
     * Check if an action requires approval
     */
    public static function requiresApproval($entityType, $action, $fieldName = null) {
        $rules = self::getRulesForAction($entityType, $action, $fieldName);
        
        if (empty($rules)) {
            return false; // No rules = no approval needed
        }
        
        // Check if any rule requires approval (not 'self')
        foreach ($rules as $rule) {
            if ($rule['approval_type'] !== 'self') {
                return true;
            }
        }
        
        return false;
    }
}

