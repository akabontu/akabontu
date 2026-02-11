<?php
require_once __DIR__ . '/../../System/kon.php';

$no = isset($_GET['no']) ? intval($_GET['no']) : 0;
$part_number = isset($_GET['part_number']) ? trim($_GET['part_number']) : '';
$itc = isset($_GET['itc']) ? trim($_GET['itc']) : '';

if ($no > 0) {
        $stmt = mysqli_prepare($kon, "SELECT * FROM itc_pn WHERE no = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $no);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
} elseif ($part_number && $itc) {
        $stmt = mysqli_prepare($kon, "SELECT * FROM itc_pn WHERE part_number = ? AND itc = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'ss', $part_number, $itc);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
} else {
        // No identifying parameters provided — return to the listing instead of showing an error.
        $redirect = defined('IN_MENU_ADMIN') ? 'menu_admin.php?menu=list_itc' : 'list_pn_itc.php';
        if (!headers_sent()) {
            header('Location: ' . $redirect);
            exit;
        } else {
            $safe = htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8');
            echo "<script>window.location.replace('{$safe}');</script>";
            echo '<noscript><meta http-equiv="refresh" content="0;url=' . $safe . '"></noscript>';
            exit;
        }
}

if (!$data) {
        echo "<p>Data tidak ditemukan.</p>";
        exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Hanya update field yang masih kosong
        $itc_1 = $data['itc_1'] ? $data['itc_1'] : trim($_POST['itc_1'] ?? '');
        $itc_2 = $data['itc_2'] ? $data['itc_2'] : trim($_POST['itc_2'] ?? '');
        $itc_3 = $data['itc_3'] ? $data['itc_3'] : trim($_POST['itc_3'] ?? '');

        $stmt = mysqli_prepare($kon, "UPDATE itc_pn SET itc_1=?, itc_2=?, itc_3=? WHERE no=?");
        mysqli_stmt_bind_param($stmt, 'sssi', $itc_1, $itc_2, $itc_3, $data['no']);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($ok) {
            $redirect = defined('IN_MENU_ADMIN') ? '?menu=list_itc&no=' : 'list_pn_itc.php';
            if (!headers_sent()) {
                header('Location: ' . $redirect);
                exit;
            } else {
                // If headers already sent (e.g. included into dashboard), use JS fallback
                $safe = htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8');
                echo "<script>window.location.replace('{$safe}');</script>";
                echo '<noscript><meta http-equiv="refresh" content="0;url=' . $safe . '"></noscript>';
                exit;
            }
        } else {
                $errors[] = 'Gagal update data: ' . mysqli_error($kon);
        }
}

// Render form. If included in dashboard, don't output full HTML wrapper.
if (!defined('IN_MENU_ADMIN')) {
        ?>
        <!doctype html>
        <html>
        <head>
                <meta charset="utf-8">
                <title>Update Interchange</title>
                <meta name="viewport" content="width=device-width,initial-scale=1">
                <link rel="stylesheet" href="../../../../assets/css/style.css">
        </head>
        <body>
        <?php
}

// Content
?>
<div class="card" style="max-width:720px;margin:18px auto;padding:18px;">
    <h2>Update Interchange — <?php echo htmlspecialchars($data['part_number']); ?></h2>
    <?php if (!empty($errors)): ?>
        <div style="color:#8a1f1f;margin-bottom:12px;">
            <?php foreach ($errors as $err) echo '<div>' . htmlspecialchars($err) . '</div>'; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="no" value="<?php echo htmlspecialchars($data['no']); ?>">
        <div class="form-row">
            <label>Existing ITC</label>
            <div><?php echo htmlspecialchars($data['itc']); ?></div>
        </div>

        <div class="form-row">
            <label for="itc_1">Interchange 1</label>
            <?php if (!empty($data['itc_1'])): ?>
                <input id="itc_1" class="input" type="text" value="<?php echo htmlspecialchars($data['itc_1']); ?>" disabled>
            <?php else: ?>
                <input id="itc_1" class="input" type="text" name="itc_1" value="">
            <?php endif; ?>
        </div>

        <div class="form-row">
            <label for="itc_2">Interchange 2</label>
            <?php if (!empty($data['itc_2'])): ?>
                <input id="itc_2" class="input" type="text" value="<?php echo htmlspecialchars($data['itc_2']); ?>" disabled>
            <?php else: ?>
                <input id="itc_2" class="input" type="text" name="itc_2" value="">
            <?php endif; ?>
        </div>

        <div class="form-row">
            <label for="itc_3">Interchange 3</label>
            <?php if (!empty($data['itc_3'])): ?>
                <input id="itc_3" class="input" type="text" value="<?php echo htmlspecialchars($data['itc_3']); ?>" disabled>
            <?php else: ?>
                <input id="itc_3" class="input" type="text" name="itc_3" value="">
            <?php endif; ?>
        </div>

        <div class="form-actions" style="margin-top:12px;">
            <button class="btn btn-edit" type="submit">Simpan</button>
            <?php if (defined('IN_MENU_ADMIN')): ?>
                <a class="btn" href="?menu=edit_itc&no=<?php echo urlencode($data['part_number']); ?>">Batal</a>
            <?php else: ?>
                <a class="btn" href="edit_pn_itc.php">Batal</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php
if (!defined('IN_MENU_ADMIN')) {
        echo "</body></html>";
}

?>