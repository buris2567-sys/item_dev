<?php
session_start();
require_once '../../config/db.php';

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    exit('Unauthorized access');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['item_id'])) {
    $item_id = $_POST['item_id'];
    $action_type = $_POST['action_type']; // 'add' หรือ 'reduce'
    $quantity = (int)$_POST['quantity'];
    $updated_by = $_SESSION['user_id'];
    
    // ป้องกันการกรอกค่าจำนวนเป็น 0 หรือติดลบ
    if ($quantity <= 0) {
        $_SESSION['error'] = "จำนวนที่ต้องการเพิ่มหรือลดต้องมากกว่า 0";
        header("Location: ../../index.php?page=manage_items");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. ดึงข้อมูลพัสดุและสต็อกปัจจุบัน (ก่อนอัปเดต)
        $stmt = $pdo->prepare("
            SELECT i.name, i.current_stock, c.name as category_name 
            FROM items i 
            LEFT JOIN categories c ON i.category_id = c.category_id 
            WHERE i.item_id = ?
        ");
        $stmt->execute([$item_id]);
        $itemData = $stmt->fetch();

        if (!$itemData) {
            throw new Exception("ไม่พบข้อมูลสิ่งของในระบบ");
        }

        // เตรียมข้อมูล Snapshot
        $item_name      = $itemData['name'];
        $category_name  = $itemData['category_name'] ?? '-';
        $previous_stock = (int)$itemData['current_stock'];
        
        // 2. คำนวณสต็อกใหม่ และกำหนดประเภท Transaction
        if ($action_type === 'add') {
            $current_stock = $previous_stock + $quantity;
            $transaction_type = 'IN';
            $quantity_change = $quantity; // เก็บเป็นค่าบวก
        } elseif ($action_type === 'reduce') {
            // ป้องกันสต็อกติดลบ
            if ($quantity > $previous_stock) {
                throw new Exception("ไม่สามารถลดสต็อกเกินจำนวนที่มีอยู่ได้ (มีอยู่ {$previous_stock} ชิ้น)");
            }
            $current_stock = $previous_stock - $quantity;
            $transaction_type = 'OUT';
            $quantity_change = -$quantity; // เก็บเป็นค่าติดลบ
        } else {
            throw new Exception("ประเภทการดำเนินการไม่ถูกต้อง");
        }

        // 3. ดึงชื่อ Username ของคนทำรายการ
        $userStmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
        $userStmt->execute([$updated_by]);
        $username = $userStmt->fetchColumn() ?: 'System';

        // 4. อัปเดตข้อมูลจำนวนลงในตาราง items
        $updateStmt = $pdo->prepare("UPDATE items SET current_stock = ? WHERE item_id = ?");
        $updateStmt->execute([$current_stock, $item_id]);

        // 5. บันทึก Transaction ฝังรายละเอียด + ชื่อ + ประเภท + คนอัปเดต ครบจบในช่องเดียว
        $remark_action = ($action_type === 'add') ? 'เพิ่มสต็อก' : 'ลดสต็อก';
        $remark = "{$remark_action}: โดย {$username}";
        
        $logStmt = $pdo->prepare("
            INSERT INTO inventory_transactions 
            (item_id, item_name, category_name, transaction_type, quantity, previous_stock, current_stock, remark, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $logStmt->execute([
            $item_id, 
            $item_name, 
            $category_name, 
            $transaction_type, 
            $quantity_change, 
            $previous_stock, 
            $current_stock, 
            $remark, 
            $updated_by
        ]);

        $pdo->commit();
        $_SESSION['success'] = "อัปเดตจำนวนสต็อกและเก็บประวัติเรียบร้อยแล้ว";

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปเดต: " . $e->getMessage();
    }
    
    header("Location: ../../index.php?page=manage_items");
    exit;
}
?>