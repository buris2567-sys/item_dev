<?php
// ไฟล์: actions/user/request_update.php
session_start();
require_once '../../config/db.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $request_id = $_POST['request_id'] ?? null;

    // รับค่าข้อมูลทั่วไปจากฟอร์ม
    $use_date = trim($_POST['use_date'] ?? '');
    $event_type = trim($_POST['event_type'] ?? '');
    $event_name = trim($_POST['event_name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $purpose = trim($_POST['purpose'] ?? '');
    $user_note = trim($_POST['user_note'] ?? '');

    // รับข้อมูลตะกร้า (อิงตาม Array ที่ JS สร้างให้ใน #hiddenCartInputs)
    // หมายเหตุ: ตรงนี้อิงจากชื่อ input ที่ฝั่ง JS สร้าง เช่น name="item_id[]" และ name="qty[]"
    $item_ids = $_POST['item_id'] ?? [];
    $qtys = $_POST['qty'] ?? [];

    // ตรวจสอบข้อมูลเบื้องต้น
    if (!$request_id || empty($item_ids)) {
        $_SESSION['error'] = "ข้อมูลไม่ครบถ้วน หรือไม่มีรายการสิ่งของในตะกร้า";
        header("Location: ../../index.php?page=create_request&id=" . $request_id);
        exit;
    }

    try {
        // เริ่ม Transaction ถ้าผิดพลาดตรงไหน ให้ย้อนกลับ (Rollback) ทั้งหมด
        $pdo->beginTransaction();

        // 1. ตรวจสอบสิทธิ์ว่าคำร้องนี้เป็นของ user คนนี้ และสถานะยังเป็น 'รออนุมัติ' 
        $stmtCheck = $pdo->prepare("SELECT status FROM requests WHERE request_id = ? AND user_id = ? FOR UPDATE");
        $stmtCheck->execute([$request_id, $user_id]);
        $req = $stmtCheck->fetch();

        if (!$req || $req['status'] !== 'รออนุมัติ') {
            throw new Exception("ไม่สามารถแก้ไขคำร้องนี้ได้ (อาจถูกอนุมัติหรือยกเลิกไปแล้ว)");
        }

        // 2. อัปเดตข้อมูลรายละเอียดคำร้อง
        $stmtUpdate = $pdo->prepare("
            UPDATE requests 
            SET use_date = ?, event_type = ?, event_name = ?, location = ?, purpose = ?, user_note = ?
            WHERE request_id = ?
        ");
        $stmtUpdate->execute([$use_date, $event_type, $event_name, $location, $purpose, $user_note, $request_id]);

        // 3. ลบรายการสิ่งของเดิมทิ้งทั้งหมด (เฉพาะของคำร้องนี้)
        $stmtDelete = $pdo->prepare("DELETE FROM request_items WHERE request_id = ?");
        $stmtDelete->execute([$request_id]);

        // 4. วนลูปบันทึกรายการสิ่งของชุดใหม่จากตะกร้าลงไป
        $stmtInsert = $pdo->prepare("INSERT INTO request_items (request_id, item_id, requested_qty) VALUES (?, ?, ?)");
        
        for ($i = 0; $i < count($item_ids); $i++) {
            $i_id = $item_ids[$i];
            $i_qty = $qtys[$i];
            
            // เช็คว่ามี ID จริงและจำนวนมากกว่า 0
            if (!empty($i_id) && $i_qty > 0) {
                $stmtInsert->execute([$request_id, $i_id, $i_qty]);
            }
        }

        // ยืนยันการบันทึกข้อมูล
        $pdo->commit();
        
        $_SESSION['success'] = "แก้ไขคำขอเบิกสิ่งของเรียบร้อยแล้ว";
        header("Location: ../../index.php?page=my_requests");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        header("Location: ../../index.php?page=create_request&id=" . $request_id);
        exit;
    }
} else {
    header("Location: ../../index.php?page=my_requests");
    exit;
}
?>