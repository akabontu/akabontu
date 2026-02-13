<?php
// Banner Product Management
require_once __DIR__ . '/../kon.php';

// Handle banner upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['banner_image'])) {
    $brand = mysqli_real_escape_string($kon, $_POST['brand']);
    $image = $_FILES['banner_image'];

    if ($image['error'] === UPLOAD_ERR_OK) {
        // Check file size (3MB limit)
        $maxSize = 3 * 1024 * 1024; // 3MB
        if ($image['size'] > $maxSize) {
            $uploadMessage = 'Ukuran file terlalu besar (maks 3MB).';
        } else {
            $imageData = file_get_contents($image['tmp_name']);
            $query = "INSERT INTO banner_up (brand, image, active) VALUES ('$brand', ?, 1)";
            $stmt = mysqli_prepare($kon, $query);
            mysqli_stmt_bind_param($stmt, 's', $imageData);
            if (mysqli_stmt_execute($stmt)) {
                $uploadMessage = 'Banner uploaded successfully.';
            } else {
                $uploadMessage = 'Error uploading banner.';
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $uploadMessage = 'Upload error.';
    }
}

// Handle toggle active status
if (isset($_GET['toggle']) && isset($_GET['brand'])) {
    $brand = mysqli_real_escape_string($kon, $_GET['brand']);
    $query = "UPDATE banner_up SET active = 1 - active WHERE brand = '$brand'";
    mysqli_query($kon, $query);
    // If this script is accessed directly (not via the menu shell), redirect
    // to avoid leaving the user on a non-dedicated endpoint. When included
    // inside the dashboard shell (`IN_MENU_ADMIN` defined) we must NOT send
    // headers because output has already started.
    if (!defined('IN_MENU_ADMIN') && !isset($_GET['ajax'])) {
        header("Location: ?menu=banner_product");
        exit;
    }
    // Otherwise (when included inside menu_admin or AJAX), continue and
    // render the fragment normally so the shell will update without full reload.
}

// Add active column if not exists (one-time operation)
$query = "ALTER TABLE banner_up ADD COLUMN IF NOT EXISTS active TINYINT(1) DEFAULT 1";
mysqli_query($kon, $query);

// Fetch distinct brands for dropdown
$brands = [
    'Komatsu',
    'Bomag',
    'Caterpillar',
    'Scania',
    'Volvo',
    'Hyva',
    'Other',
];
?>
<div class="container-fluid">
    <div class="top">
        <h2>Banner Product</h2>
        <button id="checkConnection" class="btn btn-info">Check Website Connection</button>
    </div>
    <div class="form-row">
        <div class="col-md-6">
            <div class="card p-3">
                <?php
                require_once __DIR__ . '/../kon.php';
                function tampilkan_banner() {
                global $kon;
                echo '<div class="top">';
                echo '<h2>Daftar Banner</h2>';
                echo '</div>';
                echo '<table class="table">';
                echo '<tr><th>No</th><th>Brand</th><th>Image</th><th>Status</th><th>Actions</th></tr>';
                echo '<tbody>';
                        $row= 1;
                        $query = mysqli_query($kon, "SELECT * FROM banner_up ORDER BY brand DESC");
                        while ($data = mysqli_fetch_assoc($query)) {
                            echo '<tr>';
                            echo '<td>' . $row++ . '</td>';
                            echo '<td>' . htmlspecialchars($data['brand']) . '</td>';
                            echo '<td>';
                            if (!empty($data['image'])) {
                                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                $mime = $finfo ? finfo_buffer($finfo, $data['image']) : 'image/jpeg';
                                if ($finfo) finfo_close($finfo);
                                echo '<img src="data:' . htmlspecialchars($mime) . ';base64,' . base64_encode($data['image']) . '" alt="banner" style="max-height:50px; max-width:100px;">';
                            } else {
                                echo '-';
                            }
                            echo '</td>';
                            echo '<td>';
                            $status = isset($data['active']) ? $data['active'] : 1;
                            $statusText = $status ? 'On' : 'Off';
                            $btnClass = $status ? 'btn-success' : 'btn-secondary';
                            echo '<a href="?menu=banner_product&toggle=1&brand=' . urlencode($data['brand']) . '" class="btn ' . $btnClass . ' btn-sm">' . $statusText . '</a>';
                            echo '</td>';
                            echo '<td>';
                            echo '<a href="?menu=edit_banner&brand=' . urlencode($data['brand']) . '" class="btn btn-edit">Edit</a> ';
                            echo '<a href="delete_banner.php?brand=' . urlencode($data['brand']) . '" class="btn btn-delete btn-sm" onclick="return confirm(\'Delete?\')">Delete</a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table>';
                        }
                        tampilkan_banner();
                        ?>
            </div>
        </div>
        <!-- fungsikan di awal saja //-->
        <!--div class="col-md-6">
            <div class="card p-3 mb-3">
                <h4>Upload New Banner</h4>
                <?php if (isset($uploadMessage)): ?>
                    <div class="alert alert-info"><?php echo htmlspecialchars($uploadMessage); ?></div>
                <?php endif; ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-row">
                        <label for="brand" class="form-label">Brand:</label>
                        <select name="brand" id="brand" class="form-control" required>
                            <option value="">Select Brand</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo htmlspecialchars($brand); ?>"><?php echo htmlspecialchars($brand); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="image">Image Product (opsional, JPG/PNG/GIF max 3MB)</label>
                        <input type="file" name="banner_image" id="banner_image" class="form-control" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn btn-add">Upload Banner</button>
                </form>
            </div>
        </div//-->
    </div>
</div>
<script>
document.getElementById('checkConnection').addEventListener('click', function() {
    fetch(window.location.origin)
        .then(response => {
            if (response.ok) {
                alert('Website is connected!');
            } else {
                alert('Website connection failed!');
            }
        })
        .catch(error => {
            alert('Website connection failed: ' + error.message);
        });
});
</script></content>
