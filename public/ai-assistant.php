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
        <h1>AI Assistant</h1>
        <p style="color: var(--text-light); margin-top: 0.5rem;">
            Ask questions about your contracts, payments, and data in natural language
        </p>
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
                    onkeypress="if(event.key === 'Enter') handleAIQuery()"
                >
                <button 
                    onclick="handleAIQuery()" 
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
                <button onclick="setQuickQuery('Tell me about our contracts')" class="btn btn-primary" style="text-align: left; padding: 1rem;">
                    <i class="fa-solid fa-chart-line" style="margin-right: 0.5rem;"></i>
                    Contract Overview
                </button>
                <button onclick="setQuickQuery('How many active contracts do we have?')" class="btn btn-secondary" style="text-align: left; padding: 1rem;">
                    <i class="fa-solid fa-file-contract" style="margin-right: 0.5rem;"></i>
                    Active Contracts
                </button>
                <button onclick="setQuickQuery('What contracts are expiring soon?')" class="btn btn-secondary" style="text-align: left; padding: 1rem;">
                    <i class="fa-solid fa-calendar-exclamation" style="margin-right: 0.5rem;"></i>
                    Expiring Contracts
                </button>
                <button onclick="setQuickQuery('What is our total contract value?')" class="btn btn-secondary" style="text-align: left; padding: 1rem;">
                    <i class="fa-solid fa-pound-sign" style="margin-right: 0.5rem;"></i>
                    Total Value
                </button>
                <button onclick="setQuickQuery('Show me recent payments')" class="btn btn-secondary" style="text-align: left; padding: 1rem;">
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

<script>
// Check if Web AI is available
let webAI = null;
let isWebAIAvailable = false;

// Try to load Web LLM if available
async function initWebAI() {
    try {
        // Check if Web LLM is available (via CDN or local)
        if (typeof window.llm !== 'undefined') {
            webAI = window.llm;
            isWebAIAvailable = true;
            console.log('Web AI available');
        } else {
            // Try to load from CDN
            console.log('Web AI not available - using pattern matching');
        }
    } catch (error) {
        console.log('Web AI not available:', error);
    }
}

