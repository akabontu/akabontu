# PowerShell Script untuk Set File Permissions adPanel di Windows
# Untuk XAMPP atau IIS di Windows

Write-Host "================================================" -ForegroundColor Cyan
Write-Host "Setting File Permissions untuk adPanel (Windows)" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

$rootPath = $PSScriptRoot

# Proteksi file konfigurasi sensitif
Write-Host "Protecting sensitive configuration files..." -ForegroundColor Yellow

$konFile = Join-Path $rootPath "admin\System\kon.php"
if (Test-Path $konFile) {
    try {
        # Hapus inheritance dan set permission hanya untuk SYSTEM dan Administrators
        icacls $konFile /inheritance:r /grant:r "SYSTEM:(F)" "Administrators:(F)" | Out-Null
        Write-Host "✓ admin/System/kon.php protected" -ForegroundColor Green
    } catch {
        Write-Host "✗ Failed to protect admin/System/kon.php" -ForegroundColor Red
    }
} else {
    Write-Host "⚠ admin/System/kon.php not found" -ForegroundColor Yellow
}

$siteConfigFile = Join-Path $rootPath "webpage\includes\site-config.php"
if (Test-Path $siteConfigFile) {
    try {
        icacls $siteConfigFile /inheritance:r /grant:r "SYSTEM:(F)" "Administrators:(F)" | Out-Null
        Write-Host "✓ webpage/includes/site-config.php protected" -ForegroundColor Green
    } catch {
        Write-Host "✗ Failed to protect webpage/includes/site-config.php" -ForegroundColor Red
    }
}

Write-Host ""

# Folder upload - perlu write access
Write-Host "Setting upload folder permissions..." -ForegroundColor Yellow

$uploadFolders = @(
    "admin\Control\product\img",
    "admin\assets\images"
)

foreach ($folder in $uploadFolders) {
    $folderPath = Join-Path $rootPath $folder
    if (Test-Path $folderPath) {
        try {
            # Berikan Write permission untuk Users
            icacls $folderPath /grant "Users:(OI)(CI)(M)" | Out-Null
            Write-Host "✓ $folder writable" -ForegroundColor Green
        } catch {
            Write-Host "✗ Failed to set permissions on $folder" -ForegroundColor Red
        }
    } else {
        Write-Host "⚠ $folder not found" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "================================================" -ForegroundColor Cyan
Write-Host "Permissions set successfully!" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

# Verifikasi
Write-Host "Verifying permissions:" -ForegroundColor Yellow
Write-Host ""

if (Test-Path $konFile) {
    Write-Host "admin/System/kon.php:" -ForegroundColor Cyan
    icacls $konFile | Select-String -Pattern "Administrators|SYSTEM" | ForEach-Object { Write-Host "  $_" }
}

Write-Host ""
Write-Host "Note: Di Windows/XAMPP, proteksi utama adalah:" -ForegroundColor Yellow
Write-Host "1. File .htaccess untuk mencegah akses langsung dari browser" -ForegroundColor White
Write-Host "2. Permission file kon.php dibatasi untuk Administrators saja" -ForegroundColor White
Write-Host "3. Folder upload memiliki write permission untuk aplikasi" -ForegroundColor White
