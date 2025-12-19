# Local Development Environment Setup Guide

This guide will help you set up a local PHP/MySQL environment on macOS to test the Social Care Contracts Management Application.

## Option 1: MAMP (Recommended for macOS)

MAMP (Mac, Apache, MySQL, PHP) is a free, all-in-one solution perfect for macOS.

### Step 1: Download and Install MAMP

1. Visit https://www.mamp.info/en/downloads/
2. Download MAMP (free version is sufficient)
3. Open the downloaded `.dmg` file
4. Drag MAMP to your Applications folder
5. Open MAMP from Applications

### Step 2: Start MAMP

1. Launch MAMP application
2. Click "Start Servers" button
3. Wait for Apache and MySQL to start (green indicators)
4. The default ports are:
   - Apache: 8888
   - MySQL: 8889

### Step 3: Access phpMyAdmin

1. Click "Open WebStart page" or go to: http://localhost:8888/MAMP/
2. Click on "phpMyAdmin" link (or go to: http://localhost:8888/phpMyAdmin/)

### Step 4: Create Database

1. In phpMyAdmin, click "New" in the left sidebar
2. Enter database name: `social_care_contracts`
3. Choose collation: `utf8mb4_unicode_ci`
4. Click "Create"

### Step 5: Import Schema

1. Select the `social_care_contracts` database
2. Click the "Import" tab
3. Click "Choose File"
4. Navigate to your project folder and select: `sql/schema.sql`
5. Click "Go" at the bottom

### Step 6: Configure Application

1. Copy the example environment file:
   ```bash
   cp .env.example .env
   ```

2. Edit `.env` file in your project root and update with your settings:
   ```env
   DB_HOST=localhost:8889
   DB_NAME=social_care_contracts
   DB_USER=root
   DB_PASS=root
   DB_CHARSET=utf8mb4
   ```

   **Note:** 
   - For MAMP, use `localhost:8889` for DB_HOST (or just `localhost` if using default MySQL port)
   - For XAMPP, use `localhost` and leave `DB_PASS` empty
   - The `.env` file is already in `.gitignore` so your credentials won't be committed

### Step 7: Set Document Root (Simple Setup)

**Set document root to the project root folder** (same as Hostinger). The application automatically handles routing.

1. In MAMP, click **"Preferences"** (or "MAMP" → "Preferences")
2. Go to **"Web Server"** tab
3. Click **"Select..."** next to "Document Root"
4. Navigate to and **select your project root folder** (e.g., `/Users/wellis/Desktop/Cursor/contracts/`)
   - This is the folder that contains `config/`, `public/`, `src/`, `sql/`, `index.php`, etc.
   - **Do NOT** select the `public` subfolder
5. Click **"OK"**
6. **Restart MAMP servers:**
   - Click **"Stop Servers"**
   - Wait a few seconds for servers to stop
   - Click **"Start Servers"**
   - Wait for both Apache and MySQL to show green/running status

**How it works:** The root `index.php` file automatically loads the application from the `public` folder. This is the same setup used on Hostinger and other hosting providers - just set document root to the project root folder. No special configuration needed!

### Step 8: Test the Application

1. Open your browser and go to: **http://localhost:8888/**
2. **You should see the application home page**
   - ✅ **Correct:** You see the application with navigation, content, etc.
   - ❌ **Wrong:** You see a directory listing showing folders like `config/`, `src/`, `sql/`, etc.
3. If you see a directory listing, verify:
   - Document root is set to the project root (not `public` folder)
   - Apache mod_rewrite is enabled (should be by default in MAMP)
   - Servers were restarted after changing document root

---

## Option 2: XAMPP (Cross-platform)

XAMPP works on macOS, Windows, and Linux.

### Step 1: Download and Install

1. Visit https://www.apachefriends.org/download.html
2. Download XAMPP for macOS
3. Open the downloaded file and follow installation wizard
4. Install to `/Applications/XAMPP`

### Step 2: Start Services

1. Open XAMPP Control Panel
2. Start Apache and MySQL
3. Default ports:
   - Apache: 80 (or 8080 if 80 is busy)
   - MySQL: 3306

### Step 3: Access phpMyAdmin

1. Go to: http://localhost/phpmyadmin
2. Default credentials:
   - Username: `root`
   - Password: (leave blank)

### Step 4-8: Follow steps 4-8 from MAMP section above

Create `.env` file from `.env.example` and configure:
```env
DB_HOST=localhost
DB_NAME=social_care_contracts
DB_USER=root
DB_PASS=  # Leave empty for XAMPP default
DB_CHARSET=utf8mb4
```

---

## Option 3: Homebrew (Advanced)

If you prefer command-line installation and more control.

### Step 1: Install Homebrew (if not installed)

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

### Step 2: Install PHP and MySQL

```bash
brew install php mysql
```

### Step 3: Start MySQL

```bash
brew services start mysql
```

### Step 4: Secure MySQL (optional but recommended)

```bash
mysql_secure_installation
```

### Step 5: Create Database

```bash
mysql -u root -p
```

Then in MySQL:
```sql
CREATE DATABASE social_care_contracts CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Step 6: Import Schema

```bash
mysql -u root -p social_care_contracts < sql/schema.sql
```

### Step 7: Configure Application

1. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```

2. Edit `.env` and update with your MySQL credentials:
   ```env
   DB_HOST=localhost
   DB_NAME=social_care_contracts
   DB_USER=root
   DB_PASS=your_mysql_password
   ```

### Step 8: Start PHP Built-in Server

```bash
cd /Users/wellis/Desktop/Cursor/contracts/public
php -S localhost:8000
```

Access at: http://localhost:8000

---

## Create Your First Superadmin User

After setting up the database and accessing the application, you need to create a superadmin user to get started.

### Method 1: Using phpMyAdmin

1. Open phpMyAdmin
2. Select `social_care_contracts` database
3. Click "SQL" tab
4. Run this SQL (replace email and password):

```sql
-- First, create the user
INSERT INTO users (organisation_id, email, password_hash, first_name, last_name)
VALUES (NULL, 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User');

-- Note the user ID from above, then assign superadmin role
-- Replace <user_id> with the actual ID
INSERT INTO user_roles (user_id, role_id)
SELECT <user_id>, id FROM roles WHERE name = 'superadmin';
```

**To generate a password hash**, create a temporary PHP file:

1. Create `generate_password.php` in your project root:
```php
<?php
echo password_hash('your_secure_password_here', PASSWORD_DEFAULT);
?>
```

2. Run it: `php generate_password.php`
3. Copy the hash and use it in the SQL above
4. Delete the file after use

### Method 2: Using Command Line

```bash
mysql -u root -p social_care_contracts
```

Then run the SQL commands from Method 1.

---

## Troubleshooting

### MySQL Connection Error

- Check if MySQL is running in MAMP/XAMPP
- Verify port number (8889 for MAMP, 3306 for XAMPP)
- Check username/password in `config/database.php`
- Try `localhost:8889` instead of just `localhost` for MAMP

### Port Already in Use

- MAMP: Change ports in Preferences → Ports
- XAMPP: Use port 8080 for Apache if 80 is busy

### Permission Denied Errors

- Check file permissions: `chmod 755` for folders, `chmod 644` for files
- Ensure PHP has read access to project files

### phpMyAdmin Not Loading

- Make sure Apache is running
- Clear browser cache
- Try accessing directly: http://localhost:8888/phpMyAdmin (MAMP) or http://localhost/phpmyadmin (XAMPP)

### Application Not Loading / I See a Directory Listing

**If you see a directory listing (showing folders like `config/`, `src/`, `sql/`) instead of the application:**

This usually means:
1. Document root is not set to the project root, OR
2. Apache mod_rewrite is not enabled

**Fix:**
1. **Verify document root:** In MAMP Preferences → Web Server, the document root should be your **project root folder** (the one containing `config/`, `public/`, `src/`, etc.)
   - ✅ Correct: `/Users/wellis/Desktop/Cursor/contracts/`
   - ❌ Wrong: `/Users/wellis/Desktop/Cursor/contracts/public/`
2. **Enable mod_rewrite:** In MAMP Preferences → PHP, ensure Apache modules are enabled (mod_rewrite should be enabled by default)
3. **Restart MAMP servers** (Stop, then Start)
4. Refresh your browser at `http://localhost:8888/`

**Other checks:**
- Verify `.htaccess` file exists in project root folder
- Check Apache error logs in MAMP/XAMPP
- Ensure servers were restarted after changing document root

---

## Quick Test Checklist

Once set up, verify everything works:

- [ ] MAMP/XAMPP servers are running (green indicators)
- [ ] **Document root set to project root folder** (MAMP/XAMPP - the folder containing `config/`, `public/`, etc.)
- [ ] Database `social_care_contracts` exists
- [ ] Schema imported successfully
- [ ] `.env` file configured with correct database credentials
- [ ] Can access http://localhost:8888/ (or your configured URL)
- [ ] **See application home page (NOT a directory listing)**
- [ ] Can access login page
- [ ] Can log in with superadmin account
- [ ] Can access superadmin panel
- [ ] Can create an organisation

---

## Recommended: MAMP Pro (Optional)

MAMP Pro is a paid version with additional features like:
- Virtual hosts
- Multiple PHP versions
- Easy SSL certificate management

But the free version of MAMP works perfectly for development!

---

## Next Steps

Once your local environment is set up:

1. Test all features locally
2. Create test organisations and users
3. Add sample contracts and rates
4. Test the reporting functionality
5. When ready, follow `DEPLOYMENT.md` for production deployment
