<?php
/**
 * Data Export API
 * Provides structured data exports in multiple formats for portability and AI integration
 * 
 * Formats:
 * - JSON: Standard JSON format
 * - JSON-LD: JSON-LD with schema.org vocabulary for semantic web/AI
 * - CSV: Comma-separated values
 * 
 * Endpoints:
 * - /api/export.php?format=json&type=contracts
 * - /api/export.php?format=jsonld&type=contracts
 * - /api/export.php?format=csv&type=contracts
 */
require_once dirname(__DIR__, 2) . '/config/config.php';

header('Content-Type: application/json; charset=utf-8');

// Require authentication
Auth::requireLogin();
$organisationId = Auth::getOrganisationId();
$userId = Auth::getUserId();

// Get parameters
$format = strtolower($_GET['format'] ?? 'json');
$type = strtolower($_GET['type'] ?? 'all');
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

// Validate format
$allowedFormats = ['json', 'jsonld', 'csv'];
if (!in_array($format, $allowedFormats)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid format. Use: json, jsonld, or csv']);
    exit;
}

$db = getDbConnection();
$accessibleTeamIds = RBAC::getAccessibleTeamIds();

// Build export data based on type
$exportData = [
    'metadata' => [
        'export_date' => date('c'), // ISO 8601 format
        'export_format' => $format,
        'organisation_id' => $organisationId,
        'organisation_name' => null,
        'user_id' => $userId,
        'data_types' => []
    ],
    'data' => []
];

// Get organisation name
$org = Organisation::findById($organisationId);
if ($org) {
    $exportData['metadata']['organisation_name'] = $org['name'];
}

// Export contracts
if ($type === 'all' || $type === 'contracts') {
    $contracts = Contract::findByOrganisation($organisationId, null, $accessibleTeamIds);
    
    // Deduplicate
    $seenIds = [];
    $uniqueContracts = [];
    foreach ($contracts as $contract) {
        $id = $contract['id'] ?? null;
        if ($id && !in_array($id, $seenIds)) {
            $seenIds[] = $id;
            $uniqueContracts[] = $contract;
        }
    }
    
    // Filter by date range if provided
    if ($startDate && $endDate) {
        $uniqueContracts = array_filter($uniqueContracts, function($c) use ($startDate, $endDate) {
            $contractStart = strtotime($c['start_date'] ?? '1970-01-01');
            $contractEnd = $c['end_date'] ? strtotime($c['end_date']) : PHP_INT_MAX;
            $rangeStart = strtotime($startDate);
            $rangeEnd = strtotime($endDate);
            return ($contractStart <= $rangeEnd && $contractEnd >= $rangeStart);
        });
    }
    
    // Format contracts with effective status
    $formattedContracts = array_map(function($c) {
        $effectiveStatus = Contract::getEffectiveStatus($c);
        return [
            'id' => (int)$c['id'],
            'contract_number' => $c['contract_number'] ?? null,
            'title' => $c['title'] ?? null,
            'contract_type' => $c['contract_type_name'] ?? null,
            'local_authority' => $c['local_authority_name'] ?? null,
            'start_date' => $c['start_date'] ?? null,
            'end_date' => $c['end_date'] ?? null,
            'status' => $c['status'] ?? null,
            'effective_status' => $effectiveStatus,
            'total_amount' => $c['total_amount'] ? (float)$c['total_amount'] : null,
            'procurement_route' => $c['procurement_route'] ?? null,
            'tender_status' => $c['tender_status'] ?? null,
            'created_at' => $c['created_at'] ?? null,
            'updated_at' => $c['updated_at'] ?? null
        ];
    }, $uniqueContracts);
    
    $exportData['data']['contracts'] = $formattedContracts;
    $exportData['metadata']['data_types'][] = 'contracts';
    $exportData['metadata']['contract_count'] = count($formattedContracts);
}

// Export people
if ($type === 'all' || $type === 'people') {
    $people = Person::findByOrganisation($organisationId);
    
    $formattedPeople = array_map(function($p) {
        return [
            'id' => (int)$p['id'],
            'first_name' => $p['first_name'] ?? null,
            'last_name' => $p['last_name'] ?? null,
            'date_of_birth' => $p['date_of_birth'] ?? null,
            'created_at' => $p['created_at'] ?? null,
            'updated_at' => $p['updated_at'] ?? null
        ];
    }, $people);
    
    $exportData['data']['people'] = $formattedPeople;
    $exportData['metadata']['data_types'][] = 'people';
    $exportData['metadata']['people_count'] = count($formattedPeople);
}

