<?php if (!defined('IN_MENU_ADMIN')): ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Detail Product — <?php echo htmlspecialchars($data['part_number'] ?? ''); ?></title>
  <link rel="stylesheet" href="../../../assets/css/style.css">
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
<?php // header removed: managed by menu_admin shell ?>
<?php endif; ?>

<?php
  if (!isset($_GET['no'])) {
      header('Location: ../../../../System/action/menu_admin.php'); exit;
  }

$no = intval($_GET['no']);
if ($no <= 0) { header('Location: ../../../../System/action/menu_admin.php'); exit; }

$stmt = mysqli_prepare($kon, "SELECT * FROM Product WHERE `No` = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $no);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if (!$res || mysqli_num_rows($res) == 0) { header('Location: ../../../../System/action/menu_admin.php'); exit; }
$data = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

$imageDataUri = null;
if (!empty($data['image'])) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_buffer($finfo, $data['image']) : 'image/jpeg';
    if ($finfo) finfo_close($finfo);
    $imageDataUri = 'data:' . $mime . ';base64,' . base64_encode($data['image']);
}

// Load product images (product_images takes priority over legacy blob)
$productImages = [];
$stmtImg = mysqli_prepare($kon, "SELECT path FROM product_images WHERE product_no = ? ORDER BY is_primary DESC, no ASC");
if ($stmtImg) {
  mysqli_stmt_bind_param($stmtImg, 'i', $no);
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

// Try to fetch additional interchange columns (itc_1..itc_3) from itc_pn
$itc1 = $itc2 = $itc3 = '';
if (!empty($data['part_number']) && !empty($data['itc'])) {
  $stmt2 = mysqli_prepare($kon, "SELECT itc_1, itc_2, itc_3 FROM itc_pn WHERE part_number = ? AND itc = ? LIMIT 1");
  if ($stmt2) {
    mysqli_stmt_bind_param($stmt2, 'ss', $data['part_number'], $data['itc']);
    mysqli_stmt_execute($stmt2);
    $res2 = mysqli_stmt_get_result($stmt2);
    if ($res2 && mysqli_num_rows($res2) > 0) {
      $rowitc = mysqli_fetch_assoc($res2);
      $itc1 = $rowitc['itc_1'] ?? '';
      $itc2 = $rowitc['itc_2'] ?? '';
      $itc3 = $rowitc['itc_3'] ?? '';
    }
    mysqli_stmt_close($stmt2);
  }
}

if(defined('IN_MENU_ADMIN')) {
?>
  <div class="top" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <h2 style="margin:0;font-size:1.75rem;color:#333;">Product Detail</h2>
    <div class="actions" style="display:flex;gap:0.75rem;">
      <a class="btn" href="?menu=product" style="background:#667eea;color:white;padding:0.5rem 1.25rem;border-radius:8px;text-decoration:none;border:none;cursor:pointer;font-weight:500;">Back</a>
    </div>
  </div>
<div class="container" style="width:100%;height:100%;margin:0 auto;">
<div class="card product-card">
    <div class="product-image" style="flex:0 0 50%; min-height:400px;">
      <?php if (!empty($productImages)): ?>
        <?php
          // Get base URL - since we're in admin context, calculate from document root
          $baseUrl = '/adPanel';
          $root = dirname(__DIR__, 4);
          $resolvedImages = [];
          
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
                $resolved = $baseUrl . '/' . ltrim($cand, '/');
                break;
              }
            }
            if ($resolved === null) {
              $resolved = $baseUrl . '/' . $relative;
            }
            $resolvedImages[] = $resolved;
          }
        ?>
        <div class="product-gallery" data-images="<?php echo htmlspecialchars(json_encode($resolvedImages), ENT_QUOTES); ?>" style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;height:100%;display:flex;flex-direction:column;">
          <div class="gallery-main" style="position:relative;display:flex;align-items:center;justify-content:center;height:350px;">
                <button type="button" class="gallery-nav" data-dir="prev" style="position:absolute;left:5px;width:25px;height:35px;border-radius:8px;border:none;background:#6b7280;color:#fff;font-size:18px;cursor:pointer;">&#8249;</button>
                <img class="gallery-main-img" src="<?php echo htmlspecialchars($resolvedImages[0], ENT_QUOTES); ?>" alt="Image <?php echo htmlspecialchars($product['part_number']); ?>" style="width:100%;height:100%;object-fit:contain;">
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
        <div class="product-thumb--placeholder" style="height:100%;display:flex;align-items:center;justify-content:center;background:#f5f5f5;border-radius:8px;">No Image</div>
      <?php endif; ?>
    </div>
    <div style="flex:1;" class="product-details">
        <h3 style="font-size:1.5rem;margin-bottom:1rem;color:#333;"><?php echo htmlspecialchars($data['brand'] ?? 'N/A'); ?></h3>
        
        <dl style="margin:0;padding:0;">
          <div style="display:flex;border-bottom:1px solid #e5e7eb;padding:0.75rem 0;">
            <dt style="font-weight:600;flex:0 0 35%;color:#666;">Part Number</dt>
            <dd style="flex:1;margin:0;color:#333;"><?php echo htmlspecialchars($data['part_number']); ?></dd>
          </div>
          
          <div style="display:flex;border-bottom:1px solid #e5e7eb;padding:0.75rem 0;">
            <dt style="font-weight:600;flex:0 0 35%;color:#666;">Interchange</dt>
            <dd style="flex:1;margin:0;color:#333;"><?php
              $parts = [];
              if (!empty($data['itc'])) $parts[] = $data['itc'];
              if ($itc1 !== '') $parts[] = $itc1;
              if ($itc2 !== '') $parts[] = $itc2;
              if ($itc3 !== '') $parts[] = $itc3;
              echo htmlspecialchars(implode(', ', $parts) ?: 'N/A');
            ?></dd>
          </div>
          
          <div style="display:flex;border-bottom:1px solid #e5e7eb;padding:0.75rem 0;">
            <dt style="font-weight:600;flex:0 0 35%;color:#666;">Description</dt>
            <dd style="flex:1;margin:0;color:#333;"><?php echo nl2br(htmlspecialchars($data['description'])); ?></dd>
          </div>
          
          <div style="display:flex;border-bottom:1px solid #e5e7eb;padding:0.75rem 0;">
            <dt style="font-weight:600;flex:0 0 35%;color:#666;">Category</dt>
            <dd style="flex:1;margin:0;color:#333;"><?php echo htmlspecialchars($data['category']); ?></dd>
          </div>
          
          <div style="display:flex;padding:0.75rem 0;">
            <dt style="font-weight:600;flex:0 0 35%;color:#666;">Brand</dt>
            <dd style="flex:1;margin:0;color:#333;"><?php echo htmlspecialchars($data['brand']); ?></dd>
          </div>
        </dl>
        
    </div>
  </div>
