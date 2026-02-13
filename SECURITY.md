# Security Guidelines untuk adPanel

Dokumen ini berisi panduan keamanan untuk deployment dan maintenance aplikasi adPanel.

## 📋 Daftar Isi

- [File & Folder Permissions](#file--folder-permissions)
- [Proteksi File Konfigurasi](#proteksi-file-konfigurasi)
- [Proteksi Folder Upload](#proteksi-folder-upload)
- [Apache/Web Server Security](#apacheweb-server-security)
- [Database Security](#database-security)
- [PHP Security](#php-security)
- [Checklist Deployment](#checklist-deployment)

## File & Folder Permissions

### Linux/Unix Server

#### Quick Setup
```bash
# Jalankan script otomatis
bash set_permissions.sh
```

#### Manual Setup

**File permissions (644):**
```bash
find . -type f -name "*.php" -exec chmod 644 {} \;
find . -type f -name "*.css" -exec chmod 644 {} \;
find . -type f -name "*.js" -exec chmod 644 {} \;
```

**Sensitive files (600):**
```bash
chmod 600 admin/System/kon.php
chmod 600 webpage/includes/site-config.php
```

**Directory permissions (755):**
```bash
find . -type d -exec chmod 755 {} \;
```

**Upload directories (775):**
```bash
chmod 775 admin/Control/product/img/
chmod 775 admin/assets/images/
chown -R www-data:www-data admin/Control/product/img/
```

### Windows/XAMPP

#### Quick Setup
```powershell
# Jalankan script PowerShell (sebagai Administrator)
.\set_permissions_windows.ps1
```

#### Manual Setup
```powershell
# Proteksi kon.php
icacls "admin\System\kon.php" /inheritance:r /grant:r "SYSTEM:(F)" "Administrators:(F)"

# Upload folder writable
icacls "admin\Control\product\img" /grant "Users:(OI)(CI)(M)"
```

## Proteksi File Konfigurasi

### File yang Harus Dilindungi

1. **`admin/System/kon.php`** - Kredensial database utama
2. **`webpage/includes/site-config.php`** - Konfigurasi site
3. **`.env`** - Environment variables (jika ada)

### Proteksi dengan .htaccess

File `.htaccess` sudah otomatis dibuat di folder:
- `admin/System/.htaccess` - Proteksi kon.php
- `db/.htaccess` - Proteksi file SQL
- `vendor/.htaccess` - Proteksi dependencies
- `docs/.htaccess` - Proteksi dokumentasi internal

### Verifikasi Proteksi

Test dengan mengakses langsung dari browser:
- ❌ `http://yourdomain.com/adPanel/admin/System/kon.php` → Harus 403 Forbidden
- ❌ `http://yourdomain.com/adPanel/db/adpaneldb.sql` → Harus 403 Forbidden
- ❌ `http://yourdomain.com/adPanel/vendor/` → Harus 403 Forbidden

## Proteksi Folder Upload

### Security untuk Folder Upload Gambar

File `.htaccess` untuk `admin/Control/product/img/.htaccess`:
```apache
# Hanya izinkan file gambar
<FilesMatch "\.(jpg|jpeg|png|gif|webp)$">
    Require all granted
</FilesMatch>

# Blokir file executable
<FilesMatch "\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|sh|cgi)$">
    Require all denied
</FilesMatch>

# Disable PHP execution
php_flag engine off
```

### Validasi Upload di PHP

Pastikan validasi di `admin/Control/product/Actions/add_prod.php`:
```php
// Validasi extension
$allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
if (!in_array($fileExt, $allowedExt)) {
    die("File type not allowed");
}

// Validasi MIME type
$allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$fileMime = mime_content_type($_FILES['image']['tmp_name']);
if (!in_array($fileMime, $allowedMime)) {
    die("Invalid file type");
}

// Validasi ukuran (max 5MB)
if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
    die("File too large");
}
```

## Apache/Web Server Security

### Disable Directory Listing

Di `.htaccess` root:
```apache
Options -Indexes
```

### Hide Server Information

Di Apache config atau `.htaccess`:
```apache
ServerTokens Prod
ServerSignature Off
```

### Security Headers

Tambahkan di `.htaccess` root:
```apache
# Prevent clickjacking
Header always set X-Frame-Options "SAMEORIGIN"

# XSS Protection
Header always set X-XSS-Protection "1; mode=block"

# Prevent MIME sniffing
Header always set X-Content-Type-Options "nosniff"

# Referrer Policy
Header always set Referrer-Policy "strict-origin-when-cross-origin"
```

## Database Security

### Kredensial Database

1. **Gunakan user database khusus** (bukan root)
2. **Berikan permission minimal** yang diperlukan
3. **Gunakan password yang kuat** (min 16 karakter)

```sql
-- Contoh setup user database
CREATE USER 'adpanel_user'@'localhost' IDENTIFIED BY 'StrongPassword123!@#';
GRANT SELECT, INSERT, UPDATE, DELETE ON adpanel.* TO 'adpanel_user'@'localhost';
FLUSH PRIVILEGES;
```

### Backup Database

```bash
# Backup otomatis dengan cron
0 2 * * * mysqldump -u root -p'password' adpanel | gzip > /backup/adpanel_$(date +\%Y\%m\%d).sql.gz
```

### SQL Injection Prevention

Aplikasi sudah menggunakan prepared statements, pastikan:
```php
// ✓ BENAR - Gunakan prepared statement
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);

// ✗ SALAH - Jangan langsung masukkan variable
$query = "SELECT * FROM products WHERE id = $id"; // VULNERABLE!
```

## PHP Security

### php.ini Settings Production

```ini
; Disable display errors
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php/error.log

; File upload limits
upload_max_filesize = 5M
post_max_size = 8M
max_file_uploads = 5

; Disable dangerous functions
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source

; Session security
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
session.cookie_samesite = Strict

; Hide PHP version
expose_php = Off
```

### Extension yang Diperlukan

```bash
# Check extension
php -m | grep -E "mysqli|pdo|zip|mbstring|gd"
```

Pastikan aktif:
- ✅ `mysqli` atau `pdo_mysql`
- ✅ `zip` (untuk PhpSpreadsheet)
- ✅ `mbstring`
- ✅ `gd` (untuk image processing)

## Checklist Deployment

### 🔒 Security Checklist

- [ ] File permissions sudah di-set (600 untuk kon.php, 644 untuk PHP, 755 untuk folder)
- [ ] .htaccess protection untuk folder sensitif
- [ ] Display errors = OFF di php.ini
- [ ] Database user khusus (bukan root)
- [ ] Password database kuat (min 16 karakter)
- [ ] Upload folder hanya izinkan file gambar
- [ ] Disable directory listing
- [ ] Security headers dipasang
- [ ] Session timeout diatur
- [ ] HTTPS/SSL certificate terpasang
- [ ] Backup database otomatis diatur

### 🚀 Performance Checklist

- [ ] PHP OPcache enabled
- [ ] Gzip compression enabled
- [ ] Browser caching headers
- [ ] Database index optimal
- [ ] Static assets (CSS/JS) minified

### 📊 Monitoring Checklist

- [ ] PHP error log monitoring
- [ ] Apache/Nginx error log monitoring
- [ ] Disk space monitoring
- [ ] Database size monitoring
- [ ] Backup verification rutin

## Incident Response

### Jika Terjadi Security Breach

1. **Isolasi** - Segera matikan aplikasi/server
2. **Investigasi** - Cek log Apache, PHP, database untuk activity mencurigakan
3. **Restore** - Restore dari backup yang bersih
4. **Patch** - Update dan patch vulnerability
5. **Monitor** - Monitor ketat setelah recovery

### Log yang Harus Dimonitor

```bash
# Apache error log
tail -f /var/log/apache2/error.log

# PHP error log
tail -f /var/log/php/error.log

# MySQL slow query log
tail -f /var/log/mysql/slow-query.log
```

## Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
- [Apache Security Tips](https://httpd.apache.org/docs/2.4/misc/security_tips.html)

---

**Last Updated:** February 2026  
**Maintained by:** adPanel Development Team
