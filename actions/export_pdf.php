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
            'R' => 'THSarabunNew.ttf',
            'B' => 'THSarabunNew Bold.ttf',
            'I' => 'THSarabunNew Italic.ttf',
            'BI' => 'THSarabunNew BoldItalic.ttf'
        ]
    ],
    'default_font' => 'thsarabunnew',
    'format' => 'A4',
]);

// 5. ออกแบบหน้าตา HTML ของ PDF ให้มีเส้นจุดไข่ปลาตามแบบที่ 1
$html = '
<style>
    body { 
        font-family: "thsarabunnew"; 
        font-size: 16pt; 
        line-height: 1.25; 
    }
    .title { 
        text-align: center; 
        font-size: 20pt; 
        font-weight: bold; 
        margin-bottom: 20px; 
    }
    
    /* สไตล์สำหรับตารางรายการหนังสือ/ของด้านล่าง */
    table.items-table { 
        width: 100%; 
        border-collapse: collapse; 
        margin-top: 15px; 
    }
    table.items-table th, table.items-table td { 
        border: 1px solid #000; 
        padding: 5px; 
    }
    table.items-table th { 
        background-color: #bfbfbf; 
        text-align: center; 
        font-weight: bold; 
    }
</style>

<div class="title">แบบฟอร์มเบิกเอกสารเผยแพร่และของที่ระลึก</div>

<!-- 1. ส่วนวันที่ด้านบน จัดวางกึ่งกลางค่อนขวา -->
<table style="width: 70%; margin: 0 auto 15px auto; border-collapse: separate; border-spacing: 0;">
    <tr>
        <td style="width: 10%; text-align: right; font-weight: bold; vertical-align: bottom;">วันที่</td>
        <td style="width: 15%; text-align: center; border-bottom: 1px dotted #000; vertical-align: bottom;">' . $req_d . '</td>
        <td style="width: 15%; text-align: right; font-weight: bold; padding-left: 10px; vertical-align: bottom;">เดือน</td>
        <td style="width: 30%; text-align: center; border-bottom: 1px dotted #000; vertical-align: bottom;">' . $req_m . '</td>
        <td style="width: 10%; text-align: right; font-weight: bold; padding-left: 10px; vertical-align: bottom;">พ.ศ.</td>
        <td style="width: 20%; text-align: center; border-bottom: 1px dotted #000; vertical-align: bottom;">' . $req_y . '</td>
    </tr>
</table>

<!-- 2. ข้อความเรียน -->
<table style="width: 100%; margin-bottom: 6px; border-collapse: separate; border-spacing: 0;">
    <tr>
        <td style="padding-left: 45px; vertical-align: bottom;">
            <b>เรียน</b> หัวหน้ากลุ่มส่งเสริมฝึกอบรมและเผยแพร่ประชาสัมพันธ์
        </td>
    </tr>
</table>

<!-- 3. ผู้เบิก -->
<table style="width: 100%; margin-bottom: 6px; border-collapse: separate; border-spacing: 0;">
    <tr>
        <td style="width: 55px; font-weight: bold; white-space: nowrap; vertical-align: bottom;">ผู้เบิก:</td>
        <td style="border-bottom: 1px dotted #000; padding-left: 12px; vertical-align: bottom;">' . htmlspecialchars($request['full_name'] ?? '') . '</td>
    </tr>
</table>

<!-- 4. ใช้ในวันที่ และ โทร -->
<table style="width: 100%; margin-bottom: 6px; border-collapse: separate; border-spacing: 0;">
    <tr>
        <td style="width: 75px; font-weight: bold; white-space: nowrap; vertical-align: bottom;">ใช้ในวันที่</td>
        <td style="width: 250px; border-bottom: 1px dotted #000; padding-left: 12px; vertical-align: bottom;">' . $use_date_thai . '</td>
        <td style="width: 50px; font-weight: bold; text-align: center; white-space: nowrap; vertical-align: bottom;">โทร</td>
        <td style="border-bottom: 1px dotted #000; padding-left: 12px; vertical-align: bottom;">' . htmlspecialchars($request['phone_number'] ?? '-') . '</td>
    </tr>
</table>

<!-- 5. ประเภทของงาน -->
<table style="width: 100%; margin-bottom: 6px; border-collapse: separate; border-spacing: 0;">
    <tr>
        <td style="width: 105px; font-weight: bold; white-space: nowrap; vertical-align: bottom;">ประเภทของงาน:</td>
        <td style="border-bottom: 1px dotted #000; padding-left: 12px; vertical-align: bottom;">' . htmlspecialchars($request['event_type'] ?? '') . '</td>
    </tr>
</table>

<!-- 6. ชื่องาน/โครงการ/ประชุม/สัมมนา -->
<table style="width: 100%; margin-bottom: 6px; border-collapse: separate; border-spacing: 0;">
    <tr>
        <td style="width: 205px; font-weight: bold; white-space: nowrap; vertical-align: bottom;">ชื่องาน/โครงการ/ประชุม/สัมมนา:</td>
        <td style="border-bottom: 1px dotted #000; padding-left: 12px; vertical-align: bottom;">' . htmlspecialchars($request['event_name'] ?? '') . '</td>
    </tr>
</table>

<!-- 7. สถานที่นำไปใช้ -->
<table style="width: 100%; margin-bottom: 6px; border-collapse: separate; border-spacing: 0;">
    <tr>
        <td style="width: 110px; font-weight: bold; white-space: nowrap; vertical-align: bottom;">สถานที่นำไปใช้:</td>
        <td style="border-bottom: 1px dotted #000; padding-left: 12px; vertical-align: bottom;">' . htmlspecialchars($request['location'] ?? '') . '</td>
    </tr>
</table>

<!-- 8. วัตถุประสงค์เพื่อใช้ในงาน -->
<table style="width: 100%; margin-bottom: 12px; border-collapse: separate; border-spacing: 0;">
    <tr>
        <td style="width: 150px; font-weight: bold; white-space: nowrap; vertical-align: bottom;">วัตถุประสงค์เพื่อใช้ในงาน:</td>
        <td style="border-bottom: 1px dotted #000; padding-left: 12px; vertical-align: bottom;">' . htmlspecialchars($request['purpose'] ?? '') . '</td>
    </tr>
</table>

<!-- ตารางรายการหนังสือ/สิ่งของ -->
<table class="items-table">
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
';

// 6. สั่งสร้าง PDF
$mpdf->WriteHTML($html);
$mpdf->Output('Request_'.$request['request_no'].'.pdf', 'I');