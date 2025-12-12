<?php
/**
 * Tender Application Model
 */
class TenderApplication {
    
    /**
     * Get tender application by ID
     */
    public static function findById($id) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT ta.*, 
                   o.name as organisation_name,
                   la.name as local_authority_name,
                   ct.name as contract_type_name
            FROM tender_applications ta
            LEFT JOIN organisations o ON ta.organisation_id = o.id
            LEFT JOIN local_authorities la ON ta.local_authority_id = la.id
            LEFT JOIN contract_types ct ON ta.contract_type_id = ct.id
            WHERE ta.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all tender applications for an organisation
     */
    public static function findByOrganisation($organisationId, $status = null) {
        $db = getDbConnection();
        $sql = "
            SELECT ta.*, 
                   la.name as local_authority_name,
                   ct.name as contract_type_name
            FROM tender_applications ta
            LEFT JOIN local_authorities la ON ta.local_authority_id = la.id
            LEFT JOIN contract_types ct ON ta.contract_type_id = ct.id
            WHERE ta.organisation_id = ?
        ";
        
        $params = [$organisationId];
        
        if ($status) {
            $sql .= " AND ta.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY ta.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Create new tender application
     */
    public static function create($data) {
        $db = getDbConnection();
        
        $fields = [
            'organisation_id', 'local_authority_id', 'procurement_route', 'contract_type_id',
            'title', 'description', 'service_description', 'number_of_people', 'geographic_coverage',
            'rates_json', 'total_contract_value', 'payment_terms', 'price_review_mechanism', 'inflation_indexation',
            'care_inspectorate_rating', 'relevant_experience', 'staff_qualifications', 'training_programs',
            'fair_work_compliance', 'living_wage_commitment', 'staff_terms_conditions', 'community_benefits', 'environmental_commitments',
            'staffing_levels', 'daytime_hours', 'sleepover_hours', 'languages_offered', 'specialist_skills',
            'previous_contracts', 'other_references', 'client_testimonials',
            'status', 'tender_reference', 'submission_deadline', 'created_by'
        ];
        
        $fieldList = [];
        $valueList = [];
        $placeholders = [];
        
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $fieldList[] = $field;
                $placeholders[] = '?';
                $valueList[] = $data[$field];
            }
        }
        
        if (empty($fieldList)) {
            throw new Exception('No data provided for tender application');
        }
        
        $sql = "INSERT INTO tender_applications (" . implode(', ', $fieldList) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($valueList);
        
        return $db->lastInsertId();
    }
    
    /**
     * Update tender application
     */
    public static function update($id, $data) {
        $db = getDbConnection();
        
        $fields = [
            'local_authority_id', 'procurement_route', 'contract_type_id',
            'title', 'description', 'service_description', 'number_of_people', 'geographic_coverage',
            'rates_json', 'total_contract_value', 'payment_terms', 'price_review_mechanism', 'inflation_indexation',
            'care_inspectorate_rating', 'relevant_experience', 'staff_qualifications', 'training_programs',
            'fair_work_compliance', 'living_wage_commitment', 'staff_terms_conditions', 'community_benefits', 'environmental_commitments',
            'staffing_levels', 'daytime_hours', 'sleepover_hours', 'languages_offered', 'specialist_skills',
            'previous_contracts', 'other_references', 'client_testimonials',
            'status', 'tender_reference', 'submission_deadline'
        ];
        
        $updates = [];
        $values = [];
        
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        // Handle submission
        if (isset($data['status']) && $data['status'] === 'submitted' && !isset($data['submitted_at'])) {
            $updates[] = "submitted_at = NOW()";
        }
        
        $values[] = $id;
        $sql = "UPDATE tender_applications SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * Delete tender application
     */
    public static function delete($id) {
        $db = getDbConnection();
        $stmt = $db->prepare("DELETE FROM tender_applications WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Get pre-filled data for a new tender application
     * Pulls from organisation profile and existing contracts
     */
    public static function getPrefilledData($organisationId, $localAuthorityId = null) {
        $db = getDbConnection();
        $data = [];
        
        // Get organisation profile
        $org = Organisation::findById($organisationId);
        if ($org) {
            $data['organisation_name'] = $org['name'];
            $data['company_registration_number'] = $org['company_registration_number'] ?? null;
            $data['care_inspectorate_registration'] = $org['care_inspectorate_registration'] ?? null;
            $data['care_inspectorate_rating'] = $org['care_inspectorate_rating'] ?? null;
            $data['last_inspection_date'] = $org['last_inspection_date'] ?? null;
            $data['registered_address'] = $org['registered_address'] ?? null;
            $data['trading_address'] = $org['trading_address'] ?? null;
            $data['phone'] = $org['phone'] ?? null;
            $data['website'] = $org['website'] ?? null;
            $data['main_contact_name'] = $org['main_contact_name'] ?? null;
            $data['main_contact_email'] = $org['main_contact_email'] ?? null;
            $data['main_contact_phone'] = $org['main_contact_phone'] ?? null;
            $data['geographic_coverage'] = $org['geographic_coverage'] ?? null;
            $data['service_types'] = $org['service_types'] ?? null;
            $data['languages_spoken'] = $org['languages_spoken'] ?? null;
            $data['specialist_expertise'] = $org['specialist_expertise'] ?? null;
        }
        
        // Get existing contracts with this local authority (if specified)
        if ($localAuthorityId) {
            $stmt = $db->prepare("
                SELECT c.*, ct.name as contract_type_name
                FROM contracts c
                LEFT JOIN contract_types ct ON c.contract_type_id = ct.id
                WHERE c.organisation_id = ? AND c.local_authority_id = ?
                ORDER BY c.start_date DESC
                LIMIT 5
            ");
            $stmt->execute([$organisationId, $localAuthorityId]);
            $data['previous_contracts'] = $stmt->fetchAll();
        }
        
        // Get current rates from contract types
        // Note: Rates are per contract type and local authority, not per organisation
        // We'll get the contract types and let the user enter rates for this tender
        // Use findByOrganisation to ensure no duplicates
        $contractTypes = ContractType::findByOrganisation($organisationId);
        
        // If local authority is specified, try to get rates for that LA
        if ($localAuthorityId) {
            foreach ($contractTypes as &$ct) {
                $rate = Rate::getCurrentRate($ct['id'], $localAuthorityId);
                $ct['rate'] = $rate ? $rate['rate_amount'] : null;
                $ct['effective_from'] = $rate ? $rate['effective_from'] : null;
            }
        }
        
        $data['current_rates'] = $contractTypes;
        
        // Get fair work compliance from recent contracts
        $stmt = $db->prepare("
            SELECT COUNT(*) as count, 
                   SUM(CASE WHEN fair_work_compliance = 1 THEN 1 ELSE 0 END) as compliant_count
            FROM contracts
            WHERE organisation_id = ?
        ");
        $stmt->execute([$organisationId]);
        $fairWork = $stmt->fetch();
        $data['fair_work_compliance'] = $fairWork && $fairWork['compliant_count'] > 0;
        
        return $data;
    }
}

