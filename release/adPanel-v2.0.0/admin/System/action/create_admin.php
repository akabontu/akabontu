<?php
// Simple admin creation script (web) — remove or protect after use
require_once __DIR__ . '/../kon.php';
if (!defined('IN_MENU_ADMIN')) define('IN_MENU_ADMIN', false);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $role = ($_POST['role'] ?? 'A');
  if ($username === '' || $password === '') {
    $error = 'Username dan password harus diisi.';
  } else {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($kon, 'INSERT INTO users (username, password_hash, role, created_at) VALUES (?, ?, ?, NOW())');
    if ($stmt) {
      mysqli_stmt_bind_param($stmt, 'sss', $username, $hash, $role);
      $ok = mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);
      if ($ok) {
        $message = 'Admin berhasil dibuat.';
      } else {
        $error = 'Gagal membuat admin: ' . mysqli_error($kon);
      }
    } else {
      $error = 'Database error.';
    }
  }
}
?>
<?php if (!IN_MENU_ADMIN): ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Create Admin</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<?php endif; ?>
  <div class="container" style="max-width:640px;margin:3rem auto;">
    <div class="card">
      <h2>Create Admin User</h2>
      <?php if (!empty($error)): ?><div style="color:#8a1f1f"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
      <?php if (!empty($message)): ?><div style="color:green"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
      <form method="post">
        <div class="form-row"><label>Username</label><input class="input" name="username" required></div>
        <div class="form-row"><label>Password</label><input class="input" name="password" type="password" required></div>
        <div class="form-row"><label>Role</label>
          <select name="role" class="input" style="max-width:220px;">
            <option value="A">A — Admin (input, update, view)</option>
            <option value="B" selected>B — Owner (all permissions)</option>
            <option value="C">C — Dev (all permissions)</option>
          </select>
        </div>
        <div class="form-actions">
          <button class="btn btn-add" type="submit">Create</button>
          <a class="btn btn-secondary" href="?menu=dashboard">Kembali</a>
        </div>
      </form>
      <div style="margin-top:12px;color:#6b7280">Note: remove or protect this script after creating admin user.</div>
    </div>
  </div>
<?php if (!IN_MENU_ADMIN): ?>
</body>
</html>
<?php endif; ?>
