# AI Assistant Guide

## How the AI Interface Works

The AI Assistant (`/ai-assistant.php`) provides a natural language interface for querying your contract data. It works in three stages:

### 1. **Query Processing** (Frontend)
When you ask a question, the frontend:
- Sends your query to the backend API (`/api/ai-assistant.php`)
- Displays a "Thinking..." indicator while processing
- Handles the response and displays it in a chat-like interface

### 2. **Data Retrieval** (Backend API)
The backend API (`/api/ai-assistant.php`):
- **Detects query intent**: Identifies if it's a general question (e.g., "Tell me about our contracts") or a specific query
- **Fetches relevant data**: Retrieves contracts, payments, people, or other data based on keywords
- **Calculates summaries**: For general queries, it calculates statistics like:
  - Total contracts (active/inactive)
  - Total contract value
  - Contracts expiring soon
  - Contracts by local authority
  - Payment totals
- **Returns structured data**: Sends back both summary statistics and detailed data samples

### 3. **Response Generation** (Frontend)
The frontend processes the response using one of two methods:

#### **Method A: Pattern Matching** (Current Default)
- Uses keyword matching and simple logic
- Handles common queries like:
  - "How many active contracts?"
  - "What contracts are expiring?"
  - "Total contract value"
  - "Tell me about our contracts" (general overview)

#### **Method B: Web AI** (Optional Enhancement)
- Uses browser-based AI (Web LLM) for more sophisticated responses
- Can handle complex, conversational queries
- Runs entirely in the browser (privacy-preserving)
- No API keys required

## Example: "Tell me about our contracts"

When you ask this question:

1. **Backend detects** it's a general query (`is_general_query: true`)
2. **Backend fetches** all contracts and calculates:
   ```json
   {
     "summary": {
       "contracts": {
         "total": 15,
         "active": 12,
         "inactive": 3,
         "total_value": 1250000.00,
         "expiring_soon_count": 2,
         "expiring_soon": [...],
         "by_local_authority": {...}
       }
     }
   }
   ```
3. **Frontend processes** the summary and generates a readable response:
   ```
   Here's an overview of Your Organisation's contracts:

   📊 Summary:
   • Total Contracts: 15
   • Active: 12
   • Inactive: 3
   • Total Value: £1,250,000.00

   ⚠️ Expiring Soon (next 3 months): 2
   ...
   ```

## How to Extend the AI Assistant

### Option 1: Enhance Pattern Matching

Edit `public/ai-assistant.php` and add new patterns to the `processQuery()` function:

```javascript
function processQuery(query, data) {
    const lowerQuery = query.toLowerCase();
    
    // Add your new pattern
    if (lowerQuery.includes('your keyword')) {
        // Your logic here
        return 'Your response';
    }
    
    // ... existing patterns
}
```

### Option 2: Add Web AI Support

#### Using Web LLM (Browser-Based)

1. **Add Web LLM library** to `public/ai-assistant.php`:

```html
<!-- Add before closing </head> or in header -->
<script src="https://cdn.jsdelivr.net/npm/@mlc-ai/web-llm@latest/dist/index.js"></script>
```

2. **Initialize Web AI** (already included in the code):

```javascript
async function initWebAI() {
    try {
        // Web LLM will be available as window.webllm
        const engine = await webllm.CreateWebLLMEngine("Llama-2-7b-chat-hf-q4f32_1");
        webAI = engine;
        isWebAIAvailable = true;
    } catch (error) {
        console.log('Web AI not available:', error);
    }
}
```

#### Using OpenAI API

1. **Add API key** to `.env`:
```
OPENAI_API_KEY=your_api_key_here
```

2. **Modify backend** (`public/api/ai-assistant.php`):

