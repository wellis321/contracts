<?php
/**
 * AI Provider Service
 * Handles different AI providers with access control and data isolation
 */
class AIProvider {
    
    private $preferences;
    private $userId;
    private $organisationId;
    private $accessibleTeamIds;
    
    public function __construct($userId, $organisationId, $accessibleTeamIds) {
        $this->userId = $userId;
        $this->organisationId = $organisationId;
        $this->accessibleTeamIds = $accessibleTeamIds;
        $this->preferences = AIPreference::getOrCreate($userId, $organisationId);
    }
    
    /**
     * Generate AI response with proper access control
     * Only includes data the user has access to
     */
    public function generateResponse($query, $dataContext, $summary) {
        $provider = $this->preferences['ai_provider'] ?? 'pattern_matching';
        
        // Ensure data is filtered by access control
        $filteredContext = $this->filterDataByAccess($dataContext);
        $filteredSummary = $this->filterDataByAccess($summary);
        
        // Check privacy settings
        if ($this->requiresExternalAPI($provider)) {
            if (!($this->preferences['send_data_to_external_apis'] ?? false)) {
                // User hasn't enabled external APIs, fall back to pattern matching
                return $this->patternMatching($query, $filteredContext, $filteredSummary);
            }
        }
        
        switch ($provider) {
            case 'openai':
                return $this->openAI($query, $filteredContext, $filteredSummary);
            case 'anthropic':
                return $this->anthropic($query, $filteredContext, $filteredSummary);
            case 'huggingface':
                return $this->huggingFace($query, $filteredContext, $filteredSummary);
            case 'gemini':
                return $this->gemini($query, $filteredContext, $filteredSummary);
            case 'ollama':
                return $this->ollama($query, $filteredContext, $filteredSummary);
            case 'web_llm':
                // Web LLM is handled client-side, return data for client processing
                return [
                    'method' => 'web_llm',
                    'data' => [
                        'query' => $query,
                        'context' => $filteredContext,
                        'summary' => $filteredSummary
                    ]
                ];
            default:
                return $this->patternMatching($query, $filteredContext, $filteredSummary);
        }
    }
    
    /**
     * Filter data based on user's access level
     * Ensures team members only see their team's data
     */
    private function filterDataByAccess($data) {
        if (!is_array($data)) {
            return $data;
        }
        
        // If user has access to all teams, return all data
        if ($this->accessibleTeamIds === null) {
            return $data;
        }
        
        // If user has no team access, return empty
        if (empty($this->accessibleTeamIds)) {
            return [];
        }
        
        // Filter contracts by team access
        if (isset($data['contracts']) && is_array($data['contracts'])) {
            $data['contracts'] = array_filter($data['contracts'], function($contract) {
                // If contract has no team, include it (null team means accessible to all)
                if (empty($contract['team_id'])) {
                    return true;
                }
                // Otherwise, check if user has access to this team
                return in_array($contract['team_id'], $this->accessibleTeamIds);
            });
            $data['contracts'] = array_values($data['contracts']); // Re-index array
        }
        
        // Filter payments by contract team access
        if (isset($data['payments']) && is_array($data['payments'])) {
            $db = getDbConnection();
            $filteredPayments = [];
            
            foreach ($data['payments'] as $payment) {
                if (empty($payment['contract_id'])) {
                    continue;
                }
                
                // Check if user has access to this contract's team
                $stmt = $db->prepare("SELECT team_id FROM contracts WHERE id = ? AND organisation_id = ?");
                $stmt->execute([$payment['contract_id'], $this->organisationId]);
                $contract = $stmt->fetch();
                
                if ($contract) {
                    // If contract has no team, include it
                    if (empty($contract['team_id'])) {
                        $filteredPayments[] = $payment;
                    } elseif (in_array($contract['team_id'], $this->accessibleTeamIds)) {
                        $filteredPayments[] = $payment;
                    }
                }
            }
            
            $data['payments'] = $filteredPayments;
        }
        
        return $data;
    }
    
    /**
     * Check if provider requires external API
     */
    private function requiresExternalAPI($provider) {
        $externalProviders = ['openai', 'anthropic', 'huggingface', 'gemini'];
        return in_array($provider, $externalProviders);
    }
    
