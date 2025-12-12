<?php
/**
 * Organisation Model
 */
class Organisation {
    
    /**
     * Get organisation by ID
     */
    public static function findById($id) {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM organisations WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get organisation by domain
     */
    public static function findByDomain($domain) {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM organisations WHERE domain = ?");
        $stmt->execute([$domain]);
        return $stmt->fetch();
    }
    
    /**
     * Get all organisations
     */
    public static function findAll() {
        $db = getDbConnection();
        $stmt = $db->query("SELECT * FROM organisations ORDER BY name");
        return $stmt->fetchAll();
    }
    
    /**
     * Create new organisation
     */
    public static function create($name, $domain, $seatsAllocated) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            INSERT INTO organisations (name, domain, seats_allocated, seats_used, person_singular, person_plural)
            VALUES (?, ?, ?, 0, 'person', 'people')
        ");
        $stmt->execute([$name, $domain, $seatsAllocated]);
        return $db->lastInsertId();
    }
    
    /**
     * Update organisation
     */
    public static function update($id, $name, $domain, $seatsAllocated) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            UPDATE organisations 
            SET name = ?, domain = ?, seats_allocated = ?
            WHERE id = ?
        ");
        return $stmt->execute([$name, $domain, $seatsAllocated, $id]);
    }
    
    /**
     * Update seats allocated
     */
    public static function updateSeats($id, $seatsAllocated) {
        $db = getDbConnection();
        $stmt = $db->prepare("UPDATE organisations SET seats_allocated = ? WHERE id = ?");
        return $stmt->execute([$seatsAllocated, $id]);
    }
    
    /**
     * Update terminology preferences
     */
    public static function updateTerminology($id, $personSingular, $personPlural) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            UPDATE organisations 
            SET person_singular = ?, person_plural = ?
            WHERE id = ?
        ");
        return $stmt->execute([$personSingular, $personPlural, $id]);
    }
    
    /**
     * Update organisation profile (for tender applications)
     */
    public static function updateProfile($id, $data) {
        $db = getDbConnection();
        $fields = [];
        $values = [];
        
        $profileFields = [
            'company_registration_number',
            'care_inspectorate_registration',
            'charity_number',
            'vat_number',
            'registered_address',
            'trading_address',
            'phone',
            'website',
            'care_inspectorate_rating',
            'last_inspection_date',
            'main_contact_name',
            'main_contact_email',
            'main_contact_phone',
            'geographic_coverage',
            'service_types',
            'languages_spoken',
            'specialist_expertise'
        ];
        
        foreach ($profileFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field] ?: null;
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $id;
        $sql = "UPDATE organisations SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * Recalculate seats used based on active and verified users
     */
    public static function recalculateSeatsUsed($id) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            UPDATE organisations 
            SET seats_used = (
                SELECT COUNT(*) 
                FROM users 
                WHERE organisation_id = ? 
                AND email_verified = TRUE 
                AND is_active = TRUE
            )
            WHERE id = ?
        ");
        return $stmt->execute([$id, $id]);
    }
    
    /**
     * Delete organisation
     */
    public static function delete($id) {
        $db = getDbConnection();
        $stmt = $db->prepare("DELETE FROM organisations WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
