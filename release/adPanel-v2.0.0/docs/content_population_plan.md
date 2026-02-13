# Rencana Pengisian Konten

Tujuan
- Menyusun dan memasukkan semua konten final (copy, gambar, metadata) sehingga frontend dapat diisi dan diuji.

Prioritas Halaman
- Homepage — hero, fitur utama, CTA.
- Daftar Produk — kategori, filter singkat, thumbnail.
- Detail Produk — deskripsi lengkap, spesifikasi, galeri, CTA beli/kontak.
- Halaman Bantuan / Kontak — form, FAQ.

Langkah Kerja
1. Inventaris Konten: buat daftar semua teks dan aset gambar yang dibutuhkan per halaman.
2. Draft Copy: tulis copy final atau draft untuk tiap halaman; tandai placeholder untuk yang belum tersedia.
3. Optimasi Gambar: kompres, buat variant webp, dan tetapkan ukuran (thumbnail, besar).
4. Metadata & SEO: siapkan title, meta description, og:image, struktur heading.
5. Impor Produk: gunakan `webpage/product/Data/sample_import.csv` sebagai template untuk import massal.
6. Mapping ke Template: tentukan file template/partial mana yang menerima tiap konten (mis. header, hero, product card).
7. Review & Tanda Terima: minta stakeholder menyetujui copy final.

File & Folder yang Disarankan
- `webpage/pages/` — tempat halaman statis.
- `webpage/includes/` — partials (hero, product-card, footer).
- `product/Data/` — file CSV untuk import produk.

Waktu & Deliverable
- Inventaris awal: 1 hari
- Draft copy + gambar: 2–3 hari
- Import & mapping: 1 hari
- Review stakeholder: 1 hari

Catatan
- Saya bisa mulai dengan inventaris konten: sebutkan apakah Anda ingin saya fokus `homepage` atau `product pages` terlebih dahulu.
