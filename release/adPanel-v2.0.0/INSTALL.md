# adPanel v2.0 - Installation Guide

Panduan instalasi lengkap untuk adPanel v2.0 di berbagai environment.

---

## 📋 Table of Contents

- [System Requirements](#system-requirements)
- [Pre-Installation Checklist](#pre-installation-checklist)
- [Installation Methods](#installation-methods)
  - [XAMPP (Windows/Mac/Linux)](#xampp-windowsmaclinux)
  - [Linux Server (Production)](#linux-server-production)
  - [Docker Installation](#docker-installation)
- [Post-Installation](#post-installation)
- [Troubleshooting](#troubleshooting)

---

## System Requirements

### Minimum Requirements
- **PHP:** 7.4 or higher (8.0+ recommended)
- **Database:** MySQL 5.7+ or MariaDB 10.3+
- **Web Server:** Apache 2.4+ with mod_rewrite
- **Memory:** 256MB PHP memory limit
- **Disk Space:** 100MB minimum

### Required PHP Extensions
```bash
# Check installed extensions
php -m | grep -E "mysqli|pdo|zip|mbstring|gd|json|xml"
```

Extensions yang diperlukan:
- ✅ `mysqli` atau `pdo_mysql` - Database connectivity
- ✅ `zip` - Excel/ZIP file handling
- ✅ `mbstring` - Multi-byte string support
- ✅ `gd` - Image processing
- ✅ `json` - JSON data handling
- ✅ `xml` - XML parsing

### Recommended Tools
- **Composer** - PHP dependency manager
- **Git** - Version control (optional)
- **Text Editor** - VS Code, Sublime Text, atau Notepad++

---

## Pre-Installation Checklist

Sebelum instalasi, pastikan:

- [ ] PHP dan MySQL/MariaDB sudah terinstall
- [ ] Composer sudah terinstall
- [ ] Extension PHP yang diperlukan aktif
- [ ] Apache mod_rewrite enabled
- [ ] Port 80 (HTTP) dan 3306 (MySQL) tersedia
- [ ] Akses root/administrator untuk set permissions

---

## Installation Methods

### XAMPP (Windows/Mac/Linux)

XAMPP adalah cara termudah untuk development lokal.

#### Step 1: Install XAMPP

**Download:**
- Windows: https://www.apachefriends.org/download.html
- Pilih versi dengan PHP 7.4 atau 8.0+

**Install:**
1. Jalankan installer XAMPP
2. Pilih komponen: Apache, MySQL, PHP
3. Install di folder default (misal: `C:\xampp`)

#### Step 2: Extract adPanel

```bash
# Extract ke folder htdocs
# Windows: C:\xampp\htdocs\adPanel
# Linux/Mac: /opt/lampp/htdocs/adPanel

# Atau via GUI
1. Extract file adPanel-v2.0.zip
2. Copy folder hasil extract ke xampp/htdocs/
3. Rename folder menjadi 'adPanel'
```

#### Step 3: Start Services

**Windows:**
1. Buka XAMPP Control Panel
2. Start Apache
3. Start MySQL

**Linux/Mac:**
```bash
sudo /opt/lampp/lampp start
```

#### Step 4: Enable PHP Zip Extension

**Windows XAMPP:**
```ini
# Edit: C:\xampp\php\php.ini
# Cari dan uncomment baris ini:
extension=zip

# Save dan restart Apache
```

**Verify:**
```bash
php -m | grep zip
# Output: zip
```

#### Step 5: Install Dependencies

```bash
# Buka terminal/command prompt
cd C:\xampp\htdocs\adPanel

# Install via Composer
composer install
```

**Jika Composer belum terinstall:**
- Download dari: https://getcomposer.org/download/
- Install dan restart terminal

#### Step 6: Create Database

**Via phpMyAdmin:**
1. Buka http://localhost/phpmyadmin
2. Klik "New" untuk create database
3. Nama database: `adpanel`
4. Collation: `utf8mb4_general_ci`
5. Click "Create"

**Via Command Line:**
```bash
# Login ke MySQL
mysql -u root -p

# Create database
CREATE DATABASE adpanel CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
EXIT;
```

#### Step 7: Import Database

**Via phpMyAdmin:**
1. Pilih database `adpanel`
2. Tab "Import"
3. Choose file: `db/adpaneldb.sql`
4. Click "Go"

**Via Command Line:**
```bash
# Import SQL file
mysql -u root -p adpanel < db/adpaneldb.sql

# Verify
mysql -u root -p -e "USE adpanel; SHOW TABLES;"
```

#### Step 8: Configure Database Connection

```bash
# Copy example config
copy admin\System\kon.php.example admin\System\kon.php

# Edit admin/System/kon.php
# Update dengan kredensial database Anda
```

**Edit `kon.php`:**
```php
<?php
$servername = "localhost";
$username = "root";           // Ganti jika perlu
$password = "";               // Ganti dengan password MySQL
$dbname = "adpanel";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
```

#### Step 9: Set Permissions (Windows)

```powershell
# Buka PowerShell as Administrator
cd C:\xampp\htdocs\adPanel

# Run permission script
.\set_permissions_windows.ps1
```

#### Step 10: Access Application

**Frontend:**
- URL: http://localhost/adPanel/
- Test homepage dan katalog produk

**Admin Panel:**
- URL: http://localhost/adPanel/admin/
- Default login akan bergantung pada data di database

✅ **Installation Complete!**

---

### Linux Server (Production)

Untuk production server dengan Ubuntu/Debian.

#### Step 1: Install LAMP Stack

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Apache
sudo apt install apache2 -y

# Install MySQL
sudo apt install mysql-server -y

# Install PHP 8.0 and extensions
sudo apt install php8.0 php8.0-cli php8.0-fpm php8.0-mysql php8.0-zip php8.0-gd php8.0-mbstring php8.0-curl php8.0-xml -y

# Enable Apache modules
sudo a2enmod rewrite
sudo a2enmod headers
sudo systemctl restart apache2
```

#### Step 2: Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

#### Step 3: Deploy Application

```bash
# Create directory
sudo mkdir -p /var/www/html/adPanel
cd /var/www/html/adPanel

# Upload & extract (via SFTP/SCP or wget)
wget https://your-server.com/adPanel-v2.0.zip
unzip adPanel-v2.0.zip
rm adPanel-v2.0.zip

# Or via Git
git clone https://github.com/yourusername/adPanel.git .
```

#### Step 4: Install Dependencies

```bash
cd /var/www/html/adPanel
composer install --no-dev --optimize-autoloader
```

#### Step 5: Configure Database

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

```sql
-- Create database
CREATE DATABASE adpanel CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Create user
CREATE USER 'adpanel_user'@'localhost' IDENTIFIED BY 'StrongPassword123!@#';

-- Grant privileges
GRANT SELECT, INSERT, UPDATE, DELETE ON adpanel.* TO 'adpanel_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### Step 6: Import Database

```bash
mysql -u adpanel_user -p adpanel < db/adpaneldb.sql
```

#### Step 7: Configure Application

```bash
# Copy and edit config
cp admin/System/kon.php.example admin/System/kon.php
nano admin/System/kon.php
```

Update dengan kredensial database production:
```php
$servername = "localhost";
$username = "adpanel_user";
$password = "StrongPassword123!@#";
$dbname = "adpanel";
```

#### Step 8: Set Permissions

```bash
# Run permission script
chmod +x set_permissions.sh
./set_permissions.sh

# Set ownership
sudo chown -R www-data:www-data /var/www/html/adPanel

# Set specific permissions
sudo chmod 600 admin/System/kon.php
sudo chmod 775 admin/Control/product/img
sudo chmod 775 admin/assets/images
```

#### Step 9: Configure Apache VirtualHost

```bash
# Create virtual host config
sudo nano /etc/apache2/sites-available/adpanel.conf
```

**VirtualHost Configuration:**
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/html/adPanel

    <Directory /var/www/html/adPanel>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Security headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set X-Content-Type-Options "nosniff"

    ErrorLog ${APACHE_LOG_DIR}/adpanel_error.log
    CustomLog ${APACHE_LOG_DIR}/adpanel_access.log combined
</VirtualHost>
```

**Enable site:**
```bash
sudo a2ensite adpanel.conf
sudo systemctl reload apache2
```

#### Step 10: SSL Certificate (Recommended)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y

# Get SSL certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renewal test
sudo certbot renew --dry-run
```

#### Step 11: Production PHP Settings

```bash
sudo nano /etc/php/8.0/apache2/php.ini
```

**Update settings:**
```ini
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php/error.log

upload_max_filesize = 5M
post_max_size = 8M
max_execution_time = 300
memory_limit = 256M

session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1

expose_php = Off
```

```bash
# Create log directory
sudo mkdir -p /var/log/php
sudo chown www-data:www-data /var/log/php

# Restart Apache
sudo systemctl restart apache2
```

✅ **Production Deployment Complete!**

---

### Docker Installation

Coming soon in future release.

---

## Post-Installation

### 1. Verify Installation

**Check Frontend:**
```
http://yourdomain.com/
```
- Homepage harus tampil
- Produk katalog berfungsi
- Search bar aktif

**Check Admin:**
```
http://yourdomain.com/admin/
```
- Login page harus tampil
- Test login dengan user dari database

### 2. Create Admin User

```sql
-- Via MySQL
mysql -u root -p adpanel

-- Create admin user (adjust sesuai table structure)
INSERT INTO users (username, password, email, role, created_at) 
VALUES ('admin', MD5('admin123'), 'admin@example.com', 'A', NOW());

-- Or use bcrypt (more secure)
-- Password hash should be generated via PHP
```

### 3. Test Functionality

- [ ] Login ke admin panel
- [ ] Create/Edit/Delete product
- [ ] Upload product image
- [ ] Import Excel file
- [ ] Stock IN/OUT transaction
- [ ] Banner management
- [ ] Search functionality

### 4. Setup Backup

**MySQL Backup Script:**
```bash
#!/bin/bash
# backup.sh

BACKUP_DIR="/backup/adpanel"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="adpanel"
DB_USER="adpanel_user"
DB_PASS="YourPassword"

mkdir -p $BACKUP_DIR
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/adpanel_$DATE.sql.gz

# Keep only last 7 days
find $BACKUP_DIR -name "*.sql.gz" -mtime +7 -delete
```

**Add to cron:**
```bash
chmod +x backup.sh
crontab -e

# Add: Daily backup at 2 AM
0 2 * * * /path/to/backup.sh
```

### 5. Monitoring

Setup monitoring untuk:
- Disk space
- Database size
- Error logs
- Access logs
- Uptime

---

## Troubleshooting

### Issue: "Class ZipArchive not found"

**Solution:**
```bash
# Enable zip extension
# Edit php.ini and uncomment:
extension=zip

# Restart web server
sudo systemctl restart apache2
```

### Issue: "Database connection failed"

**Check:**
1. MySQL service running: `sudo systemctl status mysql`
2. Credentials in `kon.php` correct
3. Database exists: `mysql -u root -p -e "SHOW DATABASES;"`
4. User has permissions: `SHOW GRANTS FOR 'adpanel_user'@'localhost';`

### Issue: "Permission denied" saat upload file

**Solution:**
```bash
# Fix upload folder permissions
sudo chmod 775 admin/Control/product/img
sudo chown www-data:www-data admin/Control/product/img
```

### Issue: ".htaccess not working"

**Solution:**
```bash
# Enable mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2

# Check AllowOverride in Apache config
# Should be: AllowOverride All
```

### Issue: "500 Internal Server Error"

**Check:**
1. PHP error log: `tail -f /var/log/apache2/error.log`
2. File permissions correct
3. .htaccess syntax
4. PHP extensions installed

### Issue: Excel import tidak berfungsi

**Solution:**
```bash
# Install PhpSpreadsheet
composer require phpoffice/phpspreadsheet

# Check zip extension
php -m | grep zip
```

---

## Next Steps

1. 📖 Read [README.md](README.md) untuk overview aplikasi
2. 🔒 Review [SECURITY.md](SECURITY.md) untuk security best practices
3. 📝 Check [CHANGELOG.md](CHANGELOG.md) untuk version history
4. 🎉 Mulai gunakan adPanel!

---

## Support

Jika menemui masalah:
1. Check [Troubleshooting](#troubleshooting) section
2. Review error logs
3. Check GitHub Issues
4. Contact support team

---

**Happy Installing! 🚀**
