# Simple PowerShell script to create release ZIP for adPanel v2.0
# No complex functions, just straightforward operations

$VERSION = "2.0.0"
$SOURCE = $PSScriptRoot
$TEMP_DIR = Join-Path $env:TEMP "adPanel-v$VERSION"
$ZIP_FILE = Join-Path $SOURCE "adPanel-v$VERSION.zip"

Write-Host "Creating adPanel v$VERSION Release Package..." -ForegroundColor Cyan
Write-Host ""

# Remove old files
if (Test-Path $ZIP_FILE) {
    Remove-Item $ZIP_FILE -Force
    Write-Host "Old ZIP file removed" -ForegroundColor Yellow
}

if (Test-Path $TEMP_DIR) {
    Remove-Item $TEMP_DIR -Recurse -Force
}

# Create temp directory
New-Item -ItemType Directory -Path $TEMP_DIR -Force | Out-Null
Write-Host "Created temporary directory: $TEMP_DIR" -ForegroundColor Green

# Copy all files using robocopy (excludes specified folders/files)
Write-Host "Copying files..." -ForegroundColor Yellow

$excludeDirs = @('.git', '.vscode', '.venv', 'node_modules', '.idea', 'release')
$excludeFiles = @('*.log', '*.cache', '*.tmp', 'kon.php')

$robocopyArgs = @(
    $SOURCE,
    $TEMP_DIR,
    '/E',           # Copy subdirectories including empty ones
    '/NFL',         # No file list
    '/NDL',         # No directory list
    '/NJH',         # No job header
    '/NJS',         # No job summary
    '/nc',          # No class
    '/ns',          # No size
    '/np',          # No progress
    '/XD',          # Exclude directories
    $excludeDirs,
    '/XF',          # Exclude files
    $excludeFiles,
    '*.zip',
    '*.bak',
    'create_release_fixed.ps1'
)

$result = & robocopy @robocopyArgs 2>&1
Write-Host "Files copied successfully" -ForegroundColor Green

# Verify critical files exist in temp
Write-Host ""
Write-Host "Verifying critical files..." -ForegroundColor Yellow

$criticalFiles = @(
    "README.md",
    "INSTALL.md",
    "SECURITY.md",
    "RELEASE_NOTES.md",
    "CHANGELOG.md",
    "LICENSE",    "composer.json",
    "index.php"
)

$allExist = $true
foreach ($file in $criticalFiles) {
    $path = Join-Path $TEMP_DIR $file
    if (Test-Path $path) {
        Write-Host "  OK: $file" -ForegroundColor Green
    } else {
        Write-Host "  MISSING: $file" -ForegroundColor Red
        $allExist = $false
    }
}

if (-not $allExist) {
    Write-Host ""
    Write-Host "ERROR: Some files are missing. Please check." -ForegroundColor Red
    exit 1
}

# Create ZIP file
Write-Host ""
Write-Host "Creating ZIP archive..." -ForegroundColor Yellow

Add-Type -AssemblyName System.IO.Compression.FileSystem
[System.IO.Compression.ZipFile]::CreateFromDirectory($TEMP_DIR, $ZIP_FILE, 'Optimal', $false)

# Cleanup
Remove-Item $TEMP_DIR -Recurse -Force

# Report
$size = (Get-Item $ZIP_FILE).Length / 1MB
$sizeRounded = [math]::Round($size, 2)

Write-Host ""
Write-Host "================================================" -ForegroundColor Cyan
Write-Host "  RELEASE PACKAGE CREATED SUCCESSFULLY!" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "File: $ZIP_FILE" -ForegroundColor White
Write-Host "Size: $sizeRounded MB" -ForegroundColor White
Write-Host "Version: $VERSION" -ForegroundColor White
Write-Host ""

# Calculate hash
$hash = (Get-FileHash $ZIP_FILE -Algorithm SHA256).Hash
Write-Host "SHA256: $hash" -ForegroundColor Cyan
Write-Host ""
Write-Host "Release ready for distribution!" -ForegroundColor Green
