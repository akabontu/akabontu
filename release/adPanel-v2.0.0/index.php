<?php ob_start(); ?>
<!DOCTYPE html>
<?php
// Ensure site config is available for head meta/OG assets
include 'webpage/includes/site-config.php';
?>
<!-- html lang="en" data-bs-theme="dark"-->
<html lang="en" data-bs-theme="dark" >
<head>
    <title>Magz Group — Committed to Serve — Ready to Supply</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Penyedia suku cadang heavy equipment untuk berbagai merek (Komatsu, Caterpillar, Volvo). Stok lengkap, dukungan teknis, dan pengiriman cepat ke seluruh Indonesia.">
    <meta property="og:title" content="Magz Group — Suku Cadang Heavy Equipment &amp; Dump Truck Spare Parts">
    <meta property="og:description" content="Temukan part number, interchange, dan solusi suku cadang alat berat. Hubungi sales untuk ketersediaan dan pengiriman cepat.">
    <meta property="og:image" content="<?php echo htmlspecialchars($ASSET_PATH . '/img/og-home.svg'); ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<link rel="stylesheet" href="webpage/css/combined.css">
	<link rel="stylesheet" href="webpage/css/mobile.css">

    <style>
        .fakeimg {
            height: 200px;
            background: #aaa;
        }
        /* Product grid styling: keep product cards uniform */
        #product-grid .row.align-items-stretch { align-items: stretch; }
        #product-grid .col { display: flex; }
        #product-grid .card {
            display: flex;
            flex-direction: column;
            box-shadow: none;
            min-height: 420px; /* fixed minimum so cards are consistent */
            overflow: hidden;
            width: 100%;
        }
        /* Fixed image area so image size doesn't alter card height */
        #product-grid .product-thumb {
            height: 160px;
            flex: 0 0 160px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f2f2;
        }
        #product-grid .product-thumb img {
            height: 100%;
            width: auto;
            object-fit: contain;
        }
        #product-grid .no-image-lg { height: 200px; display:flex; align-items:center; justify-content:center; background:#f5f5f5; }
        /* Make content stretch and keep button at bottom */
        #product-grid .content { flex: 1 1 auto; display:flex; flex-direction:column; justify-content:space-between; }
        #product-grid .detail-row { min-height: 30px; }
        /* Banner carousel: fixed height and cover/contain behavior so slides don't resize */
        .Banner_up .carousel-inner, #banner-up .carousel-inner { height: 500px; }
        .Banner_up .carousel-item, #banner-up .carousel-item { height: 500px; }
        .Banner_up .carousel-item img, #banner-up .carousel-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
        /* Fallback for data URI images rendered via img tag or directly set as background */
        .Banner_up .carousel-item .d-block { width:100%; height:100%; object-fit:cover; }
        /* Smaller brand/logo carousel */
        .Banner_lw .carousel-inner, #banner-lw .carousel-inner { height: 120px; }
        .Banner_lw .carousel-item, #banner-lw .carousel-item { height: 120px; }
        .Banner_lw img { max-height: 100px; object-fit: contain; }
       
        /* Floating hero/ad outside page */
        .floating-hero {
            position: fixed;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            width: 340px;
            max-width: calc(35vw);
            background: linear-gradient(90deg,#0d6efd,#0dcaf0);
            color: #fff;
            padding: 18px;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            z-index: 1050;
            pointer-events: auto;
        }
        .floating-hero h3 { margin-top: 0; color: rgba(255,255,255,0.95); font-size: 1.125rem; }
        .floating-hero p { color: rgba(255,255,255,0.9); margin-bottom: 12px; font-size: .95rem; }
        .floating-hero .btn { margin-right: .5rem; }
        .floating-hero .fh-close { position: absolute; top: 8px; right: 8px; background: transparent; border: none; color: rgba(255,255,255,0.9); font-size: 1.2rem; cursor: pointer; z-index: 20001; pointer-events: auto; }
        @media (max-width: 992px) { .floating-hero { display: none; } }
    
    </style>
</head>
<body>
<?php
include 'admin/System/kon.php';

// Add active column if not exists (one-time operation)
$query = "ALTER TABLE banner_up ADD COLUMN IF NOT EXISTS active TINYINT(1) DEFAULT 1";
mysqli_query($kon, $query);

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
    'Axle & Stering',
    'Cabin',
    'Filter',
    'Attachment',
    'Final Drive',
    'Hydraulic System',
    'General',
    'Alternatif',
];

// Build menu data: each brand has the same categories
$menuData = [];
foreach ($brands as $brand) {
    $menuData[$brand] = $categories;
}

