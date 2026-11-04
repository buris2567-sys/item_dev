<?php
if (!defined('APP_RUNNING')) exit('Forbidden');
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') exit('Unauthorized');

$thaiMonths = [1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'];
$reportMode = ($_GET['report_mode'] ?? 'monthly') === 'fiscal' ? 'fiscal' : 'monthly';

// จำกัดช่วงเวลาตามวันที่เริ่มมีพัสดุหรือมีการเคลื่อนไหวจริงในระบบ
$availability = $pdo->query("SELECT MIN(activity_at) AS first_at, MAX(activity_at) AS last_at FROM (
    SELECT created_at AS activity_at FROM items WHERE created_at IS NOT NULL
    UNION ALL
    SELECT created_at AS activity_at FROM inventory_transactions WHERE created_at IS NOT NULL
) AS inventory_dates")->fetch(PDO::FETCH_ASSOC);
$firstDataTimestamp = strtotime($availability['first_at'] ?? 'now');
$lastDataTimestamp = strtotime($availability['last_at'] ?? 'now');
$firstCalendarYear = (int)date('Y', $firstDataTimestamp) + 543;
$lastCalendarYear = (int)date('Y', $lastDataTimestamp) + 543;
$firstMonth = (int)date('n', $firstDataTimestamp);
$lastMonth = (int)date('n', $lastDataTimestamp);
$fiscalYearFor = static fn(int $timestamp): int => (int)date('Y', $timestamp) + 543 + ((int)date('n', $timestamp) >= 10 ? 1 : 0);
$firstFiscalYear = $fiscalYearFor($firstDataTimestamp);
$lastFiscalYear = $fiscalYearFor($lastDataTimestamp);

$minYear = $reportMode === 'fiscal' ? $firstFiscalYear : $firstCalendarYear;
$maxYear = $reportMode === 'fiscal' ? $lastFiscalYear : $lastCalendarYear;
$defaultYear = $maxYear;
$defaultMonth = $lastMonth;
$selectedYear = (int)($_GET['year'] ?? $defaultYear);
if ($selectedYear < $minYear || $selectedYear > $maxYear) $selectedYear = $defaultYear;
$selectedMonth = (int)($_GET['month'] ?? $defaultMonth);
if ($selectedMonth < 1 || $selectedMonth > 12) $selectedMonth = $defaultMonth;
if ($reportMode === 'monthly') {
    if ($selectedYear === $firstCalendarYear && $selectedMonth < $firstMonth) $selectedMonth = $firstMonth;
    if ($selectedYear === $lastCalendarYear && $selectedMonth > $lastMonth) $selectedMonth = $lastMonth;
}
$monthOptions = array_filter($thaiMonths, static function ($number) use ($selectedYear, $firstCalendarYear, $lastCalendarYear, $firstMonth, $lastMonth) {
    return !($selectedYear === $firstCalendarYear && $number < $firstMonth)
        && !($selectedYear === $lastCalendarYear && $number > $lastMonth);
}, ARRAY_FILTER_USE_KEY);
$yearOptions = range($maxYear, $minYear);
$gregorianYear = $selectedYear - 543;

if ($reportMode === 'fiscal') {
    $periodStart = sprintf('%04d-10-01 00:00:00', $gregorianYear - 1);
    $periodEnd = sprintf('%04d-10-01 00:00:00', $gregorianYear);
    $periodLabel = 'ปีงบประมาณ พ.ศ. ' . $selectedYear;
} else {
    $periodStart = sprintf('%04d-%02d-01 00:00:00', $gregorianYear, $selectedMonth);
    $periodEnd = date('Y-m-d H:i:s', strtotime($periodStart . ' +1 month'));
    $periodLabel = $thaiMonths[$selectedMonth] . ' พ.ศ. ' . $selectedYear;
}

$categories = $pdo->query('SELECT category_id, name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$ledgerStmt = $pdo->prepare("SELECT i.id, i.item_code, i.name, i.current_stock, i.low_stock_threshold, i.is_active, COALESCE(c.name, 'ไม่ระบุหมวดหมู่') AS category_name,
    COALESCE((SELECT t.current_stock FROM inventory_transactions t WHERE t.item_id = i.id AND t.created_at < :start_before ORDER BY t.created_at DESC, t.transaction_id DESC LIMIT 1),
    (SELECT t.previous_stock FROM inventory_transactions t WHERE t.item_id = i.id AND t.created_at >= :start_first AND t.created_at < :end_first ORDER BY t.created_at ASC, t.transaction_id ASC LIMIT 1), i.current_stock) AS opening_stock,
    COALESCE(SUM(CASE WHEN tx.quantity > 0 THEN tx.quantity ELSE 0 END), 0) AS qty_in,
    COALESCE(SUM(CASE WHEN tx.quantity < 0 THEN ABS(tx.quantity) ELSE 0 END), 0) AS qty_out
    FROM items i LEFT JOIN categories c ON c.category_id = i.category_id
    LEFT JOIN inventory_transactions tx ON tx.item_id = i.id AND tx.created_at >= :start_tx AND tx.created_at < :end_tx
    GROUP BY i.id, i.item_code, i.name, i.current_stock, i.low_stock_threshold, i.is_active, c.name ORDER BY i.name ASC");
$ledgerStmt->execute([':start_before' => $periodStart, ':start_first' => $periodStart, ':end_first' => $periodEnd, ':start_tx' => $periodStart, ':end_tx' => $periodEnd]);
$ledger = $ledgerStmt->fetchAll(PDO::FETCH_ASSOC);
$totals = ['opening' => 0, 'in' => 0, 'out' => 0, 'closing' => 0];
foreach ($ledger as &$item) {
    foreach (['opening_stock', 'qty_in', 'qty_out'] as $key) $item[$key] = (int)$item[$key];
    $item['closing_stock'] = $item['opening_stock'] + $item['qty_in'] - $item['qty_out'];
    $totals['opening'] += $item['opening_stock']; $totals['in'] += $item['qty_in']; $totals['out'] += $item['qty_out']; $totals['closing'] += $item['closing_stock'];
}
unset($item);
$topItems = array_values(array_filter($ledger, static fn($item) => $item['qty_out'] > 0));
usort($topItems, static fn($a, $b) => $b['qty_out'] <=> $a['qty_out']);
$topItems = array_slice($topItems, 0, 5);
$topMax = max(array_column($topItems, 'qty_out') ?: [1]);
$lowStockCount = count(array_filter($ledger, static fn($item) => $item['closing_stock'] <= (int)$item['low_stock_threshold']));
?>

<style>
.report-tabs .btn{border-radius:.65rem}.report-tabs .btn.active{background:#ffc107;border-color:#ffc107;color:#212529}.report-stat-card,.report-chart-card{border:0;border-radius:1rem}.report-stat-card{min-height:142px}.stat-icon{width:42px;height:42px;border-radius:.75rem;display:inline-flex;align-items:center;justify-content:center;font-size:1.25rem}.stat-number{font-size:clamp(1.55rem,2.2vw,2.15rem);letter-spacing:-.04em}.top-item-label{max-width:70%;overflow:hidden;white-space:nowrap;text-overflow:ellipsis}.top-item-bar{height:9px;border-radius:999px;background:#f1f3f5;overflow:hidden}.top-item-bar span{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,#fb8b8b,#e85d5d)}.report-table th{white-space:nowrap;font-size:.82rem}.row-low-stock td{background-color:#fff8e8!important}@media print{.sidebar-wrapper,.report-toolbar,.report-actions,#reportSearch,#categoryReportFilter,#reportPagination{display:none!important}.container-fluid,body{background:#fff!important}.col.p-4{padding:0!important}.card{box-shadow:none!important;break-inside:avoid}.report-table{font-size:9pt}}
</style>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
 <div><h3 class="fw-bolder mb-1 text-dark"><i class="bi bi-clipboard-data-fill text-warning me-2"></i>สรุปรายงานการเบิกจ่าย</h3><p class="mb-0 text-muted small">ภาพรวมการเคลื่อนไหวและยอดคงเหลือของคลังพัสดุ</p></div>
 <div class="d-flex flex-column align-items-md-end gap-1"><span class="badge text-bg-light border text-secondary rounded-pill px-3 py-2"><i class="bi bi-calendar3 me-1"></i>ข้อมูล ณ <?= htmlspecialchars($periodLabel) ?></span><small class="text-muted">เริ่มมีข้อมูลตั้งแต่ <?= date('d', $firstDataTimestamp) . ' ' . $thaiMonths[(int)date('n', $firstDataTimestamp)] . ' ' . ((int)date('Y', $firstDataTimestamp) + 543) ?></small></div>
</div>

<div class="card border-0 rounded-4 shadow-sm mb-4 report-toolbar"><div class="card-body p-3 d-flex flex-wrap align-items-center gap-2">
 <div class="btn-group report-tabs"><a href="index.php?page=inventory_report&report_mode=monthly&month=<?= $selectedMonth ?>&year=<?= $selectedYear ?>" class="btn btn-outline-dark btn-sm fw-semibold <?= $reportMode === 'monthly' ? 'active' : '' ?>">สรุปรายเดือน</a><a href="index.php?page=inventory_report&report_mode=fiscal&year=<?= $selectedYear ?>" class="btn btn-outline-dark btn-sm fw-semibold <?= $reportMode === 'fiscal' ? 'active' : '' ?>">สรุปปีงบประมาณ</a></div>
 <form method="get" class="d-flex flex-wrap align-items-center gap-2 ms-lg-2"><input type="hidden" name="page" value="inventory_report"><input type="hidden" name="report_mode" value="<?= $reportMode ?>"><?php if ($reportMode === 'monthly'): ?><select name="month" class="form-select form-select-sm" style="width:142px"><?php foreach ($monthOptions as $number => $name): ?><option value="<?= $number ?>" <?= $selectedMonth === $number ? 'selected' : '' ?>><?= $name ?></option><?php endforeach; ?></select><?php endif; ?><select name="year" class="form-select form-select-sm" style="width:125px"><?php foreach ($yearOptions as $year): ?><option value="<?= $year ?>" <?= $selectedYear === $year ? 'selected' : '' ?>>พ.ศ. <?= $year ?></option><?php endforeach; ?></select><button class="btn btn-warning btn-sm fw-bold px-3"><i class="bi bi-search me-1"></i>ค้นหา</button></form>
 <div class="ms-lg-auto d-flex gap-2 report-actions"><button type="button" class="btn btn-outline-danger btn-sm fw-semibold" onclick="window.print()"><i class="bi bi-file-earmark-pdf me-1"></i>Export PDF</button><button type="button" class="btn btn-outline-success btn-sm fw-semibold" id="exportExcel"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Export Excel</button></div>
</div></div>

<div class="row g-3 mb-4">
 <div class="col-12 col-sm-6 col-xl-3"><div class="card report-stat-card shadow-sm"><div class="card-body p-4"><div class="d-flex justify-content-between"><span class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-box-seam"></i></span><span class="small text-muted">ต้นงวด</span></div><div class="stat-number fw-bold mt-3"><?= number_format($totals['opening']) ?></div><div class="small text-muted">ยอดยกมา</div></div></div></div>
 <div class="col-12 col-sm-6 col-xl-3"><div class="card report-stat-card shadow-sm"><div class="card-body p-4"><div class="d-flex justify-content-between"><span class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-box-arrow-in-down"></i></span><span class="small text-success fw-semibold">เพิ่มเข้า</span></div><div class="stat-number fw-bold text-success mt-3">+<?= number_format($totals['in']) ?></div><div class="small text-muted">รับเข้าระหว่างงวด</div></div></div></div>
 <div class="col-12 col-sm-6 col-xl-3"><div class="card report-stat-card shadow-sm"><div class="card-body p-4"><div class="d-flex justify-content-between"><span class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-box-arrow-up"></i></span><span class="small text-danger fw-semibold">จ่ายออก</span></div><div class="stat-number fw-bold text-danger mt-3">-<?= number_format($totals['out']) ?></div><div class="small text-muted">เบิกจ่ายระหว่างงวด</div></div></div></div>
 <div class="col-12 col-sm-6 col-xl-3"><div class="card report-stat-card shadow-sm border border-warning border-2"><div class="card-body p-4 bg-warning bg-opacity-10 rounded-4"><div class="d-flex justify-content-between"><span class="stat-icon bg-warning text-dark"><i class="bi bi-stars"></i></span><span class="small text-dark fw-semibold">คงเหลือ</span></div><div class="stat-number fw-bold mt-3"><?= number_format($totals['closing']) ?></div><div class="small text-muted">คงเหลือสุทธิปลายงวด</div></div></div></div>
</div>

<div class="row g-4 mb-4"><div class="col-lg-7"><div class="card report-chart-card shadow-sm h-100"><div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between"><h6 class="fw-bold mb-0"><i class="bi bi-bar-chart-fill text-danger me-2"></i>Top 5 พัสดุที่มีการเบิกจ่ายสูงสุด</h6><span class="small text-muted"><?= htmlspecialchars($periodLabel) ?></span></div><div class="card-body px-4 pb-4"><?php if ($topItems): ?><?php foreach ($topItems as $index => $item): ?><div class="mb-3"><div class="d-flex justify-content-between gap-3 small mb-1"><span class="top-item-label" title="<?= htmlspecialchars($item['name']) ?>"><?= $index + 1 ?>. <?= htmlspecialchars($item['name']) ?></span><strong class="text-danger">-<?= number_format($item['qty_out']) ?></strong></div><div class="top-item-bar"><span style="width:<?= number_format(($item['qty_out'] / $topMax) * 100, 2) ?>%"></span></div></div><?php endforeach; ?><?php else: ?><div class="py-5 text-center text-muted"><i class="bi bi-bar-chart fs-1 d-block opacity-25 mb-2"></i>ยังไม่มีการจ่ายออกในช่วงเวลานี้</div><?php endif; ?></div></div></div>
 <div class="col-lg-5"><div class="card report-chart-card shadow-sm h-100"><div class="card-header bg-white border-0 pt-4 px-4"><h6 class="fw-bold mb-0"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>ข้อมูลสำคัญของคลัง</h6></div><div class="card-body px-4 pb-4"><div class="d-flex justify-content-between py-3 border-bottom"><span class="text-muted"><i class="bi bi-boxes me-2"></i>จำนวนรายการพัสดุ</span><strong><?= number_format(count($ledger)) ?> รายการ</strong></div><div class="d-flex justify-content-between py-3 border-bottom"><span class="text-muted"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>รายการสต็อกต่ำ</span><strong class="<?= $lowStockCount ? 'text-danger' : 'text-success' ?>"><?= number_format($lowStockCount) ?> รายการ</strong></div><div class="d-flex justify-content-between py-3"><span class="text-muted"><i class="bi bi-arrow-left-right me-2 text-primary"></i>การเคลื่อนไหวรวม</span><strong><?= number_format($totals['in'] + $totals['out']) ?> หน่วย</strong></div></div></div></div></div>

<div class="card border-0 rounded-4 shadow-sm overflow-hidden"><div class="card-header bg-white border-0 p-4 d-flex flex-wrap justify-content-between align-items-center gap-2"><div><h6 class="fw-bold mb-1"><i class="bi bi-table text-primary me-2"></i>รายละเอียดรายการคงคลัง</h6><span class="small text-muted">Stock Items Ledger — <?= htmlspecialchars($periodLabel) ?></span></div><div class="d-flex flex-wrap gap-2"><select id="categoryReportFilter" class="form-select form-select-sm" style="width:170px"><option value="all">ทุกหมวดหมู่</option><?php foreach ($categories as $category): ?><option value="<?= htmlspecialchars($category['name']) ?>"><?= htmlspecialchars($category['name']) ?></option><?php endforeach; ?></select><div class="input-group input-group-sm" style="width:235px"><span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span><input id="reportSearch" type="search" class="form-control" placeholder="ค้นหารหัสหรือชื่อพัสดุ"></div></div></div>
 <div class="table-responsive"><table class="table report-table table-hover align-middle mb-0" id="inventoryReportTable"><thead class="table-light"><tr><th class="ps-4">#</th><th>รหัสพัสดุ</th><th>รายการสิ่งของ</th><th>หมวดหมู่</th><th class="text-end">ยอดยกมา</th><th class="text-end text-success">รับเพิ่ม (+)</th><th class="text-end text-danger">จ่ายออก (-)</th><th class="text-end pe-4">คงเหลือ</th></tr></thead><tbody><?php foreach ($ledger as $index => $item): $isLow = $item['closing_stock'] <= (int)$item['low_stock_threshold']; ?><tr class="report-row <?= $isLow ? 'row-low-stock' : '' ?>" data-category="<?= htmlspecialchars($item['category_name']) ?>" data-search="<?= htmlspecialchars(mb_strtolower($item['item_code'] . ' ' . $item['name'], 'UTF-8')) ?>"><td class="ps-4 text-muted"><?= $index + 1 ?></td><td><span class="font-monospace small"><?= htmlspecialchars($item['item_code']) ?></span></td><td class="fw-semibold"><?= htmlspecialchars($item['name']) ?><?php if ($isLow): ?><span class="badge text-bg-warning ms-1">สต็อกต่ำ</span><?php endif; ?></td><td><span class="badge text-bg-light border text-secondary fw-normal"><?= htmlspecialchars($item['category_name']) ?></span></td><td class="text-end"><?= number_format($item['opening_stock']) ?></td><td class="text-end text-success fw-semibold"><?= $item['qty_in'] ? '+' . number_format($item['qty_in']) : '-' ?></td><td class="text-end text-danger fw-semibold"><?= $item['qty_out'] ? '-' . number_format($item['qty_out']) : '-' ?></td><td class="text-end pe-4 fw-bold"><?= number_format($item['closing_stock']) ?></td></tr><?php endforeach; ?><tr id="noReportResult" class="d-none"><td colspan="8" class="text-center text-muted py-4">ไม่พบรายการที่ค้นหา</td></tr></tbody><tfoot class="table-warning"><tr class="fw-bold"><td colspan="4" class="ps-4">รวมยอดทั้งสิ้น</td><td class="text-end"><?= number_format($totals['opening']) ?></td><td class="text-end text-success">+<?= number_format($totals['in']) ?></td><td class="text-end text-danger">-<?= number_format($totals['out']) ?></td><td class="text-end pe-4"><?= number_format($totals['closing']) ?></td></tr></tfoot></table></div>
 <div class="card-footer bg-white border-0 px-4 py-3 d-flex justify-content-between align-items-center"><span id="reportCount" class="small text-muted"></span><nav id="reportPagination"></nav></div></div>

<script>
document.addEventListener('DOMContentLoaded',function(){const rows=Array.from(document.querySelectorAll('.report-row')),search=document.getElementById('reportSearch'),category=document.getElementById('categoryReportFilter'),empty=document.getElementById('noReportResult'),count=document.getElementById('reportCount'),pager=document.getElementById('reportPagination'),perPage=10;let page=1;function render(){const term=(search.value||'').toLocaleLowerCase().trim(),filtered=rows.filter(row=>(category.value==='all'||row.dataset.category===category.value)&&row.dataset.search.includes(term)),pages=Math.max(1,Math.ceil(filtered.length/perPage));if(page>pages)page=pages;rows.forEach(row=>row.classList.add('d-none'));filtered.slice((page-1)*perPage,page*perPage).forEach(row=>row.classList.remove('d-none'));empty.classList.toggle('d-none',filtered.length!==0);count.textContent=`แสดง ${filtered.length?(page-1)*perPage+1:0}–${Math.min(page*perPage,filtered.length)} จาก ${filtered.length} รายการ`;pager.innerHTML=pages>1?Array.from({length:pages},(_,i)=>`<button type="button" class="btn btn-sm ${page===i+1?'btn-dark':'btn-outline-secondary'} ms-1" data-page="${i+1}">${i+1}</button>`).join(''):'';pager.querySelectorAll('[data-page]').forEach(button=>button.addEventListener('click',()=>{page=Number(button.dataset.page);render()}))}search.addEventListener('input',()=>{page=1;render()});category.addEventListener('change',()=>{page=1;render()});render();document.getElementById('exportExcel').addEventListener('click',function(){const data=[['รายงานสรุปการเบิกจ่าย','<?= htmlspecialchars($periodLabel, ENT_QUOTES) ?>'],[],['รหัสพัสดุ','รายการสิ่งของ','หมวดหมู่','ยอดยกมา','รับเพิ่ม','จ่ายออก','คงเหลือ']];rows.forEach(row=>{const cell=row.querySelectorAll('td');data.push([cell[1].innerText.trim(),cell[2].childNodes[0].textContent.trim(),cell[3].innerText.trim(),cell[4].innerText.trim(),cell[5].innerText.trim(),cell[6].innerText.trim(),cell[7].innerText.trim()])});const csv='\uFEFF'+data.map(line=>line.map(value=>`"${String(value).replaceAll('"','""')}"`).join(',')).join('\n'),link=document.createElement('a');link.href=URL.createObjectURL(new Blob([csv],{type:'text/csv;charset=utf-8;'}));link.download='inventory-report-<?= $selectedYear ?>-<?= str_pad((string)$selectedMonth, 2, '0', STR_PAD_LEFT) ?>.csv';link.click();URL.revokeObjectURL(link.href)})});
</script>
