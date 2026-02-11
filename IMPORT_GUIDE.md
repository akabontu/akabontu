# Excel/CSV Import Guide - adPanel

## 📊 Import Produk dari Excel/CSV

Fitur import memungkinkan Anda menambahkan banyak produk sekaligus dari file Excel (.xlsx, .xls) atau CSV.

### � Download Template

Sebelum import, download template yang sudah disiapkan:

- **CSV Template**: Klik tombol "CSV Template" di halaman import
- **Excel Template**: Klik tombol "Excel Template" di halaman import

Template sudah berisi contoh data yang bisa Anda edit sesuai kebutuhan.

### �📋 Format File

File harus memiliki kolom dalam urutan berikut:
1. **Part Number** - Nomor part (wajib, unik)
2. **ITC** - Interchange code
3. **Description** - Deskripsi produk
4. **Brand** - Merk (Komatsu, Caterpillar, Volvo, dll.)
5. **Category** - Kategori (Engine, Filter, Brake System, dll.)
6. **Qty** - Quantity (angka)

### 📝 Contoh Format

#### CSV Format:
```csv
Part Number,ITC,Description,Brand,Category,Qty
12345-67890,ITC001,Engine Oil Filter,Komatsu,Filter,50
23456-78901,ITC002,Brake Pad Set,Caterpillar,Brake System,25
```

#### Excel Format:
Baris pertama bisa berisi header atau langsung data. Sistem akan otomatis mendeteksi header.

### 🎯 Cara Menggunakan

1. **Login ke Admin Panel**
   - Akses: `http://localhost:8000/admin/`
   - Username: `admin`, `owner`, atau `root`
   - Password: `admin123`, `owner123`, atau `root123`

2. **Masuk ke Menu Product**
   - Klik menu "Product" di sidebar
   - Pilih "Tambah Product"

3. **Download Template**
   - Scroll ke bagian "Import Produk dari CSV/Excel"
   - Klik "CSV Template" atau "Excel Template"
   - File akan terdownload otomatis

4. **Edit Template**
   - Buka file yang didownload
   - Isi data produk sesuai format
   - Simpan file

5. **Upload dan Import**
   - Klik "Choose File" dan pilih file yang sudah diedit
   - Klik "Import Data"
   - Sistem akan memproses dan menampilkan hasil
   - Akses: `http://localhost:8000/admin/`
   - Gunakan kredensial: admin/admin123

2. **Pilih Menu Product**
   - Klik "Product" di sidebar
   - Klik tombol "Tambah Product"

3. **Download Template** (Opsional)
   - Klik "CSV Template" atau "Excel Template" untuk download contoh file

4. **Pilih File Import**
   - Klik "Choose File" di bagian "Import Produk dari CSV/Excel"
   - Pilih file CSV atau Excel Anda

5. **Import Data**
   - Klik tombol "Import"
   - Sistem akan memproses dan menampilkan hasil

### ✅ Validasi & Aturan

- **Part Number**: Wajib diisi dan harus unik
- **Brand**: Jika kosong atau tidak valid → otomatis "Other"
- **Category**: Jika kosong atau tidak valid → otomatis "Other"
- **Qty**: Jika kosong → default 0
- **Duplicate**: Part number yang sudah ada akan dilewati

### 📊 Brand yang Didukung
- Komatsu, Bomag, Caterpillar, Scania, Volvo, Hyva, Other

### 🏷️ Category yang Didukung
- Engine, Electrical, Brake System, Cylinder, Axle & Stering
- Cabin, Filter, Attachment, Final Drive, Hydraulic System

### ⚠️ Catatan Penting

- **Header Detection**: Sistem otomatis mendeteksi baris header
- **Error Handling**: Import akan dilanjutkan meski ada error pada beberapa baris
- **ITC Table**: Data ITC otomatis dimasukkan ke tabel `itc_pn`
- **File Size**: Tidak ada batasan ukuran file
- **Performance**: Import besar mungkin memakan waktu

### 🔍 Troubleshooting

**Error: "PhpSpreadsheet tidak terpasang"**
- Jalankan: `composer install` di root project

**Error: "Part number sudah ada"**
- Hapus atau ubah part number yang duplikat

**Import gagal tanpa pesan error**
- Periksa format file (CSV dengan delimiter koma)
- Pastikan kolom dalam urutan yang benar

### 📁 File Template
- `admin/Control/product/Data/sample_import.csv`
- `admin/Control/product/Data/sample_import.xlsx`

### 💡 Tips
- Gunakan Excel untuk data kompleks dengan formatting
- Gunakan CSV untuk data sederhana dan cepat
- Backup database sebelum import besar
- Test import dengan data sedikit dulu