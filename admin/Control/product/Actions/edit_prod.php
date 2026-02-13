<?php
require_once __DIR__ . '/../../../System/kon.php';

$errors = [];

// Determine No from GET or POST
$no = 0;
if (isset($_GET['no'])) $no = intval($_GET['no']);
elseif (isset($_POST['no'])) $no = intval($_POST['no']);

if ($no <= 0) { header('Location: ../../../System/action/menu_admin.php?menu=product'); exit; }

// Fetch existing record
$stmt = mysqli_prepare($kon, "SELECT * FROM product WHERE `No` = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $no);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if (!$res || mysqli_num_rows($res) == 0) { header('Location: ../../../System/action/menu_admin.php?menu=product'); exit; }
$data = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

$part_number = $data['part_number'];

// Fetch images from product_images table
$productImages = [];
$stmtImages = mysqli_prepare($kon, "SELECT no, path, is_primary FROM product_images WHERE product_no = ? ORDER BY is_primary DESC, no ASC");
if ($stmtImages) {
	mysqli_stmt_bind_param($stmtImages, 'i', $no);
	mysqli_stmt_execute($stmtImages);
	$resImages = mysqli_stmt_get_result($stmtImages);
	while ($row = mysqli_fetch_assoc($resImages)) {
		$productImages[] = $row;
	}
	mysqli_stmt_close($stmtImages);
}

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
    'Alternatif'
];

