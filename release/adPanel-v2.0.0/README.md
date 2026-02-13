# adPanel v2.0

adPanel adalah admin panel berbasis PHP + MySQL untuk manajemen produk spare parts heavy equipment. Aplikasi ini memiliki frontend katalog produk dan backend admin untuk CRUD produk, stock, banner, serta data interchange.

> **📖 Dokumentasi:** [README.md](README.md) | **🔒 Keamanan:** [SECURITY.md](SECURITY.md)

## Teknologi

- PHP (disarankan 7.4+)
- MySQL / MariaDB
- Apache (XAMPP)
- Composer (`phpoffice/phpspreadsheet` untuk import Excel)

## Fitur Utama

- Katalog produk di halaman utama (`index.php`) dengan pencarian dan filter.
- Dashboard admin (`admin/`) untuk manajemen produk, stock, banner, dan interchange.
- Import produk dari CSV/Excel.
- Manajemen transaksi stock (`Stock IN` / `Stock OUT`) dan ringkasan stock.
- Role-based access (sesuai implementasi di modul admin).

## Struktur Proyek (Ringkas)

- `index.php` — entry halaman publik.
- `admin/` — modul admin (login, dashboard, CRUD, stock, interchange).
- `webpage/` — template frontend (header/footer/page).
- `db/` — skrip SQL instalasi.
- `docs/` — dokumen internal.
- `composer.json` — dependensi PHP.

## Instalasi Cepat (XAMPP)

1. Letakkan project di folder `htdocs` (contoh: `e:\XAMPP\XAMPP_1\htdocs\adPanel`).
2. Install dependensi:

```bash
composer install
```

1. Buat database (mis. `adpanel`) lalu import SQL dari folder `db/` dengan urutan:
   - `db/create_users.sql`
   - `db/create_banner_up.sql`
   - `db/create_audit_triggers.sql`

1. Sesuaikan koneksi database di:
   - `admin/System/kon.php`
   - `webpage/includes/site-config.php` (jika dipakai pada bagian frontend)

1. Jalankan `Apache` dan `MySQL` dari XAMPP.
1. Akses aplikasi:
   - Frontend: `http://localhost/adPanel/`
   - Admin: `http://localhost/adPanel/admin/`

## Contoh Import SQL via CLI

```bash
mysql -u root -p adpanel < db/create_users.sql
mysql -u root -p adpanel < db/create_banner_up.sql
mysql -u root -p adpanel < db/create_audit_triggers.sql
```

## Troubleshooting

- **Halaman error PHP**: cek log Apache / aktifkan `display_errors` sementara di local.
- **Koneksi DB gagal**: cek kredensial di `admin/System/kon.php`.
- **Composer gagal**: pastikan versi PHP sesuai lalu ulang `composer install`.
- **Import Excel gagal**: pastikan paket `phpoffice/phpspreadsheet` sudah terpasang.

## Catatan Produksi

- Nonaktifkan `display_errors`.
- Gunakan kredensial database khusus production.
- Pastikan permission folder upload aman (write seperlunya, tidak world-writable).
- Terapkan HTTPS dan backup database berkala.

> **📌 Untuk panduan lengkap mengenai keamanan, file permissions, dan deployment checklist, lihat [SECURITY.md](SECURITY.md)**

## File & Folder Permissions

### Rekomendasi untuk Linux/Unix Server

#### File PHP & Konfigurasi (644)
```bash
# File aplikasi umum
find . -type f -name "*.php" -exec chmod 644 {} \;
find . -type f -name "*.css" -exec chmod 644 {} \;
find . -type f -name "*.js" -exec chmod 644 {} \;

# File dokumentasi
chmod 644 README.md LICENSE CHANGELOG.md
chmod 644 docs/*.md
chmod 644 composer.json

# File database
chmod 644 db/*.sql
```

#### File Konfigurasi Sensitif (600)
```bash
# PENTING: Proteksi kredensial database
chmod 600 admin/System/kon.php
chmod 600 webpage/includes/site-config.php
```

