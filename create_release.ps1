# PowerShell Script untuk Membuat Release ZIP adPanel v2.0
# Author: adPanel Development Team
# Date: February 2026

param(
    [string]$version = "2.0.0"
)

Write-Host "================================================" -ForegroundColor Cyan
Write-Host "  adPanel v$version - Release Package Creator" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

$sourceDir = $PSScriptRoot
$releaseDir = Join-Path $sourceDir "release"
$tempDir = Join-Path $releaseDir "adPanel-v$version"
$zipFile = Join-Path $sourceDir "adPanel-v$version.zip"

# Hapus release lama jika ada
if (Test-Path $releaseDir) {
    Write-Host "Cleaning old release directory..." -ForegroundColor Yellow
    Remove-Item $releaseDir -Recurse -Force
}

if (Test-Path $zipFile) {
    Write-Host "Removing old zip file..." -ForegroundColor Yellow
    Remove-Item $zipFile -Force
}

# Buat directory struktur
Write-Host "Creating release directory structure..." -ForegroundColor Yellow
New-Item -ItemType Directory -Path $tempDir -Force | Out-Null

# Daftar file/folder yang HARUS di-exclude
$excludeItems = @(
    '.git',
    '.gitignore',
    '.vscode',
    '.venv',
    '.dockerignore',
    'node_modules',
    '.idea',
    '.DS_Store',
    'Thumbs.db',
    '*.log',
    'admin\System\kon.php',  # Exclude kon.php actual, tapi include kon.php.example
    'release',
    'create_release.ps1',
    'create_release.sh',
    '*.zip',
    '*.bak',
    '*.tmp',
    '*.cache'
)

# Copy semua file kecuali yang di-exclude
Write-Host "Copying files to release directory..." -ForegroundColor Yellow

# Function untuk check apakah file/folder harus di-exclude
function Should-Exclude {
    param($path, $excludeList)
    
    $relativePath = $path.Replace($sourceDir, '').TrimStart('\')
    
    foreach ($pattern in $excludeList) {
        if ($relativePath -like "*$pattern*") {
            return $true
        }
        if ($path.Name -eq $pattern) {
            return $true
        }
    }
    return $false
}

# Copy files with exclusions
Get-ChildItem -Path $sourceDir -Recurse -Force | ForEach-Object {
    $item = $_
    
    # Skip jika di exclude list
    if (Should-Exclude $item.FullName $excludeItems) {
        return
    }
    
    # Calculate relative path
    $relativePath = $item.FullName.Substring($sourceDir.Length + 1)
    $targetPath = Join-Path $tempDir $relativePath
    
    if ($item.PSIsContainer) {
        # Create directory
        if (-not (Test-Path $targetPath)) {
            New-Item -ItemType Directory -Path $targetPath -Force | Out-Null
        }
    } else {
        # Copy file
        $targetDir = Split-Path $targetPath -Parent
        if (-not (Test-Path $targetDir)) {
            New-Item -ItemType Directory -Path $targetDir -Force | Out-Null
        }
        Copy-Item $item.FullName $targetPath -Force
    }
}

Write-Host "✓ Files copied successfully" -ForegroundColor Green
Write-Host ""

# Verify critical files are included
Write-Host "Verifying critical files..." -ForegroundColor Yellow

$criticalFiles = @(
    "README.md",
    "INSTALL.md",
    "SECURITY.md",
    "RELEASE_NOTES.md",
    "CHANGELOG.md",
    "LICENSE",
    "LICENSE_ID_v2.0.md",
    "composer.json",
    "index.php",
    "install.php",
    "admin\System\kon.php.example",
    "set_permissions.sh",
    "set_permissions_windows.ps1",
    "db\adpaneldb.sql"
)

$missingFiles = @()
foreach ($file in $criticalFiles) {
    $fullPath = Join-Path $tempDir $file
    if (-not (Test-Path $fullPath)) {
        $missingFiles += $file
        Write-Host "  ✗ Missing: $file" -ForegroundColor Red
    } else {
        Write-Host "  ✓ $file" -ForegroundColor Green
    }
}

if ($missingFiles.Count -gt 0) {
    Write-Host ""
    Write-Host "ERROR: Some critical files are missing!" -ForegroundColor Red
    Write-Host "Please check the source directory and try again." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Creating release package..." -ForegroundColor Yellow

# Create ZIP file
try {
    # Use .NET compression
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    [System.IO.Compression.ZipFile]::CreateFromDirectory($tempDir, $zipFile, 'Optimal', $false)
    
    Write-Host "✓ ZIP file created successfully" -ForegroundColor Green
} catch {
    Write-Host "✗ Failed to create ZIP file: $_" -ForegroundColor Red
    exit 1
}

# Cleanup temp directory
Write-Host "Cleaning up temporary files..." -ForegroundColor Yellow
Remove-Item $releaseDir -Recurse -Force

# Get file size
$fileSize = (Get-Item $zipFile).Length / 1MB
$fileSizeMB = [math]::Round($fileSize, 2)

Write-Host ""
Write-Host "================================================" -ForegroundColor Cyan
Write-Host "  Release Package Created Successfully!" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Release File: $zipFile" -ForegroundColor White
Write-Host "File Size: $fileSizeMB MB" -ForegroundColor White
Write-Host "Version: $version" -ForegroundColor White
Write-Host ""

# Calculate file hash for verification
Write-Host "Calculating file hash..." -ForegroundColor Yellow
$hash = Get-FileHash $zipFile -Algorithm SHA256
Write-Host "SHA256: $($hash.Hash)" -ForegroundColor Cyan

Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Yellow
Write-Host "1. Test the release package by extracting and installing" -ForegroundColor White
Write-Host "2. Review RELEASE_NOTES.md for changelog" -ForegroundColor White
Write-Host "3. Upload to release distribution server" -ForegroundColor White
Write-Host "4. Update GitHub releases with the zip file" -ForegroundColor White
Write-Host ""
Write-Host "Release ready for distribution!" -ForegroundColor Green
