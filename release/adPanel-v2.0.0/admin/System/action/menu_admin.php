<?php
// Admin Menu - centralized renderer for full-page and AJAX fragment loads
require_once __DIR__ . '/../kon.php';
// session config and auth: ensure only logged-in users can access the admin shell
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['logged_in'])) {
  // If AJAX requested, return 401 so client can redirect to login
  if (isset($_GET['ajax'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo 'Unauthorized';
    exit;
  }
  header('Location: ../Index/login.php');
  exit;
}

// Helper: include a file and return only its inner fragment (strip <body> or <main>)
function include_fragment($path){
  if (!file_exists($path)) return '<div style="padding:1rem;color:#900">File not found: '.htmlspecialchars($path).'</div>';
  ob_start();
  include $path;
  $c = ob_get_clean();
  if (preg_match('#<body[^>]*>(.*)</body>#is', $c, $m)) return $m[1];
  if (preg_match('#<main[^>]*>(.*)</main>#is', $c, $m)) return $m[1];
  return $c;
}

$currentMenu = $_GET['menu'] ?? 'dashboard';
$interchangeMenus = ['itc_product','list_itc','itc','add','edit','view','add_itc','edit_itc','add_prod_itc','edit_prod_itc'];
$settingsMenus = ['banner_product','banner_brand','edit_banner','edit_banner_brand'];
$stockMenus = ['stock_transaction','stock_list'];
$interchangeOpen = in_array($currentMenu, $interchangeMenus, true);
$settingsOpen = in_array($currentMenu, $settingsMenus, true);
$stockOpen = in_array($currentMenu, $stockMenus, true);
function render_main_content($menu) {
  // $menu is the menu key, e.g. 'dashboard', 'product', 'add', etc.
  // Ensure DB connection is visible to included pages when this function is called
  global $kon;
  // Use includes for pages; pages that are included should support being embedded (IN_MENU_ADMIN)
    if ($menu === 'dashboard') {
      include __DIR__ . '/admin_dashboard.php';
      return;
    }

    if ($menu === 'product') {
      include __DIR__ . '/../../Control/product/Views/Product.php';
      return;
    }

    // Map menu keys to include paths
    switch ($menu) {
        case 'add_itc':
        case 'edit_itc':
            if (isset($_GET['no'])) {
                $_GET['no'] = intval($_GET['no']);
                include __DIR__ . '/../../Control/matno/' . ($menu === 'add_itc' ? 'add_pn_itc.php' : 'edit_pn_itc.php');
                return;
            }
            break;
        case 'add':
            include __DIR__ . '/../../Control/product/Actions/add_prod.php';
            return;
        case 'list_itc':
            include __DIR__ . '/../../Control/matno/list_pn_itc.php';
            return;
        case 'itc_product':
          include __DIR__ . '/../../Control/product/Views/Product_itc.php';
          return;
        case 'add_prod_itc':
          include __DIR__ . '/../../Control/product/Actions/add_prod_itc.php';
          return;
        case 'edit_prod_itc':
          include __DIR__ . '/../../Control/product/Actions/edit_prod_itc.php';
          return;
        case 'edit':
            if (isset($_GET['no'])) {
                $_GET['no'] = intval($_GET['no']);
                include __DIR__ . '/../../Control/product/Actions/edit_prod.php';
                return;
            }
            break;
        case 'view':
            if (isset($_GET['no'])) {
                $_GET['no'] = intval($_GET['no']);
                include __DIR__ . '/../../Control/product/Views/detail_prod.php';
                return;
            }
            break;
        case 'banner_product':
            include __DIR__ . '/banner_product.php';
            return;
        case 'banner_brand':
            include __DIR__ . '/banner_brand.php';
            return;
        case 'edit_banner':
            if (isset($_GET['brand'])) { include __DIR__ . '/edit_banner.php'; return; }
            break;
        case 'edit_banner_brand':
            if (isset($_GET['brand'])) { include __DIR__ . '/edit_banner_brand.php'; return; }
            break;
        case 'create_admin':
            include __DIR__ . '/create_admin.php';
            return;
        case 'report':
            include __DIR__ . '/../../Control/product/Reports/report_prod.php';
            return;
        case 'transaction':
          include __DIR__ . '/../../Control/product/Actions/transaction.php';
          return;
        case 'stock_transaction':
          include __DIR__ . '/../../Control/stock/Stock_transaction.php';
          return;
        case 'stock_list':
          include __DIR__ . '/../../Control/stock/stock_list.php';
          return;
        case 'supplier':
          include __DIR__ . '/../../Control/product/Actions/transaction/supplier.php';
          return;
        case 'customer':
          include __DIR__ . '/../../Control/product/Actions/transaction/customer.php';
          return;
    }

    echo '<div style="padding:2rem;"><h1>Menu tidak ditemukan</h1><p>Menu yang Anda cari tidak tersedia.</p></div>';
}

// If requested via AJAX, output only the inner fragment and exit early
if (isset($_GET['ajax'])){
  if (!defined('IN_MENU_ADMIN')) define('IN_MENU_ADMIN', true); // ensure fragments render without standalone shell
  $menu_param = $currentMenu;
  render_main_content($menu_param);
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Menu</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
   <?php include 'admin_header.php'; ?>
  <div class="admin-fixed-wrapper">
    <div class="admin-layout">
     <nav class="sidebar">
      <h3>Menu</h3>
      <ul>
        <li><a href="?menu=dashboard" class="<?= $currentMenu === 'dashboard' ? 'active' : '' ?>"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg> Dashboard</a></li>
        <li><a href="?menu=product" class="<?= $currentMenu === 'product' ? 'active' : '' ?>"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="7.5,4.27 12,6.11 16.5,4.27"></polyline><line x1="12" y1="22.76" x2="12" y2="12"></line></svg> Product</a></li>
        <li>
          <input type="checkbox" id="interchange-toggle" <?= $interchangeOpen ? 'checked' : '' ?>>
          <label class="menu-toggle" for="interchange-toggle"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3l4 4-4 4"></path><path d="M20 7H4"></path><path d="M8 21l-4-4 4-4"></path><path d="M4 17h16"></path></svg> Interchange</label>
          <ul class="submenu">
            <li><a href="?menu=itc_product" class="<?= in_array($currentMenu,['itc_product','itc','add','edit','view','add_itc','edit_itc'], true) ? 'active' : '' ?>">Product Interchange</a></li>
            <li><a href="?menu=list_itc" class="<?= in_array($currentMenu,['list_itc','itc','add','edit','view','add_itc','edit_itc'], true) ? 'active' : '' ?>">Part Number Interchange</a></li>
          </ul>
        </li>
        <li>
          <input type="checkbox" id="stock-toggle" <?= $stockOpen ? 'checked' : '' ?>>
          <label class="menu-toggle" for="stock-toggle"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg> Stock Management</label>
          <ul class="submenu">
            <li><a href="?menu=stock_list" class="<?= $currentMenu === 'stock_list' ? 'active' : '' ?>">Stock List</a></li>
            <li><a href="?menu=stock_transaction" class="<?= $currentMenu === 'stock_transaction' ? 'active' : '' ?>">Stock Transaction</a></li>
          </ul>
        </li>
        <li>
          <input type="checkbox" id="submenu-toggle" <?= $settingsOpen ? 'checked' : '' ?>>
          <label class="menu-toggle" for="submenu-toggle"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1 1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg> Setting</label>
          <ul class="submenu">
            <li><a href="?menu=banner_product" class="<?= $currentMenu === 'banner_product' ? 'active' : '' ?>">Banner Product</a></li>
            <li><a href="?menu=banner_brand" class="<?= $currentMenu === 'banner_brand' ? 'active' : '' ?>">Banner Brand</a></li>
          </ul>
        </li>
        <li><a href="?menu=transaction" class="<?= $currentMenu === 'transaction' ? 'active' : '' ?>"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg> Transaction</a></li>
        <li><a href="?menu=report" class="<?= $currentMenu === 'report' ? 'active' : '' ?>"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14,2 14,8 20,8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10,9 9,9 8,9"></polyline></svg> Reports</a></li>
        <?php if (!empty($_SESSION['role']) && in_array($_SESSION['role'], ['B','C'])): ?>
        <li><a href="?menu=create_admin"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg> Tambah Admin</a></li>
        <?php endif; ?>
        <li><a href="../../../index.php" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9,22 9,12 15,12 15,22"></polyline></svg> Overview</a></li>
      </ul>
    </nav>
      <main class="main-content">
        <div class="main-inner">
        <?php if (!defined('IN_MENU_ADMIN')) define('IN_MENU_ADMIN', true); render_main_content($currentMenu); ?>
        </div>
      </main>
    </div>
   </div>
     <script>
     (function(){
       // Fragment cache and prefetch to reduce perceived delay when navigating
       var fragmentCache = Object.create(null);
       var ongoing = Object.create(null);

       function executeScripts(container){
         if (!container) return;
         var scripts = container.querySelectorAll('script');
         scripts.forEach(function(oldScript){
           var fresh = document.createElement('script');
           Array.prototype.slice.call(oldScript.attributes || []).forEach(function(attr){
             fresh.setAttribute(attr.name, attr.value);
           });
           fresh.textContent = oldScript.textContent;
           oldScript.parentNode.replaceChild(fresh, oldScript);
         });
       }

       function fullUrlFromHref(href){
         if (!href) return window.location.pathname + window.location.search;
         if (href.startsWith('?') || href.startsWith('./') || href.startsWith('./?')){
           return window.location.pathname + href;
         }
         return href;
       }

       function showSkeleton(){
         var inner = document.querySelector('.main-inner');
         if (!inner) return;
         inner.innerHTML = '<div class="loading-skeleton" style="padding:18px 24px;">'
           + '<div style="height:22px;background:#eee;width:40%;margin-bottom:12px;border-radius:4px"></div>'
           + '<div style="height:12px;background:#eee;width:100%;margin-bottom:8px;border-radius:4px"></div>'
           + '<div style="height:12px;background:#eee;width:90%;margin-bottom:8px;border-radius:4px"></div>'
           + '<div style="height:12px;background:#eee;width:80%;margin-bottom:8px;border-radius:4px"></div>'
           + '</div>';
       }

       function fetchAndInject(href, push){
         var full = fullUrlFromHref(href || '?menu=dashboard');
         if (fragmentCache[full]){
           var inner = document.querySelector('.main-inner'); if (!inner) return;
           inner.innerHTML = fragmentCache[full];
           executeScripts(inner);
           if (push !== false) history.pushState({url: full}, '', full);
           updateActiveFromUrl(full);
           inner.scrollTop = 0;
           initProductGallery();
           return;
         }
         if (ongoing[full]) return; // already fetching
         showSkeleton();
         var sep = full.indexOf('?') !== -1 ? '&' : '?';
         var fetchUrl = full + sep + 'ajax=1';
         ongoing[full] = true;
         fetch(fetchUrl, { credentials: 'same-origin' }).then(function(r){
           if (!r.ok) {
             if (r.status === 401) { window.location.href = '../Index/login.php'; return Promise.reject('Unauthorized'); }
             return Promise.reject('HTTP ' + r.status);
           }
           return r.text();
         }).then(function(html){
           fragmentCache[full] = html;
           var inner = document.querySelector('.main-inner'); if (!inner) return;
           inner.innerHTML = html;
           executeScripts(inner);
           if (push !== false) history.pushState({url: full}, '', full);
           updateActiveFromUrl(full);
           inner.scrollTop = 0;
           initProductGallery();
         }).catch(function(e){
           console.error('Load error', e);
           var inner = document.querySelector('.main-inner'); if (inner) inner.innerHTML = '<div style="padding:1rem;color:#900">Gagal memuat halaman.</div>';
         }).finally(function(){ delete ongoing[full]; });
       }

       function updateActiveFromUrl(full){
         try{
           var url = new URL(full, location.origin);
           var menuVal = url.searchParams.get('menu') || 'dashboard';
           var productActionMenus = new Set(['add','edit','view']);
           if (productActionMenus.has(menuVal)) menuVal = 'product';
           var interchangeMenus = new Set(['itc_product','list_itc','itc','add_itc','edit_itc']);
           var settingsMenus = new Set(['banner_product','banner_brand','edit_banner','edit_banner_brand']);
           document.querySelectorAll('.sidebar a').forEach(function(a){
             var href = a.getAttribute('href') || '';
             a.classList.toggle('active', href.indexOf('menu='+menuVal) !== -1 || (menuVal==='dashboard' && (href==='?menu=dashboard' || href==='')) );
           });
           var interToggle = document.getElementById('interchange-toggle');
           if (stockMenus = new Set(['transaction','stock_list']);
           var interToggle = document.getElementById('interchange-toggle');
           if (interToggle) interToggle.checked = interchangeMenus.has(menuVal);
           var settingToggle = document.getElementById('submenu-toggle');
           if (settingToggle) settingToggle.checked = settingsMenus.has(menuVal);
           var stockToggle = document.getElementById('stock-toggle');
           if (stockToggle) stockToggle.checked = stock
       }

       // Click handler: use AJAX for menu navigation, respect data-no-ajax
       document.addEventListener('click', function(e){
         var a = e.target.closest && e.target.closest('a');
         if (!a) return;
         if (a.hasAttribute('data-no-ajax')) return;
         var href = a.getAttribute('href') || '';
         if (!href || href.startsWith('#')) return;
         if (a.getAttribute('target') === '_blank') return;
         try{ var abs = new URL(href, location.href); if (abs.origin !== location.origin) return; } catch(e){}
         if (href.indexOf('menu=') !== -1 || href.charAt(0) === '?'){
           e.preventDefault();
           fetchAndInject(href, true);
         }
       });

       // Prefetch on hover for faster perceived load
       document.querySelectorAll('.sidebar a').forEach(function(a){
         a.addEventListener('mouseenter', function(){
           var href = a.getAttribute('href') || '';
           if (!href) return;
           var full = fullUrlFromHref(href);
           if (fragmentCache[full] || ongoing[full]) return;
           var sep = full.indexOf('?') !== -1 ? '&' : '?';
           var fetchUrl = full + sep + 'ajax=1';
           ongoing[full] = true;
           fetch(fetchUrl, { credentials: 'same-origin' }).then(function(r){ if (!r.ok) return; return r.text(); }).then(function(html){ if (html) fragmentCache[full] = html; }).catch(function(){}).finally(function(){ delete ongoing[full]; });
         });
       });

       window.addEventListener('popstate', function(e){
         fetchAndInject(location.search || '?menu=dashboard', false);
       });
       updateActiveFromUrl(location.pathname + location.search);

       function initProductGallery(){
         var galleries = document.querySelectorAll('.product-gallery');
         galleries.forEach(function(gallery){
           if (gallery.getAttribute('data-bound') === '1') return;
           var data = gallery.getAttribute('data-images') || '[]';
           var images = [];
           try { images = JSON.parse(data); } catch(e) { images = []; }
           if (!images.length) return;

           var currentIndex = 0;
           var main = gallery.querySelector('.gallery-main-img');
           var thumbs = Array.prototype.slice.call(gallery.querySelectorAll('.gallery-thumb'));
           var navs = Array.prototype.slice.call(gallery.querySelectorAll('.gallery-nav'));

           function setActive(idx){
             currentIndex = (idx + images.length) % images.length;
             if (main) main.src = images[currentIndex];
             thumbs.forEach(function(btn, i){
               btn.classList.toggle('is-active', i === currentIndex);
               btn.style.borderColor = i === currentIndex ? '#2563eb' : '#e5e7eb';
             });
           }

           thumbs.forEach(function(btn){
             btn.addEventListener('click', function(){
               var idx = parseInt(btn.getAttribute('data-index') || '0', 10);
               setActive(idx);
             });
           });

           navs.forEach(function(btn){
             btn.addEventListener('click', function(){
               var dir = btn.getAttribute('data-dir');
               if (dir === 'prev') setActive(currentIndex - 1);
               else setActive(currentIndex + 1);
             });
           });

           gallery.setAttribute('data-bound', '1');
         });
       }

       // Global image preview handler for the add product form (AJAX fragments)
       (function(){
         if (window.__productImagePreviewBound) return;
         window.__productImagePreviewBound = true;

         var max = 5;
         var urls = [];
         var lastSignature = '';

         function clearUrls(){
           urls.forEach(function(u){ try { URL.revokeObjectURL(u); } catch(e){} });
           urls = [];
         }

         function getNodes(){
           return {
             input: document.getElementById('images'),
             preview: document.getElementById('fotoPreviewList'),
             counter: document.getElementById('image-counter')
           };
         }

         function render(files){
           var nodes = getNodes();
           if (!nodes.input || !nodes.preview) return;

           clearUrls();
           nodes.preview.innerHTML = '';
           var list = Array.prototype.slice.call(files || nodes.input.files || []);

           if (nodes.counter){
             var selected = list.length;
             if (selected > max){
               nodes.counter.textContent = max + '/' + max + ' dipilih (batas tercapai)';
               nodes.counter.style.color = '#b42318';
             } else {
               nodes.counter.textContent = selected + '/' + max + ' dipilih';
               nodes.counter.style.color = '#475467';
             }
           }

           list.slice(0, max).forEach(function(file){
             if (!/^image\//.test(file.type)) return;
             var wrapper = document.createElement('div');
             wrapper.style.width = '132px';
             wrapper.style.height = '132px';
             wrapper.style.border = '1px solid #e5e7eb';
             wrapper.style.borderRadius = '10px';
             wrapper.style.overflow = 'hidden';
             wrapper.style.display = 'flex';
             wrapper.style.alignItems = 'center';
             wrapper.style.justifyContent = 'center';

             var img = document.createElement('img');
             img.className = 'thumbnail';
             img.style.maxWidth = '100%';
             img.style.maxHeight = '100%';
             img.style.objectFit = 'cover';

             var url = URL.createObjectURL(file);
             urls.push(url);
             img.src = url;
             wrapper.appendChild(img);
             nodes.preview.appendChild(wrapper);
           });
         }

         document.addEventListener('change', function(e){
           var input = document.getElementById('images');
           if (!input || e.target !== input) return;
           render(e.target.files);
           lastSignature = Array.prototype.map.call(input.files || [], function(f){ return f.name + ':' + f.size; }).join('|');
         }, true);

         setInterval(function(){
           var input = document.getElementById('images');
           if (!input) return;
           var sig = Array.prototype.map.call(input.files || [], function(f){ return f.name + ':' + f.size; }).join('|');
           if (sig !== lastSignature){
             lastSignature = sig;
             render(input.files);
           }
         }, 400);

         render();
       })();

       initProductGallery();
     })();
     </script>
     <?php include 'admin_footer.php'; ?>
</body>
</html>