```php
// Add at the top
$openaiApiKey = $_ENV['OPENAI_API_KEY'] ?? null;

// In the POST handler, after fetching data:
if ($openaiApiKey && $isGeneralQuery) {
    // Call OpenAI API
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $openaiApiKey,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an AI assistant helping with social care contract management.'
                ],
                [
                    'role' => 'user',
                    'content' => "Query: $query\n\nData: " . json_encode($summary, JSON_PRETTY_PRINT)
                ]
            ]
        ])
    ]);
    
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    $aiAnswer = $result['choices'][0]['message']['content'] ?? null;
    
    if ($aiAnswer) {
        echo json_encode([
            'query' => $query,
            'response' => $aiAnswer,
            'context' => $dataContext,
            'summary' => $summary
        ]);
        exit;
    }
}
```

#### Using Anthropic Claude API

Similar to OpenAI, but use:

```php
$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'x-api-key: ' . $_ENV['ANTHROPIC_API_KEY'],
        'anthropic-version: 2023-06-01',
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'model' => 'claude-3-opus-20240229',
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => "Query: $query\n\nData: " . json_encode($summary, JSON_PRETTY_PRINT)
            ]
        ]
    ])
]);
```

### Option 3: Add New Data Sources

To add support for querying other data types:

1. **Edit backend** (`public/api/ai-assistant.php`):

```php
// Add new keyword detection
if (stripos($query, 'your_keyword') !== false) {
    // Fetch your data
    $stmt = $db->prepare("SELECT * FROM your_table WHERE organisation_id = ?");
    $stmt->execute([$organisationId]);
    $yourData = $stmt->fetchAll();
    
    // Add to context
    $dataContext['your_data'] = $yourData;
    
    // Add to summary
    $summary['your_data'] = [
        'total' => count($yourData),
        // ... other stats
    ];
}
```

2. **Update frontend** (`public/ai-assistant.php`):

```javascript
function processQuery(query, data) {
    // Add pattern for your new data type
    if (lowerQuery.includes('your keyword')) {
        const yourData = data.summary?.your_data || {};
        return `Your custom response: ${yourData.total} items found.`;
    }
}
```

### Option 4: Add Custom Query Types

To handle specific organisational needs:

```javascript
function processQuery(query, data) {
    const lowerQuery = query.toLowerCase();
    
    // Example: Handle budget queries
    if (lowerQuery.includes('budget') || lowerQuery.includes('spending')) {
        const contracts = data.summary.contracts || {};
        const payments = data.summary.payments || {};
        
        const totalCommitted = contracts.total_value || 0;
        const totalSpent = payments.total_amount || 0;
        const remaining = totalCommitted - totalSpent;
        
        return `Budget Overview:
        • Total Committed: £${totalCommitted.toFixed(2)}
        • Total Spent: £${totalSpent.toFixed(2)}
        • Remaining: £${remaining.toFixed(2)}`;
    }
    
    // Example: Handle risk assessment queries
    if (lowerQuery.includes('risk') || lowerQuery.includes('concern')) {
        const expiring = data.summary.contracts?.expiring_soon_count || 0;
        const inactive = data.summary.contracts?.inactive || 0;
        
        let risks = [];
        if (expiring > 3) risks.push(`${expiring} contracts expiring soon`);
        if (inactive > 5) risks.push(`${inactive} inactive contracts`);
        
        if (risks.length === 0) {
            return 'No significant risks identified.';
        }
        
        return `Potential concerns:\n${risks.map(r => `• ${r}`).join('\n')}`;
    }
    
    // ... existing patterns
}
```

## Best Practices

1. **Privacy**: Use browser-based AI (Web LLM) when possible to keep data private
2. **Performance**: Limit data samples to 20-50 records for context
3. **Accuracy**: Always provide summary statistics for general queries
4. **User Experience**: Format responses clearly with sections and bullet points
5. **Error Handling**: Always have a fallback to pattern matching if AI fails
6. **Security**: Never expose API keys in frontend code; use environment variables

## Testing Your Extensions

1. **Test general queries**: "Tell me about our contracts"
2. **Test specific queries**: "How many active contracts?"
3. **Test edge cases**: Empty data, very large datasets
4. **Test error handling**: Invalid queries, API failures

## Example Extensions

See `docs/DATA_PORTABILITY_AND_AI.md` for more examples of:
- Exporting data for external AI systems
- Integrating with national systems
- Using JSON-LD for semantic AI processing

