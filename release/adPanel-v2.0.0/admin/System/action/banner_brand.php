<?php
// Banner Brand Management
require_once __DIR__ . '/../kon.php';

// Handle banner upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['banner_image'])) {
    $brand = mysqli_real_escape_string($kon, $_POST['brand']);
    $image = $_FILES['banner_image'];

    if ($image['error'] === UPLOAD_ERR_OK) {
        // Check file size (2MB limit)
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($image['size'] > $maxSize) {
            $uploadMessage = 'Ukuran file terlalu besar (maks 2MB).';
        } else {
            $imageData = file_get_contents($image['tmp_name']);
            $query = "INSERT INTO logo_brand (brand, logo_img) VALUES ('$brand', ?)";
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
    $query = "UPDATE logo_brand SET active = 1 - active WHERE brand = '$brand'";
    mysqli_query($kon, $query);
    if (!defined('IN_MENU_ADMIN') && !isset($_GET['ajax'])) {
        header("Location: ?menu=banner_brand");
        exit;
    }
}

// Add active column if not exists (one-time operation)
$query = "ALTER TABLE logo_brand ADD COLUMN IF NOT EXISTS active TINYINT(1) DEFAULT 1";
mysqli_query($kon, $query);

// Add logo_img column if not exists
$query = "ALTER TABLE logo_brand ADD COLUMN IF NOT EXISTS logo_img LONGBLOB";
mysqli_query($kon, $query);

?>
<div class="container-fluid">
    <div class="top">
        <h2>Banner Logo</h2>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card p-3">
                <h4>Upload New Logo</h4>
                <?php if (isset($uploadMessage)): ?>
                    <div class="alert alert-info"><?php echo htmlspecialchars($uploadMessage); ?></div>
                <?php endif; ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-row">
                        <label for="brand" class="form-label">Brand:</label>
                        <input type="text" name="brand" id="brand" class="form-control" placeholder="Enter brand name" required>
                    </div>
                    <div class="form-row">
                        <label for="image">Image Logo (opsional, JPG/PNG/GIF max 2MB)</label>
                        <input type="file" name="banner_image" id="banner_image" class="form-control" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn btn-add">Upload Logo</button>
                </form>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3 mb-3">
                <h4>Existing Logos</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Brand</th>
                            <th>Image</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = mysqli_query($kon, "SELECT * FROM logo_brand ORDER BY brand DESC");
                        while ($data = mysqli_fetch_assoc($query)) {
                            echo '<tr>';
                            echo '<td>' . $no++ . '</td>';
                            echo '<td>' . htmlspecialchars($data['brand']) . '</td>';
                            echo '<td>';
                            if (!empty($data['logo_img'])) {
                                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                $mime = $finfo ? finfo_buffer($finfo, $data['logo_img']) : 'image/jpeg';
                                if ($finfo) finfo_close($finfo);
                                echo '<img src="data:' . htmlspecialchars($mime) . ';base64,' . base64_encode($data['logo_img']) . '" alt="banner" style="max-height:50px; max-width:100px;">';
                            } else {
                                echo '-';
                            }
                            echo '</td>';
                            echo '<td>';
                            $status = isset($data['active']) ? $data['active'] : 1;
                            $statusText = $status ? 'On' : 'Off';
                            $btnClass = $status ? 'btn-success' : 'btn-secondary';
                            echo '<a href="?menu=banner_brand&toggle=1&brand=' . urlencode($data['brand']) . '" class="btn ' . $btnClass . ' btn-sm">' . $statusText . '</a>';
                            echo '</td>';
                            echo '<td>';
                            echo '<a href="?menu=edit_banner_brand&brand=' . urlencode($data['brand']) . '" class="btn btn-edit">Edit</a> ';
                            echo '<a href="delete_banner.php?brand=' . urlencode($data['brand']) . '" class="btn btn-delete btn-sm" onclick="return confirm(\'Delete?\')">Delete</a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div></content>
