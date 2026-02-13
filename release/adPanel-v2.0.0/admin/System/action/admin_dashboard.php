<?php
// Admin Dashboard - Main dashboard page
if (!defined('IN_MENU_ADMIN')) {
    header('Location: ../Index/login.php');
    exit;
}

global $kon;
$product_count = mysqli_fetch_assoc(mysqli_query($kon, "SELECT COUNT(*) as total FROM Product"))['total'] ?? 0;
$product_itc_count = mysqli_fetch_assoc(mysqli_query($kon, "SELECT COUNT(*) as total FROM itc_product"))['total'] ?? 0;
$supplier_count = mysqli_fetch_assoc(mysqli_query($kon, "SELECT COUNT(*) as total FROM Supplier"))['total'] ?? 0;
$customer_count = mysqli_fetch_assoc(mysqli_query($kon, "SELECT COUNT(*) as total FROM Customer"))['total'] ?? 0;

// Check if stock tables exist
$tables_exist = false;
$check_table = mysqli_query($kon, "SHOW TABLES LIKE 'stock_movement'");
if ($check_table && mysqli_num_rows($check_table) > 0) {
    $tables_exist = true;
}

$stock_data = [
    'total_items' => 0,
    'total_stock' => 0,
    'low_stock' => 0,
    'out_of_stock' => 0
];
$recent_transactions = false;

if ($tables_exist) {
    // Get stock statistics from Product table
    $stock_query = "SELECT 
        COUNT(*) as total_items,
        COALESCE(SUM(Qty), 0) as total_stock,
        SUM(CASE WHEN Qty BETWEEN 1 AND 7 THEN 1 ELSE 0 END) as low_stock,
        SUM(CASE WHEN Qty = 0 THEN 1 ELSE 0 END) as out_of_stock
    FROM Product";
    $stock_result = mysqli_query($kon, $stock_query);
    if ($stock_result) {
        $stock_data = mysqli_fetch_assoc($stock_result) ?? $stock_data;
    }

    // Get recent transactions
    $recent_transactions_query = "SELECT 
        sm.id,
        sm.part_number,
        sm.qty_in,
        sm.qty_out,
        sm.note,
        sm.created_at
    FROM stock_movement sm
    ORDER BY sm.created_at DESC
    LIMIT 5";
    $recent_transactions = mysqli_query($kon, $recent_transactions_query);
}

$cards = [
    ['href' => '?menu=product', 'title' => 'Product', 'desc' => 'Kelola data produk & stok', 'icon' => '📦', 'color' => '#3b82f6'],
    ['href' => '?menu=stock_list', 'title' => 'Stock List', 'desc' => 'Monitor stok gudang', 'icon' => '📊', 'color' => '#10b981'],
    ['href' => '?menu=stock_transaction', 'title' => 'Stock Transaction', 'desc' => 'Transaksi stock IN/OUT', 'icon' => '🔄', 'color' => '#8b5cf6'],
    ['href' => '?menu=itc_product', 'title' => 'Product Interchange', 'desc' => 'Relasi interchange produk', 'icon' => '🔗', 'color' => '#f59e0b'],
    ['href' => '?menu=list_itc', 'title' => 'Part Number Interchange', 'desc' => 'Relasi interchange part number', 'icon' => '🔢', 'color' => '#06b6d4'],
    ['href' => '?menu=banner_product', 'title' => 'Banner Product', 'desc' => 'Kelola banner utama', 'icon' => '🖼️', 'color' => '#ec4899'],
    ['href' => '?menu=banner_brand', 'title' => 'Banner Brand', 'desc' => 'Logo brand carousel', 'icon' => '🏷️', 'color' => '#14b8a6'],
    ['href' => '?menu=report', 'title' => 'Reports', 'desc' => 'Laporan produk & aktivitas', 'icon' => '📈', 'color' => '#6366f1'],
];

if (!empty($_SESSION['role']) && in_array($_SESSION['role'], ['B','C'])) {
    $cards[] = ['href' => '?menu=create_admin', 'title' => 'Tambah Admin', 'desc' => 'Kelola akun admin', 'icon' => '👤', 'color' => '#ef4444'];
}
?>

