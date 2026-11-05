<?php
/**
 * components/tables/inventory_report_table.php
 * คอมโพเนนต์ตารางแสดงรายละเอียดรายการคงคลัง (Stock Items Ledger Table)
 * ใช้งานร่วมกับ assets/js/table_pagination.js และ components/ui/card_footer_pagination.php
 */
?>

<!-- การ์ดตารางแสดงรายละเอียดรายการคงคลัง -->
<div class="card border-0 rounded-4 shadow-sm overflow-hidden mb-5">
    
    <!-- ส่วนหัวการ์ดตาราง: หัวข้อ + ตัวกรองหมวดหมู่ + ช่องค้นหา -->
    <div class="card-header bg-white border-0 p-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h6 class="fw-bold mb-1">
                <i class="bi bi-table text-primary me-2"></i>รายละเอียดรายการคงคลัง
            </h6>
            <span class="small text-muted">Stock Items Ledger — <?= htmlspecialchars($periodLabel) ?></span>
        </div>
        
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <!-- ตัวกรองหมวดหมู่ -->
            <select id="categoryReportFilter" class="form-select form-select-sm rounded-pill shadow-sm" style="width:170px">
                <option value="all">ทุกหมวดหมู่</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category['name']) ?>"><?= htmlspecialchars($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
            
            <!-- ช่องค้นหารหัสหรือชื่อพัสดุ -->
            <div class="input-group input-group-sm shadow-sm" style="width:235px">
                <span class="input-group-text bg-light border-0 rounded-start-pill text-muted">
                    <i class="bi bi-search"></i>
                </span>
                <input id="reportSearch" type="search" class="form-control bg-light border-0 rounded-end-pill shadow-none" placeholder="ค้นหารหัสหรือชื่อพัสดุ">
            </div>
        </div>
    </div>

    <!-- ส่วนเนื้อหาตาราง -->
    <div class="table-responsive">
        <table class="table report-table table-hover align-middle mb-0" id="inventoryReportTable">
            <thead class="table-light text-muted">
                <tr>
                    <th class="ps-4 fw-bold py-3 border-0">#</th>
                    <th class="fw-bold py-3 border-0">รหัสพัสดุ</th>
                    <th class="fw-bold py-3 border-0">รายการสิ่งของ</th>
                    <th class="fw-bold py-3 border-0">หมวดหมู่</th>
                    <th class="text-end fw-bold py-3 border-0">จำนวนเดิมที่มี</th>
                    <th class="text-end text-success fw-bold py-3 border-0">รับเพิ่ม (+)</th>
                    <th class="text-end text-danger fw-bold py-3 border-0">จ่ายออก (-)</th>
                    <th class="text-end pe-4 fw-bold py-3 border-0">คงเหลือ</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                <?php if (!empty($ledger)): ?>
                    <?php foreach ($ledger as $index => $item): 
                        $isLow = $item['closing_stock'] <= (int)$item['low_stock_threshold']; 
                    ?>
                        <tr class="report-row <?= $isLow ? 'row-low-stock' : '' ?>" 
                            data-category="<?= htmlspecialchars($item['category_name']) ?>" 
                            data-search="<?= htmlspecialchars(mb_strtolower($item['item_code'] . ' ' . $item['name'], 'UTF-8')) ?>">
                            <td class="ps-4 text-muted fw-bold"><?= $index + 1 ?></td>
                            <td><span class="font-monospace small px-2 py-1 bg-light rounded-3 border text-dark"><?= htmlspecialchars($item['item_code']) ?></span></td>
                            <td class="fw-semibold text-dark">
                                <?= htmlspecialchars($item['name']) ?>
                                <?php if ($isLow): ?>
                                    <span class="badge bg-warning bg-opacity-25 text-warning rounded-pill ms-1 px-2 py-1">สต็อกต่ำ</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-light text-secondary rounded-pill border px-3 py-1"><?= htmlspecialchars($item['category_name']) ?></span></td>
                            <td class="text-end fw-semibold"><?= number_format($item['opening_stock']) ?></td>
                            <td class="text-end text-success fw-semibold"><?= $item['qty_in'] ? '+' . number_format($item['qty_in']) : '-' ?></td>
                            <td class="text-end text-danger fw-semibold"><?= $item['qty_out'] ? '-' . number_format($item['qty_out']) : '-' ?></td>
                            <td class="text-end pe-4 fw-bold fs-6 text-dark"><?= number_format($item['closing_stock']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <tr id="noReportResult" style="display: none;">
                    <td colspan="8" class="text-center text-muted py-5 border-0">
                        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                        <h6 class="fw-bold mb-0">ไม่พบรายการพัสดุที่ค้นหา</h6>
                    </td>
                </tr>
            </tbody>
            <tfoot class="table-warning border-top">
                <tr class="fw-bold">
                    <td colspan="4" class="ps-4 py-3">รวมยอดทั้งสิ้น (<?= number_format(count($ledger)) ?> รายการ)</td>
                    <td class="text-end py-3"><?= number_format($totals['opening']) ?></td>
                    <td class="text-end text-success py-3">+<?= number_format($totals['in']) ?></td>
                    <td class="text-end text-danger py-3">-<?= number_format($totals['out']) ?></td>
                    <td class="text-end pe-4 py-3 fs-6"><?= number_format($totals['closing']) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- ส่วนท้ายการ์ด: ระบบ Pagination ส่วนกลาง -->
    <?php
    $paginationWrapperId = 'inventoryReportPaginationWrapper';
    include 'components/ui/card_footer_pagination.php';
    ?>
</div>

<!-- สคริปต์ควบคุม Pagination และการค้นหา/กรอง -->
<script src="assets/js/table_pagination.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchBox = document.getElementById('reportSearch');
        const categoryFilter = document.getElementById('categoryReportFilter');

        if (typeof initTablePagination === 'function') {
            initTablePagination({
                rowSelector: '.report-row',
                wrapperSelector: '#inventoryReportPaginationWrapper',
                noDataSelector: '#noReportResult',
                triggerInputs: [searchBox, categoryFilter],
                filterLogic: function(row) {
                    const cat = categoryFilter ? categoryFilter.value : 'all';
                    const term = (searchBox ? searchBox.value : '').toLowerCase().trim();
                    const rowCat = row.getAttribute('data-category') || '';
                    const rowSearch = (row.getAttribute('data-search') || '').toLowerCase();
                    const matchCat = (cat === 'all' || rowCat === cat);
                    const matchSearch = rowSearch.includes(term);
                    return matchCat && matchSearch;
                }
            });
        }
    });
</script>

