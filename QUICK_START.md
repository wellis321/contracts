# Quick Start Guide

Get your application running locally in minutes!

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

### Step 2: Configure Document Root

1. In MAMP, click **"Preferences"**
2. Go to **"Web Server"** tab
3. Click **"Select..."** next to "Document Root"
4. Navigate to your project and select the **`public`** folder
5. Click **"OK"** and restart servers (click **"Stop Servers"** then **"Start Servers"**)

### Step 3: Access the Application

Open your browser and go to:
```
http://localhost:8888/
```

Or if you kept default MAMP setup:
```
http://localhost:8888/contracts/public/
```

That's it! Your app should be running.

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

### "Cannot connect to database"

- ✅ Check MySQL is running
- ✅ Verify `.env` credentials are correct
- ✅ For MAMP, make sure port is `localhost:8889` in DB_HOST
- ✅ Test connection in phpMyAdmin

### "404 Not Found" or "Page Not Found"

- ✅ Check document root points to `public` folder
- ✅ Try accessing with full path: `http://localhost:8888/index.php`
- ✅ Check `.htaccess` file exists in `public` folder

### "Error loading page"

- ✅ Check PHP errors in MAMP/XAMPP logs
- ✅ Verify all files are in correct locations
- ✅ Check file permissions

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
- [ ] Database exists and schema imported
- [ ] `.env` file configured correctly
- [ ] Can access http://localhost (or your configured URL)
- [ ] See home page (not error page)
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


