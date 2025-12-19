<?php
/**
 * AI Assistant Interface
 * Browser-based AI assistant for querying contract data using natural language
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireLogin();
$organisationId = Auth::getOrganisationId();

$pageTitle = 'AI Assistant';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: start;">
            <div>
                <h1>AI Assistant</h1>
                <p style="color: var(--text-light); margin-top: 0.5rem;">
                    Ask questions about your contracts, payments, and data in natural language
                </p>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button class="btn btn-secondary" style="white-space: nowrap;" id="initWebLLMBtn">
                    <i class="fa-solid fa-download" style="margin-right: 0.5rem;"></i>Load Browser AI
                </button>
                <a href="<?php echo url('ai-settings.php'); ?>" class="btn btn-secondary" style="white-space: nowrap;">
                    <i class="fa-solid fa-gear" style="margin-right: 0.5rem;"></i>Settings
                </a>
            </div>
        </div>
    </div>
    
    <div style="max-width: 900px; margin: 2rem auto;">
        <!-- AI Chat Interface -->
        <div id="ai-chat-container" style="background: #f8fafc; border: 2px solid var(--border-color); border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 2rem; min-height: 400px; display: flex; flex-direction: column;">
            <div id="ai-messages" style="flex: 1; overflow-y: auto; margin-bottom: 1rem; padding: 1rem; background: white; border-radius: 0.5rem; max-height: 500px;">
                <div class="ai-message ai-assistant" style="margin-bottom: 1rem; padding: 1rem; background: #e0f2fe; border-radius: 0.5rem; border-left: 4px solid var(--primary-color);">
                    <div style="display: flex; align-items: start; gap: 0.75rem;">
                        <i class="fa-solid fa-robot" style="color: var(--primary-color); font-size: 1.5rem; flex-shrink: 0;"></i>
                        <div style="flex: 1;">
                            <strong>AI Assistant</strong>
                            <p style="margin: 0.5rem 0 0 0; color: var(--text-color);">
                                Hello! I can help you query your contract data using natural language. Try asking:
                            </p>
                            <ul style="margin: 0.5rem 0 0 1.5rem; color: var(--text-color);">
                                <li>"Tell me about our contracts"</li>
                                <li>"How many active contracts do we have?"</li>
                                <li>"What contracts are expiring in the next 3 months?"</li>
                                <li>"Show me total payments this month"</li>
                                <li>"What is our total contract value?"</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 0.5rem;">
                <input 
                    type="text" 
                    id="ai-query-input" 
                    placeholder="Ask a question about your contracts..." 
                    style="flex: 1; padding: 0.875rem; border: 2px solid var(--border-color); border-radius: 0.5rem; font-size: 1rem;"
                >
                <button 
                    id="ai-query-submit"
                    class="btn btn-primary"
                    style="padding: 0.875rem 2rem; white-space: nowrap;"
                >
                    <i class="fa-solid fa-paper-plane" style="margin-right: 0.5rem;"></i>Ask
                </button>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem;">Quick Actions</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <button class="btn btn-primary quick-action-btn" data-query="Tell me about our contracts" style="text-align: left; padding: 1rem;">
                    <i class="fa-solid fa-chart-line" style="margin-right: 0.5rem;"></i>
                    Contract Overview
                </button>
                <button class="btn btn-secondary quick-action-btn" data-query="How many active contracts do we have?" style="text-align: left; padding: 1rem;">
                    <i class="fa-solid fa-file-contract" style="margin-right: 0.5rem;"></i>
                    Active Contracts
                </button>
                <button class="btn btn-secondary quick-action-btn" data-query="What contracts are expiring soon?" style="text-align: left; padding: 1rem;">
                    <i class="fa-solid fa-calendar-exclamation" style="margin-right: 0.5rem;"></i>
                    Expiring Contracts
                </button>
                <button class="btn btn-secondary quick-action-btn" data-query="What is our total contract value?" style="text-align: left; padding: 1rem;">
                    <i class="fa-solid fa-pound-sign" style="margin-right: 0.5rem;"></i>
                    Total Value
                </button>
                <button class="btn btn-secondary quick-action-btn" data-query="Show me recent payments" style="text-align: left; padding: 1rem;">
                    <i class="fa-solid fa-money-bill-wave" style="margin-right: 0.5rem;"></i>
                    Recent Payments
                </button>
            </div>
        </div>
        
        <!-- Data Export Options -->
        <div style="background: #f8fafc; border: 2px solid var(--border-color); border-radius: 0.5rem; padding: 2rem;">
            <h3 style="margin-top: 0; margin-bottom: 1rem;">Export Data for AI Systems</h3>
            <p style="color: var(--text-light); margin-bottom: 1.5rem;">
                Export your data in standard formats that can be easily read by AI systems and transferred between national systems.
            </p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div class="card">
                    <h4 style="margin-top: 0; display: flex; align-items: center;">
                        <i class="fa-solid fa-code" style="margin-right: 0.75rem; color: var(--primary-color);"></i>
                        JSON Export
                    </h4>
                    <p style="color: var(--text-light); font-size: 0.95rem; margin-bottom: 1rem;">
                        Standard JSON format for easy integration with other systems.
                    </p>
                    <a href="<?php echo url('api/export.php?format=json&type=all'); ?>" class="btn btn-primary" style="width: 100%;">
                        <i class="fa-solid fa-download" style="margin-right: 0.5rem;"></i>Download JSON
                    </a>
                </div>
                
                <div class="card">
                    <h4 style="margin-top: 0; display: flex; align-items: center;">
                        <i class="fa-solid fa-brain" style="margin-right: 0.75rem; color: #9333ea;"></i>
                        JSON-LD Export
                    </h4>
                    <p style="color: var(--text-light); font-size: 0.95rem; margin-bottom: 1rem;">
                        Semantic web format with schema.org vocabulary - optimised for AI systems.
                    </p>
                    <a href="<?php echo url('api/export.php?format=jsonld&type=all'); ?>" class="btn" style="background: #9333ea; color: white; width: 100%;">
                        <i class="fa-solid fa-download" style="margin-right: 0.5rem;"></i>Download JSON-LD
                    </a>
                </div>
                
                <div class="card">
                    <h4 style="margin-top: 0; display: flex; align-items: center;">
                        <i class="fa-solid fa-table" style="margin-right: 0.75rem; color: var(--success-color);"></i>
                        CSV Export
                    </h4>
                    <p style="color: var(--text-light); font-size: 0.95rem; margin-bottom: 1rem;">
                        Comma-separated values for spreadsheet applications and data analysis.
                    </p>
                    <a href="<?php echo url('api/export.php?format=csv&type=all'); ?>" class="btn" style="background: var(--success-color); color: white; width: 100%;">
                        <i class="fa-solid fa-download" style="margin-right: 0.5rem;"></i>Download CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="module">
/* Version: 2024-01-15-fix-v4 - Added type="module" for dynamic imports */
// Web LLM setup using Transformers.js (browser-based AI)
let webAI = null;
let isWebAIAvailable = false;
let webAILoading = false;
let webAIModel = null;

