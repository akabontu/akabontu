<?php
if (php_sapi_name() !== 'cli') {
    echo "Script ini harus dijalankan via CLI.\n";
    exit(1);
}

function prompt($msg, $default = '') {
    echo $msg . ($default !== '' ? " [$default]" : '') . ': ';
    $line = trim(fgets(STDIN));
    return $line === '' ? $default : $line;
}

$base = __DIR__;

// Parse CLI options for non-interactive usage
$opts = getopt('', ['host:', 'user:', 'pass:', 'db:', 'yes']);
$nonInteractive = isset($opts['yes']);

// If non-interactive, use provided options or defaults without prompting
if ($nonInteractive) {
    $host = isset($opts['host']) ? $opts['host'] : 'localhost';
    $user = isset($opts['user']) ? $opts['user'] : 'root';
    $pass = isset($opts['pass']) ? $opts['pass'] : '';
    $db   = isset($opts['db']) ? $opts['db'] : 'adpaneldb';
} else {
    // Interactive: prefer CLI-provided values as defaults
    $host = isset($opts['host']) ? $opts['host'] : prompt('DB Host', 'localhost');
    $user = isset($opts['user']) ? $opts['user'] : prompt('DB User', 'root');
    $pass = isset($opts['pass']) ? $opts['pass'] : prompt('DB Pass', '');
    $db   = isset($opts['db']) ? $opts['db'] : prompt('DB Name', 'adpaneldb');
}

echo "\nMenghubungkan ke MySQL...\n";
$mysqli = new mysqli($host, $user, $pass);
if ($mysqli->connect_error) {
    echo "Koneksi gagal: " . $mysqli->connect_error . "\n";
    exit(1);
}

if (!$mysqli->select_db($db)) {
    echo "Database '$db' tidak ditemukan. Mencoba membuat...\n";
    $created = $mysqli->query("CREATE DATABASE `" . $mysqli->real_escape_string($db) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    if (!$created) {
        echo "Gagal membuat database: " . $mysqli->error . "\n";
        exit(1);
    }
    echo "Database '$db' berhasil dibuat.\n";
}

$mysqli->select_db($db);

$sqlFiles = [
    $base . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . 'create_users.sql',
    $base . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . 'create_banner_up.sql',
    $base . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . 'create_audit_triggers.sql',
];

foreach ($sqlFiles as $file) {
    if (!file_exists($file)) {
        echo "File SQL tidak ditemukan: $file\n";
        continue;
    }
    echo "Meng-import: $file ...\n";
    $sql = file_get_contents($file);
    if ($sql === false) {
        echo "Gagal membaca $file\n";
        continue;
    }

    if (!$mysqli->multi_query($sql)) {
        echo "Error saat meng-import $file: " . $mysqli->error . "\n";
        // kosongkan sisa hasil jika ada
        while ($mysqli->more_results() && $mysqli->next_result()) { /* flush */ }
        continue;
    }

    // flush all results
    do {
        if ($res = $mysqli->store_result()) {
            $res->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());

    echo "Selesai: $file\n";
}

$konPath = $base . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'System' . DIRECTORY_SEPARATOR . 'kon.php';
if (file_exists($konPath)) {
    echo "File konfigurasi sudah ada di admin/System/kon.php — tidak menimpa.\n";
} else {
    echo "Membuat file konfigurasi di admin/System/kon.php ...\n";
    $content = "<?php\n" .
        "\$host = \"" . addslashes($host) . "\";\n" .
        "\$user = \"" . addslashes($user) . "\";\n" .
        "\$pass = \"" . addslashes($pass) . "\";\n" .
        "\$db   = \"" . addslashes($db) . "\";\n\n" .
        "\$kon = mysqli_connect(\$host, \$user, \$pass, \$db);\n\n" .
        "if (!\$kon) {\n    die(\"Koneksi gagal: \" . mysqli_connect_error());\n}\n?>\n";

    $dir = dirname($konPath);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    if (file_put_contents($konPath, $content) === false) {
        echo "Gagal menulis file konfigurasi ke $konPath — periksa izin.\n";
    } else {
        echo "File konfigurasi dibuat: $konPath\n";
    }
}

echo "\nProses instalasi selesai. Silakan cek README.md untuk langkah selanjutnya.\n";
