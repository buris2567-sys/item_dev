<?php
// Excel export for an individual request. Kept beside export_pdf.php intentionally.
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Access Denied: กรุณาเข้าสู่ระบบ');
}

$requestId = (int)($_GET['id'] ?? 0);
if ($requestId <= 0) {
    http_response_code(400);
    exit('ไม่พบรหัสคำร้อง');
}

$requestStmt = $pdo->prepare("SELECT r.*, u.full_name, u.phone_number
    FROM requests r
    LEFT JOIN users u ON u.user_id = r.user_id
    WHERE r.request_id = ?");
$requestStmt->execute([$requestId]);
$request = $requestStmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    http_response_code(404);
    exit('ไม่พบข้อมูลคำร้องนี้');
}

// ผู้ดูแลดูได้ทุกคำร้อง ส่วนผู้ใช้ทั่วไปดูได้เฉพาะคำร้องของตนเอง เช่นเดียวกับ export_pdf.php
if (($_SESSION['role'] ?? '') !== 'Admin' && (int)$request['user_id'] !== (int)$_SESSION['user_id']) {
    http_response_code(403);
    exit('Access Denied: คุณไม่มีสิทธิ์ดาวน์โหลดเอกสารนี้');
}

$itemStmt = $pdo->prepare("SELECT ri.requested_qty, ri.approved_qty, ri.item_status,
        i.item_code, i.name AS item_name, c.name AS category_name
    FROM request_items ri
    LEFT JOIN items i ON i.id = ri.item_id
    LEFT JOIN categories c ON c.category_id = i.category_id
    WHERE ri.request_id = ?
    ORDER BY ri.request_item_id");
$itemStmt->execute([$requestId]);
$items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

$thaiMonths = [1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'];
$thaiDate = static function (?string $date) use ($thaiMonths): string {
    if (!$date || strtotime($date) === false) return '-';
    $timestamp = strtotime($date);
    return date('d', $timestamp) . ' ' . $thaiMonths[(int)date('n', $timestamp)] . ' ' . ((int)date('Y', $timestamp) + 543);
};
$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$generatedAt = date('d/m/') . (date('Y') + 543) . ' เวลา ' . date('H:i') . ' น.';
$fileName = 'request_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $request['request_no']) . '.xls';

header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Pragma: no-cache');
header('Expires: 0');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <style>
        body, table, td, th { font-family: "TH Sarabun New", "THSarabunNew", sans-serif; color: #111; }
        body { font-size: 16pt; }
        .title { text-align: center; font-size: 22pt; font-weight: bold; }
        .subtitle { text-align: center; font-size: 16pt; color: #555; margin-bottom: 12px; }
        .date-line { width: 70%; margin: 0 auto 12px; border-collapse: collapse; text-align: center; }
        .date-line td { padding: 2px 5px; }
        .dotted { border-bottom: 1px dotted #000; }
        .detail, .items { width: 100%; border-collapse: collapse; }
        .detail td { padding: 4px 7px; vertical-align: top; }
        .detail .label { width: 145px; font-weight: bold; white-space: nowrap; }
        .detail .value { border-bottom: 1px dotted #777; }
        .section { margin: 15px 0 6px; font-size: 18pt; font-weight: bold; }
        .items th { background: #bfbfbf; border: 1px solid #000; padding: 5px 7px; text-align: center; font-weight: bold; }
        .items td { border: 1px solid #000; padding: 5px 7px; }
        .center { text-align: center; } .right { text-align: right; }
        .footer { margin-top: 18px; color: #555; font-size: 13pt; text-align: right; }
    </style>
</head>
<body>
    <div class="title">แบบฟอร์มเบิกเอกสารเผยแพร่และของที่ระลึก</div>
    <div class="subtitle">เลขที่คำขอ <?= $escape($request['request_no']) ?></div>

    <table class="date-line"><tr>
        <td>วันที่</td><td class="dotted"><?= date('d', strtotime($request['request_date'])) ?></td>
        <td>เดือน</td><td class="dotted"><?= $thaiMonths[(int)date('n', strtotime($request['request_date']))] ?></td>
        <td>พ.ศ.</td><td class="dotted"><?= (int)date('Y', strtotime($request['request_date'])) + 543 ?></td>
    </tr></table>

    <table class="detail">
        <tr><td class="label">ผู้เบิก:</td><td class="value"><?= $escape($request['full_name'] ?: '-') ?></td></tr>
        <tr><td class="label">โทรศัพท์:</td><td class="value"><?= $escape($request['phone_number'] ?: '-') ?></td></tr>
        <tr><td class="label">ใช้ในวันที่:</td><td class="value"><?= $escape($thaiDate($request['use_date'])) ?></td></tr>
        <tr><td class="label">ประเภทของงาน:</td><td class="value"><?= $escape($request['event_type'] ?: '-') ?></td></tr>
        <tr><td class="label">ชื่องาน/โครงการ:</td><td class="value"><?= $escape($request['event_name'] ?: '-') ?></td></tr>
        <tr><td class="label">สถานที่นำไปใช้:</td><td class="value"><?= $escape($request['location'] ?: '-') ?></td></tr>
        <tr><td class="label">วัตถุประสงค์:</td><td class="value"><?= nl2br($escape($request['purpose'] ?: '-')) ?></td></tr>
        <tr><td class="label">หมายเหตุ:</td><td class="value"><?= nl2br($escape($request['user_note'] ?: '-')) ?></td></tr>
        <tr><td class="label">สถานะคำขอ:</td><td class="value"><?= $escape($request['status']) ?></td></tr>
    </table>

    <div class="section">รายการสิ่งของที่ขอเบิก</div>
    <table class="items">
        <thead><tr><th style="width:7%">ลำดับ</th><th style="width:16%">รหัสพัสดุ</th><th>รายการสิ่งของ</th><th style="width:16%">หมวดหมู่</th><th style="width:12%">จำนวนที่ขอ</th><th style="width:12%">จำนวนอนุมัติ</th><th style="width:15%">ผลพิจารณา</th></tr></thead>
        <tbody>
        <?php if ($items): ?>
            <?php foreach ($items as $index => $item): ?>
                <tr><td class="center"><?= $index + 1 ?></td><td class="center"><?= $escape($item['item_code'] ?: '-') ?></td><td><?= $escape($item['item_name'] ?: 'ไม่พบรายการ') ?></td><td><?= $escape($item['category_name'] ?: '-') ?></td><td class="center"><?= number_format((int)$item['requested_qty']) ?></td><td class="center"><?= $item['approved_qty'] === null ? '-' : number_format((int)$item['approved_qty']) ?></td><td class="center"><?= $escape($item['item_status'] ?: '-') ?></td></tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="7" class="center">ไม่พบรายการสิ่งของ</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <div class="footer">ข้อมูล ณ วันที่ <?= $escape($generatedAt) ?></div>
</body>
</html>
