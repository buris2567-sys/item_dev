<?php
session_start();
require_once '../../config/db.php';

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    exit('Unauthorized access');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['item_id'])) {
    $item_id    = $_POST['item_id'];
    $item_name  = $_POST['item_name'];
    $deleted_by = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // Step 1: ดึงรูปภาพมาลบออกจากไดเรกทอรีในเซิร์ฟเวอร์ก่อน
        $imgStmt = $pdo->prepare("SELECT images FROM items WHERE item_id = ?");
        $imgStmt->execute([$item_id]);
        $item = $imgStmt->fetch();

        if ($item && !empty($item['images'])) {
            $images = json_decode($item['images'], true);
            if (is_array($images)) {
                foreach ($images as $img) {
                    $file_path = '../../assets/uploads/items/' . $img;
                    if (file_exists($file_path)) {
                        unlink($file_path); // ลบไฟล์รูปจริง
                    }
                }
            }
        }

        // Step 2: บันทึก Log การลบลงตาราง inventory_transactions "ก่อน" สั่งลบไอเทม
        // (ระบุชื่อไอเทมใน remark ไว้ด้วย เพราะเมื่อลบสิ่งของแล้ว item_id จะกลายเป็น NULL)
        $remark = "ลบรายการสิ่งของออกจากระบบ: " . $item_name;
        //  $logStmt = $pdo->prepare("INSERT INTO inventory_transactions (item_id, transaction_type, quantity, remark, created_by) VALUES (?, 'DELETE', 0, ?, ?)");
        
        // เปลี่ยนจาก 'DELETE' เป็น 'OUT' เพื่อให้ตรงกับมาตรฐาน Database
        $logStmt = $pdo->prepare("INSERT INTO inventory_transactions (item_id, transaction_type, quantity, remark, created_by) VALUES (?, 'OUT', 0, ?, ?)");
        $logStmt->execute([$item_id, $remark, $deleted_by]);


        // Step 3: ลบรายการสิ่งของออกจากตาราง items
        $delStmt = $pdo->prepare("DELETE FROM items WHERE item_id = ?");
        $delStmt->execute([$item_id]);

        $pdo->commit();
        $_SESSION['success'] = "ลบข้อมูลสิ่งของเรียบร้อยแล้ว";
    } catch (Exception $e) {
        $pdo->rollBack();
        // $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบ: " . $e->getMessage();
    
        // ปิดการเด้งกลับชั่วคราว แล้วพ่น Error ออกมาดู
        die("เกิดข้อผิดพลาดในการลบ: " . $e->getMessage());
        }

    header("Location: ../../index.php?page=manage_items");
    exit;
}
