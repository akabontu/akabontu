<?php
if (!defined('IN_MENU_ADMIN')) define('IN_MENU_ADMIN', false);
require_once __DIR__ . '/../../../System/kon.php';

$trxErrors = [];
$trxSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_trx'])) {
	$type = trim($_POST['trx_type'] ?? '');
	$partNumber = trim($_POST['part_number'] ?? '');
	$stockMasuk = (int)($_POST['stock_masuk'] ?? 0);
	$stockKeluar = (int)($_POST['stock_keluar'] ?? 0);
	$note = trim($_POST['keterangan'] ?? '');
	$supplier = trim($_POST['supplier'] ?? '');
	$customer = trim($_POST['customer'] ?? '');

	if ($partNumber === '') {
		$trxErrors[] = 'Part number harus diisi.';
	}
	if ($type !== 'in' && $type !== 'out') {
		$trxErrors[] = 'Tipe transaksi tidak valid.';
	}
	if ($type === 'in' && $stockMasuk <= 0) {
		$trxErrors[] = 'Stock masuk harus lebih dari 0.';
	}
	if ($type === 'out' && $stockKeluar <= 0) {
		$trxErrors[] = 'Stock keluar harus lebih dari 0.';
	}

	// Validasi Supplier untuk transaksi 'in'
	if ($type === 'in') {
		if ($supplier === '') {
			$trxErrors[] = 'Supplier harus diisi untuk transaksi masuk.';
		} else {
			// Cek apakah supplier ada di database
			$stmt = mysqli_prepare($kon, "SELECT nama FROM supplier WHERE nama = ? LIMIT 1");
			if ($stmt) {
				mysqli_stmt_bind_param($stmt, 's', $supplier);
				mysqli_stmt_execute($stmt);
				mysqli_stmt_store_result($stmt);
				if (mysqli_stmt_num_rows($stmt) === 0) {
					$trxErrors[] = 'Supplier "' . htmlspecialchars($supplier) . '" belum ada di database. Silakan tambahkan terlebih dahulu dengan klik tombol "add".';
				}
				mysqli_stmt_close($stmt);
			}
		}
	}

	// Validasi Customer untuk transaksi 'out'
	if ($type === 'out') {
		if ($customer === '') {
			$trxErrors[] = 'Customer harus diisi untuk transaksi keluar.';
		} else {
			// Cek apakah customer ada di database
			$stmt = mysqli_prepare($kon, "SELECT nama FROM customer WHERE nama = ? LIMIT 1");
			if ($stmt) {
				mysqli_stmt_bind_param($stmt, 's', $customer);
				mysqli_stmt_execute($stmt);
				mysqli_stmt_store_result($stmt);
				if (mysqli_stmt_num_rows($stmt) === 0) {
					$trxErrors[] = 'Customer "' . htmlspecialchars($customer) . '" belum ada di database. Silakan tambahkan terlebih dahulu dengan klik tombol "add".';
				}
				mysqli_stmt_close($stmt);
			}
		}
	}

	if (empty($trxErrors)) {
		$transactionStarted = false;
		try {
			if (!mysqli_begin_transaction($kon)) {
				throw new RuntimeException('Gagal memulai transaksi database.');
			}
			$transactionStarted = true;

			$stmt = mysqli_prepare($kon, "SELECT Qty FROM Product WHERE part_number = ? LIMIT 1 FOR UPDATE");
			if (!$stmt) {
				throw new RuntimeException('Prepare Product gagal: ' . mysqli_error($kon));
			}
			mysqli_stmt_bind_param($stmt, 's', $partNumber);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_bind_result($stmt, $currentQty);
			if (!mysqli_stmt_fetch($stmt)) {
				mysqli_stmt_close($stmt);
				throw new RuntimeException('Part number tidak ditemukan.');
			}
			mysqli_stmt_close($stmt);

			$qtyBefore = (int)$currentQty;
			$qtyIn = $type === 'in' ? $stockMasuk : 0;
			$qtyOut = $type === 'out' ? $stockKeluar : 0;
			$qtyAfter = $qtyBefore + $qtyIn - $qtyOut;
			if ($qtyAfter < 0) {
				throw new RuntimeException('Stock keluar melebihi stock yang tersedia.');
			}

			// Trigger database (after_stock_movement_insert) akan otomatis update Product.Qty
			// Jadi kita langsung INSERT ke stock_movement, tidak perlu UPDATE Product manual
			$insert = mysqli_prepare($kon, "INSERT INTO stock_movement (part_number, qty_before, qty_in, qty_out, qty_after, note, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
			if (!$insert) {
				throw new RuntimeException('Prepare stock_movement gagal: ' . mysqli_error($kon));
			}
			mysqli_stmt_bind_param($insert, 'siiiis', $partNumber, $qtyBefore, $qtyIn, $qtyOut, $qtyAfter, $note);
			if (!mysqli_stmt_execute($insert)) {
				$err = mysqli_stmt_error($insert);
				mysqli_stmt_close($insert);
				throw new RuntimeException('Gagal menyimpan transaksi: ' . $err);
			}
			mysqli_stmt_close($insert);

			if (!mysqli_commit($kon)) {
				throw new RuntimeException('Gagal mengesahkan transaksi.');
			}
			$transactionStarted = false;
			$trxSuccess = 'Transaksi berhasil disimpan!';
		} catch (Throwable $e) {
			if ($transactionStarted) {
				mysqli_rollback($kon);
			}
			$trxErrors[] = $e->getMessage();
		}
	}
}
?>

