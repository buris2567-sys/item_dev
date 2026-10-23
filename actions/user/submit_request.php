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
    
    $items      = $_POST['items'] ?? []; // Array ตะกร้า [ '1' => '5', '3' => '10' ]

    // ตรวจสอบข้อมูลเบื้องต้น
    if (empty($use_date) || empty($event_name) || empty($items)) {
        $_SESSION['error'] = 'กรุณากรอกข้อมูลและเลือกรายการพัสดุให้ครบถ้วน';
        header("Location: ../../index.php?page=create_request");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. สร้าง Request No แบบรันอัตโนมัติ (เช่น REQ-2609-XXXX)
        $yearMonth = date('ym'); 
        $prefix = "REQ-{$yearMonth}-";
        
        // 🟢 [แก้ไข] ค้นหาเลขล่าสุดจากคอลัมน์ request_no แทน
        $stmtId = $pdo->prepare("SELECT request_no FROM requests WHERE request_no LIKE ? ORDER BY request_no DESC LIMIT 1");
        $stmtId->execute([$prefix . '%']);
        $lastNo = $stmtId->fetchColumn();

        if ($lastNo) {
            $lastNumber = (int)substr($lastNo, -4);
            $newNumber = str_pad($lastNumber + 4, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        $request_no = $prefix . $newNumber; // ได้เป็น REQ-2609-0001

        // 2. 🟢 [แก้ไข] บันทึกข้อมูลลงตาราง requests โดยใช้ request_no (ส่วน request_id จะสร้างเลข INT ให้เอง)
        $stmtReq = $pdo->prepare("
            INSERT INTO requests (request_no, user_id, use_date, event_type, event_name, location, purpose, user_note, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'รออนุมัติ')
        ");
        $stmtReq->execute([$request_no, $user_id, $use_date, $event_type, $event_name, $location, $purpose, $user_note]);

        // 🟢 [แก้ไข] ดึง request_id (INT) ที่เพิ่งสร้างเสร็จหมาดๆ ออกมาใช้งาน
        $new_request_id = $pdo->lastInsertId();

        // 3. บันทึกข้อมูลตะกร้าลงตาราง request_items
        $stmtItem = $pdo->prepare("
            INSERT INTO request_items (request_id, item_id, requested_qty) 
            VALUES (?, ?, ?)
        ");

        foreach ($items as $item_id => $qty) {
            $qty = (int)$qty;
            if ($qty > 0) {
                // 🟢 [แก้ไข] เปลี่ยนการค้นหา Primary Key ของ items จาก item_id เป็น id
                $getItem = $pdo->prepare("
                    SELECT id 
                    FROM items 
                    WHERE id = ? AND is_active = 1
                ");
                $getItem->execute([$item_id]);
                
                if ($getItem->fetch()) {
                    // ใช้ $new_request_id ที่เป็นตัวเลขผูกข้อมูล
                    $stmtItem->execute([$new_request_id, $item_id, $qty]);
                }
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
}
?>