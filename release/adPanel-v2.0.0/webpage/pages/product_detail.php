<!DOCTYPE html>
<html lang="en"data-bs-theme="dark">
<head>
    <title>Product Detail - Bootstrap 5 Website</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../css/combined.css">
    <link rel="stylesheet" href="../css/mobile.css">
    <style>
        /* Increase font sizes for product details for better readability */
        .product-info { font-size: 1.125rem; display: flex; flex-direction: column; height: 100%; }
        .product-info h2 { font-size: 1.6rem; margin-bottom: .75rem; }
        /* Spacing and separators to match card rhythm */
        .product-info dl { margin-bottom: 0; }
        .product-info dt { font-weight: 600; color: #d1d5db; padding: .6rem 0; }
        .product-info dd { font-size: 1.125rem; padding: .6rem 0; margin-bottom: 0; }
        .product-info dt, .product-info dd { border-bottom: 1px solid rgba(255,255,255,0.04); }
        .product-info dd:last-child { border-bottom: none; padding-bottom: .75rem; }
        .product-info .btn-row { margin-top: auto; }
        @media (min-width: 768px) {
            .product-info dd { font-size: 1.25rem; }
        }
        
        /* Logo styling */
        .gallery-main {
            position: relative;
        }
        
        .gallery-logo-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width:520px;
            height:350px;
            background: transparent;
            border-radius: 6px;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
        
        .gallery-logo-container.active {
            display: flex;
        }
        
        .gallery-logo-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            opacity: 0.3;
        }
    </style>
</head>
<body>
<?php
include '../../admin/System/kon.php';

// Get part_number from URL
$part_number = $_GET['part_number'] ?? '';
if (empty($part_number)) {
    die('Product not found.');
}

// Fetch product details
$part_number = mysqli_real_escape_string($kon, $part_number);
$query = "SELECT * FROM product WHERE part_number = '$part_number'";
$result = mysqli_query($kon, $query);
if (!$result || mysqli_num_rows($result) == 0) {
    die('Product not found.');
}
$product = mysqli_fetch_assoc($result);
mysqli_free_result($result);

// Fetch interchanges from itc_pn table
$query_itc = "SELECT * FROM itc_pn WHERE part_number = '$part_number'";
$result_itc = mysqli_query($kon, $query_itc);
$itc_pn = mysqli_fetch_assoc($result_itc);
if ($result_itc) mysqli_free_result($result_itc);

// Build image list (product_images takes priority over legacy blob)
$imageDataUri = null;
if (!empty($product['image'])) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_buffer($finfo, $product['image']) : 'image/jpeg';
    if ($finfo) finfo_close($finfo);
    $imageDataUri = 'data:' . $mime . ';base64,' . base64_encode($product['image']);
}

$productImages = [];
$stmtImg = mysqli_prepare($kon, "SELECT path FROM product_images WHERE product_no = ? ORDER BY is_primary DESC, no ASC");
if ($stmtImg) {
    mysqli_stmt_bind_param($stmtImg, 'i', $product['No']);
    if (mysqli_stmt_execute($stmtImg)) {
        mysqli_stmt_bind_result($stmtImg, $imgPathRow);
        while (mysqli_stmt_fetch($stmtImg)) {
            if (!empty($imgPathRow)) $productImages[] = $imgPathRow;
        }
    }
    mysqli_stmt_close($stmtImg);
}

if (empty($productImages) && $imageDataUri) {
    $productImages[] = $imageDataUri;
}

