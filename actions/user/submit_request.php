<?php
session_start();
require_once '../../config/db.php';

// ตรวจสอบว่าล็อกอินเป็น User หรือไม่
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อนทำรายการ';
    header("Location: ../../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $user_id    = $_SESSION['user_id'];
    $use_date   = trim($_POST['use_date'] ?? '');
    $event_type = trim($_POST['event_type'] ?? '');
    $event_name = trim($_POST['event_name'] ?? '');
    $location   = trim($_POST['location'] ?? '');
    $purpose    = trim($_POST['purpose'] ?? '');
    $user_note  = trim($_POST['user_note'] ?? '');
    
    // 🟢 1. รับข้อมูลตะกร้าแบบใหม่ที่ส่งมาจาก JS
    $item_ids = $_POST['item_id'] ?? [];
    $qtys = $_POST['qty'] ?? [];

    // เช็คว่าตะกร้าว่างไหม
    if (empty($item_ids)) {
        $_SESSION['error'] = "ไม่มีรายการสิ่งของในตะกร้า";
        header("Location: ../../index.php?page=create_request");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. สร้าง Request No แบบรันอัตโนมัติ (เช่น REQ-2609-XXXX)
        $yearMonth = date('ym'); 
        $prefix = "REQ-{$yearMonth}-";
        
        // ค้นหาเลขล่าสุดจากคอลัมน์ request_no แทน
        $stmtId = $pdo->prepare("SELECT request_no FROM requests WHERE request_no LIKE ? ORDER BY request_no DESC LIMIT 1");
        $stmtId->execute([$prefix . '%']);
        $lastNo = $stmtId->fetchColumn();

        if ($lastNo) {
            $lastNumber = (int)substr($lastNo, -4);
            // 🟢 ปรับให้รัน +1 ตามลำดับปกติ
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        $request_no = $prefix . $newNumber; // ได้เป็น REQ-2609-0001

        // 2. บันทึกข้อมูลลงตาราง requests โดยใช้ request_no
        $stmtReq = $pdo->prepare("
            INSERT INTO requests (request_no, user_id, use_date, event_type, event_name, location, purpose, user_note, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'รออนุมัติ')
        ");
        $stmtReq->execute([$request_no, $user_id, $use_date, $event_type, $event_name, $location, $purpose, $user_note]);

        // ดึง request_id (INT) ที่เพิ่งสร้างเสร็จหมาดๆ ออกมาใช้งาน
        $new_request_id = $pdo->lastInsertId();

        // 3. 🟢 วนลูปบันทึกรายการสิ่งของจากตะกร้าใหม่ลงตาราง request_items
        $stmtInsert = $pdo->prepare("INSERT INTO request_items (request_id, item_id, requested_qty) VALUES (?, ?, ?)");
        
        for ($i = 0; $i < count($item_ids); $i++) {
            $i_id = $item_ids[$i];
            $i_qty = $qtys[$i];
            
            // เช็คว่ามี ID จริงและจำนวนมากกว่า 0
            if (!empty($i_id) && $i_qty > 0) {
                // ผูกรหัสคำร้อง $new_request_id เข้ากับสิ่งของ
                $stmtInsert->execute([$new_request_id, $i_id, $i_qty]);
            }
        }
        
        $pdo->commit();
        $_SESSION['success'] = "สร้างคำขอเบิกพัสดุเรียบร้อยแล้ว (เลขที่ $request_no)";
        header("Location: ../../index.php?page=my_requests");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("<div style='color:red; padding: 20px; font-size: 20px;'><b>Database Error:</b> " . $e->getMessage() . "<br><br><a href='../../index.php?page=create_request'>กลับไปหน้าสร้างคำขอ</a></div>");
    }
} else {
    header("Location: ../../index.php?page=create_request");
    exit;
}
?>