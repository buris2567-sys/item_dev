<?php
/**
 * actions/admin/export_inventory_pdf.php
 * Backend Action สำหรับสร้างและดาวน์โหลดเอกสารรายงานสรุปการเบิกจ่ายพัสดุเป็น PDF (mPDF)
 * ดึงข้อมูลทั้งหมด 100% ตามรอบเดือนหรือปีงบประมาณที่เลือก
 */

session_start();
require_once '../../config/db.php';
require_once '../../vendor/autoload.php';
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

// ตั้งค่า mPDF พร้อมฟอนต์ภาษาไทย THSarabunNew ในแนวนอน (A4-L)
$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

$mpdf = new \Mpdf\Mpdf([
    'fontDir' => array_merge($fontDirs, [
        __DIR__ . '/../../assets/fonts',
    ]),
    'fontdata' => $fontData + [
        'thsarabunnew' => [
            'R'  => 'THSarabunNew.ttf',
            'B'  => 'THSarabunNew Bold.ttf',
            'I'  => 'THSarabunNew Italic.ttf',
            'BI' => 'THSarabunNew BoldItalic.ttf'
        ]
    ],
    'default_font' => 'thsarabunnew',
    'format'       => 'A4-L',
    'margin_left'  => 12,
    'margin_right' => 12,
    'margin_top'   => 12,
    'margin_bottom'=> 12,
]);

