<?php
if (!defined('IN_MENU_ADMIN')) {
    header('Location: ../../System/Index/login.php');
    exit;
}

global $kon;
$page_title = "Stock Transaction";
?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --ink: #0f172a;
        --muted: #6b7280;
        --panel: #ffffff;
        --page: #f3f6fb;
        --accent: #667eea;
        --stroke: #e6e9f2;
        --shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
    }

    .row {
        margin: 0;
        padding: 0;
    }

    .col-12 {
        padding: 0;
    }

    .transaction-page {
        font-family: "Space Grotesk", sans-serif;
        background: #f5f6f8;
        height: 600px;
        width: 100%;
        overflow: hidden;
        padding: 0;
        margin: 0;
    }

    .card.shadow-sm {
        border: 1px solid var(--stroke);
        border-radius: 16px;
        box-shadow: var(--shadow);
        height: 100%;
        display: flex;
        flex-direction: column;
        margin: 0;
        padding: 0;
    }

    .card-header {
        background: linear-gradient(135deg, #302e2e 0%, #f16755 100%);
        color: white;
        border-radius: 14px 14px 0 0;
        padding: 1.25rem;
    }

    .card-body {
        flex: 1;
        overflow: hidden;
        display: flex;
        flex-direction: column;
       
    }

    .btn-add {
        background: linear-gradient(135deg, #302e2e 0%, #4ba25e 100%);
        border: none;
        color: white;
        border-radius: 10px;
        font-weight: 600;
        padding: 0.5rem 1.25rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .btn-add:hover {
        background: linear-gradient(135deg, #4ba25e 0%, #302e2e 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .filter-bar {
        background: var(--panel);
        border: 1px solid var(--stroke);
        border-radius: 14px;
        padding: 0.75rem;
        margin-bottom: 1rem;
        margin: 0 0 1rem 0;
    }

    .filter-bar .row {
        margin: 0 -4px;
    }

    .filter-bar [class*='col-'] {
        padding: 0 4px;
    }

    .btn-secondary {
        border-radius: 10px;
        font-weight: 600;
    }

    .badge-in {
        background-color: #28a745;
    }
    .badge-out {
        background-color: #dc3545;
    }
    .qty-in {
        color: #28a745;
        font-weight: bold;
    }
    .qty-out {
        color: #dc3545;
        font-weight: bold;
    }
    .current-stock {
        font-size: 1.2rem;
        font-weight: bold;
        color: #667eea;
    }
    .form-label {
        font-weight: 600;
    }

    /* Fixed-header table wrapper */
    .transaction-table-wrapper {
        display: block;
        height: 550px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background: #fff;
        flex: 1;
        position: relative;
        width: 100%;
        margin: 0;
        padding: 0;
    }

    .transaction-table-wrapper table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin: 0;
        padding: 0;
    }

    .transaction-table-wrapper thead {
        display: block;
        width: 100%;
        background: #212529;
        position: sticky;
        top: 0;
        z-index: 10;
        overflow: visible;
    }

    .transaction-table-wrapper tbody {
        display: block;
        max-height: 418px;
        overflow-y: scroll;
        width: 100%;
        scrollbar-width: thin;
        scrollbar-color: #888 #f1f1f1;
        scrollbar-gutter: stable;
    }

    .transaction-table-wrapper tbody::-webkit-scrollbar {
        width: 8px;
    }

    .transaction-table-wrapper tbody::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .transaction-table-wrapper tbody::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .transaction-table-wrapper thead tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }

    .transaction-table-wrapper tbody tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }

    .transaction-table-wrapper thead th {
        background: #212529;
        color: white;
        padding: 14px 8px;
        font-size: 12px;
        font-weight: 700;
        text-align: center;
        border-bottom: 2px solid #343a40;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .transaction-table-wrapper tbody td {
        padding: 12px 8px;
        font-size: 13px;
        border-bottom: 1px solid #e5e7eb;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
    }

    .transaction-table-wrapper tbody td[style*="text-align: left"] {
        text-align: left;
        padding-left: 12px;
    }

    .transaction-table-wrapper tbody tr:hover {
        background: #f0f9ff;
    }

    .transaction-table-wrapper tbody tr:nth-child(even) {
        background: #f9fafb;
    }

    .transaction-table-wrapper tbody tr:nth-child(even):hover {
        background: #f0f9ff;
    }

    
    .btn-action {
        padding: 8px 14px;
        border-radius: 8px;
        border: none;
        font-size: 12px;
        font-weight: 600;
        margin: 0 4px;
        transition: all 0.3s ease;
        cursor: pointer;
        white-space: nowrap;
    }

    .btn-action-edit {
        background: linear-gradient(135deg, #3be0f6 0%, #25c3eb 100%);
        color: white;
        min-width: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-action-edit:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
    }

    .btn-action-delete {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        min-width: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-action-delete:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
    }

    /* Modal fixes */
    #modalTransaction {
        position: fixed;
        z-index: 1055;
    }
    
    #modalTransaction .modal-dialog {
        position: relative;
        z-index: 1056;
    }

    @media (max-width: 768px) {
        .transaction-page {
            height: auto;
            padding: 1rem 0.9rem 1.5rem;
        }
    }