$resolvedImages = [];
if (!empty($productImages)) {
    $baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'], 3), '/');
    $root = dirname(__DIR__, 3);
    foreach ($productImages as $img) {
        if (strpos($img, 'data:') === 0) {
            $resolvedImages[] = $img;
            continue;
        }
        $relative = ltrim((string)$img, '/');
        $candidates = [$relative];
        if (strpos($relative, 'admin/') !== 0) {
            $candidates[] = 'admin/' . $relative;
        }
        $resolved = null;
        foreach ($candidates as $cand) {
            $fs = $root . '/' . $cand;
            if (file_exists($fs)) {
                $resolved = ($baseUrl !== '' ? $baseUrl : '') . '/' . ltrim($cand, '/');
                break;
            }
        }
        if ($resolved === null) {
            $resolved = ($baseUrl !== '' ? $baseUrl : '') . '/' . $relative;
        }
        $resolvedImages[] = $resolved;
    }
}

// Fetch distinct brands for menu
$brands = [
    'Komatsu',
    'Bomag',
    'Caterpillar',
    'Scania',
    'Volvo',
    'Nissan',
    'Hyva',
    'Other',
];

// Fixed categories list
$categories = [
    'Engine',
    'Electrical',
    'Brake System',
    'Cylinder',
    'Axle & Steering',
    'Cabin',
    'Filter',
    'Attachment',
    'Final Drive',
    'Hydraulic System',
];

