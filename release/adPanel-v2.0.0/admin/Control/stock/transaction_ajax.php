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
        listTransactions();
        break;
    case 'create':
        createTransaction();
        break;
    case 'update':
        updateTransaction();
        break;
    case 'delete':
        deleteTransaction();
        break;
    case 'get_detail':
        getTransactionDetail();
        break;
    case 'get_part_numbers':
        getPartNumbers();
        break;
    case 'get_locations':
        getLocations();
        break;
    case 'get_conditions':
        getConditions();
        break;
    case 'get_current_stock':
        getCurrentStock();
        break;
    case 'get_suppliers':
        getSuppliers();
        break;
    case 'get_customers':
        getCustomers();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

// List transactions for DataTable
function listTransactions() {
    global $kon;
    
    $columns = ['id', 'part_number', 'loc_stock', 'qty_before', 'qty_in', 'qty_out', 'qty_after', 'type', 'note', 'created_at'];
    
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 25;
    $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
    $orderColumnIndex = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
    $orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'DESC';
    
    // Validate column index
    if (!isset($columns[$orderColumnIndex])) {
        $orderColumnIndex = 0;
    }
    $orderColumn = $columns[$orderColumnIndex];
    
    // Filters
    $filterType = isset($_POST['filterType']) ? $_POST['filterType'] : '';
    $filterDateFrom = isset($_POST['filterDateFrom']) ? $_POST['filterDateFrom'] : '';
    $filterDateTo = isset($_POST['filterDateTo']) ? $_POST['filterDateTo'] : '';
    
    // Base query
    $sql = "SELECT * FROM stock_movement WHERE 1=1";
    
    // Apply filters
    if (!empty($filterType)) {
        if ($filterType == 'in') {
            $sql .= " AND qty_in > 0";
        } elseif ($filterType == 'out') {
            $sql .= " AND qty_out > 0";
        }
    }
    
    if (!empty($filterDateFrom)) {
        $sql .= " AND DATE(created_at) >= '" . $kon->real_escape_string($filterDateFrom) . "'";
    }
    
    if (!empty($filterDateTo)) {
        $sql .= " AND DATE(created_at) <= '" . $kon->real_escape_string($filterDateTo) . "'";
    }
    
    // Search
    if (!empty($search)) {
        $sql .= " AND (part_number LIKE '%" . $kon->real_escape_string($search) . "%' 
                  OR note LIKE '%" . $kon->real_escape_string($search) . "%'
                  OR loc_stock LIKE '%" . $kon->real_escape_string($search) . "%')";
    }
    
    // Total records
    $totalQuery = "SELECT COUNT(*) as total FROM stock_movement WHERE 1=1";
    if (!empty($search)) {
        $totalQuery .= " AND (part_number LIKE '%" . $kon->real_escape_string($search) . "%' 
                        OR note LIKE '%" . $kon->real_escape_string($search) . "%')";
    }
    $totalResult = $kon->query($totalQuery);
    $totalRecords = $totalResult->fetch_assoc()['total'];
    
    // Filtered records
    $filteredQuery = $sql;
    $filteredResult = $kon->query($filteredQuery);
    $filteredRecords = $filteredResult->num_rows;
    
    // Order and limit
    // If ordering by 'type', order by id instead (type is calculated)
    if ($orderColumn == 'type') {
        $orderColumn = 'id';
    }
    $sql .= " ORDER BY " . $orderColumn . " " . $orderDir;
    $sql .= " LIMIT " . $start . ", " . $length;
    
    $result = $kon->query($sql);
    
    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $type = '';
            if ($row['qty_in'] > 0 && $row['qty_out'] == 0) {
                $type = '<span class="badge badge-in">Stock IN</span>';
            } elseif ($row['qty_out'] > 0 && $row['qty_in'] == 0) {
                $type = '<span class="badge badge-out">Stock OUT</span>';
            } else {
                $type = '<span class="badge bg-warning">Adjustment</span>';
            }
            
            $action = '
                <button class="btn-action btn-action-edit" onclick="editTransaction(' . $row['id'] . ')" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-action btn-action-delete" onclick="deleteTransaction(' . $row['id'] . ')" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            ';
            
            $data[] = [
                'id' => $row['id'],
                'part_number' => $row['part_number'],
                'loc_stock' => $row['loc_stock'] ? $row['loc_stock'] : 'MAIN',
                'qty_before' => $row['qty_before'],
                'qty_in' => $row['qty_in'] > 0 ? '+' . $row['qty_in'] : $row['qty_in'],
                'qty_out' => $row['qty_out'] > 0 ? '-' . $row['qty_out'] : $row['qty_out'],
                'qty_after' => $row['qty_after'],
                'type' => $type,
                'note' => substr($row['note'], 0, 50) . (strlen($row['note']) > 50 ? '...' : ''),
                'created_at' => date('Y-m-d H:i:s', strtotime($row['created_at'])),
                'action' => $action
            ];
        }
    }
    
    $response = [
        'draw' => $draw,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $data
    ];
    
    echo json_encode($response);
}