// Fetch products for display, filter by brand and/or category if set
$whereClauses = [];
if (isset($_GET['brand']) && !empty($_GET['brand'])) {
    $brand = mysqli_real_escape_string($kon, $_GET['brand']);
    $whereClauses[] = "brand = '$brand'";
}
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category = mysqli_real_escape_string($kon, $_GET['category']);
    $whereClauses[] = "category = '$category'";
}
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $searchQuery = mysqli_real_escape_string($kon, $_GET['query']);
    // Check if query is exact ITC match (itc, itc1, itc2, itc3)
    $checkItcQuery = "SELECT part_number FROM itc_pn WHERE itc_1 = ? OR itc_2 = ? OR itc_3 = ?";
    $stmt = mysqli_prepare($kon, $checkItcQuery);
    mysqli_stmt_bind_param($stmt, "sss", $searchQuery, $searchQuery, $searchQuery);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $exactItcPn);
    if (mysqli_stmt_fetch($stmt)) {
        mysqli_stmt_close($stmt);
        header("Location: webpage/pages/product_detail.php?part_number=" . urlencode($exactItcPn));
        exit;
    }
    mysqli_stmt_close($stmt);

    // Also check product.itc column
    $checkProductItcQuery = "SELECT part_number FROM product WHERE itc = ?";
    $stmt = mysqli_prepare($kon, $checkProductItcQuery);
    mysqli_stmt_bind_param($stmt, "s", $searchQuery);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $exactProductItcPn);
    if (mysqli_stmt_fetch($stmt)) {
        mysqli_stmt_close($stmt);
        header("Location: webpage/pages/product_detail.php?part_number=" . urlencode($exactProductItcPn));
        exit;
    }
    mysqli_stmt_close($stmt);

    // Check if query is exact part_number
    $checkQuery = "SELECT part_number FROM product WHERE part_number = ?";
    $stmt = mysqli_prepare($kon, $checkQuery);
    mysqli_stmt_bind_param($stmt, "s", $searchQuery);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $exactPn);
    if (mysqli_stmt_fetch($stmt)) {
        mysqli_stmt_close($stmt);
        header("Location: webpage/pages/product_detail.php?part_number=" . urlencode($exactPn));
        exit;
    }
    mysqli_stmt_close($stmt);
    // If not exact, proceed with LIKE search (include category so partial inputs like "cab" match "Cabin")
    $whereClauses[] = "(part_number LIKE '%$searchQuery%' OR itc LIKE '%$searchQuery%' OR description LIKE '%$searchQuery%' OR brand LIKE '%$searchQuery%' OR category LIKE '%$searchQuery%' OR part_number IN (SELECT part_number FROM itc_pn WHERE itc_1 LIKE '%$searchQuery%' OR itc_2 LIKE '%$searchQuery%' OR itc_3 LIKE '%$searchQuery%'))";
}
$where = "";
if (!empty($whereClauses)) {
    $where = " WHERE " . implode(" AND ", $whereClauses);
}

// Pagination setup (homepage shows 8 products per page)
$productsPerPage = 12;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, $currentPage); // Ensure page is at least 1
$offset = ($currentPage - 1) * $productsPerPage;

// Get total products count
$countQuery = "SELECT COUNT(*) as total FROM product" . $where;
$countResult = mysqli_query($kon, $countQuery);
$totalProducts = 0;
if ($countResult) {
    $countRow = mysqli_fetch_assoc($countResult);
    $totalProducts = $countRow['total'];
    mysqli_free_result($countResult);
}
$totalPages = ceil($totalProducts / $productsPerPage);

// Fetch products with pagination
$query = "SELECT * FROM product" . $where . " LIMIT $offset, $productsPerPage";

