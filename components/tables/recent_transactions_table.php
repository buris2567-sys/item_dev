<!-- การ์ดตารางประวัติการเปลี่ยนแปลงล่าสุด -->
<div class="card border-dark rounded-0 mb-4 shadow-sm" style="border-width: 1px !important;">
    <div class="card-header bg-warning border-dark rounded-0 fw-bold py-3" style="border-bottom-width: 1px !important;">
        ประวัติการเปลี่ยนแปลงล่าสุด
    </div>
    <div class="card-body p-0">
        
        <!-- แถบเครื่องมือ: ค้นหาประวัติ -->
        <div class="d-flex justify-content-end align-items-center p-3 border-bottom">
            <div class="d-flex align-items-center">
                <i class="bi bi-search me-2 text-muted"></i>
                <input type="text" id="searchTxInput" class="form-control form-control-sm border-dark rounded-0" style="width: 250px;" placeholder="ค้นหา...">
            </div>
        </div>

        <!-- ตารางข้อมูล Transaction -->
        <div class="table-responsive">
            <table class="table table-bordered border-dark align-middle text-center mb-0" id="txTable">
                <thead class="table-light">
                    <tr>
                        <th class="text-muted small fw-bold" style="width: 80px;">ลำดับ</th>
                        <th class="text-muted small fw-bold" style="width: 130px;">ประเภท</th>
                        <th class="text-start ps-4 text-muted small fw-bold">ชื่อรายการ</th>
                        <th class="text-muted small fw-bold" style="width: 100px;">จำนวน</th>
                        <th class="text-muted small fw-bold" style="width: 120px;">วันที่</th>
                        <th class="text-muted small fw-bold" style="width: 100px;">เวลา</th>
                        <th class="text-start ps-3 text-muted small fw-bold">รายละเอียด</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transactions)): ?>
                        <?php foreach ($transactions as $index => $tx): ?>
                            <?php 
                                $isAdd = ($tx['transaction_type'] === 'IN');
                                $qtySign = $isAdd ? '+' : '-';
                                $qtyClass = $isAdd ? 'text-success' : 'text-danger';
                                $txDate = !empty($tx['created_at']) ? date('d/m/Y', strtotime($tx['created_at'])) : '-';
                                $txTime = !empty($tx['created_at']) ? date('H:i:s', strtotime($tx['created_at'])) : '-';
                            ?>
                            <tr class="tx-row" data-search-text="<?= htmlspecialchars(strtolower(($tx['category_name'] ?? '') . ' ' . ($tx['item_name'] ?? '') . ' ' . ($tx['remark'] ?? '') . ' ' . ($tx['username'] ?? ''))) ?>">
                                <td class="text-muted"><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($tx['category_name'] ?? '-') ?></td>
                                <td class="text-start ps-4 fw-bold text-dark">
                                    <?= htmlspecialchars($tx['item_name'] ?? '-') ?>
                                </td>
                                <td class="fw-bold <?= $qtyClass ?>">
                                    <?= $qtySign . number_format($tx['quantity']) ?>
                                </td>
                                <td><?= $txDate ?></td>
                                <td><?= $txTime ?></td>
                                <td class="text-start ps-3 text-muted small">
                                    <?= htmlspecialchars($tx['remark'] ?? '-') ?>
                                    <?php if (!empty($tx['username'])): ?>
                                        <span class="text-secondary">(โดย <?= htmlspecialchars($tx['username']) ?>)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr id="noTxDataRow">
                            <td colspan="7" class="text-center py-4 text-muted">ไม่พบประวัติการเปลี่ยนแปลง</td>
                        </tr>
                    <?php endif; ?>
                    <tr id="noTxFilterRow" style="display: none;">
                        <td colspan="7" class="text-center py-4 text-muted">ไม่พบข้อมูลที่ค้นหา</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- แถบแบ่งหน้า Transaction (Pagination) -->
        <div class="d-flex justify-content-between align-items-center p-3 border-top">
            <div class="d-flex align-items-center">
                <span class="small text-muted me-2">Items per page:</span>
                <select id="txPerPage" class="form-select form-select-sm border-dark rounded-0" style="width: 70px;">
                    <option value="5" selected>5</option>
                    <option value="10">10</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div class="d-flex align-items-center">
                <span class="small text-muted me-3" id="txPageInfo">1 - 5 of 0</span>
                <button class="btn btn-sm btn-light border-dark rounded-0 me-1" id="txPrevPage"><i class="bi bi-chevron-left"></i></button>
                <button class="btn btn-sm btn-light border-dark rounded-0" id="txNextPage"><i class="bi bi-chevron-right"></i></button>
            </div>
        </div>

    </div>
