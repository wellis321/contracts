<?php
/**
 * AI Assistant Settings Page
 * Allows users to configure their preferred AI model and API keys
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
$organisationId = Auth::getOrganisationId();
$userId = Auth::getUserId();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && CSRF::validatePost()) {
    $data = [
        'ai_provider' => $_POST['ai_provider'] ?? 'pattern_matching',
        'openai_api_key' => trim($_POST['openai_api_key'] ?? ''),
        'anthropic_api_key' => trim($_POST['anthropic_api_key'] ?? ''),
        'huggingface_api_key' => trim($_POST['huggingface_api_key'] ?? ''),
        'gemini_api_key' => trim($_POST['gemini_api_key'] ?? ''),
        'ollama_url' => trim($_POST['ollama_url'] ?? 'http://localhost:11434'),
        'openai_model' => trim($_POST['openai_model'] ?? 'gpt-4o-mini'),
        'anthropic_model' => trim($_POST['anthropic_model'] ?? 'claude-3-haiku-20240307'),
        'huggingface_model' => trim($_POST['huggingface_model'] ?? 'mistralai/Mistral-7B-Instruct-v0.2'),
        'ollama_model' => trim($_POST['ollama_model'] ?? 'llama2'),
        'gemini_model' => trim($_POST['gemini_model'] ?? 'gemini-pro'),
        'send_data_to_external_apis' => isset($_POST['send_data_to_external_apis']),
        'use_browser_only_ai' => isset($_POST['use_browser_only_ai'])
    ];
    
    // Only update API keys if they're provided (don't overwrite with empty)
    $currentPrefs = AIPreference::get($userId);
    if (empty($data['openai_api_key']) && $currentPrefs) {
        unset($data['openai_api_key']);
    }
    if (empty($data['anthropic_api_key']) && $currentPrefs) {
        unset($data['anthropic_api_key']);
    }
    if (empty($data['huggingface_api_key']) && $currentPrefs) {
        unset($data['huggingface_api_key']);
    }
    if (empty($data['gemini_api_key']) && $currentPrefs) {
        unset($data['gemini_api_key']);
    }
    
    if (AIPreference::update($userId, $data)) {
        $success = 'AI preferences updated successfully.';
    } else {
        $error = 'Failed to update AI preferences.';
    }
}

// Get current preferences
$preferences = AIPreference::getOrCreate($userId, $organisationId);
$availableProviders = AIPreference::getAvailableProviders();

$pageTitle = 'AI Assistant Settings';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <h1>AI Assistant Settings</h1>
        <p style="color: var(--text-light); margin-top: 0.5rem;">
            Configure your preferred AI model and API keys. Your data access is automatically filtered based on your role and team permissions.
        </p>
        <div style="background: #e0f2fe; border-left: 4px solid var(--primary-color); padding: 0.75rem; border-radius: 0.5rem; margin-top: 1rem;">
            <strong style="display: block; margin-bottom: 0.25rem;">
                <i class="fa-solid fa-info-circle" style="margin-right: 0.5rem;"></i>Personal Settings
            </strong>
            <p style="margin: 0; font-size: 0.9rem; color: var(--text-color);">
                These settings are personal to you. Each user can configure their own AI preferences and API keys. 
                Your settings only affect your own AI Assistant experience.
            </p>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <?php echo CSRF::tokenField(); ?>
        
        <!-- AI Provider Selection -->
        <div style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem;">Choose Your AI Provider</h3>
            <p style="color: var(--text-light); margin-bottom: 1.5rem;">
                Select which AI service you want to use. Free options are available!
            </p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                <?php foreach ($availableProviders as $key => $provider): ?>
                    <div style="border: 2px solid <?php echo $preferences['ai_provider'] === $key ? 'var(--primary-color)' : 'var(--border-color)'; ?>; border-radius: 0.5rem; padding: 1.5rem; background: <?php echo $preferences['ai_provider'] === $key ? 'var(--primary-color-light)' : 'var(--bg-light)'; ?>; cursor: pointer;" onclick="document.getElementById('ai_provider_<?php echo $key; ?>').checked = true; updateProviderFields()">
                        <label style="display: flex; align-items: start; gap: 0.75rem; cursor: pointer;">
                            <input type="radio" name="ai_provider" value="<?php echo $key; ?>" id="ai_provider_<?php echo $key; ?>" <?php echo $preferences['ai_provider'] === $key ? 'checked' : ''; ?> onchange="updateProviderFields()" style="margin-top: 0.25rem;">
                            <div style="flex: 1;">
                                <strong style="display: block; margin-bottom: 0.5rem;">
                                    <?php echo htmlspecialchars($provider['name']); ?>
                                    <?php if ($provider['cost'] === 'free'): ?>
                                        <span style="background: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; margin-left: 0.5rem;">FREE</span>
                                    <?php endif; ?>
                                </strong>
                                <p style="margin: 0 0 0.5rem 0; color: var(--text-color); font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($provider['description']); ?>
                                </p>
                                <?php if (isset($provider['note'])): ?>
                                    <p style="margin: 0; color: var(--text-light); font-size: 0.85rem; font-style: italic;">
                                        <?php echo htmlspecialchars($provider['note']); ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (isset($provider['api_key_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($provider['api_key_url']); ?>" target="_blank" style="font-size: 0.85rem; color: var(--primary-color);">
                                        Get API Key →
                                    </a>
                                <?php endif; ?>
                            </div>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- API Keys Section -->
        <div id="apiKeysSection" style="margin-bottom: 2rem; padding-top: 2rem; border-top: 2px solid var(--border-color);">
            <h3 style="margin-bottom: 1rem;">API Keys & Configuration</h3>
            <p style="color: var(--text-light); margin-bottom: 1.5rem;">
                Enter your API keys for the selected provider. Keys are stored securely and only used for your account.
            </p>
            
            <!-- OpenAI -->
            <div id="openaiConfig" style="display: none; margin-bottom: 1.5rem; padding: 1rem; background: var(--bg-light); border-radius: 0.5rem;">
                <h4 style="margin-top: 0;">OpenAI Configuration</h4>
                <div class="form-group">
                    <label for="openai_api_key">OpenAI API Key</label>
                    <input type="password" id="openai_api_key" name="openai_api_key" class="form-control" 
                           value="<?php echo htmlspecialchars($preferences['openai_api_key'] ?? ''); ?>" 
                           placeholder="sk-...">
                    <small style="color: var(--text-light);">Get your API key from <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a></small>
                </div>
                <div class="form-group">
                    <label for="openai_model">Model</label>
                    <select id="openai_model" name="openai_model" class="form-control">
                        <option value="gpt-4o-mini" <?php echo ($preferences['openai_model'] ?? 'gpt-4o-mini') === 'gpt-4o-mini' ? 'selected' : ''; ?>>GPT-4o-mini (Cheaper)</option>
                        <option value="gpt-4" <?php echo ($preferences['openai_model'] ?? '') === 'gpt-4' ? 'selected' : ''; ?>>GPT-4 (More capable)</option>
                        <option value="gpt-3.5-turbo" <?php echo ($preferences['openai_model'] ?? '') === 'gpt-3.5-turbo' ? 'selected' : ''; ?>>GPT-3.5 Turbo (Fastest)</option>
                    </select>
                </div>
            </div>
            
            <!-- Anthropic -->
            <div id="anthropicConfig" style="display: none; margin-bottom: 1.5rem; padding: 1rem; background: var(--bg-light); border-radius: 0.5rem;">
                <h4 style="margin-top: 0;">Anthropic Configuration</h4>
                <div class="form-group">
                    <label for="anthropic_api_key">Anthropic API Key</label>
                    <input type="password" id="anthropic_api_key" name="anthropic_api_key" class="form-control" 
                           value="<?php echo htmlspecialchars($preferences['anthropic_api_key'] ?? ''); ?>" 
                           placeholder="sk-ant-...">
                    <small style="color: var(--text-light);">Get your API key from <a href="https://console.anthropic.com/" target="_blank">Anthropic Console</a></small>
                </div>
                <div class="form-group">
                    <label for="anthropic_model">Model</label>
                    <select id="anthropic_model" name="anthropic_model" class="form-control">
                        <option value="claude-3-haiku-20240307" <?php echo ($preferences['anthropic_model'] ?? 'claude-3-haiku-20240307') === 'claude-3-haiku-20240307' ? 'selected' : ''; ?>>Claude 3 Haiku (Fastest, Cheapest)</option>
                        <option value="claude-3-sonnet-20240229" <?php echo ($preferences['anthropic_model'] ?? '') === 'claude-3-sonnet-20240229' ? 'selected' : ''; ?>>Claude 3 Sonnet (Balanced)</option>
                        <option value="claude-3-opus-20240229" <?php echo ($preferences['anthropic_model'] ?? '') === 'claude-3-opus-20240229' ? 'selected' : ''; ?>>Claude 3 Opus (Most Capable)</option>
                    </select>
                </div>
            </div>
            
            <!-- Hugging Face -->
            <div id="huggingfaceConfig" style="display: none; margin-bottom: 1.5rem; padding: 1rem; background: var(--bg-light); border-radius: 0.5rem;">
                <h4 style="margin-top: 0;">Hugging Face Configuration</h4>
                <div class="form-group">
                    <label for="huggingface_api_key">Hugging Face API Key</label>
                    <input type="password" id="huggingface_api_key" name="huggingface_api_key" class="form-control" 
                           value="<?php echo htmlspecialchars($preferences['huggingface_api_key'] ?? ''); ?>" 
                           placeholder="hf_...">
                    <small style="color: var(--text-light); display: block; margin-top: 0.5rem;">
                        Get your free API key from <a href="https://huggingface.co/settings/tokens" target="_blank">Hugging Face</a><br>
                        <strong style="color: #dc2626;">Important:</strong> You need a token with <strong>"Inference API"</strong> permissions, not just "Read".<br>
                        <strong>Steps to create the right token:</strong><br>
                        1. Go to <a href="https://huggingface.co/settings/tokens" target="_blank">Hugging Face Settings → Tokens</a><br>
                        2. Click "New token"<br>
                        3. Name it (e.g., "Inference API")<br>
                        4. Select <strong>"Inference API"</strong> as the token type (or "Read" if Inference API option isn't available)<br>
                        5. Copy the token (starts with <code>hf_</code>)<br>
                        6. Paste it here and save<br>
                        <strong>Note:</strong> If you get a "permissions" error, you need to create a new token with Inference API access.
                    </small>
                </div>
                <div class="form-group">
                    <label for="huggingface_model">Model</label>
                    <input type="text" id="huggingface_model" name="huggingface_model" class="form-control" 
                           value="<?php echo htmlspecialchars($preferences['huggingface_model'] ?? 'google/flan-t5-large'); ?>" 
                           placeholder="google/flan-t5-large">
                    <small style="color: var(--text-light); display: block; margin-top: 0.5rem;">
                        <strong>Recommended models:</strong><br>
                        • <code>google/flan-t5-large</code> - Fast, reliable (default)<br>
                        • <code>mistralai/Mistral-7B-Instruct-v0.2</code> - Good quality<br>
                        • <code>meta-llama/Llama-2-7b-chat-hf</code> - Popular choice<br>
                        <strong>Note:</strong> Some models may not be available on the router endpoint. If you get a 404, try a different model.
                    </small>
                </div>
            </div>
            
            <!-- Gemini -->
            <div id="geminiConfig" style="display: none; margin-bottom: 1.5rem; padding: 1rem; background: var(--bg-light); border-radius: 0.5rem;">
                <h4 style="margin-top: 0;">Google Gemini Configuration</h4>
                <div class="form-group">
                    <label for="gemini_api_key">Gemini API Key</label>
                    <input type="password" id="gemini_api_key" name="gemini_api_key" class="form-control" 
                           value="<?php echo htmlspecialchars($preferences['gemini_api_key'] ?? ''); ?>" 
                           placeholder="AIza...">
                    <small style="color: var(--text-light);">Get your free API key from <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a></small>
                </div>
                <div class="form-group">
                    <label for="gemini_model">Model</label>
                    <select id="gemini_model" name="gemini_model" class="form-control">
                        <option value="gemini-1.5-flash" <?php echo ($preferences['gemini_model'] ?? 'gemini-1.5-flash') === 'gemini-1.5-flash' ? 'selected' : ''; ?>>Gemini 1.5 Flash (Recommended - Fast & Free)</option>
                        <option value="gemini-1.5-pro" <?php echo ($preferences['gemini_model'] ?? '') === 'gemini-1.5-pro' ? 'selected' : ''; ?>>Gemini 1.5 Pro (More Capable)</option>
                        <option value="gemini-pro" <?php echo ($preferences['gemini_model'] ?? '') === 'gemini-pro' ? 'selected' : ''; ?>>Gemini Pro (Legacy)</option>
                    </select>
                    <small style="color: var(--text-light);">Gemini 1.5 Flash is recommended for best performance and free tier compatibility.</small>
                </div>
            </div>
            
            <!-- Ollama -->
            <div id="ollamaConfig" style="display: none; margin-bottom: 1.5rem; padding: 1rem; background: var(--bg-light); border-radius: 0.5rem;">
                <h4 style="margin-top: 0;">Ollama Configuration</h4>
                <div class="form-group">
                    <label for="ollama_url">Ollama Server URL</label>
                    <input type="text" id="ollama_url" name="ollama_url" class="form-control" 
                           value="<?php echo htmlspecialchars($preferences['ollama_url'] ?? 'http://localhost:11434'); ?>" 
                           placeholder="http://localhost:11434">
                    <small style="color: var(--text-light);">Make sure Ollama is running locally. Download from <a href="https://ollama.ai" target="_blank">ollama.ai</a></small>
                </div>
                <div class="form-group">
                    <label for="ollama_model">Model</label>
                    <input type="text" id="ollama_model" name="ollama_model" class="form-control" 
                           value="<?php echo htmlspecialchars($preferences['ollama_model'] ?? 'llama2'); ?>" 
                           placeholder="llama2">
                    <small style="color: var(--text-light);">Popular models: llama2, mistral, codellama. Run 'ollama pull [model]' to download.</small>
                </div>
            </div>
        </div>
        
        <!-- Privacy Settings -->
        <div style="margin-bottom: 2rem; padding-top: 2rem; border-top: 2px solid var(--border-color);">
            <h3 style="margin-bottom: 1rem;">Privacy Settings</h3>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="send_data_to_external_apis" value="1" 
                           <?php echo ($preferences['send_data_to_external_apis'] ?? false) ? 'checked' : ''; ?>>
                    Allow sending data to external AI APIs (OpenAI, Anthropic, etc.)
                </label>
                <small style="color: var(--text-light); display: block; margin-top: 0.5rem;">
                    When enabled, your contract data will be sent to the selected AI provider. 
                    Only data you have access to (based on your role and team) will be sent.
                </small>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="use_browser_only_ai" value="1" 
                           <?php echo ($preferences['use_browser_only_ai'] ?? true) ? 'checked' : ''; ?>>
                    Prefer browser-based AI when available
                </label>
                <small style="color: var(--text-light); display: block; margin-top: 0.5rem;">
                    When available, use AI that runs entirely in your browser (no data sent to external servers).
                </small>
            </div>
        </div>
        
        <!-- Access Control Notice -->
        <div style="background: #e0f2fe; border-left: 4px solid var(--primary-color); padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem;">
            <strong style="display: block; margin-bottom: 0.5rem;">
                <i class="fa-solid fa-shield-halved" style="margin-right: 0.5rem;"></i>Access Control
            </strong>
            <p style="margin: 0; color: var(--text-color); font-size: 0.9rem;">
                Your data access is automatically filtered based on your role and team permissions. 
                <?php if (RBAC::isAdmin()): ?>
                    As an admin, you have access to all contracts in your organisation.
                <?php else: ?>
                    As a team member, you only see contracts for your assigned teams.
                <?php endif; ?>
                The AI assistant will only use data you have permission to view.
            </p>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Save Settings</button>
            <a href="<?php echo url('ai-assistant.php'); ?>" class="btn btn-secondary">Back to AI Assistant</a>
        </div>
    </form>
</div>

<script>
function updateProviderFields() {
    const selectedProvider = document.querySelector('input[name="ai_provider"]:checked')?.value || 'pattern_matching';
    
    // Hide all config sections
    document.getElementById('openaiConfig').style.display = 'none';
    document.getElementById('anthropicConfig').style.display = 'none';
    document.getElementById('huggingfaceConfig').style.display = 'none';
    document.getElementById('geminiConfig').style.display = 'none';
    document.getElementById('ollamaConfig').style.display = 'none';
    
    // Show relevant config section
    if (selectedProvider === 'openai') {
        document.getElementById('openaiConfig').style.display = 'block';
    } else if (selectedProvider === 'anthropic') {
        document.getElementById('anthropicConfig').style.display = 'block';
    } else if (selectedProvider === 'huggingface') {
        document.getElementById('huggingfaceConfig').style.display = 'block';
    } else if (selectedProvider === 'gemini') {
        document.getElementById('geminiConfig').style.display = 'block';
    } else if (selectedProvider === 'ollama') {
        document.getElementById('ollamaConfig').style.display = 'block';
    }
}

// Initialize on page load
updateProviderFields();
</script>

<?php include INCLUDES_PATH . '/footer.php'; ?>

