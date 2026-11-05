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

$fileName = 'inventory_report_' . ($reportMode === 'fiscal' ? 'fiscal_' . $selectedYear : 'monthly_' . $selectedYear . '_' . str_pad((string)$selectedMonth, 2, '0', STR_PAD_LEFT)) . '.csv';

// ตั้งค่า Header สำหรับดาวน์โหลดไฟล์ CSV
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// ใส่ UTF-8 BOM เพื่อให้โปรแกรม Microsoft Excel เปิดภาษาไทยได้โดยไม่เพี้ยน
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// 1. หัวเรื่องรายงาน
fputcsv($output, ['รายงานสรุปการเบิกจ่ายพัสดุและยอดคงเหลือ']);
fputcsv($output, ['ระบบบริหารจัดการสิ่งพิมพ์และของที่ระลึก (OAP Inventory System)']);
fputcsv($output, ['ช่วงเวลา:', $periodLabel]);
$printDate = date('d/m/') . (date('Y') + 543) . ' ' . date('H:i') . ' น.';
fputcsv($output, ['ข้อมูล ณ วันที่:', $printDate, 'จำนวนรายการทั้งหมด:', count($ledger) . ' รายการ']);
fputcsv($output, []); // บรรทัดว่าง

// 2. สรุปยอดรวม 4 กล่อง
fputcsv($output, ['สรุปภาพรวมยอดพัสดุ']);
fputcsv($output, ['จำนวนเดิมที่มี (ต้นงวด)', 'รับเพิ่ม (+) ระหว่างงวด', 'จ่ายออก (-) ระหว่างงวด', 'คงเหลือสุทธิ (ปลายงวด)']);
fputcsv($output, [$totals['opening'], $totals['in'], $totals['out'], $totals['closing']]);
fputcsv($output, []); // บรรทัดว่าง

// 3. หัวคอลัมน์ตาราง
fputcsv($output, [
    'ลำดับ',
    'รหัสพัสดุ',
    'รายการสิ่งของ',
    'หมวดหมู่',
    'จำนวนเดิมที่มี (ต้นงวด)',
    'รับเพิ่ม (+)',
    'จ่ายออก (-)',
    'คงเหลือสุทธิ (ปลายงวด)'
]);

// 4. ข้อมูลพัสดุทั้งหมด 100%
if (!empty($ledger)) {
    foreach ($ledger as $index => $item) {
        fputcsv($output, [
            $index + 1,
            $item['item_code'],
            $item['name'],
            $item['category_name'],
            $item['opening_stock'],
            $item['qty_in'],
            $item['qty_out'],
            $item['closing_stock']
        ]);
    }
}

// 5. แถวรวมยอดทั้งสิ้น
fputcsv($output, [
    'รวมยอดทั้งสิ้น',
    '',
    count($ledger) . ' รายการ',
    '',
    $totals['opening'],
    $totals['in'],
    $totals['out'],
    $totals['closing']
]);

fclose($output);
exit;

