# Deployment Guide

## Pre-Deployment Checklist

- [ ] Database schema imported
- [ ] All migration files run (see `PRODUCTION_CHECKLIST.md`)
- [ ] `.env` file created with `APP_ENV=production`
- [ ] Configuration files updated with production credentials
- [ ] Error display automatically disabled (via `APP_ENV=production`)
- [ ] HTTPS enabled (secure cookies auto-enabled in production)
- [ ] `.htaccess` configured
- [ ] Initial superadmin user created
- [ ] Setup scripts deleted (`create_superadmin.php`, `generate_password.php`)
- [ ] File permissions set correctly (folders: 755, files: 644)

## Hostinger Deployment Steps

### 1. Database Setup

1. Log in to Hostinger control panel
2. Navigate to phpMyAdmin
3. Create a new database (note the database name, username, and password)
4. Import the schema:
   - Select your database
   - Click "Import" tab
   - Choose `sql/schema.sql`
   - Click "Go"

### 2. File Upload

1. Connect via FTP/SFTP to your hosting account
2. Upload all files to your public_html directory (or appropriate directory)
3. Ensure file permissions are correct (folders: 755, files: 644)

### 3. Configuration

1. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```

2. Edit `.env` file with production credentials:
   ```env
   DB_HOST=localhost
   DB_NAME=your_database_name
   DB_USER=your_database_user
   DB_PASS=your_database_password
   DB_CHARSET=utf8mb4
   APP_URL=https://yourdomain.com
   ```

3. Edit `config/config.php` for production settings:
   ```php
   ini_set('display_errors', 0); // Disable error display
   ini_set('session.cookie_secure', 1); // Enable if HTTPS is available
   ```

**Important:** Never commit the `.env` file to version control - it's already in `.gitignore`

### 4. Configure Environment

1. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```

2. Edit `.env` and set:
   ```env
   APP_ENV=production
   APP_URL=https://yourdomain.com
   DB_HOST=your_db_host
   DB_NAME=your_db_name
   DB_USER=your_db_user
   DB_PASS=your_db_password
   ```

3. **IMPORTANT**: The system automatically:
   - Disables error display when `APP_ENV=production`
   - Enables secure cookies when `APP_ENV=production`
   - Logs errors instead of displaying them

### 5. Create Superadmin

**Option 1: Use create_superadmin.php (then DELETE it)**
1. Upload `create_superadmin.php` temporarily
2. Access via browser or command line
3. Create your superadmin user
4. **DELETE the file immediately after use**

**Option 2: Use phpMyAdmin or MySQL command line:**
   ```sql
   -- Generate password hash first, then:
   INSERT INTO users (organisation_id, email, password_hash, first_name, last_name, email_verified, is_active)
   VALUES (NULL, 'your-admin@email.com', '<password_hash>', 'Admin', 'User', TRUE, TRUE);
   
   -- Get the user ID from above, then:
   INSERT INTO user_roles (user_id, role_id) 
   VALUES (<user_id>, (SELECT id FROM roles WHERE name = 'superadmin'));
   ```

   To generate password hash, use the `generate_password.php` script (then DELETE it):
   ```bash
   php generate_password.php YourSecurePassword
   ```

### 5. Verify Deployment

1. Access your application: `https://yourdomain.com/public/index.php`
2. Test login with superadmin account
3. Create a test organisation
4. Test registration with organisation domain
5. Verify all features work correctly

## GitHub Setup

### Initial Repository Setup

1. Create a new repository on GitHub
2. Initialize git (if not already):
   ```bash
   git init
   git add .
   git commit -m "Initial commit"
   git remote add origin <your-github-repo-url>
   git push -u origin main
   ```

### Ongoing Deployment

1. Make changes locally
2. Commit and push:
   ```bash
   git add .
   git commit -m "Description of changes"
   git push
   ```
3. Pull changes on server (via SSH or Hostinger File Manager)
4. Or set up automated deployment via Hostinger Git integration

## Post-Deployment

- Monitor error logs
- Set up backups for database
- Configure SSL certificate (if not already)
- Test all user roles and permissions
- Document any custom configurations
