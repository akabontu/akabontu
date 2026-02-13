<?php
if (!defined('IN_MENU_ADMIN')) {
    header('Location: ../../System/Index/login.php');
    exit;
}

global $kon;
$page_title = "Stock List";
?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --ink: #0f172a;
        --muted: #6b7280;
        --panel: #ffffff;
        --page: #f3f6fb;
        --accent: #1f6feb;
        --accent-2: #00a87a;
        --accent-3: #f6b100;
        --accent-4: #e93e5b;
        --table-head: #111827;
        --stroke: #e6e9f2;
        --shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
    }

    .summary-card {
        border: 0;
        border-radius: 16px;
        box-shadow: var(--shadow);
        position: relative;
        overflow: hidden;
    }
    .summary-card::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, rgba(255, 255, 255, 0.18), transparent 55%);
        pointer-events: none;
    }

    .card-header {
        background: #212222;
        color: white;
        border-radius: 14px 14px 0 0;
    }

    .card.shadow-sm {
        border: 1px solid var(--stroke);
        border-radius: 16px;
        box-shadow: var(--shadow);
    }

    .filter-bar {
        background: var(--panel);
        border: 1px solid var(--stroke);
        border-radius: 14px;
        padding: 0.85rem;
        display: flex;
        gap: 0.75rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .table thead th {
        background: #1d1e1f !important;
        color: #fff;
        border-bottom: 0;
    }

    /* Fixed-header table wrapper matching product layout */
    .stock-table-wrapper {
        display: block;
        height: 430px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background: #fff;
    }

    .stock-table-wrapper table {
       
        width: 100%;
        border-collapse: collapse;
    }

    .stock-table-wrapper thead {
        display: table;
        width: 100%;
        table-layout: fixed;
        position: sticky;
        top: 0;
        z-index: 10;
        background: #2563eb;
    }

    .stock-table-wrapper tbody {
        display: block;
        max-height: 300px;
        overflow-y: scroll;
        width: 100%;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .stock-table-wrapper tbody::-webkit-scrollbar {
        display: none;
    }

    .stock-table-wrapper tbody tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }

    .stock-table-wrapper thead th {
        background: #232324;
        color: white;
        padding: 12px 8px;
        font-size: 13px;
        font-weight: 600;
        text-align: center;
        border-bottom: 1px solid #1e40af;
    }

    .stock-table-wrapper tbody td {
        padding: 8px;
        font-size: 13px;
        border-bottom: 1px solid #e5e7eb;
        text-align: center;
    }

    .stock-table-wrapper tbody tr:hover {
        background: #f0f9ff;
    }

    .stock-table-wrapper tbody tr:nth-child(even) {
        background: #f9fafb;
    }

    .stock-table-wrapper tbody tr:nth-child(even):hover {
        background: #f0f9ff;
    }

    .table tbody tr {
        vertical-align: middle;
    }

    .stock-low {
        color: #e11d48;
        font-weight: 700;
    }
    .stock-warning {
        color: #f59e0b;
        font-weight: 700;
    }
    .stock-good {
        color: #16a34a;
        font-weight: 700;
    }
    .badge-stock {
        font-size: 0.85rem;
        padding: 6px 12px;
        letter-spacing: 0.3px;
        border-radius: 999px;
    }

    .btn-secondary {
        border-radius: 10px;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .stock-page {
            height: auto;
            padding: 1rem 0.9rem 1.5rem;
        }
        .filter-bar {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<div class="continer">
    <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white summary-card" style="background: linear-gradient(135deg, #1f6feb 0%, #434444 100%);">
                    <div class="card-body">
                        <h5 class="card-title">Total Items</h5>
                        <h2 id="totalItems">-</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white summary-card" style="background: linear-gradient(135deg, #00a87a 0%, #156fc3 100%);">
                    <div class="card-body">
                        <h5 class="card-title">Total Stock</h5>
                        <h2 id="totalStock">-</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white summary-card" style="background: linear-gradient(135deg, #f6b100 0%, #4dcdff 100%);">
                    <div class="card-body">
                        <h5 class="card-title">Low Stock</h5>
                        <h2 id="lowStock">-</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white summary-card" style="background: linear-gradient(135deg, #3d3c3c 0%, #ff6b6b 100%);">
                    <div class="card-body">
                        <h5 class="card-title">Out of Stock</h5>
                        <h2 id="outOfStock">-</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-boxes me-2"></i><?php echo $page_title; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Filter Section -->
                        <div class="filter-bar mb-3">
                            <div class="col-md-4 flex-grow-1">
                                <select class="form-select" id="filterStockLevel">
                                    <option value="">All Stock Levels</option>
                                    <option value="out">Out of Stock (0)</option>
                                    <option value="low">Low Stock (1-5)</option>
                                    <option value="warning">Warning (6-10)</option>
                                    <option value="good">Good (&gt;10)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-secondary" id="btnResetFilter">
                                    <i class="fas fa-redo me-2"></i>Reset Filter
                                </button>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="stock-table-wrapper">
                            <table id="tableStock" class="table">
                                <thead>
                                    <tr>
                                        <th style="width:60px;">No</th>
                                        <th style="width:200px;">Part Number</th>
                                        <th style="width:120px;">Current Stock</th>
                                        <th style="width:140px;">Status</th>
                                        <th style="width:160px;">Last Updated</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        var ajaxBaseUrl = '/adPanel/admin/Control/stock/stock_ajax.php';

        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#tableStock').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: ajaxBaseUrl,
                    type: 'POST',
                    data: function(d) {
                        d.action = 'list';
                        d.filterStockLevel = $('#filterStockLevel').val();
                    }
                },
                columns: [
                    { data: 'no' },
                    { data: 'part_number' },
                    { data: 'current_qty', className: 'text-center' },
                    { data: 'status' },
                    { data: 'last_updated' }
                ],
                order: [[4, 'asc']],
                pageLength: 25
            });

            // Filter change event
            $('#filterStockLevel').change(function() {
                table.ajax.reload();
            });

            // Reset filter
            $('#btnResetFilter').click(function() {
                $('#filterStockLevel').val('');
                table.ajax.reload();
            });

            // Load summary
            loadSummary();
        });

        function loadSummary() {
            $.ajax({
                url: ajaxBaseUrl,
                type: 'POST',
                data: { action: 'summary' },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#totalItems').text(response.data.total_items ?? 0);
                        $('#totalStock').text(response.data.total_stock ?? 0);
                        $('#lowStock').text(response.data.low_stock ?? 0);
                        $('#outOfStock').text(response.data.out_of_stock ?? 0);
                    }
                }
            });
        }

    </script>
