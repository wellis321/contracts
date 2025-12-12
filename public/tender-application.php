<?php
/**
 * Tender Application Form
 * Create and manage tender applications with pre-filled data
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
RBAC::requireOrganisationAdmin();

$organisationId = Auth::getOrganisationId();
$organisation = Organisation::findById($organisationId);
$error = '';
$success = '';
$tenderApplication = null;

// Get tender application ID if editing
$applicationId = $_GET['id'] ?? null;
if ($applicationId) {
    $tenderApplication = TenderApplication::findById($applicationId);
    if (!$tenderApplication || $tenderApplication['organisation_id'] != $organisationId) {
        header('Location: ' . url('tender-applications.php?error=not_found'));
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'save' || $action === 'submit') {
            try {
                $data = [
                    'organisation_id' => $organisationId,
                    'local_authority_id' => intval($_POST['local_authority_id'] ?? 0),
                    'procurement_route' => trim($_POST['procurement_route'] ?? ''),
                    'contract_type_id' => !empty($_POST['contract_type_id']) ? intval($_POST['contract_type_id']) : null,
                    'title' => trim($_POST['title'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'service_description' => trim($_POST['service_description'] ?? ''),
                    'number_of_people' => !empty($_POST['number_of_people']) ? intval($_POST['number_of_people']) : null,
                    'geographic_coverage' => trim($_POST['geographic_coverage'] ?? ''),
                    'rates_json' => json_encode($_POST['rates'] ?? []),
                    'total_contract_value' => !empty($_POST['total_contract_value']) ? floatval($_POST['total_contract_value']) : null,
                    'payment_terms' => trim($_POST['payment_terms'] ?? ''),
                    'price_review_mechanism' => trim($_POST['price_review_mechanism'] ?? ''),
                    'inflation_indexation' => trim($_POST['inflation_indexation'] ?? ''),
                    'care_inspectorate_rating' => trim($_POST['care_inspectorate_rating'] ?? ''),
                    'relevant_experience' => trim($_POST['relevant_experience'] ?? ''),
                    'staff_qualifications' => trim($_POST['staff_qualifications'] ?? ''),
                    'training_programs' => trim($_POST['training_programs'] ?? ''),
                    'fair_work_compliance' => isset($_POST['fair_work_compliance']),
                    'living_wage_commitment' => isset($_POST['living_wage_commitment']),
                    'staff_terms_conditions' => trim($_POST['staff_terms_conditions'] ?? ''),
                    'community_benefits' => trim($_POST['community_benefits'] ?? ''),
                    'environmental_commitments' => trim($_POST['environmental_commitments'] ?? ''),
                    'staffing_levels' => !empty($_POST['staffing_levels']) ? intval($_POST['staffing_levels']) : null,
                    'daytime_hours' => !empty($_POST['daytime_hours']) ? floatval($_POST['daytime_hours']) : null,
                    'sleepover_hours' => !empty($_POST['sleepover_hours']) ? floatval($_POST['sleepover_hours']) : null,
                    'languages_offered' => trim($_POST['languages_offered'] ?? ''),
                    'specialist_skills' => trim($_POST['specialist_skills'] ?? ''),
                    'previous_contracts' => trim($_POST['previous_contracts'] ?? ''),
                    'other_references' => trim($_POST['other_references'] ?? ''),
                    'client_testimonials' => trim($_POST['client_testimonials'] ?? ''),
                    'tender_reference' => trim($_POST['tender_reference'] ?? ''),
                    'submission_deadline' => !empty($_POST['submission_deadline']) ? $_POST['submission_deadline'] : null,
                    'status' => $action === 'submit' ? 'submitted' : 'draft',
                    'created_by' => Auth::getUserId()
                ];
                
                if ($applicationId) {
                    unset($data['organisation_id']); // Don't update organisation_id
                    unset($data['created_by']); // Don't update created_by
                    TenderApplication::update($applicationId, $data);
                    $success = $action === 'submit' ? 'Tender application submitted successfully.' : 'Tender application saved.';
                } else {
                    $applicationId = TenderApplication::create($data);
                    $success = $action === 'submit' ? 'Tender application created and submitted successfully.' : 'Tender application saved as draft.';
                    
                    // Link to opportunity if created from one
                    $opportunityId = $_GET['opportunity_id'] ?? $_POST['opportunity_id'] ?? null;
                    if ($opportunityId) {
                        try {
                            TenderOpportunity::markAsApplied($opportunityId, $applicationId);
                        } catch (Exception $e) {
                            // Don't fail the whole operation if opportunity update fails
                            error_log("Error linking opportunity: " . $e->getMessage());
                        }
                    }
                }
                
                // Refresh data
                $tenderApplication = TenderApplication::findById($applicationId);
            } catch (Exception $e) {
                $error = 'Error saving tender application: ' . $e->getMessage();
            }
        }
    }
}

// Get pre-filled data if creating new application
$prefilledData = null;
$opportunity = null;
if (!$applicationId) {
    // Check if creating from an opportunity
    $opportunityId = $_GET['opportunity_id'] ?? null;
    if ($opportunityId) {
        $opportunity = TenderOpportunity::findById($opportunityId);
        if ($opportunity) {
            // Pre-fill from opportunity
            $prefilledData = [
                'local_authority_id' => $opportunity['local_authority_id'],
                'contract_type_id' => $opportunity['contract_type_id'],
                'title' => $opportunity['title'],
                'description' => $opportunity['description'],
                'tender_reference' => $opportunity['tender_reference'],
                'submission_deadline' => $opportunity['submission_deadline'],
                'number_of_people' => $opportunity['number_of_people'],
                'geographic_coverage' => $opportunity['geographic_coverage'],
                'total_contract_value' => $opportunity['estimated_value']
            ];
        }
    } else {
        // Use existing pre-fill logic
        $localAuthorityId = $_GET['local_authority_id'] ?? null;
        $prefilledData = TenderApplication::getPrefilledData($organisationId, $localAuthorityId);
    }
}

// Get reference data
$db = getDbConnection();
$localAuthorities = $db->query("SELECT * FROM local_authorities ORDER BY name")->fetchAll();
$contractTypes = ContractType::findByOrganisation($organisationId);
$procurementRoutes = ProcurementRoute::findAll();

$pageTitle = $applicationId ? 'Edit Tender Application' : 'New Tender Application';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <h2><?php echo $applicationId ? 'Edit Tender Application' : 'New Tender Application'; ?></h2>
            <?php if ($applicationId): ?>
                <a href="<?php echo url('tender-applications.php'); ?>" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Back to Applications
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($opportunity): ?>
        <div class="alert" style="background: #d1fae5; border-color: #10b981; color: #065f46; margin-bottom: 1.5rem;">
            <strong><i class="fa-solid fa-lightbulb"></i> Creating Application from Opportunity:</strong> 
            This form has been pre-filled with information from the tender opportunity 
            "<strong><?php echo htmlspecialchars($opportunity['title']); ?></strong>" 
            for <?php echo htmlspecialchars($opportunity['local_authority_name']); ?>.
            <?php if ($opportunity['source_url']): ?>
                <a href="<?php echo htmlspecialchars($opportunity['source_url']); ?>" target="_blank" 
                   style="color: #065f46; text-decoration: underline; margin-left: 0.5rem;">
                    View Original Notice <i class="fas fa-external-link-alt" style="font-size: 0.75rem;"></i>
                </a>
            <?php endif; ?>
        </div>
        <input type="hidden" name="opportunity_id" value="<?php echo $opportunity['id']; ?>">
    <?php elseif ($prefilledData): ?>
        <div class="alert" style="background: #e0f2fe; border-color: #0ea5e9; color: #0c4a6e; margin-bottom: 1.5rem;">
            <strong><i class="fa-solid fa-info-circle"></i> Pre-filled Information:</strong> 
            This form has been pre-filled with information from your organisation profile and existing contracts. 
            Please review and update as needed.
        </div>
    <?php endif; ?>
    
    <form method="POST" action="" id="tender-form">
        <?php echo CSRF::tokenField(); ?>
        <input type="hidden" name="action" value="save" id="form-action">
        
        <!-- Section 1: Basic Information -->
        <section style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 2px solid var(--border-color);">
            <h3 style="margin-bottom: 1.5rem;">1. Basic Information</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div class="form-group">
                    <label for="title">Tender Title *</label>
                    <input type="text" id="title" name="title" class="form-control" required
                           value="<?php echo htmlspecialchars($tenderApplication['title'] ?? $prefilledData['title'] ?? ''); ?>"
                           placeholder="e.g., Supported Living Services - Edinburgh">
                </div>
                
                <div class="form-group">
                    <label for="local_authority_id">Local Authority *</label>
                    <select id="local_authority_id" name="local_authority_id" class="form-control" required>
                        <option value="">Select...</option>
                        <?php foreach ($localAuthorities as $la): ?>
                            <option value="<?php echo $la['id']; ?>"
                                    <?php echo ($tenderApplication['local_authority_id'] ?? $prefilledData['local_authority_id'] ?? '') == $la['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($la['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="procurement_route">Procurement Route</label>
                    <select id="procurement_route" name="procurement_route" class="form-control">
                        <option value="">Select...</option>
                        <?php foreach ($procurementRoutes as $route): ?>
                            <option value="<?php echo htmlspecialchars($route['name']); ?>"
                                    <?php echo ($tenderApplication['procurement_route'] ?? '') === $route['name'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($route['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="contract_type_id">Contract Type</label>
                    <select id="contract_type_id" name="contract_type_id" class="form-control">
                        <option value="">Select...</option>
                        <?php foreach ($contractTypes as $type): ?>
                            <option value="<?php echo $type['id']; ?>"
                                    <?php echo ($tenderApplication['contract_type_id'] ?? $prefilledData['contract_type_id'] ?? '') == $type['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="tender_reference">Tender Reference</label>
                    <input type="text" id="tender_reference" name="tender_reference" class="form-control"
                           value="<?php echo htmlspecialchars($tenderApplication['tender_reference'] ?? $prefilledData['tender_reference'] ?? ''); ?>"
                           placeholder="Reference from LA tender portal">
                </div>
                
                <div class="form-group">
                    <label for="submission_deadline">Submission Deadline</label>
                    <input type="date" id="submission_deadline" name="submission_deadline" class="form-control"
                           value="<?php echo htmlspecialchars($tenderApplication['submission_deadline'] ?? $prefilledData['submission_deadline'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 1rem;">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3"
                          placeholder="Brief description of the tender"><?php echo htmlspecialchars($tenderApplication['description'] ?? $prefilledData['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="service_description">Service Description</label>
                <textarea id="service_description" name="service_description" class="form-control" rows="5"
                          placeholder="Detailed description of the services to be provided"><?php echo htmlspecialchars($tenderApplication['service_description'] ?? ''); ?></textarea>
            </div>
        </section>
        
        <!-- Section 2: Organisation Details (Pre-filled) -->
        <section style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 2px solid var(--border-color);">
            <h3 style="margin-bottom: 1.5rem;">2. Organisation Details</h3>
            <p style="color: var(--text-light); margin-bottom: 1rem; font-size: 0.9rem;">
                <i class="fa-solid fa-info-circle"></i> This section is pre-filled from your organisation profile. 
                Update your <a href="<?php echo url('organisation.php'); ?>">organisation profile</a> to change these details.
            </p>
            
            <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 0.5rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div>
                        <strong>Organisation Name:</strong><br>
                        <?php echo htmlspecialchars($organisation['name'] ?? ''); ?>
                    </div>
                    <?php if ($prefilledData['company_registration_number'] ?? $organisation['company_registration_number'] ?? ''): ?>
                        <div>
                            <strong>Company Registration:</strong><br>
                            <?php echo htmlspecialchars($prefilledData['company_registration_number'] ?? $organisation['company_registration_number'] ?? ''); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($prefilledData['care_inspectorate_registration'] ?? $organisation['care_inspectorate_registration'] ?? ''): ?>
                        <div>
                            <strong>Care Inspectorate Registration:</strong><br>
                            <?php echo htmlspecialchars($prefilledData['care_inspectorate_registration'] ?? $organisation['care_inspectorate_registration'] ?? ''); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($prefilledData['care_inspectorate_rating'] ?? $organisation['care_inspectorate_rating'] ?? ''): ?>
                        <div>
                            <strong>Care Inspectorate Rating:</strong><br>
                            <?php echo htmlspecialchars($prefilledData['care_inspectorate_rating'] ?? $organisation['care_inspectorate_rating'] ?? ''); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        
        <!-- Section 3: Pricing -->
        <section style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 2px solid var(--border-color);">
            <h3 style="margin-bottom: 1.5rem;">3. Pricing</h3>
            
            <?php if ($prefilledData && !empty($prefilledData['current_rates'])): ?>
                <div class="alert" style="background: #e0f2fe; border-color: #0ea5e9; color: #0c4a6e; margin-bottom: 1rem;">
                    <strong>Current Rates:</strong> Your current rates are shown below. You can adjust these for this tender.
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <?php 
                    $rates = $tenderApplication ? json_decode($tenderApplication['rates_json'], true) : [];
                    foreach ($prefilledData['current_rates'] as $rate): 
                        $rateValue = $rates[$rate['id']] ?? $rate['rate'] ?? '';
                    ?>
                        <div class="form-group" style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                            <label style="min-width: 200px; margin: 0;">
                                <?php echo htmlspecialchars($rate['name']); ?>:
                            </label>
                            <input type="number" step="0.01" name="rates[<?php echo $rate['id']; ?>]" 
                                   class="form-control" style="max-width: 150px;"
                                   value="<?php echo htmlspecialchars($rateValue); ?>"
                                   placeholder="£ per hour">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <div class="form-group">
                    <label for="total_contract_value">Total Contract Value</label>
                    <input type="number" step="0.01" id="total_contract_value" name="total_contract_value" 
                           class="form-control"
                           value="<?php echo htmlspecialchars($tenderApplication['total_contract_value'] ?? $prefilledData['total_contract_value'] ?? ''); ?>"
                           placeholder="£">
                </div>
                
                <div class="form-group">
                    <label for="payment_terms">Payment Terms</label>
                    <input type="text" id="payment_terms" name="payment_terms" class="form-control"
                           value="<?php echo htmlspecialchars($tenderApplication['payment_terms'] ?? ''); ?>"
                           placeholder="e.g., Monthly in arrears">
                </div>
                
                <div class="form-group">
                    <label for="price_review_mechanism">Price Review Mechanism</label>
                    <input type="text" id="price_review_mechanism" name="price_review_mechanism" class="form-control"
                           value="<?php echo htmlspecialchars($tenderApplication['price_review_mechanism'] ?? ''); ?>"
                           placeholder="e.g., Annual review">
                </div>
                
                <div class="form-group">
                    <label for="inflation_indexation">Inflation Indexation</label>
                    <input type="text" id="inflation_indexation" name="inflation_indexation" class="form-control"
                           value="<?php echo htmlspecialchars($tenderApplication['inflation_indexation'] ?? ''); ?>"
                           placeholder="e.g., CPI linked">
                </div>
            </div>
        </section>
        
        <!-- Section 4: Operational Details -->
        <section style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 2px solid var(--border-color);">
            <h3 style="margin-bottom: 1.5rem;">4. Operational Details</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <div class="form-group">
                    <label for="number_of_people">Number of People</label>
                    <input type="number" id="number_of_people" name="number_of_people" class="form-control"
                           value="<?php echo htmlspecialchars($tenderApplication['number_of_people'] ?? $prefilledData['number_of_people'] ?? ''); ?>"
                           placeholder="Capacity">
                </div>
                
                <div class="form-group">
                    <label for="staffing_levels">Staffing Levels</label>
                    <input type="number" id="staffing_levels" name="staffing_levels" class="form-control"
                           value="<?php echo htmlspecialchars($tenderApplication['staffing_levels'] ?? ''); ?>"
                           placeholder="Number of staff">
                </div>
                
                <div class="form-group">
                    <label for="daytime_hours">Daytime Hours</label>
                    <input type="number" step="0.01" id="daytime_hours" name="daytime_hours" class="form-control"
                           value="<?php echo htmlspecialchars($tenderApplication['daytime_hours'] ?? ''); ?>"
                           placeholder="Hours per week">
                </div>
                
                <div class="form-group">
                    <label for="sleepover_hours">Sleepover Hours</label>
                    <input type="number" step="0.01" id="sleepover_hours" name="sleepover_hours" class="form-control"
                           value="<?php echo htmlspecialchars($tenderApplication['sleepover_hours'] ?? ''); ?>"
                           placeholder="Hours per week">
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 1rem;">
                <label for="geographic_coverage">Geographic Coverage</label>
                <textarea id="geographic_coverage" name="geographic_coverage" class="form-control" rows="2"
                          placeholder="Areas where services will be provided"><?php echo htmlspecialchars($tenderApplication['geographic_coverage'] ?? $prefilledData['geographic_coverage'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="languages_offered">Languages Offered</label>
                <textarea id="languages_offered" name="languages_offered" class="form-control" rows="2"
                          placeholder="e.g., English, Polish, Urdu, BSL"><?php echo htmlspecialchars($tenderApplication['languages_offered'] ?? $prefilledData['languages_spoken'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="specialist_skills">Specialist Skills</label>
                <textarea id="specialist_skills" name="specialist_skills" class="form-control" rows="3"
                          placeholder="e.g., Learning disabilities, Autism, Dementia care"><?php echo htmlspecialchars($tenderApplication['specialist_skills'] ?? $prefilledData['specialist_expertise'] ?? ''); ?></textarea>
            </div>
        </section>
        
        <!-- Section 5: Fair Work & Community Benefits -->
        <section style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 2px solid var(--border-color);">
            <h3 style="margin-bottom: 1.5rem;">5. Fair Work & Community Benefits</h3>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="fair_work_compliance" value="1"
                           <?php echo ($tenderApplication['fair_work_compliance'] ?? $prefilledData['fair_work_compliance'] ?? false) ? 'checked' : ''; ?>>
                    Fair Work Compliance
                </label>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="living_wage_commitment" value="1"
                           <?php echo ($tenderApplication['living_wage_commitment'] ?? false) ? 'checked' : ''; ?>>
                    Living Wage Commitment
                </label>
            </div>
            
            <div class="form-group">
                <label for="staff_terms_conditions">Staff Terms & Conditions</label>
                <textarea id="staff_terms_conditions" name="staff_terms_conditions" class="form-control" rows="3"
                          placeholder="Description of staff terms and conditions"><?php echo htmlspecialchars($tenderApplication['staff_terms_conditions'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="community_benefits">Community Benefits</label>
                <textarea id="community_benefits" name="community_benefits" class="form-control" rows="3"
                          placeholder="Community benefits offered"><?php echo htmlspecialchars($tenderApplication['community_benefits'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="environmental_commitments">Environmental Commitments</label>
                <textarea id="environmental_commitments" name="environmental_commitments" class="form-control" rows="2"
                          placeholder="Environmental commitments"><?php echo htmlspecialchars($tenderApplication['environmental_commitments'] ?? ''); ?></textarea>
            </div>
        </section>
        
        <!-- Section 6: Experience & References -->
        <section style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1.5rem;">6. Experience & References</h3>
            
            <?php if ($prefilledData && !empty($prefilledData['previous_contracts'])): ?>
                <div class="alert" style="background: #e0f2fe; border-color: #0ea5e9; color: #0c4a6e; margin-bottom: 1rem;">
                    <strong>Previous Contracts:</strong> You have <?php echo count($prefilledData['previous_contracts']); ?> previous contract(s) with this local authority.
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="relevant_experience">Relevant Experience</label>
                <textarea id="relevant_experience" name="relevant_experience" class="form-control" rows="4"
                          placeholder="Describe relevant experience"><?php echo htmlspecialchars($tenderApplication['relevant_experience'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="staff_qualifications">Staff Qualifications</label>
                <textarea id="staff_qualifications" name="staff_qualifications" class="form-control" rows="3"
                          placeholder="Staff qualifications and training"><?php echo htmlspecialchars($tenderApplication['staff_qualifications'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="training_programs">Training Programs</label>
                <textarea id="training_programs" name="training_programs" class="form-control" rows="3"
                          placeholder="Training programs offered"><?php echo htmlspecialchars($tenderApplication['training_programs'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="previous_contracts">Previous Contracts</label>
                <textarea id="previous_contracts" name="previous_contracts" class="form-control" rows="3"
                          placeholder="References to previous contracts"><?php echo htmlspecialchars($tenderApplication['previous_contracts'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="other_references">Other References</label>
                <textarea id="other_references" name="other_references" class="form-control" rows="2"
                          placeholder="Other references"><?php echo htmlspecialchars($tenderApplication['other_references'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="client_testimonials">Client Testimonials</label>
                <textarea id="client_testimonials" name="client_testimonials" class="form-control" rows="3"
                          placeholder="Client testimonials"><?php echo htmlspecialchars($tenderApplication['client_testimonials'] ?? ''); ?></textarea>
            </div>
        </section>
        
        <!-- Form Actions -->
        <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem; padding-top: 2rem; border-top: 2px solid var(--border-color);">
            <button type="submit" class="btn btn-secondary" onclick="document.getElementById('form-action').value='save';">
                <i class="fa-solid fa-save"></i> Save Draft
            </button>
            <button type="submit" class="btn btn-primary" onclick="if(confirm('Are you sure you want to submit this tender application?')) { document.getElementById('form-action').value='submit'; } else { return false; }">
                <i class="fa-solid fa-paper-plane"></i> Submit Application
            </button>
        </div>
    </form>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

