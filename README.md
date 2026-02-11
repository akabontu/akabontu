# Instalasi dan Panduan Cepat

Panduan ini menjelaskan cara menginstal dan menjalankan proyek pada mesin pengembangan (Windows / XAMPP). Semua instruksi ditulis dalam Bahasa Indonesia.

## Prasyarat
- PHP (disarankan PHP 7.4+)
- MySQL / MariaDB (tersedia lewat XAMPP)
- Composer
- Web server seperti Apache (XAMPP)

Proyek ini diharapkan diletakkan di dalam folder `htdocs` XAMPP, misal `C:\xampp\htdocs\adpanel`.

## Langkah Instalasi

1. Salin/letakkan proyek ke folder web server

   - Jika belum ada, pindahkan seluruh isi repository ke `htdocs/adpanel`.

2. Pasang dependensi PHP dengan Composer

```bash
cd path/to/adpanel
composer install
```

3. Buat database dan import skema

   - Buka phpMyAdmin atau gunakan CLI MySQL.
   - Contoh import via CLI (sesuaikan user/password):

```bash
mysql -u root -p < db/create_users.sql
mysql -u root -p < db/create_banner_up.sql
mysql -u root -p < db/create_audit_triggers.sql
```

   - Urutan import: `create_users.sql`, `create_banner_up.sql`, lalu `create_audit_triggers.sql`.

4. Konfigurasi koneksi database

   - File konfigurasi koneksi ada di `admin/System/kon.php` dan kemungkinan juga di `webpage/includes/site-config.php`.
   - Buka file tersebut dan sesuaikan `DB_HOST`, `DB_USER`, `DB_PASS`, dan `DB_NAME` sesuai database yang dibuat.

5. (Opsional) Set permission

   - Pastikan folder upload (jika ada) memiliki izin tulis dari web server.

6. Jalankan XAMPP / Apache + MySQL

   - Buka XAMPP Control Panel, start `Apache` dan `MySQL`.

7. Akses aplikasi

   - Buka browser dan akses: `http://localhost/adpanel/` atau path yang sesuai.
   - Halaman admin biasanya berada di `http://localhost/adpanel/admin/`.

## Catatan tentang kredensial admin
- Cek file `db/create_users.sql` untuk melihat apakah ada akun admin default yang dibuat oleh skrip. Jika tidak ada, buat user admin secara manual di tabel users.

## Troubleshooting umum
- Jika halaman menampilkan error PHP: aktifkan `display_errors` sementara di `php.ini` atau cek `apache/error.log`.
- Jika koneksi DB gagal: periksa kembali `admin/System/kon.php` dan pastikan MySQL berjalan.
- Jika dependensi Composer tidak terpasang: jalankan `composer install` lagi dan periksa versi PHP.

## Menjalankan pada lingkungan produksi
- Untuk produksi, jangan aktifkan `display_errors`. Gunakan konfigurasi database yang aman, dan set permission file sesuai praktik keamanan.

## Kontak / Referensi
- File SQL ada di folder `db/` (lihat `db/create_users.sql`, `db/create_banner_up.sql`, `db/create_audit_triggers.sql`).
- File konfigurasi koneksi biasanya di `admin/System/kon.php`.

---

Jika Anda mau, saya dapat:
- Membuat file contoh konfigurasi `.env.example` atau `kon.php.example`.
- Menambahkan skrip instalasi otomatis (PHP/CLI) untuk membantu import SQL dan konfigurasi.

Silakan beri tahu pilihan Anda.
# adPanel

Admin panel untuk manajemen produk heavy equipment spare parts. Project ini menggunakan PHP/MySQL dengan Bootstrap untuk frontend.

## Fitur Utama

- **Halaman Utama (index.php)**: Daftar produk dengan pencarian, filter brand/category, pagination, carousel banner dan logo.
- **Detail Produk (webpage/product_detail.php)**: Tampilan detail produk berdasarkan part number.
- **Dashboard Admin (admin/menu_admin.php)**: Interface admin dengan sidebar menu.
- **Manajemen Produk**: Tambah, edit, lihat, hapus produk (product/add_prod.php, edit_prod.php, dll.).
- **Interchange**: Tambah, edit, list interchange PN dan produk (folder itc/).
- **Banner & Logo**: Kelola banner produk dan brand (banner_product.php, banner_brand.php).
- **Import/Export**: Import produk dari CSV/Excel, dengan deteksi duplicate.
- **Otorisasi**: Role-based access (A: Admin, B: Owner, C: Dev).
- **Search**: Pencarian produk, redirect ke detail jika exact part number.

## Struktur File

