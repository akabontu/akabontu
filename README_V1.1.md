# Rencana Pengembangan — adPanel V1.1

Dokumen singkat ini menjabarkan scope dan prioritas pengembangan untuk versi 1.1.

Tujuan
- Tingkatkan pengalaman pemasangan & keamanan, tambahkan fitur kecil yang banyak diminta, dan siapkan basis untuk pengembangan berkelanjutan.

Scope (garis besar)
- Perbaikan instalasi & dokumentasi
- Autentikasi + manajemen pengguna lebih baik
- Peningkatan manajemen produk dan media
- Keamanan dan testing
- Packaging / distribusi

Fitur Prioritas (High)
- Installer non-interaktif lengkap (`--force`, `--sql`, validasi file SQL)
- Halaman login + manajemen user (role: admin, editor)
- Reset password via email (SMTP config contoh)
- Validasi upload file (ekstensi, ukuran, sanitasi nama)
- Perbaikan README + dokumentasi instalasi langkah-demi-langkah

Fitur Prioritas (Medium)
- API sederhana (JSON) untuk operasi CRUD produk (dengan token/CSRF)
- Pencarian & filter produk di panel admin
- Pagination untuk list produk
- Upload gambar otomatis resize/optimasi (opcional lib)

Fitur Prioritas (Low)
- Export/import produk CSV
- Tema admin ringan (improve CSS)
- Activity log admin sederhana (aksi CRUD penting)

Teknis / Refactor
- Pisahkan konfigurasi sensitif: dukung `.env` atau `kon.php` example lebih jelas
- Tambahkan script Composer (jika perlu) dan update `composer.json` sesuai dependensi baru
- Strukturkan folder `src/` jika mulai memisah logika (opsional)

Keamanan & QA
- Code input/output sanitization (prevent XSS/SQL injection)
- Gunakan prepared statements (mysqli prepared / PDO) untuk query sensitif
- Tambahkan basic unit/integration tests (PHPUnit) untuk fungsi kritis
- Static analysis / linter (phpcs / phpstan) rekomendasi

Dokumentasi & Packaging
- Update `README.md` utama dengan panduan upgrade dari V1.0 ke V1.1
- Update `releases/CHECKSUMS.txt` dan `RELEASE_NOTES.txt` saat rilis
- Tambah `CHANGELOG.md` dan `UPGRADE.md`

Milestone & Estimasi (kasar)
- M1 (1–3 hari): perbaikan installer, dokumentasi instalasi, contoh `kon.php`/`.env` (priority High)
- M2 (3–7 hari): login + manajemen user, reset password (High)
- M3 (3–5 hari): upload validation, product pagination, basic API (Medium)
- M4 (2–4 hari): testing, security hardening, packaging & release notes

Checklist Rilis V1.1
- Semua fitur High selesai dan diuji
- README + UPGRADE.md tersedia
- CHECKSUMS dan RELEASE_NOTES diperbarui
- Build ZIP minimal + full tersedia di `releases/`
- Smoke test manual (install fresh instance dari ZIP)

Next Steps (rekomendasi singkat)
1. Konfirmasi prioritas (apakah login/multi-user wajib di V1.1?)
2. Buat issue/branch per milestone (`v1.1/m1-installer`, dsb.)
3. Kerjakan M1 dulu, verifikasi dengan smoke-test, lalu lanjut M2.

Jika setuju, saya bisa membuat `UPGRADE.md`, issue list, dan branch skeleton untuk memulai pengembangan.
