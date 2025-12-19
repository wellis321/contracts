# Hostinger Database Import Guide

This guide walks you through importing the SQL schema to your Hostinger database.

## Quick Steps

1. **Log in to Hostinger**
   - Go to your Hostinger control panel
   - Navigate to **phpMyAdmin**

2. **Select Your Database**
   - In the left sidebar, click on your database (e.g., `u248320297_care_contracts`)
   - Hostinger database names usually look like: `u[number]_database_name`

3. **Import the Schema**
   - Click the **"Import"** tab at the top
   - Click **"Choose File"** button
   - Select `sql/schema.sql` from your project folder
   - Click **"Go"** at the bottom
   - Wait for the import to complete

4. **Import Required Migrations** ⚠️ **IMPORTANT**
   - After importing `schema.sql`, you **MUST** run migration files
   - The application requires several migrations to work properly
   - **See `HOSTINGER_MIGRATIONS_GUIDE.md` for complete list**
   - **Minimum required migrations:**
     1. `migration_email_verification.sql` (for login)
     2. `migration_teams_hierarchy.sql` (for contracts - **REQUIRED**)
     3. `migration_default_contract_types.sql` (for contracts)
     4. `migration_person_tracking.sql` (for people tracking)
     5. `migration_contract_procurement_fields.sql` (for full features)
     6. `migration_audit_logs.sql` (for change tracking)
   
   **How to import each migration:**
   - Select your database
   - Click **"SQL"** tab
   - Click **"Choose File"**
   - Select the migration file from `sql/` folder
   - Click **"Go"**
   - Repeat for each migration file

## Important Notes

✅ **Database Name Doesn't Matter**
- The `schema.sql` file has database name references commented out
- It will work with any database name (like `u248320297_care_contracts`)
- You don't need to modify the SQL file

✅ **Database Credentials**
- Get your database credentials from Hostinger control panel
- Update your `.env` file with:
  ```env
  DB_HOST=localhost
  DB_NAME=u248320297_care_contracts
  DB_USER=your_hostinger_db_user
  DB_PASS=your_hostinger_db_password
  DB_CHARSET=utf8mb4
  ```

## Troubleshooting

### "Table already exists" errors
- This means the schema was already imported
- You can either:
  - Drop all tables and re-import, OR
  - Skip importing and just run any new migrations

### Import fails or times out
- Try importing in smaller chunks
- Check the file size limit in phpMyAdmin settings
- You can also use command line if you have SSH access:
  ```bash
  mysql -u your_user -p u248320297_care_contracts < sql/schema.sql
  ```

### Can't find phpMyAdmin
- In Hostinger control panel, look for "Databases" section
- Click on your database name
- Look for "phpMyAdmin" link or button

## After Import

1. **Update `.env` file** with your Hostinger database credentials
2. **Test the connection** by accessing your website
3. **Create a superadmin user** (see `LOCAL_SETUP.md` for instructions)

---

**Need Help?** Check the main `DEPLOYMENT.md` file for complete deployment instructions.

