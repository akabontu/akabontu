<?php
// Admin entry point - redirects to login or dashboard based on authentication
// Configure session for localhost
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
session_start();

if (!empty($_SESSION['logged_in'])) {
    // User is logged in, redirect to dashboard
    header('Location: System/action/menu_admin.php');
    exit;
} else {
    // User not logged in, redirect to login
    header('Location: System/Index/login.php');
    exit;
}
?>