</div>

<!-- Script จัดการการค้นหาและแบ่งหน้าสำหรับ Transaction Table -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const txRows = Array.from(document.querySelectorAll('.tx-row'));
    const searchTxInput = document.getElementById('searchTxInput');
    const txPerPageSelect = document.getElementById('txPerPage');
    const txPrevPageBtn = document.getElementById('txPrevPage');
    const txNextPageBtn = document.getElementById('txNextPage');
    const txPageInfo = document.getElementById('txPageInfo');
    const noTxFilterRow = document.getElementById('noTxFilterRow');

    let currentTxPage = 1;
    let txPerPage = parseInt(txPerPageSelect.value);
    let filteredTxRows = [...txRows];

    function updateTxTable() {
        const query = searchTxInput.value.trim().toLowerCase();

        // กรองแถวตามคำค้นหา
        filteredTxRows = txRows.filter(row => {
            const text = row.getAttribute('data-search-text') || '';
            return text.includes(query);
        });

        // ซ่อนทุกแถว
        txRows.forEach(row => row.style.display = 'none');

        // คำนวณแบ่งหน้า
        const totalItems = filteredTxRows.length;
        const totalPages = Math.ceil(totalItems / txPerPage) || 1;
        if (currentTxPage > totalPages) currentTxPage = totalPages;
        if (currentTxPage < 1) currentTxPage = 1;

        const startIndex = (currentTxPage - 1) * txPerPage;
        const endIndex = Math.min(startIndex + txPerPage, totalItems);

        // แสดงเฉพาะแถวในหน้าที่เลือก
        for (let i = startIndex; i < endIndex; i++) {
            filteredTxRows[i].style.display = '';
        }

        // อัปเดตตัวเลขและปุ่ม Pagination
        if (totalItems === 0) {
            if (noTxFilterRow) noTxFilterRow.style.display = '';
            txPageInfo.textContent = '0 - 0 of 0';
        } else {
            if (noTxFilterRow) noTxFilterRow.style.display = 'none';
            txPageInfo.textContent = `${startIndex + 1} - ${endIndex} of ${totalItems}`;
        }

        txPrevPageBtn.disabled = (currentTxPage === 1 || totalItems === 0);
        txNextPageBtn.disabled = (currentTxPage === totalPages || totalItems === 0);
    }

    if (searchTxInput) {
        searchTxInput.addEventListener('input', () => {
            currentTxPage = 1;
            updateTxTable();
        });
    }

    if (txPerPageSelect) {
        txPerPageSelect.addEventListener('change', (e) => {
            txPerPage = parseInt(e.target.value);
            currentTxPage = 1;
            updateTxTable();
        });
    }

    if (txPrevPageBtn) {
        txPrevPageBtn.addEventListener('click', () => {
            if (currentTxPage > 1) {
                currentTxPage--;
                updateTxTable();
            }
        });
    }

    if (txNextPageBtn) {
        txNextPageBtn.addEventListener('click', () => {
            const totalPages = Math.ceil(filteredTxRows.length / txPerPage);
            if (currentTxPage < totalPages) {
                currentTxPage++;
                updateTxTable();
            }
        });
    }

    // เรียกทำงานครั้งแรก
    if (txRows.length > 0) {
        updateTxTable();
    }
});
</script>
