<?php
/**
 * Payment Method Model
 */
class PaymentMethod {
    
    /**
     * Get all payment methods
     */
    public static function findAll($includeInactive = false) {
        $db = getDbConnection();
        $sql = "SELECT * FROM payment_methods";
        if (!$includeInactive) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY name";
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get payment method by ID
     */
    public static function findById($id) {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM payment_methods WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
