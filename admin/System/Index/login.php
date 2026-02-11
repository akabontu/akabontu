<?php
// Login page moved into admin folder — now using DB-backed auth, CSRF and rate-limiting
// Configure session for localhost
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
session_start();
require_once __DIR__ . '/../kon.php';

if (!empty($_SESSION['logged_in'])) {
  header('Location: index.php'); exit;
}

$errors = [];

// Simple session-based rate limit: max 5 attempts per 15 minutes
$maxAttempts = 5;
$decaySeconds = 15 * 60;
if (!isset($_SESSION['login_attempts'])) {
  $_SESSION['login_attempts'] = [];
}
// purge old attempts
$_SESSION['login_attempts'] = array_filter($_SESSION['login_attempts'], function($ts) use ($decaySeconds) { return ($ts + $decaySeconds) >= time(); });
if (count($_SESSION['login_attempts']) >= $maxAttempts) {
  $errors[] = 'Terlalu banyak percobaan login. Coba lagi nanti.';
}

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
  $token = $_POST['csrf_token'] ?? '';
  if (!hash_equals($_SESSION['csrf_token'], $token)) {
    $errors[] = 'CSRF token tidak valid.';
  } 
  else {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    if ($user === '' || $pass === '') {
      $errors[] = 'Username dan password harus diisi.';
    } else {
      $stmt = mysqli_prepare($kon, 'SELECT id, username, password_hash, role FROM users WHERE username = ? LIMIT 1');
      if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $user);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $uid, $uname, $phash, $urole);
        if (mysqli_stmt_fetch($stmt)) {
          if (password_verify($pass, $phash)) {
            // success
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $uname;
            $_SESSION['role'] = isset($urole) ? strtoupper(trim($urole)) : 'A';
            // reset attempts
            $_SESSION['login_attempts'] = [];
            mysqli_stmt_close($stmt);
            header('Location: ../action/menu_admin.php'); exit;
          } else {
            $errors[] = 'Username atau password salah.';
          }
        } else {
          $errors[] = 'Username atau password salah.';
        }
        mysqli_stmt_close($stmt);
      } else {
        $errors[] = 'Database error.';
      }
    }
  }
  
  // record attempt timestamp regardless of cause
  $_SESSION['login_attempts'][] = time();
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login - adPanel</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
  <style>
    /* Login layout with header/footer space reserved */
    body { background: #282829; }
    /* viewport between header(75px) and footer(75px) */
    .login-viewport { min-height: calc(100vh - 150px); display:flex; align-items:center; justify-content:center; padding:20px 0; }
    .login-wrap{
      width:720px;
      height:460px;
      box-sizing:border-box;
      margin:0;
      padding:0;
    }
    .card{ padding:32px; height:100%; box-sizing:border-box; display:flex; flex-direction:column; justify-content:center; }
    .page-title{ font-size:28px; margin-bottom:16px; }
    .form-row{ margin-bottom:14px; }
    .input{ padding:12px 14px; font-size:16px; width:100%; box-sizing:border-box; }
    .form-actions{ display:flex; justify-content:flex-end; }
    .form-actions .btn{ padding:10px 18px; font-size:15px; min-width:120px; }
    @media (max-width:820px){
      .login-viewport{ min-height: auto; }
      .login-wrap{ width:92%; height:auto; }
      .card{ padding:20px; }
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/../action/admin_header.php'; ?>
  <div class="login-viewport">
  <div class="container login-wrap">
    <div class="card">
      <h2 class="page-title">Selamat Datang di adPanel</h2>
      <?php if (!empty($errors)): ?>
        <div style="color:#8a1f1f;margin:10px 0;">
          <?php foreach($errors as $e) echo htmlspecialchars($e) . '<br>'; ?>
        </div>
      <?php endif; ?>
      <form method="post" class="form">
        <div class="form-row">
          <label for="username">Username</label>
          <input id="username" class="input" type="text" name="username" required>
        </div>
        <div class="form-row">
          <label for="password">Password</label>
          <input id="password" class="input" type="password" name="password" required>
        </div>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="form-actions">
          <button class="btn btn-add" type="submit">Login</button>
        </div>
      </form>
      
  </div>
  </div>
<?php include __DIR__ . '/../action/admin_footer.php'; ?>
</body>
</html>