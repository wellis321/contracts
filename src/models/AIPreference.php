<?php
/**
 * AI Preference Model
 * Manages user AI assistant preferences and API keys
 */
class AIPreference {
    
    /**
     * Get or create AI preferences for current user
     */
    public static function getOrCreate($userId, $organisationId) {
        $db = getDbConnection();
        
        // Try to get existing preferences
        $stmt = $db->prepare("SELECT * FROM ai_preferences WHERE user_id = ?");
        $stmt->execute([$userId]);
        $prefs = $stmt->fetch();
        
        if ($prefs) {
            return $prefs;
        }
        
        // Create default preferences
        $stmt = $db->prepare("
            INSERT INTO ai_preferences (
                user_id, organisation_id, ai_provider, 
                use_browser_only_ai, send_data_to_external_apis
            ) VALUES (?, ?, 'pattern_matching', TRUE, FALSE)
        ");
        $stmt->execute([$userId, $organisationId]);
        
        return self::getOrCreate($userId, $organisationId);
    }
    
    /**
     * Get AI preferences for current user
     */
    public static function get($userId) {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM ai_preferences WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    /**
     * Update AI preferences
     */
    public static function update($userId, $data) {
        $db = getDbConnection();
        $organisationId = Auth::getOrganisationId();
        
        // Ensure preferences exist
        self::getOrCreate($userId, $organisationId);
        
        $allowedFields = [
            'ai_provider', 'openai_api_key', 'anthropic_api_key', 
            'huggingface_api_key', 'gemini_api_key', 'ollama_url',
            'openai_model', 'anthropic_model', 'huggingface_model', 
            'ollama_model', 'gemini_model',
            'send_data_to_external_apis', 'use_browser_only_ai'
        ];
        
        $updates = [];
        $params = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                if (in_array($field, ['send_data_to_external_apis', 'use_browser_only_ai'])) {
                    $params[] = $data[$field] ? 1 : 0;
                } else {
                    $params[] = $data[$field];
                }
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $params[] = $userId;
        
        $sql = "UPDATE ai_preferences SET " . implode(', ', $updates) . " WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Get available AI providers
     */
    public static function getAvailableProviders() {
        return [
            'pattern_matching' => [
                'name' => 'Pattern Matching (Built-in)',
                'description' => 'Simple keyword-based responses. No API keys required. Free.',
                'requires_api_key' => false,
                'privacy' => 'local',
                'cost' => 'free'
            ],
            'web_llm' => [
                'name' => 'Web LLM (Browser-based)',
                'description' => 'Runs entirely in your browser. No data sent to external servers. Free.',
                'requires_api_key' => false,
                'privacy' => 'local',
                'cost' => 'free',
                'note' => 'Requires initial model download (~2-4GB)'
            ],
            'huggingface' => [
                'name' => 'Hugging Face (Free Tier)',
                'description' => 'Free AI models via Hugging Face Inference API. Limited requests per month.',
                'requires_api_key' => true,
                'privacy' => 'external',
                'cost' => 'free',
                'api_key_url' => 'https://huggingface.co/settings/tokens'
            ],
            'ollama' => [
                'name' => 'Ollama (Local)',
                'description' => 'Run AI models locally on your computer. Completely private. Free.',
                'requires_api_key' => false,
                'privacy' => 'local',
                'cost' => 'free',
                'note' => 'Requires Ollama installed locally (https://ollama.ai)'
            ],
            'gemini' => [
                'name' => 'Google Gemini (Free Tier)',
                'description' => 'Google\'s Gemini AI. Free tier available with generous limits.',
                'requires_api_key' => true,
                'privacy' => 'external',
                'cost' => 'free',
                'api_key_url' => 'https://makersuite.google.com/app/apikey'
            ],
            'openai' => [
                'name' => 'OpenAI GPT',
                'description' => 'OpenAI\'s GPT models (GPT-4, GPT-4o-mini). Paid service.',
                'requires_api_key' => true,
                'privacy' => 'external',
                'cost' => 'paid',
                'api_key_url' => 'https://platform.openai.com/api-keys'
            ],
            'anthropic' => [
                'name' => 'Anthropic Claude',
                'description' => 'Anthropic\'s Claude models. Paid service.',
                'requires_api_key' => true,
                'privacy' => 'external',
                'cost' => 'paid',
                'api_key_url' => 'https://console.anthropic.com/'
            ]
        ];
    }
}




