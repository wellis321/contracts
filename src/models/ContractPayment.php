<?php
/**
 * Contract Payment Model
 */
class ContractPayment {
    
    /**
     * Get payments for a contract
     */
    public static function findByContract($contractId) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT cp.*, pm.name as payment_method_name
            FROM contract_payments cp
            LEFT JOIN payment_methods pm ON cp.payment_method_id = pm.id
            WHERE cp.contract_id = ?
            ORDER BY cp.payment_date DESC, cp.created_at DESC
        ");
        $stmt->execute([$contractId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Create payment record
     */
    public static function create($contractId, $paymentMethodId, $amount, $paymentDate = null, $description = null, $paymentFrequency = null) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            INSERT INTO contract_payments (contract_id, payment_method_id, payment_frequency, amount, payment_date, description)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $contractId,
            $paymentMethodId,
            $paymentFrequency,
            $amount,
            $paymentDate,
            $description
        ]);
    }
    
    /**
     * Update payment record
     */
    public static function update($id, $paymentMethodId, $amount, $paymentDate = null, $description = null, $paymentFrequency = null) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            UPDATE contract_payments 
            SET payment_method_id = ?, payment_frequency = ?, amount = ?, payment_date = ?, description = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $paymentMethodId,
            $paymentFrequency,
            $amount,
            $paymentDate,
            $description,
            $id
        ]);
    }
    
    /**
     * Delete payment record
     */
    public static function delete($id) {
        $db = getDbConnection();
        $stmt = $db->prepare("DELETE FROM contract_payments WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Verify payment belongs to organisation's contract
     */
    public static function belongsToOrganisation($id, $organisationId) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT cp.id 
            FROM contract_payments cp
            JOIN contracts c ON cp.contract_id = c.id
            WHERE cp.id = ? AND c.organisation_id = ?
        ");
        $stmt->execute([$id, $organisationId]);
        return $stmt->fetch() !== false;
    }
}
