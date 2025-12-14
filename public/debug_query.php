<?php
/**
 * Debug script to identify why contracts are duplicated
 * Place this in your public folder and access it directly
 */

require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
$personId = $_GET['id'] ?? 0;

if (!$personId) {
    die("Please provide a person ID: ?id=3");
}

$db = getDbConnection();

echo "<h2>Debug: Contract Duplication for Person ID: $personId</h2>";
echo "<hr>";

// Check 1: Contracts from contract_people junction table
echo "<h3>1. Contracts from contract_people junction table:</h3>";
try {
    $stmt = $db->prepare("
        SELECT 
            cp.id as cp_id,
            c.id as contract_id,
            c.title,
            c.contract_number,
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
    $results1 = $stmt->fetchAll();
    
    if (empty($results1)) {
        echo "<p>No contracts found in contract_people table.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr>
                <th>CP ID</th>
                <th>Contract ID</th>
                <th>Title</th>
                <th>Contract Number</th>
                <th>Type</th>
                <th>LA</th>
                <th>Start</th>
                <th>End</th>
              </tr>";
        foreach ($results1 as $row) {
            echo "<tr>";
            echo "<td>{$row['cp_id']}</td>";
            echo "<td>{$row['contract_id']}</td>";
            echo "<td>{$row['title']}</td>";
            echo "<td>{$row['contract_number']}</td>";
            echo "<td>{$row['contract_type_name']}</td>";
            echo "<td>{$row['local_authority_name']}</td>";
            echo "<td>{$row['person_start_date']}</td>";
            echo "<td>{$row['person_end_date']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p><strong>Total rows: " . count($results1) . "</strong></p>";
        
        // Check for duplicate contract IDs
        $contractIds = array_column($results1, 'contract_id');
        $duplicates = array_diff_assoc($contractIds, array_unique($contractIds));
        if (!empty($duplicates)) {
            echo "<p style='color: red;'><strong>⚠️ DUPLICATE CONTRACT IDs FOUND: " . implode(', ', array_unique($duplicates)) . "</strong></p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Check 2: Check if person_id column exists and query direct links
echo "<h3>2. Contracts with direct person_id link:</h3>";
try {
    $checkStmt = $db->query("SHOW COLUMNS FROM contracts LIKE 'person_id'");
    $personIdColumnExists = $checkStmt->rowCount() > 0;
    
    if (!$personIdColumnExists) {
        echo "<p>person_id column does not exist in contracts table.</p>";
    } else {
        $stmt = $db->prepare("
            SELECT 
                c.id as contract_id,
                c.title,
                c.contract_number,
                c.person_id,
                ct.name as contract_type_name,
                la.name as local_authority_name,
                c.start_date,
                c.end_date
            FROM contracts c
            LEFT JOIN contract_types ct ON c.contract_type_id = ct.id
            LEFT JOIN local_authorities la ON c.local_authority_id = la.id
            WHERE c.person_id = ?
            ORDER BY c.start_date DESC
        ");
        $stmt->execute([$personId]);
        $results2 = $stmt->fetchAll();
        
        if (empty($results2)) {
            echo "<p>No contracts found with direct person_id link.</p>";
        } else {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr>
                    <th>Contract ID</th>
                    <th>Title</th>
                    <th>Contract Number</th>
                    <th>Person ID</th>
                    <th>Type</th>
                    <th>LA</th>
                    <th>Start</th>
                    <th>End</th>
                  </tr>";
            foreach ($results2 as $row) {
                echo "<tr>";
                echo "<td>{$row['contract_id']}</td>";
                echo "<td>{$row['title']}</td>";
                echo "<td>{$row['contract_number']}</td>";
                echo "<td>{$row['person_id']}</td>";
                echo "<td>{$row['contract_type_name']}</td>";
                echo "<td>{$row['local_authority_name']}</td>";
                echo "<td>{$row['start_date']}</td>";
                echo "<td>{$row['end_date']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "<p><strong>Total rows: " . count($results2) . "</strong></p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Check 3: What Person::getContracts() actually returns
echo "<h3>3. What Person::getContracts() returns:</h3>";
$contracts = Person::getContracts($personId);
echo "<p><strong>Total contracts returned: " . count($contracts) . "</strong></p>";

if (!empty($contracts)) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr>
            <th>Array Index</th>
            <th>Contract ID</th>
            <th>Title</th>
            <th>Contract Number</th>
            <th>Type</th>
            <th>LA</th>
          </tr>";
    foreach ($contracts as $index => $contract) {
        echo "<tr>";
        echo "<td>$index</td>";
        echo "<td>{$contract['id']}</td>";
        echo "<td>{$contract['title']}</td>";
        echo "<td>{$contract['contract_number']}</td>";
        echo "<td>{$contract['contract_type_name']}</td>";
        echo "<td>{$contract['local_authority_name']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for duplicates
    $contractIds = array_column($contracts, 'id');
    $duplicates = array_diff_assoc($contractIds, array_unique($contractIds));
    if (!empty($duplicates)) {
        echo "<p style='color: red;'><strong>⚠️ DUPLICATE CONTRACT IDs IN FINAL OUTPUT: " . implode(', ', array_unique($duplicates)) . "</strong></p>";
    }
}

echo "<hr>";
echo "<p><a href='person-view.php?id=$personId'>View Person Page</a></p>";