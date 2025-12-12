<?php
/**
 * People Management Page
 * Manage individuals and track them across contracts and local authorities
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
RBAC::requireAdmin(); // Only admins can manage people

$organisationId = Auth::getOrganisationId();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create') {
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $dateOfBirth = $_POST['date_of_birth'] ?? null;
            
            if (empty($firstName) || empty($lastName)) {
                $error = 'First name and last name are required.';
            } else {
                try {
                    $personId = Person::create($organisationId, $firstName, $lastName, $dateOfBirth ?: null);
                    
                    // Add identifiers if provided
                    $identifiers = [
                        ['type' => 'CHI', 'value' => trim($_POST['chi_number'] ?? '')],
                        ['type' => 'SWIS', 'value' => trim($_POST['swis_number'] ?? '')],
                        ['type' => 'NI', 'value' => trim($_POST['ni_number'] ?? '')],
                        ['type' => 'Organisation', 'value' => trim($_POST['org_identifier'] ?? '')]
                    ];
                    
                    $hasPrimary = false;
                    foreach ($identifiers as $idx => $identifier) {
                        if (!empty($identifier['value'])) {
                            $isPrimary = !$hasPrimary && isset($_POST['primary_identifier']) && $_POST['primary_identifier'] == $identifier['type'];
                            if ($isPrimary) $hasPrimary = true;
                            Person::addIdentifier($personId, $identifier['type'], $identifier['value'], $isPrimary);
                        }
                    }
                    
                    $success = ucfirst(getPersonTerm(true)) . ' created successfully.';
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) {
                        $error = 'An identifier with this value already exists.';
                    } else {
                        $error = 'Error creating person: ' . $e->getMessage();
                    }
                }
            }
        } elseif ($action === 'add_identifier') {
            $personId = $_POST['person_id'] ?? 0;
            $identifierType = trim($_POST['identifier_type'] ?? '');
            $identifierValue = trim($_POST['identifier_value'] ?? '');
            $isPrimary = isset($_POST['is_primary']);
            
            if (empty($identifierType) || empty($identifierValue)) {
                $error = 'Identifier type and value are required.';
            } elseif (!Person::belongsToOrganisation($personId, $organisationId)) {
                $error = 'Unauthorized access.';
            } else {
                try {
                    Person::addIdentifier($personId, $identifierType, $identifierValue, $isPrimary);
                    $success = 'Identifier added successfully.';
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) {
                        $error = 'An identifier with this value already exists.';
                    } else {
                        $error = 'Error adding identifier.';
                    }
                }
            }
        }
    }
}

// Get search term
$search = $_GET['search'] ?? null;

// Get all people
$people = Person::findByOrganisation($organisationId, $search);

// Deduplicate people
$seenIds = [];
$seenPersonKeys = [];
$uniquePeople = [];
foreach ($people as $person) {
    $id = $person['id'] ?? null;
    $firstName = $person['first_name'] ?? '';
    $lastName = $person['last_name'] ?? '';
    $dateOfBirth = $person['date_of_birth'] ?? '';
    
    // First priority: skip if we've seen this exact person ID
    if ($id && in_array($id, $seenIds)) {
        continue;
    }
    
    // Second priority: if we've seen this exact combination of name and DOB, skip
    $personKey = md5($firstName . '|' . $lastName . '|' . $dateOfBirth);
    if (in_array($personKey, $seenPersonKeys)) {
        continue;
    }
    
    // Add to unique people
    if ($id) {
        $seenIds[] = $id;
    }
    $seenPersonKeys[] = $personKey;
    $uniquePeople[] = $person;
}
$people = $uniquePeople;

// Get terminology preferences
$personSingular = getPersonTerm(true);
$personPlural = getPersonTerm(false);

$pageTitle = ucfirst($personPlural);
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2><?php echo ucfirst(htmlspecialchars($personPlural)); ?></h2>
                <p>Manage <?php echo htmlspecialchars($personPlural); ?> and track them across contracts and local authorities</p>
            </div>
            <button onclick="document.getElementById('createForm').style.display='block'" class="btn btn-primary">
                Add <?php echo ucfirst(htmlspecialchars($personSingular)); ?>
            </button>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Search Form -->
    <form method="GET" action="" style="margin-bottom: 1.5rem;">
        <div style="display: flex; gap: 1rem; align-items: end;">
            <div class="form-group" style="flex: 1;">
                <label for="search">Search by Name</label>
                <input type="text" id="search" name="search" class="form-control" 
                       value="<?php echo htmlspecialchars($search ?? ''); ?>" 
                       placeholder="Enter first or last name">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-secondary">Search</button>
                <?php if ($search): ?>
                    <a href="<?php echo url('people.php'); ?>" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </div>
    </form>
    
    <!-- Create Form -->
    <div id="createForm" style="display: none; margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem;">
        <h3>Add New <?php echo ucfirst(htmlspecialchars($personSingular)); ?></h3>
        <form method="POST" action="">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="create">
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control">
                </div>
            </div>
            
            <h4 style="margin-top: 1.5rem; margin-bottom: 1rem;">Identifiers</h4>
            <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 1rem;">
                Add at least one identifier to uniquely identify this <?php echo htmlspecialchars($personSingular); ?>. Select one as primary.
            </p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label for="chi_number">CHI Number</label>
                    <input type="text" id="chi_number" name="chi_number" class="form-control">
                    <label style="margin-top: 0.5rem; font-weight: normal;">
                        <input type="radio" name="primary_identifier" value="CHI"> Primary identifier
                    </label>
                </div>
                
                <div class="form-group">
                    <label for="swis_number">SWIS Number</label>
                    <input type="text" id="swis_number" name="swis_number" class="form-control">
                    <label style="margin-top: 0.5rem; font-weight: normal;">
                        <input type="radio" name="primary_identifier" value="SWIS"> Primary identifier
                    </label>
                </div>
                
                <div class="form-group">
                    <label for="ni_number">National Insurance Number</label>
                    <input type="text" id="ni_number" name="ni_number" class="form-control">
                    <label style="margin-top: 0.5rem; font-weight: normal;">
                        <input type="radio" name="primary_identifier" value="NI"> Primary identifier
                    </label>
                </div>
                
                <div class="form-group">
                    <label for="org_identifier">Organisation Identifier</label>
                    <input type="text" id="org_identifier" name="org_identifier" class="form-control">
                    <label style="margin-top: 0.5rem; font-weight: normal;">
                        <input type="radio" name="primary_identifier" value="Organisation"> Primary identifier
                    </label>
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">Create <?php echo ucfirst(htmlspecialchars($personSingular)); ?></button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('createForm').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
    
    <!-- People List -->
    <?php if (empty($people)): ?>
        <p>No <?php echo htmlspecialchars($personPlural); ?> found. <?php echo $search ? 'Try a different search term.' : 'Create your first ' . htmlspecialchars($personSingular) . ' above.'; ?></p>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="table" style="min-width: 800px;">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Date of Birth</th>
                        <th>Primary Identifier</th>
                        <th>Contracts</th>
                        <th>Local Authorities</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($people as $person): 
                        $identifiers = Person::getIdentifiers($person['id']);
                        $contracts = Person::getContracts($person['id']);
                        $laHistory = Person::getLocalAuthorityHistory($person['id']);
                        
                        // Get primary identifier
                        $primaryIdentifier = null;
                        foreach ($identifiers as $identifier) {
                            if ($identifier['is_primary']) {
                                $primaryIdentifier = $identifier;
                                break;
                            }
                        }
                        // If no primary, use first identifier
                        if (!$primaryIdentifier && !empty($identifiers)) {
                            $primaryIdentifier = $identifiers[0];
                        }
                    ?>
                        <tr onclick="window.location.href='<?php echo htmlspecialchars(url('person-view.php?id=' . $person['id'])); ?>'" 
                            style="cursor: pointer; transition: background-color 0.2s;">
                            <td>
                                <strong><?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_name']); ?></strong>
                            </td>
                            <td>
                                <?php echo $person['date_of_birth'] ? date(DATE_FORMAT, strtotime($person['date_of_birth'])) : '<span style="color: var(--text-light);">-</span>'; ?>
                            </td>
                            <td>
                                <?php if ($primaryIdentifier): ?>
                                    <span><strong><?php echo htmlspecialchars($primaryIdentifier['identifier_type']); ?>:</strong> <?php echo htmlspecialchars($primaryIdentifier['identifier_value']); ?></span>
                                    <?php if (count($identifiers) > 1): ?>
                                        <br><small style="color: var(--text-light);">+<?php echo count($identifiers) - 1; ?> more</small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: var(--text-light);">No identifiers</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (count($contracts) > 0): ?>
                                    <span class="badge badge-success"><?php echo count($contracts); ?> contract<?php echo count($contracts) !== 1 ? 's' : ''; ?></span>
                                <?php else: ?>
                                    <span style="color: var(--text-light);">0 contracts</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (count($laHistory) > 0): ?>
                                    <span class="badge badge-secondary"><?php echo count($laHistory); ?> LA<?php echo count($laHistory) !== 1 ? 's' : ''; ?></span>
                                <?php else: ?>
                                    <span style="color: var(--text-light);">0 LAs</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
