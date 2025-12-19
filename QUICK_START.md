# Quick Start Guide

Get your application running locally in minutes!

> **💡 SIMPLE SETUP:** Set your document root to the **project root folder** (same as Hostinger). The `.htaccess` file automatically routes requests to the `public` folder. No need to configure the `public` folder as document root!

## Prerequisites Check

Before starting, make sure you have:
- [ ] MAMP, XAMPP, or PHP installed
- [ ] Database created
- [ ] Schema imported
- [ ] `.env` file configured

## Option 1: Using MAMP (Easiest)

### Step 1: Start MAMP Servers

1. Open MAMP application
2. Click **"Start Servers"** button
3. Wait for green indicators (Apache and MySQL)

### Step 2: Configure Document Root (Simple!)

**Set document root to the project root folder** (just like Hostinger). The application automatically handles routing.

1. In MAMP, click **"Preferences"** (or "MAMP" → "Preferences")
2. Go to **"Web Server"** tab
3. Click **"Select..."** next to "Document Root"
4. Navigate to and **select your project root folder**: `/Users/wellis/Desktop/Cursor/contracts/`
   - Select the folder that contains `config/`, `public/`, `src/`, `index.php`, etc.
   - **Do NOT** select the `public` subfolder
5. Click **"OK"**
6. **Restart servers:**
   - Click **"Stop Servers"**
   - Wait a few seconds
   - Click **"Start Servers"**
   - Wait for both Apache and MySQL to show green/running status

**That's it!** The root `index.php` file automatically loads the application from the `public` folder. This works the same way as Hostinger and other hosting providers - just set document root to the project root folder.

### Step 3: Access the Application

Open your browser and go to:
```
http://localhost:8888/
```

You should see the application home page. The URL routing is handled automatically.

---

## Option 2: Using XAMPP

### Step 1: Start XAMPP Services

1. Open XAMPP Control Panel
2. Click **"Start"** next to Apache
3. Click **"Start"** next to MySQL
4. Both should show green "Running" status

### Step 2: Set Document Root (Option A)

1. Edit XAMPP Apache config (usually `/Applications/XAMPP/etc/httpd.conf`)
2. Find `DocumentRoot` and change to your `public` folder path
3. Restart Apache

### Step 2: Or Use Project in htdocs (Option B)

1. Copy your project to `/Applications/XAMPP/htdocs/contracts/`
2. Access at: `http://localhost/contracts/public/`

### Step 3: Access the Application

Open your browser:
```
http://localhost/contracts/public/
```

Or if you configured document root:
```
http://localhost/
```

---

## Option 3: Using PHP Built-in Server (No MAMP/XAMPP)

Great if you already have PHP and MySQL installed separately.

### Step 1: Start MySQL (if not already running)

```bash
# If using Homebrew MySQL
brew services start mysql

# Or manually
mysql.server start
```

### Step 2: Navigate to Project

```bash
cd /Users/wellis/Desktop/Cursor/contracts/public
```

### Step 3: Start PHP Server

```bash
php -S localhost:8000
```

You'll see:
```
PHP 8.x.x Development Server (http://localhost:8000) started
```

### Step 4: Access the Application

Open your browser:
```
http://localhost:8000
```

**Note:** Keep the terminal window open while the server is running. Press `Ctrl+C` to stop.

---

## First Time Setup (If Not Done Yet)

### 1. Create Database

- **MAMP**: Go to http://localhost:8888/phpMyAdmin
- **XAMPP**: Go to http://localhost/phpmyadmin
- Create database: `social_care_contracts`
- Import `sql/schema.sql`

### 2. Configure Environment

```bash
# Copy example .env file
cp .env.example .env

# Edit .env with your database credentials
# For MAMP:
#   DB_HOST=localhost:8889
#   DB_USER=root
#   DB_PASS=root
# For XAMPP:
#   DB_HOST=localhost
#   DB_USER=root
#   DB_PASS=
```

### 3. Create Superadmin User

See `LOCAL_SETUP.md` section "Create Your First Superadmin User"

---

## Troubleshooting

### "I see a directory listing instead of the application"

**This usually means:**
1. Document root is not set correctly, OR
2. Apache mod_rewrite is not enabled

**Fix:**
1. **Verify document root:** In MAMP Preferences → Web Server, the document root should be your **project root folder** (the one containing `config/`, `public/`, `src/`, etc.)
2. **Enable mod_rewrite:** In MAMP Preferences → PHP, make sure Apache modules are enabled (mod_rewrite should be enabled by default)
3. **Restart MAMP servers** (Stop, then Start)
4. Refresh your browser at `http://localhost:8888/`

**Alternative:** If you prefer, you can set document root to the `public` folder instead. Both methods work, but using the project root is simpler and matches Hostinger setup.

### "Cannot connect to database"

- ✅ Check MySQL is running (green indicator in MAMP)
- ✅ Verify `.env` credentials are correct
- ✅ For MAMP, make sure port is `localhost:8889` in DB_HOST
- ✅ Test connection in phpMyAdmin

### "404 Not Found" or "Page Not Found"

- ✅ **Verify document root points to `public` folder** (see troubleshooting above)
- ✅ Try accessing with full path: `http://localhost:8888/index.php`
- ✅ Check `.htaccess` file exists in `public` folder

### "Error loading page" or blank page

- ✅ Check PHP errors in MAMP/XAMPP logs
- ✅ Verify all files are in correct locations
- ✅ Check file permissions
- ✅ **Verify document root is set to `public` folder**

### Port Already in Use

**MAMP:**
- Change ports in MAMP Preferences → Ports
- Update `.env` DB_HOST accordingly

**XAMPP:**
- Use port 8080 if 80 is busy
- Access at http://localhost:8080

**PHP Built-in:**
- Use different port: `php -S localhost:8001`

---

## Quick Checklist

- [ ] Servers started (Apache/MySQL or PHP server)
- [ ] **Document root set to project root folder** (MAMP/XAMPP - the folder containing `config/`, `public/`, etc.)
- [ ] Database exists and schema imported
- [ ] `.env` file configured correctly
- [ ] Can access http://localhost:8888/ (or your configured URL)
- [ ] **See application home page (NOT a directory listing)**
- [ ] Can log in with superadmin account

---

## Stopping the Server

### MAMP/XAMPP
- Click "Stop Servers" button

### PHP Built-in Server
- Press `Ctrl+C` in terminal

---

## Next Steps

Once the app is running:

1. Log in with your superadmin account
2. Create your first organisation via Super Admin panel
3. Test registration with organisation domain
4. Create contract types
5. Add contracts and rates
6. Explore the reporting features

For detailed setup instructions, see `LOCAL_SETUP.md`






