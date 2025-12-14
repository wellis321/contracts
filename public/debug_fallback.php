<?php
/**
 * Enhanced Debug for Name-Matching Fallback Query
 */

require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
$personId = $_GET['id'] ?? 0;

if (!$personId) {
    die("Please provide a person ID: ?id=3");
}

$db = getDbConnection();

echo "<h2>Enhanced Debug: Fallback Query Analysis for Person ID: $personId</h2>";
echo "<hr>";

// Get person details
$person = Person::findById($personId);
if (!$person) {
    die("Person not found");
}

$firstName = $person['first_name'] ?? '';
$lastName = $person['last_name'] ?? '';
$fullName = trim($firstName . ' ' . $lastName);

echo "<h3>Person Details:</h3>";
echo "<p><strong>Name:</strong> $fullName</p>";
echo "<p><strong>Organisation:</strong> {$person['organisation_name']}</p>";
echo "<hr>";

// Check if person_id column exists
$checkStmt = $db->query("SHOW COLUMNS FROM contracts LIKE 'person_id'");
$personIdColumnExists = $checkStmt->rowCount() > 0;

echo "<h3>Database Schema Check:</h3>";
echo "<p><strong>person_id column exists in contracts table:</strong> " . ($personIdColumnExists ? 'YES' : 'NO') . "</p>";
echo "<hr>";

// Run the exact fallback query
echo "<h3>Fallback Query Results (Name Matching):</h3>";
echo "<p>Searching for contracts where title LIKE: <code>" . htmlspecialchars($fullName . '%') . "</code></p>";

$stmt = $db->prepare("
    SELECT 
        c.id as contract_id,
        c.title,
        c.contract_number,
        c.person_id,
        c.start_date,
        c.end_date,
        c.organisation_id,
        c.contract_type_id,
        c.local_authority_id,
        ct.id as ct_id,
        ct.name as contract_type_name,
        la.id as la_id,
        la.name as local_authority_name
    FROM contracts c
    LEFT JOIN contract_types ct ON c.contract_type_id = ct.id
    LEFT JOIN local_authorities la ON c.local_authority_id = la.id
    WHERE c.title LIKE ? 
    AND c.person_id IS NULL
    ORDER BY c.start_date DESC, c.end_date DESC
");
$stmt->execute([$fullName . '%']);
$contractsByName = $stmt->fetchAll();

echo "<p><strong>Total rows returned by query: " . count($contractsByName) . "</strong></p>";

if (empty($contractsByName)) {
    echo "<p style='color: orange;'>No contracts found matching the name pattern.</p>";
} else {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr>
            <th>Row #</th>
            <th>Contract ID</th>
            <th>Title</th>
            <th>Number</th>
            <th>Person ID</th>
            <th>Type ID</th>
            <th>Type Name</th>
            <th>LA ID</th>
            <th>LA Name</th>
            <th>Org ID</th>
          </tr>";
    
    foreach ($contractsByName as $index => $row) {
        echo "<tr>";
        echo "<td>$index</td>";
        echo "<td>{$row['contract_id']}</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>{$row['contract_number']}</td>";
        echo "<td>" . ($row['person_id'] ?? 'NULL') . "</td>";
        echo "<td>{$row['contract_type_id']}</td>";
        echo "<td>{$row['contract_type_name']}</td>";
        echo "<td>{$row['local_authority_id']}</td>";
        echo "<td>{$row['local_authority_name']}</td>";
        echo "<td>{$row['organisation_id']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for duplicate contract IDs
    $contractIds = array_column($contractsByName, 'contract_id');
    echo "<p><strong>Contract IDs found:</strong> " . implode(', ', $contractIds) . "</p>";
    
    $uniqueIds = array_unique($contractIds);
    if (count($contractIds) !== count($uniqueIds)) {
        echo "<p style='color: red;'><strong><i class='fa-solid fa-exclamation-triangle' style='margin-right: 0.5rem;'></i>DUPLICATE CONTRACT IDs IN QUERY RESULT!</strong></p>";
        $duplicates = array_diff_assoc($contractIds, $uniqueIds);
        echo "<p>Duplicate IDs: " . implode(', ', array_unique($duplicates)) . "</p>";
    } else {
        echo "<p style='color: green;'><i class='fa-solid fa-check-circle' style='margin-right: 0.5rem;'></i>No duplicate contract IDs in query result.</p>";
    }
}

echo "<hr>";

// Now check contract_types and local_authorities for potential issues
echo "<h3>Related Table Checks:</h3>";

if (!empty($contractsByName)) {
    $contractId = $contractsByName[0]['contract_id'];
    
    // Check contract_types
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM contract_types WHERE id = ?");
    $stmt->execute([$contractsByName[0]['contract_type_id']]);
    $ctCount = $stmt->fetch()['count'];
    echo "<p>Contract Type ID {$contractsByName[0]['contract_type_id']}: $ctCount row(s) in contract_types table</p>";
    
    // Check local_authorities
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM local_authorities WHERE id = ?");
    $stmt->execute([$contractsByName[0]['local_authority_id']]);
    $laCount = $stmt->fetch()['count'];
    echo "<p>Local Authority ID {$contractsByName[0]['local_authority_id']}: $laCount row(s) in local_authorities table</p>";
    
    // Check if there are multiple contracts with the same title
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM contracts WHERE title LIKE ?");
    $stmt->execute([$fullName . '%']);
    $titleCount = $stmt->fetch()['count'];
    echo "<p>Total contracts with title like '{$fullName}%': $titleCount</p>";
    
    if ($titleCount > 1) {
        echo "<p style='color: orange;'><strong><i class='fa-solid fa-exclamation-triangle' style='margin-right: 0.5rem;'></i>Multiple contracts found with similar titles!</strong></p>";
        
        $stmt = $db->prepare("SELECT id, title, contract_number, person_id FROM contracts WHERE title LIKE ?");
        $stmt->execute([$fullName . '%']);
        $allSimilar = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Title</th><th>Number</th><th>Person ID</th></tr>";
        foreach ($allSimilar as $row) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
            echo "<td>{$row['contract_number']}</td>";
            echo "<td>" . ($row['person_id'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

echo "<hr>";
echo "<p><a href='person-view.php?id=$personId'>View Person Page</a></p>";