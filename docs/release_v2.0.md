# Release Runbook — adPanel v2.0

Tanggal rilis: **2026-02-13**  
Versi: **2.0.0**

## Tujuan

Menetapkan langkah rilis minimum agar `adPanel v2.0` siap dipublikasikan ke environment production dengan risiko rendah.

## Scope Rilis

- Frontend katalog produk (`index.php`, folder `webpage/`).
- Backend admin (`admin/`) untuk manajemen produk, stock, banner, dan interchange.
- Dokumentasi teknis dan operasional (`README.md`, `CHANGELOG.md`, `docs/`).

## Pre-Release Checklist

- [ ] Konfigurasi database production sudah disiapkan.
- [ ] Kredensial pada `admin/System/kon.php` dan `webpage/includes/site-config.php` sudah sesuai production.
- [ ] Dependensi Composer berhasil terpasang (`composer install --no-dev --optimize-autoloader`).
- [ ] Import SQL sudah dieksekusi sesuai urutan pada dokumentasi.
- [ ] Hak akses file/folder upload dibatasi seperlunya (no world-writable).
- [ ] `display_errors` dimatikan di production.
- [ ] HTTPS aktif dan dapat diakses dari endpoint publik.
- [ ] Backup database baseline dibuat sebelum go-live.

## Langkah Go-Live

1. Aktifkan maintenance page jika diperlukan.
2. Deploy source code `main` terbaru.
3. Jalankan `composer install --no-dev --optimize-autoloader`.
4. Verifikasi koneksi DB dan kredensial aplikasi.
5. Jalankan smoke test:
   - Akses `http://<host>/`
   - Login admin `http://<host>/admin/`
   - Cek list produk, banner, stock transaction.
6. Matikan maintenance mode.

## Post-Release Validation

- [ ] Homepage load normal tanpa error PHP.
- [ ] Login/logout admin berfungsi.
- [ ] CRUD produk dapat menyimpan perubahan.
- [ ] Stock IN/OUT tercatat.
- [ ] Banner tampil di frontend sesuai konfigurasi.
- [ ] Tidak ada error kritis pada log Apache/PHP.

## Rollback Plan

Jika terjadi kegagalan kritis setelah rilis:

1. Aktifkan maintenance mode.
2. Restore source code ke snapshot rilis sebelumnya.
3. Restore database dari backup baseline (jika migrasi/data update menyebabkan isu).
4. Lakukan verifikasi smoke test pada versi rollback.
5. Dokumentasikan incident dan RCA sebelum jadwal rilis ulang.
