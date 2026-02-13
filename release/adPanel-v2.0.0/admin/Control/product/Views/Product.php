<?php
include(__DIR__ . '/../../../System/kon.php');
// Keep session available but do not redirect here; shell manages auth and header/footer
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!function_exists('include_fragment')) {
function include_fragment($path){
  if (!file_exists($path)) return '<div style="padding:1rem;color:#900">File not found: '.htmlspecialchars($path).'</div>';
  ob_start();
  include $path;
  $c = ob_get_clean();
  if (preg_match('#<body[^>]*>(.*)</body>#is', $c, $m)) return $m[1];
  if (preg_match('#<main[^>]*>(.*)</main>#is', $c, $m)) return $m[1];
  return $c;
}
}

function tampilkan_product() {
        global $kon;
        echo '<div class="top" style="display: flex; justify-content: space-between; align-items: center;">';
        echo '<h2>Daftar Product</h2>';
        echo '<div style="display: flex; gap: 10px; align-items: center;">';
        // Search Form
        $searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';
        echo '<form method="GET" action="" style="display: flex; gap: 10px; align-items: center;">';
        echo '<input type="hidden" name="menu" value="product">';
        echo '<input type="text" name="search" placeholder="Cari Part Number, Description, Brand, atau Category..." value="' . htmlspecialchars($searchKeyword) . '" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; width: 300px;">';
        echo '<button type="submit" class="btn btn-search" style="padding: 8px 16px; background-color: #046314; color: white; border: none; border-radius: 4px; cursor: pointer;">Cari</button>';
        if (!empty($searchKeyword)) {
            echo '<a href="?menu=product" class="btn btn-reset" style="padding: 8px 16px; background-color: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none;">Reset</a>';
        }
        echo '</form>';
        echo '<a class="btn btn-add" href="?menu=add">Tambah Product</a>';
        echo '</div>';
        echo '</div>';
        echo '<div class="fixed-header-table-wrapper">';
        echo '<table class="table">';
        echo '<thead style="background-color: #000; color: #fff;">';
        echo '<tr>'
      //. '<th style="width:50px;text-align:center;">#</th>'
      . '<th style="width:50px;text-align:center;">No</th>'
      . '<th style="width:100px;text-align:center;">Part Number</th>'
      . '<th style="width:100px;text-align:center;">Interchange</th>'
      . '<th style="width:100px;text-align:left;">Description</th>'
      . '<th style="width:80px;text-align:center;">Brand</th>'
      . '<th style="width:80px;text-align:center;">Category</th>'
      . '<th style="width:50px;text-align:center;">Qty</th>'
      . '<th style="width:50px;text-align:center;">Berat</th>'
      . '<th style="width:80px;text-align:center;">Image</th>'
      . '<th style="width:100px;text-align:center;">Action</th>'
      . '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        // Build search query
        $searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';
        if (!empty($searchKeyword)) {
            $searchTerm = '%' . mysqli_real_escape_string($kon, $searchKeyword) . '%';
            $query = mysqli_query($kon, "SELECT * FROM Product WHERE part_number LIKE '$searchTerm' OR description LIKE '$searchTerm' OR brand LIKE '$searchTerm' OR category LIKE '$searchTerm' OR itc LIKE '$searchTerm' ORDER BY `No` DESC");
        } else {
            $query = mysqli_query($kon, "SELECT * FROM Product ORDER BY `No` DESC");
        }
        // Base URL for assets when the app is installed under a subfolder (e.g., /adPanel)
        $baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'], 4), '/');
        $row = 1;
        while($data = mysqli_fetch_assoc($query)){
          // Ambil gambar utama dari product_images (prioritas is_primary, lalu id terkecil)
          $imgPath = null;
          $imgMime = null;
          $stmtImg = mysqli_prepare($kon, "SELECT path, mime FROM product_images WHERE product_no = ? ORDER BY is_primary DESC, no ASC LIMIT 1");
          if ($stmtImg) {
            mysqli_stmt_bind_param($stmtImg, 'i', $data['No']);
            if (mysqli_stmt_execute($stmtImg)) {
              mysqli_stmt_bind_result($stmtImg, $imgPath, $imgMime);
              mysqli_stmt_fetch($stmtImg);
            }
            mysqli_stmt_close($stmtImg);
          }

          echo '<tr>';
          echo '<td style="width:50px;text-align:center;">' . $row++ . '</td>';
          //echo '<td style="width:50px;text-align:center;">' . htmlspecialchars($data['No']) . '</td>';
          echo '<td style="width:100px;text-align:center;">' . htmlspecialchars($data['part_number']) . '</td>';
          echo '<td style="width:100px;text-align:center;">' . htmlspecialchars($data['itc']) . '</td>';
          echo '<td style="width:100px;text-align:left;">' . htmlspecialchars($data['description']) . '</td>';
          echo '<td style="width:80px;text-align:center;">' . htmlspecialchars($data['brand']) . '</td>';
          echo '<td style="width:80px;text-align:center;">' . htmlspecialchars($data['category']) . '</td>';
          echo '<td style="width:50px;text-align:center;">' . htmlspecialchars($data['Qty']) . '</td>';
          echo '<td style="width:50px;text-align:center;">' . htmlspecialchars($data['berat'] ?? '') . '</td>';
          echo '<td style="width:80px;text-align:center;">',
          // Resolve image path candidates to handle different stored formats
          $imgSrc = null;
          if (!empty($imgPath)) {
            $relative = ltrim($imgPath, '/');
            $root = dirname(__DIR__, 4); // htdocs/adPanel
            $candidates = [$relative];
            if (strpos($relative, 'admin/') !== 0) {
              $candidates[] = 'admin/' . $relative;
            }
            foreach ($candidates as $cand) {
              $fs = $root . '/' . $cand;
              if (file_exists($fs)) {
                $imgSrc = ($baseUrl !== '' ? $baseUrl : '') . '/' . ltrim($cand, '/');
                break;
              }
            }
            // If not found on disk, still try the provided path
            if ($imgSrc === null) {
              $imgSrc = ($baseUrl !== '' ? $baseUrl : '') . '/' . $relative;
            }
          }

          if (!empty($imgSrc)) {
            echo '<img src="' . htmlspecialchars($imgSrc) . '" alt="img" style="max-height:50px;border-radius:2px;">';
          } elseif (!empty($data['image'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = $finfo ? finfo_buffer($finfo, $data['image']) : 'image/jpeg';
            if ($finfo) finfo_close($finfo);
            echo '<img src="data:' . htmlspecialchars($mime) . ';base64,' . base64_encode($data['image']) . '" alt="img" style="max-height:50px;border-radius:2px;">';
          } else {
            echo '-';
          }
          echo '</td>';
          echo '<td style="width:100px;text-align:center;">';
          echo '<div style="display:flex; gap: 2px; align-items: center; justify-content: center;">';
          $viewUrl = '?menu=view&no=' . urlencode($data['No']);
          $editUrl = '?menu=edit&no=' . urlencode($data['No']);
          echo '<a class="btn btn-view" href="' . htmlspecialchars($viewUrl, ENT_QUOTES) . '" title="Lihat" aria-label="Lihat">';
          echo '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
          echo '</a>';
          echo '<a class="btn btn-edit" href="' . htmlspecialchars($editUrl, ENT_QUOTES) . '">Edit</a>';
          // show delete only to Owner (B) and Dev (C)
          $showDelete = !empty($_SESSION['role']) && in_array($_SESSION['role'], ['B','C']);
          if ($showDelete) {
            $delUrl = '../../Control/product/Actions/del_prod.php?no=' . urlencode($data['No']);
            echo '<a class="btn btn-delete" data-no-ajax href="' . htmlspecialchars($delUrl, ENT_QUOTES) . '" onclick="return confirm(\'Yakin hapus?\')">Hapus</a>';
          }
          echo '</div>';
          echo '</td>';
          echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
      }
      // Render only the fragment content (no header/footer). The shell (`menu_admin.php`) will embed this.
      tampilkan_product();
      ?>
