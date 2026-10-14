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
    $category_id = trim($_POST['category_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $user_id = $_SESSION['user_id'] ?? null;

    // 🟢 1. ตรวจสอบข้อมูลบังคับ
    if (empty($item_id) || empty($category_id) || empty($name)) {
        $_SESSION['error'] = 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน';
        header("Location: ../../index.php?page=manage_items");
        exit;
    }

    // 🟢 2. ดึงข้อมูลพัสดุเดิมเพื่อตรวจสอบความถูกต้องและจัดการรูปภาพ
    $stmtOld = $pdo->prepare("SELECT * FROM items WHERE item_id = ?");
    $stmtOld->execute([$item_id]);
    $oldItem = $stmtOld->fetch();

    if (!$oldItem) {
        $_SESSION['error'] = 'ไม่พบรายการสิ่งของที่ต้องการแก้ไข';
        header("Location: ../../index.php?page=manage_items");
        exit;
    }

    // 🟢 3. ตรวจสอบว่า category_id มีอยู่จริงในตาราง categories (ป้องกัน FK Error #1452)
    $stmtCat = $pdo->prepare("SELECT name FROM categories WHERE category_id = ?");
    $stmtCat->execute([$category_id]);
    $category_name = $stmtCat->fetchColumn();

    if (!$category_name) {
        $_SESSION['error'] = 'หมวดหมู่ที่เลือกไม่มีอยู่ในระบบ';
        header("Location: ../../index.php?page=manage_items");
        exit;
    }

    // 🟢 4. จัดการรูปภาพเดิมร่วมกับรูปภาพที่อัปโหลดใหม่
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
                        // ลบรูปเดิมในตำแหน่ง slot นั้นออกหากมีอยู่
                        if (!empty($final_images[$slot]) && file_exists($upload_dir . $final_images[$slot])) {
                            @unlink($upload_dir . $final_images[$slot]);
                        }

                        $new_filename = $item_id . '_edit_' . ($slot + 1) . '_' . uniqid() . '.' . $ext;
                        if (move_uploaded_file($tmpName, $upload_dir . $new_filename)) {
                            $final_images[$slot] = $new_filename;
                        }
                    }
                }
            }
        }
    }

    // เคลียร์ index ของ array ให้เรียงลำดับต่อเนื่อง
    $final_images = array_values(array_filter($final_images));
    $images_json = json_encode($final_images, JSON_UNESCAPED_UNICODE);

    try {
        $pdo->beginTransaction();

        // 🟢 5. อัปเดตข้อมูลลงตาราง items
        $stmtUpdate = $pdo->prepare("
            UPDATE items 
            SET category_id = ?, name = ?, description = ?, images = ? 
            WHERE item_id = ?
        ");
        $stmtUpdate->execute([$category_id, $name, $description, $images_json, $item_id]);

        // 🟢 6. บันทึกประวัติการแก้ไขลงตาราง inventory_transactions
        $stmtTrans = $pdo->prepare("
            INSERT INTO inventory_transactions 
            (item_id, category_name, transaction_type, quantity, previous_stock, current_stock, remark, created_by, created_at)
            VALUES (?, ?, 'แก้ไขสิ่งของ', 0, ?, ?, 'แก้ไขข้อมูลรายละเอียดสิ่งของ', ?, NOW())
        ");
        $stmtTrans->execute([$item_id, $category_name, $oldItem['current_stock'], $oldItem['current_stock'], $user_id]);

        $pdo->commit();
        $_SESSION['success'] = 'แก้ไขข้อมูลสิ่งของเรียบร้อยแล้ว';
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage();
    }

    header("Location: ../../index.php?page=manage_items");
    exit;
}