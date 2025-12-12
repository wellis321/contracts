<?php
/**
 * Rate Model
 */
class Rate {
    
    /**
     * Get current rate for contract type and local authority
     */
    public static function getCurrentRate($contractTypeId, $localAuthorityId) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT * FROM rates 
            WHERE contract_type_id = ? 
            AND local_authority_id = ? 
            AND is_current = 1
            ORDER BY effective_from DESC
            LIMIT 1
        ");
        $stmt->execute([$contractTypeId, $localAuthorityId]);
        return $stmt->fetch();
    }
    
    /**
     * Get all rates for contract type
     */
    public static function findByContractType($contractTypeId) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT r.*, la.name as local_authority_name
            FROM rates r
            LEFT JOIN local_authorities la ON r.local_authority_id = la.id
            WHERE r.contract_type_id = ?
            ORDER BY r.effective_from DESC, la.name
        ");
        $stmt->execute([$contractTypeId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if a rate can be modified (only current rates can be modified)
     */
    public static function canModify($rateId) {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT is_current FROM rates WHERE id = ?");
        $stmt->execute([$rateId]);
        $rate = $stmt->fetch();
        return $rate && $rate['is_current'] == 1;
    }
    
    /**
     * Get rate by ID
     */
    public static function findById($rateId) {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM rates WHERE id = ?");
        $stmt->execute([$rateId]);
        return $stmt->fetch();
    }
    
    /**
     * Update an existing rate (only if it's the current rate)
     * 
     * IMPORTANT: This method only allows updating rates where is_current = 1.
     * Historical rates (is_current = 0) are immutable and cannot be modified.
     * This ensures data integrity - once a rate is superseded by a new rate,
     * the old rate becomes a permanent historical record.
     */
    public static function updateRate($rateId, $rateAmount, $effectiveFrom, $changedBy = null) {
        $db = getDbConnection();
        
        // Check if rate exists and is current
        $rate = self::findById($rateId);
        if (!$rate) {
            throw new Exception('Rate not found.');
        }
        
        if ($rate['is_current'] != 1) {
            throw new Exception('Cannot modify historical rates. Only current rates can be updated. Please create a new rate instead.');
        }
        
        try {
            $db->beginTransaction();
            
            // Record change in history
            $stmt = $db->prepare("
                INSERT INTO rate_history (
                    rate_id, contract_type_id, local_authority_id,
                    previous_rate, new_rate, changed_by
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $rateId,
                $rate['contract_type_id'],
                $rate['local_authority_id'],
                $rate['rate_amount'],
                $rateAmount,
                $changedBy
            ]);
            
            // Update the rate
            $stmt = $db->prepare("
                UPDATE rates 
                SET rate_amount = ?, effective_from = ?, updated_at = NOW()
                WHERE id = ? AND is_current = 1
            ");
            $stmt->execute([$rateAmount, $effectiveFrom, $rateId]);
            
            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Create or update rate
     * 
     * IMPORTANT: This method always creates a NEW rate. It never modifies existing rates.
     * When a new rate is set, the old current rate is marked as is_current = 0.
     * Historical rates (is_current = 0) cannot be modified - they are immutable.
     * This ensures data integrity and prevents accidental changes to historical rate data.
     */
    public static function setRate($contractTypeId, $localAuthorityId, $rateAmount, $effectiveFrom, $changedBy = null) {
        $db = getDbConnection();
        
        try {
            $db->beginTransaction();
            
            // Get current rate
            $currentRate = self::getCurrentRate($contractTypeId, $localAuthorityId);
            
            if ($currentRate) {
                // Mark old rate as not current (this is the only allowed update to non-current rates)
                $stmt = $db->prepare("UPDATE rates SET is_current = 0 WHERE id = ? AND is_current = 1");
                $stmt->execute([$currentRate['id']]);
                
                if ($stmt->rowCount() === 0) {
                    throw new Exception('Cannot modify historical rates. The previous rate is no longer current.');
                }
                
                // Record in history
                $stmt = $db->prepare("
                    INSERT INTO rate_history (
                        rate_id, contract_type_id, local_authority_id,
                        previous_rate, new_rate, changed_by
                    ) VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $currentRate['id'],
                    $contractTypeId,
                    $localAuthorityId,
                    $currentRate['rate_amount'],
                    $rateAmount,
                    $changedBy
                ]);
            }
            
            // Create new rate
            $stmt = $db->prepare("
                INSERT INTO rates (
                    contract_type_id, local_authority_id, rate_amount, 
                    effective_from, is_current
                ) VALUES (?, ?, ?, ?, 1)
            ");
            $stmt->execute([
                $contractTypeId,
                $localAuthorityId,
                $rateAmount,
                $effectiveFrom
            ]);
            
            $rateId = $db->lastInsertId();
            
            // If no previous rate, still record in history
            if (!$currentRate && $changedBy) {
                $stmt = $db->prepare("
                    INSERT INTO rate_history (
                        rate_id, contract_type_id, local_authority_id,
                        previous_rate, new_rate, changed_by
                    ) VALUES (?, ?, ?, NULL, ?, ?)
                ");
                $stmt->execute([
                    $rateId,
                    $contractTypeId,
                    $localAuthorityId,
                    $rateAmount,
                    $changedBy
                ]);
            }
            
            $db->commit();
            return $rateId;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Get rate history
     */
    public static function getHistory($contractTypeId, $localAuthorityId = null) {
        $db = getDbConnection();
        $sql = "
            SELECT rh.*, 
                   la.name as local_authority_name,
                   u.first_name, u.last_name
            FROM rate_history rh
            LEFT JOIN local_authorities la ON rh.local_authority_id = la.id
            LEFT JOIN users u ON rh.changed_by = u.id
            WHERE rh.contract_type_id = ?
        ";
        $params = [$contractTypeId];
        
        if ($localAuthorityId) {
            $sql .= " AND rh.local_authority_id = ?";
            $params[] = $localAuthorityId;
        }
        
        $sql .= " ORDER BY rh.changed_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
