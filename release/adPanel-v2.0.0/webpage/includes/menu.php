<?php
// Partial: webpage/includes/menu.php
// Renders brand/category dropdowns. Accepts $menuData if set, otherwise builds a default menu.
// Links default to $menuIndexPath if provided by page, otherwise to /adpanel/index.php
if (!isset($menuData) || !is_array($menuData)) {
    $brands = [
        'Komatsu', 'Bomag', 'Caterpillar', 'Scania', 'Volvo','Nissan','Hyva', 'Other'
    ];
    $categories = [
        'Engine','Electrical','Brake System','Cylinder','Axle & Stering','Cabin','Filter','Attachment','Final Drive','Hydraulic System','General','Alternatif'
    ];
    $menuData = [];
    foreach ($brands as $b) {
        $menuData[$b] = $categories;
    }
}

// default index path (absolute to site root). Pages may set $menuIndexPath to override (e.g. '../../index.php').
// Prefer explicit $menuIndexPath, then global $INDEX_PATH from site-config, then fallback to hardcoded path.
$indexPath = isset($menuIndexPath) ? $menuIndexPath : (isset($INDEX_PATH) ? $INDEX_PATH : '/adpanel/index.php');
?>
<div class="container p-2 bg-dark d-flex align-items-center">
    <div class="d-flex flex-wrap"style="gap: 0.5px; width: auto;">
    <?php foreach ($menuData as $brand => $brandCategories): ?>
        <div class="btn-group d-inline-block me-2" data-bs-theme="dark">
            <a class="btn btn-primary" href="<?php echo htmlspecialchars($indexPath); ?>?brand=<?php echo urlencode($brand); ?>"><?php echo htmlspecialchars($brand); ?></a>
            <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="visually-hidden">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu bg-secondary">
                <?php /*foreach ($brandCategories as $category): ?>
                    <li><a class="dropdown-item" href="<?php echo htmlspecialchars($indexPath); ?>?brand=<?php echo urlencode($brand); ?>&category=<?php echo urlencode($category); ?>"><?php echo htmlspecialchars($category); ?></a></li>
                <?php endforeach; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?php echo htmlspecialchars($indexPath); ?>?brand=<?php echo urlencode($brand); ?>">All <?php echo htmlspecialchars($brand); ?></a></li>
            </ul> */ ?>

            <?php
            /*
            Disabled: hide category links for Bomag and Caterpillar.
            To re-enable this behavior, remove the comment markers around the following block. */

            if ($brand === 'Other'):
                // For 'Other' brand, show only 'General' and 'Alternatif' categories
                $otherCategories = ['General', 'Alternatif'];
                foreach ($otherCategories as $category): ?>
                    <li><a class="dropdown-item" href="<?php echo htmlspecialchars($indexPath); ?>?brand=<?php echo urlencode($brand); ?>&category=<?php echo urlencode($category); ?>"><?php echo htmlspecialchars($category); ?></a></li>
                <?php endforeach; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?php echo htmlspecialchars($indexPath); ?>?brand=<?php echo urlencode($brand); ?>">All <?php echo htmlspecialchars($brand); ?></a></li>
            <?php elseif ($brand === 'Hyva'):
                // For 'Hyva' brand, show specific categories only
                $hyvaCategories = ['Hydraulic System', 'Electrical', 'Filter', 'General', 'Cylinder', 'Attachment'];
                foreach ($hyvaCategories as $category): ?>
                    <li><a class="dropdown-item" href="<?php echo htmlspecialchars($indexPath); ?>?brand=<?php echo urlencode($brand); ?>&category=<?php echo urlencode($category); ?>"><?php echo htmlspecialchars($category); ?></a></li>
                <?php endforeach; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?php echo htmlspecialchars($indexPath); ?>?brand=<?php echo urlencode($brand); ?>">All <?php echo htmlspecialchars($brand); ?></a></li>
            <?php elseif (!in_array($brand, ['Bomag', 'Caterpillar'])):
                foreach ($brandCategories as $category): ?>
                    <li><a class="dropdown-item" href="<?php echo htmlspecialchars($indexPath); ?>?brand=<?php echo urlencode($brand); ?>&category=<?php echo urlencode($category); ?>"><?php echo htmlspecialchars($category); ?></a></li>
                <?php endforeach; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?php echo htmlspecialchars($indexPath); ?>?brand=<?php echo urlencode($brand); ?>">All <?php echo htmlspecialchars($brand); ?></a></li>
            <?php else: ?>
                <li><a class="dropdown-item" href="<?php echo htmlspecialchars($indexPath); ?>?brand=<?php echo urlencode($brand); ?>">All <?php echo htmlspecialchars($brand); ?></a></li>
            <?php endif;
            
            ?>
        </div>
    <?php endforeach; ?>
    </div>

    <form id="searchFormMenu" class="d-flex ms-auto menu-search"style="gap: 0.5px; width: 350px;" method="get" action="<?php echo htmlspecialchars(isset($INDEX_PATH) ? $INDEX_PATH : $indexPath); ?>">
        <?php
        $selectedCategoryMenu = $_GET['category'] ?? '';
        $queryValueMenu = htmlspecialchars($_GET['query'] ?? '');
        ?>
        <div class="input-group">
            <?php if (!empty($categories) && is_array($categories)): ?>
                <select name="category" class="form-select me-2" aria-label="Kategori" style="max-width:100px;">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $selectedCategoryMenu === $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <input type="hidden" name="category"  value="<?php echo htmlspecialchars($selectedCategoryMenu); ?>">
            <?php endif; ?>

            <input id="searchInputMenu" class="form-control" type="text" placeholder="Cari produk: brand, part number, interchange, description" name="query" aria-label="Cari produk" value="<?php echo $queryValueMenu; ?>">
            <button class="btn btn-primary" type="submit">Search</button>
        </div>
    </form>

    <script>
    (function(){
        var inp = document.getElementById('searchInputMenu');
        if (inp) {
            inp.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    document.getElementById('searchFormMenu').submit();
                }
            });
        }
    })();
    </script>

</div>
