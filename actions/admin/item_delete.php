<?php
if (!defined('APP_RUNNING')) {
    session_start();
    require_once '../../config/db.php';
} else {
    require_once 'config/db.php';
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    $_SESSION['error'] = 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถทำรายการนี้ได้';
    header("Location: ../../index.php?page=manage_items");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = trim($_POST['item_id'] ?? '');

    if (empty($item_id)) {
        $_SESSION['error'] = 'ไม่พบรหัสสิ่งของที่ต้องการลบ';
        header("Location: ../../index.php?page=manage_items");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 🟢 1. ตรวจสอบว่ามีการนำพัสดุนี้ไปใช้ในใบเบิก (request_items) หรือยัง
        $stmtReq = $pdo->prepare("SELECT COUNT(*) FROM request_items WHERE item_id = ?");
        $stmtReq->execute([$item_id]);
        if ($stmtReq->fetchColumn() > 0) {
            $_SESSION['error'] = 'ไม่สามารถลบสิ่งของนี้ได้ เนื่องจากมีประวัติการเบิกในระบบแล้ว';
            $pdo->rollBack();
            header("Location: ../../index.php?page=manage_items");
            exit;
        }

        // 🟢 2. ลบข้อมูลประวัติในตารางลูก (inventory_transactions) ก่อน เพื่อไม่ให้ติด FK Constraint (RESTRICT)
        $stmtDelTrans = $pdo->prepare("DELETE FROM inventory_transactions WHERE item_id = ?");
        $stmtDelTrans->execute([$item_id]);

        // 🟢 3. ดึงชื่อไฟล์รูปภาพมาเพื่อทำการลบไฟล์ออกจาก Disk
        $stmtImg = $pdo->prepare("SELECT images FROM items WHERE item_id = ?");
        $stmtImg->execute([$item_id]);
        $imagesJson = $stmtImg->fetchColumn();

        if ($imagesJson) {
            $images = json_decode($imagesJson, true);
            if (is_array($images)) {
                foreach ($images as $img) {
                    $file_path = '../../assets/uploads/items/' . $img;
                    if (file_exists($file_path)) {
                        @unlink($file_path);
                    }
                }
            }
        }

        // 🟢 4. ลบข้อมูลในตารางหลัก items
        $stmtDel = $pdo->prepare("DELETE FROM items WHERE item_id = ?");
        $stmtDel->execute([$item_id]);

        $pdo->commit();
        $_SESSION['success'] = 'ลบรายการสิ่งของเรียบร้อยแล้ว';
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'ไม่สามารถลบข้อมูลได้: ' . $e->getMessage();
    }

    header("Location: ../../index.php?page=manage_items");
    exit;
}