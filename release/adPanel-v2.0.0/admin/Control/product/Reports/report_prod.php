<?php
require_once __DIR__ . '/../../../System/kon.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
// require login
if (empty($_SESSION['logged_in'])) {
    header('Location: ../admin/login.php'); exit;
}
// Reports are now accessible to all authenticated users
$brands = [
    'Komatsu','Bomag','Caterpillar','Scania','Volvo','Nissan','Hyva','Other',
];
$categories = [
    'Engine','Electrical','Brake System','Cylinder','Axle & Stering','Cabin','Filter','Attachment','Final Drive','Hydraulic System','General','Alternatif',
];
$summary = [];
$sql = "SELECT brand, category, sum(qty) as total_no FROM Product GROUP BY brand, category";
$result = mysqli_query($kon, $sql);
if ($result) {
    while($row = mysqli_fetch_assoc($result)) {
        $summary[$row['brand']][$row['category']] = $row['total_no'];
    }
}
?>

<?php if (!defined('IN_MENU_ADMIN')) { ?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Stock</title>
    <link rel="stylesheet" href="../../../assets/css/style.css">

</head>
<body>
<?php } ?>
<?php if (!defined('IN_MENU_ADMIN')) { ?>
        <div class="dashboard-container">
<?php } ?>
        <div class="dashboard-title" style="margin-bottom:32px; color:#2563eb;">Report Stock</div>
		<!-- Report Card //-->
        <div class="dashboard-row" style="margin-bottom:15px;">
            <div class="dashboard-card" style="background:#e0e7ef; box-shadow:0 2px 8px #2563eb22;">
                <h3 style="color:#2563eb;">Total Product</h3>
                <div class="big" style="font-size:2.8em; color:#2563eb; margin-bottom:8px;">
                    <?php $total = mysqli_query($kon, "SELECT COUNT(no) as total FROM Product"); $t = mysqli_fetch_assoc($total); echo $t['total'] ?? 0; ?>
                </div>
                <div class="sub">All Brands & Categories</div>
            </div>
            <div class="dashboard-card" style="background:#f3f6fa; box-shadow:0 2px 8px #2563eb22;">
                <h3 >Brand Terbanyak</h3>
                <div class="big">
                    <?php $maxb = mysqli_query($kon, "SELECT brand, COUNT(no) as total FROM Product GROUP BY brand ORDER BY total DESC LIMIT 1"); $mb = mysqli_fetch_assoc($maxb); echo $mb['brand'] ?? '-'; ?>
                </div>
                <div class="sub" ><?php echo $mb['total'] ?? 0; ?></div>
            </div>
            <div class="dashboard-card" style="background:#f3f6fa; box-shadow:0 2px 8px #2563eb22;">
                <h3 >Kategori Terbanyak</h3>
                <div class="big" style="font-size:2.2em; color:#2563eb; margin-bottom:8px;">
                    <?php $maxc = mysqli_query($kon, "SELECT category, COUNT(no) as total FROM Product GROUP BY category ORDER BY total DESC LIMIT 1"); $mc = mysqli_fetch_assoc($maxc); echo $mc['category'] ?? '-'; ?>
                </div>
                <div class="sub"> <?php echo $mc['total'] ?? 0; ?></div>
            </div>
			<div class="dashboard-card" style="background:#f3f6fa; box-shadow:0 2px 8px #2563eb22;">
                <h3 style="color:#2563eb;">Stock Brand</h3>
                <div class="big" style="font-size:2.2em; color:#2563eb; margin-bottom:8px;">
                    <?php $maxc = mysqli_query($kon, "SELECT brand, sum(qty) as total FROM Product GROUP BY brand ORDER BY total DESC LIMIT 1"); $mc = mysqli_fetch_assoc($maxc); echo $mc['brand'] ?? '-'; ?>
                </div>
                <div class="sub">Quantity: <?php echo $mc['total'] ?? 0; ?></div>
            </div>
			<div class="dashboard-card" style="background:#f3f6fa; box-shadow:0 2px 8px #2563eb22;">
                <h3 style="color:#2563eb;">Stock category</h3>
                <div class="big" style="font-size:2.2em; color:#2563eb; margin-bottom:8px;">
                    <?php $maxc = mysqli_query($kon, "SELECT category, sum(qty) as total FROM Product GROUP BY category ORDER BY total DESC LIMIT 1"); $mc = mysqli_fetch_assoc($maxc); echo $mc['category'] ?? '-'; ?>
                </div>
                <div class="sub">Quantity: <?php echo $mc['total'] ?? 0; ?></div>
            </div>


        </div>


		<!-- Tabel Report //-->
        <div style="margin-bottom:18px; font-size:1.3em; font-weight:600; color:#2563eb; text-align:center;">Brand & Category</div>
        <div class="dashboard-row">
            <div class="dashboard-card" style="flex:2; background:#fff; box-shadow:0 2px 8px #2563eb22;">
                <table class="table-report" style="margin-top:0;">
                    <tr><th>Brand</th><?php foreach ($categories as $cat) echo '<th>' . htmlspecialchars($cat) . '</th>'; ?></tr>
                    <?php foreach ($brands as $brand): ?>
                    <tr>
                        <td class="brand-label" style="color:#2563eb; font-weight:600; text-align:left;"><?php echo htmlspecialchars($brand); ?></td>
                        <?php foreach ($categories as $cat): ?>
                            <td><?php echo isset($summary[$brand][$cat]) ? $summary[$brand][$cat] : 0; ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
      </div>
<?php if (!defined('IN_MENU_ADMIN')) { ?>
    </main>
</div>
</body>
</html>
<?php } ?>
