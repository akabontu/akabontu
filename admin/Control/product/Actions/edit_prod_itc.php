<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../../../System/kon.php';
if (!defined('IN_MENU_ADMIN')) define('IN_MENU_ADMIN', true);

// Handle update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $no = intval($_POST['no'] ?? 0);
  if ($no > 0) {
    $description = trim($_POST['description'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $pn_b1 = trim($_POST['pn_b1'] ?? '');
    $brand_1 = trim($_POST['brand_1'] ?? '');
    $pn_b2 = trim($_POST['pn_b2'] ?? '');
    $brand_2 = trim($_POST['brand_2'] ?? '');
    $pn_b3 = trim($_POST['pn_b3'] ?? '');
    $brand_3 = trim($_POST['brand_3'] ?? '');

    $stmt = mysqli_prepare($kon, 'UPDATE itc_product SET description = ?, brand = ?, pn_b1 = ?, brand_1 = ?, pn_b2 = ?, brand_2 = ?, pn_b3 = ?, brand_3 = ? WHERE `no` = ?');
    if ($stmt) {
      mysqli_stmt_bind_param($stmt, 'ssssssssi', $description, $brand, $pn_b1, $brand_1, $pn_b2, $brand_2, $pn_b3, $brand_3, $no);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);
    }
  }
  header('Location: /adpanel/admin/System/action/menu_admin.php?menu=itc_product');
  exit;
}

$no = intval($_GET['no'] ?? 0);
if ($no <= 0) {
  header('Location: /adpanel/admin/System/action/menu_admin.php?menu=itc_product');
  exit;
}

$stmt = mysqli_prepare($kon, 'SELECT no, part_number, description, brand, pn_b1, brand_1, pn_b2, brand_2, pn_b3, brand_3 FROM itc_product WHERE `no` = ? LIMIT 1');
if (!$stmt) {
  echo '<div style="padding:1rem;color:#b91c1c;">Gagal memuat data.</div>';
  exit;
}
mysqli_stmt_bind_param($stmt, 'i', $no);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if (!$res || mysqli_num_rows($res) === 0) {
  mysqli_stmt_close($stmt);
  header('Location: /adpanel/admin/System/action/menu_admin.php?menu=itc_product');
  exit;
}
$data = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);
?>
<div class="top">
  <h2>Edit Product Interchange</h2>
  <a class="btn" href="?menu=itc_product">Kembali</a>
</div>

<div class="container-fluid p-1 card-inner" style="overflow:hidden; background:#fff; box-shadow:0 2px 5px #2563eb22; max-width:900px;">
  <form method="post" action="../../Control/product/Actions/edit_prod_itc.php" class="card form" style="overflow:hidden;">
    <input type="hidden" name="no" value="<?php echo htmlspecialchars($data['no']); ?>">

    <div class="row-two">
      <div class="col">
        <div class="field-inline">
          <label for="part_number">Part Number</label>
          <input id="part_number" class="input" type="text" name="part_number" value="<?php echo htmlspecialchars($data['part_number']); ?>" readonly>
        </div>
      </div>
      <div class="col">
        <div class="field-inline">
          <label for="brand">Brand</label>
          <input id="brand" class="input" type="text" name="brand" value="<?php echo htmlspecialchars($data['brand']); ?>" required>
        </div>
      </div>
    </div>

    <div class="form-row">
      <div class="field-inline field-multiline">
        <label for="description">Description</label>
        <textarea id="description" class="input" name="description" rows="3" required><?php echo htmlspecialchars($data['description']); ?></textarea>
      </div>
    </div>

    <div class="row-two">
      <div class="col">
        <div class="field-inline">
          <label for="pn_b1">PN B1</label>
          <input id="pn_b1" class="input" type="text" name="pn_b1" value="<?php echo htmlspecialchars($data['pn_b1']); ?>">
        </div>
      </div>
      <div class="col">
        <div class="field-inline">
          <label for="brand_1">Brand 1</label>
          <input id="brand_1" class="input" type="text" name="brand_1" value="<?php echo htmlspecialchars($data['brand_1']); ?>">
        </div>
      </div>
    </div>

    <div class="row-two">
      <div class="col">
        <div class="field-inline">
          <label for="pn_b2">PN B2</label>
          <input id="pn_b2" class="input" type="text" name="pn_b2" value="<?php echo htmlspecialchars($data['pn_b2']); ?>">
        </div>
      </div>
      <div class="col">
        <div class="field-inline">
          <label for="brand_2">Brand 2</label>
          <input id="brand_2" class="input" type="text" name="brand_2" value="<?php echo htmlspecialchars($data['brand_2']); ?>">
        </div>
      </div>
    </div>

    <div class="row-two">
      <div class="col">
        <div class="field-inline">
          <label for="pn_b3">PN B3</label>
          <input id="pn_b3" class="input" type="text" name="pn_b3" value="<?php echo htmlspecialchars($data['pn_b3']); ?>">
        </div>
      </div>
      <div class="col">
        <div class="field-inline">
          <label for="brand_3">Brand 3</label>
          <input id="brand_3" class="input" type="text" name="brand_3" value="<?php echo htmlspecialchars($data['brand_3']); ?>">
        </div>
      </div>
    </div>

    <div class="form-actions form-actions--inline" style="margin-top:14px; justify-content:flex-start; gap:8px;">
      <button class="btn btn-edit" type="submit">Simpan</button>
      <button class="btn" type="reset">Reset</button>
      <a class="btn" href="?menu=itc_product">Batal</a>
    </div>
  </form>
</div>