// Build menu data: each brand has the same categories
$menuData = [];
foreach ($brands as $brand) {
    $menuData[$brand] = $categories;
}
?>
    <?php
    // Ensure menu links point back to root index from this subfolder
    $menuIndexPath = '../../index.php';
    include '../includes/header.php';
    ?>

    <!-- Product Detail -->
    <div class="container mt-1">
        <div class="row align-items-stretch">
            <div class="col-md-6" >
                <div class="product-image" style="height: 100%;">
                    <?php if (!empty($resolvedImages)): ?>
                        <div class="product-gallery" data-images="<?php echo htmlspecialchars(json_encode($resolvedImages), ENT_QUOTES); ?>" style="border:1px solid #e5e7eb;border-radius:8px;padding:5px;">
                            <div class="gallery-main" style="position:relative;display:flex;align-items:center;justify-content:center;height:350px;">
                                <button type="button" class="gallery-nav" data-dir="prev" style="position:absolute;left:5px;width:25px;height:35px;border-radius:8px;border:none;background:#6b7280;color:#fff;font-size:18px;cursor:pointer;">&#8249;</button>
                                <img class="gallery-main-img" src="<?php echo htmlspecialchars($resolvedImages[0], ENT_QUOTES); ?>" alt="Image <?php echo htmlspecialchars($product['part_number']); ?>" style="width:100%;height:100%;object-fit:contain;">
                                <!--div class="overlay-label" style="position:absolute;top:50%;left:50%;transform:translate(-50%, -50%) rotate(-25deg);color:#d30c0c;padding:4px 8px;border-radius:4px;font-weight:700;font-size:24px;">CV. Magz Group</div-->
                                <div class="gallery-logo-container active" id="productLogoContainer"style="width: 530px;hight:350px">
                                    <img src="../img/frame.png" alt="Logo" >
                                </div>
                                <button type="button" class="gallery-nav" data-dir="next" style="position:absolute;right:5px;width:25px;height:35px;border-radius:8px;border:none;background:#6b7280;color:#fff;font-size:18px;cursor:pointer;">&#8250;</button>
                            </div>
                            <div class="gallery-thumbs" style="display:flex;gap:8px;overflow-x:auto;margin-top:10px;padding-bottom:4px;">
                                <?php foreach ($resolvedImages as $idx => $src): ?>
                                    <button type="button" class="gallery-thumb<?php echo $idx === 0 ? ' is-active' : ''; ?>" data-index="<?php echo $idx; ?>" style="border:2px solid <?php echo $idx === 0 ? '#2563eb' : '#e5e7eb'; ?>;border-radius:6px;padding:2px;background:#fff;cursor:pointer;">
                                        <img src="<?php echo htmlspecialchars($src, ENT_QUOTES); ?>" alt="thumb" style="width:52px;height:52px;object-fit:cover;display:block;">
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-image-detail"><img src="../img/logo_magz.jpeg" alt="No Image Available"></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 bg-dark text-light rounded product-info">
                    <h2 class="h4 mb-3"><?php echo htmlspecialchars($product['brand'] ?? 'N/A'); ?></h2>

                    <dl class="row mb-0 gx-0 align-items-start">
                        <dt class="col-sm-3 text-sm-start ps-0 pe-2">Part Number</dt>
                        <dd class="col-sm-9 mb-2 ps-sm-3 ps-0"><?php echo htmlspecialchars($product['part_number'] ?? 'N/A'); ?></dd>

                        <dt class="col-sm-3 text-sm-start ps-0 pe-2">Interchange</dt>
                        <dd class="col-sm-9 mb-2 ps-sm-3 ps-0">
                            <?php
                            $parts = [];
                            if (!empty($product['itc'])) $parts[] = $product['itc'];
                            if (!empty($itc_pn['itc_1'])) $parts[] = $itc_pn['itc_1'];
                            if (!empty($itc_pn['itc_2'])) $parts[] = $itc_pn['itc_2'];
                            if (!empty($itc_pn['itc_3'])) $parts[] = $itc_pn['itc_3'];
                            echo htmlspecialchars(implode(', ', $parts) ?: 'N/A');
                            ?>
                        </dd>

                        <dt class="col-sm-3 text-sm-start ps-0 pe-2">Description</dt>
                        <dd class="col-sm-9 mb-2 ps-sm-3 ps-0"><?php echo nl2br(htmlspecialchars($product['description'] ?? 'N/A')); ?></dd>

                        <dt class="col-sm-3 text-sm-start ps-0 pe-2">Stock</dt>
                        <dd class="col-sm-9 mb-2 ps-sm-3 ps-0"><?php echo htmlspecialchars($product['Qty'] ?? 'N/A'); ?>&#9;<span><?php echo htmlspecialchars($product['UoM'] ?? 'N/A'); ?></span></dd>

                        <dt class="col-sm-3 text-sm-start ps-0 pe-2">Berat</dt>
                        <dd class="col-sm-9 mb-2 ps-sm-3 ps-0"><?php echo htmlspecialchars($product['berat'] ?? 'N/A'); ?>&#9;<span><?php echo htmlspecialchars($product['massa'] ?? 'N/A'); ?></span></dd>
                    </dl>

                    <div class="btn-row">
                        <a href="https://wa.me/6281110108000?text=I'm interested in product <?php echo urlencode($product['part_number']); ?>" class="btn btn-success me-2" target="_blank">Contact via WhatsApp</a>
                        <a href="../../index.php" class="btn btn-secondary">Back to Product List</a>
                    </div>
                </div>
            </div>
    </div>

  
</body>
<script>
(function () {
    var galleries = document.querySelectorAll('.product-gallery');
    if (!galleries.length) return;

    galleries.forEach(function (gallery) {
        var images = [];
        try {
            images = JSON.parse(gallery.getAttribute('data-images') || '[]');
        } catch (e) {
            images = [];
        }
        if (!images.length) return;

        var mainImg = gallery.querySelector('.gallery-main-img');
        var thumbs = gallery.querySelectorAll('.gallery-thumb');
        var index = 0;

        function setActive(i) {
            index = (i + images.length) % images.length;
            if (mainImg) mainImg.src = images[index];
            thumbs.forEach(function (btn, idx) {
                var active = idx === index;
                btn.classList.toggle('is-active', active);
                btn.style.borderColor = active ? '#2563eb' : '#e5e7eb';
            });
        }

        gallery.querySelectorAll('.gallery-nav').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var dir = btn.getAttribute('data-dir');
                setActive(dir === 'prev' ? index - 1 : index + 1);
            });
        });

        thumbs.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var i = parseInt(btn.getAttribute('data-index'), 10) || 0;
                setActive(i);
            });
        });
    });
})();
</script>
  <?php include '../includes/footer.php'; ?>
</html>