#### Folder Aplikasi (755)
```bash
# Folder utama
find . -type d -exec chmod 755 {} \;

# Folder khusus yang perlu dikontrol
chmod 755 admin/
chmod 755 admin/Control/
chmod 755 admin/System/
chmod 755 webpage/
chmod 755 vendor/
```

#### Folder Upload (775 atau 755)
```bash
# Folder yang perlu write access untuk upload gambar
chmod 775 admin/Control/product/img/
chmod 775 admin/assets/images/

# Jika menggunakan user www-data
chown -R www-data:www-data admin/Control/product/img/
```

#### Script Otomatis (Bash)
Simpan sebagai `set_permissions.sh`:

```bash
#!/bin/bash
# Set permissions untuk adPanel

echo "Setting file permissions..."

# Set baseline permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Proteksi file sensitif
chmod 600 admin/System/kon.php
chmod 600 webpage/includes/site-config.php

# Folder upload
chmod 775 admin/Control/product/img/
chmod 775 admin/assets/images/

# Set ownership (ganti www-data sesuai user web server)
# chown -R www-data:www-data .

echo "Permissions set successfully!"
```

Jalankan dengan: `bash set_permissions.sh`

### Rekomendasi untuk Windows (XAMPP)

Di Windows, permission berbeda dengan Linux. Yang penting:

#### Proteksi File Konfigurasi Database
```powershell
# Batasi akses ke kon.php - hanya SYSTEM dan Administrators
icacls "admin\System\kon.php" /inheritance:r /grant:r "SYSTEM:(F)" "Administrators:(F)"
```

#### Folder Upload
```powershell
# Pastikan folder img bisa write
icacls "admin\Control\product\img" /grant "Users:(OI)(CI)(M)"
```

#### Gunakan .htaccess untuk Proteksi

**File `admin/System/.htaccess`:**
```apache
# Proteksi file konfigurasi
<Files "kon.php">
    Require all denied
</Files>
<Files "kon.php.example">
    Require all denied
</Files>
```

**File `admin/.htaccess`:**
```apache
# Hanya izinkan akses ke file PHP yang seharusnya diakses
Options -Indexes

# Proteksi folder sensitif
<FilesMatch "^(kon|database|config).php$">
    Require all denied
</FilesMatch>
```

**File `db/.htaccess`:**
```apache
# Proteksi file SQL
Require all denied
```

**File `vendor/.htaccess`:**
```apache
# Proteksi folder vendor
Require all denied
```

### Ringkasan Permission

| Tipe | Permission | Contoh |
|------|-----------|--------|
| File PHP umum | `644` | `*.php`, `*.css`, `*.js` |
| File config sensitif | `600` | `admin/System/kon.php` |
| Dokumentasi | `644` | `README.md`, `LICENSE` |
| Folder aplikasi | `755` | `admin/`, `webpage/`, `vendor/` |
| Folder upload | `775` atau `755` | `admin/Control/product/img/` |
| Executable script | `755` | `*.sh` (jika ada) |

### Verifikasi Permission

```bash
# Cek permission file sensitif
ls -la admin/System/kon.php
# Output yang aman: -rw------- (600)

# Cek permission folder upload
ls -ld admin/Control/product/img/
# Output yang aman: drwxrwxr-x (775) atau drwxr-xr-x (755)
```

### Catatan Keamanan

1. **Jangan gunakan 777** - permission ini sangat berbahaya dan membuat file/folder bisa dimodifikasi siapa saja
2. **File kon.php** harus memiliki permission paling ketat (600)
3. **Folder upload** cukup 755, kecuali ada masalah write permission maka gunakan 775
4. **Disable directory listing** dengan file `.htaccess` atau konfigurasi Apache
5. **Gunakan .htaccess** untuk proteksi folder yang tidak boleh diakses langsung dari browser

## Lisensi

MIT — lihat file `LICENSE`.

Terjemahan Bahasa Indonesia untuk lisensi tersedia di `LICENSE_ID_v2.0.md` (hanya untuk referensi; versi legal yang berlaku tetap `LICENSE`).

