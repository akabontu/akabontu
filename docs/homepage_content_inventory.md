# Inventaris Konten — Homepage (sinkron dengan `index.php`)

Ringkasan
- Tujuan: menyusun semua teks, field database, dan aset yang nyata digunakan di `index.php` agar pengisian konten dan mapping ke template akurat.

Komponen & Field Konten (berdasarkan `index.php`)
- Header / Navigation
  - Logo file: `logo.svg` / `logo.png` (dipakai di `webpage/includes/header.php`)
  - Brand menu (dropdown per merk): fixed list (Komatsu, Bomag, Caterpillar, Scania, Volvo, Hyva, Other)
  - Category list (static): Engine, Electrical, Brake System, Cylinder, Axle & Stering, Cabin, Filter, Attachment, Final Drive, Hydraulic System
  - Search input: `query` param; behavior: if exact match on ITC/part_number → redirect to `product_detail.php`

- Banner Carousel (atas)
  - Sumber: table `banner_up` (field `image` as BLOB)
  - Display: base64 data URI currently created from BLOB
  - Controls: indicators, prev/next

- Product Grid / List
  - Query params handled: `brand`, `category`, `query`, `page`
  - Pagination: `productsPerPage = 10`, `page` param -> offset calculation
  - Product fields used/displayed (from `Product` table):
    - `part_number` (used for link to `product_detail.php`)
    - `brand`
    - `itc` (interchange)
    - `description` (rendered with nl2br)
    - `Qty` (stock)
    - `image` (BLOB rendered as data URI)
  - Card layout: image (or placeholder), overlay label, detail rows (Product, Part Number, Interchange, Description, Stock), WhatsApp button

- Logo Carousel (bawah)
  - Sumber: table `logo_brand` (field `logo_img` as BLOB)
  - Rendering: two logos per carousel slide

- Footer & Extras
  - Footer partial: `webpage/includes/footer.php`
  - Floating WhatsApp link: `https://wa.me/6281110108000` (image at `webpage/img/wa-logo.png`)
  - Marquee: hardcoded contact/company line (present at bottom of `index.php`)

Metadata / SEO
- Current title: `Magz Group` (hardcoded in `index.php`) — prepare `meta description`, `og:title`, `og:description`, `og:image` when populating content

Gambar & Penyimpanan
- Saat ini gambar disimpan di DB sebagai BLOB (banner_up.image, Product.image, logo_brand.logo_img) dan di-render inline via base64; recommended: export/convert to file-based assets (WebP + JPG fallback) for caching.
- Ukuran rekomendasi:
  - Banner: 1200–1920px wide (optimasi <200KB)
  - Product grid thumbnail: ~400×300

Mapping ke File/Template
- Entry point: `index.php`
- Partials: `webpage/includes/header.php`, `webpage/includes/footer.php`
- Detail target: `webpage/pages/product_detail.php`
- Suggested extraction: `webpage/includes/product-card.php` for product card reuse

Checklist Ketersediaan Konten (aktual)
- [ ] `banner_up` active banners (image BLOB) tersedia
- [ ] `logo_brand` active logos (logo_img BLOB) tersedia
- [ ] Minimal 10 produk terisi untuk halaman pertama
- [ ] Contoh part_number / itc ada untuk menguji redirect search
- [ ] WhatsApp number dan marquee text final

Catatan Implementasi & Perbaikan yang Disarankan
- `index.php` saat ini menyusun beberapa klausa SQL menggunakan interpolasi string untuk LIKE — pertimbangkan prepared statements to avoid injection risks for all user input.
- Ekstrak product card ke partial (`webpage/includes/product-card.php`) untuk menyederhanakan `index.php` dan memudahkan styling.
- Ganti inline base64 BLOB rendering dengan file URLs dan caching untuk performa.

Tindakan berikut yang bisa saya ambil sekarang:
- (A) Tulis draft `meta title` + `meta description` + 2 variasi hero/title untuk homepage, atau
- (B) Buat partial `webpage/includes/product-card.php` dan refactor `index.php` untuk menggunakannya.

