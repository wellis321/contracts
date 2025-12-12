<?php
/**
 * Team Role Model
 * Manages custom team roles for organizations
 */

class TeamRole {
    
    /**
     * Find team role by ID
     */
    public static function findById($id) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT tr.*, o.name as organisation_name
            FROM team_roles tr
            JOIN organisations o ON tr.organisation_id = o.id
            WHERE tr.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Find all team roles for an organisation
     */
    public static function findByOrganisation($organisationId, $includeInactive = false) {
        $db = getDbConnection();
        $sql = "
            SELECT *
            FROM team_roles
            WHERE organisation_id = ?
        ";
        
        if (!$includeInactive) {
            $sql .= " AND is_active = 1";
        }
        
        $sql .= " ORDER BY display_order ASC, name ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$organisationId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if team role belongs to organisation
     */
    public static function belongsToOrganisation($roleId, $organisationId) {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT id FROM team_roles WHERE id = ? AND organisation_id = ?");
        $stmt->execute([$roleId, $organisationId]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Create team role
     */
    public static function create($organisationId, $name, $description = null, $accessLevel = 'team', $displayOrder = 0) {
        $db = getDbConnection();
        
        // Check if role with same name already exists
        $stmt = $db->prepare("SELECT id FROM team_roles WHERE organisation_id = ? AND name = ?");
        $stmt->execute([$organisationId, $name]);
        if ($stmt->fetch()) {
            throw new Exception("A team role with this name already exists.");
        }
        
        $stmt = $db->prepare("
            INSERT INTO team_roles (organisation_id, name, description, access_level, display_order)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $organisationId,
            $name,
            $description,
            $accessLevel,
            $displayOrder
        ]);
    }
    
    /**
     * Update team role
     */
    public static function update($id, $data) {
        $db = getDbConnection();
        
        // Check if name conflicts with another role
        if (isset($data['name'])) {
            $stmt = $db->prepare("SELECT organisation_id FROM team_roles WHERE id = ?");
            $stmt->execute([$id]);
            $role = $stmt->fetch();
            
            if ($role) {
                $stmt = $db->prepare("SELECT id FROM team_roles WHERE organisation_id = ? AND name = ? AND id != ?");
                $stmt->execute([$role['organisation_id'], $data['name'], $id]);
                if ($stmt->fetch()) {
                    throw new Exception("A team role with this name already exists.");
                }
            }
        }
        
        $fields = [];
        $values = [];
        
        if (isset($data['name'])) {
            $fields[] = "name = ?";
            $values[] = $data['name'];
        }
        if (isset($data['description'])) {
            $fields[] = "description = ?";
            $values[] = $data['description'];
        }
        if (isset($data['access_level'])) {
            $fields[] = "access_level = ?";
            $values[] = $data['access_level'];
        }
        if (isset($data['display_order'])) {
            $fields[] = "display_order = ?";
            $values[] = $data['display_order'];
        }
        if (isset($data['is_active'])) {
            $fields[] = "is_active = ?";
            $values[] = $data['is_active'] ? 1 : 0;
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $id;
        $sql = "UPDATE team_roles SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * Delete team role
     */
    public static function delete($id) {
        $db = getDbConnection();
        
        // Check if role is in use
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM user_teams WHERE team_role_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result && $result['count'] > 0) {
            throw new Exception("Cannot delete team role: it is currently assigned to " . $result['count'] . " user(s).");
        }
        
        $stmt = $db->prepare("DELETE FROM team_roles WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Initialize default roles for an organisation (if they don't exist)
     */
    public static function initializeDefaults($organisationId) {
        $defaults = [
            ['name' => 'Member', 'description' => 'Basic team membership with view access', 'access_level' => 'team', 'display_order' => 1],
            ['name' => 'Manager', 'description' => 'Can manage contracts assigned to their team and child teams', 'access_level' => 'team', 'display_order' => 2],
            ['name' => 'Admin', 'description' => 'Can manage contracts assigned to their team and child teams', 'access_level' => 'team', 'display_order' => 3],
            ['name' => 'Finance', 'description' => 'Can view and edit all contracts in the organisation', 'access_level' => 'organisation', 'display_order' => 4],
            ['name' => 'Senior Manager', 'description' => 'Can view and edit all contracts in the organisation', 'access_level' => 'organisation', 'display_order' => 5]
        ];
        
        foreach ($defaults as $role) {
            try {
                self::create(
                    $organisationId,
                    $role['name'],
                    $role['description'],
                    $role['access_level'],
                    $role['display_order']
                );
            } catch (Exception $e) {
                // Role already exists, skip
            }
        }
    }
}

