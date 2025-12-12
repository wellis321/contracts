<?php
/**
 * Tender Opportunity Model
 * Manages available tender opportunities
 */
class TenderOpportunity {
    
    /**
     * Get tender opportunity by ID
     */
    public static function findById($id) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        
        $stmt = $db->prepare("
            SELECT topp.*, 
                   la.name as local_authority_name,
                   ct.name as contract_type_name,
                   creator.first_name as creator_first_name,
                   creator.last_name as creator_last_name
            FROM tender_opportunities topp
            LEFT JOIN local_authorities la ON topp.local_authority_id = la.id
            LEFT JOIN contract_types ct ON topp.contract_type_id = ct.id
            LEFT JOIN users creator ON topp.created_by = creator.id
            WHERE topp.id = ? 
            AND (topp.organisation_id IS NULL OR topp.organisation_id = ?)
        ");
        
        $stmt->execute([$id, $organisationId]);
        return $stmt->fetch();
    }
    
    /**
     * Get all tender opportunities
     */
    public static function findAll($filters = []) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        
        $sql = "
            SELECT topp.*, 
                   la.name as local_authority_name,
                   ct.name as contract_type_name,
                   creator.first_name as creator_first_name,
                   creator.last_name as creator_last_name
            FROM tender_opportunities topp
            LEFT JOIN local_authorities la ON topp.local_authority_id = la.id
            LEFT JOIN contract_types ct ON topp.contract_type_id = ct.id
            LEFT JOIN users creator ON topp.created_by = creator.id
            WHERE (topp.organisation_id IS NULL OR topp.organisation_id = ?)
        ";
        
        $params = [$organisationId];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $sql .= " AND topp.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['local_authority_id'])) {
            $sql .= " AND topp.local_authority_id = ?";
            $params[] = $filters['local_authority_id'];
        }
        
        if (!empty($filters['contract_type_id'])) {
            $sql .= " AND topp.contract_type_id = ?";
            $params[] = $filters['contract_type_id'];
        }
        
        if (!empty($filters['source'])) {
            $sql .= " AND topp.source = ?";
            $params[] = $filters['source'];
        }
        
        if (!empty($filters['upcoming_only'])) {
            $sql .= " AND topp.submission_deadline >= CURDATE()";
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (topp.title LIKE ? OR topp.description LIKE ? OR topp.tender_reference LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY topp.submission_deadline ASC, topp.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Create new tender opportunity
     */
    public static function create($data) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        $userId = Auth::getUserId();
        
        $stmt = $db->prepare("
            INSERT INTO tender_opportunities (
                organisation_id, local_authority_id, contract_type_id,
                title, description, tender_reference, source, source_url,
                published_date, submission_deadline, clarification_deadline, award_date_expected,
                estimated_value, contract_duration_months, number_of_people, geographic_coverage,
                status, interest_level, notes, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['organisation_id'] ?? $organisationId,
            $data['local_authority_id'],
            $data['contract_type_id'] ?? null,
            $data['title'],
            $data['description'] ?? null,
            $data['tender_reference'] ?? null,
            $data['source'] ?? 'manual',
            $data['source_url'] ?? null,
            $data['published_date'] ?? null,
            $data['submission_deadline'],
            $data['clarification_deadline'] ?? null,
            $data['award_date_expected'] ?? null,
            $data['estimated_value'] ?? null,
            $data['contract_duration_months'] ?? null,
            $data['number_of_people'] ?? null,
            $data['geographic_coverage'] ?? null,
            $data['status'] ?? 'open',
            $data['interest_level'] ?? null,
            $data['notes'] ?? null,
            $userId
        ]);
        
        return $db->lastInsertId();
    }
    
    /**
     * Update tender opportunity
     */
    public static function update($id, $data) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        
        $updates = [];
        $params = [];
        
        $allowedFields = [
            'local_authority_id', 'contract_type_id', 'title', 'description',
            'tender_reference', 'source', 'source_url', 'published_date',
            'submission_deadline', 'clarification_deadline', 'award_date_expected',
            'estimated_value', 'contract_duration_months', 'number_of_people',
            'geographic_coverage', 'status', 'interest_level', 'notes',
            'application_created', 'tender_application_id'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $params[] = $id;
        $params[] = $organisationId;
        
        $sql = "UPDATE tender_opportunities SET " . implode(', ', $updates) . 
               " WHERE id = ? AND (organisation_id IS NULL OR organisation_id = ?)";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Delete tender opportunity
     */
    public static function delete($id) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        
        $stmt = $db->prepare("
            DELETE FROM tender_opportunities 
            WHERE id = ? AND (organisation_id IS NULL OR organisation_id = ?)
        ");
        return $stmt->execute([$id, $organisationId]);
    }
    
    /**
     * Mark opportunity as applied (link to tender application)
     */
    public static function markAsApplied($opportunityId, $tenderApplicationId) {
        return self::update($opportunityId, [
            'status' => 'applied',
            'application_created' => true,
            'tender_application_id' => $tenderApplicationId
        ]);
    }
}

