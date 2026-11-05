<?php
/**
 * actions/admin/export_inventory_excel.php
 * Backend Action สำหรับดาวน์โหลดเอกสารรายงานสรุปการเบิกจ่ายพัสดุเป็นไฟล์ CSV/Excel
 * รองรับภาษาไทย 100% ด้วย UTF-8 BOM และดึงข้อมูลพัสดุครบทั้งหมดตามช่วงเวลาที่เลือก
 */

session_start();
require_once '../../config/db.php';
require_once '../../includes/inventory_report_helper.php';

// ตรวจสอบสิทธิ์การเข้าใช้งานเฉพาะ Admin
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    die("Access Denied: เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถดาวน์โหลดรายงานได้");
}

$reportMode   = ($_GET['report_mode'] ?? 'monthly') === 'fiscal' ? 'fiscal' : 'monthly';
$selectedYear  = isset($_GET['year']) ? (int)$_GET['year'] : null;
$selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : null;

$data = getInventoryReportData($pdo, $reportMode, $selectedYear, $selectedMonth);
$periodLabel = $data['periodLabel'];
$totals      = $data['totals'];
$ledger      = $data['ledger'];

$fileName = 'inventory_report_' . ($reportMode === 'fiscal' ? 'fiscal_' . $selectedYear : 'monthly_' . $selectedYear . '_' . str_pad((string)$selectedMonth, 2, '0', STR_PAD_LEFT)) . '.xls';

// CSV ไม่สามารถกำหนดฟอนต์หรือรูปแบบตารางได้ จึงส่งออกเป็น Excel-compatible HTML
// เพื่อให้ Excel เปิดเป็นรายงานที่ใช้ TH Sarabun New และรูปแบบเดียวกับ PDF
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Pragma: no-cache');
header('Expires: 0');

$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$printDate = date('d/m/') . (date('Y') + 543) . ' เวลา ' . date('H:i') . ' น.';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <style>
        body, table, td, th { font-family: "TH Sarabun New", "THSarabunNew", sans-serif; color: #212529; }
        body { font-size: 16pt; }
        .title { text-align: center; font-size: 22pt; font-weight: bold; }
        .subtitle { text-align: center; font-size: 16pt; color: #555; }
        .meta { margin: 12px 0; width: 100%; }
        .meta td { border: 0; padding: 2px 0; }
        .summary, .report-table { width: 100%; border-collapse: collapse; }
        .summary { margin: 10px 0 15px; }
        .summary td { border: 1px solid #adb5bd; padding: 8px; text-align: center; }
        .summary-label { font-size: 14pt; }
        .summary-value { font-size: 20pt; font-weight: bold; }
        .opening { background: #e7f1ff; color: #084298; }
        .incoming { background: #d1e7dd; color: #0f5132; }
        .outgoing { background: #f8d7da; color: #842029; }
        .closing { background: #fff3cd; color: #664d03; border: 2px solid #ffc107 !important; }
        .report-table th { background: #e9ecef; border: 1px solid #6c757d; padding: 6px; text-align: center; font-weight: bold; }
        .report-table td { border: 1px solid #adb5bd; padding: 5px 7px; }
        .right { text-align: right; } .center { text-align: center; }
        .in { color: #0f5132; font-weight: bold; } .out { color: #842029; font-weight: bold; }
        .total td { background: #fff3cd; font-weight: bold; border-top: 2px solid #b8860b; }
    </style>
</head>
<body>
    <div class="title">รายงานสรุปการเบิกจ่ายพัสดุและยอดคงเหลือ</div>
    <div class="subtitle">ระบบบริหารจัดการสิ่งพิมพ์และของที่ระลึก (OAP Inventory System)</div>
    <table class="meta">
        <tr><td><strong>ช่วงเวลา:</strong> <?= $escape($periodLabel) ?></td><td style="text-align:right">ข้อมูล ณ วันที่ <?= $escape($printDate) ?> | ทั้งหมด <?= count($ledger) ?> รายการ</td></tr>
    </table>
    <table class="summary"><tr>
        <td class="opening"><div class="summary-label">ยอดยกมา (ต้นงวด)</div><div class="summary-value"><?= number_format($totals['opening']) ?></div></td>
        <td class="incoming"><div class="summary-label">รับเพิ่ม (+)</div><div class="summary-value">+<?= number_format($totals['in']) ?></div></td>
        <td class="outgoing"><div class="summary-label">จ่ายออก (-)</div><div class="summary-value">-<?= number_format($totals['out']) ?></div></td>
        <td class="closing"><div class="summary-label">คงเหลือสุทธิ</div><div class="summary-value"><?= number_format($totals['closing']) ?></div></td>
    </tr></table>
    <table class="report-table">
        <thead><tr><th>#</th><th>รหัสพัสดุ</th><th>รายการสิ่งของ</th><th>หมวดหมู่</th><th>ยอดยกมา</th><th>รับเพิ่ม (+)</th><th>จ่ายออก (-)</th><th>คงเหลือ</th></tr></thead>
        <tbody>
        <?php foreach ($ledger as $index => $item): ?>
            <tr><td class="center"><?= $index + 1 ?></td><td class="center"><?= $escape($item['item_code']) ?></td><td><?= $escape($item['name']) ?></td><td><?= $escape($item['category_name']) ?></td><td class="right"><?= number_format($item['opening_stock']) ?></td><td class="right in"><?= $item['qty_in'] ? '+' . number_format($item['qty_in']) : '-' ?></td><td class="right out"><?= $item['qty_out'] ? '-' . number_format($item['qty_out']) : '-' ?></td><td class="right"><strong><?= number_format($item['closing_stock']) ?></strong></td></tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr class="total"><td colspan="4">รวมยอดทั้งสิ้น (<?= count($ledger) ?> รายการ)</td><td class="right"><?= number_format($totals['opening']) ?></td><td class="right in">+<?= number_format($totals['in']) ?></td><td class="right out">-<?= number_format($totals['out']) ?></td><td class="right"><?= number_format($totals['closing']) ?></td></tr></tfoot>
    </table>
</body>
</html>
<?php
exit;

