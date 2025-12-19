# Social Care Contracts Management Application

A PHP/MySQL multi-tenant web application for social care providers in Scotland to manage contracts, track rates, and handle various payment methods.

## Features

- Multi-organisation support with domain-based registration
- Role-based access control (superadmin, organisation admin, staff)
- Contract management for single person and bulk contracts
- Customisable contract types per organisation
- Rate management with historical tracking
- Payment method tracking
- Reporting on payments and rate changes
- Support for all Scottish Local Authorities
- Responsive design for mobile and desktop

## Technology Stack

- PHP
- MySQL (via phpMyAdmin)
- Plain HTML/CSS (no frameworks)

## Installation

### Local Development

1. Clone the repository:
```bash
git clone <repository-url>
cd contracts
```

2. Set up your local PHP development environment (XAMPP, MAMP, or similar)

3. Create the database:
   - Open phpMyAdmin
   - Create a new database: `social_care_contracts`
   - Import the schema from `sql/schema.sql`

4. Configure database connection:
   - Copy `.env.example` to `.env`: `cp .env.example .env`
   - Edit `.env` file and update database credentials:
     ```env
     DB_HOST=localhost
     DB_NAME=social_care_contracts
     DB_USER=root
     DB_PASS=
     ```
   - The `.env` file is gitignored for security

5. Set up your web server:
   - **Simple setup:** Point your web server document root to the **project root directory** (same as Hostinger)
   - The `.htaccess` file automatically routes requests to the `public` folder
   - For MAMP/XAMPP: Set document root to project root folder in server preferences
   - See `QUICK_START.md` for detailed step-by-step instructions

6. Start your local server (choose one):
   - **MAMP**: Start servers, **set document root to project root folder**, access at http://localhost:8888
   - **XAMPP**: Start Apache/MySQL, set document root to project root folder, access at http://localhost/
   - **PHP Built-in**: Run `php -S localhost:8000` from `public` folder, access at http://localhost:8000

7. Access the application:
   - **See `QUICK_START.md` for detailed step-by-step instructions**
   - The setup works the same way locally and on Hostinger - no special configuration needed!

### Production Deployment (Hostinger)

1. Upload files to Hostinger:
   - Upload all files to your hosting directory

2. Set up database:
   - Create database via Hostinger control panel
   - Import `sql/schema.sql` via phpMyAdmin

3. Configure production settings:
   - Update `config/database.php` with production credentials
   - Update `config/config.php`:
     - Set `APP_URL` to your domain
     - Disable error display: `ini_set('display_errors', 0);`
     - Enable HTTPS for sessions: `ini_set('session.cookie_secure', 1);`

4. Set up URL rewriting (if needed):
   - Configure `.htaccess` in the public directory

## Initial Setup

1. Create a superadmin user manually in the database:
   ```sql
   INSERT INTO users (organisation_id, email, password_hash, first_name, last_name)
   VALUES (NULL, 'admin@example.com', '$2y$10$...', 'Admin', 'User');
   
   -- Get the user ID and role IDs, then:
   INSERT INTO user_roles (user_id, role_id) 
   VALUES (<user_id>, (SELECT id FROM roles WHERE name = 'superadmin'));
   ```

2. Create your first organisation via the Super Admin panel

3. Users can then register using the organisation domain

## File Structure

```
/contracts/
├── /config/          Configuration files
├── /includes/        Header and footer templates
├── /public/          Public-facing files
│   ├── /assets/      CSS and other assets
│   └── /pages/       Content pages
├── /src/
│   ├── /classes/     Core classes (Auth, RBAC, CSRF)
│   └── /models/      Data models
├── /sql/             Database schema
└── overview.txt      Project overview
```

## Security

- Password hashing using bcrypt
- CSRF protection on all forms
- Organisation data isolation
- SQL injection prevention with prepared statements
- XSS prevention with htmlspecialchars

## UK English

All user-facing text uses UK English spelling (e.g., "organisation" not "organization").

## License

[Your License Here]

## Support

For issues and questions, please contact your system administrator.
