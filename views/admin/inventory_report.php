<?php
// ตรวจสอบความปลอดภัยและสิทธิ์ Admin
if (!defined('APP_RUNNING')) exit('Forbidden');
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') exit('Unauthorized');

// ดึงข้อมูลผ่าน Service Helper ส่วนกลาง
require_once 'includes/inventory_report_helper.php';
$reportData = getInventoryReportData($pdo);
extract($reportData);
?>
<!-- //css -->
<link rel="stylesheet" href="assets/css/inventory_report.css">

<style>
    .report-tabs .btn {
        border-radius: .65rem;
    }
    .report-tabs .btn.active {
        background: #ffc107;
        border-color: #ffc107;
        color: #212529;
    }
    .report-stat-card,
    .report-chart-card {
        border: 0;
        border-radius: 1rem;
    }
    .report-stat-card {
        min-height: 142px;
    }
    .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: .75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .stat-number {
        font-size: clamp(1.55rem, 2.2vw, 2.15rem);
        letter-spacing: -.04em;
    }
    .top-item-label {
        max-width: 70%;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    .top-item-bar {
        height: 9px;
        border-radius: 999px;
        background: #f1f3f5;
        overflow: hidden;
    }
    .top-item-bar span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #fb8b8b, #e85d5d);
    }
    .report-table th {
        white-space: nowrap;
        font-size: .85rem;
    }
    .row-low-stock td {
        background-color: #fff8e8 !important;
    }
    @media print {
        .sidebar-wrapper,
        .report-toolbar,
        .report-actions,
        #reportSearch,
        #categoryReportFilter,
        #inventoryReportPaginationWrapper {
            display: none !important;
        }
        .container-fluid, body {
            background: #fff !important;
        }
        .col.p-4 {
            padding: 0 !important;
        }
        .card {
            box-shadow: none !important;
            break-inside: avoid;
        }
        .report-table {
            font-size: 9pt;
        }
    }
</style>

<!-- ส่วนหัวเรื่องหน้า -->
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-2" style="border-bottom: 2px solid #e9ecef;">
    <div>
        <h3 class="fw-bolder mb-1 text-dark">
            <i class="bi bi-clipboard-data-fill text-warning me-2"></i>สรุปรายงานการเบิกจ่าย
        </h3>
        <p class="mb-0 text-muted small">ภาพรวมการเคลื่อนไหวและยอดคงเหลือของคลังพัสดุ</p>
    </div>
    <div class="d-flex flex-column align-items-md-end gap-1">
        <span class="badge text-bg-light border text-secondary rounded-pill px-3 py-2 shadow-sm">
            <i class="bi bi-calendar3 me-1"></i>ข้อมูล ณ <?= htmlspecialchars($periodLabel) ?>
        </span>
    </div>
</div>

<!-- แถบเครื่องมือ: สลับโหมด, เลือกช่วงเวลา, ปุ่ม Export -->
<div class="card border-0 rounded-4 shadow-sm mb-4 report-toolbar bg-white">
    <div class="card-body p-3 d-flex flex-wrap align-items-center gap-2">
        <!-- ปุ่มสลับโหมดรายเดือน / ปีงบประมาณ -->
        <div class="btn-group report-tabs shadow-sm">
            <a href="index.php?page=inventory_report&report_mode=monthly&month=<?= $selectedMonth ?>&year=<?= $selectedYear ?>" 
               class="btn btn-outline-dark btn-sm fw-semibold <?= $reportMode === 'monthly' ? 'active' : '' ?>">
                สรุปรายเดือน
            </a>
            <a href="index.php?page=inventory_report&report_mode=fiscal&year=<?= $selectedYear ?>" 
               class="btn btn-outline-dark btn-sm fw-semibold <?= $reportMode === 'fiscal' ? 'active' : '' ?>">
                สรุปปีงบประมาณ
            </a>
        </div>

        <!-- ฟอร์มเลือกเดือน/ปี -->
        <form method="get" class="d-flex flex-wrap align-items-center gap-2 ms-lg-2">
            <input type="hidden" name="page" value="inventory_report">
            <input type="hidden" name="report_mode" value="<?= htmlspecialchars($reportMode) ?>">

            <?php if ($reportMode === 'monthly'): ?>
                <select name="month" class="form-select form-select-sm rounded-pill shadow-sm" style="width:142px">
                    <?php foreach ($monthOptions as $number => $name): ?>
                        <option value="<?= $number ?>" <?= $selectedMonth === $number ? 'selected' : '' ?>><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <select name="year" class="form-select form-select-sm rounded-pill shadow-sm" style="width:125px">
                <?php foreach ($yearOptions as $year): ?>
                    <option value="<?= $year ?>" <?= $selectedYear === $year ? 'selected' : '' ?>>พ.ศ. <?= $year ?></option>
                <?php endforeach; ?>
            </select>

            <button class="btn btn-warning btn-sm fw-bold px-3 rounded-pill shadow-sm">
                <i class="bi bi-search me-1"></i>ค้นหา
            </button>
        </form>

        <!-- ปุ่ม Export PDF และ Excel (เชื่อมกับ Backend Actions ดึงข้อมูลครบ 100%) -->
        <div class="ms-lg-auto d-flex gap-2 report-actions">
            <a href="actions/admin/export_inventory_pdf.php?report_mode=<?= urlencode($reportMode) ?>&month=<?= (int)$selectedMonth ?>&year=<?= (int)$selectedYear ?>" 
               target="_blank" 
               class="btn btn-outline-danger btn-sm fw-semibold rounded-pill px-3 shadow-sm d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
            <a href="actions/admin/export_inventory_excel.php?report_mode=<?= urlencode($reportMode) ?>&month=<?= (int)$selectedMonth ?>&year=<?= (int)$selectedYear ?>" 
               class="btn btn-outline-success btn-sm fw-semibold rounded-pill px-3 shadow-sm d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export Excel
            </a>
        </div>
    </div>
