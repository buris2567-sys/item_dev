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

// ตรวจสอบสิทธิ์ความปลอดภัย: ถ้าไม่ใช่ Admin ต้องเป็นเจ้าของใบเบิกเท่านั้นถึงจะโหลดได้
if (($_SESSION['role'] ?? '') !== 'Admin' && $request['user_id'] != $_SESSION['user_id']) {
    die("Access Denied: คุณไม่มีสิทธิ์ดาวน์โหลดเอกสารของบุคคลอื่น");
}

// 1. ดึงข้อมูลรายการสิ่งของในคำร้องนี้
$itemStmt = $pdo->prepare("
    SELECT ri.requested_qty, ri.approved_qty, i.name AS item_name 
    FROM request_items ri 
    LEFT JOIN items i ON ri.item_id = i.id 
    WHERE ri.request_id = ?
");
$itemStmt->execute([$request_id]);
$items = $itemStmt->fetchAll();

// 2. ฟังก์ชันช่วยแปลงวันที่เป็นภาษาไทย
$thai_months = [
    1=>'มกราคม', 2=>'กุมภาพันธ์', 3=>'มีนาคม', 4=>'เมษายน', 5=>'พฤษภาคม', 6=>'มิถุนายน', 
    7=>'กรกฎาคม', 8=>'สิงหาคม', 9=>'กันยายน', 10=>'ตุลาคม', 11=>'พฤศจิกายน', 12=>'ธันวาคม'
];
$req_time = strtotime($request['request_date']);
$req_d = date('d', $req_time);
$req_m = $thai_months[(int)date('m', $req_time)];
$req_y = date('Y', $req_time) + 543;

$use_time = strtotime($request['use_date']);
$use_date_thai = date('d', $use_time) . ' ' . $thai_months[(int)date('m', $use_time)] . ' ' . (date('Y', $use_time) + 543);

// 3. สร้างตารางรายการสิ่งของ (ลูปข้อมูล)
$tableRows = '';
foreach ($items as $item) {
    $appQty = ($item['approved_qty'] > 0) ? $item['approved_qty'] : '-';
    $tableRows .= '
    <tr>
        <td style="text-align: left;">' . htmlspecialchars($item['item_name']) . '</td>
        <td style="text-align: center;">' . $item['requested_qty'] . '</td>
        <td style="text-align: center;">' . $appQty . '</td>
    </tr>';
}
if (empty($items)) {
    $tableRows = '<tr><td colspan="3" style="text-align: center;">ไม่มีรายการสิ่งของ</td></tr>';
}

// 4. ตั้งค่า mPDF และฟอนต์ภาษาไทย
$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

$mpdf = new \Mpdf\Mpdf([
    'fontDir' => array_merge($fontDirs, [
        __DIR__ . '/../assets/fonts', 
    ]),
    'fontdata' => $fontData + [
        'thsarabunnew' => [
            'R' => 'THSarabunNew.ttf'
        ]
    ],
    'default_font' => 'thsarabunnew',
    'format' => 'A4',
]);

// 5. ออกแบบหน้าตา HTML ของ PDF (โค้ดที่แก้เรื่องตัวหนังสือเรียงตั้งแล้ว)
$html = '
<style>
    body { font-family: "thsarabunnew"; font-size: 16pt; line-height: 1.2; }
    h1 { text-align: center; font-size: 20pt; font-weight: bold; margin-bottom: 20px; }
    
    /* สไตล์สำหรับโครงสร้างฟอร์มเส้นประ */
    table.form-layout { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
    table.form-layout td { vertical-align: bottom; padding: 2px 0; border: none; }
    
    /* 🟢 เอา width: 1% และ nowrap ออก เพื่อแก้ปัญหาตัวอักษรเรียงแนวตั้ง */
    .label { font-weight: bold; padding-right: 5px; } 
    .line { border-bottom: 1px dotted #000; color: #333; }
    
    /* สไตล์สำหรับตารางรายการของ */
    table.items { width: 100%; border-collapse: collapse; margin-top: 15px; }
    table.items th, table.items td { border: 1px solid #000; padding: 5px; }
    table.items th { background-color: #d9d9d9; text-align: center; font-weight: bold; }
</style>

<h1>แบบฟอร์มเบิกเอกสารเผยแพร่และของที่ระลึก</h1>

<!-- ส่วนวันที่ด้านบน -->
<table class="form-layout" style="width: 60%; margin: 0 auto 15px auto;">
    <tr>
        <td class="label" style="text-align: right; width: 15%;">วันที่</td>
        <td class="line" style="text-align: center; width: 15%;">' . $req_d . '</td>
        <td class="label" style="text-align: right; padding-left: 10px; width: 15%;">เดือน</td>
        <td class="line" style="text-align: center; width: 35%;">' . $req_m . '</td>
        <td class="label" style="text-align: right; padding-left: 10px; width: 10%;">พ.ศ.</td>
        <td class="line" style="text-align: center; width: 10%;">' . $req_y . '</td>
    </tr>
</table>

<!-- ส่วนข้อมูลผู้เบิก -->
<table class="form-layout">
    <tr>
        <td colspan="2" style="padding-bottom: 10px;">
            <span style="padding-left: 30px;"><b>เรียน</b> หัวหน้ากลุ่มส่งเสริมฝึกอบรมและเผยแพร่ประชาสัมพันธ์</span>
        </td>
    </tr>
    <tr>
        <td class="label" style="width: 10%;">ผู้เบิก:</td>
        <td class="line" style="width: 90%;">&nbsp;&nbsp;' . htmlspecialchars($request['full_name']) . '</td>
    </tr>
</table>

<!-- ส่วนข้อมูลการใช้งาน -->
<table class="form-layout">
    <tr>
        <td class="label" style="width: 15%;">ใช้ในวันที่</td>
        <td class="line" style="width: 35%;">&nbsp;&nbsp;' . $use_date_thai . '</td>
        <td class="label" style="width: 10%; text-align: center;">โทร</td>
        <td class="line" style="width: 40%;">&nbsp;&nbsp;' . htmlspecialchars($request['phone_number'] ?? '-') . '</td>
    </tr>
</table>

<table class="form-layout">
    <tr>
        <td class="label" style="width: 20%;">ประเภทของงาน:</td>
        <td class="line" style="width: 80%;">&nbsp;&nbsp;' . htmlspecialchars($request['event_type']) . '</td>
    </tr>
    <tr>
        <td class="label" style="width: 35%;">ชื่องาน/โครงการ/ประชุม/สัมมนา:</td>
        <td class="line" style="width: 65%;">&nbsp;&nbsp;' . htmlspecialchars($request['event_name']) . '</td>
    </tr>
    <tr>
        <td class="label" style="width: 20%;">สถานที่นำไปใช้:</td>
        <td class="line" style="width: 80%;">&nbsp;&nbsp;' . htmlspecialchars($request['location']) . '</td>
    </tr>
    <tr>
        <td class="label" style="width: 30%;">วัตถุประสงค์เพื่อใช้ในงาน:</td>
        <td class="line" style="width: 70%;">&nbsp;&nbsp;' . htmlspecialchars($request['purpose']) . '</td>
    </tr>
</table>

<!-- ส่วนตารางรายการสิ่งของ -->
<table class="items">
    <thead>
        <tr>
            <th>หนังสือทั้งหมด</th>
            <th style="width: 15%;">คำขอ</th>
            <th style="width: 15%;">อนุมัติ</th>
        </tr>
    </thead>
    <tbody>
        ' . $tableRows . '
    </tbody>
</table>

<!-- วันที่กำกับมุมล่างขวา -->
<div style="text-align: right; margin-top: 30px;">
    ' . date('d-m-Y', strtotime($request['request_date'])) . '
</div>
';

// 6. สั่งสร้าง PDF
$mpdf->WriteHTML($html);
$mpdf->Output('Request_'.$request['request_no'].'.pdf', 'I');