<!-- Header -->
<?php
// Ensure site config is loaded so header can use $ASSET_PATH and $INDEX_PATH
include __DIR__ . '/site-config.php';
?>
<header id="site-header" class="container py-2 bg-transparent shadow-sm">
    <div class="row align-items-center">
        <div class="col-auto">
            <a class="navbar-brand" href="<?php echo htmlspecialchars($INDEX_PATH); ?>"><img src="<?php echo htmlspecialchars($ASSET_PATH); ?>/img/logo_magz.png" alt="CV. Magz Group" style="height:70px;"></a>
        </div>
        <div class="col-12 col-md">
              <h2 class="text-blue h5 mb-0">A Be Solution<br><span class="h5 fw-normal">Heavy Equipment &amp; Truck Spare Parts</span></h2>
              <p class="fs-4 fw-bold text-muted mb-0">Committed to Serve — Ready to Supply</p>
        </div>
        
    </div>
</header>

<?php
// Include site-wide menu partial. If the page set $menuData beforehand, the partial will use it.
include __DIR__ . '/menu.php';
?>

