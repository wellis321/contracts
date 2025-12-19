# Hostinger Database Migrations Guide

After importing `schema.sql`, you need to run additional migration files to add features like teams, email verification, and other functionality.

## Required Migrations (Run in this order)

Run these migrations in phpMyAdmin on Hostinger:

### 1. Email Verification
**File:** `sql/migration_email_verification.sql`
- Adds `email_verified`, `verification_token`, and `verification_token_expires_at` columns to users table
- **Required for:** User registration and login

### 2. Teams Hierarchy
**File:** `sql/migration_teams_hierarchy.sql`
- Creates `teams`, `team_types`, and `user_teams` tables
- Adds `team_id` to contracts table
- **Required for:** Team-based access control and contract management

### 3. Custom Team Roles
**File:** `sql/migration_custom_team_roles.sql`
- Adds custom role support for teams
- **Required if:** You want to use team roles

### 4. Organisation Profile
**File:** `sql/migration_organisation_profile.sql`
- Adds organisation profile fields for tender applications
- **Required for:** Tender application functionality

### 5. Person Tracking
**File:** `sql/migration_person_tracking.sql`
- Adds person tracking tables and fields
- **Required for:** Tracking people across contracts

### 6. Default Contract Types
**File:** `sql/migration_default_contract_types.sql`
- Creates default contract types
- **Required for:** Contract management

### 7. Payment Frequency
**File:** `sql/migration_payment_frequency.sql`
- Adds payment frequency options
- **Required for:** Payment tracking

### 8. Tender Applications
**File:** `sql/migration_tender_applications.sql`
- Creates tender application tables
- **Required for:** Tender application management

### 9. Tender Opportunities
**File:** `sql/migration_tender_opportunities.sql`
- Creates tender opportunity tracking tables
- **Required for:** Tender monitoring

### 10. Tender Monitoring
**File:** `sql/migration_tender_monitoring.sql`
- Creates tender monitoring configuration tables
- **Required for:** Automated tender monitoring

### 11. Local Authority Rates Info
**File:** `sql/migration_local_authority_rates_info.sql`
- Creates local authority rate information tables
- **Required for:** Rate monitoring and tracking

### 12. Organisation Terminology
**File:** `sql/migration_organisation_terminology.sql`
- Adds custom terminology fields (person/people terms)
- **Optional but recommended**

### 13. Contract Procurement Fields
**File:** `sql/migration_contract_procurement_fields.sql`
- Adds procurement-related fields to contracts
- **Required for:** Full contract management

### 14. Contract Stipulations
**File:** `sql/migration_contract_stipulations.sql`
- Adds contract stipulations table
- **Optional**

### 15. Contract Number Prefix
**File:** `sql/migration_contract_number_prefix.sql`
- Adds contract number prefix support
- **Optional**

### 16. Audit Logs
**File:** `sql/migration_audit_logs.sql`
- Creates audit logging tables
- **Required for:** Change tracking and compliance

### 17. Seat Change Requests
**File:** `sql/migration_seat_change_requests.sql`
- Creates seat change request tables
- **Required for:** Managing organisation seat allocations

### 18. Fix Seat Counting
**File:** `sql/migration_fix_seat_counting.sql`
- Fixes seat counting logic
- **Required if:** You have existing organisations

### 19. Glossary Terms
**File:** `sql/migration_glossary_terms.sql`
- Creates glossary system
- **Optional**

### 20. Glossary Suggestions
**File:** `sql/migration_glossary_suggestions.sql`
- Adds glossary suggestion functionality
- **Optional**

### 21. Rate Warning Dismissals
**File:** `sql/migration_rate_warning_dismissals.sql`
- Adds rate warning dismissal tracking
- **Optional**

### 22. Protect Historical Rates
**File:** `sql/migration_protect_historical_rates.sql`
- Adds triggers to protect historical rate data
- **Optional but recommended**

### 23. AI Preferences
**File:** `sql/migration_ai_preferences.sql`
- Creates AI preferences table
- **Required if:** You want to use the AI assistant feature

## Quick Import Steps

1. **Log in to Hostinger phpMyAdmin**
2. **Select your database** (`u248320297_care_contracts`)
3. **For each migration file:**
   - Click the **"SQL"** tab
   - Click **"Choose File"**
   - Select the migration file from `sql/` folder
   - Click **"Go"**
   - Wait for success message
   - Repeat for next migration

## Alternative: Import All at Once

If you have SSH access, you can run all migrations at once:

```bash
cd /path/to/your/project
for file in sql/migration_*.sql; do
    mysql -u your_user -p your_database < "$file"
done
```

## Minimum Required Migrations

If you want to get the site working quickly, run at minimum:

1. ✅ `migration_email_verification.sql` (for login)
2. ✅ `migration_teams_hierarchy.sql` (for contracts page)
3. ✅ `migration_default_contract_types.sql` (for contracts)
4. ✅ `migration_person_tracking.sql` (for people tracking)
5. ✅ `migration_contract_procurement_fields.sql` (for full contract features)
6. ✅ `migration_audit_logs.sql` (for change tracking)

## Troubleshooting

### "Table already exists" errors
- This means the migration was already run
- You can safely skip it and continue with the next one

### "Column already exists" errors
- The migration uses IF NOT EXISTS checks
- These errors are usually safe to ignore

### Foreign key errors
- Make sure you've run migrations in order
- Some migrations depend on others (e.g., teams needs to be created before contracts can reference it)

## After Running Migrations

1. **Update your superadmin user** (if you created it before running email verification migration):
   ```sql
   UPDATE users 
   SET email_verified = TRUE, is_active = TRUE 
   WHERE organisation_id IS NULL;
   ```

2. **Test the application:**
   - Try logging in
   - Check that pages load without errors
   - Verify you can access the superadmin panel

---

**Note:** The migrations are designed to be safe to run multiple times (they use `IF NOT EXISTS` checks), but it's best to run them in order.

