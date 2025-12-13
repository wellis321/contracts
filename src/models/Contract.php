<?php
/**
 * Contract Model
 */
class Contract {
    
    /**
     * Get contract by ID
     */
    public static function findById($id) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT c.*, 
                   ct.name as contract_type_name,
                   la.name as local_authority_name,
                   o.name as organisation_name,
                   t.name as team_name,
                   tt.name as team_type_name
            FROM contracts c
            LEFT JOIN contract_types ct ON c.contract_type_id = ct.id
            LEFT JOIN local_authorities la ON c.local_authority_id = la.id
            LEFT JOIN organisations o ON c.organisation_id = o.id
            LEFT JOIN teams t ON c.team_id = t.id
            LEFT JOIN team_types tt ON t.team_type_id = tt.id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all contracts for an organisation (with team-based filtering)
     */
    public static function findByOrganisation($organisationId, $status = null, $teamIds = null) {
        $db = getDbConnection();
        $sql = "
            SELECT c.*, 
                   ct.name as contract_type_name,
                   la.name as local_authority_name,
                   t.name as team_name,
                   tt.name as team_type_name
            FROM contracts c
            LEFT JOIN contract_types ct ON c.contract_type_id = ct.id
            LEFT JOIN local_authorities la ON c.local_authority_id = la.id
            LEFT JOIN teams t ON c.team_id = t.id
            LEFT JOIN team_types tt ON t.team_type_id = tt.id
            WHERE c.organisation_id = ?
        ";
        $params = [$organisationId];
        
        // Filter by team access if teamIds provided
        // null means user can access all teams (finance/senior manager)
        // empty array means no access
        // array with IDs means filter to those teams
        if ($teamIds !== null && is_array($teamIds) && !empty($teamIds)) {
            $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
            $sql .= " AND (c.team_id IS NULL OR c.team_id IN ($placeholders))";
            $params = array_merge($params, $teamIds);
        } elseif ($teamIds === []) {
            // Empty array means no team access - return empty
            return [];
        }
        
        if ($status !== null) {
            $sql .= " AND c.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Generate a contract number automatically
     * Format: PREFIX-YYYY-NNN (e.g., HCS-2024-001, SAMH-2024-001)
     */
    public static function generateContractNumber($organisationId, $startDate) {
        $db = getDbConnection();
        
        // Get organisation prefix
        $stmt = $db->prepare("SELECT contract_number_prefix, name FROM organisations WHERE id = ?");
        $stmt->execute([$organisationId]);
        $org = $stmt->fetch();
        
        // Use prefix if set, otherwise derive from organisation name
        $prefix = $org['contract_number_prefix'] ?? null;
        if (empty($prefix)) {
            // Derive prefix from organisation name (take uppercase letters or first 4-5 chars)
            $name = $org['name'] ?? '';
            // Try to extract acronym (uppercase letters)
            preg_match_all('/\b[A-Z]/', $name, $matches);
            if (!empty($matches[0])) {
                $prefix = implode('', $matches[0]);
            } else {
                // Fallback: use first 4 uppercase characters of name
                $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 4));
            }
            // Ensure prefix is not empty
            if (empty($prefix)) {
                $prefix = 'ORG';
            }
        }
        
        // Get year from start date
        $year = date('Y', strtotime($startDate));
        
        // Find the highest sequence number for this prefix and year
        $stmt = $db->prepare("
            SELECT contract_number 
            FROM contracts 
            WHERE organisation_id = ? 
            AND contract_number LIKE ?
            AND contract_number IS NOT NULL
            AND contract_number != ''
            ORDER BY contract_number DESC
            LIMIT 1
        ");
        $pattern = $prefix . '-' . $year . '-%';
        $stmt->execute([$organisationId, $pattern]);
        $lastContract = $stmt->fetch();
        
        $sequence = 1;
        if ($lastContract && !empty($lastContract['contract_number'])) {
            // Extract sequence number from last contract number
            // Format: PREFIX-YYYY-NNN
            $parts = explode('-', $lastContract['contract_number']);
            if (count($parts) === 3 && $parts[0] === $prefix && $parts[1] === $year) {
                $lastSequence = intval($parts[2]);
                $sequence = $lastSequence + 1;
            }
        }
        
        // Format: PREFIX-YYYY-NNN (e.g., HCS-2024-001)
        return sprintf('%s-%s-%03d', strtoupper($prefix), $year, $sequence);
    }
    
    /**
     * Create new contract
     */
    public static function create($data) {
        $db = getDbConnection();
        
        // Auto-generate contract number if not provided
        if (empty($data['contract_number']) && !empty($data['start_date'])) {
            $data['contract_number'] = self::generateContractNumber(
                $data['organisation_id'],
                $data['start_date']
            );
        }
        
        // Check if person_id column exists
        $personIdColumnExists = false;
        try {
            $checkStmt = $db->query("SHOW COLUMNS FROM contracts LIKE 'person_id'");
            $personIdColumnExists = $checkStmt->rowCount() > 0;
        } catch (Exception $e) {
            // Column doesn't exist
        }
        
        if ($personIdColumnExists) {
            $stmt = $db->prepare("
                INSERT INTO contracts (
                    organisation_id, team_id, contract_type_id, local_authority_id, person_id,
                    title, description, stipulations, contract_number, procurement_route, tender_status,
                    framework_agreement_id, evaluation_criteria, quality_price_weighting,
                    start_date, end_date, contract_duration_months, extension_options,
                    price_review_mechanism, inflation_indexation, fair_work_compliance,
                    community_benefits, is_single_person, number_of_people, total_amount,
                    daytime_hours, sleepover_hours, number_of_staff, status, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['organisation_id'],
                $data['team_id'] ?? null,
                $data['contract_type_id'],
                $data['local_authority_id'],
                $data['person_id'] ?? null,
                $data['title'],
                $data['description'] ?? null,
                $data['stipulations'] ?? null,
                $data['contract_number'] ?? null,
                $data['procurement_route'] ?? null,
                $data['tender_status'] ?? null,
                $data['framework_agreement_id'] ?? null,
                $data['evaluation_criteria'] ?? null,
                $data['quality_price_weighting'] ?? null,
                $data['start_date'],
                $data['end_date'] ?? null,
                $data['contract_duration_months'] ?? null,
                $data['extension_options'] ?? null,
                $data['price_review_mechanism'] ?? null,
                $data['inflation_indexation'] ?? null,
                isset($data['fair_work_compliance']) ? ($data['fair_work_compliance'] ? 1 : 0) : 0,
                $data['community_benefits'] ?? null,
                $data['is_single_person'] ? 1 : 0,
                $data['number_of_people'] ?? 1,
                $data['total_amount'] ?? null,
                $data['daytime_hours'] ?? null,
                $data['sleepover_hours'] ?? null,
                $data['number_of_staff'] ?? null,
                $data['status'] ?? 'active',
                $data['created_by'] ?? null
            ]);
        } else {
            // person_id column doesn't exist, insert without it
            $stmt = $db->prepare("
                INSERT INTO contracts (
                    organisation_id, team_id, contract_type_id, local_authority_id,
                    title, description, stipulations, contract_number, procurement_route, tender_status,
                    framework_agreement_id, evaluation_criteria, quality_price_weighting,
                    start_date, end_date, contract_duration_months, extension_options,
                    price_review_mechanism, inflation_indexation, fair_work_compliance,
                    community_benefits, is_single_person, number_of_people, total_amount,
                    daytime_hours, sleepover_hours, number_of_staff, status, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['organisation_id'],
                $data['team_id'] ?? null,
                $data['contract_type_id'],
                $data['local_authority_id'],
                $data['title'],
                $data['description'] ?? null,
                $data['stipulations'] ?? null,
                $data['contract_number'] ?? null,
                $data['procurement_route'] ?? null,
                $data['tender_status'] ?? null,
                $data['framework_agreement_id'] ?? null,
                $data['evaluation_criteria'] ?? null,
                $data['quality_price_weighting'] ?? null,
                $data['start_date'],
                $data['end_date'] ?? null,
                $data['contract_duration_months'] ?? null,
                $data['extension_options'] ?? null,
                $data['price_review_mechanism'] ?? null,
                $data['inflation_indexation'] ?? null,
                isset($data['fair_work_compliance']) ? ($data['fair_work_compliance'] ? 1 : 0) : 0,
                $data['community_benefits'] ?? null,
                $data['is_single_person'] ? 1 : 0,
                $data['number_of_people'] ?? 1,
                $data['total_amount'] ?? null,
                $data['daytime_hours'] ?? null,
                $data['sleepover_hours'] ?? null,
                $data['number_of_staff'] ?? null,
                $data['status'] ?? 'active',
                $data['created_by'] ?? null
            ]);
        }
        
        $contractId = $db->lastInsertId();
        
        // Log the creation
        if ($contractId) {
            AuditService::logCreate('contract', $contractId, $data);
        }
        
        return $contractId;
    }
    
    /**
     * Update contract
     */
    public static function update($id, $data) {
        // Auto-generate contract number if not provided and we have start_date
        if (empty($data['contract_number']) && !empty($data['start_date'])) {
            // Get organisation_id from existing contract
            $existing = self::findById($id);
            if ($existing && !empty($existing['organisation_id'])) {
                $data['contract_number'] = self::generateContractNumber(
                    $existing['organisation_id'],
                    $data['start_date']
                );
            }
        }
        $db = getDbConnection();
        
        // Get old data for audit logging
        $oldData = self::findById($id);
        
        // Check if person_id column exists
        $personIdColumnExists = false;
        try {
            $checkStmt = $db->query("SHOW COLUMNS FROM contracts LIKE 'person_id'");
            $personIdColumnExists = $checkStmt->rowCount() > 0;
        } catch (Exception $e) {
            // Column doesn't exist
        }
        
        if ($personIdColumnExists) {
            $stmt = $db->prepare("
                UPDATE contracts SET
                    team_id = ?,
                    contract_type_id = ?,
                    local_authority_id = ?,
                    person_id = ?,
                    title = ?,
                    description = ?,
                    stipulations = ?,
                    contract_number = ?,
                    procurement_route = ?,
                    tender_status = ?,
                    framework_agreement_id = ?,
                    evaluation_criteria = ?,
                    quality_price_weighting = ?,
                    start_date = ?,
                    end_date = ?,
                    contract_duration_months = ?,
                    extension_options = ?,
                    price_review_mechanism = ?,
                    inflation_indexation = ?,
                    fair_work_compliance = ?,
                    community_benefits = ?,
                    is_single_person = ?,
                    number_of_people = ?,
                    total_amount = ?,
                    daytime_hours = ?,
                    sleepover_hours = ?,
                    number_of_staff = ?,
                    status = ?
                WHERE id = ?
            ");
            return $stmt->execute([
                $data['team_id'] ?? null,
                $data['contract_type_id'],
                $data['local_authority_id'],
                $data['person_id'] ?? null,
                $data['title'],
                $data['description'] ?? null,
                $data['stipulations'] ?? null,
                $data['contract_number'] ?? null,
                $data['procurement_route'] ?? null,
                $data['tender_status'] ?? null,
                $data['framework_agreement_id'] ?? null,
                $data['evaluation_criteria'] ?? null,
                $data['quality_price_weighting'] ?? null,
                $data['start_date'],
                $data['end_date'] ?? null,
                $data['contract_duration_months'] ?? null,
                $data['extension_options'] ?? null,
                $data['price_review_mechanism'] ?? null,
                $data['inflation_indexation'] ?? null,
                isset($data['fair_work_compliance']) ? ($data['fair_work_compliance'] ? 1 : 0) : 0,
                $data['community_benefits'] ?? null,
                $data['is_single_person'] ? 1 : 0,
                $data['number_of_people'] ?? 1,
                $data['total_amount'] ?? null,
                $data['daytime_hours'] ?? null,
                $data['sleepover_hours'] ?? null,
                $data['number_of_staff'] ?? null,
                $data['status'] ?? 'active',
                $id
            ]);
        } else {
            // person_id column doesn't exist, update without it
            $stmt = $db->prepare("
                UPDATE contracts SET
                    team_id = ?,
                    contract_type_id = ?,
                    local_authority_id = ?,
                    title = ?,
                    description = ?,
                    stipulations = ?,
                    contract_number = ?,
                    procurement_route = ?,
                    tender_status = ?,
                    framework_agreement_id = ?,
                    evaluation_criteria = ?,
                    quality_price_weighting = ?,
                    start_date = ?,
                    end_date = ?,
                    contract_duration_months = ?,
                    extension_options = ?,
                    price_review_mechanism = ?,
                    inflation_indexation = ?,
                    fair_work_compliance = ?,
                    community_benefits = ?,
                    is_single_person = ?,
                    number_of_people = ?,
                    total_amount = ?,
                    daytime_hours = ?,
                    sleepover_hours = ?,
                    number_of_staff = ?,
                    status = ?
                WHERE id = ?
            ");
            return $stmt->execute([
                $data['team_id'] ?? null,
                $data['contract_type_id'],
                $data['local_authority_id'],
                $data['title'],
                $data['description'] ?? null,
                $data['stipulations'] ?? null,
                $data['contract_number'] ?? null,
                $data['procurement_route'] ?? null,
                $data['tender_status'] ?? null,
                $data['framework_agreement_id'] ?? null,
                $data['evaluation_criteria'] ?? null,
                $data['quality_price_weighting'] ?? null,
                $data['start_date'],
                $data['end_date'] ?? null,
                $data['contract_duration_months'] ?? null,
                $data['extension_options'] ?? null,
                $data['price_review_mechanism'] ?? null,
                $data['inflation_indexation'] ?? null,
                isset($data['fair_work_compliance']) ? ($data['fair_work_compliance'] ? 1 : 0) : 0,
                $data['community_benefits'] ?? null,
                $data['is_single_person'] ? 1 : 0,
                $data['number_of_people'] ?? 1,
                $data['total_amount'] ?? null,
                $data['daytime_hours'] ?? null,
                $data['sleepover_hours'] ?? null,
                $data['number_of_staff'] ?? null,
                $data['status'] ?? 'active',
                $id
            ]);
        }
        
        // Log the update
        if ($oldData) {
            AuditService::logUpdate('contract', $id, $oldData, $data);
        }
        
        return true;
    }
    
    /**
     * Get contracts expiring soon (for workflow alerts)
     */
    public static function getExpiringSoon($organisationId, $monthsAhead = 6, $teamIds = null) {
        $db = getDbConnection();
        $sql = "
            SELECT c.*, 
                   ct.name as contract_type_name,
                   la.name as local_authority_name,
                   t.name as team_name,
                   tt.name as team_type_name
            FROM contracts c
            LEFT JOIN contract_types ct ON c.contract_type_id = ct.id
            LEFT JOIN local_authorities la ON c.local_authority_id = la.id
            LEFT JOIN teams t ON c.team_id = t.id
            LEFT JOIN team_types tt ON t.team_type_id = tt.id
            WHERE c.organisation_id = ?
            AND c.status = 'active'
            AND c.end_date IS NOT NULL
            AND c.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? MONTH)
        ";
        $params = [$organisationId, $monthsAhead];
        
        // Filter by team access
        if ($teamIds !== null && is_array($teamIds) && !empty($teamIds)) {
            $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
            $sql .= " AND (c.team_id IS NULL OR c.team_id IN ($placeholders))";
            $params = array_merge($params, $teamIds);
        } elseif ($teamIds === []) {
            return [];
        }
        
        $sql .= " ORDER BY c.end_date ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get contracts by tender status
     */
    public static function findByTenderStatus($organisationId, $tenderStatus) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT c.*, 
                   ct.name as contract_type_name,
                   la.name as local_authority_name
            FROM contracts c
            LEFT JOIN contract_types ct ON c.contract_type_id = ct.id
            LEFT JOIN local_authorities la ON c.local_authority_id = la.id
            WHERE c.organisation_id = ? AND c.tender_status = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$organisationId, $tenderStatus]);
        return $stmt->fetchAll();
    }
    