</div>

<!-- ส่วนที่ 1: การ์ดสรุปยอดรวม 4 กล่อง -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card report-stat-card shadow-sm bg-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-box-seam"></i></span>
                    <span class="small text-muted fw-semibold">ต้นงวด</span>
                </div>
                <div class="stat-number fw-bold mt-3 text-dark"><?= number_format($totals['opening']) ?></div>
                <div class="small text-muted">จำนวนเดิมที่มี</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card report-stat-card shadow-sm bg-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-box-arrow-in-down"></i></span>
                    <span class="small text-success fw-semibold">เพิ่มเข้า</span>
                </div>
                <div class="stat-number fw-bold text-success mt-3">+<?= number_format($totals['in']) ?></div>
                <div class="small text-muted">รับเข้าระหว่างงวด</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card report-stat-card shadow-sm bg-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-box-arrow-up"></i></span>
                    <span class="small text-danger fw-semibold">จ่ายออก</span>
                </div>
                <div class="stat-number fw-bold text-danger mt-3">-<?= number_format($totals['out']) ?></div>
                <div class="small text-muted">เบิกจ่ายระหว่างงวด</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card report-stat-card shadow-sm border border-warning border-2 bg-white">
            <div class="card-body p-4 bg-warning bg-opacity-10 rounded-4">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="stat-icon bg-warning text-dark"><i class="bi bi-box-seam"></i></span>
                    <span class="small text-dark fw-bold">คงเหลือ</span>
                </div>
                <div class="stat-number fw-bold mt-3 text-dark"><?= number_format($totals['closing']) ?></div>
                <div class="small text-muted">คงเหลือสุทธิปลายงวด</div>
            </div>
        </div>
    </div>
</div>

<!-- ส่วนที่ 2: กราฟ Top 5 พัสดุที่มีการเบิกจ่ายสูงสุด และ ข้อมูลสำคัญของคลัง -->
<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card report-chart-card shadow-sm h-100 bg-white">
            <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-bar-chart-fill text-danger me-2"></i>Top 5 พัสดุที่มีการเบิกจ่ายสูงสุด
                </h6>
                <span class="small text-muted"><?= htmlspecialchars($periodLabel) ?></span>
            </div>
            <div class="card-body px-4 pb-4">
                <?php if ($topItems): ?>
                    <?php foreach ($topItems as $index => $item): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between gap-3 small mb-1">
                                <span class="top-item-label" title="<?= htmlspecialchars($item['name']) ?>">
                                    <?= $index + 1 ?>. <?= htmlspecialchars($item['name']) ?>
                                </span>
                                <strong class="text-danger">-<?= number_format($item['qty_out']) ?></strong>
                            </div>
                            <div class="top-item-bar">
                                <span style="width:<?= number_format(($item['qty_out'] / $topMax) * 100, 2) ?>%"></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="py-5 text-center text-muted">
                        <i class="bi bi-bar-chart fs-1 d-block opacity-25 mb-2"></i>
                        ยังไม่มีการจ่ายออกในช่วงเวลานี้
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-5">
        <div class="card report-chart-card shadow-sm h-100 bg-white">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-lightning-charge-fill text-warning me-2"></i>ข้อมูลสำคัญของคลัง
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="d-flex justify-content-between py-3 border-bottom">
                    <span class="text-muted"><i class="bi bi-boxes me-2"></i>จำนวนรายการพัสดุ</span>
                    <strong class="text-dark"><?= number_format(count($ledger)) ?> รายการ</strong>
                </div>
                <div class="d-flex justify-content-between py-3 border-bottom">
                    <span class="text-muted"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>รายการสต็อกต่ำ</span>
                    <strong class="<?= $lowStockCount ? 'text-danger' : 'text-success' ?>"><?= number_format($lowStockCount) ?> รายการ</strong>
                </div>
                <div class="d-flex justify-content-between py-3">
                    <span class="text-muted"><i class="bi bi-arrow-left-right me-2 text-primary"></i>การเคลื่อนไหวรวม</span>
                    <strong class="text-dark"><?= number_format($totals['in'] + $totals['out']) ?> หน่วย</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ส่วนที่ 3: คอมโพเนนต์ตารางแสดงรายละเอียดรายการคงคลัง (แยกไฟล์ Component) -->
<?php include 'components/tables/inventory_report_table.php'; ?>