$mpdf->SetTitle('รายงานสรุปการเบิกจ่ายพัสดุ');
$mpdf->SetHTMLFooter('
    <div style="font-family: thsarabunnew; font-size: 11pt; color: #555; border-top: 0.4pt solid #999; padding-top: 3pt;">
        รายงานสรุปการเบิกจ่ายพัสดุ <span style="float: right;">หน้า {PAGENO} / {nbpg}</span>
    </div>
');

$printDate = date('d/m/') . (date('Y') + 543) . ' เวลา ' . date('H:i') . ' น.';

// ตกแต่งสไตล์ CSS ของเอกสาร PDF
$html = '
<style>
    body {
        font-family: "thsarabunnew";
        font-size: 15pt;
        color: #212529;
        line-height: 1.2;
    }
    .header-box {
        text-align: center;
        margin-bottom: 12px;
    }
    .main-title {
        font-size: 20pt;
        font-weight: bold;
        margin: 0;
        color: #1a1a1a;
    }
    .sub-title {
        font-size: 15pt;
        color: #555555;
        margin-top: 2px;
    }
    .meta-box {
        font-size: 13pt;
        margin-bottom: 12px;
    }
    
    /* การ์ดสรุปยอด 4 ช่อง */
    table.summary-cards {
        width: 100%;
        border-collapse: separate;
        border-spacing: 8px;
        margin-bottom: 12px;
    }
    table.summary-cards td {
        padding: 8px 12px;
        border-radius: 6px;
        text-align: center;
    }
    .card-opening { background-color: #e7f1ff; border: 1px solid #b6d4fe; }
    .card-in      { background-color: #d1e7dd; border: 1px solid #a3cfbb; }
    .card-out     { background-color: #f8d7da; border: 1px solid #f1aeb5; }
    .card-closing { background-color: #fff3cd; border: 2px solid #ffc107; }

    /* ตารางรายการ */
    table.data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 5px;
    }
    table.data-table th, table.data-table td {
        border: 1px solid #adb5bd;
        padding: 5px 7px;
        font-size: 13pt;
    }
    table.data-table thead { display: table-header-group; }
    table.data-table tr { page-break-inside: avoid; }
    table.data-table th {
        background-color: #f1f3f5;
        font-weight: bold;
        text-align: center;
    }
    table.data-table tfoot td {
        background-color: #fff3cd;
        font-weight: bold;
    }
    .text-center { text-align: center; }
    .text-right  { text-align: right; }
    .text-left   { text-align: left; }
    .text-success{ color: #198754; font-weight: bold; }
    .text-danger { color: #dc3545; font-weight: bold; }
    .document-rule { border-top: 1px solid #333; margin: 8px 0 10px; }
</style>

<div class="header-box">
    <div class="main-title">รายงานสรุปการเบิกจ่ายพัสดุและยอดคงเหลือ</div>
    <div class="sub-title">ระบบบริหารจัดการสิ่งพิมพ์และของที่ระลึก (OAP Inventory System)</div>
</div>

<div class="document-rule"></div>

<table style="width: 100%;" class="meta-box">
    <tr>
        <td style="width: 50%; text-align: left; font-size: 14pt;">
            <strong>ช่วงเวลา:</strong> ' . htmlspecialchars($periodLabel) . '
        </td>
        <td style="width: 50%; text-align: right; color: #555;">
            ข้อมูล ณ วันที่ ' . $printDate . ' | ทั้งหมด ' . count($ledger) . ' รายการ
        </td>
    </tr>
</table>

<!-- สรุปตัวเลขยอดรวมทั้งสิ้น -->
<table class="summary-cards">
    <tr>
        <td class="card-opening" style="width: 25%;">
            <div style="font-size: 12pt; color: #084298;">จำนวนเดิมที่มี (ต้นงวด)</div>
            <div style="font-size: 18pt; font-weight: bold; color: #084298;">' . number_format($totals['opening']) . '</div>
        </td>
        <td class="card-in" style="width: 25%;">
            <div style="font-size: 12pt; color: #0f5132;">รับเพิ่ม (+) ระหว่างงวด</div>
            <div style="font-size: 18pt; font-weight: bold; color: #0f5132;">+' . number_format($totals['in']) . '</div>
        </td>
        <td class="card-out" style="width: 25%;">
            <div style="font-size: 12pt; color: #842029;">จ่ายออก (-) ระหว่างงวด</div>
            <div style="font-size: 18pt; font-weight: bold; color: #842029;">-' . number_format($totals['out']) . '</div>
        </td>
        <td class="card-closing" style="width: 25%;">
            <div style="font-size: 12pt; color: #664d03;">คงเหลือสุทธิ (ปลายงวด)</div>
            <div style="font-size: 18pt; font-weight: bold; color: #664d03;">' . number_format($totals['closing']) . '</div>
        </td>
    </tr>
</table>

<!-- ตารางแสดงรายการข้อมูลทั้งหมด 100% -->
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 45px;">#</th>
            <th style="width: 100px;">รหัสพัสดุ</th>
            <th style="text-align: left;">รายการสิ่งของ</th>
            <th style="width: 160px; text-align: left;">หมวดหมู่</th>
            <th style="width: 95px;" class="text-right">จำนวนเดิมที่มี</th>
            <th style="width: 90px;" class="text-right">รับเพิ่ม (+)</th>
            <th style="width: 90px;" class="text-right">จ่ายออก (-)</th>
            <th style="width: 95px;" class="text-right">คงเหลือ</th>
        </tr>
    </thead>
    <tbody>';

if (!empty($ledger)) {
    foreach ($ledger as $i => $row) {
        $qtyIn  = $row['qty_in'] ? '+' . number_format($row['qty_in']) : '-';
        $qtyOut = $row['qty_out'] ? '-' . number_format($row['qty_out']) : '-';

        $html .= '
        <tr>
            <td class="text-center">' . ($i + 1) . '</td>
            <td class="text-center">' . htmlspecialchars($row['item_code']) . '</td>
            <td class="text-left">' . htmlspecialchars($row['name']) . '</td>
            <td class="text-left">' . htmlspecialchars($row['category_name']) . '</td>
            <td class="text-right">' . number_format($row['opening_stock']) . '</td>
            <td class="text-right text-success">' . $qtyIn . '</td>
            <td class="text-right text-danger">' . $qtyOut . '</td>
            <td class="text-right" style="font-weight: bold;">' . number_format($row['closing_stock']) . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="8" class="text-center py-4">ไม่พบข้อมูลพัสดุในช่วงเวลานี้</td></tr>';
}

$html .= '
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="text-left" style="font-size: 14pt;">รวมยอดทั้งสิ้น (' . count($ledger) . ' รายการ)</td>
            <td class="text-right">' . number_format($totals['opening']) . '</td>
            <td class="text-right text-success">+' . number_format($totals['in']) . '</td>
            <td class="text-right text-danger">-' . number_format($totals['out']) . '</td>
            <td class="text-right" style="font-size: 14pt;">' . number_format($totals['closing']) . '</td>
        </tr>
    </tfoot>
</table>

<table style="width: 100%; margin-top: 30px; border-collapse: collapse;">
    <tr>
        <td style="width: 50%; text-align: center; vertical-align: top;">
            ลงชื่อ..............................................................<br>
            ( ' . htmlspecialchars($_SESSION['full_name'] ?? '..............................................................') . ' )<br>
            ผู้จัดทำรายงาน
        </td>
        <td style="width: 50%; text-align: center; vertical-align: top;">
            ลงชื่อ..............................................................<br>
            ( .............................................................. )<br>
            ผู้อนุมัติ / หัวหน้างาน
        </td>
    </tr>
</table>
';

$mpdf->WriteHTML($html);

$fileName = 'inventory_report_' . ($reportMode === 'fiscal' ? 'fiscal_' . $selectedYear : 'monthly_' . $selectedYear . '_' . $selectedMonth) . '.pdf';
$mpdf->Output($fileName, 'I');
exit;