    /**
     * Delete contract
     */
    public static function delete($id) {
        $db = getDbConnection();
        
        // Get data before deletion for audit logging
        $oldData = self::findById($id);
        
        $stmt = $db->prepare("DELETE FROM contracts WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        // Log the deletion
        if ($result && $oldData) {
            AuditService::logDelete('contract', $id, $oldData);
        }
        
        return $result;
    }
    
    /**
     * Verify contract belongs to organisation
     */
    public static function belongsToOrganisation($id, $organisationId) {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT id FROM contracts WHERE id = ? AND organisation_id = ?");
        $stmt->execute([$id, $organisationId]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Get effective status of a contract based on dates
     * This overrides the stored status if the contract has expired
     */
    public static function getEffectiveStatus($contract) {
        $storedStatus = $contract['status'] ?? 'active';
        $endDate = $contract['end_date'] ?? null;
        $startDate = $contract['start_date'] ?? null;
        
        // If no end date, use stored status
        if (!$endDate) {
            return $storedStatus;
        }
        
        $today = new DateTime();
        $end = new DateTime($endDate);
        
        // If end date has passed, contract should be inactive/completed
        if ($end < $today) {
            // If stored status is 'active', return 'inactive' (or 'completed' if preferred)
            // Otherwise, respect the stored status (might be 'completed' already)
            if ($storedStatus === 'active') {
                return 'inactive';
            }
        }
        
        // If start date is in the future, contract hasn't started yet
        if ($startDate) {
            $start = new DateTime($startDate);
            if ($start > $today) {
                return 'pending'; // Or 'draft' or keep as 'active' depending on preference
            }
        }
        
        return $storedStatus;
    }
    
    /**
     * Automatically update expired contracts to inactive status
     * Call this periodically (e.g., via cron job) or on page load
     */
    public static function updateExpiredContracts($organisationId = null) {
        $db = getDbConnection();
        
        $sql = "
            UPDATE contracts 
            SET status = 'inactive', updated_at = NOW()
            WHERE status = 'active'
            AND end_date IS NOT NULL
            AND end_date < CURDATE()
        ";
        
        $params = [];
        if ($organisationId) {
            $sql .= " AND organisation_id = ?";
            $params[] = $organisationId;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
