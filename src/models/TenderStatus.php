<?php
/**
 * Tender Status Model
 */

class TenderStatus {
    
    /**
     * Get all tender statuses
     */
    public static function findAll($includeInactive = false) {
        $db = getDbConnection();
        $sql = "SELECT * FROM tender_statuses";
        if (!$includeInactive) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY display_order, name";
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get tender status by name
     */
    public static function findByName($name) {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM tender_statuses WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch();
    }
}
