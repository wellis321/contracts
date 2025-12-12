# Production Deployment Checklist

This checklist ensures your Social Care Contracts Management system is properly configured and secured for production use.

## Pre-Deployment

### 1. Environment Configuration
- [ ] Copy `.env.example` to `.env` on production server
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_URL` to your production domain with HTTPS (e.g., `https://yourdomain.com`)
- [ ] Configure all database credentials in `.env`
- [ ] Configure email settings (`MAIL_FROM`, `MAIL_REPLY_TO`)
- [ ] Verify `.env` file is NOT tracked in git (check `.gitignore`)

### 2. Database Setup
- [ ] Create production database
- [ ] Create dedicated database user with minimal required permissions
- [ ] Use strong database password (16+ characters)
- [ ] Run all SQL migrations in order:
  ```bash
  # Run schema.sql first
  mysql -u your_user -p your_database < sql/schema.sql
  
  # Then run all migrations in order
  mysql -u your_user -p your_database < sql/migration_*.sql
  ```
- [ ] **DO NOT** run `sql/seed_test_data.sql` in production (test data only)
- [ ] Verify database connection works with production credentials
- [ ] Set up automated database backups

### 3. File Permissions
- [ ] Set proper file permissions:
  ```bash
  # Directories should be 755
  find . -type d -exec chmod 755 {} \;
  
  # Files should be 644
  find . -type f -exec chmod 644 {} \;
  
  # .env should be 600 (readable only by owner)
  chmod 600 .env
  ```
- [ ] Ensure web server user can read files but not write (except uploads directory if used)
- [ ] Verify `.htaccess` files are in place and working

### 4. Web Server Configuration

#### Apache
- [ ] Enable `mod_rewrite` module
- [ ] Enable `mod_headers` module (for security headers)
- [ ] Configure virtual host to point to `/public` directory
- [ ] Set up SSL certificate (Let's Encrypt recommended)
- [ ] Force HTTPS redirect
- [ ] Verify security headers are working (check with browser dev tools)

#### Nginx
- [ ] Configure server block to point to `/public` directory
- [ ] Set up SSL certificate
- [ ] Configure PHP-FPM
- [ ] Add security headers in nginx config
- [ ] Test rewrite rules

### 5. PHP Configuration
- [ ] PHP version 7.4+ (8.0+ recommended)
- [ ] Enable required PHP extensions:
  - `pdo_mysql`
  - `mbstring`
  - `openssl`
  - `curl`
  - `dom` (for tender importing)
- [ ] Configure `php.ini`:
  ```ini
  display_errors = Off
  log_errors = On
  error_log = /path/to/php-error.log
  upload_max_filesize = 10M
  post_max_size = 10M
  memory_limit = 256M
  max_execution_time = 60
  ```
- [ ] Enable OPcache for performance
- [ ] Set proper `session.save_path` with secure permissions

### 6. Security Hardening
- [ ] Verify `.htaccess` files protect sensitive files (`.sql`, `.log`, `.ini`)
- [ ] Ensure directory listing is disabled
- [ ] Verify CSRF protection is working (test form submissions)
- [ ] Check that error pages don't expose sensitive information
- [ ] Review and update default passwords if any
- [ ] Set up firewall rules (only allow necessary ports)
- [ ] Configure rate limiting if available

### 7. Email Configuration
- [ ] Test email sending functionality
- [ ] For production, consider upgrading to SMTP:
  - Update `src/classes/Email.php` to use SMTP library (PHPMailer, SwiftMailer, etc.)
  - Configure SMTP credentials in `.env`
  - Test email delivery
- [ ] Set up SPF/DKIM records for your domain
- [ ] Verify emails are not going to spam

### 8. Application Setup
- [ ] Create superadmin account:
  ```bash
  php create_superadmin.php
  ```
- [ ] Test login functionality
- [ ] Verify all pages load correctly
- [ ] Test form submissions and CSRF protection
- [ ] Verify file uploads work (if applicable)

### 9. Scheduled Tasks (Cron Jobs)
- [ ] Set up cron job for tender monitoring:
  ```bash
  # Run every 6 hours
  0 */6 * * * /usr/bin/php /path/to/contracts/scripts/check-tenders.php >> /path/to/logs/tender-check.log 2>&1
  ```
- [ ] Set up cron job for expired contract updates (if needed):
  ```bash
  # Run daily at 2 AM
  0 2 * * * /usr/bin/php /path/to/contracts/scripts/update-expired-contracts.php >> /path/to/logs/contracts.log 2>&1
  ```
- [ ] Verify cron jobs are running and logging correctly

### 10. Monitoring & Logging
- [ ] Set up error logging:
  - PHP error log
  - Application-specific logs
  - Database error logs
- [ ] Configure log rotation to prevent disk space issues
- [ ] Set up monitoring/alerting (optional but recommended):
  - Uptime monitoring
  - Error rate monitoring
  - Database connection monitoring
- [ ] Test error logging works

### 11. Backup Strategy
- [ ] Set up automated database backups (daily recommended)
- [ ] Store backups securely (encrypted, off-site)
- [ ] Test backup restoration process
- [ ] Document backup procedures
- [ ] Set up file backups if user uploads are used

### 12. Performance Optimization
- [ ] Enable PHP OPcache
- [ ] Configure database query caching if available
- [ ] Set up CDN for static assets (optional)
- [ ] Enable gzip compression
- [ ] Optimize images if applicable
- [ ] Test page load times

### 13. Documentation
- [ ] Document production URL and access information
- [ ] Document database credentials (stored securely, not in code)
- [ ] Document backup procedures
- [ ] Document recovery procedures
- [ ] Create runbook for common issues

## Post-Deployment

### 14. Testing
- [ ] Test all major functionality:
  - User registration and email verification
  - Login/logout
  - Contract creation and management
  - Reports generation
  - Tender monitoring (if enabled)
  - Rate management
  - Payment tracking
- [ ] Test on multiple browsers
- [ ] Test on mobile devices
- [ ] Verify all links work correctly
- [ ] Test error handling (404, 500 pages)

### 15. Security Audit
- [ ] Run security scan (optional tools: OWASP ZAP, Burp Suite)
- [ ] Verify HTTPS is enforced
- [ ] Check security headers are present
- [ ] Verify no sensitive data in error messages
- [ ] Test SQL injection protection
- [ ] Test XSS protection
- [ ] Verify CSRF tokens on all forms

### 16. User Acceptance Testing
- [ ] Have end users test the system
- [ ] Collect feedback
- [ ] Fix any critical issues
- [ ] Document known issues/limitations

## Maintenance

### Regular Tasks
- [ ] Monitor error logs weekly
- [ ] Review database performance monthly
- [ ] Update dependencies quarterly (PHP, libraries)
- [ ] Review and rotate backups monthly
- [ ] Check disk space usage
- [ ] Review user accounts and permissions
- [ ] Update SSL certificates before expiry

### Updates
- [ ] Test updates in staging environment first
- [ ] Backup database before updates
- [ ] Review changelog before updating
- [ ] Test all functionality after updates
- [ ] Have rollback plan ready

## Emergency Contacts
- [ ] Document server administrator contact
- [ ] Document database administrator contact
- [ ] Document hosting provider support
- [ ] Document application developer contact

## Notes
- Keep this checklist updated as your deployment process evolves
- Review and update quarterly
- Document any deviations from this checklist

---

**Last Updated:** December 2024
**Version:** 1.0