// Export payments
if ($type === 'all' || $type === 'payments') {
    $payments = [];
    if (!empty($exportData['data']['contracts'])) {
        $contractIds = array_column($exportData['data']['contracts'], 'id');
        if (!empty($contractIds)) {
            $placeholders = implode(',', array_fill(0, count($contractIds), '?'));
            $sql = "SELECT cp.*, pm.name as payment_method_name, c.title as contract_title
                    FROM contract_payments cp
                    LEFT JOIN payment_methods pm ON cp.payment_method_id = pm.id
                    LEFT JOIN contracts c ON cp.contract_id = c.id
                    WHERE cp.contract_id IN ($placeholders)";
            $params = $contractIds;
            
            if ($startDate && $endDate) {
                $sql .= " AND cp.payment_date BETWEEN ? AND ?";
                $params[] = $startDate;
                $params[] = $endDate;
            }
            
            $sql .= " ORDER BY cp.payment_date DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $payments = $stmt->fetchAll();
        }
    }
    
    $formattedPayments = array_map(function($p) {
        return [
            'id' => (int)$p['id'],
            'contract_id' => (int)$p['contract_id'],
            'contract_title' => $p['contract_title'] ?? null,
            'amount' => (float)$p['amount'],
            'payment_date' => $p['payment_date'] ?? null,
            'payment_method' => $p['payment_method_name'] ?? null,
            'payment_frequency' => $p['payment_frequency'] ?? null,
            'description' => $p['description'] ?? null,
            'created_at' => $p['created_at'] ?? null
        ];
    }, $payments);
    
    $exportData['data']['payments'] = $formattedPayments;
    $exportData['metadata']['data_types'][] = 'payments';
    $exportData['metadata']['payment_count'] = count($formattedPayments);
}

// Export rates
if ($type === 'all' || $type === 'rates') {
    $stmt = $db->prepare("
        SELECT r.*, ct.name as contract_type_name, la.name as local_authority_name
        FROM rates r
        LEFT JOIN contract_types ct ON r.contract_type_id = ct.id
        LEFT JOIN local_authorities la ON r.local_authority_id = la.id
        WHERE r.organisation_id = ?
        ORDER BY r.effective_date DESC, r.created_at DESC
    ");
    $stmt->execute([$organisationId]);
    $rates = $stmt->fetchAll();
    
    $formattedRates = array_map(function($r) {
        return [
            'id' => (int)$r['id'],
            'contract_type' => $r['contract_type_name'] ?? null,
            'local_authority' => $r['local_authority_name'] ?? null,
            'rate' => (float)$r['rate'],
            'effective_date' => $r['effective_date'] ?? null,
            'created_at' => $r['created_at'] ?? null
        ];
    }, $rates);
    
    $exportData['data']['rates'] = $formattedRates;
    $exportData['metadata']['data_types'][] = 'rates';
    $exportData['metadata']['rate_count'] = count($formattedRates);
}

// Output based on format
if ($format === 'jsonld') {
    // JSON-LD with schema.org vocabulary for semantic web/AI
    header('Content-Type: application/ld+json; charset=utf-8');
    
    $jsonLd = [
        '@context' => [
            '@vocab' => 'https://schema.org/',
            'sccm' => 'https://socialcarecontracts.scot/vocab#'
        ],
        '@type' => 'Dataset',
        'name' => 'Social Care Contracts Data Export',
        'description' => 'Contract management data export for ' . ($exportData['metadata']['organisation_name'] ?? 'Organisation'),
        'datePublished' => $exportData['metadata']['export_date'],
        'publisher' => [
            '@type' => 'Organization',
            'name' => $exportData['metadata']['organisation_name'] ?? 'Unknown'
        ],
        'data' => []
    ];
    
    // Convert contracts to schema.org format
    if (!empty($exportData['data']['contracts'])) {
        $jsonLd['data']['contracts'] = array_map(function($c) {
            return [
                '@type' => 'sccm:Contract',
                '@id' => 'contract:' . $c['id'],
                'identifier' => $c['contract_number'],
                'name' => $c['title'],
                'startDate' => $c['start_date'],
                'endDate' => $c['end_date'],
                'amount' => $c['total_amount'] ? [
                    '@type' => 'MonetaryAmount',
                    'currency' => 'GBP',
                    'value' => $c['total_amount']
                ] : null,
                'status' => $c['effective_status']
            ];
        }, $exportData['data']['contracts']);
    }
    
    // Convert payments
    if (!empty($exportData['data']['payments'])) {
        $jsonLd['data']['payments'] = array_map(function($p) {
            return [
                '@type' => 'sccm:Payment',
                '@id' => 'payment:' . $p['id'],
                'amount' => [
                    '@type' => 'MonetaryAmount',
                    'currency' => 'GBP',
                    'value' => $p['amount']
                ],
                'dateReceived' => $p['payment_date'],
                'paymentMethod' => $p['payment_method']
            ];
        }, $exportData['data']['payments']);
    }
    
    echo json_encode($jsonLd, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
} elseif ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="export_' . date('Y-m-d') . '.csv"');
    
    // Output CSV
    $output = fopen('php://output', 'w');
    
    // Metadata
    fputcsv($output, ['Export Metadata']);
    fputcsv($output, ['Export Date', $exportData['metadata']['export_date']]);
    fputcsv($output, ['Organisation', $exportData['metadata']['organisation_name'] ?? 'Unknown']);
    fputcsv($output, ['Data Types', implode(', ', $exportData['metadata']['data_types'])]);
    fputcsv($output, []);
    
    // Contracts
    if (!empty($exportData['data']['contracts'])) {
        fputcsv($output, ['Contracts']);
        fputcsv($output, array_keys($exportData['data']['contracts'][0]));
        foreach ($exportData['data']['contracts'] as $contract) {
            fputcsv($output, $contract);
        }
        fputcsv($output, []);
    }
    
    // Payments
    if (!empty($exportData['data']['payments'])) {
        fputcsv($output, ['Payments']);
        fputcsv($output, array_keys($exportData['data']['payments'][0]));
        foreach ($exportData['data']['payments'] as $payment) {
            fputcsv($output, $payment);
        }
        fputcsv($output, []);
    }
    
    fclose($output);
    
} else {
    // Standard JSON
    echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}




