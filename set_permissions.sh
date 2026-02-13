#!/bin/bash
# Script untuk set file permissions adPanel
# Untuk Linux/Unix production server

echo "================================================"
echo "Setting File Permissions untuk adPanel"
echo "================================================"

# Warna untuk output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Set baseline permissions
echo -e "${YELLOW}Setting baseline permissions...${NC}"
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
echo -e "${GREEN}✓ Baseline permissions set${NC}"

# Proteksi file konfigurasi sensitif
echo -e "${YELLOW}Protecting sensitive configuration files...${NC}"
if [ -f "admin/System/kon.php" ]; then
    chmod 600 admin/System/kon.php
    echo -e "${GREEN}✓ admin/System/kon.php (600)${NC}"
fi

if [ -f "webpage/includes/site-config.php" ]; then
    chmod 600 webpage/includes/site-config.php
    echo -e "${GREEN}✓ webpage/includes/site-config.php (600)${NC}"
fi

# Folder upload - perlu write access
echo -e "${YELLOW}Setting upload folder permissions...${NC}"
if [ -d "admin/Control/product/img" ]; then
    chmod 775 admin/Control/product/img
    echo -e "${GREEN}✓ admin/Control/product/img/ (775)${NC}"
fi

if [ -d "admin/assets/images" ]; then
    chmod 775 admin/assets/images
    echo -e "${GREEN}✓ admin/assets/images/ (775)${NC}"
fi

# Proteksi file .htaccess
echo -e "${YELLOW}Setting .htaccess permissions...${NC}"
find . -name ".htaccess" -exec chmod 644 {} \;
echo -e "${GREEN}✓ All .htaccess files (644)${NC}"

# Set ownership (uncomment jika diperlukan)
# echo -e "${YELLOW}Setting ownership...${NC}"
# WEB_USER="www-data"  # Ganti sesuai user web server (www-data, apache, nginx, dll)
# chown -R $WEB_USER:$WEB_USER .
# echo -e "${GREEN}✓ Ownership set to $WEB_USER${NC}"

echo ""
echo "================================================"
echo -e "${GREEN}Permissions set successfully!${NC}"
echo "================================================"
echo ""

# Verifikasi file sensitif
echo "Verifying sensitive files:"
if [ -f "admin/System/kon.php" ]; then
    ls -lh admin/System/kon.php | awk '{print $1, $9}'
fi
if [ -f "webpage/includes/site-config.php" ]; then
    ls -lh webpage/includes/site-config.php | awk '{print $1, $9}'
fi

echo ""
echo "Verifying upload folders:"
if [ -d "admin/Control/product/img" ]; then
    ls -ldh admin/Control/product/img | awk '{print $1, $9}'
fi

echo ""
echo -e "${YELLOW}Note:${NC} Jika ada masalah write permission pada upload folder,"
echo "uncomment bagian 'Set ownership' di script ini dan jalankan dengan sudo."
