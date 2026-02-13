<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../System/kon.php';

// Check if user is logged in
if (empty($_SESSION['logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'list':
        listStock();
        break;
    case 'summary':
        getSummary();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

// List stock for DataTable
function listStock() {
    global $kon;
    
    $columns = ['no', 'part_number', 'current_qty', 'status', 'last_updated'];
    
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 25;
    $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
    $orderColumnIndex = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
    $orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'ASC';
    if (!isset($columns[$orderColumnIndex])) {
        $orderColumnIndex = 0;
    }
    $orderColumn = $columns[$orderColumnIndex];
    
    // Filters
    $filterStockLevel = isset($_POST['filterStockLevel']) ? $_POST['filterStockLevel'] : '';
    
    // Base query
    $sql = "SELECT p.part_number, p.Qty AS current_qty, lm.last_updated
            FROM product p
            LEFT JOIN (
                SELECT part_number, MAX(created_at) AS last_updated
                FROM stock_movement
                GROUP BY part_number
            ) lm ON lm.part_number = p.part_number
            WHERE 1=1";
    
    if (!empty($filterStockLevel)) {
        switch ($filterStockLevel) {
            case 'out':
                $sql .= " AND p.Qty = 0";
                break;
            case 'low':
                $sql .= " AND p.Qty BETWEEN 1 AND 3";
                break;
            case 'warning':
                $sql .= " AND p.Qty BETWEEN 4 AND 7";
                break;
            case 'good':
                $sql .= " AND p.Qty > 7";
                break;
        }
    }
    
    // Search
    if (!empty($search)) {
        $sql .= " AND (p.part_number LIKE '%" . $kon->real_escape_string($search) . "%')";
    }
    
    // Total records
    $totalQuery = "SELECT COUNT(*) as total FROM product WHERE 1=1";
    $totalResult = $kon->query($totalQuery);
    $totalRecords = $totalResult->fetch_assoc()['total'];
    
    // Filtered records
    $filteredQuery = $sql;
    $filteredResult = $kon->query($filteredQuery);
    $filteredRecords = $filteredResult ? $filteredResult->num_rows : 0;
    
    // Order and limit
    if ($orderColumn === 'no') {
        $orderColumn = 'p.part_number';
    } elseif ($orderColumn === 'status') {
        $orderColumn = 'current_qty';
    }
    if ($orderColumn === 'last_updated') {
        $orderColumn = 'lm.last_updated';
    } elseif ($orderColumn === 'current_qty') {
        $orderColumn = 'p.Qty';
    } elseif ($orderColumn === 'part_number') {
        $orderColumn = 'p.part_number';
    }
    $sql .= " ORDER BY " . $orderColumn . " " . $orderDir;
    $sql .= " LIMIT " . $start . ", " . $length;
    
    $result = $kon->query($sql);
    
    $data = [];
    $no = $start + 1;
    while ($row = $result->fetch_assoc()) {
        $qty = (int)$row['current_qty'];
        $status = '';
        $qtyClass = '';
        
        if ($qty == 0) {
            $status = '<span class="badge bg-danger badge-stock">OUT OF STOCK</span>';
            $qtyClass = 'stock-low';
        } elseif ($qty <= 3) {
            $status = '<span class="badge bg-danger badge-stock">LOW STOCK</span>';
            $qtyClass = 'stock-low';
        } elseif ($qty <= 7) {
            $status = '<span class="badge bg-warning badge-stock">WARNING</span>';
            $qtyClass = 'stock-warning';
        } else {
            $status = '<span class="badge bg-success badge-stock">GOOD</span>';
            $qtyClass = 'stock-good';
        }
        
        $data[] = [
            'no' => $no++,
            'part_number' => $row['part_number'],
            'current_qty' => '<span class="' . $qtyClass . '">' . $qty . '</span>',
            'status' => $status,
            'last_updated' => $row['last_updated'] ? date('Y-m-d H:i', strtotime($row['last_updated'])) : '-'
        ];
    }
    
    $response = [
        'draw' => $draw,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $data
    ];
    
    echo json_encode($response);
}

// Get summary statistics
function getSummary() {
    global $kon;
    
    // Total items
    $sql = "SELECT COUNT(DISTINCT part_number) as total FROM product";
    $result = $kon->query($sql);
    $totalItems = $result->fetch_assoc()['total'];
    
    // Total stock
    $sql = "SELECT COALESCE(SUM(Qty), 0) as total FROM product";
    $result = $kon->query($sql);
    $totalStock = $result->fetch_assoc()['total'];
    
    // Low stock (1-7)
    $sql = "SELECT COUNT(*) as total FROM product WHERE Qty BETWEEN 1 AND 7";
    $result = $kon->query($sql);
    $lowStock = $result->fetch_assoc()['total'];
    
    // Out of stock
    $sql = "SELECT COUNT(*) as total FROM product WHERE Qty = 0";
    $result = $kon->query($sql);
    $outOfStock = $result->fetch_assoc()['total'];
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_items' => $totalItems,
            'total_stock' => $totalStock,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock
        ]
    ]);
}
?>
