# AI Assistant System - Complete Summary

## What Has Been Built

A comprehensive, configurable AI assistant system with:
- ✅ Multiple AI provider options (7 providers)
- ✅ Free AI options available
- ✅ Strict access control and data isolation
- ✅ User-configurable settings
- ✅ Privacy controls

## Quick Start

1. **Run the migration**:
   ```sql
   source sql/migration_ai_preferences.sql
   ```

2. **Access AI Assistant** (Any logged-in user):
   - Go to `/ai-assistant.php`
   - Click "Settings" to configure your preferred AI provider
   - **Note**: No special role required - all users can configure their own AI preferences

3. **Choose a Free Option**:
   - **Pattern Matching** (default) - No setup required, works immediately
   - **Hugging Face** - Get free API key at https://huggingface.co/settings/tokens
   - **Google Gemini** - Get free API key at https://makersuite.google.com/app/apikey
   - **Ollama** - Install locally from https://ollama.ai

## Files Created

### Database
- `sql/migration_ai_preferences.sql` - User AI preferences table

### Models
- `src/models/AIPreference.php` - Manages user AI preferences

### Services
- `src/services/AIProvider.php` - Handles all AI providers with access control

### Pages
- `public/ai-settings.php` - User settings page for AI configuration
- `public/ai-assistant.php` - Updated to use new AI system

### Documentation
- `docs/AI_PROVIDERS_AND_ACCESS_CONTROL.md` - Detailed provider and security info
- `docs/AI_ASSISTANT_GUIDE.md` - How to extend the AI system

## Access Control Features

### ✅ Organisation Isolation
- Users can only access their own organisation's data
- Database queries always filter by `organisation_id`
- AI providers only receive organisation-specific data

### ✅ Role-Based Access
- **Admins**: See all contracts in organisation
- **Team Members**: Only see contracts for their teams
- **Finance/Senior Managers**: See all teams' contracts

### ✅ Team-Based Filtering
- Contracts filtered by user's team access
- Payments filtered by contract team access
- People filtered by organisation (all team members see same people)

### ✅ Privacy Controls
- Users must explicitly enable external API data sending
- Browser-only AI preferred when available
- API keys stored per-user (encrypted in production)

## Available AI Providers

| Provider | Cost | Privacy | Setup Required |
|----------|------|---------|----------------|
| Pattern Matching | Free | 100% Local | None |
| Web LLM | Free | 100% Local | Add library |
| Hugging Face | Free | External | API Key |
| Google Gemini | Free | External | API Key |
| Ollama | Free | 100% Local | Install locally |
| OpenAI | Paid | External | API Key |
| Anthropic | Paid | External | API Key |

## How Access Control Works

```
User Query
    ↓
Backend API (/api/ai-assistant.php)
    ↓
Get User's Accessible Team IDs (RBAC::getAccessibleTeamIds())
    ↓
Fetch Data (filtered by organisation_id)
    ↓
AIProvider Service
    ↓
Filter Data by Team Access
    ↓
Send to AI Provider (only filtered data)
    ↓
Return Response to User
```

## Security Guarantees

1. **Organisation Isolation**: Database-level filtering ensures no cross-organisation data access
2. **Team Filtering**: Application-level filtering ensures team members only see their data
3. **Explicit Consent**: Users must enable external API data sending
4. **Per-User Keys**: Each user has their own API keys
5. **No Key Exposure**: API keys never sent to frontend

## Configuration Flow

**Access**: Any logged-in user can configure their own AI preferences (no admin role required)

1. User goes to `/ai-settings.php` (accessible to all logged-in users)
2. Selects preferred AI provider
3. Enters API key (if required) - stored per-user
4. Chooses model (if applicable)
5. Sets privacy preferences
6. Saves settings
7. Settings stored in `ai_preferences` table (per-user)
8. AI Assistant uses these settings for all queries from that user

**Important**: Each user has their own AI preferences. Team members and admins can both configure their own settings independently.

## Testing Checklist

- [ ] Run migration: `sql/migration_ai_preferences.sql`
- [ ] Test Pattern Matching (default, no setup)
- [ ] Test with Hugging Face free API key
- [ ] Test with Gemini free API key
- [ ] Test access control with different user roles
- [ ] Verify team members only see their contracts
- [ ] Verify admins see all contracts
- [ ] Test privacy settings (external API toggle)

## Next Steps

1. **Run the migration** to create the `ai_preferences` table
2. **Test with Pattern Matching** (works immediately)
3. **Try a free provider** (Hugging Face or Gemini)
4. **Configure user preferences** at `/ai-settings.php`
5. **Test access control** with different user roles

## Free AI Recommendations

### For Privacy-Conscious Users
- **Ollama** (local, completely private)
- **Web LLM** (browser-based, private)

### For Best Free Quality
- **Google Gemini** (generous free tier)
- **Hugging Face** (multiple free models)

### For No Setup
- **Pattern Matching** (built-in, works immediately)

## Support

See detailed documentation:
- `docs/AI_PROVIDERS_AND_ACCESS_CONTROL.md` - Provider details and security
- `docs/AI_ASSISTANT_GUIDE.md` - How to extend the system

