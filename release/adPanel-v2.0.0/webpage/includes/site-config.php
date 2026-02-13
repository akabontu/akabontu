<?php
// Site configuration for base paths and assets.
// Pages may override $BASE_URL before including header if needed.
if (!isset($BASE_URL)) {
    // Default when served from e:/XAMPP_64/htdocs/adpanel
    $BASE_URL = '/adpanel';
}

$ASSET_PATH = rtrim($BASE_URL, '/') . '/webpage';
$INDEX_PATH = rtrim($BASE_URL, '/') . '/index.php';

// Expose constants if not defined
if (!defined('SITE_BASE_URL')) define('SITE_BASE_URL', $BASE_URL);
if (!defined('SITE_ASSET_PATH')) define('SITE_ASSET_PATH', $ASSET_PATH);
if (!defined('SITE_INDEX_PATH')) define('SITE_INDEX_PATH', $INDEX_PATH);

?>
