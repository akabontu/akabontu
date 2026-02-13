<?php
declare(strict_types=1);

header('Content-Type: application/xml; charset=UTF-8');

function xmlEscape(string $value): string
{
    return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function addUrl(array &$urls, string $loc, string $changeFreq, string $priority): void
{
    $urls[$loc] = [
        'loc' => $loc,
        'lastmod' => date('Y-m-d'),
        'changefreq' => $changeFreq,
        'priority' => $priority,
    ];
}

$baseUrl = 'https://magzgroup.co.id';
$urls = [];

addUrl($urls, $baseUrl . '/', 'daily', '1.0');

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';
$dbName = getenv('DB_NAME') ?: 'adpaneldb';

$connection = @mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

if ($connection) {
    mysqli_set_charset($connection, 'utf8mb4');

    $query = 'SELECT part_number, brand, category FROM product WHERE part_number IS NOT NULL AND part_number <> ""';
    $result = mysqli_query($connection, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $partNumber = trim((string) ($row['part_number'] ?? ''));
            $brand = trim((string) ($row['brand'] ?? ''));
            $category = trim((string) ($row['category'] ?? ''));

            if ($partNumber !== '') {
                addUrl(
                    $urls,
                    $baseUrl . '/webpage/pages/product_detail.php?part_number=' . rawurlencode($partNumber),
                    'weekly',
                    '0.8'
                );
            }

            if ($brand !== '') {
                addUrl(
                    $urls,
                    $baseUrl . '/?brand=' . rawurlencode($brand),
                    'weekly',
                    '0.7'
                );
            }

            if ($brand !== '' && $category !== '') {
                addUrl(
                    $urls,
                    $baseUrl . '/?brand=' . rawurlencode($brand) . '&category=' . rawurlencode($category),
                    'weekly',
                    '0.6'
                );
            }
        }
        mysqli_free_result($result);
    }

    mysqli_close($connection);
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($urls as $url) {
    echo "  <url>\n";
    echo '    <loc>' . xmlEscape($url['loc']) . "</loc>\n";
    echo '    <lastmod>' . xmlEscape($url['lastmod']) . "</lastmod>\n";
    echo '    <changefreq>' . xmlEscape($url['changefreq']) . "</changefreq>\n";
    echo '    <priority>' . xmlEscape($url['priority']) . "</priority>\n";
    echo "  </url>\n";
}

echo '</urlset>' . "\n";
