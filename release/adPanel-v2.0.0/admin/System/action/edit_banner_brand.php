<?php
// Edit Banner Brand
require_once __DIR__ . '/../kon.php';

// Check if brand is provided
if (!isset($_GET['brand']) || empty($_GET['brand'])) {
    die("Brand not specified.");
}

$brand = mysqli_real_escape_string($kon, $_GET['brand']);

// Fetch current banner data
$query = "SELECT * FROM logo_brand WHERE brand = '$brand'";
$result = mysqli_query($kon, $query);
if (!$result || mysqli_num_rows($result) == 0) {
    die("Banner not found.");
}
$data = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_brand = mysqli_real_escape_string($kon, $_POST['brand']);
    
    // Check if image is uploaded
    if (!empty($_FILES['banner_image']['tmp_name'])) {
        $image = file_get_contents($_FILES['banner_image']['tmp_name']);
        $update_query = "UPDATE logo_brand SET brand = '$new_brand', logo_img = ? WHERE brand = '$brand'";
        $stmt = mysqli_prepare($kon, $update_query);
        mysqli_stmt_bind_param($stmt, 's', $image);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $update_query = "UPDATE logo_brand SET brand = '$new_brand' WHERE brand = '$brand'";
        mysqli_query($kon, $update_query);
    }
    
    // Redirect back to banner_brand
    header("Location: ?menu=banner_brand");
    exit;
}
?>

<div class="top">
    <h2>Edit Banner Brand</h2>
</div>
<div class="form-row">
    <div class="col-md-6">
        <div class="card p-3 mb-3">
            <h4>Edit Banner for <?php echo htmlspecialchars($data['brand']); ?></h4>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <label for="brand" class="form-label">Brand:</label>
                    <input type="text" name="brand" id="brand" class="form-control" value="<?php echo htmlspecialchars($data['brand']); ?>" required>
                </div>
                <div class="form-row">
                    <label for="image">New Image (optional, JPG/PNG/GIF max 2MB)</label>
                    <input type="file" name="banner_image" id="banner_image" class="form-control" accept="image/*">
                    <small>Leave empty to keep current image.</small>
                </div>
                <div class="form-row">
                    <label>Current Image:</label><br>
                    <?php if (!empty($data['logo_img'])): ?>
                        <?php
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime = $finfo ? finfo_buffer($finfo, $data['logo_img']) : 'image/jpeg';
                        if ($finfo) finfo_close($finfo);
                        ?>
                        <img src="data:<?php echo htmlspecialchars($mime); ?>;base64,<?php echo base64_encode($data['logo_img']); ?>" alt="current banner" style="max-height:100px; max-width:200px;">
                    <?php else: ?>
                        No image
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-add">Update Banner</button>
                <a href="?menu=banner_brand" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>