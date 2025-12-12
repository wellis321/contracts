<?php
/**
 * Procurement Route Model
 */

class ProcurementRoute {
    
    /**
     * Get all procurement routes
     */
    public static function findAll($includeInactive = false) {
        $db = getDbConnection();
        $sql = "SELECT * FROM procurement_routes";
        if (!$includeInactive) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY name";
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get procurement route by name
     */
    public static function findByName($name) {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM procurement_routes WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch();
    }
}
