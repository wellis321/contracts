<?php
/**
 * Contract Type Model
 */
class ContractType {
    
    /**
     * Get contract type by ID
     */
    public static function findById($id) {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM contract_types WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all contract types for an organisation
     * Includes both system defaults and organisation-specific types
     */
    public static function findByOrganisation($organisationId, $includeInactive = false) {
        $db = getDbConnection();
        // Get contract types, ensuring no duplicates by name
        // Prioritize organisation-specific types over system defaults when names match
        $sql = "
            SELECT ct.* FROM (
                SELECT *,
                       ROW_NUMBER() OVER (
                           PARTITION BY name 
                           ORDER BY CASE WHEN organisation_id = ? THEN 0 ELSE 1 END, id
                       ) as rn
                FROM contract_types
                WHERE (organisation_id = ? OR (organisation_id IS NULL AND is_system_default = 1))
        ";
        if (!$includeInactive) {
            $sql .= " AND is_active = 1";
        }
        $sql .= "
            ) ct
            WHERE ct.rn = 1
            ORDER BY ct.is_system_default DESC, ct.name
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$organisationId, $organisationId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get only system default contract types
     */
    public static function findSystemDefaults($includeInactive = false) {
        $db = getDbConnection();
        $sql = "SELECT * FROM contract_types WHERE organisation_id IS NULL AND is_system_default = 1";
        if (!$includeInactive) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY name";
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get only organisation-specific contract types (not system defaults)
     */
    public static function findCustomByOrganisation($organisationId, $includeInactive = false) {
        $db = getDbConnection();
        $sql = "SELECT * FROM contract_types WHERE organisation_id = ?";
        if (!$includeInactive) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY name";
        $stmt = $db->prepare($sql);
        $stmt->execute([$organisationId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Create new contract type
     */
    public static function create($organisationId, $name, $description = null) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            INSERT INTO contract_types (organisation_id, name, description)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$organisationId, $name, $description]);
        return $db->lastInsertId();
    }
    
    /**
     * Update contract type
     */
    public static function update($id, $name, $description = null, $isActive = true) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            UPDATE contract_types 
            SET name = ?, description = ?, is_active = ?
            WHERE id = ?
        ");
        return $stmt->execute([$name, $description, $isActive ? 1 : 0, $id]);
    }
    
    /**
     * Delete contract type (soft delete)
     */
    public static function delete($id) {
        $db = getDbConnection();
        $stmt = $db->prepare("UPDATE contract_types SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Verify contract type belongs to organisation or is a system default
     */
    public static function belongsToOrganisation($id, $organisationId) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT id FROM contract_types 
            WHERE id = ? 
            AND (organisation_id = ? OR (organisation_id IS NULL AND is_system_default = 1))
        ");
        $stmt->execute([$id, $organisationId]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Check if contract type is a system default
     */
    public static function isSystemDefault($id) {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT is_system_default FROM contract_types WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result && $result['is_system_default'] == 1;
    }
}
