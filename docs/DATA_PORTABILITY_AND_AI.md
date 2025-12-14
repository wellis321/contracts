# Data Portability and AI Integration Guide

## Overview

This system is designed with data portability and AI integration in mind. All data can be exported in standard formats that are easily readable by AI systems and transferable between national systems.

## Data Export Formats

### 1. JSON (JavaScript Object Notation)
**Best for:** General data exchange, API integration, simple AI processing

**Access:** `/api/export.php?format=json&type=all`

**Features:**
- Standard JSON format
- Human-readable
- Easy to parse programmatically
- Compatible with all modern systems

**Example:**
```json
{
  "metadata": {
    "export_date": "2025-01-15T10:30:00+00:00",
    "organisation_name": "Example Organisation",
    "contract_count": 25
  },
  "data": {
    "contracts": [...],
    "payments": [...],
    "people": [...]
  }
}
```

### 2. JSON-LD (JSON for Linking Data)
**Best for:** AI systems, semantic web, knowledge graphs, national system integration

**Access:** `/api/export.php?format=jsonld&type=all`

**Features:**
- Uses schema.org vocabulary
- Semantic annotations for AI understanding
- Linked data format
- Optimised for machine learning and AI processing
- Follows W3C standards

**Example:**
```json
{
  "@context": {
    "@vocab": "https://schema.org/",
    "sccm": "https://socialcarecontracts.scot/vocab#"
  },
  "@type": "Dataset",
  "data": {
    "contracts": [{
      "@type": "sccm:Contract",
      "@id": "contract:123",
      "name": "Contract Title",
      "startDate": "2024-01-01",
      "amount": {
        "@type": "MonetaryAmount",
        "currency": "GBP",
        "value": 50000
      }
    }]
  }
}
```

### 3. CSV (Comma-Separated Values)
**Best for:** Spreadsheet applications, data analysis, simple imports

**Access:** `/api/export.php?format=csv&type=all`

**Features:**
- Universal compatibility
- Easy to import into Excel, Google Sheets, etc.
- Human-readable
- Works with most data analysis tools

## Export Types

You can export specific data types:

- `type=all` - All data (contracts, payments, people, rates)
- `type=contracts` - Contracts only
- `type=payments` - Payments only
- `type=people` - People/clients only
- `type=rates` - Rate information only

## Date Filtering

Add date filters to exports:

- `start_date=2024-01-01` - Filter from this date
- `end_date=2024-12-31` - Filter to this date

**Example:**
```
/api/export.php?format=json&type=contracts&start_date=2024-01-01&end_date=2024-12-31
```

## AI Integration

### Browser-Based AI Assistant

The system includes an AI assistant interface (`/ai-assistant.php`) that allows you to:

- Ask natural language questions about your data
- Get instant answers about contracts, payments, and people
- Export data in AI-friendly formats

**Example Queries:**
- "How many active contracts do we have?"
- "What contracts are expiring in the next 3 months?"
- "Show me total payments this month"
- "What is our total contract value?"

### Web AI Integration

The system is designed to work with browser-based AI (Web LLM) which:

- Runs entirely in your browser (privacy-preserving)
- No API keys required
- No data sent to external servers
- Works offline

### API-Based AI Integration

For more advanced AI capabilities, you can:

1. **Export data** using the API endpoints
2. **Feed to your AI system** (OpenAI, Anthropic, local LLM, etc.)
3. **Process with AI** for insights, analysis, predictions

**Example Workflow:**
```javascript
// 1. Export data
const response = await fetch('/api/export.php?format=jsonld&type=all');
const data = await response.json();

// 2. Send to AI system
const aiResponse = await fetch('https://api.openai.com/v1/chat/completions', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer YOUR_API_KEY',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    model: 'gpt-4',
    messages: [{
      role: 'user',
      content: `Analyze this contract data: ${JSON.stringify(data)}`
    }]
  })
});
```

## Best Practices for Data Portability

### 1. Use Standard Formats
- **JSON** for general data exchange
- **JSON-LD** for AI and semantic web
- **CSV** for spreadsheet applications

### 2. Include Metadata
All exports include:
- Export date (ISO 8601 format)
- Organisation information
- Data type information
- Record counts

### 3. Consistent Data Structure
- All dates in ISO 8601 format (YYYY-MM-DD)
- All amounts as floats with currency
- All IDs as integers
- Null values explicitly set to null

### 4. Semantic Annotations
JSON-LD exports use:
- Schema.org vocabulary for standard concepts
- Custom vocabulary for domain-specific concepts
- Linked data principles for relationships

## Integration with National Systems

### Data Transfer

1. **Export from this system** in JSON-LD format
2. **Validate** against schema (if provided)
3. **Transform** if needed for target system
4. **Import** into national system

### API Access

For automated integration:

```bash
# Get authentication token (if implementing API auth)
curl -X POST https://your-system.com/api/auth \
  -d "email=user@example.com&password=password"

# Export data
curl -X GET "https://your-system.com/api/export.php?format=jsonld&type=all" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## AI System Integration

### For Organisation AI Systems

1. **Regular Exports:** Schedule regular exports (daily/weekly)
2. **Feed to AI:** Import JSON-LD into your AI system
3. **Analysis:** Use AI for insights, predictions, recommendations
4. **Action:** Implement AI recommendations back into system

### For National AI Systems

1. **Standard Format:** Use JSON-LD with schema.org
2. **Anonymisation:** Remove PII if required
3. **Aggregation:** Combine with other organisation data
4. **Analysis:** National-level insights and trends

## Security Considerations

- All exports require authentication
- Data is filtered by organisation (multi-tenant security)
- Team-based access control applies
- Exports include only data user has permission to view
- Consider data anonymisation for external AI systems

## Future Enhancements

- **Real-time API:** WebSocket or Server-Sent Events for live data
- **GraphQL API:** More flexible querying
- **RDF Export:** Full RDF/OWL for advanced semantic systems
- **FHIR Integration:** Healthcare data standards if applicable
- **Automated AI Insights:** Built-in AI analysis and recommendations

## Example Use Cases

### 1. Organisation AI Assistant
Export data → Feed to organisation's AI system → Get insights on contract performance, payment trends, etc.

### 2. National System Integration
Export in JSON-LD → Transform to national format → Import into national system

### 3. Data Analysis
Export CSV → Import into Excel/Python/R → Perform statistical analysis

### 4. Reporting Automation
Export JSON → Feed to reporting tool → Generate automated reports

## Support

For questions about data formats or integration, see the API documentation or contact support.

