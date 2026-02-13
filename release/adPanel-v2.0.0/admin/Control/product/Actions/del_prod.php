<?php
// Deletion endpoint — enforce authentication and role-based authorization
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../../../System/kon.php';

if (!function_exists('delete_dir_recursive')) {
    function delete_dir_recursive(string $dir): void {
        if (!is_dir($dir)) return;
        $items = scandir($dir);
        if ($items === false) return;
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                delete_dir_recursive($path);
            } elseif (is_file($path)) {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}

// must be logged in
if (empty($_SESSION['logged_in'])) {
	header('Location: ../admin/login.php'); exit;
}

// only Owner (B) and Dev (C) may delete
$role = $_SESSION['role'] ?? 'A';
if (!in_array($role, ['B','C'])) {
	header('Location: /adpanel/admin/?menu=product'); exit;
}

if (!isset($_GET['no'])) { header('Location: /adpanel/admin/?menu=product'); exit; }
$no = intval($_GET['no']);
if ($no <= 0) { header('Location: /adpanel/admin/?menu=product'); exit; }

// First, get the part_number for cleanup in itc_pn table
$stmt = mysqli_prepare($kon, "SELECT part_number FROM Product WHERE `No` = ?");
mysqli_stmt_bind_param($stmt, 'i', $no);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $part_number);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Cleanup product images on disk and in DB
$rootDir = dirname(__DIR__, 4); // htdocs/adPanel
$imgPaths = [];
$stmt = mysqli_prepare($kon, "SELECT path FROM product_images WHERE product_no = ?");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $no);
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_bind_result($stmt, $imgPath);
        while (mysqli_stmt_fetch($stmt)) {
            $imgPaths[] = $imgPath;
        }
    }
    mysqli_stmt_close($stmt);
}

foreach ($imgPaths as $path) {
    $fs = $rootDir . '/' . ltrim((string)$path, '/');
    if (is_file($fs)) {
        @unlink($fs);
    }
}

$stmt = mysqli_prepare($kon, "DELETE FROM product_images WHERE product_no = ?");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $no);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

$productImgDir = $rootDir . '/admin/Control/product/img/' . $no;
delete_dir_recursive($productImgDir);


// Delete from Product table
$stmt = mysqli_prepare($kon, "DELETE FROM Product WHERE `No` = ?");
mysqli_stmt_bind_param($stmt, 'i', $no);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Also delete from itc_pn table if part_number exists
if (!empty($part_number)) {
    $stmt = mysqli_prepare($kon, "DELETE FROM itc_pn WHERE part_number = ?");
    mysqli_stmt_bind_param($stmt, 's', $part_number);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

header('Location: /adpanel/admin/System/action/menu_admin.php?menu=product');
exit;
?>