// Initialize Web LLM using Transformers.js
async function initWebAI() {
    console.log('initWebAI called');
    
    // Show immediate feedback
    const btn = document.getElementById('initWebLLMBtn');
    if (!btn) {
        console.error('Button not found!');
        alert('Error: Button not found. Please refresh the page.');
        return;
    }
    
    if (webAILoading) {
        console.log('Already loading...');
        alert('AI model is already loading. Please wait.');
        return;
    }
    
    if (isWebAIAvailable) {
        console.log('Already loaded');
        alert('Browser AI is already loaded and ready to use!');
        return;
    }
    
    webAILoading = true;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right: 0.5rem;"></i>Loading...';
    console.log('Button updated - starting load');
    
    try {
        console.log('Starting Web AI initialization...');
        
        // Load Transformers.js library dynamically
        // Note: Since we're using type="module", dynamic imports are supported
        console.log('Importing Transformers.js...');
        // Import Transformers.js
        const { pipeline, env } = await import('https://cdn.jsdelivr.net/npm/@xenova/transformers@2.17.2');
        console.log('Transformers.js imported successfully');
        
        // Disable local model files (use CDN)
        env.allowLocalModels = false;
        
        console.log('Loading AI model... This may take a minute on first load.');
        
        // Show loading message
        const messagesContainer = document.getElementById('ai-messages');
        if (messagesContainer) {
            const loadingMsg = document.createElement('div');
            loadingMsg.id = 'web-ai-loading';
            loadingMsg.className = 'ai-message ai-assistant';
            loadingMsg.style.cssText = 'margin-bottom: 1rem; padding: 1rem; background: #dbeafe; border-left: 4px solid var(--primary-color); border-radius: 0.5rem;';
            loadingMsg.innerHTML = '<div style="display: flex; align-items: start; gap: 0.75rem;">' +
                '<i class="fa-solid fa-spinner fa-spin" style="color: var(--primary-color); font-size: 1.5rem; flex-shrink: 0;"></i>' +
                '<div style="flex: 1;">' +
                '<strong>Loading Browser AI...</strong>' +
                '<p style="margin: 0.5rem 0 0 0; color: var(--text-color); font-size: 0.9rem;">' +
                'Downloading and initializing AI model. This only happens once and may take 1-2 minutes. The model will run entirely in your browser - no data sent to external servers!' +
                '</p>' +
                '</div>' +
                '</div>';
            messagesContainer.appendChild(loadingMsg);
        }
        
        // Load a small, fast text generation model
        // Try multiple models in order of preference (fallback if one fails)
        let modelLoaded = false;
        const modelsToTry = [
            'Xenova/gpt2', // Small, publicly accessible (~50MB)
            'Xenova/distilgpt2' // Even smaller fallback (~35MB)
        ];
        
        for (let i = 0; i < modelsToTry.length && !modelLoaded; i++) {
            try {
                console.log('Trying model: ' + modelsToTry[i]);
                webAIModel = await pipeline(
                    'text-generation',
                    modelsToTry[i],
                    {
                        progress_callback: (progress) => {
                            if (progress.status == 'progress' && progress.file) {
                                const percent = progress.progress || 0;
                                console.log('Loading: ' + (percent * 100).toFixed(1) + '%');
                            }
                        }
                    }
                );
                modelLoaded = true;
                console.log('Successfully loaded model: ' + modelsToTry[i]);
            } catch (modelError) {
                console.warn('Failed to load model ' + modelsToTry[i] + ':', modelError.message);
                if (i === modelsToTry.length - 1) {
                    // Last model failed, rethrow the error
                    throw modelError;
                }
            }
        }
        
        webAI = webAIModel;
        isWebAIAvailable = true;
        webAILoading = false;
        console.log('Web AI initialized successfully');
        
        // Remove loading message and show success
        const loadingMsg = document.getElementById('web-ai-loading');
        if (loadingMsg) loadingMsg.remove();
        
        if (messagesContainer) {
            const notification = document.createElement('div');
            notification.className = 'ai-message ai-assistant';
            notification.style.cssText = 'margin-bottom: 1rem; padding: 1rem; background: #d1fae5; border-left: 4px solid #10b981; border-radius: 0.5rem;';
            notification.innerHTML = '<div style="display: flex; align-items: start; gap: 0.75rem;">' +
                '<i class="fa-solid fa-check-circle" style="color: #10b981; font-size: 1.5rem; flex-shrink: 0;"></i>' +
                '<div style="flex: 1;">' +
                '<strong>Browser AI Ready</strong>' +
                '<p style="margin: 0.5rem 0 0 0; color: var(--text-color); font-size: 0.9rem;">' +
                'AI is now running locally in your browser! Your queries will be processed completely privately - no data sent to external servers.' +
                '</p>' +
                '</div>' +
                '</div>';
            messagesContainer.appendChild(notification);
        }
        
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check-circle" style="margin-right: 0.5rem;"></i>AI Loaded';
            btn.style.background = '#10b981';
            btn.style.color = 'white';
        }
        
    } catch (error) {
        console.error('Web AI initialization error:', error);
        console.error('Error stack:', error.stack);
        webAILoading = false;
        isWebAIAvailable = false;
        
        // Remove loading message
        const loadingMsg = document.getElementById('web-ai-loading');
        if (loadingMsg) loadingMsg.remove();
        
        // Show error notification
        const messagesContainer = document.getElementById('ai-messages');
        if (messagesContainer) {
            const notification = document.createElement('div');
            notification.className = 'ai-message ai-assistant';
            notification.style.cssText = 'margin-bottom: 1rem; padding: 1rem; background: #fee2e2; border-left: 4px solid #ef4444; border-radius: 0.5rem;';
            const errorMsg = error.message || 'Unknown error';
            notification.innerHTML = '<div style="display: flex; align-items: start; gap: 0.75rem;">' +
                '<i class="fa-solid fa-exclamation-triangle" style="color: #ef4444; font-size: 1.5rem; flex-shrink: 0;"></i>' +
                '<div style="flex: 1;">' +
                '<strong>Browser AI Not Available</strong>' +
                '<p style="margin: 0.5rem 0 0 0; color: var(--text-color); font-size: 0.9rem;">' +
                'Could not load browser AI. Using pattern matching instead.<br>' +
                '<strong>Error:</strong> ' + errorMsg + '<br>' +
                '<small>Check the browser console (F12) for more details. Make sure you\'re using a modern browser (Chrome, Firefox, Edge, Safari).</small>' +
                '</p>' +
                '</div>' +
                '</div>';
            messagesContainer.appendChild(notification);
        }
        
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-download" style="margin-right: 0.5rem;"></i>Load Browser AI';
        }
    }
}