</style>
        <div class="row" style="height: 500px;">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-exchange-alt me-2"></i><?php echo $page_title; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Filter Section -->
                        <div class="filter-bar">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-2">
                                    <button type="button" class="btn-add w-100" data-bs-toggle="modal" data-bs-target="#modalTransaction">
                                        <i class="fas fa-plus me-2"></i>Add Transaction
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="filterType">
                                        <option value="">All Types</option>
                                        <option value="in">Stock IN</option>
                                        <option value="out">Stock OUT</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="date" class="form-control" id="filterDateFrom" placeholder="From Date">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" class="form-control" id="filterDateTo" placeholder="To Date">
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-secondary w-100" id="btnResetFilter">
                                        <i class="fas fa-redo me-2"></i>Reset Filter
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="transaction-table-wrapper">
                            <table id="tableTransaction" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th style="width:50px;text-align:center;">ID</th>
                                        <th style="width:120px;text-align:center;">Part Number</th>
                                        <th style="width:100px;text-align:center;">Location</th>
                                        <th style="width:80px;text-align:center;">Qty Before</th>
                                        <th style="width:70px;text-align:center;">Qty IN</th>
                                        <th style="width:70px;text-align:center;">Qty OUT</th>
                                        <th style="width:80px;text-align:center;">Qty After</th>
                                        <th style="width:80px;text-align:center;">Type</th>
                                        <th style="width:150px;text-align:center;">Note</th>
                                        <th style="width:120px;text-align:center;">Created At</th>
                                        <th style="width:100px;text-align:center;">Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<!-- Modal Add/Edit Transaction -->
