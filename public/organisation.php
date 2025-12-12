<?php
/**
 * Organisation Settings (Organisation Admin)
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
RBAC::requireOrganisationAdmin(); // Only organisation admins (not superadmins)

$organisationId = Auth::getOrganisationId();
$organisation = Organisation::findById($organisationId);

// Safety check - should not happen if access control is correct
if (!$organisation) {
    header('Location: ' . url('index.php?error=organisation_not_found'));
    exit;
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_terminology') {
            $personSingular = trim($_POST['person_singular'] ?? '');
            $personPlural = trim($_POST['person_plural'] ?? '');
            
            if (empty($personSingular) || empty($personPlural)) {
                $error = 'Please provide both singular and plural terms.';
            } else {
                try {
                    Organisation::updateTerminology($organisationId, $personSingular, $personPlural);
                    $success = 'Terminology preferences updated successfully.';
                    // Refresh organisation data
                    $organisation = Organisation::findById($organisationId);
                } catch (Exception $e) {
                    $error = 'Error updating terminology: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'request_seat_change') {
            $requestedSeats = intval($_POST['requested_seats'] ?? 0);
            $message = trim($_POST['message'] ?? '');
            
            if ($requestedSeats <= 0) {
                $error = 'Please enter a valid number of seats.';
            } else {
                try {
                    SeatChangeRequest::create(
                        $organisationId,
                        Auth::getUserId(),
                        $organisation['seats_allocated'],
                        $requestedSeats,
                        $message ?: null
                    );
                    $success = 'Seat change request submitted successfully. The super administrator will review your request.';
                } catch (Exception $e) {
                    $error = 'Error submitting request: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'update_profile') {
            try {
                $profileData = [
                    'company_registration_number' => trim($_POST['company_registration_number'] ?? ''),
                    'care_inspectorate_registration' => trim($_POST['care_inspectorate_registration'] ?? ''),
                    'charity_number' => trim($_POST['charity_number'] ?? ''),
                    'vat_number' => trim($_POST['vat_number'] ?? ''),
                    'registered_address' => trim($_POST['registered_address'] ?? ''),
                    'trading_address' => trim($_POST['trading_address'] ?? ''),
                    'phone' => trim($_POST['phone'] ?? ''),
                    'website' => trim($_POST['website'] ?? ''),
                    'care_inspectorate_rating' => trim($_POST['care_inspectorate_rating'] ?? ''),
                    'last_inspection_date' => !empty($_POST['last_inspection_date']) ? $_POST['last_inspection_date'] : null,
                    'main_contact_name' => trim($_POST['main_contact_name'] ?? ''),
                    'main_contact_email' => trim($_POST['main_contact_email'] ?? ''),
                    'main_contact_phone' => trim($_POST['main_contact_phone'] ?? ''),
                    'geographic_coverage' => trim($_POST['geographic_coverage'] ?? ''),
                    'service_types' => trim($_POST['service_types'] ?? ''),
                    'languages_spoken' => trim($_POST['languages_spoken'] ?? ''),
                    'specialist_expertise' => trim($_POST['specialist_expertise'] ?? '')
                ];
                
                Organisation::updateProfile($organisationId, $profileData);
                $success = 'Organisation profile updated successfully. This information will be pre-filled in tender applications.';
                // Refresh organisation data
                $organisation = Organisation::findById($organisationId);
            } catch (Exception $e) {
                $error = 'Error updating profile: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Organisation Settings';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Organisation Settings</h2>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
        <div>
            <h3>Organisation Information</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($organisation['name'] ?? ''); ?></p>
            <p><strong>Domain:</strong> <?php echo htmlspecialchars($organisation['domain'] ?? ''); ?></p>
            <p><strong>Seats Allocated:</strong> <?php echo htmlspecialchars($organisation['seats_allocated'] ?? 0); ?></p>
            <p><strong>Seats Used:</strong> <?php echo htmlspecialchars($organisation['seats_used'] ?? 0); ?></p>
            <p><strong>Available Seats:</strong> 
                <?php 
                $allocated = $organisation['seats_allocated'] ?? 0;
                $used = $organisation['seats_used'] ?? 0;
                $available = $allocated - $used;
                $color = $available > 0 ? 'var(--success-color)' : 'var(--danger-color)';
                ?>
                <span style="color: <?php echo $color; ?>;"><?php echo $available; ?></span>
            </p>
        </div>
        
        <div>
            <h3>Terminology Preferences</h3>
            <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 1rem;">
                Customise how <?php echo htmlspecialchars(getPersonTerm(false)); ?> are referred to throughout the site. 
                This allows your organisation to use terminology that aligns with your values and practices (e.g., "person we support", "service user", "client", "patient").
            </p>
            
            <form method="POST" action="">
                <?php echo CSRF::tokenField(); ?>
                <input type="hidden" name="action" value="update_terminology">
                
                <div class="form-group">
                    <label for="person_singular">Singular Term *</label>
                    <input 
                        type="text" 
                        id="person_singular" 
                        name="person_singular" 
                        class="form-control" 
                        required
                        placeholder="e.g., person we support, service user, client"
                        value="<?php echo htmlspecialchars($organisation['person_singular'] ?? 'person'); ?>"
                    >
                    <small style="color: var(--text-light);">
                        How to refer to a single individual (e.g., "a person we support", "a service user", "a client")
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="person_plural">Plural Term *</label>
                    <input 
                        type="text" 
                        id="person_plural" 
                        name="person_plural" 
                        class="form-control" 
                        required
                        placeholder="e.g., people we support, service users, clients"
                        value="<?php echo htmlspecialchars($organisation['person_plural'] ?? 'people'); ?>"
                    >
                    <small style="color: var(--text-light);">
                        How to refer to multiple individuals (e.g., "people we support", "service users", "clients")
                    </small>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update Terminology</button>
                </div>
            </form>
            
            <div style="margin-top: 1.5rem; padding: 1rem; background: var(--bg-light); border-radius: 0.375rem;">
                <p style="margin: 0; font-size: 0.9rem; color: var(--text-light);">
                    <strong>Current Preview:</strong><br>
                    Singular: "<?php echo htmlspecialchars($organisation['person_singular'] ?? 'person'); ?>"<br>
                    Plural: "<?php echo htmlspecialchars($organisation['person_plural'] ?? 'people'); ?>"
                </p>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.85rem; color: var(--text-light); font-style: italic;">
                    These terms will be used throughout the site wherever "person" or "people" appear.
                </p>
            </div>
        </div>
        
        <div>
            <h3>Request Seat Change</h3>
            <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 1rem;">
                Need more seats or want to reduce your allocated seats? Submit a request to the super administrator.
            </p>
            
            <?php
            // Get recent requests for this organisation
            $recentRequests = SeatChangeRequest::getByOrganisation($organisationId);
            ?>
            
            <?php if (!empty($recentRequests)): ?>
                <div style="margin-bottom: 1.5rem; padding: 1rem; background: var(--bg-light); border-radius: 0.375rem;">
                    <h4 style="margin-top: 0; font-size: 1rem;">Recent Requests</h4>
                    <?php foreach (array_slice($recentRequests, 0, 3) as $request): ?>
                        <div style="margin-bottom: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-color);">
                            <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap;">
                                <div>
                                    <strong><?php echo $request['current_seats']; ?> → <?php echo $request['requested_seats']; ?> seats</strong>
                                    <br>
                                    <small style="color: var(--text-light);">
                                        <?php echo date('d M Y, H:i', strtotime($request['created_at'])); ?>
                                    </small>
                                </div>
                                <div>
                                    <?php
                                    $statusColors = [
                                        'pending' => 'var(--warning-color)',
                                        'approved' => 'var(--success-color)',
                                        'rejected' => 'var(--danger-color)',
                                        'completed' => 'var(--success-color)'
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Pending',
                                        'approved' => 'Approved',
                                        'rejected' => 'Rejected',
                                        'completed' => 'Completed'
                                    ];
                                    $color = $statusColors[$request['status']] ?? 'var(--text-light)';
                                    $label = $statusLabels[$request['status']] ?? ucfirst($request['status']);
                                    ?>
                                    <span style="color: <?php echo $color; ?>; font-weight: 600;"><?php echo $label; ?></span>
                                </div>
                            </div>
                            <?php if ($request['message']): ?>
                                <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: var(--text-light);">
                                    <?php echo htmlspecialchars($request['message']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <?php echo CSRF::tokenField(); ?>
                <input type="hidden" name="action" value="request_seat_change">
                
                <div class="form-group">
                    <label for="requested_seats">Requested Number of Seats *</label>
                    <input 
                        type="number" 
                        id="requested_seats" 
                        name="requested_seats" 
                        class="form-control" 
                        required
                        min="1"
                        value="<?php echo htmlspecialchars($organisation['seats_allocated'] ?? 0); ?>"
                    >
                    <small style="color: var(--text-light);">
                        Current: <?php echo htmlspecialchars($organisation['seats_allocated'] ?? 0); ?> seats
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="message">Message (Optional)</label>
                    <textarea 
                        id="message" 
                        name="message" 
                        class="form-control" 
                        rows="4"
                        placeholder="Please explain why you need this change..."
                    ></textarea>
                    <small style="color: var(--text-light);">
                        Provide context for your seat change request
                    </small>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Organisation Profile Section -->
    <div style="margin-top: 3rem; padding-top: 2rem; border-top: 2px solid var(--border-color);">
        <h2 style="margin-bottom: 1.5rem;">Organisation Profile</h2>
        <p style="color: var(--text-light); margin-bottom: 2rem;">
            Complete your organisation profile to enable automatic pre-filling of tender applications. 
            This information will be reused across all tender submissions, saving time and ensuring consistency.
        </p>
        
        <form method="POST" action="" id="profile-form">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="update_profile">
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <!-- Legal & Registration -->
                <div class="card" style="margin: 0;">
                    <h3 style="margin-top: 0;">Legal & Registration</h3>
                    
                    <div class="form-group">
                        <label for="company_registration_number">Company Registration Number</label>
                        <input type="text" id="company_registration_number" name="company_registration_number" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($organisation['company_registration_number'] ?? ''); ?>"
                               placeholder="e.g., SC123456">
                        <small style="color: var(--text-light);">Companies House registration number</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="care_inspectorate_registration">Care Inspectorate Registration</label>
                        <input type="text" id="care_inspectorate_registration" name="care_inspectorate_registration" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($organisation['care_inspectorate_registration'] ?? ''); ?>"
                               placeholder="e.g., CS123456789">
                    </div>
                    
                    <div class="form-group">
                        <label for="charity_number">Charity Number (if applicable)</label>
                        <input type="text" id="charity_number" name="charity_number" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($organisation['charity_number'] ?? ''); ?>"
                               placeholder="e.g., SC012345">
                    </div>
                    
                    <div class="form-group">
                        <label for="vat_number">VAT Number</label>
                        <input type="text" id="vat_number" name="vat_number" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($organisation['vat_number'] ?? ''); ?>"
                               placeholder="e.g., GB123456789">
                    </div>
                </div>
                
                <!-- Addresses & Contact -->
                <div class="card" style="margin: 0;">
                    <h3 style="margin-top: 0;">Addresses & Contact</h3>
                    
                    <div class="form-group">
                        <label for="registered_address">Registered Address</label>
                        <textarea id="registered_address" name="registered_address" 
                                  class="form-control" rows="3"
                                  placeholder="Full registered address"><?php echo htmlspecialchars($organisation['registered_address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="trading_address">Trading Address (if different)</label>
                        <textarea id="trading_address" name="trading_address" 
                                  class="form-control" rows="3"
                                  placeholder="Trading address if different from registered"><?php echo htmlspecialchars($organisation['trading_address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Main Phone Number</label>
                        <input type="tel" id="phone" name="phone" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($organisation['phone'] ?? ''); ?>"
                               placeholder="e.g., 0131 234 5678">
                    </div>
                    
                    <div class="form-group">
                        <label for="website">Website</label>
                        <input type="url" id="website" name="website" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($organisation['website'] ?? ''); ?>"
                               placeholder="https://example.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="main_contact_name">Main Contact Name</label>
                        <input type="text" id="main_contact_name" name="main_contact_name" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($organisation['main_contact_name'] ?? ''); ?>"
                               placeholder="Name of main contact for tenders">
                    </div>
                    
                    <div class="form-group">
                        <label for="main_contact_email">Main Contact Email</label>
                        <input type="email" id="main_contact_email" name="main_contact_email" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($organisation['main_contact_email'] ?? ''); ?>"
                               placeholder="tenders@example.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="main_contact_phone">Main Contact Phone</label>
                        <input type="tel" id="main_contact_phone" name="main_contact_phone" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($organisation['main_contact_phone'] ?? ''); ?>"
                               placeholder="e.g., 0131 234 5678">
                    </div>
                </div>
                
                <!-- Quality & Compliance -->
                <div class="card" style="margin: 0;">
                    <h3 style="margin-top: 0;">Quality & Compliance</h3>
                    
                    <div class="form-group">
                        <label for="care_inspectorate_rating">Care Inspectorate Rating</label>
                        <select id="care_inspectorate_rating" name="care_inspectorate_rating" class="form-control">
                            <option value="">Select rating...</option>
                            <option value="Excellent" <?php echo ($organisation['care_inspectorate_rating'] ?? '') === 'Excellent' ? 'selected' : ''; ?>>Excellent</option>
                            <option value="Very Good" <?php echo ($organisation['care_inspectorate_rating'] ?? '') === 'Very Good' ? 'selected' : ''; ?>>Very Good</option>
                            <option value="Good" <?php echo ($organisation['care_inspectorate_rating'] ?? '') === 'Good' ? 'selected' : ''; ?>>Good</option>
                            <option value="Adequate" <?php echo ($organisation['care_inspectorate_rating'] ?? '') === 'Adequate' ? 'selected' : ''; ?>>Adequate</option>
                            <option value="Weak" <?php echo ($organisation['care_inspectorate_rating'] ?? '') === 'Weak' ? 'selected' : ''; ?>>Weak</option>
                            <option value="Unsatisfactory" <?php echo ($organisation['care_inspectorate_rating'] ?? '') === 'Unsatisfactory' ? 'selected' : ''; ?>>Unsatisfactory</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_inspection_date">Last Inspection Date</label>
                        <input type="date" id="last_inspection_date" name="last_inspection_date" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($organisation['last_inspection_date'] ?? ''); ?>">
                    </div>
                </div>
                
                <!-- Service Information -->
                <div class="card" style="margin: 0;">
                    <h3 style="margin-top: 0;">Service Information</h3>
                    
                    <div class="form-group">
                        <label for="geographic_coverage">Geographic Coverage</label>
                        <textarea id="geographic_coverage" name="geographic_coverage" 
                                  class="form-control" rows="3"
                                  placeholder="e.g., Edinburgh, Lothians, Fife"><?php echo htmlspecialchars($organisation['geographic_coverage'] ?? ''); ?></textarea>
                        <small style="color: var(--text-light);">Areas where you provide services</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="service_types">Service Types</label>
                        <textarea id="service_types" name="service_types" 
                                  class="form-control" rows="3"
                                  placeholder="e.g., Supported living, Care at home, Respite care"><?php echo htmlspecialchars($organisation['service_types'] ?? ''); ?></textarea>
                        <small style="color: var(--text-light);">Types of services you provide</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="languages_spoken">Languages Spoken</label>
                        <textarea id="languages_spoken" name="languages_spoken" 
                                  class="form-control" rows="2"
                                  placeholder="e.g., English, Polish, Urdu, BSL"><?php echo htmlspecialchars($organisation['languages_spoken'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="specialist_expertise">Specialist Expertise</label>
                        <textarea id="specialist_expertise" name="specialist_expertise" 
                                  class="form-control" rows="3"
                                  placeholder="e.g., Learning disabilities, Autism, Dementia care, Mental health"><?php echo htmlspecialchars($organisation['specialist_expertise'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">Save Profile</button>
            </div>
        </form>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
