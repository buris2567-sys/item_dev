<?php
session_start();

// 1. ปรับ Path ถอยหลังแค่ 1 ชั้น
require_once '../config/db.php';
require_once '../vendor/autoload.php'; 

// ตรวจสอบว่าล็อกอินหรือยัง
if (!isset($_SESSION['user_id'])) {
    die("Access Denied: กรุณาเข้าสู่ระบบ");
}

$request_id = $_GET['id'] ?? '';
if (empty($request_id)) {
    die("ไม่พบรหัสคำร้อง");
}

// ดึงข้อมูลคำร้อง
$stmt = $pdo->prepare("
    SELECT r.*, u.full_name 
    FROM requests r 
    LEFT JOIN users u ON r.user_id = u.user_id 
    WHERE r.request_id = ?
");
$stmt->execute([$request_id]);
$request = $stmt->fetch();

if (!$request) {
    die("ไม่พบข้อมูลคำร้องนี้");
}
// ดึงข้อมูลรายการสิ่งของในคำร้องนี้
$itemStmt = $pdo->prepare("
    SELECT ri.requested_qty, ri.approved_qty, i.name AS item_name 
    FROM request_items ri 
    LEFT JOIN items i ON ri.item_id = i.id 
    WHERE ri.request_id = ?
");
$itemStmt->execute([$request_id]);
$items = $itemStmt->fetchAll();

// สร้างตัวแปรเก็บ HTML ของแถวตาราง
$tableRows = '';
$i = 1;
foreach ($items as $item) {
    // ถ้าจำนวนอนุมัติเป็น 0 หรือว่าง ให้แสดงผลเป็น '-' เพื่อความสวยงาม
    $appQty = ($item['approved_qty'] > 0) ? $item['approved_qty'] : '-';
    
    $tableRows .= '
    <tr>
        <td>' . $i . '</td>
        <td style="text-align: left;">' . htmlspecialchars($item['item_name']) . '</td>
        <td>' . $item['requested_qty'] . '</td>
        <td>' . $appQty . '</td>
    </tr>';
    $i++;
}

// ถ้าไม่มีของในตะกร้าเลย (เผื่อไว้)
if (empty($items)) {
    $tableRows = '<tr><td colspan="4">ไม่มีรายการสิ่งของ</td></tr>';
}
// 2. ตรวจสอบสิทธิ์ความปลอดภัย: ถ้าไม่ใช่ Admin ต้องเป็นเจ้าของใบเบิกเท่านั้นถึงจะโหลดได้
if ($_SESSION['role'] !== 'Admin' && $request['user_id'] != $_SESSION['user_id']) {
    die("Access Denied: คุณไม่มีสิทธิ์ดาวน์โหลดเอกสารของบุคคลอื่น");
}

// ตั้งค่า mPDF และฟอนต์ภาษาไทย
$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

$mpdf = new \Mpdf\Mpdf([
    'fontDir' => array_merge($fontDirs, [
        __DIR__ . '/../assets/fonts', // 🟢 ถอยหลัง 1 ชั้น
    ]),
    'fontdata' => $fontData + [
        'thsarabunnew' => [
            'R' => 'THSarabunNew.ttf' 
            // 🟢 ลบ B, I, BI ออกไปก่อน เพราะเรายังไม่มีไฟล์
        ]
    ],
    'default_font' => 'thsarabunnew',
    'format' => 'A4',
]);

// ออกแบบหน้าตา HTML ของ PDF
$html = '
<style>
    body { font-family: "thsarabunnew"; font-size: 16pt; }
    h1 { text-align: center; font-size: 22pt; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #000; padding: 8px; text-align: center; }
    th { background-color: #f2f2f2; }
</style>

<h1>ใบเบิกสิ่งพิมพ์และของที่ระลึก</h1>
<p><b>เลขที่ใบเบิก:</b> ' . htmlspecialchars($request['request_no']) . '</p>
<p><b>ผู้เบิก:</b> ' . htmlspecialchars($request['full_name']) . '</p>
<p><b>วันที่ต้องการใช้งาน:</b> ' . date('d/m/Y', strtotime($request['use_date'])) . '</p>
<p><b>รายละเอียดงาน:</b> ' . htmlspecialchars($request['event_name']) . '</p>

<table>
    <thead>
        <tr>
            <th style="width: 10%;">ลำดับ</th>
            <th style="width: 50%;">รายการ</th>
            <th style="width: 20%;">จำนวนที่ขอ</th>
            <th style="width: 20%;">จำนวนที่อนุมัติ</th>
        </tr>
    </thead>
    <tbody>
        ' . $tableRows . '
    </tbody>
</table>

<br><br><br>
<table style="border: none;">
    <tr>
        <td style="border: none; text-align: center;">
            <p>ลงชื่อ.......................................................ผู้เบิก</p>
            <p>(' . htmlspecialchars($request['full_name']) . ')</p>
        </td>
        <td style="border: none; text-align: center;">
            <p>ลงชื่อ.......................................................ผู้อนุมัติ</p>
            <p>(.......................................................)</p>
        </td>
    </tr>
</table>
';

// สั่งสร้าง PDF
$mpdf->WriteHTML($html);
$mpdf->Output('Request_'.$request['request_no'].'.pdf', 'I');