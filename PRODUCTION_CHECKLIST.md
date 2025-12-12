# Production Deployment Checklist

## Critical Security Issues - MUST FIX BEFORE PRODUCTION

### ✅ Fixed Issues

1. **Error Reporting** - Now environment-based (disabled in production)
2. **Session Security** - Secure cookies enabled in production
3. **Debug Logging** - Removed from header.php
4. **.gitignore** - Updated to exclude .cursor/ and debug.log

### ⚠️ Manual Steps Required

1. **Set Environment Variable**
   - Add `APP_ENV=production` to your `.env` file on production server
   - This automatically disables error display and enables secure cookies

2. **Delete Setup Scripts** (if not already done)
   - Delete `create_superadmin.php` after creating your superadmin
   - Delete `generate_password.php` after use
   - These should NOT be in production

3. **Configure .env File**
   - Copy `.env.example` to `.env` on production server
   - Update with production values:
     - `APP_ENV=production`
     - `APP_URL=https://yourdomain.com` (use HTTPS)
     - Database credentials
     - Email settings (if using)

4. **HTTPS Configuration**
   - Ensure HTTPS is enabled on your production server
   - The system will automatically use secure cookies when `APP_ENV=production`

5. **File Permissions**
   - Set appropriate file permissions (folders: 755, files: 644)
   - Ensure `.env` file is not publicly accessible (should be outside web root or protected)

6. **Database Migrations**
   - Run all migration files in order:
     - `sql/migration_email_verification.sql`
     - `sql/migration_default_contract_types.sql`
     - `sql/migration_person_tracking.sql`

## Pre-Deployment Verification

- [ ] All migrations run successfully
- [ ] `.env` file configured with production values
- [ ] `APP_ENV=production` set in `.env`
- [ ] HTTPS enabled and working
- [ ] Error display disabled (check by visiting a non-existent page)
- [ ] Secure cookies enabled (check browser dev tools)
- [ ] Setup scripts removed (`create_superadmin.php`, `generate_password.php`)
- [ ] Database credentials are secure and not in code
- [ ] All sensitive files in `.gitignore`

## Post-Deployment Testing

- [ ] Login works correctly
- [ ] Email verification works
- [ ] All pages load without errors
- [ ] No debug information visible in error pages
- [ ] Session cookies are secure (HttpOnly, Secure flags set)
- [ ] Database connections work
- [ ] File uploads (if any) work with correct permissions

## Security Best Practices

1. **Never commit**:
   - `.env` file
   - `create_superadmin.php`
   - `generate_password.php`
   - Any files with hardcoded credentials

2. **Always use**:
   - Environment variables for sensitive data
   - HTTPS in production
   - Secure session cookies
   - Error logging (not display) in production

3. **Regular maintenance**:
   - Keep dependencies updated
   - Monitor error logs
   - Review access logs
   - Regular backups
