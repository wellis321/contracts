<?php
/**
 * Person Model
 * Handles individual person tracking across contracts and local authorities
 */

class Person {
    
    /**
     * Get person by ID
     */
    public static function findById($id) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT p.*, o.name as organisation_name
            FROM people p
            LEFT JOIN organisations o ON p.organisation_id = o.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Find person by identifier (CHI, SWIS, NI number, etc.)
     */
    public static function findByIdentifier($identifierType, $identifierValue) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT p.*, o.name as organisation_name
            FROM people p
            INNER JOIN person_identifiers pi ON p.id = pi.person_id
            LEFT JOIN organisations o ON p.organisation_id = o.id
            WHERE pi.identifier_type = ? AND pi.identifier_value = ?
        ");
        $stmt->execute([$identifierType, $identifierValue]);
        return $stmt->fetch();
    }
    
    /**
     * Get all identifiers for a person
     */
    public static function getIdentifiers($personId) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT * FROM person_identifiers 
            WHERE person_id = ? 
            ORDER BY is_primary DESC, created_at ASC
        ");
        $stmt->execute([$personId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all people for an organisation
     */
    public static function findByOrganisation($organisationId, $search = null) {
        $db = getDbConnection();
        $sql = "SELECT * FROM people WHERE organisation_id = ?";
        $params = [$organisationId];
        
        if ($search) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY last_name, first_name";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Create new person
     */
    public static function create($organisationId, $firstName, $lastName, $dateOfBirth = null) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            INSERT INTO people (organisation_id, first_name, last_name, date_of_birth)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$organisationId, $firstName, $lastName, $dateOfBirth]);
        return $db->lastInsertId();
    }
    
    /**
     * Update person
     */
    public static function update($id, $firstName, $lastName, $dateOfBirth = null) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            UPDATE people 
            SET first_name = ?, last_name = ?, date_of_birth = ?
            WHERE id = ?
        ");
        return $stmt->execute([$firstName, $lastName, $dateOfBirth, $id]);
    }
    
    /**
     * Add identifier to person
     */
    public static function addIdentifier($personId, $identifierType, $identifierValue, $isPrimary = false, $verified = false, $notes = null) {
        $db = getDbConnection();
        
        // If setting as primary, unset other primary identifiers of same type
        if ($isPrimary) {
            $stmt = $db->prepare("
                UPDATE person_identifiers 
                SET is_primary = FALSE 
                WHERE person_id = ? AND identifier_type = ?
            ");
            $stmt->execute([$personId, $identifierType]);
        }
        
        $stmt = $db->prepare("
            INSERT INTO person_identifiers (person_id, identifier_type, identifier_value, is_primary, verified, notes)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                is_primary = VALUES(is_primary),
                verified = VALUES(verified),
                notes = VALUES(notes)
        ");
        return $stmt->execute([$personId, $identifierType, $identifierValue, $isPrimary ? 1 : 0, $verified ? 1 : 0, $notes]);
    }
    
    /**
     * Get all contracts for a person (across all local authorities)
     * Checks both contract_people junction table and direct person_id on contracts
     */
    public static function getContracts($personId) {
        $db = getDbConnection();
        
        $allContracts = [];
        $seenContractIds = [];
        
        // First, try to get contracts from contract_people junction table
        try {
            $stmt = $db->prepare("
                SELECT 
                    c.*,
                    ct.name as contract_type_name,
                    la.name as local_authority_name,
                    cp.start_date as person_start_date,
                    cp.end_date as person_end_date,
                    cp.notes as contract_notes
                FROM contract_people cp
                INNER JOIN contracts c ON cp.contract_id = c.id
                LEFT JOIN contract_types ct ON c.contract_type_id = ct.id
                LEFT JOIN local_authorities la ON cp.local_authority_id = la.id
                WHERE cp.person_id = ?
                ORDER BY cp.start_date DESC, cp.end_date DESC
            ");
            $stmt->execute([$personId]);
            $contractsFromJunction = $stmt->fetchAll();
            
            foreach ($contractsFromJunction as $contract) {
                $contractId = $contract['id'] ?? null;
                if ($contractId && !in_array($contractId, $seenContractIds)) {
                    $seenContractIds[] = $contractId;
                    $allContracts[] = $contract;
                }
            }
        } catch (Exception $e) {
            // contract_people table might not exist, continue
        }
        
        // Then, try to get contracts with direct person_id link
        // Check if person_id column exists first
        $personIdColumnExists = false;
        try {
            $checkStmt = $db->query("SHOW COLUMNS FROM contracts LIKE 'person_id'");
            $personIdColumnExists = $checkStmt->rowCount() > 0;
        } catch (Exception $e) {
            // Column doesn't exist
        }
        
        if ($personIdColumnExists) {
            try {
                $stmt = $db->prepare("
                    SELECT 
                        c.*,
                        ct.name as contract_type_name,
                        la.name as local_authority_name,
                        c.start_date as person_start_date,
                        c.end_date as person_end_date,
                        NULL as contract_notes
                    FROM contracts c
                    LEFT JOIN contract_types ct ON c.contract_type_id = ct.id
                    LEFT JOIN local_authorities la ON c.local_authority_id = la.id
                    WHERE c.person_id = ?
                    ORDER BY c.start_date DESC, c.end_date DESC
                ");
                $stmt->execute([$personId]);
                $contractsFromDirect = $stmt->fetchAll();
                
                foreach ($contractsFromDirect as $contract) {
                    $contractId = $contract['id'] ?? null;
                    if ($contractId && !in_array($contractId, $seenContractIds)) {
                        $seenContractIds[] = $contractId;
                        $allContracts[] = $contract;
                    }
                }
            } catch (Exception $e) {
                // Query failed, but that's okay
            }
        }
        
        // If no contracts found and person_id column exists, try matching by name as fallback
        if (empty($allContracts) && $personIdColumnExists) {
            try {
                // Get person's name
                $person = self::findById($personId);
                if ($person) {
                    $firstName = $person['first_name'] ?? '';
                    $lastName = $person['last_name'] ?? '';
                    $fullName = trim($firstName . ' ' . $lastName);
                    
                    if (!empty($fullName)) {
                        // Try to find contracts where title starts with person's name
                        $stmt = $db->prepare("
                            SELECT 
                                c.*,
                                ct.name as contract_type_name,
                                la.name as local_authority_name,
                                c.start_date as person_start_date,
                                c.end_date as person_end_date,
                                NULL as contract_notes
                            FROM contracts c
                            LEFT JOIN contract_types ct ON c.contract_type_id = ct.id
                            LEFT JOIN local_authorities la ON c.local_authority_id = la.id
                            WHERE c.title LIKE ? 
                            AND c.person_id IS NULL
                            ORDER BY c.start_date DESC, c.end_date DESC
                        ");
                        $stmt->execute([$fullName . '%']);
                        $contractsByName = $stmt->fetchAll();
                        
                        foreach ($contractsByName as $contract) {
                            $contractId = $contract['id'] ?? null;
                            if ($contractId && !in_array($contractId, $seenContractIds)) {
                                $seenContractIds[] = $contractId;
                                $allContracts[] = $contract;
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                // Fallback failed, that's okay
            }
        }
        
        // Sort by start date
        usort($allContracts, function($a, $b) {
            $aDate = $a['person_start_date'] ? strtotime($a['person_start_date']) : 0;
            $bDate = $b['person_start_date'] ? strtotime($b['person_start_date']) : 0;
            return $bDate <=> $aDate;
        });
        
        return $allContracts;
    }
    
    /**
     * Get payment history for a person across all local authorities
     */
    public static function getPaymentHistory($personId, $startDate = null, $endDate = null) {
        $db = getDbConnection();
        
        // Check if payment_frequency column exists
        $paymentFreqExists = false;
        try {
            $checkStmt = $db->query("SHOW COLUMNS FROM contract_payments LIKE 'payment_frequency'");
            $paymentFreqExists = $checkStmt->rowCount() > 0;
        } catch (Exception $e) {
            // Column doesn't exist
        }
        
        $paymentFreqField = $paymentFreqExists ? 'cpay.payment_frequency,' : '';
        
        $sql = "
            SELECT 
                cp.*,
                c.title as contract_title,
                c.contract_number,
                la.name as local_authority_name,
                pm.name as payment_method_name,
                cpay.amount,
                cpay.payment_date,
                " . ($paymentFreqExists ? "cpay.payment_frequency," : "NULL as payment_frequency,") . "
                cpay.description
            FROM contract_people cp
            INNER JOIN contracts c ON cp.contract_id = c.id
            INNER JOIN contract_payments cpay ON c.id = cpay.contract_id
            LEFT JOIN local_authorities la ON cp.local_authority_id = la.id
            LEFT JOIN payment_methods pm ON cpay.payment_method_id = pm.id
            WHERE cp.person_id = ?
        ";
        $params = [$personId];
        
        if ($startDate) {
            $sql .= " AND cpay.payment_date >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND cpay.payment_date <= ?";
            $params[] = $endDate;
        }
        
        $sql .= " ORDER BY cpay.payment_date DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get local authority history for a person
     * Checks both contract_people junction table and direct person_id on contracts
     */
    public static function getLocalAuthorityHistory($personId) {
        $db = getDbConnection();
        
        $allLAs = [];
        $seenLaIds = [];
        
        // First, try to get from contract_people junction table
        try {
            $stmt = $db->prepare("
                SELECT DISTINCT
                    cp.local_authority_id,
                    la.name as local_authority_name,
                    MIN(cp.start_date) as first_contact_date,
                    MAX(COALESCE(cp.end_date, CURDATE())) as last_contact_date
                FROM contract_people cp
                INNER JOIN local_authorities la ON cp.local_authority_id = la.id
                WHERE cp.person_id = ?
                GROUP BY cp.local_authority_id, la.name
                ORDER BY first_contact_date ASC
            ");
            $stmt->execute([$personId]);
            $lasFromJunction = $stmt->fetchAll();
            
            foreach ($lasFromJunction as $la) {
                $laId = $la['local_authority_id'] ?? null;
                if ($laId && !in_array($laId, $seenLaIds)) {
                    $seenLaIds[] = $laId;
                    $allLAs[] = $la;
                }
            }
        } catch (Exception $e) {
            // contract_people table might not exist, continue
        }
        
        // Then, try to get from direct person_id link
        // Check if person_id column exists first
        $personIdColumnExists = false;
        try {
            $checkStmt = $db->query("SHOW COLUMNS FROM contracts LIKE 'person_id'");
            $personIdColumnExists = $checkStmt->rowCount() > 0;
        } catch (Exception $e) {
            // Column doesn't exist
        }
        
        if ($personIdColumnExists) {
            try {
                $stmt = $db->prepare("
                    SELECT DISTINCT
                        c.local_authority_id,
                        la.name as local_authority_name,
                        MIN(c.start_date) as first_contact_date,
                        MAX(COALESCE(c.end_date, CURDATE())) as last_contact_date
                    FROM contracts c
                    INNER JOIN local_authorities la ON c.local_authority_id = la.id
                    WHERE c.person_id = ?
                    GROUP BY c.local_authority_id, la.name
                    ORDER BY first_contact_date ASC
                ");
                $stmt->execute([$personId]);
                $lasFromDirect = $stmt->fetchAll();
                
                foreach ($lasFromDirect as $la) {
                    $laId = $la['local_authority_id'] ?? null;
                    if ($laId && !in_array($laId, $seenLaIds)) {
                        $seenLaIds[] = $laId;
                        $allLAs[] = $la;
                    }
                }
            } catch (Exception $e) {
                // Query failed, but that's okay
            }
        }
        
        // Sort by first contact date
        usort($allLAs, function($a, $b) {
            $aDate = $a['first_contact_date'] ? strtotime($a['first_contact_date']) : 0;
            $bDate = $b['first_contact_date'] ? strtotime($b['first_contact_date']) : 0;
            return $aDate <=> $bDate;
        });
        
        return $allLAs;
    }
    
    /**
     * Link person to contract
     */
    public static function linkToContract($personId, $contractId, $localAuthorityId, $startDate, $endDate = null, $notes = null) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            INSERT INTO contract_people (contract_id, person_id, local_authority_id, start_date, end_date, notes)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                local_authority_id = VALUES(local_authority_id),
                start_date = VALUES(start_date),
                end_date = VALUES(end_date),
                notes = VALUES(notes)
        ");
        return $stmt->execute([$contractId, $personId, $localAuthorityId, $startDate, $endDate, $notes]);
    }
    
    /**
     * Verify person belongs to organisation
     */
    public static function belongsToOrganisation($id, $organisationId) {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT id FROM people WHERE id = ? AND organisation_id = ?");
        $stmt->execute([$id, $organisationId]);
        return $stmt->fetch() !== false;
    }
}