// Make function globally available (do this immediately)
if (typeof window !== 'undefined') {
    window.initWebAI = initWebAI;
    console.log('initWebAI function registered globally');
}

// AI query handler with Web AI support
async function handleAIQuery() {
    const input = document.getElementById('ai-query-input');
    const query = input.value.trim();
    
    if (!query) return;
    
    const messagesContainer = document.getElementById('ai-messages');
    
    // Add user message
    addMessage('user', query);
    input.value = '';
    
    // Show thinking indicator
    const thinkingId = addMessage('assistant', 'Thinking...', true);
    
    try {
        // Call API to get data context
        <?php $apiUrl = url('api/ai-assistant.php'); ?>
        const apiUrl = <?php echo json_encode($apiUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ query: query })
        });
        
        const data = await response.json();
        
        // Remove thinking message
        const thinkingElement = document.getElementById(thinkingId);
        if (thinkingElement) {
            thinkingElement.remove();
        }
        
        // Process query based on AI method
        let answer;
        
        // Check if server already provided an AI response (external APIs)
        if (data.ai_response) {
            answer = data.ai_response;
        } else {
            // Always use Web LLM if available, regardless of server response
            var useWebLLM = false;
            if (isWebAIAvailable && webAI) {
                useWebLLM = true;
                console.log('Using Web LLM (browser AI)');
            } else {
                console.log('Web LLM not available, using pattern matching. isWebAIAvailable:', isWebAIAvailable, 'webAI:', !!webAI);
            }
            if (useWebLLM) {
            // Use Web LLM (Transformers.js) for more sophisticated processing
            try {
                // Wait for Web LLM to be ready if it's still loading
                if (webAILoading) {
                    addMessage('assistant', 'Loading AI model... Please wait. This may take a minute on first use.', true);
                    while (webAILoading && !isWebAIAvailable) {
                        await new Promise(resolve => setTimeout(resolve, 500));
                    }
                }
                
                if (isWebAIAvailable && webAI) {
                    // For GPT-2, use a simpler prompt format with data context
                    const orgName = data.organisation_name || 'an organisation';
                    
                    // Build a prompt with actual data context
                    let contextInfo = '';
                    if (data.summary && data.summary.contracts) {
                        const contracts = data.summary.contracts;
                        contextInfo = 'You have ' + (contracts.total || 0) + ' contracts. ';
                        contextInfo += (contracts.active || 0) + ' are active, ' + (contracts.inactive || 0) + ' are inactive. ';
                        if (contracts.total_value) {
                            contextInfo += 'Total value: £' + contracts.total_value.toFixed(2) + '. ';
                        }
                    }
                    if (data.summary && data.summary.people) {
                        contextInfo += 'You have ' + (data.summary.people.total || 0) + ' people in the system. ';
                    }
                    
                    // Create a simple completion prompt with context
                    const prompt = 'Context: ' + contextInfo + '\n\nQuestion: ' + query + '\n\nAnswer: ';
                    console.log('Web LLM prompt:', prompt);
                    
                    // Use Transformers.js to generate response
                    const result = await webAI(prompt, {
                        max_new_tokens: 150,
                        temperature: 0.8,
                        do_sample: true,
                        top_p: 0.9,
                        repetition_penalty: 1.2,
                        no_repeat_ngram_size: 3
                    });
                    
                    // Extract generated text
                    let rawAnswer = result[0].generated_text || 'No response generated';
                    console.log('Web LLM raw response:', rawAnswer);
                    
                    // Remove the original prompt from the response
                    if (rawAnswer.startsWith(prompt)) {
                        rawAnswer = rawAnswer.substring(prompt.length).trim();
                    }
                    
                    // Clean up the response (but be less aggressive)
                    answer = cleanAIResponse(rawAnswer, query, data);
                    console.log('Web LLM cleaned response:', answer);
                } else {
                    // Web LLM not available, fallback to pattern matching
                    answer = processQuery(query, data);
                }
            } catch (aiError) {
                console.error('Web LLM error:', aiError);
                // Fallback to pattern matching
                answer = processQuery(query, data);
                answer = 'Browser AI error: ' + aiError.message + '\n\nFalling back to pattern matching:\n\n' + answer;
            }
            } else {
                // Use pattern matching with enhanced summary data
                answer = processQuery(query, data);
            }
        }
        
        // Show error if there was one
        if (data.error) {
            answer = 'Error: ' + data.error + '\n\nFalling back to pattern matching:\n\n' + answer;
        }
        
        addMessage('assistant', answer);
        
    } catch (error) {
        const thinkingElement = document.getElementById(thinkingId);
        if (thinkingElement) {
            thinkingElement.remove();
        }
        addMessage('assistant', 'Sorry, I encountered an error. Please try again or use the export options below.');
        console.error('AI Query Error:', error);
    }
}

