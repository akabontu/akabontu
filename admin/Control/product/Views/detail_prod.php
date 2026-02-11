<?php if (!defined('IN_MENU_ADMIN')): ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Detail Product — <?php echo htmlspecialchars($data['part_number'] ?? ''); ?></title>
  <link rel="stylesheet" href="../../../assets/css/style.css">
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
  <div class="top">
  <h2>Detail Product</h2>
  <div class="actions">
    <a class="btn" href="?menu=product">Kembali</a>
    <a class="btn btn-delete" href="../Actions/del_prod.php?no=<?php echo urlencode($data['No']); ?>" onclick="return confirm('Yakin hapus?')">Hapus</a>
  </div>
</div>
<div class="container">
<div class="card product-card">
  <div style="display:flex; gap:24px; align-items:flex-start;">
    <div style="flex:0 0 360px;">
      <?php if (!empty($productImages)): ?>
        <?php
          $baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'], 4), '/');
          $resolvedImages = [];
          foreach ($productImages as $img) {
            if (strpos($img, 'data:') === 0) {
              $resolvedImages[] = $img;
              continue;
            }
            $relative = ltrim((string)$img, '/');
            $root = dirname(__DIR__, 4);
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
        ?>
        <div class="product-gallery" data-images="<?php echo htmlspecialchars(json_encode($resolvedImages), ENT_QUOTES); ?>" style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;">
          <div class="gallery-main" style="position:relative;display:flex;align-items:center;justify-content:center;height:260px;">
            <button type="button" class="gallery-nav" data-dir="prev" style="position:absolute;left:6px;width:36px;height:36px;border-radius:8px;border:none;background:#6b7280;color:#fff;font-size:18px;cursor:pointer;">&#8249;</button>
            <img class="gallery-main-img" src="<?php echo htmlspecialchars($resolvedImages[0], ENT_QUOTES); ?>" alt="Image <?php echo htmlspecialchars($data['part_number']); ?>" style="max-width:100%;max-height:100%;object-fit:contain;">
            <button type="button" class="gallery-nav" data-dir="next" style="position:absolute;right:6px;width:36px;height:36px;border-radius:8px;border:none;background:#6b7280;color:#fff;font-size:18px;cursor:pointer;">&#8250;</button>
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
        <div class="product-thumb--placeholder">No Image</div>
      <?php endif; ?>
    </div>
    <div style="flex:1;" class="product-details">
        <div class="product-row">
          <div class="product-label">No</div>
          <div class="product-value"><?php echo htmlspecialchars($data['No']); ?></div>
        </div>
        <div class="product-row">
          <div class="product-label">Part Number</div>
          <div class="product-value"><?php echo htmlspecialchars($data['part_number']); ?></div>
        </div>
        <div class="product-row">
          <div class="product-label">Interchange</div>
          <div class="product-value"><?php
            $parts = [];
            if (!empty($data['itc'])) $parts[] = $data['itc'];
            if ($itc1 !== '') $parts[] = $itc1;
            if ($itc2 !== '') $parts[] = $itc2;
            if ($itc3 !== '') $parts[] = $itc3;
            echo htmlspecialchars(implode(', ', $parts));
          ?></div>
        </div>
        <div class="product-row">
          <div class="product-label">Description</div>
          <div class="product-value"><?php echo nl2br(htmlspecialchars($data['description'])); ?></div>
        </div>
        <div class="product-row">
          <div class="product-label">Brand</div>
          <div class="product-value"><?php echo htmlspecialchars($data['brand']); ?></div>
        </div>
        <div class="product-row">
          <div class="product-label">Category</div>
          <div class="product-value"><?php echo htmlspecialchars($data['category']); ?></div>
        </div>
    </div>
  </div>
</div>
</div>
<?php
return;
}
?>
<?php // footer removed: managed by menu_admin shell ?>
</body>
</html>
