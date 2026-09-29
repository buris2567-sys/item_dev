<?php
session_start();
require_once '../../config/db.php';

// ตรวจสอบสิทธิ์
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'User') {
    exit('Unauthorized access');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id    = $_SESSION['user_id'];
    $use_date   = $_POST['use_date'];
    $event_type = $_POST['event_type'];
    $event_name = trim($_POST['event_name']);
    $location   = trim($_POST['location']);
    $purpose    = trim($_POST['purpose']);
    $user_note  = trim($_POST['user_note']);
    
    // รับค่าตะกร้าสินค้า รูปแบบ array: $_POST['items']['ITM123'] = 5;
    $request_items = $_POST['items'] ?? [];

    if (empty($request_items)) {
        $_SESSION['error'] = "กรุณาเลือกสิ่งของอย่างน้อย 1 รายการ";
        header("Location: ../../index.php?page=create_request");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. สร้างรหัสคำขอ (Request ID) 
        // ตัวอย่าง: REQ-2609-XXXX
        $req_prefix = 'REQ-' . date('ym') . '-';
        $request_id = $req_prefix . strtoupper(substr(uniqid(), -4));

        // 2. บันทึกข้อมูลลงตาราง requests
        $stmt = $pdo->prepare("
            INSERT INTO requests 
            (request_id, user_id, use_date, event_type, event_name, location, purpose, user_note, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
        ");
        $stmt->execute([$request_id, $user_id, $use_date, $event_type, $event_name, $location, $purpose, $user_note]);

        // 3. บันทึกรายการสิ่งของลงตาราง request_items[cite: 35]
        $itemStmt = $pdo->prepare("
            INSERT INTO request_items (request_id, item_id, requested_qty, item_status) 
            VALUES (?, ?, ?, 'Pending')
        ");

        foreach ($request_items as $item_id => $qty) {
            $qty = (int)$qty;
            if ($qty > 0) {
                $itemStmt->execute([$request_id, $item_id, $qty]);
            }
        }

        $pdo->commit();
        $_SESSION['success'] = "ส่งคำขอเบิกพัสดุรหัส $request_id เรียบร้อยแล้ว ระบบกำลังรอการอนุมัติ";

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการส่งคำขอ: " . $e->getMessage();
    }
    
    // ส่งกลับไปหน้าประวัติคำขอ (หรือหน้าที่ต้องการ)
    header("Location: ../../index.php?page=my_requests");
    exit;
}
?>