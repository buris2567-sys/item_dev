<?php
session_start();
require_once '../../config/db.php'; // ปรับ path ให้ตรงกับโครงสร้างของคุณ

// ป้องกันไม่ให้คนที่ไม่ใช่ admin เข้าถึง API นี้
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    echo json_encode(['count' => 0, 'items' => []]);
    exit;
}

try {
    // 1. นับจำนวนคำขอทั้งหมดที่ยัง "รออนุมัติ"
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM requests WHERE status = 'รออนุมัติ'");
    $stmtCount->execute();
    $count = $stmtCount->fetchColumn();

    // 2. ดึงข้อมูลรายการล่าสุด 5 รายการมาแสดงใน Dropdown กระดิ่ง
    $stmtList = $pdo->prepare("
        SELECT request_no, use_date 
        FROM requests 
        WHERE status = 'รออนุมัติ' 
        ORDER BY request_id DESC 
        LIMIT 5
    ");
    $stmtList->execute();
    $items = $stmtList->fetchAll(PDO::FETCH_ASSOC);

    // ส่งข้อมูลกลับไปเป็นรูปแบบ JSON ให้ JavaScript ใช้งาน
    echo json_encode([
        'status' => 'success',
        'count' => (int)$count,
        'items' => $items
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>