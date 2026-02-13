<?php if (!defined('IN_MENU_ADMIN')): ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Tambah Product</title>
  <link rel="stylesheet" href="../../../assets/css/style.css">
</head>
<body>
<?php // header removed: managed by menu_admin shell ?>
<?php endif; ?>
<?php
include(__DIR__ . '/../../../System/kon.php');

if (!function_exists('product_has_image_column')) {
  function product_has_image_column(mysqli $conn): bool {
    static $hasColumn = null;
    if ($hasColumn !== null) return $hasColumn;
    $result = mysqli_query($conn, "SHOW COLUMNS FROM Product LIKE 'image'");
    if ($result) {
      $hasColumn = mysqli_num_rows($result) > 0;
      mysqli_free_result($result);
    } else {
      $hasColumn = false;
    }
    return $hasColumn;
  }
}

// Tambah: autoload PhpSpreadsheet jika ada
if (file_exists(__DIR__ . '/../../../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../../../vendor/autoload.php';
}
$errors = [];

// Create product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
        $part_number = trim($_POST['part_number'] ?? '');
        $itc = trim($_POST['itc'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $brand = trim($_POST['brand'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $qty = intval($_POST['qty'] ?? 0);
        $brt = intval($_POST['berat'] ?? 0);
        $uom = trim($_POST['uom'] ?? '');
        $massa = trim($_POST['sat'] ?? '');
        $kondisi = trim($_POST['kondisi'] ?? '');
        $loc_stock = trim($_POST['loc_stock'] ?? '');

        if ($part_number === '') $errors[] = 'Part number harus diisi.';
        if ($description === '') $errors[] = 'Description harus diisi.';
        if ($brand === '') $errors[] = 'Brand harus diisi.';
        if ($category === '') $errors[] = 'Category harus diisi.';
        if ($uom === '') $errors[] = 'UoM harus diisi.';
        if ($massa === '') $errors[] = 'Sat harus diisi.';
        if ($kondisi === '') $errors[] = 'Kondisi harus diisi.';
        if ($loc_stock === '') $errors[] = 'Location harus diisi.';

        // Qty boleh kosong, default 0

        // Whitelist validation for brand and category
        $allowed_brands = ['Komatsu','Bomag','Caterpillar','Scania','Volvo','Nissan','Hyva','Other'];
        $allowed_categories = [
            'Engine','Electrical','Brake System','Cylinder','Axle & Stering','Cabin','Filter','Attachment','Final Drive','Hydraulic System','General','Alternatif'
        ];
        if ($brand !== '' && !in_array($brand, $allowed_brands)) $errors[] = 'Brand tidak valid.';
        if ($category !== '' && !in_array($category, $allowed_categories)) $errors[] = 'Category tidak valid.';

        $imageSlots = [];
        $maxImageCount = 5;
        $maxImageSizeBytes = 2 * 1024 * 1024;
        $allowedImageMime = ['image/jpeg', 'image/png', 'image/gif'];

        if (!empty($_FILES['images'])) {
          $files = $_FILES['images'];
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

          if ($actualCount > $maxImageCount) {
            $errors[] = 'Maksimal 5 gambar dapat diupload sekaligus.';
          } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            foreach ($names as $idx => $uploadName) {
              $uploadName = (string)$uploadName;
              $errorCode = $errorCodes[$idx] ?? UPLOAD_ERR_NO_FILE;
              if ($uploadName === '' || $errorCode === UPLOAD_ERR_NO_FILE) continue;
              if ($errorCode !== UPLOAD_ERR_OK) {
                $errors[] = "Upload gambar '{$uploadName}' gagal.";
                continue;
              }

              $tmpName = $tmpNames[$idx];
              $sizeBytes = (int)($sizes[$idx] ?? 0);
              $mime = $finfo ? finfo_file($finfo, $tmpName) : null;
              if (!$mime || !in_array($mime, $allowedImageMime, true)) {
                $errors[] = "Tipe file '{$uploadName}' tidak didukung.";
                continue;
              }

              if ($sizeBytes > $maxImageSizeBytes) {
                $errors[] = "Ukuran file '{$uploadName}' melebihi 2MB.";
                continue;
              }

              $extension = strtolower(pathinfo($uploadName, PATHINFO_EXTENSION));
              if ($extension === '') {
                $extension = $mime === 'image/png' ? 'png' : ($mime === 'image/gif' ? 'gif' : 'jpg');
              }

              $imageSlots[] = [
                'tmp_name' => $tmpName,
                'mime' => $mime,
                'size' => $sizeBytes,
                'extension' => $extension,
                'original_name' => $uploadName,
              ];
            }
            if ($finfo) {
              finfo_close($finfo);
            }
          }
        }

        $hasProductImageColumn = product_has_image_column($kon);
        $primaryImageData = null;
        if ($hasProductImageColumn && !empty($imageSlots)) {
          $primaryImageData = @file_get_contents($imageSlots[0]['tmp_name']);
          if ($primaryImageData === false) {
            $primaryImageData = null;
          }
        }

        if (empty($errors)) {
                $storedFiles = [];
                $transactionStarted = false;
                $ok = false;
                try {
                        if (!mysqli_begin_transaction($kon)) {
                          throw new RuntimeException('Gagal memulai transaksi database.');
                        }
                        $transactionStarted = true;

                        if ($hasProductImageColumn && $primaryImageData !== null) {
                          $stmt = mysqli_prepare($kon, "INSERT INTO Product (part_number, itc, description, brand, category, Qty, UoM, berat, massa, kondisi,loc_stock,image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                          if (!$stmt) {
                            throw new RuntimeException('Prepare product gagal: ' . mysqli_error($kon));
                          }
                          mysqli_stmt_bind_param($stmt, "sssssisisssb", $part_number, $itc, $description, $brand, $category, $qty, $uom, $brt, $massa, $kondisi, $loc_stock, $primaryImageData);
                          mysqli_stmt_send_long_data($stmt, 11, $primaryImageData);
                        } else {
                          $stmt = mysqli_prepare($kon, "INSERT INTO Product (part_number, itc, description, brand, category, Qty, UoM, berat, massa,kondisi,loc_stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                          if (!$stmt) {
                            throw new RuntimeException('Prepare product gagal: ' . mysqli_error($kon));
                          }
                          mysqli_stmt_bind_param($stmt, "sssssisisss", $part_number, $itc, $description, $brand, $category, $qty, $uom, $brt, $massa, $kondisi, $loc_stock);
                        }

                        if (!mysqli_stmt_execute($stmt)) {
                          $msg = mysqli_stmt_error($stmt);
                          mysqli_stmt_close($stmt);
                          throw new RuntimeException('Gagal menyimpan produk: ' . $msg);
                        }
                        mysqli_stmt_close($stmt);

                        $productNo = mysqli_insert_id($kon);

                        if ($part_number !== '' && $itc !== '' && $description !== '') {
                          $stmt2 = mysqli_prepare($kon, "INSERT INTO itc_pn (part_number, itc, description) VALUES (?, ?, ?)");
                          if (!$stmt2) {
                            throw new RuntimeException('Prepare itc_pn gagal: ' . mysqli_error($kon));
                          }
                          mysqli_stmt_bind_param($stmt2, "sss", $part_number, $itc, $description);
                          if (!mysqli_stmt_execute($stmt2)) {
                            $msg = mysqli_stmt_error($stmt2);
                            mysqli_stmt_close($stmt2);
                            throw new RuntimeException('Gagal menyimpan itc_pn: ' . $msg);
                          }
                          mysqli_stmt_close($stmt2);
                        }

                        // Simpan juga ke tabel itc_product (hanya kolom utama)
                        if ($part_number !== '') {
                          $stmtProductItc = mysqli_prepare($kon, "INSERT INTO itc_product (part_number, description, brand) VALUES (?, ?, ?)");
                          if (!$stmtProductItc) {
                            throw new RuntimeException('Prepare itc_product gagal: ' . mysqli_error($kon));
                          }
                          mysqli_stmt_bind_param($stmtProductItc, "sss", $part_number, $description, $brand);
                          if (!mysqli_stmt_execute($stmtProductItc)) {
                            $msg = mysqli_stmt_error($stmtProductItc);
                            mysqli_stmt_close($stmtProductItc);
                            throw new RuntimeException('Gagal menyimpan itc_product: ' . $msg);
                          }
                          mysqli_stmt_close($stmtProductItc);
                        }

                        if (!empty($imageSlots)) {
                          $uploadBaseDir = __DIR__ . '/../img';
                          if (!is_dir($uploadBaseDir) && !mkdir($uploadBaseDir, 0775, true)) {
                            throw new RuntimeException('Gagal membuat folder upload gambar.');
                          }

                          $targetDir = $uploadBaseDir . '/' . $productNo;
                          if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true)) {
                            throw new RuntimeException('Gagal membuat folder gambar untuk produk.');
                          }

                          foreach ($imageSlots as $index => $img) {
                            $newFilename = $productNo . '-' . ($index + 1) . '-' . uniqid();
                            if ($img['extension'] !== '') {
                              $newFilename .= '.' . $img['extension'];
                            }
                            $targetPath = $targetDir . '/' . $newFilename;
                            if (!move_uploaded_file($img['tmp_name'], $targetPath)) {
                              throw new RuntimeException("Gagal menyimpan file '{$img['original_name']}'.");
                            }
                            $storedFiles[] = $targetPath;
                            $relativePath = 'admin/Control/product/img/' . $productNo . '/' . $newFilename;
                            $relativePath = str_replace('\\', '/', $relativePath);

                            $stmtImg = mysqli_prepare($kon, "INSERT INTO product_images (part_number, product_no, filename, path, mime, size, is_primary, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                            if (!$stmtImg) {
                              throw new RuntimeException('Prepare product_images gagal: ' . mysqli_error($kon));
                            }
                            $isPrimary = $index === 0 ? 1 : 0;
                            mysqli_stmt_bind_param($stmtImg, "sisssii", $part_number, $productNo, $newFilename, $relativePath, $img['mime'], $img['size'], $isPrimary);
                            if (!mysqli_stmt_execute($stmtImg)) {
                              $msg = mysqli_stmt_error($stmtImg);
                              mysqli_stmt_close($stmtImg);
                              throw new RuntimeException('Gagal menyimpan gambar produk: ' . $msg);
                            }
                            mysqli_stmt_close($stmtImg);
                          }
                        }

                        if (!mysqli_commit($kon)) {
                          throw new RuntimeException('Gagal mengesahkan transaksi.');
                        }
                        $transactionStarted = false;
                        $ok = true;
                } catch (Throwable $e) {
                        if ($transactionStarted) {
                          mysqli_rollback($kon);
                        }
                        foreach ($storedFiles as $stored) {
                          if (is_file($stored)) {
                            unlink($stored);
                          }
                        }
                        if ($e instanceof mysqli_sql_exception && $e->getCode() == 1062) {
                          $errors[] = 'Part number sudah ada, silakan gunakan yang berbeda.';
                        } else {
                          $errors[] = 'Gagal menyimpan: ' . $e->getMessage();
                        }
                        $ok = false;
                }
                if (!empty($ok)) {
                        echo '<div id="success-msg" class="card" style="position:fixed;top:20px;right:20px;z-index:9999;border-left:4px solid #27ae60;background:#fff;box-shadow:0 6px 18px rgba(15,23,42,0.18);width:320px;"><div style="color:#155724;padding:10px 12px">Produk berhasil ditambahkan!</div></div>';
                        echo '<script>document.getElementById("part_number").focus(); document.getElementById("part_number").value = ""; document.getElementById("itc").value = ""; document.getElementById("description").value = ""; document.getElementById("brand").value = ""; document.getElementById("category").value = ""; document.getElementById("qty").value = ""; var beratEl = document.getElementById("berat"); if (beratEl) beratEl.value = ""; var uomEl = document.getElementById("uom"); if (uomEl) uomEl.value = ""; var satEl = document.getElementById("sat"); if (satEl) satEl.value = ""; var imgInput = document.getElementById("images"); if (imgInput) imgInput.value = ""; var preview = document.getElementById("fotoPreviewList"); if (preview) preview.innerHTML = ""; var counter = document.getElementById("image-counter"); if (counter) { counter.textContent = "0/5 dipilih"; counter.style.color = "#475467"; } setTimeout(() => { document.getElementById("success-msg").style.display = "none"; }, 20000);</script>';
                        // header('Location: admin_dashboard.php?menu=product'); exit;
                }
        }
}

// Simple CSV/Excel import: expected order part_number,itc,description,brand,category,qty
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import'])) {
  if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = 'Pilih file CSV atau Excel.';
  } else {
    $tmp = $_FILES['file']['tmp_name'];
    $filename = $_FILES['file']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if ($ext === 'csv') {
      if (($h = fopen($tmp, 'r')) !== false) {
        $row = 0;
        $importErrors = [];
        $importedCount = 0;
        while (($r = fgetcsv($h, 0, ',')) !== false) {
          $row++;
          // treat first row as header if it contains known header tokens
          if ($row === 1) {
            $cells = array_map(function($c){ return strtolower(trim((string)$c)); }, $r);
            $known = ['part_number','part number','pn','part','itc','description','brand','category','qty','quantity','name','nama'];
            $isHeader = false;
            foreach ($cells as $c) { if ($c !== '' && in_array($c, $known, true)) { $isHeader = true; break; } }
            if ($isHeader) continue; // skip header row
          }
          $pn = trim($r[0] ?? '');
          $itc = trim($r[1] ?? '');
          $desc = trim($r[2] ?? '');
          $brand = trim($r[3] ?? '');
          $cat = trim($r[4] ?? '');
          $qty = intval($r[5] ?? 0);
          $uom = trim($r[6] ?? '');
          $brt = intval($r[7] ?? 0);
          $massa = trim($r[8] ?? '');
          $kondisi = trim($r[9] ?? '');
          $loc_stock = trim($r[10] ?? '');
          if ($pn === '') continue;

          $allowed_brands = ['Komatsu','Bomag','Caterpillar','Scania','Volvo','Nissan','Hyva','Other'];
          $allowed_categories = [
            'Engine','Electrical','Brake System','Cylinder','Axle & Stering','Cabin','Filter','Attachment','Final Drive','Hydraulic System','General','Alternatif'
          ];

          // For imports be permissive: fill missing values and accept unknown brands/categories.
          if ($brand === '') $brand = 'Other';
          if ($cat === 'categories') $cat = 'General';

          // Validate brand and category (case-insensitive)
          $brandLower = strtolower($brand);
          $foundBrand = null;
          foreach ($allowed_brands as $ab) {
            if (strtolower($ab) === $brandLower) {
              $foundBrand = $ab;
              break;
            }
          }
          if ($foundBrand === null) {
            $importErrors[] = "Row $row: Brand '$brand' tidak valid. Menggunakan 'Other'.";
            $brand = 'Other';
          } else {
            $brand = $foundBrand;
          }
          if ($cat !== '') {
            $catLower = strtolower($cat);
            $foundCat = null;
            foreach ($allowed_categories as $ac) {
              if (strtolower($ac) === $catLower) {
                $foundCat = $ac;
                break;
              }
            }
            if ($foundCat === null) {
              $importErrors[] = "Row $row: Category '$cat' tidak valid.";
              $cat = '';
            } else {
              $cat = $foundCat;
            }
          }

          // Check if part_number already exists
          $check = mysqli_prepare($kon, "SELECT 1 FROM Product WHERE part_number = ?");
          mysqli_stmt_bind_param($check, "s", $pn);
          mysqli_stmt_execute($check);
          mysqli_stmt_bind_result($check, $exists);
          if (mysqli_stmt_fetch($check)) {
            $importErrors[] = "Row $row: Part number '$pn' sudah ada.";
            mysqli_stmt_close($check);
            continue;
          }
          mysqli_stmt_close($check);

          $stmt = mysqli_prepare($kon, "INSERT INTO Product (part_number, itc, description, brand, category, Qty, UoM, berat, massa,kondisi,loc_stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
          if (!$stmt) { 
            $importErrors[] = "Row $row: prepare failed: " . mysqli_error($kon); 
            continue; 
          }
          mysqli_stmt_bind_param($stmt, 'sssssisisss', $pn, $itc, $desc, $brand, $cat, $qty, $uom, $brt, $massa, $kondisi, $loc_stock);
          if (!mysqli_stmt_execute($stmt)) { 
            $importErrors[] = "Row $row: insert product failed: " . mysqli_stmt_error($stmt); 
          }
          mysqli_stmt_close($stmt);

          if ($pn !== '' && $itc !== '' && $desc !== '') {
            $stmt2 = mysqli_prepare($kon, "INSERT IGNORE INTO itc_pn (part_number, itc, description) VALUES (?, ?, ?)");
            if (!$stmt2) { $importErrors[] = "Row $row: prepare itc_pn failed: " . mysqli_error($kon); continue; }
            mysqli_stmt_bind_param($stmt2, "sss", $pn, $itc, $desc);
            if (!mysqli_stmt_execute($stmt2)) { $importErrors[] = "Row $row: insert itc_pn failed: " . mysqli_stmt_error($stmt2); }
            mysqli_stmt_close($stmt2);
          }

          // Simpan juga ke itc_product (hanya part_number, description, brand)
          if ($pn !== '') {
            $stmtProductItc = mysqli_prepare($kon, "INSERT IGNORE INTO itc_product (part_number, description, brand) VALUES (?, ?, ?)");
            if ($stmtProductItc) {
              mysqli_stmt_bind_param($stmtProductItc, "sss", $pn, $desc, $brand);
              if (!mysqli_stmt_execute($stmtProductItc)) { $importErrors[] = "Row $row: insert itc_product failed: " . mysqli_stmt_error($stmtProductItc); }
              mysqli_stmt_close($stmtProductItc);
            } else {
              $importErrors[] = "Row $row: prepare itc_product failed: " . mysqli_error($kon);
            }
          }

          $importedCount++;
        }
        fclose($h);
        if (empty($importErrors)) {
          echo '<div id="success-msg" class="card" style="position:fixed;top:20px;right:20px;z-index:9999;border-left:4px solid #27ae60;background:#fff;box-shadow:0 6px 18px rgba(15,23,42,0.18);width:320px;"><div style="color:#155724;padding:10px 12px">Import berhasil! ' . $importedCount . ' produk telah ditambahkan.</div></div>';
          echo '<script>setTimeout(() => { document.getElementById("success-msg").style.display = "none"; }, 500);</script>';
        } else {
          $errors[] = 'Import selesai dengan ' . count($importErrors) . ' error. ' . $importedCount . ' produk berhasil diimport. Error: ' . implode('; ', array_slice($importErrors,0,3));
        }
      } else {
        $errors[] = 'Gagal membuka file CSV.';
      }
    } elseif ($ext === 'xls' || $ext === 'xlsx') {
      if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
        $errors[] = 'PhpSpreadsheet tidak terpasang. Gunakan file CSV atau install package phpoffice/phpspreadsheet via Composer.';
      } else {
        try {
          $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmp);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        $row = 0;
        $importErrors = [];
        $importedCount = 0;
        foreach ($rows as $r) {
          $row++;
          if ($row === 1) {
            $cells = array_map(function($c){ return strtolower(trim((string)$c)); }, $r);
            $known = ['part_number','part number','pn','part','itc','description','brand','category','qty','quantity','name','nama'];
            $isHeader = false;
            foreach ($cells as $c) { if ($c !== '' && in_array($c, $known, true)) { $isHeader = true; break; } }
            if ($isHeader) continue;
          }
          $pn = trim($r[0] ?? '');
          $itc = trim($r[1] ?? '');
          $desc = trim($r[2] ?? '');
          $brand = trim($r[3] ?? '');
          $cat = trim($r[4] ?? '');
          $qty = intval($r[5] ?? 0);
          $uom = trim($r[6] ?? '');
          $brt = intval($r[7] ?? 0);
          $massa = trim($r[8] ?? '');
          $kondisi = trim($r[9] ?? '');
          $loc_stock = trim($r[10] ?? '');

          // Skip empty part numbers
          if ($pn === '') continue;

          $allowed_brands = ['Komatsu','Bomag','Caterpillar','Scania','Volvo','Nissan','Hyva','Other'];
          $allowed_categories = [
            'Engine','Electrical','Brake System','Cylinder','Axle & Stering','Cabin','Filter','Attachment','Final Drive','Hydraulic System','General','Alternatif'
          ];

          // For imports be permissive: fill missing values and accept unknown brands/categories.
          if ($brand === '') $brand = 'Other';
          if ($cat === '') $cat = 'General';
          if ($loc_stock === '') $loc_stock = 'WHS';
          IF($kondisi === '') $kondisi = 'New';
          

          // Validate brand and category (case-insensitive)
          $brandLower = strtolower($brand);
          $foundBrand = null;
          foreach ($allowed_brands as $ab) {
            if (strtolower($ab) === $brandLower) {
              $foundBrand = $ab;
              break;
            }
          }
          if ($foundBrand === null) {
            $importErrors[] = "Row $row: Brand '$brand' tidak valid. Menggunakan 'Other'.";
            $brand = 'Other';
          } else {
            $brand = $foundBrand;
          }
          if ($cat !== '') {
            $catLower = strtolower($cat);
            $foundCat = null;
            foreach ($allowed_categories as $ac) {
              if (strtolower($ac) === $catLower) {
                $foundCat = $ac;
                break;
              }
            }
            if ($foundCat === null) {
              $importErrors[] = "Row $row: Category '$cat' tidak valid.";
              $cat = 'General';
            } else {
              $cat = $foundCat;
            }
          }

          // Check if part_number already exists
          $check = mysqli_prepare($kon, "SELECT 1 FROM Product WHERE part_number = ?");
          mysqli_stmt_bind_param($check, "s", $pn);
          mysqli_stmt_execute($check);
          mysqli_stmt_bind_result($check, $exists);
          $duplicate = mysqli_stmt_fetch($check);
          mysqli_stmt_close($check);

          if ($duplicate) {
            $importErrors[] = "Row $row: Part number '$pn' sudah ada.";
            continue;
          }

          $stmt = mysqli_prepare($kon, "INSERT INTO Product (part_number, itc, description, brand, category, Qty, UoM, berat, massa,kondisi,loc_stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
          if (!$stmt) { 
            $importErrors[] = "Row $row: prepare failed: " . mysqli_error($kon); 
            continue; 
          }
          mysqli_stmt_bind_param($stmt, 'sssssisisss', $pn, $itc, $desc, $brand, $cat, $qty, $uom, $brt, $massa, $kondisi, $loc_stock);
          if (!mysqli_stmt_execute($stmt)) { 
            $importErrors[] = "Row $row: insert product failed: " . mysqli_stmt_error($stmt); 
          }
          mysqli_stmt_close($stmt);
          // Jika itc tidak kosong, masukkan ke itc_pn
          if ($itc !== ''){
            $stmt2 = mysqli_prepare($kon, "INSERT INTO itc_pn (part_number, itc, description) VALUES (?, ?, ?)");
            if (!$stmt2) { $importErrors[] = "Row $row: prepare itc_pn failed: " . mysqli_error($kon); continue; }
            mysqli_stmt_bind_param($stmt2, "sss", $pn, $itc, $desc);
            if (!mysqli_stmt_execute($stmt2)) { $importErrors[] = "Row $row: insert itc_pn failed: " . mysqli_stmt_error($stmt2); }
            mysqli_stmt_close($stmt2);
          }

          // Simpan juga ke itc_product (hanya part_number, description, brand)
          if ($pn !== ''&& $desc !== '' && $brand !== '') {
            $stmtBrand = mysqli_prepare($kon, "INSERT IGNORE INTO itc_product (part_number, description, brand) VALUES (?, ?, ?)");
            if ($stmtBrand) {
              mysqli_stmt_bind_param($stmtBrand, "sss", $pn, $desc, $brand);
              if (!mysqli_stmt_execute($stmtBrand)) { $importErrors[] = "Row $row: insert itc_product failed: " . mysqli_stmt_error($stmtBrand); }
              mysqli_stmt_close($stmtBrand);
            } else {
              $importErrors[] = "Row $row: prepare itc_product failed: " . mysqli_error($kon);
            }
          }

          $importedCount++;
        }
        if (empty($importErrors)) {
          echo '<div id="success-msg" class="card" style="position:fixed;top:20px;right:20px;z-index:9999;border-left:4px solid #27ae60;background:#fff;box-shadow:0 6px 18px rgba(15,23,42,0.18);width:320px;"><div style="color:#155724;padding:10px 12px">Import berhasil! ' . $importedCount . ' produk telah ditambahkan.</div></div>';
          echo '<script>setTimeout(() => { document.getElementById("success-msg").style.display = "none"; }, 500);</script>';
        } else {
          $errors[] = 'Import selesai dengan ' . count($importErrors) . ' error. ' . $importedCount . ' produk berhasil diimport. Error: ' . implode('; ', array_slice($importErrors,0,3));
        }
      } catch (Exception $e) {
        $errors[] = 'Gagal membaca file Excel: ' . $e->getMessage();
      }
    }
  } else {
    $errors[] = 'Format file tidak didukung. Hanya CSV, XLS, XLSX.';
  }
}
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

                // current selection (preserve after submit)
                $selectedCategory = '';
                if (isset($_POST['category'])) $selectedCategory = trim($_POST['category']);
                elseif (isset($category) && $category !== '') $selectedCategory = $category;

                // preserve selected brand
                $selectedBrand = '';
                if (isset($_POST['brand'])) $selectedBrand = trim($_POST['brand']);
                elseif (isset($brand) && $brand !== '') $selectedBrand = $brand;

                // preserve selected UoM
                $selectedUom = isset($_POST['uom']) ? trim($_POST['uom']) : '';

                // preserve selected weight unit
                $selectedSat = isset($_POST['sat']) ? trim($_POST['sat']) : '';

                // preserve selected kondisi
                $selectedKondisi = isset($_POST['kondisi']) ? trim($_POST['kondisi']) : '';

