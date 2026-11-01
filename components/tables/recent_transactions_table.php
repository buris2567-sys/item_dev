<?php
// ดึงประวัติการเปลี่ยนแปลงล่าสุด (พร้อมชื่อสิ่งของ, หมวดหมู่, เลขใบเบิก)
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

<!-- CSS Counter สำหรับลำดับแถว -->
<style>
    table#txTable { counter-reset: txRowNumber; }
    table#txTable tbody tr.tx-row:not([style*="display: none"]) { counter-increment: txRowNumber; }
    table#txTable tbody tr.tx-row:not([style*="display: none"]) .tx-row-number::before { content: counter(txRowNumber); }
</style>

<!-- การ์ดตารางประวัติการเปลี่ยนแปลงล่าสุด -->
<div class="card border-0 rounded-4 shadow-sm mb-5 border-warning border-top border-4 bg-white">

<?php
$cardHeader = [
    'icon'   => 'bi-clock-history',
    'title'  => 'ประวัติการเปลี่ยนแปลงล่าสุด',
    'search' => ['id' => 'searchTxInput', 'placeholder' => 'ค้นหาประวัติ...'],
];
include 'components/ui/card_header.php';
?>

    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle text-center mb-0" id="txTable">
            <thead class="table-light text-muted">
                <tr>
                    <th class="fw-bold py-3 border-0" style="width: 60px;">ลำดับ</th>
                    <th class="fw-bold py-3 border-0" style="width: 120px;">ประเภท</th>
                    <th class="fw-bold py-3 border-0 text-start ps-3">ชื่อรายการ</th>
                    <th class="fw-bold py-3 border-0 text-start ps-3">เหตุการณ์</th>
                    <th class="fw-bold py-3 border-0" style="width: 80px;">เดิม</th>
                    <th class="fw-bold py-3 border-0" style="width: 100px;">เปลี่ยนแปลง</th>
                    <th class="fw-bold py-3 border-0" style="width: 80px;">คงเหลือ</th>
                    <th class="fw-bold py-3 border-0" style="width: 100px;">วันที่</th>
                    <th class="fw-bold py-3 border-0 text-start ps-3">รายละเอียด</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $tx):
                        $txType = $tx['transaction_type'];
                        $qty    = (int)$tx['quantity'];

                        if ($qty > 0) {
                            $qtySign  = '+';
                            $qtyClass = 'text-success';
                            $prevClass = 'text-muted';
                            $currClass = 'text-dark';
                        } elseif ($qty < 0) {
                            $qtySign  = '';
                            $qtyClass = 'text-danger';
                            $prevClass = 'text-muted';
                            $currClass = 'text-dark';
                        } else {
                            $qtySign  = '';
                            $qtyClass = 'text-dark fw-bold';
                            $prevClass = 'text-muted';
                            $currClass = 'text-dark';
                        }
                    ?>
                        <tr class="tx-row"
                            data-search-text="<?= htmlspecialchars(strtolower(
                                ($tx['category_name'] ?? '') . ' ' .
                                ($tx['item_name']     ?? '') . ' ' .
                                ($tx['remark']        ?? '') . ' ' .
                                ($tx['username']      ?? '') . ' ' .
                                ($tx['request_no']    ?? '')
                            )) ?>"
                            style="border-bottom: 1px solid #f8f9fa;">
                            <td class="text-muted fw-bold py-3">
                                <span class="tx-row-number bg-light rounded-circle d-inline-flex justify-content-center align-items-center"
                                      style="width: 28px; height: 28px;"></span>
                            </td>
                            <td class="text-muted small"><?= htmlspecialchars($tx['category_name'] ?? '-') ?></td>
                            <td class="text-start ps-3 fw-bold text-dark"><?= htmlspecialchars($tx['item_name'] ?? '-') ?></td>
                            <td class="text-start ps-3">
                                <span class="badge rounded-pill px-3 py-2 <?= ($qty > 0) ? 'bg-success' : (($qty < 0) ? 'bg-danger' : 'bg-secondary') ?>">
                                    <?= htmlspecialchars($tx['transaction_type'] ?? '-') ?>
                                </span>
                            </td>
                            <td class="<?= $prevClass ?> fw-bold bg-light"><?= number_format($tx['previous_stock'] ?? 0) ?></td>
                            <td class="fw-bold <?= $qtyClass ?>"><?= $qtySign . number_format($qty) ?></td>
                            <td class="<?= $currClass ?> fw-bold bg-light">
                                <?php if ($txType === 'ลบสิ่งของ'): ?>
                                    <span class="badge bg-danger rounded-pill">ลบแล้ว</span>
                                <?php else: ?>
                                    <?= number_format($tx['current_stock'] ?? 0) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="small fw-bold"><?= !empty($tx['created_at']) ? date('d/m/Y', strtotime($tx['created_at'])) : '-' ?></div>
                                <div class="small text-muted"><?= !empty($tx['created_at']) ? date('H:i', strtotime($tx['created_at'])) : '-' ?></div>
                            </td>
                            <td class="text-start ps-3 text-muted small">
                                <!-- หมายเหตุหลัก -->
                                <?= htmlspecialchars($tx['remark'] ?? '-') ?>

                                <!-- เลขที่ใบเบิก (แสดงเมื่อมีการอ้างอิง) -->
                                <?php if (!empty($tx['request_no'])): ?>
                                    <div class="text-primary mt-1">
                                        <i class="bi bi-file-earmark-text"></i> อ้างอิง:
                                        <a href="index.php?page=request_view&id=<?= $tx['request_id'] ?>">
                                            <?= htmlspecialchars($tx['request_no']) ?>
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <!-- ผู้ทำรายการ -->
                                <?php if (!empty($tx['username'])): ?>
                                    <div class="text-secondary mt-1" style="font-size: 0.85em;">
                                        <i class="bi bi-person-fill"></i> ทำรายการโดย: <?= htmlspecialchars($tx['username']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted border-0">
                            <div class="bg-light rounded-circle d-inline-flex justify-content-center align-items-center mb-3 shadow-sm" style="width: 70px; height: 70px;">
                                <i class="bi bi-clock-history fs-2 text-secondary opacity-50"></i>
                            </div>
                            <h6 class="fw-bold mb-0">ยังไม่มีประวัติการเปลี่ยนแปลง</h6>
                        </td>
                    </tr>
                <?php endif; ?>

                <tr id="noTxFilterRow" style="display: none;">
                    <td colspan="9" class="text-center py-5 text-muted border-0">
                        <div class="bg-light rounded-circle d-inline-flex justify-content-center align-items-center mb-3 shadow-sm" style="width: 70px; height: 70px;">
                            <i class="bi bi-inbox fs-2 text-secondary opacity-50"></i>
                        </div>
                        <h6 class="fw-bold mb-0">ไม่พบประวัติการเปลี่ยนแปลงที่ค้นหา</h6>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

<?php
$paginationWrapperId = 'txTablePaginationWrapper';
include 'components/ui/card_footer_pagination.php';
?>
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
