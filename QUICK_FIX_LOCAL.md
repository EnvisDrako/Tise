# Quick Fix for Local Setup Issues

## 🚨 Current Issues Detected

1. **Missing PHP MySQL Extension** - `pdo_mysql` not found
2. **Missing PHP Extensions** - Several required extensions missing
3. **Database Connection Failed** - Cannot connect to MySQL

## 🔧 Quick Solutions

### Option 1: Use XAMPP (Recommended)

#### 1. Install XAMPP
- Download from: https://www.apachefriends.org/
- Install with Apache, MySQL, and PHP
- Start Apache and MySQL services

#### 2. Move Project to XAMPP
```bash
# Copy your project to XAMPP directory
C:\xampp\htdocs\integrated-crm\
```

#### 3. Access via XAMPP
- URL: `http://localhost/integrated-crm/`
- This will use XAMPP's PHP with all required extensions

### Option 2: Fix Current PHP Installation

#### 1. Enable PHP Extensions
Edit your `php.ini` file and uncomment these lines:
```ini
extension=pdo_mysql
extension=mbstring
extension=fileinfo
extension=gd
extension=curl
extension=zip
extension=openssl
```

#### 2. Restart Web Server
After editing `php.ini`, restart your web server.

#### 3. Install MySQL
Make sure MySQL is installed and running on your system.

## 🎯 Immediate Test

### Test 1: Check if Website Loads
1. Go to: `http://localhost/integrated-crm/`
2. You should see the homepage (not directory listing)

### Test 2: Check Setup Status
1. Go to: `http://localhost/integrated-crm/setup_local.php`
2. Follow any remaining setup instructions

### Test 3: Test Login
1. Go to: `http://localhost/integrated-crm/login.php`
2. Try logging in with: `admin` / `admin123`

## 🔍 Current Status

✅ **Fixed Issues:**
- Added SYSTEM_ACCESS constant to all public PHP files
- Created configuration file
- Directory structure is correct
- File permissions are good

❌ **Remaining Issues:**
- PHP MySQL extension missing
- Some PHP extensions missing
- Database connection failing

## 🚀 Recommended Next Steps

1. **Install XAMPP** (easiest solution)
2. **Move project to** `C:\xampp\htdocs\integrated-crm\`
3. **Start Apache and MySQL** in XAMPP Control Panel
4. **Access** `http://localhost/integrated-crm/`
5. **Run setup** `http://localhost/integrated-crm/setup_local.php`
6. **Import database** via phpMyAdmin

## 📞 If Still Having Issues

The directory listing issue should be resolved now. If you're still seeing a directory listing:

1. **Check if index.php exists** in the root directory (it should redirect to public/)
2. **Check if .htaccess is working** (Apache mod_rewrite enabled)
3. **Try accessing directly**: `http://localhost/integrated-crm/public/`

The main issue was missing PHP extensions, which XAMPP will solve completely!