if(defined('IN_MENU_ADMIN')) { ?>
<?php if (!empty($errors)): ?>
        <div class="card" style="position:fixed;top:20px;right:20px;z-index:9999;border-left:4px solid #e74c3c;background:#fff;box-shadow:0 6px 18px rgba(15,23,42,0.18);width:320px;">
          <?php foreach ($errors as $err): ?>
            <div style="color:#8a1f1f;padding:10px 12px"><?php echo htmlspecialchars($err); ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
  <!-- Hanya tampilkan form saja -->
  <h2>Tambah Product</h2>
   <div class="container-fluid p-1 card-inner" style="overflow:hidden; background:#fff; box-shadow:0 2px 2px #2563eb22; height:725px;">
    <form method="POST" enctype="multipart/form-data" class="card form" style="height: 725px;; overflow:hidden;">
      <div class="row-two">
        <div class="col">
          <div class="field-inline">
            <label for="part_number">Part Number</label>
            <input id="part_number" class="input" type="text" name="part_number" required>
          </div>
        </div>
        <div class="col">
          <div class="field-inline">
            <label for="itc">Interchange</label>
            <input id="itc" class="input" type="text" name="itc">
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="field-inline field-multiline">
          <label for="description">Description</label>
          <textarea id="description" class="input" name="description" rows="4" required></textarea>
        </div>
      </div>

      <div class="row-two">
        <div class="col">
          <div class="field-inline">
            <label for="brand">Brand</label>
            <select id="brand" class="input" name="brand" required>
            <option value="">-- Pilih brand --</option>
            <?php
              $brands = [
                'Komatsu', 'Bomag', 'Caterpillar', 'Scania', 'Volvo', 'Nissan', 'Hyva', 'Other',
              ];
              foreach ($brands as $b):
            ?>
              <option value="<?php echo htmlspecialchars($b); ?>" <?php if ($selectedBrand === $b) echo 'selected'; ?>><?php echo htmlspecialchars($b); ?></option>
            <?php endforeach; ?>
          </select>
          </div>
        </div>
        <div class="col">
          <div class="field-inline">
            <label for="category">Category</label>
            <select id="category" class="input" name="category" required>
            <option value="">-- Pilih category --</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?php echo htmlspecialchars($c); ?>" <?php if ($selectedCategory === $c) echo 'selected'; ?>><?php echo htmlspecialchars($c); ?></option>
            <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <div class="row-two">
        <div class="col">
          <div class="field-inline">
            <label for="qty">Quantity</label>
            <input id="qty" class="input" type="number" name="qty" min="0">
             <label for="uom">UoM</label>
            <select id="uom" class="input" name="uom" required>
              <option value="">-- Pilih UoM --</option>
              <?php
                $uomOptions = ['PC','EA','AU','Set','Pkt'];
                foreach ($uomOptions as $uom):
              ?>
                <option value="<?php echo htmlspecialchars($uom); ?>" <?php if (strcasecmp($selectedUom, $uom) === 0) echo 'selected'; ?>><?php echo htmlspecialchars($uom); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col">
          <div class="field-inline">
            <label for="berat">Berat</label>
            <input id="berat" class="input" type="number" name="berat" min="0" required>
            <label for="sat">Sat</label>
            <select id="sat" class="input" name="sat" required>
              <option value="">-- Pilih Sat --</option>
              <?php
                $satOptions = ['Gram','Ons','Kg','Kw','Ton'];
                foreach ($satOptions as $sat):
              ?>
                <option value="<?php echo htmlspecialchars($sat); ?>" <?php if (strcasecmp($selectedSat, $sat) === 0) echo 'selected'; ?>><?php echo htmlspecialchars($sat); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      <div class="row-two">
        <div class="col">
          <div class="field-inline">
            <label for="kondisi">Kondisi</label>
            <select id="kondisi" class="input" name="kondisi" required>
            <option value="">-- Pilih kondisi --</option>
            <?php
              $kondisi = [
                'New', 'Second', 'Recondition',
              ];
              foreach ($kondisi as $k):
            ?>
              <option value="<?php echo htmlspecialchars($k); ?>" <?php if ($selectedKondisi === $k) echo 'selected'; ?>><?php echo htmlspecialchars($k); ?></option>
            <?php endforeach; ?>
          </select>
          </div>
        </div>
        <div class="col">
          <div class="field-inline">
            <label for="loc_stock">Location</label>
            <input id="loc_stock" class="input" type="text" name="loc_stock" required>
          </div>
        </div>
      </div>
      <div class="row-two media-row">
        <div class="col" style="display:flex; flex-direction:column; width:50%; height:100%;">
          <div class="form-row" style="flex:1; display:flex; width:100%; flex-direction:column;">
            <label for="images">Image Product (opsional, JPG/PNG/GIF max 3MB) - pilih hingga 5 gambar</label>
            <input id="images" class="input" type="file" name="images[]" accept="image/jpeg,image/png,image/gif,image/*" multiple data-max="5">
            <div style="font-size:12px;color:#64748b;margin-top:4px;">Gunakan tombol Ctrl/Shift untuk memilih lebih dari satu file.</div>
            <div id="image-counter" style="font-size:12px;color:#475467;margin-top:4px;">0/5 dipilih</div>
            <div id="fotoPreviewList" style="flex:1;min-height:200px;width:100%;margin-top:8px;display:flex;flex-wrap:wrap;gap:8px;"></div>
              <div class="form-actions form-actions--inline" style="margin-top:auto; justify-content:flex-start;">
                <button class="btn btn-add" type="submit" name="submit">Simpan</button>
                <button class="btn" type="reset">Reset</button>
                <a class="btn" href="?menu=product">Kembali</a>
              </div>
          </div>
        </div>
        
          <div class="import-card" style="height: 415px;width: 50%;padding: 16px;box-sizing: border-box;border: 1px solid #e5e7eb;border-radius: 8px;background-color: #f9fafb;">
            <h3>Import Produk dari CSV/Excel</h3>
            <div class="import-card__note">
              <div class="import-card__note-title">Instruksi:</div>
              <ul>
                <li>Format: part_number, itc, description, brand, category, qty, UoM, berat,massa</li>
                <li>Maksimal file: 10MB untuk CSV, 50MB untuk Excel</li>
              </ul>
            </div>
            <div class="import-card__downloads">
              <div class="import-card__downloads-label">Download template:</div>
              <div class="import-card__downloads-buttons">
                <a class="btn btn-import" href="../../../download.php?file=sample_import.csv">CSV Template</a>
                <a class="btn btn-import btn-import--excel" href="../../../download.php?file=sample_import.xlsx">Excel Template</a>
              </div>
            </div>
            <label class="import-card__file-label" for="file">Pilih File CSV atau Excel</label>
            <input class="input" type="file" id="file" name="file" accept=".csv,.xls,.xlsx">
            <div id="file-info" class="import-card__file-info"></div>
            <div class="import-card__actions">
              <button class="btn btn-add btn-import-submit" type="submit" name="import" formnovalidate>Import Data</button>
            </div>
          </div>
        
      </div>

     

    </form>
  
 </div>

  <script>
  // Preview multiple images
  (function(){
    if (window.__productImagePreviewBound) return;
    window.__productImagePreviewBound = true;
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

    // Attach listener at document level to survive dynamic reloads
    document.addEventListener('change', function(e){
      var input = document.getElementById('images');
      if(!input || e.target !== input) return;
      render(e.target.files);
      lastSignature = Array.prototype.map.call(input.files || [], function(f){ return f.name + ':' + f.size; }).join('|');
    }, true);

    // Fallback polling in case change event is missed
    setInterval(function(){
      var input = document.getElementById('images');
      if(!input) return;
      var sig = Array.prototype.map.call(input.files || [], function(f){ return f.name + ':' + f.size; }).join('|');
      if(sig !== lastSignature){
        lastSignature = sig;
        render(input.files);
      }
    }, 400);

    // Initial render when the script runs (in case files are already present)
    render();
  })();

  // File info for import
  (function(){
    var fileInput = document.getElementById('file');
    var fileInfo = document.getElementById('file-info');
    if(!fileInput || !fileInfo) return;

    fileInput.addEventListener('change', function(){
      var file = this.files && this.files[0];
      if(!file){
        fileInfo.textContent = '';
        return;
      }

      var size = (file.size / 1024 / 1024).toFixed(2);
      var type = file.name.split('.').pop().toUpperCase();
      fileInfo.textContent = 'File: ' + file.name + ' | Size: ' + size + ' MB | Type: ' + type;

      // Basic validation
      var maxSize = type === 'CSV' ? 10 : 50; // 10MB for CSV, 50MB for Excel
      if(size > maxSize){
        fileInfo.style.color = '#e74c3c';
        fileInfo.textContent += ' ⚠️ File terlalu besar (max ' + maxSize + 'MB)';
      } else {
        fileInfo.style.color = '#27ae60';
        fileInfo.textContent += ' ✅ File siap diupload';
      }
    });
  })();

  // Force download for browsers that don't support download attribute
  (function(){
    var downloadLinks = document.querySelectorAll('a[href*="download.php"]');
    downloadLinks.forEach(function(link){
      link.addEventListener('click', function(e){
        // PHP script handles the download, but add visual feedback
        this.style.opacity = '0.7';
        setTimeout(() => { this.style.opacity = '1'; }, 1000);
      });
    });
  })();
  </script>
  <?php
  return;
}
?>
</div>
  <?php // footer removed: managed by menu_admin shell ?>

</body>
  </html>