// Create transaction
function createTransaction() {
    global $kon;
    
    $part_number = $kon->real_escape_string($_POST['part_number']);
    $qty_in = intval($_POST['qty_in']);
    $qty_out = intval($_POST['qty_out']);
    $note = $kon->real_escape_string($_POST['note']);
    $loc_stock = $kon->real_escape_string($_POST['loc_stock']);
    $kondisi = $kon->real_escape_string($_POST['kondisi']);
    $created_by = $kon->real_escape_string($_SESSION['username'] ?? 'admin');
    
    // Validation
    if (empty($part_number) || empty($note)) {
        echo json_encode(['success' => false, 'message' => 'Part number and note are required']);
        return;
    }
    
    if ($qty_in == 0 && $qty_out == 0) {
        echo json_encode(['success' => false, 'message' => 'Quantity IN or OUT must be greater than 0']);
        return;
    }
    
    // Insert transaction (trigger will handle stock update)
    $sql = "INSERT INTO stock_movement (part_number, qty_in, qty_out, note, loc_stock, kondisi, created_by) 
            VALUES ('$part_number', $qty_in, $qty_out, '$note', '$loc_stock', '$kondisi', '$created_by')";
    
    if ($kon->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Transaction created successfully']);
    } else {
        // Check if error is from trigger validation
        $error = $kon->error;
        if (strpos($error, 'Stock tidak mencukupi') !== false) {
            echo json_encode(['success' => false, 'message' => 'Insufficient stock for this transaction']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $error]);
        }
    }
}

// Update transaction
function updateTransaction() {
    global $kon;
    
    $id = intval($_POST['transaction_id']);
    $part_number = $kon->real_escape_string($_POST['part_number']);
    $qty_in = intval($_POST['qty_in']);
    $qty_out = intval($_POST['qty_out']);
    $note = $kon->real_escape_string($_POST['note']);
    $loc_stock = $kon->real_escape_string($_POST['loc_stock']);
    $kondisi = $kon->real_escape_string($_POST['kondisi']);
    
    // Validation
    if (empty($part_number) || empty($note)) {
        echo json_encode(['success' => false, 'message' => 'Part number and note are required']);
        return;
    }
    
    if ($qty_in == 0 && $qty_out == 0) {
        echo json_encode(['success' => false, 'message' => 'Quantity IN or OUT must be greater than 0']);
        return;
    }
    
    // Update transaction (trigger will handle stock adjustment)
    $sql = "UPDATE stock_movement 
            SET part_number = '$part_number', 
                qty_in = $qty_in, 
                qty_out = $qty_out, 
                note = '$note',
                loc_stock = '$loc_stock',
                kondisi = '$kondisi'
            WHERE id = $id";
    
    if ($kon->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Transaction updated successfully']);
    } else {
        $error = $kon->error;
        if (strpos($error, 'tidak boleh negatif') !== false) {
            echo json_encode(['success' => false, 'message' => 'Update failed: Stock would become negative']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $error]);
        }
    }
}

// Delete transaction
function deleteTransaction() {
    global $kon;
    
    $id = intval($_POST['id']);
    
    // Delete transaction (trigger will restore stock)
    $sql = "DELETE FROM stock_movement WHERE id = $id";
    
    if ($kon->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Transaction deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $kon->error]);
    }
}

// Get transaction detail
function getTransactionDetail() {
    global $kon;
    
    $id = intval($_POST['id']);
    
    $sql = "SELECT * FROM stock_movement WHERE id = $id";
    $result = $kon->query($sql);
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Transaction not found']);
    }
}

// Get part numbers
function getPartNumbers() {
    global $kon;
    
    $sql = "SELECT DISTINCT part_number FROM product ORDER BY part_number";
    $result = $kon->query($sql);
    
    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    echo json_encode(['success' => true, 'data' => $data]);
}

// Get stock locations
function getLocations() {
    global $kon;

    $sql = "SELECT DISTINCT loc_stock FROM product WHERE loc_stock IS NOT NULL AND loc_stock != '' ORDER BY loc_stock ASC";
    $result = $kon->query($sql);

    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    if (empty($data)) {
        $data[] = ['loc_stock' => 'Warehouse'];
    }

    echo json_encode(['success' => true, 'data' => $data]);
}

// Get product conditions
function getConditions() {
    global $kon;

    $sql = "SELECT DISTINCT kondisi FROM product WHERE kondisi IS NOT NULL AND kondisi != '' ORDER BY kondisi ASC";
    $result = $kon->query($sql);

    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    if (empty($data)) {
        $data[] = ['kondisi' => 'New'];
    }

    echo json_encode(['success' => true, 'data' => $data]);
}

// Get current stock
function getCurrentStock() {
    global $kon;
    
    $part_number = $kon->real_escape_string($_POST['part_number']);
    $loc_stock = $kon->real_escape_string($_POST['loc_stock']);
    $kondisi = $kon->real_escape_string($_POST['kondisi']);
    
    $sql = "SELECT current_qty FROM stock_location 
            WHERE part_number = '$part_number' 
              AND loc_stock = '$loc_stock' 
              AND kondisi = '$kondisi'";
    
    $result = $kon->query($sql);
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Stock not found']);
    }
}

// Get suppliers
function getSuppliers() {
    global $kon;
    
    $sql = "SELECT nama FROM supplier ORDER BY nama ASC";
    $result = $kon->query($sql);
    
    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = ['id' => $row['nama'], 'text' => $row['nama']];
        }
    }
    
    echo json_encode(['success' => true, 'data' => $data]);
}

// Get customers
function getCustomers() {
    global $kon;
    
    $sql = "SELECT nama FROM customer ORDER BY nama ASC";
    $result = $kon->query($sql);
    
    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = ['id' => $row['nama'], 'text' => $row['nama']];
        }
    }
    
    echo json_encode(['success' => true, 'data' => $data]);
}
?>