<div class="card" style="margin-bottom:16px;height:750px;overflow:hidden;">
	<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
		<div>
			<h2 style="margin:0;margin-bottom:8px;">Transaksi</h2>	
		</div>
	</div>

	<?php if (!empty($trxErrors)): ?>
		<div id="error-msg" class="card" style="position:fixed;top:20px;right:20px;z-index:9999;border-left:4px solid #e74c3c;background:#fff;box-shadow:0 6px 18px rgba(15,23,42,0.18);width:320px;">
			<?php foreach ($trxErrors as $err): ?>
				<div style="color:#8a1f1f;padding:10px 12px;"><?php echo htmlspecialchars($err); ?></div>
			<?php endforeach; ?>
		</div>
		<script>
			document.addEventListener('DOMContentLoaded', function(){
				var errorMsg = document.getElementById('error-msg');
				if (errorMsg) {
					setTimeout(function() {
						errorMsg.style.display = 'none';
					}, 5000);
				}
			});
		</script>
	<?php elseif ($trxSuccess !== ''): ?>
		<div id="success-msg" class="card" style="position:fixed;top:20px;right:20px;z-index:9999;border-left:4px solid #27ae60;background:#fff;box-shadow:0 6px 18px rgba(15,23,42,0.18);width:380px;">
			<div style="color:#155724;padding:10px 12px;margin-bottom:10px;"><?php echo htmlspecialchars($trxSuccess); ?></div>
			<div style="display:flex;gap:8px;padding:0 12px 12px;">
				<button onclick="document.getElementById('btnContinue').click()" style="flex:1;padding:8px;background:#27ae60;color:white;border:none;border-radius:4px;cursor:pointer;font-weight:bold;">Transaksi Lagi</button>
				<button onclick="window.location.href='?menu=transaction';" style="flex:1;padding:8px;background:#95a5a6;color:white;border:none;border-radius:4px;cursor:pointer;font-weight:bold;">Selesai</button>
			</div>
		</div>
	<?php endif; ?>
	<form method="post">
		<div class="form-row">
			<select id="trx_type" name="trx_type" class="input">
				<option value="in">Input</option>
				<option value="out">Output</option>
            </select>
		</div>

		<div class="form-row">
			<label for="part_number">Part Number</label>
			<input id="part_number" name="part_number" class="input" type="text" placeholder="Cari Part Number" list="part_number_list" autocomplete="off">
			<datalist id="part_number_list">
				<?php
				$pnRows = mysqli_query($kon, "SELECT part_number FROM Product ORDER BY part_number ASC");
				if ($pnRows):
					while ($pn = mysqli_fetch_assoc($pnRows)):
				?>
					<option value="<?php echo htmlspecialchars($pn['part_number']); ?>"></option>
				<?php
					endwhile;
				endif;
				?>
			</datalist>
		</div>

		<div class="row-two">
			<div class="col">
				<div class="field-inline">
					<label for="stock_awal">Stock Awal</label>
					<input id="stock_awal" name="stock_awal" class="input" type="number" min="0" value="0">
				</div>
			</div>
			<div class="col">
				<div class="field-inline">
					<label for="stock_masuk">Stock Masuk</label>
					<input id="stock_masuk" name="stock_masuk" class="input" type="number" min="0" value="0">
				</div>
			</div>
			<div class="col">
				<div class="field-inline">
					<label for="stock_keluar">Stock Keluar</label>
					<input id="stock_keluar" name="stock_keluar" class="input" type="number" min="0" value="0">
				</div>
			</div>
			<div class="col">
				<div class="field-inline">
					<label for="stock_akhir">Stock Akhir</label>
					<input id="stock_akhir" class="input" type="number" min="0" value="0" readonly style="background:#f0f0f0;cursor:not-allowed;">
				</div>
			</div>
		</div>

		<div class="row-two">
			<div class="col">
				<div class="field-inline">
					<label for="supplier">Supplier</label>
					<div style="display:flex;gap:8px;align-items:flex-start;flex-wrap:wrap;position:relative;">
						<div style="flex:1;min-width:150px;position:relative;">
							<input id="supplier" name="supplier" class="input" type="text" placeholder="Cari Supplier" list="supplier_list" autocomplete="off">
							<span id="supplier-warning" style="position:absolute;right:8px;top:8px;color:#e74c3c;font-weight:bold;font-size:13px;display:none;background:#fff;padding:0 4px;">⚠ Belum ada</span>
						</div>
						<datalist id="supplier_list">
							<?php
							$supplierRows = mysqli_query($kon, "SELECT nama FROM supplier ORDER BY nama ASC");
							if ($supplierRows):
								while ($sup = mysqli_fetch_assoc($supplierRows)):
							?>
								<option value="<?php echo htmlspecialchars($sup['nama']); ?>"></option>
							<?php
								endwhile;
							endif;
							?>
						</datalist>
						<button id="btnAddSupplier" class="btn btn-import" type="button" disabled>add</button>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="field-inline">
					<label for="customer">Customer</label>
					<div style="display:flex;gap:8px;align-items:flex-start;flex-wrap:wrap;position:relative;">
						<div style="flex:1;min-width:150px;position:relative;">
							<input id="customer" name="customer" class="input" type="text" placeholder="Cari Customer" list="customer_list" autocomplete="off">
							<span id="customer-warning" style="position:absolute;right:8px;top:8px;color:#e74c3c;font-weight:bold;font-size:13px;display:none;background:#fff;padding:0 4px;">⚠ Belum ada</span>
						</div>
						<datalist id="customer_list">
							<?php
							$customerRows = mysqli_query($kon, "SELECT nama FROM customer ORDER BY nama ASC");
							if ($customerRows):
								while ($cust = mysqli_fetch_assoc($customerRows)):
							?>
								<option value="<?php echo htmlspecialchars($cust['nama']); ?>"></option>
							<?php
								endwhile;
							endif;
							?>
						</datalist>
						<button id="btnAddCustomer" class="btn btn-import" type="button" disabled>add</button>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="field-inline">
					<label for="keterangan">Keterangan</label>
					<input id="keterangan" name="keterangan" class="input" type="text" >
				</div>
			</div>
		</div>

		<div class="form-actions">
			<button id="btnSubmitTrx" class="btn btn-add" type="submit" name="submit_trx" value="1">Simpan Transaksi</button>
			<button id="btnContinue" class="btn" type="reset" style="display:none;">Reset Form</button>
			<a class="btn" href="?menu=product">Kembali</a>
		</div>
	</form>
	