// Initialize on page load
initWebAI();

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
        const response = await fetch('<?php echo url('api/ai-assistant.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ query: query })
        });
        
        const data = await response.json();
        
        // Remove thinking message
        document.getElementById(thinkingId)?.remove();
        
        // Process query
        let answer;
        
        if (isWebAIAvailable && webAI) {
            // Use Web AI for more sophisticated processing
            try {
                const summaryText = data.summary ? `\n\nSummary Statistics:\n${JSON.stringify(data.summary, null, 2)}` : '';
                const prompt = `You are an AI assistant helping with social care contract management for ${data.organisation_name || 'an organisation'}.

User question: ${query}
${summaryText}

Available data context (sample):
${JSON.stringify(data.context, null, 2)}

Please provide a helpful, conversational answer based on the data context and summary. Be specific with numbers and dates. If the data doesn't contain enough information, suggest using the export options. Format your response in plain text with clear sections.`;

                answer = await webAI.generate(prompt);
            } catch (aiError) {
                console.error('Web AI error:', aiError);
                // Fallback to pattern matching
                answer = processQuery(query, data);
            }
        } else {
            // Use pattern matching with enhanced summary data
            answer = processQuery(query, data);
        }
        
        addMessage('assistant', answer);
        
    } catch (error) {
        document.getElementById(thinkingId)?.remove();
        addMessage('assistant', 'Sorry, I encountered an error. Please try again or use the export options below.');
        console.error('AI Query Error:', error);
    }
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
        let response = `Here's an overview of ${orgName}'s contracts:\n\n`;
        
        response += `📊 **Summary:**\n`;
        response += `• Total Contracts: ${contractSummary.total || 0}\n`;
        response += `• Active: ${contractSummary.active || 0}\n`;
        response += `• Inactive: ${contractSummary.inactive || 0}\n`;
        response += `• Total Value: £${(contractSummary.total_value || 0).toFixed(2)}\n\n`;
        
        if (contractSummary.expiring_soon_count > 0) {
            response += `⚠️ **Expiring Soon (next 3 months):** ${contractSummary.expiring_soon_count}\n`;
            if (contractSummary.expiring_soon && contractSummary.expiring_soon.length > 0) {
                response += `\nUpcoming expirations:\n`;
                contractSummary.expiring_soon.forEach(c => {
                    response += `• ${c.title} - ${c.end_date} (${c.local_authority})\n`;
                });
            }
            response += `\n`;
        }
        
        if (contractSummary.by_local_authority && Object.keys(contractSummary.by_local_authority).length > 0) {
            response += `📍 **By Local Authority:**\n`;
            const sortedLA = Object.entries(contractSummary.by_local_authority)
                .sort((a, b) => b[1].count - a[1].count)
                .slice(0, 5);
            sortedLA.forEach(([la, data]) => {
                response += `• ${la}: ${data.count} contract${data.count !== 1 ? 's' : ''} (Value: £${data.value.toFixed(2)})\n`;
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
            return status === 'active' && (!endDate || endDate >= today);
        }).length || 0;
        return `You have ${count} active contract${count !== 1 ? 's' : ''}.`;
    }
    
    if (lowerQuery.includes('expiring') || lowerQuery.includes('ending soon')) {
        const expiring = summary.contracts?.expiring_soon || [];
        
        if (expiring.length === 0) {
            return 'No contracts are expiring in the next 3 months.';
        }
        
        let response = `You have ${expiring.length} contract${expiring.length !== 1 ? 's' : ''} expiring soon:\n\n`;
        expiring.forEach(c => {
            response += `• ${c.title} - Ends ${c.end_date} (${c.local_authority})\n`;
        });
        return response;
    }
    
    if (lowerQuery.includes('payment') || lowerQuery.includes('income') || lowerQuery.includes('revenue')) {
        const paymentSummary = summary.payments || {};
        if (paymentSummary.total_count === 0) {
            return 'No payment data available.';
        }
        
        return `Total payments: £${(paymentSummary.total_amount || 0).toFixed(2)} (from ${paymentSummary.total_count} payment record${paymentSummary.total_count !== 1 ? 's' : ''})`;
    }
    
    if (lowerQuery.includes('total value') || lowerQuery.includes('contract value')) {
        const total = summary.contracts?.total_value || 0;
        return `Total contract value: £${total.toFixed(2)}`;
    }
    
    // Default response with summary if available
    if (isGeneralQuery && Object.keys(summary).length > 0) {
        let response = `Here's what I found about ${orgName}:\n\n`;
        
        if (summary.contracts) {
            response += `**Contracts:** ${summary.contracts.total || 0} total (${summary.contracts.active || 0} active, ${summary.contracts.inactive || 0} inactive)\n`;
            response += `Total value: £${(summary.contracts.total_value || 0).toFixed(2)}\n\n`;
        }
        
        if (summary.payments) {
            response += `**Payments:** £${(summary.payments.total_amount || 0).toFixed(2)} from ${summary.payments.total_count || 0} records\n\n`;
        }
        
        if (summary.people) {
            response += `**People:** ${summary.people.total || 0} total\n\n`;
        }
        
        response += `For more detailed information, try asking specific questions or use the export options below.`;
        return response;
    }
    
    // Default response
    return `I found some data related to your query. For more detailed analysis, please use the export options below to download your data in JSON, JSON-LD, or CSV format.`;
}

function addMessage(role, content, isThinking = false) {
    const messagesContainer = document.getElementById('ai-messages');
    const messageId = 'msg-' + Date.now();
    
    const messageDiv = document.createElement('div');
    messageDiv.id = messageId;
    messageDiv.className = 'ai-message ai-' + role;
    messageDiv.style.cssText = 'margin-bottom: 1rem; padding: 1rem; border-radius: 0.5rem; ' + 
        (role === 'user' 
            ? 'background: #dbeafe; border-left: 4px solid var(--primary-color); margin-left: 2rem;' 
            : 'background: #e0f2fe; border-left: 4px solid var(--primary-color);');
    
    if (role === 'user') {
        messageDiv.innerHTML = `
            <div style="display: flex; align-items: start; gap: 0.75rem;">
                <i class="fa-solid fa-user" style="color: var(--primary-color); font-size: 1.5rem; flex-shrink: 0;"></i>
                <div style="flex: 1;">
                    <strong>You</strong>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-color); white-space: pre-wrap;">${escapeHtml(content)}</p>
                </div>
            </div>
        `;
    } else {
        messageDiv.innerHTML = `
            <div style="display: flex; align-items: start; gap: 0.75rem;">
                <i class="fa-solid fa-robot" style="color: var(--primary-color); font-size: 1.5rem; flex-shrink: 0;"></i>
                <div style="flex: 1;">
                    <strong>AI Assistant</strong>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-color); white-space: pre-wrap;">${escapeHtml(content)}</p>
                </div>
            </div>
        `;
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
</script>

<?php include INCLUDES_PATH . '/footer.php'; ?>