### Root Files
- `index.php`: Halaman utama produk.
- `composer.json`: Dependencies PHP.
- `docker-compose.yml`: Setup Docker.
- `README.md`: Dokumentasi ini.
- `LICENSE`: Lisensi MIT.
- `maintenance.html`: Halaman maintenance.
- `robots.txt`, `sitemap.xml`: SEO.

### Folder
- **admin/**: File admin (dashboard, login, create_admin, header/footer).
- **assets/**: CSS, images.
- **conn/**: Koneksi DB (kon.php).
- **db/**: SQL scripts (create_users.sql, dll.).
- **itc/**: Interchange management (add, edit, list, view).
- **product/**: Produk management (add, edit, view, del, report).
- **webpage/**: Header, footer, product_detail.

## Dependencies

- PHP 7.4+
- MySQL
- PhpSpreadsheet (untuk import Excel): `composer require phpoffice/phpspreadsheet`

## Instalasi

# adPanel

adPanel adalah admin panel sederhana untuk manajemen produk spare parts heavy equipment dan truck. Aplikasi ini dibangun dengan PHP (server-rendered), MySQL untuk penyimpanan data, dan Bootstrap untuk layout frontend.

## Ringkasan

- Tipe: PHP + MySQL (server-rendered)
- Tujuan: CRUD produk, manajemen banner/logo, import produk dari CSV/Excel, dan fitur interchange (ITC)
- Target lingkungan: XAMPP (Windows) atau LAMP stack

## Fitur Utama

- Halaman utama (`index.php`): daftar produk, pencarian, filter, pagination, banner/logo carousel.
- Detail produk (`webpage/pages/product_detail.php`): tampilan detail berdasarkan part number.
- Dashboard Admin (`admin/`): login, dashboard, manajemen produk, banner, dan interchange.
- Import/Export: import CSV/Excel via PhpSpreadsheet.
- Role-based access control: peran untuk admin/owner/dev.

## Perubahan & Catatan Terbaru (dikerjakan)

Berikut perubahan yang baru saja diterapkan pada cabang kerja lokal:

- Header diperbaiki dan dirapikan: `webpage/includes/header.php` (struktur grid, label aksesibilitas pada search).
- Meta SEO & Open Graph ditambahkan pada `index.php` dan placeholder OG dibuat di `webpage/img/og-home.svg`.
- Hero section ditambahkan ke `index.php` (H1 tunggal dipertahankan untuk aksesibilitas).
- Banner promo dapat ditutup/dismiss dengan persistensi `localStorage` dan sekarang tampil sebagai bar non-sticky di bagian bawah halaman.
- Perbaikan aksesibilitas ringan: tambahkan `aria-label` pada input search dan ubah heading duplikat (H1 → H2).

Catatan penting: beberapa gambar (banner, logo, produk) saat ini disimpan sebagai BLOB di database; pertimbangkan migrasi ke file-based assets (web/ files) untuk caching dan optimasi performa.

## Struktur Proyek (ringkas)

- `index.php` — Halaman utama dan entry.
- `admin/` — Panel admin (login, dashboard, aksi CRUD).
- `webpage/` — Template frontend, includes, halaman produk.
- `db/` — Skrip SQL untuk membuat tabel dasar.
- `docs/` — Dokumen internal (presentasi, checklist, draft konten).

## Instalasi & Jalankan (Quickstart)

1. Pastikan XAMPP atau PHP + MySQL terpasang.
2. Letakkan proyek di `htdocs` (mis. `e:\XAMPP_64\htdocs\adpanel`).
3. Jalankan Composer jika diperlukan:

```powershell
composer install
```

4. Buat database dan import skrip di folder `db/` (contoh: `create_users.sql`).
5. Sesuaikan konfigurasi koneksi di `webpage/System/kon.php` jika perlu.
6. Akses aplikasi melalui browser: `http://localhost/adpanel/`.

## Konfigurasi

File koneksi utama ada di `webpage/System/kon.php`. Sesuaikan nilai berikut sesuai lingkungan Anda:

- `DB_HOST` (biasanya `localhost` atau service name untuk Docker)
- `DB_NAME` (nama database yang dibuat untuk adPanel)
- `DB_USER` dan `DB_PASS` (user database)

Contoh pengaturan (di environment local):

```php
// webpage/System/kon.php (contoh)
$dbHost = 'localhost';
$dbName = 'adpanel_db';
$dbUser = 'adpanel_user';
$dbPass = 's3cret';
$pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
```

Pastikan file tidak dapat diakses publik selain melalui include PHP, dan simpan kredensial sensitif di luar kontrol versi saat production.

## Docker (opsional)

Proyek menyertakan `docker-compose.yml` sebagai contoh untuk menjalankan aplikasi dengan PHP + MySQL. Contoh langkah cepat:

1. Periksa `docker-compose.yml` dan sesuaikan environment variables untuk database.
2. Jalankan kontainer:

```bash
docker compose up -d --build
```

3. Setelah kontainer aktif, import skrip SQL (lihat bagian SQL Setup) atau akses web pada port yang dikonfigurasi.

Untuk development, mount volume kode lokal agar perubahan langsung terlihat di container. Pada production, gunakan image yang dibangun, set environment variables via secret manager, dan jangan mount source secara langsung.

## SQL Setup

Semua skrip SQL dasar ada di folder `db/`. Langkah membuat database lokal menggunakan MySQL CLI:

```bash
# buat database
mysql -u root -p -e "CREATE DATABASE adpanel_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
# import schema
mysql -u root -p adpanel_db < db/create_users.sql
# import script tambahan jika perlu
mysql -u root -p adpanel_db < db/create_banner_up.sql
mysql -u root -p adpanel_db < db/create_audit_triggers.sql
```

Atau gunakan phpMyAdmin / MySQL Workbench untuk import file-file `.sql` dari folder `db/`.

Jika Anda menggunakan Docker, jalankan perintah import dari dalam service MySQL:

```bash
docker compose exec db-container-name sh -c 'mysql -u root -p"$MYSQL_ROOT_PASSWORD" adpanel_db < /path/in/container/db/create_users.sql'
```

## Checklist Deploy

Sebelum memindahkan ke production, ikuti checklist singkat ini:

- Backup: buat backup DB dan file konfigurasi.
- Konfigurasi: set kredensial DB, base URL, dan secrets di environment (jangan commit ke git).
- Permissions: pastikan folder upload (jika ada) dapat ditulis oleh webserver, tetapi tidak world-writable.
- Assets: optimalkan gambar (WebP/AVIF), set header cache, dan gunakan CDN bila perlu.
- Migrate images: jika saat ini gambar disimpan sebagai BLOB, rencanakan migrasi ke filesystem dan update template.
- HTTPS: pasang sertifikat TLS dan paksa redirect HTTP → HTTPS.
- Caching: aktifkan opcode cache (OPcache) dan pertimbangkan reverse proxy (Varnish) jika diperlukan.
- Cron & queue: atur cron jobs untuk tugas background (jika ada) dan pastikan queue worker berjalan.
- Security: nonaktifkan display_errors, set proper `session.cookie_secure`, `session.cookie_httponly`.
- Audit: jalankan Lighthouse dan axe/Pa11y setelah deploy, perbaiki issue kritis.
- Monitoring & Logs: setup log rotation dan monitoring (process, DB, disk).

## Catatan Konfigurasi Tambahan

- Jika ingin mengubah base URL atau path publik, periksa `index.php` dan includes yang membentuk links.
- Untuk environment production, simpan konfigurasi sensitif di variables lingkungan atau file config yang tidak di-commit.
- Jika memindahkan gambar dari BLOB ke filesystem, pastikan sync dan fallback masih bekerja untuk data lama.
## Menjalankan Audit Aksesibilitas & SEO (lokal)

Contoh perintah yang direkomendasikan untuk audit manual/CI:

- Lighthouse (Chrome) — buka `index.php` → Lighthouse → run.
- Pa11y CLI (install melalui npm):

```bash
npm install -g pa11y
pa11y http://localhost/adpanel/index.php
```

- axe-core + Puppeteer (contoh skrip CI) — gunakan `axe-core` npm package bersama Puppeteer untuk capture report.

Simpan hasil audit (HTML/JSON) dan bagi file report jika memerlukan bantuan untuk prioritisasi perbaikan.

## Tips Pengembangan

- Untuk performa: migrasikan images dari DB BLOB → file system, lalu sediakan WebP/AVIF dan header caching.
- Pisahkan komponen UI berulang (product card) ke partial di `webpage/includes/`.
- Gunakan prepared statements untuk semua query input pengguna (sudah diterapkan sebagian di search redirect).

## Kontribusi

1. Fork/clone repository.
2. Buat branch fitur: `feature/your-change`.
3. Buka pull request dengan deskripsi perubahan.

## Lisensi

MIT — lihat berkas `LICENSE`.

---

Dokumentasi ini dibuat/diupdate cepat untuk mendukung proses review dan audit. Jika ingin saya tambahkan bagian spesifik (contoh: instruksi Docker, contoh SQL setup, atau checklist deploy), beri perintah dan saya akan tambahkan.

