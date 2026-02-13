# adPanel v2.0 - Release Notes

**Release Date:** February 13, 2026  
**Version:** 2.0.0  
**Codename:** Production Ready

---

## 🎉 Highlights

adPanel v2.0 adalah versi production-ready dari admin panel manajemen spare parts heavy equipment. Release ini fokus pada stabilitas, keamanan, dan dokumentasi lengkap untuk deployment.

## ✨ What's New

### 📚 Documentation
- **README.md** yang komprehensif dengan panduan instalasi cepat
- **SECURITY.md** - Panduan keamanan lengkap untuk production deployment
- **INSTALL.md** - Panduan instalasi step-by-step untuk berbagai environment
- **LICENSE_ID_v2.0.md** - Terjemahan lisensi dalam Bahasa Indonesia
- Dokumentasi internal di folder `docs/` untuk stakeholder presentation dan planning

### 🔒 Security Enhancements
- File permissions dan proteksi folder sensitif dengan `.htaccess`
- Script automation untuk set permissions (Linux & Windows)
- Proteksi file konfigurasi database (`kon.php`)
- Upload folder security dengan validasi file type
- Security headers dan best practices implementation
- Prevention untuk directory listing dan file exposure

### 🛠️ Core Features
- **Product Management** - CRUD operasi lengkap untuk produk spare parts
- **Stock Management** - Tracking stock IN/OUT dengan history transaksi
- **Interchange Data** - Manajemen data part number interchange
- **Banner Management** - Kelola banner homepage untuk brand dan produk
- **Excel/CSV Import** - Import bulk product data dari file spreadsheet
- **Role-based Access** - User management dengan level akses berbeda
- **Search & Filter** - Advanced search dan filtering di katalog produk
- **Responsive Design** - UI yang responsif untuk desktop dan mobile

### 🗄️ Database
- SQL schema dengan audit triggers
- Optimized indexes untuk performa query
- Sample data untuk testing
- Backup/restore procedures

### 🔧 Technical Stack
- PHP 7.4+ compatible
- MySQL/MariaDB database
- Apache web server (XAMPP support)
- Composer dependency management
- PhpSpreadsheet library untuk Excel operations
- Bootstrap-based responsive UI

## 📦 Package Contents

```
adPanel-v2.0/
├── admin/                      # Admin panel backend
│   ├── Control/               # CRUD controllers
│   │   ├── product/          # Product management
│   │   ├── stock/            # Stock management
│   │   └── matno/            # Interchange data
│   ├── System/               # System core
│   │   ├── kon.php.example   # Database config template
│   │   └── action/           # Core actions
│   └── assets/               # Admin assets
├── webpage/                   # Public frontend
│   ├── pages/                # Frontend pages
│   ├── includes/             # Common includes
│   └── css/                  # Stylesheets
├── db/                       # Database files
│   ├── adpaneldb.sql        # Main database schema
│   └── *.sql                # Additional SQL scripts
├── docs/                     # Documentation
├── vendor/                   # Composer dependencies
├── index.php                 # Homepage entry
├── install.php              # Installation wizard
├── README.md                # Main documentation
├── SECURITY.md              # Security guide
├── INSTALL.md               # Installation guide
├── CHANGELOG.md             # Version history
├── LICENSE                  # MIT License
└── composer.json            # PHP dependencies
```

## 🚀 Installation Quick Start

### Requirements
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache with mod_rewrite
- Composer
- PHP Extensions: mysqli/pdo_mysql, zip, mbstring, gd

### Basic Installation

1. **Extract Files**
   ```bash
   unzip adPanel-v2.0.zip -d /var/www/html/adPanel
   cd /var/www/html/adPanel
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Database Setup**
   ```bash
   mysql -u root -p -e "CREATE DATABASE adpanel"
   mysql -u root -p adpanel < db/adpaneldb.sql
   ```

4. **Configure Database**
   ```bash
   cp admin/System/kon.php.example admin/System/kon.php
   # Edit kon.php dengan kredensial database Anda
   ```

5. **Set Permissions**
   ```bash
   # Linux
   bash set_permissions.sh
   
   # Windows (as Administrator)
   .\set_permissions_windows.ps1
   ```

6. **Access Application**
   - Frontend: `http://localhost/adPanel/`
   - Admin: `http://localhost/adPanel/admin/`

📖 **Untuk panduan instalasi lengkap, lihat [INSTALL.md](INSTALL.md)**

## 🔐 Security Notes

⚠️ **PENTING untuk Production:**

1. **Database Configuration**
   - Ganti kredensial database default
   - Gunakan user database khusus (bukan root)
   - Password minimal 16 karakter

2. **PHP Configuration**
   - Set `display_errors = Off`
   - Enable error logging
   - Disable dangerous functions

3. **File Permissions**
   - `kon.php` harus 600 (rw-------)
   - PHP files 644 (rw-r--r--)
   - Directories 755 (rwxr-xr-x)
   - Upload folders 775 dengan proper ownership

4. **Web Server**
   - Enable HTTPS/SSL
   - Configure security headers
   - Disable directory listing
   - Proteksi folder sensitif dengan .htaccess

5. **Backup**
   - Setup automated database backup
   - Test restore procedures
   - Keep backup off-site

📖 **Untuk panduan keamanan lengkap, lihat [SECURITY.md](SECURITY.md)**

## 🐛 Known Issues & Limitations

### Known Issues
- None reported in this release

### Limitations
- Single language support (Indonesian)
- No built-in email notifications
- Manual backup required

### Browser Support
- Chrome/Edge 90+
- Firefox 88+
- Safari 14+

## 📝 Upgrade Notes

### From v1.x to v2.0

Ini adalah major release yang memerlukan fresh installation. Tidak ada direct upgrade path dari v1.x.

**Migration Steps:**
1. Backup database v1.x
2. Export data yang diperlukan
3. Fresh install v2.0
4. Import data dari v1.x (manual atau script)

## 🔄 Changelog Summary

### Added
- Comprehensive documentation (README, SECURITY, INSTALL)
- Security hardening with .htaccess protection
- Permission management scripts
- Enhanced admin dashboard
- Stock transaction history
- Interchange data management
- Product image upload validation
- Session security improvements

### Changed
- Updated database schema with audit triggers
- Improved UI/UX for admin panel
- Better error handling and logging
- Optimized database queries
- Enhanced search functionality

### Fixed
- SQL injection vulnerabilities
- XSS prevention
- CSRF protection
- Upload security issues
- Session management issues

### Security
- File permission best practices
- Configuration file protection
- Upload folder security
- SQL injection prevention
- XSS/CSRF protection
- Session security hardening

## 🤝 Support & Community

### Documentation
- README: `/README.md`
- Security Guide: `/SECURITY.md`
- Installation Guide: `/INSTALL.md`
- Changelog: `/CHANGELOG.md`

### Issues & Bug Reports
- GitHub Issues: [github.com/akabontu/akabontu/issues](https://github.com/akabontu/akabontu/issues)

### Contributing
Contributions are welcome! Please read the LICENSE file for terms.

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

Terjemahan Bahasa Indonesia tersedia di [LICENSE_ID_v2.0.md](LICENSE_ID_v2.0.md) (hanya referensi, versi legal adalah LICENSE).

## 🙏 Acknowledgments

- PHPSpreadsheet untuk Excel import/export functionality
- Bootstrap untuk responsive UI framework
- Open source community

## 📞 Contact

For questions, suggestions, or support, please use GitHub Issues.

---

**Thank you for using adPanel v2.0!** 🚀

We hope this release helps you manage your spare parts inventory efficiently and securely.