</div>


<script>
(function () {
    var galleries = document.querySelectorAll('.product-gallery');
    if (!galleries.length) return;

    galleries.forEach(function (gallery) {
        var images = [];
        try {
            images = JSON.parse(gallery.getAttribute('data-images') || '[]');
        } catch (e) {
            console.error('Error parsing images:', e);
            images = [];
        }
        if (!images.length) return;

        var mainImg = gallery.querySelector('.gallery-main-img');
        var thumbs = gallery.querySelectorAll('.gallery-thumb');
        var navButtons = gallery.querySelectorAll('.gallery-nav');
        var index = 0;

        console.log('Gallery initialized:', images.length, 'images');

        function setActive(i) {
            index = (i + images.length) % images.length;
            if (mainImg) {
                mainImg.src = images[index];
                console.log('Changed to image', index);
            }
            thumbs.forEach(function (btn, idx) {
                var active = idx === index;
                btn.classList.toggle('is-active', active);
                btn.style.borderColor = active ? '#2563eb' : '#e5e7eb';
            });
        }

        navButtons.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var dir = btn.getAttribute('data-dir');
                console.log('Navigation clicked:', dir);
                setActive(dir === 'prev' ? index - 1 : index + 1);
            });
        });

        thumbs.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var i = parseInt(btn.getAttribute('data-index'), 10) || 0;
                console.log('Thumb clicked:', i);
                setActive(i);
            });
        });
    });
})();
</script>

<?php
return;
}
?>
<?php // footer removed: managed by menu_admin shell ?>
</body>
</html>
