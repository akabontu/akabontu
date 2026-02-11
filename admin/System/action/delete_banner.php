<?php
require_once __DIR__ . '/../kon.php';

if (isset($_GET['brand'])) {
    $brand = mysqli_real_escape_string($kon, $_GET['brand']);
    $query = "DELETE FROM banner_up WHERE brand = '$brand'";
    if (mysqli_query($kon, $query)) {
        header('Location: menu_admin.php?menu=banner_product');
        exit;
    } else {
        echo 'Error deleting banner.';
    }
} else {
    header('Location: menu_admin.php?menu=banner_product');
    exit;
}
?>