<div class="modal fade" id="modalTransaction" tabindex="-1" aria-labelledby="modalTransactionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTransactionLabel">
                    <i class="fas fa-exchange-alt me-2"></i>Add New Transaction
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTransaction">
                <div class="modal-body">
                    <input type="hidden" id="transaction_id" name="transaction_id">
                    <input type="hidden" id="created_by" name="created_by" value="<?php echo htmlspecialchars($_SESSION['username'] ?? 'admin'); ?>">
                     
                    <div class="row">
                        <div class="col-md-6 mb-3">
                                <label for="part_number" class="form-label">Part Number <span class="text-danger">*</span></label>
                                <select class="form-select" id="part_number" name="part_number" required>
                                    <option value="">-- Select Part Number --</option>
                                </select>
                                <div class="mt-2">
                                    <small class="text-muted">Current Stock: </small>
                                    <span class="current-stock" id="currentStock">-</span>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="loc_stock" class="form-label">Location <span class="text-danger">*</span></label>
                                <select class="form-select" id="loc_stock" name="loc_stock" required>
                                    <option value="">-- Select Location --</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="kondisi" class="form-label">Condition <span class="text-danger">*</span></label>
                                <select class="form-select" id="kondisi" name="kondisi" required>
                                    <option value="">-- Select Condition --</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="transaction_type" class="form-label">Transaction Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="transaction_type" name="transaction_type" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="in">Stock IN (Receive from Supplier)</option>
                                    <option value="out">Stock OUT (Delivery to Customer)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3" id="qtyInGroup">
                                <label for="qty_in" class="form-label">Quantity IN</label>
                                <input type="number" class="form-control" id="qty_in" name="qty_in" value="0" min="0">
                            </div>

                            <div class="col-md-6 mb-3" id="qtyOutGroup">
                                <label for="qty_out" class="form-label">Quantity OUT</label>
                                <input type="number" class="form-control" id="qty_out" name="qty_out" value="0" min="0">
                            </div>
                        </div>

                        <!-- Supplier Field (shown for IN transactions) -->
                        <div class="mb-3" id="supplierGroup" style="display:none;">
                            <label for="supplier" class="form-label">Supplier <span class="text-danger">*</span></label>
                            <select class="form-select" id="supplier" name="supplier" style="width: 100%;">
                                <option value="">-- Select Supplier --</option>
                            </select>
                        </div>

                        <!-- Customer Field (shown for OUT transactions) -->
                        <div class="mb-3" id="customerGroup" style="display:none;">
                            <label for="customer" class="form-label">Customer <span class="text-danger">*</span></label>
                            <select class="form-select" id="customer" name="customer" style="width: 100%;">
                                <option value="">-- Select Customer --</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="note" class="form-label">Additional Note</label>
                            <textarea class="form-control" id="note" name="note" rows="2" placeholder="Optional additional information"></textarea>
                        </div>

                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> Quantity Before and Quantity After will be calculated automatically by the system.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnSubmit">
                            <i class="fas fa-save me-2"></i>Save Transaction
                        </button>
                    </div>
                </form>
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
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Base URL for AJAX requests (global variable)
    var ajaxBaseUrl = '/adPanel/admin/Control/stock/transaction_ajax.php';
    
    $(document).ready(function() {
        // Move modal to body to avoid z-index issues
        $('#modalTransaction').appendTo('body');
        
        // Initialize Select2
        $('#part_number').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalTransaction'),
                placeholder: '-- Select Part Number --',
                allowClear: true
            });

            // Initialize DataTable
            var table = $('#tableTransaction').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/adPanel/admin/Control/stock/transaction_ajax.php',
                    type: 'POST',
                    data: function(d) {
                        d.action = 'list';
                        d.filterType = $('#filterType').val();
                        d.filterDateFrom = $('#filterDateFrom').val();
                        d.filterDateTo = $('#filterDateTo').val();
                    }
                },
                columns: [
                    { data: 'id' },
                    { data: 'part_number' },
                    { data: 'loc_stock' },
                    { data: 'qty_before', className: 'text-center' },
                    { data: 'qty_in', className: 'text-center qty-in' },
                    { data: 'qty_out', className: 'text-center qty-out' },
                    { data: 'qty_after', className: 'text-center' },
                    { data: 'type' },
                    { data: 'note' },
                    { data: 'created_at' },
                    { data: 'action', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']],
                pageLength: 25,
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>'
                }
            });

            // Load part numbers for dropdown
            loadPartNumbers();

            // Load stock locations for dropdown
            loadLocations();

            // Load conditions for dropdown
            loadConditions();
            
            // Load suppliers and customers for dropdowns
            loadSuppliers();
            loadCustomers();

            // Filter change event
            $('#filterType, #filterDateFrom, #filterDateTo').change(function() {
                table.ajax.reload();
            });

            // Reset filter
            $('#btnResetFilter').click(function() {
                $('#filterType').val('');
                $('#filterDateFrom').val('');
                $('#filterDateTo').val('');
                table.ajax.reload();
            });

            // Transaction type change
            $('#transaction_type').change(function() {
                var type = $(this).val();
                if (type === 'in') {
                    $('#qty_in').val(0).prop('readonly', false);
                    $('#qty_out').val('').prop('readonly', true);
                    $('#qtyInGroup label').html('Quantity IN <span class="text-danger">*</span>');
                    $('#qtyOutGroup label').html('Quantity OUT');
                    // Show supplier, hide customer
                    $('#supplierGroup').show();
                    $('#customerGroup').hide();
                    $('#supplier').prop('required', true);
                    $('#customer').prop('required', false);
                } else if (type === 'out') {
                    $('#qty_in').val('').prop('readonly', true);
                    $('#qty_out').val(0).prop('readonly', false);
                    $('#qtyInGroup label').html('Quantity IN');
                    $('#qtyOutGroup label').html('Quantity OUT <span class="text-danger">*</span>');
                    // Show customer, hide supplier
                    $('#supplierGroup').hide();
                    $('#customerGroup').show();
                    $('#supplier').prop('required', false);
                    $('#customer').prop('required', true);
                } else {
                    // No type selected, hide both
                    $('#supplierGroup').hide();
                    $('#customerGroup').hide();
                    $('#supplier').prop('required', false);
                    $('#customer').prop('required', false);
                }
            });

            // Part number, location, or condition change - load current stock
            $('#part_number, #loc_stock, #kondisi').change(function() {
                loadCurrentStock();
            });

            // Form submit
            $('#formTransaction').submit(function(e) {
                e.preventDefault();
                
                // Build note field from supplier/customer selection
                var transactionType = $('#transaction_type').val();
                var additionalNote = $('#note').val().trim();
                var finalNote = additionalNote; // Start with additional note
                
                if (transactionType === 'in') {
                    var supplier = $('#supplier').val();
                    if (supplier) {
                        finalNote =supplier + (additionalNote ? ' | ' + additionalNote : '');
                    }
                } else if (transactionType === 'out') {
                    var customer = $('#customer').val();
                    if (customer) {
                        finalNote = customer + (additionalNote ? ' | ' + additionalNote : '');
                    }
                }
                
                // Build form data
                var formData = $(this).serialize();
                // Remove the original note field and add the built one
                formData = formData.replace(/note=[^&]*(&|$)/, '');
                formData += '&note=' + encodeURIComponent(finalNote);
                
                var action = $('#transaction_id').val() ? 'update' : 'create';
                formData += '&action=' + action;

                $.ajax({
                    url: ajaxBaseUrl,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    beforeSend: function() {
                        $('#btnSubmit').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i>Processing...');
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $('#modalTransaction').modal('hide');
                            table.ajax.reload();
                            $('#formTransaction')[0].reset();
                            $('#part_number').val('').trigger('change');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred: ' + xhr.responseText
                        });
                    },
                    complete: function() {
                        $('#btnSubmit').prop('disabled', false).html('<i class="fas fa-save me-2"></i>Save Transaction');
                    }
                });
            });

            // Reset form when modal is closed
            $('#modalTransaction').on('hidden.bs.modal', function() {
                $('#formTransaction')[0].reset();
                $('#transaction_id').val('');
                $('#part_number').val('').trigger('change');
                $('#supplier').val('').trigger('change');
                $('#customer').val('').trigger('change');
                $('#supplierGroup').hide();
                $('#customerGroup').hide();
                $('#currentStock').text('-');
                $('#modalTransactionLabel').html('<i class="fas fa-exchange-alt me-2"></i>Add New Transaction');
            });
        });

        // Load part numbers
        function loadPartNumbers() {
            $.ajax({
                url: ajaxBaseUrl,
                type: 'POST',
                data: { action: 'get_part_numbers' },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var options = '<option value="">-- Select Part Number --</option>';
                        $.each(response.data, function(index, item) {
                            options += '<option value="' + item.part_number + '">' + item.part_number + '</option>';
                        });
                        $('#part_number').html(options);
                    } else {
                        console.error('Failed to load part numbers:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading part numbers:', error);
                    console.error('Response:', xhr.responseText);
                }
            });
        }

        // Load stock locations
        function loadLocations() {
            $.ajax({
                url: ajaxBaseUrl,
                type: 'POST',
                data: { action: 'get_locations' },
                dataType: 'json',
                success: function(response) {
                    var options = '<option value="">-- Select Location --</option>';

                    if (response.success && response.data && response.data.length > 0) {
                        $.each(response.data, function(index, item) {
                            if (item.loc_stock) {
                                options += '<option value="' + item.loc_stock + '">' + item.loc_stock + '</option>';
                            }
                        });
                    }

                    if (options === '<option value="">-- Select Location --</option>') {
                        options += '<option value="MAIN">MAIN</option>';
                    }

                    $('#loc_stock').html(options);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading locations:', error);
                    $('#loc_stock').html('<option value="MAIN">MAIN</option>');
                }
            });
        }

        // Load conditions
        function loadConditions() {
            $.ajax({
                url: ajaxBaseUrl,
                type: 'POST',
                data: { action: 'get_conditions' },
                dataType: 'json',
                success: function(response) {
                    var options = '<option value="">-- Select Condition --</option>';

                    if (response.success && response.data && response.data.length > 0) {
                        $.each(response.data, function(index, item) {
                            if (item.kondisi) {
                                options += '<option value="' + item.kondisi + '">' + item.kondisi + '</option>';
                            }
                        });
                    }

                    if (options === '<option value="">-- Select Condition --</option>') {
                        options += '<option value="Good">Good</option>';
                    }

                    $('#kondisi').html(options);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading conditions:', error);
                    $('#kondisi').html('<option value="Good">Good</option>');
                }
            });
        }

        // Load current stock
        function loadCurrentStock() {
            var part_number = $('#part_number').val();
            var loc_stock = $('#loc_stock').val();
            var kondisi = $('#kondisi').val();

            if (part_number) {
                $.ajax({
                    url: ajaxBaseUrl,
                    type: 'POST',
                    data: {
                        action: 'get_current_stock',
                        part_number: part_number,
                        loc_stock: loc_stock,
                        kondisi: kondisi
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#currentStock').text(response.data.current_qty + ' units');
                        } else {
                            $('#currentStock').text('0 units (New Item)');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading current stock:', error);
                        $('#currentStock').text('0 units (New Item)');
                    }
                });
            } else {
                $('#currentStock').text('-');
            }
        }

        // Load suppliers
        function loadSuppliers() {
            $.ajax({
                url: ajaxBaseUrl,
                type: 'POST',
                data: { action: 'get_suppliers' },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var options = '<option value="">-- Select Supplier --</option>';
                        $.each(response.data, function(index, item) {
                            options += '<option value="' + item.id + '">' + item.text + '</option>';
                        });
                        $('#supplier').html(options);
                        
                        // Initialize Select2 for supplier
                        $('#supplier').select2({
                            theme: 'bootstrap-5',
                            dropdownParent: $('#modalTransaction'),
                            placeholder: '-- Select Supplier --',
                            allowClear: true
                        });
                    } else {
                        console.error('Failed to load suppliers:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading suppliers:', error);
                }
            });
        }

        // Load customers
        function loadCustomers() {
            $.ajax({
                url: ajaxBaseUrl,
                type: 'POST',
                data: { action: 'get_customers' },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var options = '<option value="">-- Select Customer --</option>';
                        $.each(response.data, function(index, item) {
                            options += '<option value="' + item.id + '">' + item.text + '</option>';
                        });
                        $('#customer').html(options);
                        
                        // Initialize Select2 for customer
                        $('#customer').select2({
                            theme: 'bootstrap-5',
                            dropdownParent: $('#modalTransaction'),
                            placeholder: '-- Select Customer --',
                            allowClear: true
                        });
                    } else {
                        console.error('Failed to load customers:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading customers:', error);
                }
            });
        }

        // Edit transaction
        function editTransaction(id) {
            $.ajax({
                url: ajaxBaseUrl,
                type: 'POST',
                data: { action: 'get_detail', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var data = response.data;
                        $('#transaction_id').val(data.id);
                        $('#part_number').val(data.part_number).trigger('change');
                        $('#loc_stock').val(data.loc_stock);
                        $('#kondisi').val(data.kondisi);
                        $('#qty_in').val(data.qty_in);
                        $('#qty_out').val(data.qty_out);
                        $('#note').val(data.note);
                        
                        if (data.qty_in > 0) {
                            $('#transaction_type').val('in').trigger('change');
                        } else {
                            $('#transaction_type').val('out').trigger('change');
                        }
                        
                        $('#modalTransactionLabel').html('<i class="fas fa-edit me-2"></i>Edit Transaction');
                        $('#modalTransaction').modal('show');
                    }
                }
            });
        }

        // Delete transaction
        function deleteTransaction(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Stock will be adjusted automatically!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: ajaxBaseUrl,
                        type: 'POST',
                        data: { action: 'delete', id: id },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                $('#tableTransaction').DataTable().ajax.reload();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: response.message
                                });
                            }
                        }
                    });
                }
            });
        }
</script>
