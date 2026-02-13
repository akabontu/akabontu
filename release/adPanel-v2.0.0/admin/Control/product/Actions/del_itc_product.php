<?php
// Delete a single itc_product row by `no` with role-based guard
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../../../System/kon.php';

// Require login
if (empty($_SESSION['logged_in'])) {
    header('Location: ../admin/login.php');
    exit;
}

if (!isset($_GET['no'])) {
    header('Location: /adpanel/admin/System/action/menu_admin.php?menu=itc_product');
    exit;
}

$no = intval($_GET['no']);
if ($no <= 0) {
    header('Location: /adpanel/admin/System/action/menu_admin.php?menu=itc_product');
    exit;
}

$stmt = mysqli_prepare($kon, 'DELETE FROM itc_product WHERE `no` = ?');
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $no);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

header('Location: /adpanel/admin/System/action/menu_admin.php?menu=itc_product');
exit;
?>
