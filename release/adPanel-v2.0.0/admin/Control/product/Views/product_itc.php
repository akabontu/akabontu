<?php
include(__DIR__ . '/../../../System/kon.php');
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function tampilkan_itc_product() {
	global $kon;
	echo '<div class="top" style="display: flex; justify-content: space-between; align-items: center;">';
        echo '<h2>Daftar Product Interchange</h2>';
        echo '<div style="display: flex; gap: 10px; align-items: center;">';
        // Search Form
        $searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';
        echo '<form method="GET" action="" style="display: flex; gap: 10px; align-items: center;">';
        echo '<input type="hidden" name="menu" value="itc_product">';
        echo '<input type="text" name="search" placeholder="Cari Part Number, Description, atau Brand..." value="' . htmlspecialchars($searchKeyword) . '" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; width: 300px;">';
        echo '<button type="submit" class="btn btn-search" style="padding: 8px 16px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Cari</button>';
        if (!empty($searchKeyword)) {
            echo '<a href="?menu=itc_product" class="btn btn-reset" style="padding: 8px 16px; background-color: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none;">Reset</a>';
        }
        echo '</form>';
        
        echo '</div>';
        echo '</div>';
        echo '<div class="fixed-header-table-wrapper">';
        echo '<table class="table">';
		echo '<thead>';
		echo '<tr>'
		//. '<th style="width:40px;text-align:center;">#</th>'
		. '<th style="width:60px;text-align:center;">No</th>'
		. '<th style="width:160px;text-align:left;">Part Number</th>'
		. '<th style="width:180px;text-align:left;">Description</th>'
		. '<th style="width:120px;text-align:center;">Brand</th>'
		. '<th style="width:140px;text-align:center;">PN B1</th>'
		. '<th style="width:120px;text-align:center;">Brand 1</th>'
		. '<th style="width:140px;text-align:center;">PN B2</th>'
		. '<th style="width:120px;text-align:center;">Brand 2</th>'
		. '<th style="width:140px;text-align:center;">PN B3</th>'
		. '<th style="width:120px;text-align:center;">Brand 3</th>'
		. '<th style="width:200px;text-align:center;">Action</th>'
		. '</tr>';
	echo '</thead>';
	echo '<tbody>';

	// Build search query
	$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';
	if (!empty($searchKeyword)) {
		$searchTerm = '%' . mysqli_real_escape_string($kon, $searchKeyword) . '%';
		$query = mysqli_query($kon, "SELECT no, part_number, description, brand, pn_b1, brand_1, pn_b2, brand_2, pn_b3, brand_3 FROM itc_product WHERE part_number LIKE '$searchTerm' OR description LIKE '$searchTerm' OR brand LIKE '$searchTerm' ORDER BY `no` DESC");
	} else {
		$query = mysqli_query($kon, "SELECT no, part_number, description, brand, pn_b1, brand_1, pn_b2, brand_2, pn_b3, brand_3 FROM itc_product ORDER BY `no` DESC");
	}
	$row = 1;
	if ($query && mysqli_num_rows($query) > 0) {
		while ($data = mysqli_fetch_assoc($query)) {
			echo '<tr>';
			echo '<td style="width:40px;text-align:center;">' . $row++ . '</td>';
			//echo '<td style="width:60px;text-align:center;">' . htmlspecialchars($data['no']) . '</td>';
			echo '<td style="width:160px;text-align:left;">' . htmlspecialchars($data['part_number']) . '</td>';
			echo '<td style="width:180px;text-align:left;">' . htmlspecialchars($data['description']) . '</td>';
			echo '<td style="width:120px;text-align:center;">' . htmlspecialchars($data['brand']) . '</td>';
			echo '<td style="width:140px;text-align:center;">' . htmlspecialchars($data['pn_b1']) . '</td>';
			echo '<td style="width:120px;text-align:center;">' . htmlspecialchars($data['brand_1']) . '</td>';
			echo '<td style="width:140px;text-align:center;">' . htmlspecialchars($data['pn_b2']) . '</td>';
			echo '<td style="width:120px;text-align:center;">' . htmlspecialchars($data['brand_2']) . '</td>';
			echo '<td style="width:140px;text-align:center;">' . htmlspecialchars($data['pn_b3']) . '</td>';
			echo '<td style="width:120px;text-align:center;">' . htmlspecialchars($data['brand_3']) . '</td>';
			echo '<td style="width:200px;text-align:center;">';
			echo '<div style="display:flex; gap:4px; align-items:center; justify-content: center;">';
			$editUrl = '?menu=edit_prod_itc&no=' . urlencode($data['no']);
			echo '<a class="btn btn-edit" href="' . htmlspecialchars($editUrl, ENT_QUOTES) . '">Edit</a>';
			$delUrl = '../../Control/product/Actions/del_itc_product.php?no=' . urlencode($data['no']);
			echo '<a class="btn btn-delete" data-no-ajax href="' . htmlspecialchars($delUrl, ENT_QUOTES) . '" onclick="return confirm(\'Yakin hapus relasi ini?\')">Hapus</a>';
			echo '</div>';
			echo '</td>';
			echo '</tr>';
		}
	} else {
		echo '<tr><td colspan="12" style="text-align:center;color:#94a3b8;">Belum ada data interchange.</td></tr>';
	}

	echo '</tbody>';
	echo '</table>';
	echo '</div>';
}

tampilkan_itc_product();
?>