<?php
/**
 * Team Type Model
 * Handles organization-specific team types
 */
class TeamType {
    
    /**
     * Get all team types for an organisation
     */
    public static function findByOrganisation($organisationId, $includeInactive = false) {
        $db = getDbConnection();
        $sql = "SELECT * FROM team_types WHERE organisation_id = ?";
        if (!$includeInactive) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY display_order, name";
        $stmt = $db->prepare($sql);
        $stmt->execute([$organisationId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get team type by ID
     */
    public static function findById($id) {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM team_types WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Create team type
     */
    public static function create($organisationId, $name, $description = null, $displayOrder = 0) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            INSERT INTO team_types (organisation_id, name, description, display_order)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([
            $organisationId,
            $name,
            $description,
            $displayOrder
        ]);
    }
    
    /**
     * Update team type
     */
    public static function update($id, $data) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            UPDATE team_types SET
                name = ?,
                description = ?,
                display_order = ?,
                is_active = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['display_order'] ?? 0,
            $data['is_active'] ?? true,
            $id
        ]);
    }
    
    /**
     * Delete team type (only if no teams use it)
     */
    public static function delete($id) {
        $db = getDbConnection();
        
        // Check if any teams use this type
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM teams WHERE team_type_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            throw new Exception("Cannot delete team type: teams are still using it.");
        }
        
        $stmt = $db->prepare("DELETE FROM team_types WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Verify team type belongs to organisation
     */
    public static function belongsToOrganisation($id, $organisationId) {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT id FROM team_types WHERE id = ? AND organisation_id = ?");
        $stmt->execute([$id, $organisationId]);
        return $stmt->fetch() !== false;
    }
}

