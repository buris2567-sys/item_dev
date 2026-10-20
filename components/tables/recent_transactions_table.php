<!-- // ดึงประวัติการเปลี่ยนแปลงล่าสุด -->
<?php
// 🟢 [แก้ไข] LEFT JOIN items โดยใช้ i.id แทน i.item_id
// และเพิ่มการดึงเลขใบเบิก (request_no) จากตาราง requests มาแสดงเผื่อไว้ด้วย
$transactions = $pdo->query("
    SELECT 
        t.*, 
        u.username, 
        i.name AS item_name,
        c.name AS category_name,
        r.request_no
    FROM inventory_transactions t
    LEFT JOIN users u ON t.created_by = u.user_id
    LEFT JOIN items i ON t.item_id = i.id
    LEFT JOIN categories c ON i.category_id = c.category_id
    LEFT JOIN requests r ON t.request_id = r.request_id
    ORDER BY t.created_at DESC
")->fetchAll();

?>


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
                <input type="text" id="searchTxInput" class="form-control form-control-sm border-dark rounded-0" style="width: 250px;" placeholder="ค้นหาประวัติ...">
            </div>
        </div>

        <div class="table-responsive">
            <style>
                table#txTable { counter-reset: txRowNumber; }
                table#txTable tbody tr.tx-row:not([style*="display: none"]) { counter-increment: txRowNumber; }
                table#txTable tbody tr.tx-row:not([style*="display: none"]) .tx-row-number::before { content: counter(txRowNumber); }
            </style>

            <table class="table table-bordered border-dark align-middle text-center mb-0" id="txTable">
                <thead class="table-light">
                    <tr>
                        <th class="text-muted small fw-bold" style="width: 60px;">ลำดับ</th>
                        <th class="text-muted small fw-bold" style="width: 120px;">ประเภท</th>
                        <th class="text-start ps-3 text-muted small fw-bold">ชื่อรายการ</th>
                        <th class="text-start ps-3 text-muted small fw-bold">เหตุการณ์</th>
                        <th class="text-muted small fw-bold bg-light" style="width: 80px;">เดิม</th>
                        <th class="text-muted small fw-bold" style="width: 100px;">เปลี่ยนแปลง</th>
                        <th class="text-muted small fw-bold bg-light" style="width: 80px;">คงเหลือ</th>
                        <th class="text-muted small fw-bold" style="width: 100px;">วันที่</th>
                        <th class="text-start ps-3 text-muted small fw-bold">รายละเอียด (อ้างอิง)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transactions)): ?>
                        <?php foreach ($transactions as $tx): ?>
                            <?php
                            $txType = $tx['transaction_type'];
                            $qty = (int)$tx['quantity'];

                            // 🟢 [แก้ไข] เช็คค่า quantity ถ้ามากกว่า 0 คือของเข้า, ติดลบคือของออก (ระบบใหม่เราเก็บค่าติดลบตรงๆ ใน DB แล้ว)
                            if ($qty > 0) {
                                $qtySign = '+';
                                $qtyClass = 'text-success';
                                $prevClass = 'text-muted';
                                $currClass = 'text-dark';
                            } elseif ($qty < 0) {
                                $qtySign = ''; // ไม่ต้องใส่ - ซ้อน เพราะเลขมันติดลบมาแต่แรกอยู่แล้ว
                                $qtyClass = 'text-danger';
                                $prevClass = 'text-danger'; 
                                $currClass = 'text-danger'; 
                            } else {
                                // กรณีแก้ไขที่จำนวนไม่เปลี่ยน (qty = 0)
                                $qtySign = '';
                                $qtyClass = 'text-dark fw-bold';
                                $prevClass = 'text-muted';
                                $currClass = 'text-dark';
                            }
                            ?>
                            <tr class="tx-row" data-search-text="<?= htmlspecialchars(strtolower(($tx['category_name'] ?? '') . ' ' . ($tx['item_name'] ?? '') . ' ' . ($tx['remark'] ?? '') . ' ' . ($tx['username'] ?? '') . ' ' . ($tx['request_no'] ?? ''))) ?>">
                                <td class="text-muted fw-bold"><span class="tx-row-number"></span></td>
                                <td><?= htmlspecialchars($tx['category_name'] ?? '-') ?></td>
                                <td class="text-start ps-3 fw-bold text-dark"><?= htmlspecialchars($tx['item_name'] ?? '-') ?></td>
                                <td class="text-start ps-3 fw-bold text-dark">
                                    <span class="badge <?= ($qty > 0) ? 'bg-success' : (($qty < 0) ? 'bg-danger' : 'bg-secondary') ?>">
                                        <?= htmlspecialchars($tx['transaction_type'] ?? '-') ?>
                                    </span>
                                </td>
                             
                                <td class="<?= $prevClass ?> fw-bold bg-light"><?= number_format($tx['previous_stock'] ?? 0) ?></td>
                                <td class="fw-bold <?= $qtyClass ?>"><?= $qtySign . number_format($qty) ?></td>
                                <td class="<?= $currClass ?> fw-bold bg-light">
                                    <?php if ($txType === 'ลบสิ่งของ'): ?>
                                        <span class="badge bg-danger rounded-0">ลบแล้ว</span>
                                    <?php else: ?>
                                        <?= number_format($tx['current_stock'] ?? 0) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small fw-bold"><?= !empty($tx['created_at']) ? date('d/m/Y', strtotime($tx['created_at'])) : '-' ?></div>
                                    <div class="small text-muted"><?= !empty($tx['created_at']) ? date('H:i', strtotime($tx['created_at'])) : '-' ?></div>
                                </td>
                                <td class="text-start ps-3 text-muted small">
                                    <?= htmlspecialchars($tx['remark'] ?? '-') ?>
                                    <!-- 🟢 แสดงเลขใบเบิก ถ้ามี -->
                                    <?php if (!empty($tx['request_no'])): ?>
                                        <div class="text-primary mt-1"><i class="bi bi-file-earmark-text"></i> อ้างอิง: <?= htmlspecialchars($tx['request_no']) ?></div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr id="noTxFilterRow" style="display: none;">
                        <td colspan="9" class="text-center py-5 text-muted border-0">
                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                            <h6 class="fw-bold mb-0">ไม่พบประวัติการเปลี่ยนแปลงที่ค้นหา</h6>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="txTablePaginationWrapper">
            <?php include 'includes/pagination.php'; ?>
        </div>

    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchTxBox = document.getElementById('searchTxInput');
        if (typeof initTablePagination === 'function') {
            initTablePagination({
                rowSelector: '.tx-row',                        
                wrapperSelector: '#txTablePaginationWrapper',  
                noDataSelector: '#noTxFilterRow',              
                triggerInputs: [searchTxBox],                  
                
                filterLogic: function(row) {
                    const search = searchTxBox.value.toLowerCase().trim();
                    const rowText = row.getAttribute('data-search-text') || '';
                    return rowText.includes(search);
                }
            });
        }
    });
</script>