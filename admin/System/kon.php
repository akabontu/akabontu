<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "adpaneldb";

$kon = mysqli_connect($host, $user, $pass, $db);

if (!$kon) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>