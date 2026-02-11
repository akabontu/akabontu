<?php
// Partial: webpage/includes/product-card.php
// Expects $product array (or null) in scope
if (!isset($product)) {
    $product = null;
}

$imageSrc = null;
if ($product) {
    // Prefer product_images (path) if available, fall back to legacy blob.
    $productNo = $product['No'] ?? null;
    $imgPath = null;
    $imgMime = null;

    if (!empty($productNo)) {
        global $kon;
        if (isset($kon)) {
            $stmtImg = mysqli_prepare($kon, "SELECT path, mime FROM product_images WHERE product_no = ? ORDER BY is_primary DESC, no ASC LIMIT 1");
            if ($stmtImg) {
                mysqli_stmt_bind_param($stmtImg, 'i', $productNo);
                if (mysqli_stmt_execute($stmtImg)) {
                    mysqli_stmt_bind_result($stmtImg, $imgPath, $imgMime);
                    mysqli_stmt_fetch($stmtImg);
                }
                mysqli_stmt_close($stmtImg);
            }
        }
    }

    if (!empty($imgPath)) {
        $baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $relative = ltrim((string)$imgPath, '/');
        $root = dirname(__DIR__, 2);
        $candidates = [$relative];
        if (strpos($relative, 'admin/') !== 0) {
            $candidates[] = 'admin/' . $relative;
        }
        foreach ($candidates as $cand) {
            $fs = $root . '/' . $cand;
            if (file_exists($fs)) {
                $imageSrc = ($baseUrl !== '' ? $baseUrl : '') . '/' . ltrim($cand, '/');
                break;
            }
        }
        if ($imageSrc === null) {
            $imageSrc = ($baseUrl !== '' ? $baseUrl : '') . '/' . $relative;
        }
    } elseif (!empty($product['image'])) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_buffer($finfo, $product['image']) : 'image/jpeg';
        if ($finfo) finfo_close($finfo);
        $imageSrc = 'data:' . $mime . ';base64,' . base64_encode($product['image']);
    }
}
?>

<div class="card" >
    <div class="product-thumb"style="height: 300px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
        <div class="image" style="height: 100%;"><a href="<?php echo $product ? 'webpage/pages/product_detail.php?part_number=' . urlencode($product['part_number']) : '#'; ?>">
            <?php if ($product && !empty($imageSrc)): ?>
                <div class="product-thumb-overlay">
                    <img class="img-fluid" src="<?php echo htmlspecialchars($imageSrc, ENT_QUOTES); ?>" alt="Foto <?php echo htmlspecialchars($product['part_number']); ?>">
                    <div class="overlay-label" style="position: absolute; absolute;top:50%;left:50%;transform:translate(-50%, -50%) rotate(-0deg); height:150px; width: 305px;">
                        <img src="webpage/img/frame.png" style="opacity: 0.3" alt="Logo" style="height: 100%; width: 100%; object-fit: contain;">
                        <!--span style="font-size: 11px; font-weight: 600; color: #1e293b;">CV. Magz Group</span-->
                    </div>
                </div>
            <?php else: ?>
                <div class="no-image-lg"><img src="webpage/img/logo_magz.jpeg" alt="No Image"></div>
            <?php endif; ?>
        </a></div>
    </div>
    <div class="content" style="padding:5px;">
        <div style="flex:1; min-width:auto;">
            <div class="detail-row">
                <div class="detail-label" ><div class="detail-value" style="text-align:left; color:#e9ecef;"><h5 class="product-brand"style="color:#e9ecef;"><?php echo htmlspecialchars($product['brand'] ?? 'N/A'); ?></h5></div></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Part Number</div>
                <div class="detail-value" style="color:#e9ecef;"><?php echo htmlspecialchars($product['part_number'] ?? 'N/A'); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Interchange</div>
                <div class="detail-value" style="color:#e9ecef;"><?php echo htmlspecialchars($product['itc'] ?? 'N/A'); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Description</div>
                <div class="detail-value" style="color:#e9ecef;"><?php echo nl2br(htmlspecialchars($product['description'] ?? 'N/A')); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Stock</div>
                <div class="detail-value" style="color:#e9ecef;"><?php echo htmlspecialchars($product['Qty'] ?? 'N/A'); ?>&#9;<span><?php echo htmlspecialchars($product['UoM'] ?? 'N/A'); ?></span></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Berat</div>
                <div class="detail-value" style="color:#e9ecef;"><?php echo htmlspecialchars($product['berat'] ?? 'N/A'); ?>&#9;<span><?php echo htmlspecialchars($product['massa'] ?? 'N/A'); ?></span></div>
            </div>
            <div class="button-wa text-end">
                <a href="https://wa.me/6281110108000" class="btn-wa" target="_blank">Chat WhatsApp</a>
         </div>
        </div>
    </div>
</div>
