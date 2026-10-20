<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    die("เกิดข้อผิดพลาด: คุณไม่มีสิทธิ์ หรือ Session หมดอายุ กรุณาล็อกอินใหม่");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = trim($_POST['item_id'] ?? '');
    $category_id = trim($_POST['category_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $user_id = $_SESSION['user_id'];

    if (empty($item_id) || empty($category_id) || empty($name)) {
        die("เกิดข้อผิดพลาด: กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน");
    }

    try {
        $pdo->beginTransaction();

        // 🟢 [แก้ไข] เปลี่ยน WHERE item_id เป็น id
        $stmtOld = $pdo->prepare("SELECT * FROM items WHERE id = ?");
        $stmtOld->execute([$item_id]);
        $oldItem = $stmtOld->fetch();

        if (!$oldItem) {
            throw new Exception("ไม่พบรายการสิ่งของรหัส $item_id ที่ต้องการแก้ไข");
        }

        $existing_images = json_decode($oldItem['images'] ?? '[]', true);
        if (!is_array($existing_images)) $existing_images = [];

        $upload_dir = '../../assets/uploads/items/';
        $final_images = $existing_images;

        if (isset($_FILES['item_images']['name']) && is_array($_FILES['item_images']['name'])) {
            foreach ($_FILES['item_images']['name'] as $slot => $fileName) {
                if (!empty($fileName) && $_FILES['item_images']['error'][$slot] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['item_images']['tmp_name'][$slot];
                    $fileSize = $_FILES['item_images']['size'][$slot];

                    if ($fileSize <= 5242880) {
                        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                            
                            if (!empty($final_images[$slot]) && file_exists($upload_dir . $final_images[$slot])) {
                                @unlink($upload_dir . $final_images[$slot]);
                            }

                            // ชื่อไฟล์ใหม่จะเป็นเลข id_edit_1_... ซึ่งสั้นและดูแลง่ายขึ้น
                            $new_filename = $item_id . '_edit_' . ($slot + 1) . '_' . uniqid() . '.' . $ext;
                            if (move_uploaded_file($tmpName, $upload_dir . $new_filename)) {
                                $final_images[$slot] = $new_filename;
                            }
                        }
                    }
                }
            }
        }

        $final_images = array_values(array_filter($final_images));
        $images_json = json_encode($final_images, JSON_UNESCAPED_UNICODE);

        // 🟢 [แก้ไข] เปลี่ยน WHERE item_id เป็น id
        $stmtUpdate = $pdo->prepare("
            UPDATE items 
            SET category_id = ?, name = ?, description = ?, images = ? 
            WHERE id = ?
        ");
        $stmtUpdate->execute([$category_id, $name, $description, $images_json, $item_id]);

        $stmtTrans = $pdo->prepare("
            INSERT INTO inventory_transactions 
            (item_id, transaction_type, quantity, previous_stock, current_stock, remark, created_by, created_at)
            VALUES (?, 'แก้ไขสิ่งของ', 0, ?, ?, 'แก้ไขข้อมูลรายละเอียดสิ่งของ', ?, NOW())
        ");
        $stmtTrans->execute([$item_id, $oldItem['current_stock'], $oldItem['current_stock'], $user_id]);

        $pdo->commit();
        $_SESSION['success'] = 'แก้ไขข้อมูลสิ่งของเรียบร้อยแล้ว';
        
        header("Location: ../../index.php?page=manage_items");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("<div style='color:red; padding: 20px; font-size: 20px;'><b>Database Error:</b> " . $e->getMessage() . "<br><br><a href='../../index.php?page=manage_items'>กลับไปหน้าจัดการสิ่งของ</a></div>");
    }
}