function cleanAIResponse(rawText, query, data) {
    // Remove repetitive phrases
    let cleaned = rawText;
    
    // Remove common repetitive patterns
    const repetitivePatterns = [
        /Please note that some of the responses may not be accurate\.\s*/gi,
        /If you have any specific questions, please let me know and I'll try to help\.\s*/gi,
        /Your responses\.\s*/gi,
        /A question on (how to fill out|the use of|the type of|a method of filling out) the data\.\s*/gi,
        /If the data doesn't contain enough information, say so\.\s*/gi
    ];
    
    repetitivePatterns.forEach(function(pattern) {
        cleaned = cleaned.replace(pattern, '');
    });
    
    // Remove duplicate sentences
    const sentences = cleaned.split(/[.!?]\s+/).filter(function(s) {
        return s.trim().length > 0;
    });
    const uniqueSentences = [];
    const seen = new Set();
    
    sentences.forEach(function(sentence) {
        const normalized = sentence.trim().toLowerCase();
        if (!seen.has(normalized) && sentence.trim().length > 10) {
            seen.add(normalized);
            uniqueSentences.push(sentence.trim());
        }
    });
    
    cleaned = uniqueSentences.join('. ') + (uniqueSentences.length > 0 ? '.' : '');
    
    // If the cleaned response is too short or seems nonsensical, fall back to pattern matching
    // But be less aggressive - only fall back if it's really bad
    if (cleaned.length < 10 || (cleaned.split(' ').length < 3 && cleaned.length < 30)) {
        console.log('Web LLM response too short, falling back to pattern matching');
        return processQuery(query, data);
    }
    
    // Limit response length
    if (cleaned.length > 500) {
        cleaned = cleaned.substring(0, 500) + '...';
    }
    
    return cleaned.trim() || processQuery(query, data);
}

