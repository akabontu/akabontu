<?php
// Force download script for template files
$file = $_GET['file'] ?? '';

$allowedFiles = [
    'sample_import.csv' => 'text/csv',
    'sample_import.xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];

if (!isset($allowedFiles[$file])) {
    http_response_code(404);
    die('File not found');
}

$filePath = __DIR__ . '/admin/Control/product/Data/' . $file;

if (!file_exists($filePath)) {
    http_response_code(404);
    die('File not found');
}

// Force download
header('Content-Type: ' . $allowedFiles[$file]);
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

readfile($filePath);
exit;
?>