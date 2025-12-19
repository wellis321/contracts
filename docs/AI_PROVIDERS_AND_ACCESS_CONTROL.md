# AI Providers and Access Control

## Overview

The AI Assistant system is designed with multiple provider options, free alternatives, and strict access control to ensure data privacy and security.

## Available AI Providers

### Free Options

1. **Pattern Matching (Built-in)** - Default
   - No API keys required
   - Runs entirely locally
   - Simple keyword-based responses
   - **Privacy**: 100% local, no data leaves your server

2. **Web LLM (Browser-based)**
   - No API keys required
   - Runs entirely in the user's browser
   - Requires initial model download (~2-4GB)
   - **Privacy**: 100% local, no data sent anywhere
   - **Setup**: Add Web LLM library to frontend

3. **Hugging Face Inference API** ⭐ Free Tier
   - Free API key from Hugging Face
   - Generous free tier limits
   - Multiple free models available
   - **Privacy**: Data sent to Hugging Face servers
   - **Get API Key**: https://huggingface.co/settings/tokens

4. **Google Gemini** ⭐ Free Tier
   - Free API key from Google
   - Generous free tier with good limits
   - High-quality responses
   - **Privacy**: Data sent to Google servers
   - **Get API Key**: https://makersuite.google.com/app/apikey

5. **Ollama (Local)**
   - Completely free and local
   - Runs on user's computer
   - Requires Ollama installation
   - **Privacy**: 100% local, no data sent anywhere
   - **Setup**: Install from https://ollama.ai

### Paid Options

6. **OpenAI GPT**
   - High-quality responses
   - Multiple model options (GPT-4, GPT-4o-mini, GPT-3.5)
   - **Privacy**: Data sent to OpenAI servers
   - **Cost**: Pay-per-use
   - **Get API Key**: https://platform.openai.com/api-keys

7. **Anthropic Claude**
   - Excellent for long contexts
   - Multiple model options (Opus, Sonnet, Haiku)
   - **Privacy**: Data sent to Anthropic servers
   - **Cost**: Pay-per-use
   - **Get API Key**: https://console.anthropic.com/

## Access Control

### How It Works

The AI system automatically filters data based on:

1. **Organisation Isolation**
   - Users can only access data from their own organisation
   - Database queries always include `WHERE organisation_id = ?`
   - AI providers only receive data from the user's organisation

2. **Role-Based Access Control (RBAC)**
   - **Admins**: Access to all contracts in their organisation
   - **Team Members**: Only access contracts assigned to their teams
   - **Finance/Senior Managers**: Access to all teams' contracts

3. **Team-Based Filtering**
   - The `AIProvider` service filters contracts by team access
   - Only contracts the user has permission to view are included
   - Payments are filtered by contract team access

### Implementation Details

```php
// In AIProvider::filterDataByAccess()
// If user has access to all teams, return all data
if ($this->accessibleTeamIds === null) {
    return $data;
}

// If user has no team access, return empty
if (empty($this->accessibleTeamIds)) {
    return [];
}

// Filter contracts by team access
$data['contracts'] = array_filter($data['contracts'], function($contract) {
    if (empty($contract['team_id'])) {
        return true; // Null team = accessible to all
    }
    return in_array($contract['team_id'], $this->accessibleTeamIds);
});
```

### Data Flow

1. **User asks question** → Frontend sends query to `/api/ai-assistant.php`
2. **Backend fetches data** → Uses `RBAC::getAccessibleTeamIds()` to get user's team access
3. **Data filtering** → `AIProvider` filters data by organisation and team access
4. **AI processing** → Only filtered data is sent to AI provider (if external)
5. **Response** → AI response is returned to user

### Privacy Settings

Users can control:

- **Send data to external APIs**: Must be explicitly enabled
- **Prefer browser-only AI**: Automatically uses local AI when available
- **API key storage**: Keys are stored per-user, encrypted (in production)

## Security Considerations

### API Key Storage

- Currently stored in database (should be encrypted in production)
- Each user has their own API keys
- Keys are never exposed to frontend
- Keys are only used server-side

### Data Isolation

- Organisation-level isolation enforced at database level
- Team-level filtering enforced in application layer
- AI providers only receive data user has access to
- No cross-organisation data leakage possible

### Best Practices

1. **For Maximum Privacy**: Use Web LLM or Ollama (local AI)
2. **For Free Tier**: Use Hugging Face or Gemini
3. **For Quality**: Use OpenAI or Anthropic (paid)
4. **For Testing**: Use Pattern Matching (built-in)

## Configuration

Users configure their AI preferences at `/ai-settings.php`:

- Select AI provider
- Enter API keys (if required)
- Choose model (if applicable)
- Set privacy preferences
- Configure local AI settings (Ollama URL, etc.)

## Free AI Model Recommendations

### Hugging Face (Free)
- `mistralai/Mistral-7B-Instruct-v0.2` - Good balance
- `meta-llama/Llama-2-7b-chat-hf` - Popular choice
- `google/flan-t5-large` - Fast responses

### Ollama (Local, Free)
- `llama2` - General purpose
- `mistral` - Good for instructions
- `codellama` - Good for structured data

### Google Gemini (Free Tier)
- `gemini-pro` - Best free option for quality
- Generous rate limits
- Good for contract analysis

## Testing Access Control

To verify access control is working:

1. **Create test users** with different roles
2. **Assign contracts to different teams**
3. **Test AI queries** from each user
4. **Verify** that users only see their accessible data

## Future Enhancements

- API key encryption at rest
- Audit logging of AI queries
- Rate limiting per user
- Cost tracking for paid providers
- Model performance comparison




