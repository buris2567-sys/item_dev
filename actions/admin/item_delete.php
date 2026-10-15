<?php
session_start();
require_once '../../config/db.php';

// ตรวจสอบสิทธิ์
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    $_SESSION['error'] = 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถทำรายการนี้ได้';
    header("Location: ../../index.php?page=manage_items");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = trim($_POST['item_id'] ?? '');
    $user_id = $_SESSION['user_id'];

    if (empty($item_id)) {
        $_SESSION['error'] = 'ไม่พบรหัสสิ่งของที่ต้องการลบ';
        header("Location: ../../index.php?page=manage_items");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. ดึงข้อมูลสต็อกปัจจุบันมาเพื่อบันทึกลงประวัติ
        $stmtStock = $pdo->prepare("SELECT current_stock FROM items WHERE item_id = ?");
        $stmtStock->execute([$item_id]);
        $current_stock = (int)$stmtStock->fetchColumn();

        // 🟢 2. ปรับ is_active = 0 (ซ่อน) และล้างสต็อก current_stock = 0 ไปพร้อมกัน
        $stmtUpdate = $pdo->prepare("UPDATE items SET is_active = 0, current_stock = 0 WHERE item_id = ?");
        $stmtUpdate->execute([$item_id]);

        // 3. บันทึกประวัติลงตาราง inventory_transactions ว่ามีการ "ลบ(ซ่อน)และล้างสต็อก"
        // quantity ให้ติดลบตามจำนวนสต็อกที่ถูกล้างออกไป
        $qty_removed = -$current_stock;
        
        $stmtTx = $pdo->prepare("
            INSERT INTO inventory_transactions 
            (item_id, transaction_type, quantity, previous_stock, current_stock, remark, created_by, created_at)
            VALUES (?, 'ลบสิ่งของ', ?, ?, 0, 'ยกเลิกการใช้งานและล้างสต็อกเป็น 0', ?, NOW())
        ");
        $stmtTx->execute([$item_id, $qty_removed, $current_stock, $user_id]);

        $pdo->commit();
        $_SESSION['success'] = 'ระงับการใช้งานและล้างสต็อกสิ่งของเรียบร้อยแล้ว';
        
        header("Location: ../../index.php?page=manage_items");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("<div style='color:red; padding: 20px; font-size: 20px;'><b>Database Error:</b> " . $e->getMessage() . "<br><br><a href='../../index.php?page=manage_items'>กลับไปหน้าเดิม</a></div>");
    }
}