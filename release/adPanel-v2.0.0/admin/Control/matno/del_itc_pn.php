<?php
// Deletion endpoint for itc_pn rows — enforce authentication and role-based authorization
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../../System/kon.php';

// must be logged in
if (empty($_SESSION['logged_in'])) {
    header('Location: ../admin/login.php'); exit;
}

// only Owner (B) and Dev (C) may delete
$role = $_SESSION['role'] ?? 'A';
if (!in_array($role, ['B','C'])) {
    header('Location: /adpanel/admin/?menu=itc_pn'); exit;
}

if (!isset($_GET['no'])) { header('Location: /adpanel/admin/?menu=itc_pn'); exit; }
$no = intval($_GET['no']);
if ($no <= 0) { header('Location: /adpanel/admin/?menu=itc_pn'); exit; }

$stmt = mysqli_prepare($kon, "DELETE FROM itc_pn WHERE `no` = ?");
mysqli_stmt_bind_param($stmt, 'i', $no);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: /adpanel/admin/System/action/admin_dashboard.php?menu=itc_pn');
exit;
?>
