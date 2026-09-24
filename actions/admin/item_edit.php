<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    exit('Unauthorized access');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['item_id'])) {
    
    $item_id     = $_POST['item_id'];
    $category_id = $_POST['category_id'];
    $name        = trim($_POST['name']);
    $description = trim($_POST['description']);
    $updated_by  = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // 1. ดึงข้อมูลพัสดุเดิม (เพื่อเอาชื่อภาพเก่ามาจัดการ)
        $stmt = $pdo->prepare("SELECT current_stock, images FROM items WHERE item_id = ?");
        $stmt->execute([$item_id]);
        $itemData = $stmt->fetch();
        $current_stock = (int)$itemData['current_stock'];
        
        // จัดการรูปภาพเดิม
        $existing_images = [];
        if (!empty($itemData['images'])) {
            $existing_images = json_decode($itemData['images'], true) ?: [];
        }

        $upload_dir = '../../assets/uploads/items/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        // วนลูปตรวจสอบว่ามีการอัปโหลดไฟล์ใหม่ในแต่ละช่อง (0, 1, 2) หรือไม่
        if (isset($_FILES['item_images'])) {
            for ($i = 0; $i < 3; $i++) {
                if (!empty($_FILES['item_images']['name'][$i]) && $_FILES['item_images']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmp_name  = $_FILES['item_images']['tmp_name'][$i];
                    $file_name = $_FILES['item_images']['name'][$i];
                    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                        // ลบรูปภาพเก่าในช่องนี้ (ถ้ามี) ออกจากโฟลเดอร์
                        if (isset($existing_images[$i])) {
                            $old_file = $upload_dir . $existing_images[$i];
                            if (file_exists($old_file)) unlink($old_file);
                        }

                        // บันทึกรูปภาพใหม่
                        $new_filename = $item_id . '_edit_' . ($i + 1) . '_' . uniqid() . '.' . $ext;
                        if (move_uploaded_file($tmp_name, $upload_dir . $new_filename)) {
                            $existing_images[$i] = $new_filename; // อัปเดตชื่อรูปใหม่ลง array ที่ตำแหน่งเดิม
                        }
                    }
                }
            }
        }
        
        // ล้างค่า null ใน array และจัดเรียง index ใหม่
        $existing_images = array_values(array_filter($existing_images));
        $images_json = !empty($existing_images) ? json_encode($existing_images) : null;

        // 2. ดึงข้อมูลประกอบสำหรับ Transaction
        $catStmt = $pdo->prepare("SELECT name FROM categories WHERE category_id = ?");
        $catStmt->execute([$category_id]);
        $category_name = $catStmt->fetchColumn() ?: '-';

        $userStmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
        $userStmt->execute([$updated_by]);
        $username = $userStmt->fetchColumn() ?: 'System';

        // 3. อัปเดตข้อมูลลงตาราง items
        $updateStmt = $pdo->prepare("UPDATE items SET category_id = ?, name = ?, description = ?, images = ? WHERE item_id = ?");
        $updateStmt->execute([$category_id, $name, $description, $images_json, $item_id]);

        // 4. บันทึก Transaction (Snapshot)
        $quantity_change = 0;
        $remark = "แก้ไขข้อมูลสิ่งของ: {$name} โดย {$username}";

        $logStmt = $pdo->prepare("
            INSERT INTO inventory_transactions 
            (item_id, item_name, category_name, transaction_type, quantity, previous_stock, current_stock, remark, created_by) 
            VALUES (?, ?, ?, 'EDIT', ?, ?, ?, ?, ?)
        ");
        $logStmt->execute([
            $item_id, 
            $name, 
            $category_name, 
            $quantity_change, 
            $current_stock, 
            $current_stock, 
            $remark, 
            $updated_by
        ]);

        $pdo->commit();
        $_SESSION['success'] = "แก้ไขข้อมูลสิ่งของเรียบร้อยแล้ว";

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการแก้ไข: " . $e->getMessage();
    }
    
    header("Location: ../../index.php?page=manage_items");
    exit;
}
?>