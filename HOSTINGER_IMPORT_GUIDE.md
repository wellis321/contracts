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

4. **Import Migrations (if any)**
   - After importing `schema.sql`, you may need to import migration files
   - Select your database again
   - Click **"Import"** tab
   - Import each migration file in order (they're numbered/dated)

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

