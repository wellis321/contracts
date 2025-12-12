# Test URLs for Tender Import

## Public Contracts Scotland URL Format

Public Contracts Scotland URLs typically follow these patterns:

1. **Search Results Page:**
   ```
   https://www.publiccontractsscotland.gov.uk/search/show/search_view.aspx?ID=...
   ```

2. **Tender Notice Page:**
   ```
   https://www.publiccontractsscotland.gov.uk/search/show/search_view.aspx?ID=...
   ```

## How to Get a Test URL

1. **Visit Public Contracts Scotland:**
   - Go to: https://www.publiccontractsscotland.gov.uk

2. **Search for Opportunities:**
   - Click "Search" in the top menu
   - Search for: "social care" or "care services" or "supported living"
   - Filter by category: "Health and social work services" (CPV code 85000000)

3. **Select a Tender Notice:**
   - Click on any tender notice from the results
   - Copy the full URL from your browser's address bar
   - It should look something like:
     ```
     https://www.publiccontractsscotland.gov.uk/search/show/search_view.aspx?ID=NOV123456
     ```

4. **Test the Import:**
   - Go to your application: `/tender-opportunities.php`
   - Click "Import from URL"
   - Paste the URL
   - Click "Import Opportunity"

## Example Search URLs

You can use these to find opportunities:

- **Social Care Search:**
  https://www.publiccontractsscotland.gov.uk/search/search_main.aspx?ID=SEARCH&Keywords=social+care

- **Health and Social Work Services:**
  https://www.publiccontractsscotland.gov.uk/search/search_main.aspx?ID=SEARCH&Category=85000000

## Notes

- The import feature works best with individual tender notice pages (not search results)
- Some fields may not be extractable depending on the page structure
- You can always manually complete missing fields after import
- The system will attempt to match local authority names automatically

