# Tender Opportunity Monitoring System

## Overview

The system automatically monitors Public Contracts Scotland (PCS) for new tender opportunities matching your criteria and notifies you when they're found.

## Features

- **Automated Monitoring**: Checks PCS API for new opportunities
- **Customizable Criteria**: Set keywords, local authorities, contract types, CPV codes, and value ranges
- **Multiple Notification Methods**: Email, in-app, or both
- **Auto-Import**: Opportunities are automatically imported into your system
- **Instant Alerts**: Get notified immediately when new opportunities match your criteria

## Setup

### 1. Run the Database Migration

```sql
-- Run the migration file
source sql/migration_tender_monitoring.sql;
```

### 2. Configure Monitoring Preferences

1. Go to **Tender Opportunities** page
2. Click **"Set Up Monitoring"**
3. Configure your monitoring criteria:
   - **Keywords**: e.g., "social care", "supported living"
   - **CPV Codes**: e.g., "85000000" (Health and social work services)
   - **Local Authorities**: Select specific authorities or leave blank for all
   - **Notification Method**: Choose email, in-app, or both
   - **Email Address**: Where to send notifications (optional, uses account email if blank)

### 3. Set Up Automated Checking (Cron Job)

To enable automatic checking, set up a cron job on your server:

```bash
# Check every 6 hours
0 */6 * * * /usr/bin/php /path/to/contracts/scripts/check-tenders.php

# Or check every hour
0 * * * * /usr/bin/php /path/to/contracts/scripts/check-tenders.php

# Or check daily at 9 AM
0 9 * * * /usr/bin/php /path/to/contracts/scripts/check-tenders.php
```

**Note**: Replace `/path/to/contracts` with your actual application path.

### 4. Manual Testing

You can manually trigger a check from the monitoring page:
- Go to **Tender Monitoring** page
- Click **"Check Now"** button

## How It Works

1. **API Monitoring**: The system queries the Public Contracts Scotland API using your criteria
2. **Opportunity Detection**: New opportunities matching your criteria are identified
3. **Auto-Import**: Opportunities are automatically imported into your system
4. **Notification**: You receive notifications via your chosen method(s)
5. **Tracking**: All found opportunities are tracked in the monitoring preferences

## Public Contracts Scotland API

The system uses the official PCS API:
- **Base URL**: `https://api.publiccontractsscotland.gov.uk/v1/Notices`
- **No Authentication Required**: The API is publicly accessible
- **Rate Limits**: Be respectful of API rate limits (checking every 6 hours is recommended)

## CPV Codes (Common Procurement Vocabulary)

Common codes for social care:
- **85000000**: Health and social work services
- **85310000**: Social work services
- **85320000**: Social services
- **85321000**: Administrative social services
- **85322000**: Community action programme

## Notification Methods

### Email Notifications
- Sent immediately when new opportunities are found
- Includes opportunity details and link to view
- Uses your configured email address or account email

### In-App Notifications
- Stored in the `tender_notifications` table
- Can be viewed on the Tender Opportunities page
- Marked as read when viewed

## Troubleshooting

### No Opportunities Found
- Check that your keywords match opportunities on PCS
- Verify CPV codes are correct
- Ensure monitoring preference is set to "Active"
- Check the "Last Checked" timestamp

### API Errors
- Verify the PCS API is accessible from your server
- Check server logs for detailed error messages
- Ensure cURL is enabled on your PHP installation

### Notifications Not Sending
- Verify email address is correct
- Check server mail configuration
- Review PHP error logs for mail errors
- Ensure notification method is set correctly

## Future Enhancements

Potential improvements:
- Microsoft Graph API integration for Teams notifications
- Desktop app for real-time notifications
- Browser extension for PCS integration
- Advanced filtering and matching algorithms
- Integration with other tender sources

