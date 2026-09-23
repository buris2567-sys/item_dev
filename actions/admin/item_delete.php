<?php
session_start();
require_once '../../config/db.php';

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    exit('Unauthorized access');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['item_id'])) {
    $item_id    = $_POST['item_id'];
    $deleted_by = $_SESSION['user_id'];
    
    try {
        $pdo->beginTransaction();

        // 1. ดึงชื่อ, หมวดหมู่ และสต็อกเดิม ก่อนที่จะถูกลบทิ้ง
        $stmt = $pdo->prepare("
            SELECT i.name, i.current_stock, i.images, c.name as category_name 
            FROM items i 
            LEFT JOIN categories c ON i.category_id = c.category_id 
            WHERE i.item_id = ?
        ");
        $stmt->execute([$item_id]);
        $itemData = $stmt->fetch();

        // เตรียมข้อมูล Snapshot
        $item_name      = $itemData['name'] ?? ($_POST['item_name'] ?? 'ไม่ทราบชื่อ');
        $category_name  = $itemData['category_name'] ?? '-';
        $previous_stock = $itemData ? (int)$itemData['current_stock'] : 0;
        $current_stock  = 0; 
        $quantity_change = -$previous_stock; 

        // 2. ดึงชื่อคนทำรายการ (Username)
        $userStmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
        $userStmt->execute([$deleted_by]);
        $username = $userStmt->fetchColumn() ?: 'System';
        
        // 3. จัดการลบไฟล์รูปภาพ
        if ($itemData && !empty($itemData['images'])) {
            $images = json_decode($itemData['images'], true);
            if (is_array($images)) {
                foreach ($images as $img) {
                    $file_path = '../../assets/uploads/items/' . $img;
                    if (file_exists($file_path)) unlink($file_path);
                }
            }
        }

        // 4. บันทึก Transaction ฝังรายละเอียด + ชื่อ + ประเภท + คนลบ ครบจบในช่องเดียว
        $remark = "ลบสิ่งของ: {$item_name} (ประเภท: {$category_name}) โดย {$username}";
        
        $logStmt = $pdo->prepare("
            INSERT INTO inventory_transactions 
            (item_id, item_name, category_name, transaction_type, quantity, previous_stock, current_stock, remark, created_by) 
            VALUES (?, ?, ?, 'DELETE', ?, ?, ?, ?, ?)
        ");
        $logStmt->execute([$item_id, $item_name, $category_name, $quantity_change, $previous_stock, $current_stock, $remark, $deleted_by]);

        // 5. ลบข้อมูลจากตาราง items ออกถาวร
        $delStmt = $pdo->prepare("DELETE FROM items WHERE item_id = ?");
        $delStmt->execute([$item_id]);

        $pdo->commit();
        $_SESSION['success'] = "ลบข้อมูลสิ่งของและเก็บประวัติเรียบร้อยแล้ว";

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบ: " . $e->getMessage();
    }
    
    header("Location: ../../index.php?page=manage_items");
    exit;
}
?>