<div style="overflow:hidden; height:800px;width:100%;">
    <h1 style="margin:0 0 8px;">Dashboard Admin</h1>
    <p style="margin:0 0 24px;color:#475569;">Selamat datang di panel administrasi. Pilih menu di bawah untuk memulai.</p>

    <!-- Statistics Cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;margin-bottom:24px;">
         <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="font-size:28px;background:#10b981;color:white;width:50px;height:50px;border-radius:10px;display:flex;align-items:center;justify-content:center;">👤</div>
                <div>
                    <div style="font-size:0.85rem;color:#64748b;margin-bottom:4px;">Total Supplier</div>
                    <div style="font-size:1.75rem;font-weight:700;color:#1e293b;"><?php echo number_format($supplier_count); ?></div>
                </div>
            </div>
        </div>
         <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="font-size:28px;background:#3b82f6;color:white;width:50px;height:50px;border-radius:10px;display:flex;align-items:center;justify-content:center;">👤</div>
                <div>
                    <div style="font-size:0.85rem;color:#64748b;margin-bottom:4px;">Total Customer</div>
                    <div style="font-size:1.75rem;font-weight:700;color:#1e293b;"><?php echo number_format($customer_count); ?></div>
                </div>
            </div>
        </div>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="font-size:28px;background:#6366f1;color:white;width:50px;height:50px;border-radius:10px;display:flex;align-items:center;justify-content:center;">📦</div>
                <div>
                    <div style="font-size:0.85rem;color:#64748b;margin-bottom:4px;">Total Products</div>
                    <div style="font-size:1.75rem;font-weight:700;color:#1e293b;"><?php echo number_format($product_count); ?></div>
                </div>
            </div>
        </div>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="font-size:28px;background:#e6c222;color:white;width:50px;height:50px;border-radius:10px;display:flex;align-items:center;justify-content:center;">📦</div>
                <div>
                    <div style="font-size:0.85rem;color:#64748b;margin-bottom:4px;">Total Interchange</div>
                    <div style="font-size:1.75rem;font-weight:700;color:#1e293b;"><?php echo number_format($product_itc_count); ?></div>
                </div>
            </div>
        </div>

        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="font-size:28px;">📊</div>
                <div>
                    <div style="font-size:0.85rem;color:#64748b;margin-bottom:4px;">Total Stock Items</div>
                    <div style="font-size:1.75rem;font-weight:700;color:#1e293b;"><?php echo number_format($stock_data['total_items']); ?></div>
                </div>
            </div>
        </div>

        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="font-size:28px;">⚠️</div>
                <div>
                    <div style="font-size:0.85rem;color:#64748b;margin-bottom:4px;">Low Stock</div>
                    <div style="font-size:1.75rem;font-weight:700;color:#f59e0b;"><?php echo number_format($stock_data['low_stock']); ?></div>
                </div>
            </div>
        </div>

        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="font-size:28px;">🚫</div>
                <div>
                    <div style="font-size:0.85rem;color:#64748b;margin-bottom:4px;">Out of Stock</div>
                    <div style="font-size:1.75rem;font-weight:700;color:#ef4444;"><?php echo number_format($stock_data['out_of_stock']); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Setup Warning -->
    <?php if (!$tables_exist): ?>
    <div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:10px;padding:16px;margin-bottom:24px;">
        <div style="display:flex;align-items:start;gap:12px;">
            <div style="font-size:24px;">⚠️</div>
            <div>
                <div style="font-weight:700;color:#92400e;margin-bottom:8px;">Stock Management Tables Not Found</div>
                <div style="color:#78350f;font-size:0.95rem;margin-bottom:12px;">
                    Database tables for Stock Management feature are not created yet. Please import the SQL file to enable stock tracking.
                </div>
                <div style="background:#fff;border:1px solid #fcd34d;border-radius:6px;padding:12px;font-family:monospace;font-size:0.85rem;color:#92400e;">
                    📁 File: <strong>db/create_stock_movement_triggers.sql</strong><br>
                    💡 Import this file using phpMyAdmin or run via MySQL CLI
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Menu Cards -->
    <h2 style="margin:24px 0 12px;font-size:1.25rem;color:#1e293b;">Quick Access Menu</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px;">
        <?php foreach ($cards as $card): ?>
        <a class="dashboard-card-link" href="<?php echo htmlspecialchars($card['href']); ?>" style="text-decoration:none;color:inherit;">
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px;box-shadow:0 4px 12px rgba(15,23,42,0.06);display:flex;flex-direction:column;gap:6px;transition:all 0.2s;position:relative;overflow:hidden;">
                <div style="position:absolute;top:0;right:0;width:60px;height:60px;background:<?php echo $card['color']; ?>;opacity:0.1;border-radius:0 0 0 100%;"></div>
                <div style="font-size:22px;position:relative;z-index:1;"><?php echo $card['icon']; ?></div>
                <div style="font-weight:700;font-size:1.05rem;position:relative;z-index:1;"><?php echo htmlspecialchars($card['title']); ?></div>
                <div style="color:#64748b;font-size:0.95rem;position:relative;z-index:1;"><?php echo htmlspecialchars($card['desc']); ?></div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Recent Transactions -->
    <?php if ($recent_transactions && mysqli_num_rows($recent_transactions) > 0): ?>
    <h2 style="margin:32px 0 12px;font-size:1.25rem;color:#1e293b;">Recent Transactions</h2>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.08); height: 190px; overflow-y: auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:2px solid #e5e7eb;">
                    <th style="padding:12px 8px;text-align:left;font-size:0.9rem;color:#64748b;font-weight:600;">Part Number</th>
                    <th style="padding:12px 8px;text-align:center;font-size:0.9rem;color:#64748b;font-weight:600;">Qty IN</th>
                    <th style="padding:12px 8px;text-align:center;font-size:0.9rem;color:#64748b;font-weight:600;">Qty OUT</th>
                    <th style="padding:12px 8px;text-align:left;font-size:0.9rem;color:#64748b;font-weight:600;">Note</th>
                    <th style="padding:12px 8px;text-align:left;font-size:0.9rem;color:#64748b;font-weight:600;">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($recent_transactions)): ?>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:12px 8px;font-weight:600;"><?php echo htmlspecialchars($row['part_number']); ?></td>
                    <td style="padding:12px 8px;text-align:center;color:#10b981;font-weight:600;">
                        <?php echo $row['qty_in'] > 0 ? '+' . $row['qty_in'] : '-'; ?>
                    </td>
                    <td style="padding:12px 8px;text-align:center;color:#ef4444;font-weight:600;">
                        <?php echo $row['qty_out'] > 0 ? '-' . $row['qty_out'] : '-'; ?>
                    </td>
                    <td style="padding:12px 8px;color:#475569;font-size:0.9rem;">
                        <?php echo htmlspecialchars(substr($row['note'], 0, 50)) . (strlen($row['note']) > 50 ? '...' : ''); ?>
                    </td>
                    <td style="padding:12px 8px;color:#64748b;font-size:0.85rem;">
                        <?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div style="margin-top:12px;text-align:right;">
            <a href="?menu=stock_transaction" style="color:#3b82f6;text-decoration:none;font-size:0.9rem;font-weight:600;">
                View All Transactions →
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.dashboard-card-link:hover > div {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(15,23,42,0.12) !important;
    border-color: #cbd5e1;
}
</style>