<script>
(function(){
	var typeSelect = document.getElementById('trx_type');
	var stockAwal = document.getElementById('stock_awal');
	var stockMasuk = document.getElementById('stock_masuk');
	var stockKeluar = document.getElementById('stock_keluar');
	var supplier = document.getElementById('supplier');
	var customer = document.getElementById('customer');
	var btnAddSupplier = document.getElementById('btnAddSupplier');
	var btnAddCustomer = document.getElementById('btnAddCustomer');
	var noteInput = document.getElementById('keterangan');
	var btnSubmitTrx = document.getElementById('btnSubmitTrx');

	if (!typeSelect) return;

	function setDisabled(el, disabled){
		if (!el) return;
		el.disabled = disabled;
		// Clear nilai hanya untuk stock fields
		if (disabled && el.id && (el.id === 'stock_masuk' || el.id === 'stock_keluar')) {
			el.value = '';
		}
	}

	function disableFieldWithoutClearing(el, disabled){
		if (!el) return;
		el.disabled = disabled;
		// Jangan clear supplier/customer saat disabled
	}

	function applyState(){
		var val = typeSelect.value;
		if (stockAwal) {
			stockAwal.readOnly = true; // keep visible but not editable
			stockAwal.disabled = false;
		}
		if (val === 'in') {
			setDisabled(stockKeluar, true);
			disableFieldWithoutClearing(customer, true);
			setDisabled(stockMasuk, false);
			disableFieldWithoutClearing(supplier, false);
		} else if (val === 'out') {
			setDisabled(stockMasuk, true);
			disableFieldWithoutClearing(supplier, true);
			setDisabled(stockKeluar, false);
			disableFieldWithoutClearing(customer, false);
		} else {
			setDisabled(stockMasuk, false);
			setDisabled(stockKeluar, false);
			disableFieldWithoutClearing(supplier, false);
			disableFieldWithoutClearing(customer, false);
		}
		updateNoteFromParty();
		updateAddButtons();
		updateSubmitButtonState();
		calculateStockAkhir();
	}
	function getDatalistValues(listId){
		var list = document.getElementById(listId);
		if (!list) return [];
		return Array.prototype.slice.call(list.options || []).map(function(o){
			return (o.value || '').toLowerCase();
		});
	}

	var supplierValues = getDatalistValues('supplier_list');
	var customerValues = getDatalistValues('customer_list');

	function updateAddButtonState(input, values, button){
		if (!input || !button) return;
		if (input.disabled) {
			button.disabled = true;
			return;
		}
		var val = (input.value || '').trim().toLowerCase();
		if (val === '') {
			button.disabled = true;
			return;
		}
		button.disabled = values.indexOf(val) !== -1;
	}

	function updateAddButtons(){
		updateAddButtonState(supplier, supplierValues, btnAddSupplier);
		updateAddButtonState(customer, customerValues, btnAddCustomer);
		updateSubmitButtonState();
	}

	function updateSubmitButtonState(){
		if (!btnSubmitTrx) return;
		var val = typeSelect.value;
		var canSubmit = true;

		if (val === 'in') {
			// Untuk input: supplier harus ada di database
			var supplierVal = (supplier && supplier.value || '').trim();
			if (supplierVal === '') {
				canSubmit = false;
			} else if (supplierValues.indexOf(supplierVal.toLowerCase()) === -1) {
				// Supplier tidak ada di database
				canSubmit = false;
			}
		} else if (val === 'out') {
			// Untuk output: customer harus ada di database
			var customerVal = (customer && customer.value || '').trim();
			if (customerVal === '') {
				canSubmit = false;
			} else if (customerValues.indexOf(customerVal.toLowerCase()) === -1) {
				// Customer tidak ada di database
				canSubmit = false;
			}
		}

		btnSubmitTrx.disabled = !canSubmit;
		if (!canSubmit) {
			btnSubmitTrx.title = val === 'in' ? 'Supplier harus ada di database' : 'Customer harus ada di database';
		} else {
			btnSubmitTrx.title = '';
		}
	}

	if (supplier) supplier.addEventListener('input', function(){
		updateNoteFromParty();
		updateAddButtons();
		updateSubmitButtonState();
		updateWarnings();
	});
	if (customer) customer.addEventListener('input', function(){
		updateNoteFromParty();
		updateAddButtons();
		updateSubmitButtonState();
		updateWarnings();
	});

	function updateWarnings(){
		var supplierWarning = document.getElementById('supplier-warning');
		var customerWarning = document.getElementById('customer-warning');
		
		if (supplierWarning && supplier) {
			var val = (supplier.value || '').trim().toLowerCase();
			if (val !== '' && supplierValues.indexOf(val) === -1) {
				supplierWarning.style.display = 'inline';
			} else {
				supplierWarning.style.display = 'none';
			}
		}
		
		if (customerWarning && customer) {
			var val = (customer.value || '').trim().toLowerCase();
			if (val !== '' && customerValues.indexOf(val) === -1) {
				customerWarning.style.display = 'inline';
			} else {
				customerWarning.style.display = 'none';
			}
		}
	}

	if (btnAddSupplier) {
		btnAddSupplier.addEventListener('click', function(){
			if (btnAddSupplier.disabled) return;
			// Simpan form state sebelum navigate
			saveFormState();
			window.location.href = '?menu=supplier';
		});
	}
	if (btnAddCustomer) {
		btnAddCustomer.addEventListener('click', function(){
			if (btnAddCustomer.disabled) return;
			// Simpan form state sebelum navigate
			saveFormState();
			window.location.href = '?menu=customer';
		});
	}

	if (supplier) {
		supplier.addEventListener('keydown', function(e){
			if (e.key === 'Enter') {
				var val = (supplier.value || '').trim().toLowerCase();
				// Hanya submit jika data ada di database
				if (val !== '' && supplierValues.indexOf(val) !== -1) {
					e.preventDefault();
					var form = supplier.closest('form');
					if (form) form.requestSubmit();
				}
				// Jika belum ada, jangan do anything - biarkan user klik "add"
				e.preventDefault();
			}
		});
	}
	if (customer) {
		customer.addEventListener('keydown', function(e){
			if (e.key === 'Enter') {
				var val = (customer.value || '').trim().toLowerCase();
				// Hanya submit jika data ada di database
				if (val !== '' && customerValues.indexOf(val) !== -1) {
					e.preventDefault();
					var form = customer.closest('form');
					if (form) form.requestSubmit();
				}
				// Jika belum ada, jangan do anything - biarkan user klik "add"
				e.preventDefault();
			}
		});
	}

	// Simpan form state ke sessionStorage
	function saveFormState(){
		var formData = {
			trx_type: typeSelect ? typeSelect.value : '',
			part_number: document.getElementById('part_number') ? document.getElementById('part_number').value : '',
			stock_masuk: stockMasuk ? stockMasuk.value : '',
			stock_keluar: stockKeluar ? stockKeluar.value : '',
			supplier: supplier ? supplier.value : '',
			customer: customer ? customer.value : '',
			keterangan: noteInput ? noteInput.value : ''
		};
		sessionStorage.setItem('formState', JSON.stringify(formData));
	}

	// Restore form state dari sessionStorage
	function restoreFormState(){
		var savedData = sessionStorage.getItem('formState');
		if (savedData) {
			try {
				var formData = JSON.parse(savedData);
				if (typeSelect) typeSelect.value = formData.trx_type || 'in';
				var partNumberInput = document.getElementById('part_number');
				if (partNumberInput) partNumberInput.value = formData.part_number || '';
				if (stockMasuk) stockMasuk.value = formData.stock_masuk || '0';
				if (stockKeluar) stockKeluar.value = formData.stock_keluar || '0';
				if (supplier) supplier.value = formData.supplier || '';
				if (customer) customer.value = formData.customer || '';
				if (noteInput) noteInput.value = formData.keterangan || '';
				
				// Trigger events untuk update state
				if (typeSelect) {
					var event = new Event('change');
					typeSelect.dispatchEvent(event);
				}
				applyState();
				updateWarnings();
				
				// Clear sessionStorage setelah restore
				sessionStorage.removeItem('formState');
			} catch(e) {
				console.log('Error restoring form state:', e);
			}
		}
	}

	// Restore form state saat page load
	document.addEventListener('DOMContentLoaded', function(){
		restoreFormState();
		updateWarnings();
		
		// Handler untuk tombol Reset (btnContinue)
		var btnContinue = document.getElementById('btnContinue');
		if (btnContinue) {
			btnContinue.addEventListener('click', function(){
				// Close success message
				var successMsg = document.getElementById('success-msg');
				if (successMsg) {
					successMsg.style.display = 'none';
				}
				
				// Reset form
				var form = document.querySelector('form');
				if (form) form.reset();
				applyState();
				updateWarnings();
				
				// Set fokus ke type select
				if (typeSelect) {
					typeSelect.focus();
				}
				
				// Scroll ke atas
				window.scrollTo(0, 0);
			});
		}
	});

	function updateNoteFromParty(){
		if (!noteInput) return;
		if (noteInput.dataset.userEdited === '1') return;
		var val = typeSelect.value;
		var note = '';
		if (val === 'in' && supplier.value.trim() !== '') {
			note = supplier.value.trim();
		} else if (val === 'out' && customer.value.trim() !== '') {
			note =  customer.value.trim();
		}
		noteInput.value = note;
	}

	if (noteInput) {
		noteInput.addEventListener('input', function(){
			noteInput.dataset.userEdited = noteInput.value.trim() === '' ? '0' : '1';
		});
	}

	function calculateStockAkhir(){
		var stockAkhirInput = document.getElementById('stock_akhir');
		if (!stockAkhirInput) return;
		var awal = parseInt(stockAwal.value) || 0;
		var masuk = parseInt(stockMasuk.value) || 0;
		var keluar = parseInt(stockKeluar.value) || 0;
		var akhir = awal + masuk - keluar;
		stockAkhirInput.value = akhir < 0 ? 0 : akhir;
	}

	if (stockMasuk) {
		stockMasuk.addEventListener('input', calculateStockAkhir);
	}
	if (stockKeluar) {
		stockKeluar.addEventListener('input', calculateStockAkhir);
	}

	typeSelect.addEventListener('change', applyState);
	applyState();
	updateSubmitButtonState();
})();
</script>

