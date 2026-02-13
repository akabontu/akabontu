<?php
// Admin Header: revised layout with session-aware username
if (defined('ADMIN_HEADER_RENDERED')) return;
define('ADMIN_HEADER_RENDERED', true);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$logged_in = !empty($_SESSION['logged_in']);
$username = htmlspecialchars($_SESSION['username'] ?? 'Admin');
$rawRole = $_SESSION['role'] ?? 'A';
$rawUser = $_SESSION['username'] ?? '';
$role = $logged_in ? htmlspecialchars(strtoupper(trim($rawRole))) : '';
?>
<div class="admin-header">
  <div class="header-inner">
    <img src="../../assets/images/adp_lg_new.png" alt="Logo" class="header-logo">
    <div></div>
    <form method="post" action="../Index/logout.php">
      <span class="header-admin-icon">👤</span>
      <?php if ($logged_in): ?><span class="header-username" style="color:darkgreen"><?php echo $username; ?></span><?php endif; ?>
      <button type="submit" class="header-logout">Logout</button>
    </form>
  </div>
</div>
<?php if ($logged_in): ?>
<script>
(function(){
  var role = '<?php echo $role; ?>';
  document.addEventListener('DOMContentLoaded', function(){
    if (document.body) document.body.classList.add('role-' + role);
  });
})();
</script>
<style>
/* Role-based UI adjustments: role-A hides destructive controls */
.role-A .btn-delete { display: none !important; }
/* Elements explicitly marked to be disabled for role A */
.role-A .disable-for-A { opacity: 0.6; pointer-events: none; }
</style>
<?php if ($role === 'A' && strtolower(trim($rawUser)) !== 'admin'): ?>
  <div style="background:#fff7ed;border-left:4px solid #f59e0b;padding:10px 16px;margin:0;">
    <div style="max-width:1100px;margin:0 auto;font-family:Inter,Segoe UI,Arial,sans-serif;color:#92400e;font-size:0.95rem;">
      Beberapa opsi pada halaman disembunyikan karena peran Anda (A). Jika Anda membutuhkan akses penuh (penghapusan), silakan hubungi pemilik/Owner (role B).
    </div>
  </div>
<?php endif; ?>
<?php endif; ?>
<script>
// Layout script: apply fixed-canvas scaling and ensure main-content scrolls
(function(){
  function applyAdminLayout(){
    var wrapper = document.querySelector('.admin-fixed-wrapper');
    if (!wrapper) return;
    var targetW = 1366, targetH = 768;
    function rescale(){
      var scale = Math.min(window.innerWidth / targetW, window.innerHeight / targetH, 1);
      wrapper.style.transform = 'scale(' + scale + ')';
      wrapper.style.transformOrigin = 'top center';
      document.body.style.overflow = (scale < 1) ? 'auto' : 'hidden';
    }
    window.addEventListener('resize', rescale);
    window.addEventListener('load', rescale);
    rescale();

    var main = document.querySelector('.admin-fixed-wrapper .main-content');
    if (main){
      main.style.overflowY = 'auto';
      main.style.overflowX = 'hidden';
      main.style.height = '100%';
    }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', applyAdminLayout); else applyAdminLayout();
})();
</script>
