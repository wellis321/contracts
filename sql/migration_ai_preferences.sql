-- Migration: AI Assistant Preferences
-- Allows users to configure their preferred AI model and API keys

CREATE TABLE IF NOT EXISTS ai_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    organisation_id INT NOT NULL,
    
    -- AI Provider Selection
    ai_provider ENUM('pattern_matching', 'web_llm', 'openai', 'anthropic', 'huggingface', 'ollama', 'gemini') 
        DEFAULT 'pattern_matching' COMMENT 'AI provider to use',
    
    -- API Keys (encrypted at application level, stored as plain text for now - should be encrypted in production)
    openai_api_key VARCHAR(255) NULL COMMENT 'OpenAI API key (encrypted)',
    anthropic_api_key VARCHAR(255) NULL COMMENT 'Anthropic API key (encrypted)',
    huggingface_api_key VARCHAR(255) NULL COMMENT 'Hugging Face API key (encrypted)',
    gemini_api_key VARCHAR(255) NULL COMMENT 'Google Gemini API key (encrypted)',
    ollama_url VARCHAR(255) NULL DEFAULT 'http://localhost:11434' COMMENT 'Ollama server URL',
    
    -- Model Selection
    openai_model VARCHAR(100) DEFAULT 'gpt-4o-mini' COMMENT 'OpenAI model (gpt-4, gpt-4o-mini, etc.)',
    anthropic_model VARCHAR(100) DEFAULT 'claude-3-haiku-20240307' COMMENT 'Anthropic model (claude-3-opus, claude-3-sonnet, claude-3-haiku)',
    huggingface_model VARCHAR(255) DEFAULT 'mistralai/Mistral-7B-Instruct-v0.2' COMMENT 'Hugging Face model',
    ollama_model VARCHAR(255) DEFAULT 'llama2' COMMENT 'Ollama model name',
    gemini_model VARCHAR(100) DEFAULT 'gemini-1.5-flash' COMMENT 'Google Gemini model',
    
    -- Privacy Settings
    send_data_to_external_apis BOOLEAN DEFAULT FALSE COMMENT 'Allow sending data to external AI APIs (OpenAI, Anthropic, etc.)',
    use_browser_only_ai BOOLEAN DEFAULT TRUE COMMENT 'Prefer browser-based AI when available',
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_ai_pref (user_id),
    INDEX idx_organisation (organisation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