function processQuery(query, data) {
    const lowerQuery = query.toLowerCase();
    const context = data.context || {};
    const summary = data.summary || {};
    const isGeneralQuery = data.is_general_query || false;
    const orgName = data.organisation_name || 'Your Organisation';
    
    // Handle general queries like "Tell me about our contracts"
    if (isGeneralQuery && lowerQuery.includes('contract')) {
        const contractSummary = summary.contracts || {};
        let response = 'Here\'s an overview of ' + orgName + '\'s contracts:\n\n';
        
        response += '**Summary:**\n';
        response += '• Total Contracts: ' + (contractSummary.total || 0) + '\n';
        response += '• Active: ' + (contractSummary.active || 0) + '\n';
        response += '• Inactive: ' + (contractSummary.inactive || 0) + '\n';
        response += '• Total Value: £' + (contractSummary.total_value || 0).toFixed(2) + '\n\n';
        
        if (contractSummary.expiring_soon_count > 0) {
            response += '**Expiring Soon (next 3 months):** ' + contractSummary.expiring_soon_count + '\n';
            if (contractSummary.expiring_soon && contractSummary.expiring_soon.length > 0) {
                response += '\nUpcoming expirations:\n';
                contractSummary.expiring_soon.forEach(function(c) {
                    response += '• ' + c.title + ' - ' + c.end_date + ' (' + c.local_authority + ')\n';
                });
            }
            response += '\n';
        }
        
        if (contractSummary.by_local_authority && Object.keys(contractSummary.by_local_authority).length > 0) {
            response += '**By Local Authority:**\n';
            const sortedLA = Object.entries(contractSummary.by_local_authority)
                .sort(function(a, b) { return b[1].count - a[1].count; })
                .slice(0, 5);
            sortedLA.forEach(function(item) {
                const la = item[0];
                const laData = item[1];
                response += '• ' + la + ': ' + laData.count + ' contract' + (laData.count !== 1 ? 's' : '') + ' (Value: £' + laData.value.toFixed(2) + ')\n';
            });
        }
        
        return response;
    }
    
    // Pattern matching for specific queries
    if (lowerQuery.includes('active contract') || lowerQuery.includes('how many contract')) {
        const count = summary.contracts?.active || context.contracts?.filter(c => {
            const status = c.status || '';
            const endDate = c.end_date ? new Date(c.end_date) : null;
            const today = new Date();
            return status == 'active' && (!endDate || endDate >= today);
        }).length || 0;
        return 'You have ' + count + ' active contract' + (count !== 1 ? 's' : '') + '.';
    }
    
    if (lowerQuery.includes('expiring') || lowerQuery.includes('ending soon')) {
        const expiring = summary.contracts?.expiring_soon || [];
        
        if (expiring.length == 0) {
            return 'No contracts are expiring in the next 3 months.';
        }
        
        let response = 'You have ' + expiring.length + ' contract' + (expiring.length !== 1 ? 's' : '') + ' expiring soon:\n\n';
        expiring.forEach(function(c) {
            response += '• ' + c.title + ' - Ends ' + c.end_date + ' (' + c.local_authority + ')\n';
        });
        return response;
    }
    
    if (lowerQuery.includes('payment') || lowerQuery.includes('income') || lowerQuery.includes('revenue')) {
        const paymentSummary = summary.payments || {};
        if (paymentSummary.total_count == 0) {
            return 'No payment data available.';
        }
        
        return 'Total payments: £' + (paymentSummary.total_amount || 0).toFixed(2) + ' (from ' + paymentSummary.total_count + ' payment record' + (paymentSummary.total_count !== 1 ? 's' : '') + ')';
    }
    
    if (lowerQuery.includes('total value') || lowerQuery.includes('contract value')) {
        const total = summary.contracts?.total_value || 0;
        return 'Total contract value: £' + total.toFixed(2);
    }
    
    // Default response with summary if available
    if (isGeneralQuery && Object.keys(summary).length > 0) {
        let response = 'Here\'s what I found about ' + orgName + ':\n\n';
        
        if (summary.contracts) {
            response += '**Contracts:** ' + (summary.contracts.total || 0) + ' total (' + (summary.contracts.active || 0) + ' active, ' + (summary.contracts.inactive || 0) + ' inactive)\n';
            response += 'Total value: £' + (summary.contracts.total_value || 0).toFixed(2) + '\n\n';
        }
        
        if (summary.payments) {
            response += '**Payments:** £' + (summary.payments.total_amount || 0).toFixed(2) + ' from ' + (summary.payments.total_count || 0) + ' records\n\n';
        }
        
        if (summary.people) {
            response += '**People:** ' + (summary.people.total || 0) + ' total\n\n';
        }
        
        response += 'For more detailed information, try asking specific questions or use the export options below.';
        return response;
    }
    
    // Default response
    return 'I found some data related to your query. For more detailed analysis, please use the export options below to download your data in JSON, JSON-LD, or CSV format.';
}

