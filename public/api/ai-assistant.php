<?php
/**
 * AI Assistant API
 * Provides natural language query interface using Web AI APIs
 * 
 * Uses browser-based AI (Web LLM) for privacy and cost efficiency
 * Can also integrate with OpenAI, Anthropic, etc. if API keys provided
 */
require_once dirname(__DIR__, 2) . '/config/config.php';

header('Content-Type: application/json; charset=utf-8');

// Require authentication
Auth::requireLogin();
$organisationId = Auth::getOrganisationId();

// Handle CORS for API access
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    http_response_code(200);
    exit;
}

// Get request
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST') {
    $query = $input['query'] ?? '';
    $context = $input['context'] ?? 'general';
    
    if (empty($query)) {
        http_response_code(400);
        echo json_encode(['error' => 'Query is required']);
        exit;
    }
    
    // Get relevant data based on query
    $db = getDbConnection();
    $accessibleTeamIds = RBAC::getAccessibleTeamIds();
    
    // Detect query intent
    $lowerQuery = strtolower($query);
    $isGeneralQuery = (
        stripos($query, 'tell me') !== false || 
        stripos($query, 'about our') !== false ||
        stripos($query, 'summary') !== false ||
        stripos($query, 'overview') !== false ||
        stripos($query, 'describe') !== false
    );
    
    // For general queries, fetch comprehensive summaries
    $dataContext = [];
    $summary = [];
    
    // Always fetch contract summary for general queries or contract-related queries
    if ($isGeneralQuery || stripos($query, 'contract') !== false || stripos($query, 'agreement') !== false) {
        $allContracts = Contract::findByOrganisation($organisationId, null, $accessibleTeamIds);
        
        // Calculate contract statistics
        $activeCount = 0;
        $inactiveCount = 0;
        $totalValue = 0;
        $expiringSoon = [];
        $byStatus = [];
        $byLocalAuthority = [];
        
        $threeMonths = new DateTime();
        $threeMonths->modify('+3 months');
        $today = new DateTime();
        
        foreach ($allContracts as $contract) {
            // Calculate effective status
            $effectiveStatus = Contract::getEffectiveStatus($contract);
            
            if ($effectiveStatus === 'active') {
                $activeCount++;
            } else {
                $inactiveCount++;
            }
            
            $totalValue += floatval($contract['total_amount'] ?? 0);
            
            // Check if expiring soon
            if (!empty($contract['end_date'])) {
                $endDate = new DateTime($contract['end_date']);
                if ($endDate >= $today && $endDate <= $threeMonths) {
                    $expiringSoon[] = [
                        'title' => $contract['title'] ?? 'Untitled',
                        'end_date' => $contract['end_date'],
                        'local_authority' => $contract['local_authority_name'] ?? 'Unknown'
                    ];
                }
            }
            
            // Group by status
            $status = $effectiveStatus;
            if (!isset($byStatus[$status])) {
                $byStatus[$status] = 0;
            }
            $byStatus[$status]++;
            
            // Group by local authority
            $la = $contract['local_authority_name'] ?? 'Unknown';
            if (!isset($byLocalAuthority[$la])) {
                $byLocalAuthority[$la] = ['count' => 0, 'value' => 0];
            }
            $byLocalAuthority[$la]['count']++;
            $byLocalAuthority[$la]['value'] += floatval($contract['total_amount'] ?? 0);
        }
        
        $summary['contracts'] = [
            'total' => count($allContracts),
            'active' => $activeCount,
            'inactive' => $inactiveCount,
            'total_value' => $totalValue,
            'expiring_soon_count' => count($expiringSoon),
            'expiring_soon' => array_slice($expiringSoon, 0, 5),
            'by_status' => $byStatus,
            'by_local_authority' => $byLocalAuthority
        ];
        
        // Include sample contracts for detailed context
        $dataContext['contracts'] = array_slice($allContracts, 0, 20);
    }
    
    // Check if query mentions payments
    if ($isGeneralQuery || stripos($query, 'payment') !== false || stripos($query, 'income') !== false || stripos($query, 'revenue') !== false) {
        $stmt = $db->prepare("
            SELECT cp.*, c.title as contract_title, pm.name as payment_method_name
            FROM contract_payments cp
            LEFT JOIN contracts c ON cp.contract_id = c.id
            LEFT JOIN payment_methods pm ON cp.payment_method_id = pm.id
            WHERE c.organisation_id = ?
            ORDER BY cp.payment_date DESC
            LIMIT 50
        ");
        $stmt->execute([$organisationId]);
        $payments = $stmt->fetchAll();
        
        $totalPayments = 0;
        $paymentCount = count($payments);
        foreach ($payments as $payment) {
            $totalPayments += floatval($payment['amount'] ?? 0);
        }
        
        $summary['payments'] = [
            'total_count' => $paymentCount,
            'total_amount' => $totalPayments,
            'recent_payments' => array_slice($payments, 0, 10)
        ];
        
        $dataContext['payments'] = $payments;
    }
    
    // Check if query mentions people
    if ($isGeneralQuery || stripos($query, 'person') !== false || stripos($query, 'people') !== false || stripos($query, 'client') !== false) {
        $people = Person::findByOrganisation($organisationId);
        
        $summary['people'] = [
            'total' => count($people)
        ];
        
        $dataContext['people'] = array_slice($people, 0, 20);
    }
    
    // Get organisation info
    $stmt = $db->prepare("SELECT name FROM organisations WHERE id = ?");
    $stmt->execute([$organisationId]);
    $org = $stmt->fetch();
    
    // Use AI Provider service with access control
    $userId = Auth::getUserId();
    $aiProvider = new AIProvider($userId, $organisationId, $accessibleTeamIds);
    
    try {
        $aiResponse = $aiProvider->generateResponse($query, $dataContext, $summary);
        
        // If AI provider returned a response directly (external APIs)
        if (isset($aiResponse['response'])) {
            echo json_encode([
                'query' => $query,
                'is_general_query' => $isGeneralQuery,
                'organisation_name' => $org['name'] ?? 'Your Organisation',
                'summary' => $summary,
                'context' => $dataContext,
                'ai_response' => $aiResponse['response'],
                'ai_method' => $aiResponse['method'],
                'suggestions' => [
                    'Try asking: "Tell me about our contracts"',
                    'Try asking: "How many active contracts do we have?"',
                    'Try asking: "What contracts are expiring soon?"',
                    'Try asking: "Show me payments this month"',
                    'Try asking: "What is our total contract value?"'
                ]
            ]);
        } else {
            // Return data for client-side processing (pattern matching, web_llm)
            echo json_encode([
                'query' => $query,
                'is_general_query' => $isGeneralQuery,
                'organisation_name' => $org['name'] ?? 'Your Organisation',
                'summary' => $summary,
                'context' => $dataContext,
                'ai_method' => $aiResponse['method'] ?? 'pattern_matching',
                'suggestions' => [
                    'Try asking: "Tell me about our contracts"',
                    'Try asking: "How many active contracts do we have?"',
                    'Try asking: "What contracts are expiring soon?"',
                    'Try asking: "Show me payments this month"',
                    'Try asking: "What is our total contract value?"'
                ]
            ]);
        }
    } catch (Exception $e) {
        // If AI provider fails, fall back to pattern matching
        http_response_code(200);
        echo json_encode([
            'query' => $query,
            'is_general_query' => $isGeneralQuery,
            'organisation_name' => $org['name'] ?? 'Your Organisation',
            'summary' => $summary,
            'context' => $dataContext,
            'ai_method' => 'pattern_matching',
            'error' => $e->getMessage(),
            'suggestions' => [
                'Try asking: "Tell me about our contracts"',
                'Try asking: "How many active contracts do we have?"',
                'Try asking: "What contracts are expiring soon?"',
                'Try asking: "Show me payments this month"',
                'Try asking: "What is our total contract value?"'
            ]
        ]);
    }
    
} else {
    // GET request - return API info
    echo json_encode([
        'name' => 'AI Assistant API',
        'version' => '1.0',
        'description' => 'Natural language query interface for contract data',
        'endpoints' => [
            'POST /api/ai-assistant.php' => 'Process natural language query',
            'GET /api/export.php' => 'Export data in various formats'
        ],
        'supported_formats' => ['json', 'jsonld', 'csv'],
        'ai_models' => [
            'browser' => 'Web LLM (runs locally in browser)',
            'openai' => 'OpenAI GPT (requires API key)',
            'anthropic' => 'Anthropic Claude (requires API key)'
        ]
    ]);
}

