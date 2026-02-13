<?php
require_once __DIR__ . '/../../System/kon.php';

// Simple listing of interchange part numbers (itc_pn table)
// Renders inside the dashboard when IN_MENU_ADMIN is defined.

// Build back URL: prefer returning to product edit when `no` is provided,
// otherwise fallback to product list. Keep part_number if provided.
$backUrl = '?menu=product';
if (!empty($_GET['no'])) {
    $backUrl .= '&no=' . urlencode($_GET['no']);
} elseif (!empty($_GET['part_number'])) {
    $backUrl .= '&part_number=' . urlencode($_GET['part_number']);
}

// Handle search query (GET param `q`) — search part_number, description, or itc
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = mysqli_prepare($kon, "SELECT no, part_number, description, itc, itc_1, itc_2, itc_3 FROM itc_pn WHERE part_number LIKE ? OR description LIKE ? OR itc LIKE ? ORDER BY no DESC");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
        mysqli_stmt_execute($stmt);
        $query = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
    } else {
        // fallback
        $query = mysqli_query($kon, "SELECT no, part_number, description, itc, itc_1, itc_2, itc_3 FROM itc_pn ORDER BY no DESC");
    }
} else {
    $query = mysqli_query($kon, "SELECT no, part_number, description, itc, itc_1, itc_2, itc_3 FROM itc_pn ORDER BY no DESC");
}

?>
<?php if (!defined('IN_MENU_ADMIN')): ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interchange Part Number</title>
    <link rel="stylesheet" href="../../../assets/css/style.css">
    
</head>
<body>

<div class="container">
<?php endif; ?>
    <div class="top" style="margin-bottom:16px; color:#2563eb;">
        <h2>Data Interchange Part Number</h2>
        <form method="get" action="../../System/action/menu_admin.php" style="display:inline-flex; gap:6px; margin-left:auto;">
            <input type="hidden" name="menu" value="list_itc">
            <input type="text" name="q" value="<?php echo htmlspecialchars($q ?? ''); ?>" placeholder="Cari part number, description, atau interchange" class="input" style="width:320px;">
            <button class="btn" type="submit">Cari</button>
            <a class="btn" href="?menu=list_itc">Reset</a>
            <a class="btn" href="<?php echo htmlspecialchars($backUrl); ?>">Kembali</a>
        </form>
    </div>
    <div class="fixed-header-table-wrapper">
		<table class="table" style="border-collapse: collapse;">
                    <thead>
                        <tr style="height: 40px;">
                            <!--th style="width: 40px; text-align:center; padding: 8px 5px;">#</th-->
                            <th style="width: 60px; text-align:center; padding: 8px 5px;">No</th>
                            <th style="width: 120px; text-align:center; padding: 8px 5px;">Part Number</th>
                            <th style="width: 240px; text-align:left; padding: 8px 5px;">Description</th>
                            <th style="width: 140px; text-align:center; padding: 8px 5px;">Interchange</th>
                            <th style="width: 140px; text-align:center; padding: 8px 5px;">Interchange 1</th>
                            <th style="width: 140px; text-align:center; padding: 8px 5px;">Interchange 2</th>
                            <th style="width: 140px; text-align:center; padding: 8px 5px;">Interchange 3</th>
                            <th style="width: 170px; text-align:center; padding: 8px 5px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $rownum = 1;
                    if ($query && mysqli_num_rows($query) > 0) {
                        while ($r = mysqli_fetch_assoc($query)) {
                            echo '<tr style="height: 40px;">';
                            echo '<td style="width: 40px; text-align:center; padding: 8px 5px; border-bottom: 1px solid #f1f5f9;">' . $rownum++ . '</td>';
                            //echo '<td style="width: 60px; text-align:center; padding: 8px 5px; border-bottom: 1px solid #f1f5f9;">' . htmlspecialchars($r['no']) . '</td>';
                            echo '<td style="width: 170px; text-align:center; padding: 8px 5px; border-bottom: 1px solid #f1f5f9;">' . htmlspecialchars($r['part_number']) . '</td>';
                            echo '<td style="width: 220px; text-align:left; padding: 8px 5px; border-bottom: 1px solid #f1f5f9;">' . htmlspecialchars($r['description']) . '</td>';
                            echo '<td style="width: 140px; text-align:center; padding: 8px 5px; border-bottom: 1px solid #f1f5f9;">' . htmlspecialchars($r['itc']) . '</td>';
                            echo '<td style="width: 140px; text-align:center; padding: 8px 5px; border-bottom: 1px solid #f1f5f9;">' . htmlspecialchars($r['itc_1']) . '</td>';
                            echo '<td style="width: 140px; text-align:center; padding: 8px 5px; border-bottom: 1px solid #f1f5f9;">' . htmlspecialchars($r['itc_2']) . '</td>';
                            echo '<td style="width: 140px; text-align:center; padding: 8px 5px; border-bottom: 1px solid #f1f5f9;">' . htmlspecialchars($r['itc_3']) . '</td>';
                            echo '<td style="width: 170px; text-align:center; padding: 8px 5px; border-bottom: 1px solid #f1f5f9;">';
                            echo '<div style="display: flex; gap: 4px; align-items: center; justify-content: center;">';
                            echo '<a class="btn btn-edit" href="?menu=add_itc&no=' . urlencode($r['no']) . '">Update</a>';
                            $showDelete = !empty($_SESSION['role']) && in_array($_SESSION['role'], ['B','C']);
                            if ($showDelete) {
                                echo '<a class="btn btn-delete" data-no-ajax href="/adpanel/admin/Control/Mat_no/del_itc_pn.php?no=' . urlencode($r['no']) . '" onclick="return confirm(\'Yakin hapus?\')">Hapus</a>';
                            }
                            echo '</div>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr style="height: 40px;"><td colspan="9" style="text-align:center; padding: 8px 5px;">Data tidak ditemukan.</td></tr>';
                    }
                    ?>
                    </tbody>
                </table>
	</div>

</body>
</html>