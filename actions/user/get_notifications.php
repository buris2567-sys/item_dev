<?php
session_start();
require_once '../../config/db.php';

// ป้องกันไม่ให้ผู้ที่ไม่ได้เข้าสู่ระบบเข้าถึง API นี้
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'count' => 0, 'items' => []]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // 1. นับจำนวนคำขอทั้งหมดของ User นี้ ยกเว้นสถานะ "รออนุมัติ" และ "Pending"
    $stmtCount = $pdo->prepare("
        SELECT COUNT(*) 
        FROM requests 
        WHERE user_id = ? 
          AND TRIM(status) != 'รออนุมัติ' 
          AND LOWER(TRIM(status)) != 'pending'
    ");
    $stmtCount->execute([$user_id]);
    $count = (int)$stmtCount->fetchColumn();

    // 2. ดึงข้อมูลรายการล่าสุด 5 รายการ (ที่ไม่ใช่ "รออนุมัติ") มาแสดงใน Dropdown กระดิ่ง
    $stmtList = $pdo->prepare("
        SELECT request_id, request_no, status, use_date, approved_at, request_date 
        FROM requests 
        WHERE user_id = ? 
          AND TRIM(status) != 'รออนุมัติ' 
          AND LOWER(TRIM(status)) != 'pending' 
        ORDER BY COALESCE(approved_at, request_date) DESC, request_id DESC 
        LIMIT 5
    ");
    $stmtList->execute([$user_id]);
    $rawItems = $stmtList->fetchAll(PDO::FETCH_ASSOC);

    $items = [];
    foreach ($rawItems as $item) {
        $displayDate = !empty($item['approved_at']) 
            ? date('d/m/Y H:i', strtotime($item['approved_at']))
            : (!empty($item['request_date']) ? date('d/m/Y', strtotime($item['request_date'])) : '-');

        $items[] = [
            'request_id'   => $item['request_id'],
            'request_no'   => $item['request_no'],
            'status'       => $item['status'],
            'date_display' => $displayDate,
            'use_date'     => !empty($item['use_date']) ? date('d/m/Y', strtotime($item['use_date'])) : '-'
        ];
    }

    // ส่งข้อมูลกลับไปเป็นรูปแบบ JSON ให้ JavaScript ใช้งาน
    echo json_encode([
        'status' => 'success',
        'count'  => $count,
        'items'  => $items
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status'  => 'error', 
        'message' => $e->getMessage(),
        'count'   => 0,
        'items'   => []
    ]);
}
?>