// Selected category for the form (preserve POST on validation error)
$selectedCategory = isset($_POST['category']) ? trim($_POST['category']) : ($data['category'] ?? '');
// Selected brand for the form (preserve POST on validation error)
$selectedBrand = isset($_POST['brand']) ? trim($_POST['brand']) : ($data['brand'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
        // Get form values
        // ITC: only accept if currently empty
        $itc = (!empty($data['itc'])) ? $data['itc'] : trim($_POST['itc'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $brand = trim($_POST['brand'] ?? '');
        $category = trim($_POST['category'] ?? '');

        // For edit, ITC is not required since it's managed separately via the add button
        // if ($itc === '') $errors[] = 'Interchange harus diisi.';
        if ($description === '') $errors[] = 'Description harus diisi.';

        // Whitelist validation for brand and category
        $allowed_brands = ['Komatsu','Bomag','Caterpillar','Scania','Volvo','Nissan','Hyva','Other'];
        $allowed_categories = [
            'Engine','Electrical','Brake System','Cylinder','Axle & Stering','Cabin','Filter','Attachment','Final Drive','Hydraulic System','General','Alternatif'
        ];
        if ($brand !== '' && !in_array($brand, $allowed_brands)) $errors[] = 'Brand tidak valid.';
        if ($category !== '' && !in_array($category, $allowed_categories)) $errors[] = 'Category tidak valid.';

        // Handle image delete operations
        if (!empty($_POST['delete_images'])) {
                $toDelete = is_array($_POST['delete_images']) ? $_POST['delete_images'] : [$_POST['delete_images']];
                foreach ($toDelete as $imgNo) {
                        $imgNo = intval($imgNo);
                        // Find image record
                        $stmtFind = mysqli_prepare($kon, "SELECT path FROM product_images WHERE no = ? AND product_no = ?");
                        if ($stmtFind) {
                                mysqli_stmt_bind_param($stmtFind, "ii", $imgNo, $no);
                                mysqli_stmt_execute($stmtFind);
                                $resFind = mysqli_stmt_get_result($stmtFind);
                                if ($row = mysqli_fetch_assoc($resFind)) {
                                        // Delete file from disk
                                        $filePath = __DIR__ . '/../../../../' . ltrim($row['path'], '/');
                                        @unlink($filePath);
                                        // Delete record from database
                                        $stmtDel = mysqli_prepare($kon, "DELETE FROM product_images WHERE no = ?");
                                        if ($stmtDel) {
                                                mysqli_stmt_bind_param($stmtDel, "i", $imgNo);
                                                mysqli_stmt_execute($stmtDel);
                                                mysqli_stmt_close($stmtDel);
                                        }
                                }
                                mysqli_stmt_close($stmtFind);
                        }
                }
                // Reload images after deletion
                $productImages = [];
                $stmtImages = mysqli_prepare($kon, "SELECT no, path, is_primary FROM product_images WHERE product_no = ? ORDER BY is_primary DESC, no ASC");
                if ($stmtImages) {
                        mysqli_stmt_bind_param($stmtImages, 'i', $no);
                        mysqli_stmt_execute($stmtImages);
                        $resImages = mysqli_stmt_get_result($stmtImages);
                        while ($row = mysqli_fetch_assoc($resImages)) {
                                $productImages[] = $row;
                        }
                        mysqli_stmt_close($stmtImages);
                }
        }

        // Handle image replace/upload - replace existing or add new (multi files)
        if (!empty($_FILES['images'])) {
                $files = $_FILES['images'];
                $replaceImgNo = intval($_POST['replace_img'] ?? 0);
                $allowed = ['image/jpeg','image/png','image/gif'];
                $maxSize = 3 * 1024 * 1024;
                $maxTotal = 5;

                $names = is_array($files['name']) ? $files['name'] : [$files['name']];
                $tmpNames = is_array($files['tmp_name']) ? $files['tmp_name'] : [$files['tmp_name']];
                $errorCodes = is_array($files['error']) ? $files['error'] : [$files['error']];
                $sizes = is_array($files['size']) ? $files['size'] : [$files['size']];

                $actualCount = 0;
                foreach ($names as $idx => $uploadName) {
                        $errorCode = $errorCodes[$idx] ?? UPLOAD_ERR_NO_FILE;
                        if ($uploadName === '' || $errorCode === UPLOAD_ERR_NO_FILE) continue;
                        $actualCount++;
                }

                if ($actualCount > 0) {
                        if ($replaceImgNo > 0 && $actualCount > 1) {
                                $errors[] = 'Replace hanya bisa 1 gambar sekali upload.';
                        }

                        $countStmt = mysqli_prepare($kon, "SELECT COUNT(*) as cnt FROM product_images WHERE product_no = ?");
                        $currentCount = 0;
                        if ($countStmt) {
                                mysqli_stmt_bind_param($countStmt, "i", $no);
                                mysqli_stmt_execute($countStmt);
                                $resCount = mysqli_stmt_get_result($countStmt);
                                if ($row = mysqli_fetch_assoc($resCount)) {
                                        $currentCount = intval($row['cnt']);
                                }
                                mysqli_stmt_close($countStmt);
                        }

                        if ($replaceImgNo === 0 && ($currentCount + $actualCount) > $maxTotal) {
                                $remaining = $maxTotal - $currentCount;
                                $errors[] = 'Produk sudah memiliki ' . $currentCount . ' gambar. Hanya bisa menambah ' . $remaining . ' gambar lagi (max total ' . $maxTotal . ').';
                        }

                        if (empty($errors)) {
                                // store uploads inside admin/Control/product/img/<product_no>/ for per-product organization
                                $uploadDir = __DIR__ . '/../img/';
                                if (!is_dir($uploadDir)) {
                                  mkdir($uploadDir, 0755, true);
                                }
                                // create product-specific subfolder
                                $productDir = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $no . DIRECTORY_SEPARATOR;
                                if (!is_dir($productDir)) {
                                  mkdir($productDir, 0755, true);
                                }

                                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                $replaced = false;
                                $addedCount = 0;

                                foreach ($names as $idx => $uploadName) {
                                        $uploadName = (string)$uploadName;
                                        $errorCode = $errorCodes[$idx] ?? UPLOAD_ERR_NO_FILE;
                                        if ($uploadName === '' || $errorCode === UPLOAD_ERR_NO_FILE) continue;
                                        if ($errorCode !== UPLOAD_ERR_OK) {
                                                $errors[] = "Upload gambar '{$uploadName}' gagal.";
                                                continue;
                                        }

                                        if ($replaceImgNo > 0 && $replaced) {
                                                continue;
                                        }

                                        $tmpName = $tmpNames[$idx];
                                        $sizeBytes = (int)($sizes[$idx] ?? 0);
                                        $mime = $finfo ? finfo_file($finfo, $tmpName) : null;
                                        if (!$mime || !in_array($mime, $allowed, true)) {
                                                $errors[] = "Tipe file '{$uploadName}' tidak didukung.";
                                                continue;
                                        }
                                        if ($sizeBytes > $maxSize) {
                                                $errors[] = "Ukuran file '{$uploadName}' terlalu besar (maks 3MB).";
                                                continue;
                                        }

                                        $ext = pathinfo($uploadName, PATHINFO_EXTENSION);
                                        if ($ext === '') {
                                                $ext = $mime === 'image/png' ? 'png' : ($mime === 'image/gif' ? 'gif' : 'jpg');
                                        }

                                        // Generate filename using same pattern as add_prod: <productNo>-<index>-<uniqid>.<ext>
                                        if ($replaceImgNo > 0) {
                                                // Fetch existing record to retain its index (if filename matches pattern)
                                                $stmtOld = mysqli_prepare($kon, "SELECT path, filename, is_primary FROM product_images WHERE no = ? AND product_no = ?");
                                                if ($stmtOld) {
                                                        mysqli_stmt_bind_param($stmtOld, "ii", $replaceImgNo, $no);
                                                        mysqli_stmt_execute($stmtOld);
                                                        $resOld = mysqli_stmt_get_result($stmtOld);
                                                        if ($rowOld = mysqli_fetch_assoc($resOld)) {
                                                                $oldFilePath = __DIR__ . '/../../../../' . ltrim($rowOld['path'], '/');
                                                                $isPrimary = intval($rowOld['is_primary']);
                                                                $origFilename = '';
                                                                if (!empty($rowOld['filename'])) {
                                                                  $origFilename = $rowOld['filename'];
                                                                } else {
                                                                  $origFilename = basename($rowOld['path']);
                                                                }
                                                                $indexForName = 1;
                                                                if (preg_match('/^\d+-(\d+)-/', $origFilename, $midx)) {
                                                                  $indexForName = intval($midx[1]);
                                                                }
                                                                $filename = $no . '-' . $indexForName . '-' . uniqid();
                                                                if ($ext !== '') $filename .= '.' . $ext;
                                                                $targetPath = $productDir . $filename;
                                                                // DB path uses product-specific folder
                                                                $relativePath = 'admin/Control/product/img/' . $no . '/' . $filename;
                                                                $dbPath = str_replace('\\', '/', $relativePath);

                                                                if (!move_uploaded_file($tmpName, $targetPath)) {
                                                                        $errors[] = 'Gagal menyimpan file gambar ke server.';
                                                                        // do not continue here; let cleanup happen later
                                                                } else {
                                                                        // delete old file
                                                                        @unlink($oldFilePath);
                                                                        // update record
                                                                        $stmtUpd = mysqli_prepare($kon, "UPDATE product_images SET path = ?, is_primary = ?, filename = ?, mime = ?, size = ?, part_number = ? WHERE no = ? AND product_no = ?");
                                                                        if ($stmtUpd) {
                                                                          mysqli_stmt_bind_param($stmtUpd, "sissisii", $dbPath, $isPrimary, $filename, $mime, $sizeBytes, $part_number, $replaceImgNo, $no);
                                                                          if (!mysqli_stmt_execute($stmtUpd)) {
                                                                            $errors[] = 'Gagal mengupdate gambar: ' . mysqli_stmt_error($stmtUpd);
                                                                            @unlink($targetPath);
                                                                          } else {
                                                                            $replaced = true;
                                                                          }
                                                                          mysqli_stmt_close($stmtUpd);
                                                                        } else {
                                                                          $errors[] = 'Gagal menyiapkan query update.';
                                                                          @unlink($targetPath);
                                                                        }
                                                                }
                                                        }
                                                        mysqli_stmt_close($stmtOld);
                                                }
                                        } else {
                                                // Add new images until max 5
                                                $seq = $addedCount + 1;
                                                $filename = $no . '-' . $seq . '-' . uniqid();
                                                if ($ext !== '') $filename .= '.' . $ext;
                                                $targetPath = $productDir . $filename;
                                                // DB path uses product-specific folder
                                                $relativePath = 'admin/Control/product/img/' . $no . '/' . $filename;
                                                $dbPath = str_replace('\\', '/', $relativePath);

                                                if (!move_uploaded_file($tmpName, $targetPath)) {
                                                        $errors[] = 'Gagal menyimpan file gambar ke server.';
                                                        continue;
                                                }

                                                $isPrimary = ($currentCount === 0 && $addedCount === 0) ? 1 : 0;
                                                // Use the same INSERT signature as add_prod to keep rows consistent
                                                $stmtIns = mysqli_prepare($kon, "INSERT INTO product_images (part_number, product_no, filename, path, mime, size, is_primary, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                                                if (!$stmtIns) {
                                                  $errors[] = 'Prepare product_images gagal: ' . mysqli_error($kon);
                                                  @unlink($targetPath);
                                                } else {
                                                  mysqli_stmt_bind_param($stmtIns, "sisssii", $part_number, $no, $filename, $dbPath, $mime, $sizeBytes, $isPrimary);
                                                  if (!mysqli_stmt_execute($stmtIns)) {
                                                    $errors[] = 'Gagal menambah gambar: ' . mysqli_stmt_error($stmtIns);
                                                    @unlink($targetPath);
                                                  } else {
                                                    $addedCount++;
                                                  }
                                                  mysqli_stmt_close($stmtIns);
                                                }
                                        }
                                }

                                if ($finfo) {
                                        finfo_close($finfo);
                                }

                                // Reload images
                                $productImages = [];
                                $stmtImages = mysqli_prepare($kon, "SELECT no, path, is_primary FROM product_images WHERE product_no = ? ORDER BY is_primary DESC, no ASC");
                                if ($stmtImages) {
                                        mysqli_stmt_bind_param($stmtImages, 'i', $no);
                                        mysqli_stmt_execute($stmtImages);
                                        $resImages = mysqli_stmt_get_result($stmtImages);
                                        while ($row = mysqli_fetch_assoc($resImages)) {
                                                $productImages[] = $row;
                                        }
                                        mysqli_stmt_close($stmtImages);
                                }
                        }
                }
        }

        // Update Product table (brand, category, description, and ITC if provided)
        if (empty($errors)) {
            $stmt = mysqli_prepare($kon, "UPDATE Product SET itc=?, description=?, brand=?, category=? WHERE `No` = ?");
            mysqli_stmt_bind_param($stmt, "ssssi", $itc, $description, $brand, $category, $no);
            if (!mysqli_stmt_execute($stmt)) {
              $errors[] = 'Gagal memperbarui data: ' . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        }

        // If ITC was entered (and was previously empty), update itc_pn table
        if ($itc !== '' && empty($data['itc']) && empty($errors)) { 
            $stmt2 = mysqli_prepare($kon, "INSERT INTO itc_pn (part_number, itc, description) VALUES (?, ?, ?)");
            if ($stmt2) {
                mysqli_stmt_bind_param($stmt2, "sss", $part_number, $itc, $description);
                if (!mysqli_stmt_execute($stmt2)) {
                    $errors[] = 'Gagal menyimpan itc_pn: ' . mysqli_stmt_error($stmt2);
                }
                mysqli_stmt_close($stmt2);
            }
        }

        // Show success message
        if (empty($errors)) {
          echo '<div id="success-msg" class="card" style="position:fixed;top:20px;right:20px;z-index:9999;border-left:4px solid #27ae60;background:#fff;box-shadow:0 6px 18px rgba(15,23,42,0.18);width:320px;"><div style="color:#155724;padding:10px 12px">Produk berhasil dirubah!</div></div>';
          echo '<script>setTimeout(() => { document.getElementById("success-msg").style.display = "none"; }, 500);</script>';
        }
}



?>
<?php if (!defined('IN_MENU_ADMIN')): ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit Product</title>
  <link rel="stylesheet" href="../../../../assets/css/style.css">
</head>
<body>
<?php // header removed: managed by menu_admin shell ?>
<?php endif; ?>
 <h2 style="margin:0 0 5px;">Edit Product</h2>
  <div class="card" style="margin-bottom:16px;height:700px;display:flex;flex-direction:column;">
      <?php if (!empty($errors)): ?>
        <div class="card" id="error-msg" style="position:fixed;top:20px;right:20px;z-index:9999;border-left:4px solid #e74c3c;background:#fff;box-shadow:0 6px 18px rgba(15,23,42,0.18);width:auto
        
        
        ;">
          <?php foreach($errors as $err): ?><div style="color:#8a1f1f;padding:6px 0"><?php echo htmlspecialchars($err); echo'<script>setTimeout(() => { document.getElementById("error-msg").style.display = "none"; }, 500);</script>';?></div><?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form id="editForm" method="POST" enctype="multipart/form-data" action="">
        <input type="hidden" name="no" value="<?php echo htmlspecialchars($data['No']); ?>">
        <div style="padding:0 5px;flex:1;overflow-y:auto;">
          <!-- SECTION 1: Basic Info -->
          <fieldset style="border:1px solid #e5e7eb;border-radius:6px;padding:5px;margin:10px 0;">
            <legend style="padding:0 8px;color:#1f2937;font-weight:600;font-size:14px;">📋 Info Dasar</legend>
          <div class="row-two">
            <div class="col">
              <div class="field-inline">
                <label for="part_number">Part Number</label>
                <input id="part_number" class="input" type="text" name="part_number" value="<?php echo htmlspecialchars($data['part_number']); ?>" readonly style="background:#f0f0f0;cursor:not-allowed;">
              </div>
            </div>
            <div class="col">
              <div class="field-inline">
                <label for="brand">Brand</label>
                <select id="brand" class="input" name="brand">
                  <option value="">-- Pilih brand --</option>
                  <?php
                    $brands = ['Komatsu','Bomag','Caterpillar','Scania','Volvo','Nissan','Hyva','Other'];
                    foreach ($brands as $b):
                  ?>
                    <option value="<?php echo htmlspecialchars($b); ?>" <?php if ($selectedBrand === $b) echo 'selected'; ?>><?php echo htmlspecialchars($b); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>

          <div class="row-two">
            <div class="col">
              <div class="field-inline">
                <label for="itc">Interchange (ITC)</label>
                <div style="display:flex;gap:8px;align-items:flex-start;">
                  <input
                    id="itc"
                    class="input"
                    type="text"
                    name="itc"
                    value="<?php echo htmlspecialchars($data['itc'] ?? ''); ?>"
                    style="flex:1;<?php echo !empty($data['itc']) ? 'background:#f0f0f0;cursor:not-allowed;' : ''; ?>"
                    <?php echo !empty($data['itc']) ? 'readonly' : ''; ?>
                  >
                  <button
                    class="btn btn-import"
                    type="button"
                    id="btnAddItc"
                    <?php echo empty($data['itc']) ? 'disabled' : ''; ?>
                    style="white-space:nowrap;"
                  >add</button>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="field-inline">
                <label for="category">Category</label>
                <select id="category" class="input" name="category">
                  <option value="">-- Pilih category --</option>
                  <?php foreach ($categories as $c): ?>
                    <option value="<?php echo htmlspecialchars($c); ?>" <?php if ($selectedCategory === $c) echo 'selected'; ?>><?php echo htmlspecialchars($c); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
          </fieldset>

        <!-- SECTION 2: Description -->
        <fieldset style="border:1px solid #e5e7eb;border-radius:6px;padding:6px;margin:12px 0;">
          <legend style="padding:0 8px;color:#1f2937;font-weight:600;font-size:14px;">📝 Deskripsi</legend>
          
          <div class="form-row">
            <label for="description" style="font-size:12px;">Deskripsi Produk</label>
            <textarea id="description" class="input" name="description" rows="3" placeholder="Detail produk, spesifikasi, kegunaan, dll..." style="resize:vertical;font-family:inherit;font-size:12px;"><?php echo htmlspecialchars($data['description'] ?? ''); ?></textarea>
          </div>
        </fieldset>
        <!-- SECTION 3: Gambar -->
        <fieldset style="border:1px solid #e5e7eb;border-radius:6px;padding:12px;margin:12px 0;">
          <legend style="padding:0 8px;color:#1f2937;font-weight:600;font-size:13px;">🖼️ Gambar (Maks 5)</legend>

          <div class="row-two" style="gap:12px;">
            <!-- Kolom kiri: Gambar saat ini -->
            <div class="col" style="display:flex;flex-direction:column;min-width:45%;">
              <p style="margin:0 0 8px 0;color:#4b5563;font-size:12px;font-weight:500;">Saat Ini:</p>
              <div id="images-container" style="display:flex;gap:8px;flex-wrap:wrap;max-height:130px;overflow-y:auto;align-content:flex-start;">
                  <?php if (!empty($productImages)): ?>
                    <?php 
                      $baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'], 4), '/');
                      foreach ($productImages as $img): 
                        if (!empty($img['path'])):
                          $relative = ltrim($img['path'], '/');
                          $imgSrc = ($baseUrl !== '' ? $baseUrl : '') . '/' . $relative;
                    ?>
                        <div style="position:relative;border:2px solid #ddd;border-radius:6px;overflow:visible;background:#f9f9f9;width:120px;height:120px;flex-shrink:0;">
                          <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="Product Image <?php echo $img['no']; ?>" style="display:block;width:100%;height:100%;object-fit:cover;border-radius:4px;">
                          <div style="position:absolute;top:2px;left:2px;background:rgba(0,0,0,0.8);color:#fff;padding:1px 4px;border-radius:2px;font-size:10px;font-weight:600;z-index:2;">No. <?php echo $img['no']; ?><?php if ($img['is_primary']) echo ' ⭐'; ?></div>
                          <div style="display:flex;gap:4px;position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,0.9);padding:6px 4px;border-radius:0 0 4px 4px;opacity:0;transition:opacity 0.2s ease;z-index:3;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'">
                            <button class="btn btn-sm" type="button" style="flex:1;padding:2px;font-size:10px;background:#3b82f6;color:white;border:none;border-radius:2px;cursor:pointer;" onclick="selectImageToReplace(<?php echo $img['no']; ?>)">✎</button>
                            <button class="btn btn-sm" type="button" style="flex:1;padding:2px;font-size:10px;background:#ef4444;color:white;border:none;border-radius:2px;cursor:pointer;" onclick="confirmDelete(<?php echo $img['no']; ?>)">✕</button>
                          </div>
                        </div>
                    <?php 
                        endif;
                      endforeach; 
                    ?>
                  <?php else: ?>
                    <div style="width:100%;text-align:center;padding:20px 10px;background:#f3f4f6;border:2px dashed #d1d5db;border-radius:6px;color:#6b7280;font-size:12px;">📭 Belum ada</div>
                  <?php endif; ?>
              </div>
            </div>

            <!-- Kolom kanan: Upload pengganti / tambah -->
            <div class="col" style="display:flex;flex-direction:column;hight:300px;min-width:45%;">
              <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:10px;hight:100%;">
                <input type="hidden" id="replace_img" name="replace_img" value="0">
                <label for="images" style="display:block;margin-bottom:6px;font-weight:500;color:#374151;font-size:12px;">📸 Pilih Gambar (max 3MB) - Akan disimpan otomatis</label>
                <input id="images" type="file" name="images[]" accept="image/jpeg,image/png,image/gif,image/*" multiple data-max="5" style="font-size:12px;padding:4px;border:1px solid #e6eef8;border-radius:6px;">
                <div style="font-size:11px;color:#64748b;margin-top:2px;">💡 Ctrl/Shift untuk pilih multiple | Klik Simpan untuk upload</div>
                <div id="image-counter" style="font-size:11px;color:#475467;margin-top:4px;font-weight:500;">0/5 dipilih</div>
                <div id="fotoPreviewList" style="max-height:200px;width:100%;margin-top:6px;display:flex;flex-wrap:wrap;gap:6px;overflow-y:auto;"></div>
                <span id="replace-mode" style="display:none;margin-top:6px;padding:4px 8px;background:#fecaca;color:#dc2626;border-radius:3px;font-weight:500;font-size:11px;"></span>
              </div>
            </div>
          </div>
        </fieldset>

        <!-- Hidden inputs for form control -->
        <input type="hidden" name="update" value="1">
        <input type="hidden" name="delete_images" id="delete_images_input" value="">
        <!-- Actions -->
        <div style="display:fixed;gap:8px;padding:12px 16px;background:#fff;border-top:1px solid #e5e7eb;border-radius:0;margin-top:0;position:fixed;bottom:70px;z-index:10;">
          <button class="btn btn-edit" type="submit" style="flex:1;font-size:13px;">💾 Simpan Perubahan</button>
          <button class="btn" type="button" onclick="goback()" style="flex:1;font-size:13px;">🔙 Kembali</button>
        </div>
        </div>

        
      </form>
      
    </div>
    
<?php if(!defined('IN_MENU_ADMIN')): ?>
</body>
</html>
<?php endif; ?>
<script>
// Image management functions
function selectImageToReplace(imgNo) {
  document.getElementById('replace_img').value = imgNo;
  var badge = document.getElementById('replace-mode');
  if (badge) {
    badge.textContent = 'Replace Mode (No. ' + imgNo + ')';
    badge.style.display = 'inline-block';
  }
  var input = document.getElementById('images');
  if (input) input.value = '';
  var preview = document.getElementById('fotoPreviewList');
  if (preview) preview.innerHTML = '';
  var counter = document.getElementById('image-counter');
  if (counter) {
    counter.textContent = '0/5 dipilih';
    counter.style.color = '#475467';
  }
  if (input) input.focus();
}

function cancelUpload() {
  document.getElementById('replace_img').value = '0';
  var badge = document.getElementById('replace-mode');
  if (badge) badge.style.display = 'none';
  var input = document.getElementById('images');
  if (input) input.value = '';
  var preview = document.getElementById('fotoPreviewList');
  if (preview) preview.innerHTML = '';
  var counter = document.getElementById('image-counter');
  if (counter) {
    counter.textContent = '0/5 dipilih';
    counter.style.color = '#475467';
  }
}

function confirmDelete(imgNo) {
  if (confirm('Yakin ingin menghapus gambar ini?')) {
    document.getElementById('delete_images_input').value = imgNo;
    var form = document.getElementById('editForm');
    if (form) form.submit();
  }
}

function goback() {
  window.location.href = window.location.href.split('?')[0] + '?menu=product';
}
// Preview multiple images
(function(){
  var max = 5;
  var urls = [];

  function clearUrls(){
    urls.forEach(function(u){ try { URL.revokeObjectURL(u); } catch(e){} });
    urls = [];
  }

  function getNodes(){
    return {
      input: document.getElementById('images'),
      preview: document.getElementById('fotoPreviewList'),
      counter: document.getElementById('image-counter')
    };
  }

  function render(files){
    var nodes = getNodes();
    if(!nodes.input || !nodes.preview) return;

    clearUrls();
    nodes.preview.innerHTML = '';
    var list = Array.prototype.slice.call(files || nodes.input.files || []);
    if(nodes.counter){
      var selected = list.length;
      if(selected > max){
        nodes.counter.textContent = max + '/' + max + ' dipilih (batas tercapai)';
        nodes.counter.style.color = '#b42318';
      } else {
        nodes.counter.textContent = selected + '/' + max + ' dipilih';
        nodes.counter.style.color = '#475467';
      }
    }

    list.slice(0, max).forEach(function(file){
      if(!/^image\//.test(file.type)) return;
      var wrapper = document.createElement('div');
      wrapper.style.width = '132px';
      wrapper.style.height = '132px';
      wrapper.style.border = '1px solid #e5e7eb';
      wrapper.style.borderRadius = '10px';
      wrapper.style.overflow = 'hidden';
      wrapper.style.display = 'flex';
      wrapper.style.alignItems = 'center';
      wrapper.style.justifyContent = 'center';

      var img = document.createElement('img');
      img.className = 'thumbnail';
      img.style.maxWidth = '100%';
      img.style.maxHeight = '100%';
      img.style.objectFit = 'cover';

      var url = URL.createObjectURL(file);
      urls.push(url);
      img.src = url;
      wrapper.appendChild(img);
      nodes.preview.appendChild(wrapper);
    });
  }

  var lastSignature = '';

  // Event listener for file selection
  var fileInput = document.getElementById('images');
  if (fileInput) {
    fileInput.addEventListener('change', function(e){
      render(this.files);
      lastSignature = Array.prototype.map.call(this.files || [], function(f){ return f.name + ':' + f.size; }).join('|');
    });
  }

  setInterval(function(){
    var input = document.getElementById('images');
    if(!input) return;
    var sig = Array.prototype.map.call(input.files || [], function(f){ return f.name + ':' + f.size; }).join('|');
    if(sig !== lastSignature){
      lastSignature = sig;
      render(input.files);
    }
  }, 400);
})();
</script>
<script>
// Tombol add ITC
document.addEventListener('DOMContentLoaded', function() {
  var itcInput = document.getElementById('itc');
  var btnAdd = document.getElementById('btnAddItc');
  var partNumber = document.getElementById('part_number').value.trim();

  // Jika ITC kosong, enable input dan disable tombol
  if (itcInput && btnAdd) {
    console.log('ITC value:', itcInput.value.trim()); // Debug log
    if (!itcInput.value.trim()) {
      itcInput.disabled = false;
      btnAdd.disabled = true;
      console.log('ITC empty - input enabled, button disabled'); // Debug log
      itcInput.addEventListener('input', function() {
        if (itcInput.value.trim()) {
          btnAdd.disabled = false;
        } else {
          btnAdd.disabled = true;
        }
      });
    }

    btnAdd.addEventListener('click', function() {
      var itc = itcInput.value.trim();
      if (!itc) return;
      var noField = document.querySelector('input[name="no"]');
      var noVal = noField ? noField.value : '';
      // Open the edit ITC form inside dashboard
      var url = '?menu=edit_itc&itc=' + encodeURIComponent(itc) + '&part_number=' + encodeURIComponent(partNumber) + (noVal ? '&no=' + encodeURIComponent(noVal) : '');
      console.log('Redirecting to:', url); // Debug log
      window.location.href = url;
    });
  }
});
</script>
</body>
</html>
