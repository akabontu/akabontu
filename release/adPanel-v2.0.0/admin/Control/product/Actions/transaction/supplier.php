<?php
require_once __DIR__ . '/../../../../System/kon.php';
require_once __DIR__ . '/../../../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$errors = [];
$success = false;
$importSuccess = 0;
$importErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_supplier'])) {
  if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
 
    try {
      if ($fileExt === 'csv') {
        // Process CSV
        $handle = fopen($fileTmp, 'r');
        if ($handle) {
          $rowNum = 0;
          while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $rowNum++;
            if ($rowNum === 1) continue; // Skip header
            
            $nama = trim($data[0] ?? '');
            $alamat = trim($data[1] ?? '');
            $contact = trim($data[2] ?? '');
            // Force phone to string to prevent Excel scientific notation
            $phone = trim(strval($data[3] ?? ''));
            $email = trim($data[4] ?? '');
            $bank = trim($data[5] ?? '');
            $noRek = trim($data[6] ?? '');
            $npwp = trim($data[7] ?? '');
            if ($nama === '') {
              $importErrors[] = "Baris $rowNum: Nama supplier kosong";
              continue;
            }
            
            // Validasi phone setelah variabel ada (mobile/landline)
            if ($phone !== '' && !preg_match("/^[\+]?[0-9\-\(\)\s\.]{10,20}$/", $phone)) {
                $importErrors[] = "Baris $rowNum: Format Nomor HP tidak valid (value: '$phone', length: " . strlen($phone) . ")";
                continue;
            }

            // Validasi NPWP setelah variabel ada  
            if ($npwp !== '' && !preg_match("/^[0-9]{2}\.?[0-9]{3}\.?[0-9]{3}\.?[0-9]-?[0-9]{3}\.?[0-9]{3}$/", $npwp)) {
                $importErrors[] = "Baris $rowNum: Format NPWP tidak valid";
                continue;
            }

            // Convert empty values to empty string for NOT NULL columns
            $contact_val = ($contact === '') ? '' : $contact;
            $phone_val = ($phone === '') ? '' : $phone;
            $email_val = ($email === '') ? '' : $email;
            $bank_val = ($bank === '') ? '' : $bank;
            $noRek_val = ($noRek === '') ? '' : $noRek;
            $npwp_val = ($npwp === '') ? '' : $npwp;
            
            $stmt = mysqli_prepare($kon, "INSERT INTO supplier (nama, alamat, contact,  phone, email,  Bank, no_rek, NPWP) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
              mysqli_stmt_bind_param($stmt, 'ssssssss', $nama, $alamat, $contact_val, $phone_val, $email_val, $bank_val, $noRek_val, $npwp_val);
              if (mysqli_stmt_execute($stmt)) {
                $importSuccess++;
              } else {
                $importErrors[] = "Baris $rowNum: " . mysqli_stmt_error($stmt);
              }
              mysqli_stmt_close($stmt);
            }
          }
          fclose($handle);
        }
      } elseif (in_array($fileExt, ['xlsx', 'xls'])) {
        // Process Excel
        $spreadsheet = IOFactory::load($fileTmp);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();
        
        for ($rowNum = 2; $rowNum <= $highestRow; $rowNum++) {
          // Get formatted string value to preserve leading zeros
          $nama = trim($worksheet->getCellByColumnAndRow(1, $rowNum)->getFormattedValue());
          $alamat = trim($worksheet->getCellByColumnAndRow(2, $rowNum)->getFormattedValue());
          $contact = trim($worksheet->getCellByColumnAndRow(3, $rowNum)->getFormattedValue());
          $phone = trim($worksheet->getCellByColumnAndRow(4, $rowNum)->getFormattedValue());
          $email = trim($worksheet->getCellByColumnAndRow(5, $rowNum)->getFormattedValue());
          $bank = trim($worksheet->getCellByColumnAndRow(6, $rowNum)->getFormattedValue());
          $noRek = trim($worksheet->getCellByColumnAndRow(7, $rowNum)->getFormattedValue());
          $npwp = trim($worksheet->getCellByColumnAndRow(8, $rowNum)->getFormattedValue());
          
          if ($nama === '') {
            $importErrors[] = "Baris $rowNum: Nama supplier kosong";
            continue;
          }
          
          // Validasi phone: strip whitespace/dashes for counting, allow 9-17 digits
          if ($phone !== '') {
            $phoneDigits = preg_replace('/[^0-9]/', '', $phone); // Only digits
            if (strlen($phoneDigits) < 9 || strlen($phoneDigits) > 17) {
              $importErrors[] = "Baris $rowNum: Format Nomor HP tidak valid (value: '$phone', digits: " . strlen($phoneDigits) . ")";
              continue;
            }
          }

          // Validasi NPWP setelah variabel ada  
          if ($npwp !== '' && !preg_match("/^[0-9]{2}\.?[0-9]{3}\.?[0-9]{3}\.?[0-9]-?[0-9]{3}\.?[0-9]{3}$/", $npwp)) {
              $importErrors[] = "Baris $rowNum: Format NPWP tidak valid";
              continue;
          }

          // Convert empty values to empty string for NOT NULL columns
          $contact_val = ($contact === '') ? '' : $contact;
          $phone_val = ($phone === '') ? '' : $phone;
          $email_val = ($email === '') ? '' : $email;
          $bank_val = ($bank === '') ? '' : $bank;
          $noRek_val = ($noRek === '') ? '' : $noRek;
          $npwp_val = ($npwp === '') ? '' : $npwp;
          
          $stmt = mysqli_prepare($kon, "INSERT INTO supplier (nama, alamat, contact,  phone, email, Bank, no_rek, NPWP) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
          if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ssssssss', $nama, $alamat, $contact_val, $phone_val, $email_val, $bank_val, $noRek_val, $npwp_val);
            if (mysqli_stmt_execute($stmt)) {
              $importSuccess++;
            } else {
              $importErrors[] = "Baris $rowNum: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
          }
        }
      } else {
        $errors[] = 'Format file tidak didukung. Gunakan .xlsx, .xls, atau .csv';
      }
    } catch (Exception $e) {
      $errors[] = 'Error membaca file: ' . $e->getMessage();
    }
  } else {
    $errors[] = 'File tidak valid atau tidak ada file yang diupload.';
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_supplier'])) {
  $nama = trim($_POST['nama'] ?? '');
  $alamat = trim($_POST['alamat'] ?? '');
  $contact = trim($_POST['contact'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $bank = trim($_POST['bank'] ?? '');
  $noRek = trim($_POST['no_rek'] ?? '');
  $npwp = trim($_POST['npwp'] ?? '');

  if ($nama === '') $errors[] = 'Nama supplier wajib diisi.';

  if (empty($errors)) {
    // Convert empty values to empty string for NOT NULL columns
    $contact_val = ($contact === '') ? '' : $contact;
    $phone_val = ($phone === '') ? '' : $phone;
    $email_val = ($email === '') ? '' : $email;
    $bank_val = ($bank === '') ? '' : $bank;
    $noRek_val = ($noRek === '') ? '' : $noRek;
    $npwp_val = ($npwp === '') ? '' : $npwp;
    
    $stmt = mysqli_prepare($kon, "INSERT INTO supplier (nama, alamat, contact, phone, email, Bank, no_rek, NPWP) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
      mysqli_stmt_bind_param($stmt, 'ssssssss', $nama, $alamat, $contact_val, $phone_val, $email_val, $bank_val, $noRek_val, $npwp_val);
      if (mysqli_stmt_execute($stmt)) {
        $success = true;
      } else {
        $errors[] = 'Gagal menyimpan supplier: ' . mysqli_stmt_error($stmt);
      }
      mysqli_stmt_close($stmt);
    } else {
      $errors[] = 'Prepare gagal: ' . mysqli_error($kon);
    }
  }
}
?>

<?php if (!defined('IN_MENU_ADMIN')): ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Tambah Supplier</title>
  <link rel="stylesheet" href="../../../../../assets/css/style.css">
</head>
<body>
<?php endif; ?>

<div class="card" style="margin-bottom:16px;">
  <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
    <h2 style="margin:0;">Tambah Supplier</h2>
  </div>
  <form method="POST" style="margin-top:12px;">
    <div class="row-two">
      <div class="col">
        <div class="field-inline">
          <label for="nama">Nama Supplier</label>
          <input id="nama" class="input" type="text" name="nama" required>
        </div>
      </div>
      <div class="col">
        <div class="field-inline">
          <label for="contact">Contact</label>
          <input id="contact" class="input" type="text" name="contact">
        </div>
      </div>
    </div>

    <div class="form-row">
      <div class="field-inline field-multiline">
        <label for="alamat">Alamat</label>
        <textarea id="alamat" class="input" name="alamat" rows="3"></textarea>
      </div>
    </div>

    <div class="row-two">
      <div class="col">
        <div class="field-inline">
          <label for="phone">Phone</label>
          <input id="phone" class="input" type="text" name="phone">
        </div>
      </div>
      <div class="col">
        <div class="field-inline">
          <label for="email">Email</label>
          <input id="email" class="input" type="text" name="email">
        </div>
      </div>
    </div>
    <div class="row-two">
      <div class="col">
        <div class="field-inline">
          <label for="bank">Bank</label>
          <input id="bank" class="input" type="text" name="bank">
        </div>
      </div>
      <div class="col">
        <div class="field-inline">
          <label for="no_rek">No. Rekening</label>
          <input id="no_rek" class="input" type="text" name="no_rek">
        </div>
      </div>
    </div>
    <div class="form-row">
      <div class="field-inline">
        <label for="npwp">NPWP</label>
        <input id="npwp" class="input" type="text" name="npwp">
      </div>
    </div>

    <div class="form-actions">
      <button class="btn btn-add" type="submit" name="save_supplier">Simpan Supplier</button>
      <a class="btn" href="?menu=transaction">Kembali</a>
    </div>
  </form>
  
  <!-- Success Message (Fixed Position, Auto-hide) -->
  <?php if ($importSuccess > 0): ?>
    <div id="success-msg" class="card" style="position:fixed;top:20px;right:20px;z-index:9999;border-left:4px solid #27ae60;background:#fff;box-shadow:0 6px 18px rgba(15,23,42,0.18);width:320px;">
      <div style="color:#155724;padding:10px 12px">Berhasil import <?php echo $importSuccess; ?> supplier.</div>
    </div>
    <script>setTimeout(() => { var el = document.getElementById("success-msg"); if(el) el.style.display = "none"; }, 500);</script>
  <?php endif; ?>

  <?php if ($success): ?>
    <div id="success-msg" class="card" style="position:fixed;top:20px;right:20px;z-index:9999;border-left:4px solid #27ae60;background:#fff;box-shadow:0 6px 18px rgba(15,23,42,0.18);width:320px;">
      <div style="color:#155724;padding:10px 12px">Supplier berhasil ditambahkan.</div>
    </div>
    <script>setTimeout(() => { var el = document.getElementById("success-msg"); if(el) el.style.display = "none"; }, 500);</script>
  <?php endif; ?>

  <!-- Warning Messages (Fixed Position) -->
  <?php if (!empty($importErrors)): ?>
    <div class="card" style="position:fixed;top:20px;right:20px;z-index:9999;border-left:4px solid #f39c12;background:#fff;box-shadow:0 6px 18px rgba(15,23,42,0.18);width:320px;max-height:400px;overflow-y:auto;">
      <div style="color:#856404;padding:10px 12px;font-weight:bold;">Beberapa baris gagal diimport:</div>
      <?php foreach ($importErrors as $err): ?>
        <div style="color:#856404;padding:6px 12px;font-size:13px;"><?php echo htmlspecialchars($err); ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- Error Messages (Fixed Position) -->
  <?php if (!empty($errors)): ?>
    <div class="card" style="position:fixed;top:20px;right:20px;z-index:9999;border-left:4px solid #e74c3c;background:#fff;box-shadow:0 6px 18px rgba(15,23,42,0.18);width:320px;">
      <?php foreach ($errors as $err): ?>
        <div style="color:#8a1f1f;padding:10px 12px"><?php echo htmlspecialchars($err); ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="card" style="margin-top:12px;border-left:4px solid #3498db;">
    <h3 style="margin:0 0 12px 0;color:#2c3e50;">Import dari Excel/CSV</h3>
    <p style="margin:0 0 12px 0;color:#7f8c8d;font-size:14px;">Format kolom: Nama | Alamat | Contact | Phone | Email | Bank | No Rek| NPWP</p>
    <form method="POST" enctype="multipart/form-data" style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;">
      <div style="flex:1;min-width:200px;">
        <label for="file" style="display:block;margin-bottom:4px;font-weight:500;">Pilih File (.xlsx, .xls, .csv)</label>
        <input id="file" type="file" name="file" accept=".xlsx,.xls,.csv" class="input" required>
      </div>
      <button class="btn btn-import" type="submit" name="import_supplier">Import</button>
    </form>
  </div>

</div>

<?php if (!defined('IN_MENU_ADMIN')): ?>
</body>
</html>
<?php endif; ?>
