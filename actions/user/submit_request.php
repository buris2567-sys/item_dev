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
    
    $items      = $_POST['items'] ?? []; // Array ตะกร้า [ 'ITM001' => '5', 'ITM002' => '10' ]

    // ตรวจสอบข้อมูลเบื้องต้น
    if (empty($use_date) || empty($event_name) || empty($items)) {
        $_SESSION['error'] = 'กรุณากรอกข้อมูลและเลือกรายการพัสดุให้ครบถ้วน';
        header("Location: ../../index.php?page=create_request");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. สร้าง Request ID แบบรันอัตโนมัติ (เช่น REQ-2609-XXXX)
        $yearMonth = date('ym'); // เช่น ปี 2026 เดือน 09 -> 2609
        $prefix = "REQ-{$yearMonth}-";
        
        // หาเลขล่าสุดในเดือนนี้
        $stmtId = $pdo->prepare("SELECT request_id FROM requests WHERE request_id LIKE ? ORDER BY request_id DESC LIMIT 1");
        $stmtId->execute([$prefix . '%']);
        $lastId = $stmtId->fetchColumn();

        if ($lastId) {
            $lastNumber = (int)substr($lastId, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        $request_id = $prefix . $newNumber; // ได้เป็น REQ-2609-0001

        // 2. บันทึกข้อมูลลงตาราง requests
        $stmtReq = $pdo->prepare("
            INSERT INTO requests (request_id, user_id, use_date, event_type, event_name, location, purpose, user_note, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'รออนุมัติ')
        ");
        $stmtReq->execute([$request_id, $user_id, $use_date, $event_type, $event_name, $location, $purpose, $user_note]);

        // 3. บันทึกข้อมูลตะกร้าลงตาราง request_items
        $stmtItem = $pdo->prepare("
            INSERT INTO request_items (request_id, item_id, item_name, category, images, requested_qty) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($items as $item_id => $qty) {
            $qty = (int)$qty;
            if ($qty > 0) {
                // ดึงข้อมูลแบบเต็ม (ชื่อ, หมวดหมู่, รูปภาพ) จากตาราง items
                $getItem = $pdo->prepare("
                    SELECT i.name, i.images, c.name as cat_name 
                    FROM items i 
                    LEFT JOIN categories c ON i.category_id = c.category_id 
                    WHERE i.item_id = ?
                ");
                $getItem->execute([$item_id]);
                $itemData = $getItem->fetch();

                // กำหนดค่า Snapshot
                $item_name = $itemData['name'] ?? 'ไม่ทราบชื่อ';
                $category  = $itemData['cat_name'] ?? 'ไม่ระบุ';
                $images    = $itemData['images'] ?? null;

                // บันทึกลงตาราง request_items
                $stmtItem->execute([$request_id, $item_id, $item_name, $category, $images, $qty]);
            }
        }
        $pdo->commit();
        $_SESSION['success'] = "สร้างคำขอเบิกพัสดุเรียบร้อยแล้ว (เลขที่ $request_id)";
        header("Location: ../../index.php?page=my_requests");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $e->getMessage();
        header("Location: ../../index.php?page=create_request");
        exit;
    }
}
?>