<div class="card" style="margin-bottom:5px;">
	<h3 style="margin:0 0 12px;">Tabel Stok</h3>
	<div class="table-wrapper">
		<table class="table" id="stock-table">
			<thead>
				<tr>
					<th style="text-align: center;">No</th>
					<th style="text-align: center;">Part Number</th>
					<th style="text-align: center;">Description</th>
					<th style="text-align: center;">Brand</th>
					<th style="text-align: center;">Category</th>
					<th style="text-align: center;">Stock</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$stockRows = mysqli_query($kon, "SELECT `No`, part_number, description, brand, category, Qty FROM Product ORDER BY `No` DESC");
				if ($stockRows && mysqli_num_rows($stockRows) > 0):
					while ($row = mysqli_fetch_assoc($stockRows)):
				?>
				<tr data-part-number="<?php echo htmlspecialchars($row['part_number']); ?>" data-qty="<?php echo htmlspecialchars($row['Qty']); ?>" style="display:none;">
					<td style="text-align:center;"><?php echo htmlspecialchars($row['No']); ?></td>
					<td style="text-align:center;"><?php echo htmlspecialchars($row['part_number']); ?></td>
					<td style="text-align:center;"><?php echo htmlspecialchars($row['description']); ?></td>
					<td style="text-align:center;"><?php echo htmlspecialchars($row['brand']); ?></td>
					<td style="text-align:center;"><?php echo htmlspecialchars($row['category']); ?></td>
					<td style="text-align:center;"><?php echo htmlspecialchars($row['Qty']); ?></td>
				</tr>
				<?php
					endwhile;
				endif;
				?>
				<!-- Empty row: always rendered, JS controls visibility -->
				<tr id="stock-empty-row">
					<td colspan="6" style="text-align:center;color:#94a3b8;">Pilih part number terlebih dahulu.</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<style>
		/* Only the history table scrolls; header stays fixed */
		.history-table-wrapper {
			display: block;
			height: 240px;
			overflow-y: scroll;
			overflow-x: hidden;
		}
		.history-table-wrapper table {
			width: 100%;
			border-collapse: collapse;
			display: table;
		}
		.history-table-wrapper thead {
			display: table;
			width: 100%;
			table-layout: fixed;
		}
		.history-table-wrapper tbody {
			display: block;
			overflow-y: scroll;
			height: 200px;
		}
		.history-table-wrapper tbody tr {
			display: table;
			width: 100%;
			table-layout: fixed;
		}
		.history-table-wrapper thead th {
			background: #2563eb;
		}
	</style>