$products = [];
$result = mysqli_query($kon, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    mysqli_free_result($result);
}
?>
    <?php include 'webpage/includes/header.php'; ?>

    <?php
    // Fetch banners from banner_up table
    $query = "SELECT * FROM banner_up WHERE active = 1";
    $result = mysqli_query($kon, $query);
    $banners = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $banners[] = $row;
        }
        mysqli_free_result($result);
    }
    ?>
        <!-- Carousel -->
        <div class="container px-2 banner-wrapper">
            <div class="Banner_up">
                <div id="banner-up" class="carousel slide" data-bs-ride="carousel">
                    <!-- Indicators/dots -->
                    <div class="carousel-indicators">
                        <?php for ($i = 0; $i < count($banners); $i++): ?>
                            <button type="button" data-bs-target="#banner-up" data-bs-slide-to="<?php echo $i; ?>" class="<?php echo $i == 0 ? 'active' : ''; ?>"></button>
                        <?php endfor; ?>
                    </div>
                    <!-- The slideshow/carousel -->
                    <div class="carousel-inner">
                        <?php foreach ($banners as $index => $banner): ?>
                            <div class="carousel-item <?php echo $index == 0 ? 'active' : ''; ?>">
                                <?php if (!empty($banner['image'])): ?>
                                    <?php
                                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                    $mime = $finfo ? finfo_buffer($finfo, $banner['image']) : 'image/jpeg';
                                    if ($finfo) finfo_close($finfo);
                                    ?>
                                    <img src="data:<?php echo htmlspecialchars($mime); ?>;base64,<?php echo base64_encode($banner['image']); ?>" alt="banner" class="d-block" style="width:100%">
                                <?php else: ?>
                                    <div class="d-block" style="width:100%; height:200px; background:#aaa;">No Image</div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Left and right controls/icons -->
                    <button class="carousel-control-prev" type="button" data-bs-target="#banner-up" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#banner-up" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Product List -->
        <div class="container" id="product-grid">
		<h2>Product List</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 p-1 align-items-stretch g-3">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="col">
                        <?php include 'webpage/includes/product-card.php'; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="container-fluid py-4 text-left " style="width: auto;">
                    <h1>Mohon Maaf Stock Belum Tersedia</h1>
                </div>
        <?php endif; ?>
        </div>
        
       <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="container mt-4">
        <nav aria-label="Product pagination">
            <ul class="pagination justify-content-center">
                <!-- Previous button -->
                <?php if ($currentPage > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage - 1])); ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled">
                        <span class="page-link" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </span>
                    </li>
                <?php endif; ?>

                <!-- Page numbers -->
                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);

                // Show first page if not in range
                if ($startPage > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($_GET, ['page' => 1])) . '">1</a></li>';
                    if ($startPage > 2) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }

                // Show page numbers
                for ($i = $startPage; $i <= $endPage; $i++) {
                    if ($i == $currentPage) {
                        echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
                    } else {
                        echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($_GET, ['page' => $i])) . '">' . $i . '</a></li>';
                    }
                }

                // Show last page if not in range
                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($_GET, ['page' => $totalPages])) . '">' . $totalPages . '</a></li>';
                }
                ?>

                <!-- Next button -->
                <?php if ($currentPage < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage + 1])); ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled">
                        <span class="page-link" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </span>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
     </div>
    <?php endif; ?>
     </div>

    <!-- Carousel -->
    <div class="container p-1 banner-wrapper">
        <?php
        // Fetch banners from logo_brand table
        $query = "SELECT * FROM logo_brand WHERE active = 1";
        $result = mysqli_query($kon, $query);
        $banners_lw = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $banners_lw[] = $row;
            }
            mysqli_free_result($result);
        }
        ?>
        <div class="Banner_lw">
            <div id="banner-lw" class="carousel slide" data-bs-ride="carousel">
                <!-- Indicators/dots -->
                <div class="carousel-indicators">
                    <?php
                    $totalBanners = count($banners_lw);
                    $slidesCount = ceil($totalBanners / 2);
                    for ($i = 0; $i < $slidesCount; $i++):
                    ?>
                        <button type="button" data-bs-target="#banner-lw" data-bs-slide-to="<?php echo $i; ?>" class="<?php echo $i == 0 ? 'active' : ''; ?>"></button>
                    <?php endfor; ?>
                </div>
                <!-- The slideshow/carousel -->
                <div class="carousel-inner">
                    <?php
                    $totalBanners = count($banners_lw);
                    $slidesCount = ceil($totalBanners / 2);
                    for ($slideIndex = 0; $slideIndex < $slidesCount; $slideIndex++):
                        $startIndex = $slideIndex * 2;
                        $endIndex = min($startIndex + 2, $totalBanners);
                        $slideBanners = array_slice($banners_lw, $startIndex, 2);
                    ?>
                        <div class="carousel-item <?php echo $slideIndex == 0 ? 'active' : ''; ?>">
                            <div class="row justify-content-center">
                                <?php foreach ($slideBanners as $banner): ?>
                                    <div class="col-2 text-center">
                                        <?php if (!empty($banner['logo_img'])): ?>
                                            <?php
                                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                            $mime = $finfo ? finfo_buffer($finfo, $banner['logo_img']) : 'image/jpeg';
                                            if ($finfo) finfo_close($finfo);
                                            ?>
                                            <img src="data:<?php echo htmlspecialchars($mime); ?>;base64,<?php echo base64_encode($banner['logo_img']); ?>" alt="banner" class="img-fluid">
                                        <?php else: ?>
                                            <div style="width:100%; height:100px; background:#aaa;">No Image</div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
                <!-- Left and right controls/icons -->
                <button class="carousel-control-prev" type="button" data-bs-target="#banner-lw" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#banner-lw" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        </div>
    </div>


 <!-- Hero -->
    <!--div class="container my-4">
        <div class="p-4 bg-light rounded-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-6 fw-bold">Temukan Part &amp; Solusi untuk Alat Berat Anda</h1>
                    <p class="lead">Ratusan part, dukungan teknis ahli, dan pengiriman cepat ke seluruh Indonesia.</p>
                    <a href="#product-grid" class="btn btn-primary btn-lg me-2">Lihat Produk Unggulan</a>
                    <a href="https://wa.me/6281110108000" class="btn btn-outline-secondary btn-lg" target="_blank">Konsultasi Gratis</a>
                </div>
                <div class="col-md-4 text-center d-none d-md-block">
                    <img src="<?php echo htmlspecialchars($ASSET_PATH . '/img/og-home.svg'); ?>" alt="Hero" class="img-fluid rounded" style="max-height:160px;">
                </div>
            </div>
        </div>
    </div-->
    
    <script>
    (function(){
        try {
            if (localStorage && localStorage.getItem('promoDismissed') === '1') return;
        } catch(e) {}
        var banner = document.getElementById('promoBanner');
        if (!banner) return;
        banner.style.opacity = '1';
        var closeBtn = document.getElementById('promoClose');

        var hideDelay = 10000; // auto-hide after 10s
        var hideTimer = null;

        function hideBanner(persist){
            if (!banner) return;
            banner.style.transition = 'opacity 200ms ease';
            banner.style.opacity = '0';
            setTimeout(function(){ banner.parentNode && banner.parentNode.removeChild(banner); }, 210);
            if (persist) {
                try { localStorage.setItem('promoDismissed', '1'); } catch(e) {}
            }
        }

        function startTimer(){
            clearTimer();
            hideTimer = setTimeout(function(){ hideBanner(true); }, hideDelay);
        }
        function clearTimer(){ if (hideTimer) { clearTimeout(hideTimer); hideTimer = null; } }

        // start auto-hide timer
        startTimer();

        // pause on hover
        banner.addEventListener('mouseenter', function(){ clearTimer(); });
        banner.addEventListener('mouseleave', function(){ startTimer(); });

        closeBtn.addEventListener('click', function(){
            hideBanner(true);
        });
    })();
    </script>

    <?php include 'webpage/includes/footer.php'; ?>
    <!-- Floating hero ad (desktop) -->
    <div id="floatingHero" class="floating-hero" role="dialog" aria-label="Promo">
        <button id="floatingHeroClose" class="fh-close" aria-label="Close">&times;</button>
        <h3>Temukan Part &amp; Solusi untuk Alat Berat</h3>
        <p>Ratusan part, dukungan teknis ahli, dan pengiriman cepat ke seluruh Indonesia.</p>
        <div>
            <a href="#product-grid" class="btn btn-dark btn-sm">Lihat Produk</a>
            <a href="https://wa.me/6281110108000" class="btn btn-outline-light btn-sm" target="_blank">Konsultasi</a>
        </div>
    </div>

    <script>
    (function(){
        var fh = document.getElementById('floatingHero');
        if (!fh) return;
        try {
            if (localStorage) localStorage.removeItem('floatingHeroDismissed');
            if (sessionStorage && sessionStorage.getItem('floatingHeroDismissed') === '1') {
                fh.parentNode && fh.parentNode.removeChild(fh);
                return;
            }
        } catch(e) {}

        function hideFloatingHero(){
            if (!fh) return;
            fh.style.transition = 'opacity 200ms ease';
            fh.style.opacity = '0';
            setTimeout(function(){ fh.parentNode && fh.parentNode.removeChild(fh); }, 210);
            try { if (sessionStorage) sessionStorage.setItem('floatingHeroDismissed', '1'); } catch(e) {}
        }

        document.addEventListener('click', function(e){
            var btn = e.target && e.target.closest ? e.target.closest('#floatingHeroClose') : null;
            if (!btn) return;
            e.preventDefault();
            hideFloatingHero();
        }, true);
    })();
    </script>
    <a href="https://wa.me/6281110108000" class="floating-wa" target="_blank"><img src="<?php echo htmlspecialchars($ASSET_PATH . '/img/wa-logo.png'); ?>" alt="WhatsApp Admin"  /></a>
    <marquee style="scrollamount:5; height:30px;font-color:white" >
    <b>CV. Magz Group - A Be Solution Heavy Equipment Spare Parts  |  Grand City, Cluster Hyland U3-32, Kota Balikpapan, Kalimantan Timur 76125  |  Phone: +62811-1010-8000 </b></marquee>
<?php if (ob_get_level()) { ob_end_flush(); } ?>
</body>

</html>