function addMessage(role, content, isThinking = false) {
    const messagesContainer = document.getElementById('ai-messages');
    const messageId = 'msg-' + Date.now();
    
    const messageDiv = document.createElement('div');
    messageDiv.id = messageId;
    messageDiv.className = 'ai-message ai-' + role;
    messageDiv.style.cssText = 'margin-bottom: 1rem; padding: 1rem; border-radius: 0.5rem; ' + 
        (role == 'user' 
            ? 'background: #dbeafe; border-left: 4px solid var(--primary-color); margin-left: 2rem;' 
            : 'background: #e0f2fe; border-left: 4px solid var(--primary-color);');
    
    if (role == 'user') {
        messageDiv.innerHTML = '<div style="display: flex; align-items: start; gap: 0.75rem;">' +
            '<i class="fa-solid fa-user" style="color: var(--primary-color); font-size: 1.5rem; flex-shrink: 0;"></i>' +
            '<div style="flex: 1;">' +
            '<strong>You</strong>' +
            '<p style="margin: 0.5rem 0 0 0; color: var(--text-color); white-space: pre-wrap;">' + escapeHtml(content) + '</p>' +
            '</div>' +
            '</div>';
    } else {
        // Process content to render HTML icons while escaping other HTML
        let processedContent = escapeHtml(content);
        
        // Replace markdown-style bold headers with icons and HTML
        processedContent = processedContent.replace(/\*\*Summary:\*\*/g, '<i class="fa-solid fa-chart-line" style="margin-right: 0.5rem; color: var(--primary-color);"></i><strong>Summary:</strong>');
        processedContent = processedContent.replace(/\*\*Expiring Soon \(next 3 months\):\*\*/g, '<i class="fa-solid fa-exclamation-triangle" style="margin-right: 0.5rem; color: #f59e0b;"></i><strong>Expiring Soon (next 3 months):</strong>');
        processedContent = processedContent.replace(/\*\*By Local Authority:\*\*/g, '<i class="fa-solid fa-map-marker-alt" style="margin-right: 0.5rem; color: var(--primary-color);"></i><strong>By Local Authority:</strong>');
        
        messageDiv.innerHTML = '<div style="display: flex; align-items: start; gap: 0.75rem;">' +
            '<i class="fa-solid fa-robot" style="color: var(--primary-color); font-size: 1.5rem; flex-shrink: 0;"></i>' +
            '<div style="flex: 1;">' +
            '<strong>AI Assistant</strong>' +
            '<div style="margin: 0.5rem 0 0 0; color: var(--text-color); white-space: pre-wrap;">' + processedContent + '</div>' +
            '</div>' +
            '</div>';
    }
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    
    return messageId;
}

function setQuickQuery(query) {
    document.getElementById('ai-query-input').value = query;
    document.getElementById('ai-query-input').focus();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Set up event listeners when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Load Browser AI button
    const initBtn = document.getElementById('initWebLLMBtn');
    if (initBtn) {
        initBtn.addEventListener('click', function() {
            console.log('Button clicked!');
            initWebAI();
        });
    }
    
    // Query submit button
    const submitBtn = document.getElementById('ai-query-submit');
    if (submitBtn) {
        submitBtn.addEventListener('click', handleAIQuery);
    }
    
    // Enter key on input
    const queryInput = document.getElementById('ai-query-input');
    if (queryInput) {
        queryInput.addEventListener('keypress', function(event) {
            if (event.key == 'Enter') {
                handleAIQuery();
            }
        });
    }
    
    // Quick action buttons
    const quickActionBtns = document.querySelectorAll('.quick-action-btn');
    quickActionBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const query = this.getAttribute('data-query');
            if (query) {
                setQuickQuery(query);
            }
        });
    });
});
</script>

<?php include INCLUDES_PATH . '/footer.php'; ?>