<div class="card">
	<h3 style="margin:0 0 12px;">Riwayat Transaksi (100 terakhir)</h3>
	<div class="table-wrapper history-table-wrapper">
		<table class="table">
			<thead>
				<tr>
					<th style="width: 25px; text-align:center;">ID</th>
					<th style="width: 100px; text-align:center;">Tanggal</th>
					<th style="width: 100px; text-align:center;">Part Number</th>
					<th style="width: 75px; text-align:center;">Stock Awal</th>
					<th style="width: 75px; text-align:center;">Stock Masuk</th>
					<th style="width: 75px; text-align:center;">Stock Keluar</th>
					<th style="width: 75px; text-align:center;">Stock Akhir</th>
					<th style="width: 200px; text-align:left;">Keterangan</th>
				</tr>
			</thead>
			<tbody>
				<tr id="history-empty-row">
					<td colspan="8" style="text-align:center;color:#94a3b8;">Pilih part number terlebih dahulu.</td>
				</tr>
				<?php
				$historyRows = mysqli_query($kon, "SELECT id, part_number, qty_before, qty_in, qty_out, qty_after, note, created_at FROM stock_movement ORDER BY created_at DESC LIMIT 100");
				if ($historyRows && mysqli_num_rows($historyRows) > 0):
					while ($trx = mysqli_fetch_assoc($historyRows)):
				?>
				<tr data-part-number="<?php echo htmlspecialchars($trx['part_number']); ?>" style="display:none;">
					<td style="width: 25px;text-align:center;"><?php echo htmlspecialchars($trx['id']); ?></td>
					<td style="width: 100px;text-align:center;"><?php echo htmlspecialchars($trx['created_at']); ?></td>
					<td style="width: 100px;text-align:center;"><?php echo htmlspecialchars($trx['part_number']); ?></td>
					<td style="width: 75px;text-align:center;"><?php echo htmlspecialchars($trx['qty_before']); ?></td>
					<td style="width: 75px;text-align:center;"><?php echo htmlspecialchars($trx['qty_in']); ?></td>
					<td style="width: 75px;text-align:center;"><?php echo htmlspecialchars($trx['qty_out']); ?></td>
					<td style="width: 75px;text-align:center;"><?php echo htmlspecialchars($trx['qty_after']); ?></td>
					<td style="width: 200px;text-align:left;"><?php echo htmlspecialchars($trx['note']); ?></td>
				</tr>
				<?php
					endwhile;
				endif;
				?>
			</tbody>
		</table>
	</div>
