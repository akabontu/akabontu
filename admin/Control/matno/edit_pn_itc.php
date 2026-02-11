<?php if (!defined('IN_MENU_ADMIN')): ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Daftar Interchange</title>
  <link rel="stylesheet" href="../../../assets/css/style.css">
</head>
<body>
<?php // header removed: managed by menu_admin shell ?>
<main class="main-content">
<?php endif; ?>
      <?php
      require_once __DIR__ . '/../../System/kon.php';
      function tampilkan_interchange() {
        global $kon;
        // Build back URL: prefer returning to product edit when `no` is provided,
        // otherwise fallback to product list. Keep part_number if provided.
        $backUrl = '?menu=product';
        if (!empty($_GET['no'])) {
          $backUrl .= '&no=' . urlencode($_GET['no']);
        } elseif (!empty($_GET['part_number'])) {
          $backUrl .= '&part_number=' . urlencode($_GET['part_number']);
        }
        echo '<div class="top">';
        echo '<h2>Daftar Interchange</h2>';
        echo '<a class="btn" href="' . htmlspecialchars($backUrl) . '" style="margin-left:16px;">Kembali</a>';
        echo '</div>';
        echo '<table class="table">';
        echo '<tr><th style="width: 70px;">#</th><th style="width: 70px;">No</th><th>Part Number</th><th>Description</th><th>Interchange</th><th>Interchange 1</th><th>Interchange 2</th><th>Interchange 3</th><th>Actions</th></tr>';
        
        // Build query with optional filters (trim inputs)
        $where = [];
        $params = [];
        $types = '';
        $itc_filter = isset($_GET['itc']) ? trim($_GET['itc']) : '';
        $pn_filter = isset($_GET['part_number']) ? trim($_GET['part_number']) : '';
        if ($itc_filter !== '') {
          $where[] = 'itc = ?';
          $params[] = $itc_filter;
          $types .= 's';
        }
        if ($pn_filter !== '') {
          $where[] = 'part_number = ?';
          $params[] = $pn_filter;
          $types .= 's';
        }
        $sql = "SELECT * FROM itc_pn";
        if ($where) {
          $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY `No` DESC";

        // Use prepared statement only when we have params; fall back to mysqli_query otherwise
        $query = false;
        if (!empty($params)) {
          $stmt = mysqli_prepare($kon, $sql);
          if (!$stmt) {
            echo '<div style="color:#8a1f1f;padding:6px 0">SQL prepare error: ' . htmlspecialchars(mysqli_error($kon)) . '</div>';
            return;
          }
          if ($types && $params) {
            // bind trimmed params
            mysqli_stmt_bind_param($stmt, $types, ...$params);
          }
          if (!mysqli_stmt_execute($stmt)) {
            echo '<div style="color:#8a1f1f;padding:6px 0">SQL execute error: ' . htmlspecialchars(mysqli_stmt_error($stmt)) . '</div>';
            mysqli_stmt_close($stmt);
            return;
          }
          $query = mysqli_stmt_get_result($stmt);
        } else {
          $query = mysqli_query($kon, $sql);
          if (!$query) {
            echo '<div style="color:#8a1f1f;padding:6px 0">SQL error: ' . htmlspecialchars(mysqli_error($kon)) . '</div>';
            return;
          }
        }

        // Debug helper: show final SQL and params when debug=1
        if (!empty($_GET['debug']) && $_GET['debug'] == '1') {
          echo '<pre style="color:#374151">DEBUG SQL: ' . htmlspecialchars($sql) . "\nPARAMS: " . htmlspecialchars(json_encode($params)) . '</pre>';
        }

        $row = 1;
        if ($query && mysqli_num_rows($query) === 0) {
          echo '<tr><td colspan="9" style="text-align:center;color:#6b7280;padding:24px 0">Tidak ada data interchange.</td></tr>';
        }

        while($data = mysqli_fetch_assoc($query)){
          // primary key may be returned as 'No' or 'no' depending on server/driver
          $pk = $data['No'] ?? $data['no'] ?? '';
          echo '<tr>';
          echo '<td style="width: 70px;">' . $row++ . '</td>';
          echo '<td style="width: 70px;">' . htmlspecialchars($pk) . '</td>';
          echo '<td>' . htmlspecialchars($data['part_number'] ?? '') . '</td>';
          echo '<td>' . htmlspecialchars($data['description'] ?? '') . '</td>';
          echo '<td>' . htmlspecialchars($data['itc'] ?? '') . '</td>';
          echo '<td>' . htmlspecialchars($data['itc_1'] ?? '') . '</td>';
          echo '<td>' . htmlspecialchars($data['itc_2'] ?? '') . '</td>';
          echo '<td>' . htmlspecialchars($data['itc_3'] ?? '') . '</td>';
          echo '<td>';
          echo '<div class="container button" style="display: flex; gap: 2px; align-items: stretch;">';
          // Edit opens the add/edit ITC form inside dashboard
          echo '<a class="btn btn-edit" href="?menu=add_itc&no=' . urlencode($pk) . '">UPDATE</a>';
          // show delete only to Owner (B) and Dev (C)
          $showDelete = !empty($_SESSION['role']) && in_array($_SESSION['role'], ['B','C']);
          if ($showDelete) {
            echo '<a class="btn btn-delete" data-no-ajax href="/adpanel/admin/Control/Mat_no/del_itc_pn.php?no=' . urlencode($pk) . '" onclick="return confirm(\'Yakin hapus?\')">Hapus</a>';
          }
          echo '</div>';
          echo '</td>';
          echo '</tr>';
        }
        if (!empty($params) && isset($stmt) && $stmt) {
          mysqli_stmt_close($stmt);
        }
        echo '</table>';
      }

      // Render the list
      tampilkan_interchange();
      ?>
    </main>
<?php // footer removed: managed by menu_admin shell ?>
</body>
</html>