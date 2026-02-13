#!/bin/bash
# Bash Script untuk Membuat Release ZIP adPanel v2.0
# Author: adPanel Development Team
# Date: February 2026

VERSION=${1:-"2.0.0"}

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${CYAN}================================================${NC}"
echo -e "${CYAN}  adPanel v${VERSION} - Release Package Creator${NC}"
echo -e "${CYAN}================================================${NC}"
echo ""

SOURCE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
RELEASE_DIR="$SOURCE_DIR/release"
TEMP_DIR="$RELEASE_DIR/adPanel-v${VERSION}"
ZIP_FILE="$SOURCE_DIR/adPanel-v${VERSION}.zip"

# Cleanup old release
if [ -d "$RELEASE_DIR" ]; then
    echo -e "${YELLOW}Cleaning old release directory...${NC}"
    rm -rf "$RELEASE_DIR"
fi

if [ -f "$ZIP_FILE" ]; then
    echo -e "${YELLOW}Removing old zip file...${NC}"
    rm -f "$ZIP_FILE"
fi

# Create directory structure
echo -e "${YELLOW}Creating release directory structure...${NC}"
mkdir -p "$TEMP_DIR"

# Files/folders to exclude
EXCLUDE_PATTERNS=(
    ".git"
    ".gitignore"
    ".vscode"
    ".venv"
    ".dockerignore"
    "node_modules"
    ".idea"
    ".DS_Store"
    "Thumbs.db"
    "*.log"
    "admin/System/kon.php"
    "release"
    "create_release.ps1"
    "create_release.sh"
    "*.zip"
    "*.bak"
    "*.tmp"
    "*.cache"
)

# Build rsync exclude options
RSYNC_EXCLUDE=""
for pattern in "${EXCLUDE_PATTERNS[@]}"; do
    RSYNC_EXCLUDE="$RSYNC_EXCLUDE --exclude='$pattern'"
done

# Copy files with exclusions
echo -e "${YELLOW}Copying files to release directory...${NC}"

# Use rsync for efficient copying with exclusions
eval rsync -av \
    --exclude='.git' \
    --exclude='.gitignore' \
    --exclude='.vscode' \
    --exclude='.venv' \
    --exclude='.dockerignore' \
    --exclude='node_modules' \
    --exclude='.idea' \
    --exclude='.DS_Store' \
    --exclude='Thumbs.db' \
    --exclude='*.log' \
    --exclude='admin/System/kon.php' \
    --exclude='release' \
    --exclude='create_release.ps1' \
    --exclude='create_release.sh' \
    --exclude='*.zip' \
    --exclude='*.bak' \
    --exclude='*.tmp' \
    --exclude='*.cache' \
    "$SOURCE_DIR/" "$TEMP_DIR/" > /dev/null

echo -e "${GREEN}✓ Files copied successfully${NC}"
echo ""

# Verify critical files
echo -e "${YELLOW}Verifying critical files...${NC}"

CRITICAL_FILES=(
    "README.md"
    "INSTALL.md"
    "SECURITY.md"
    "RELEASE_NOTES.md"
    "CHANGELOG.md"
    "LICENSE"
    "LICENSE_ID_v2.0.md"
    "composer.json"
    "index.php"
    "install.php"
    "admin/System/kon.php.example"
    "set_permissions.sh"
    "set_permissions_windows.ps1"
    "db/adpaneldb.sql"
)

MISSING_FILES=()
for file in "${CRITICAL_FILES[@]}"; do
    if [ ! -f "$TEMP_DIR/$file" ]; then
        MISSING_FILES+=("$file")
        echo -e "  ${RED}✗ Missing: $file${NC}"
    else
        echo -e "  ${GREEN}✓ $file${NC}"
    fi
done

if [ ${#MISSING_FILES[@]} -gt 0 ]; then
    echo ""
    echo -e "${RED}ERROR: Some critical files are missing!${NC}"
    echo -e "${RED}Please check the source directory and try again.${NC}"
    exit 1
fi

echo ""
echo -e "${YELLOW}Creating release package...${NC}"

# Create ZIP file
cd "$RELEASE_DIR" || exit 1
zip -r "$ZIP_FILE" "adPanel-v${VERSION}" -q

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ ZIP file created successfully${NC}"
else
    echo -e "${RED}✗ Failed to create ZIP file${NC}"
    exit 1
fi

# Cleanup temp directory
echo -e "${YELLOW}Cleaning up temporary files...${NC}"
rm -rf "$RELEASE_DIR"

# Get file size
FILE_SIZE=$(du -h "$ZIP_FILE" | cut -f1)

echo ""
echo -e "${CYAN}================================================${NC}"
echo -e "${GREEN}  Release Package Created Successfully!${NC}"
echo -e "${CYAN}================================================${NC}"
echo ""
echo -e "${NC}Release File: $ZIP_FILE${NC}"
echo -e "${NC}File Size: $FILE_SIZE${NC}"
echo -e "${NC}Version: $VERSION${NC}"
echo ""

# Calculate file hash
echo -e "${YELLOW}Calculating file hash...${NC}"
if command -v sha256sum &> /dev/null; then
    HASH=$(sha256sum "$ZIP_FILE" | cut -d' ' -f1)
    echo -e "${CYAN}SHA256: $HASH${NC}"
elif command -v shasum &> /dev/null; then
    HASH=$(shasum -a 256 "$ZIP_FILE" | cut -d' ' -f1)
    echo -e "${CYAN}SHA256: $HASH${NC}"
fi

echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo -e "${NC}1. Test the release package by extracting and installing${NC}"
echo -e "${NC}2. Review RELEASE_NOTES.md for changelog${NC}"
echo -e "${NC}3. Upload to release distribution server${NC}"
echo -e "${NC}4. Update GitHub releases with the zip file${NC}"
echo ""
echo -e "${GREEN}Release ready for distribution! 🚀${NC}"