    /**
     * Pattern matching (built-in, no AI)
     */
    private function patternMatching($query, $context, $summary) {
        // This will be handled by the frontend processQuery function
        return [
            'method' => 'pattern_matching',
            'data' => [
                'query' => $query,
                'context' => $context,
                'summary' => $summary
            ]
        ];
    }
    
    /**
     * OpenAI API
     */
    private function openAI($query, $context, $summary) {
        $apiKey = $this->preferences['openai_api_key'] ?? null;
        $model = $this->preferences['openai_model'] ?? 'gpt-4o-mini';
        
        if (!$apiKey) {
            throw new Exception('OpenAI API key not configured');
        }
        
        $prompt = $this->buildPrompt($query, $context, $summary);
        
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an AI assistant helping with social care contract management. Only use the data provided to answer questions. Be specific with numbers and dates.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 1000,
                'temperature' => 0.7
            ])
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            $error = json_decode($response, true);
            throw new Exception('OpenAI API error: ' . ($error['error']['message'] ?? 'Unknown error'));
        }
        
        $result = json_decode($response, true);
        return [
            'method' => 'openai',
            'response' => $result['choices'][0]['message']['content'] ?? 'No response generated'
        ];
    }
    
    /**
     * Anthropic Claude API
     */
    private function anthropic($query, $context, $summary) {
        $apiKey = $this->preferences['anthropic_api_key'] ?? null;
        $model = $this->preferences['anthropic_model'] ?? 'claude-3-haiku-20240307';
        
        if (!$apiKey) {
            throw new Exception('Anthropic API key not configured');
        }
        
        $prompt = $this->buildPrompt($query, $context, $summary);
        
        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01',
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => $model,
                'max_tokens' => 1000,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'system' => 'You are an AI assistant helping with social care contract management. Only use the data provided to answer questions. Be specific with numbers and dates.'
            ])
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            $error = json_decode($response, true);
            throw new Exception('Anthropic API error: ' . ($error['error']['message'] ?? 'Unknown error'));
        }
        
        $result = json_decode($response, true);
        return [
            'method' => 'anthropic',
            'response' => $result['content'][0]['text'] ?? 'No response generated'
        ];
    }
    
    /**
     * Hugging Face Inference API (Free Tier)
     */
    private function huggingFace($query, $context, $summary) {
        $apiKey = $this->preferences['huggingface_api_key'] ?? null;
        $model = $this->preferences['huggingface_model'] ?? 'google/flan-t5-large';
        
        if (!$apiKey) {
            throw new Exception('Hugging Face API key not configured');
        }
        
        $prompt = $this->buildPrompt($query, $context, $summary);
        
        // Validate token format
        if (!preg_match('/^hf_[A-Za-z0-9]{20,}$/', $apiKey)) {
            throw new Exception('Invalid Hugging Face API key format. Token should start with "hf_" followed by alphanumeric characters.');
        }
        
        // Try the inference endpoint first (most reliable for Read tokens)
        // The api-inference endpoint works better with Read tokens
        $endpoint = "https://api-inference.huggingface.co/models/{$model}";
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'inputs' => $prompt,
                'parameters' => [
                    'max_new_tokens' => 500,
                    'temperature' => 0.7,
                    'return_full_text' => false
                ],
                'options' => [
                    'wait_for_model' => true
                ]
            ]),
            CURLOPT_TIMEOUT => 60 // Increase timeout for model loading
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // If inference endpoint fails, try router endpoint as fallback
        if ($httpCode !== 200 && $httpCode !== 503) { // 503 means model is loading, which is OK
            // Try router endpoint as fallback
            $endpoint = "https://router.huggingface.co/models/{$model}";
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json'
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'inputs' => $prompt,
                    'parameters' => [
                        'max_new_tokens' => 500,
                        'temperature' => 0.7,
                        'return_full_text' => false
                    ],
                    'options' => [
                        'wait_for_model' => true
                    ]
                ]),
                CURLOPT_TIMEOUT => 60
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
        }
        
        if ($httpCode !== 200 && $httpCode !== 503) {
            $error = json_decode($response, true);
            $errorMsg = 'Unknown error';
            $rawResponse = substr($response, 0, 500); // First 500 chars for debugging
            
            // Try to extract meaningful error message
            if (is_array($error)) {
                if (isset($error['error'])) {
                    if (is_array($error['error'])) {
                        $errorMsg = isset($error['error']['message']) ? $error['error']['message'] : json_encode($error['error']);
                    } else {
                        $errorMsg = $error['error'];
                    }
                } elseif (isset($error['message'])) {
                    $errorMsg = $error['message'];
                } elseif (isset($error['msg'])) {
                    $errorMsg = $error['msg'];
                } else {
                    // If we can't parse it, show the raw response
                    $errorMsg = 'HTTP ' . $httpCode . ': ' . $rawResponse;
                }
            } elseif (is_string($error)) {
                $errorMsg = $error;
            } else {
                // Show raw response if we can't parse
                $errorMsg = 'HTTP ' . $httpCode . ': ' . $rawResponse;
            }
            
            if ($curlError) {
                $errorMsg .= ' (cURL error: ' . $curlError . ')';
            }
            
            throw new Exception('Hugging Face API error: ' . $errorMsg);
        }
        
        $result = json_decode($response, true);
        
        // Handle different response formats
        $text = 'No response generated';
        if (is_array($result)) {
            if (isset($result[0]['generated_text'])) {
                $text = $result[0]['generated_text'];
            } elseif (isset($result['generated_text'])) {
                $text = $result['generated_text'];
            } elseif (isset($result[0]) && is_string($result[0])) {
                $text = $result[0];
            }
        } elseif (is_string($result)) {
            $text = $result;
        }
        
        return [
            'method' => 'huggingface',
            'response' => $text
        ];
    }
    
    /**
     * Google Gemini API (Free Tier)
     */
    private function gemini($query, $context, $summary) {
        $apiKey = $this->preferences['gemini_api_key'] ?? null;
        $model = $this->preferences['gemini_model'] ?? 'gemini-1.5-flash';
        
        if (!$apiKey) {
            throw new Exception('Gemini API key not configured');
        }
        
        $prompt = $this->buildPrompt($query, $context, $summary);
        
        // Try v1 API first (newer models)
        $ch = curl_init("https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$apiKey}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ])
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // If v1 fails, try v1beta with gemini-pro
        if ($httpCode !== 200) {
            $error = json_decode($response, true);
            $errorMsg = $error['error']['message'] ?? 'Unknown error';
            
            // If model not found, try with gemini-pro on v1beta
            if (stripos($errorMsg, 'not found') !== false && $model !== 'gemini-pro') {
                $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={$apiKey}");
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json'
                    ],
                    CURLOPT_POSTFIELDS => json_encode([
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ]
                    ])
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
            }
            
            if ($httpCode !== 200) {
                $error = json_decode($response, true);
                throw new Exception('Gemini API error: ' . ($error['error']['message'] ?? 'Unknown error'));
            }
        }
        
        $result = json_decode($response, true);
        return [
            'method' => 'gemini',
            'response' => $result['candidates'][0]['content']['parts'][0]['text'] ?? 'No response generated'
        ];
    }
    
    /**
     * Ollama (Local AI)
     */
    private function ollama($query, $context, $summary) {
        $ollamaUrl = $this->preferences['ollama_url'] ?? 'http://localhost:11434';
        $model = $this->preferences['ollama_model'] ?? 'llama2';
        
        $prompt = $this->buildPrompt($query, $context, $summary);
        
        $ch = curl_init("{$ollamaUrl}/api/generate");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => $model,
                'prompt' => $prompt,
                'stream' => false
            ]),
            CURLOPT_TIMEOUT => 60 // Ollama can be slow
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Ollama error: Make sure Ollama is running locally at ' . $ollamaUrl);
        }
        
        $result = json_decode($response, true);
        return [
            'method' => 'ollama',
            'response' => $result['response'] ?? 'No response generated'
        ];
    }
    
    /**
     * Build prompt for AI providers
     */
    private function buildPrompt($query, $context, $summary) {
        $summaryText = !empty($summary) ? "\n\nSummary Statistics:\n" . json_encode($summary, JSON_PRETTY_PRINT) : '';
        $contextText = !empty($context) ? "\n\nAvailable Data Context:\n" . json_encode($context, JSON_PRETTY_PRINT) : '';
        
        return "You are an AI assistant helping with social care contract management.

User question: {$query}
{$summaryText}
{$contextText}

Please provide a helpful, conversational answer based on the data provided. Be specific with numbers and dates. If the data doesn't contain enough information, say so. Format your response in plain text with clear sections.";
    }
}