</div>

</div>

	
<script>
(function(){
	var pnInput = document.getElementById('part_number');
	var stockAwal = document.getElementById('stock_awal');
	var stockTable = document.getElementById('stock-table');
	var historyTable = document.querySelector('.card:nth-of-type(3) table');
	if (!pnInput) return;

	function normalize(val){
		return (val || '').toString().trim().toLowerCase();
	}

	function filterTable(table, emptyRowId, emptyText){
		if (!table) return;
		var rows = Array.prototype.slice.call(table.querySelectorAll('tbody tr'));
		var pn = normalize(pnInput.value);
		var visibleCount = 0;
		rows.forEach(function(row){
			if (row.id === emptyRowId) return;
			var rowPn = normalize(row.getAttribute('data-part-number'));
			var show = pn !== '' && rowPn === pn;
			row.style.display = show ? '' : 'none';
			if (show) visibleCount++;
		});

		var emptyRow = table.querySelector('#' + emptyRowId);
		if (emptyRow) {
			emptyRow.style.display = visibleCount === 0 ? '' : 'none';
			if (emptyText) emptyRow.querySelector('td').textContent = emptyText;
		}
	}

	function applyFilter(){
		var emptyText = pnInput.value.trim() === ''
			? 'Pilih part number terlebih dahulu.'
			: 'Data tidak ditemukan.';
		filterTable(stockTable, 'stock-empty-row', emptyText);
		filterTable(historyTable, 'history-empty-row', emptyText);
		updateStockAwal();
	}

	function updateStockAwal(){
		if (!stockAwal) return;
		var pn = normalize(pnInput.value);
		if (pn === '') {
			stockAwal.value = '';
			return;
		}
		var qty = '';
		if (stockTable) {
			var rows = Array.prototype.slice.call(stockTable.querySelectorAll('tbody tr'));
			for (var i = 0; i < rows.length; i++) {
				var row = rows[i];
				if (row.id === 'stock-empty-row') continue;
				var rowPn = normalize(row.getAttribute('data-part-number'));
				if (rowPn === pn) {
					qty = row.dataset && row.dataset.qty ? row.dataset.qty : '';
					break;
				}
			}
		}
		stockAwal.value = qty !== '' ? qty : '0';
		// Recalculate stock akhir when stock awal changes
		var stockAkhirInput = document.getElementById('stock_akhir');
		if (stockAkhirInput) {
			var awal = parseInt(stockAwal.value) || 0;
			var masuk = parseInt(stockMasuk.value) || 0;
			var keluar = parseInt(stockKeluar.value) || 0;
			var akhir = awal + masuk - keluar;
			stockAkhirInput.value = akhir < 0 ? 0 : akhir;
		}
	}

	pnInput.addEventListener('input', applyFilter);
	pnInput.addEventListener('change', applyFilter);
	pnInput.addEventListener('blur', updateStockAwal);
	applyFilter();
})();
</script>
