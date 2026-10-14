<?php
if (!defined('APP_RUNNING')) {
    session_start();
    require_once '../../config/db.php';
} else {
    require_once 'config/db.php';
}

// ตรวจสอบสิทธิ์ผู้ใช้งาน
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    $_SESSION['error'] = 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถทำรายการนี้ได้';
    header("Location: ../../index.php?page=manage_items");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = trim($_POST['category_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $initial_stock = (int)($_POST['initial_stock'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $user_id = $_SESSION['user_id'] ?? null;

    // 🟢 1. ตรวจสอบข้อมูลบังคับเพื่อป้องกัน NOT NULL Violation
    if (empty($category_id) || empty($name)) {
        $_SESSION['error'] = 'กรุณากรอกข้อมูลหมวดหมู่และชื่อสิ่งของให้ครบถ้วน';
        header("Location: ../../index.php?page=manage_items");
        exit;
    }

    // 🟢 2. ตรวจสอบว่า category_id มีอยู่จริงในตาราง categories หรือไม่ (ป้องกัน Foreign Key Error #1452)
    $stmtCat = $pdo->prepare("SELECT name FROM categories WHERE category_id = ?");
    $stmtCat->execute([$category_id]);
    $category_name = $stmtCat->fetchColumn();

    if (!$category_name) {
        $_SESSION['error'] = 'หมวดหมู่ที่เลือกไม่มีอยู่ในระบบ กรุณาเลือกใหม่';
        header("Location: ../../index.php?page=manage_items");
        exit;
    }

    // 🟢 3. ตรวจสอบว่า user_id ของผู้สร้างมีตัวตนในตาราง users หรือไม่
    $stmtUser = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $stmtUser->execute([$user_id]);
    if (!$stmtUser->fetchColumn()) {
        $user_id = null; // หากไม่พบผู้ใช้ ให้ตั้งเป็น NULL เพื่อไม่ให้ขัดกับ FK fk_trans_user
    }

    // สร้างรหัสสินค้าแบบสุ่มเพื่อใช้เป็น Primary Key (item_id)
    $item_id = 'ITM' . time() . rand(100, 999);

    // 🟢 4. จัดการอัปโหลดรูปภาพ (เก็บเป็น JSON Array)
    $uploaded_images = [];
    $upload_dir = '../../assets/uploads/items/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (isset($_FILES['item_images']['name']) && is_array($_FILES['item_images']['name'])) {
        $file_count = count($_FILES['item_images']['name']);
        for ($i = 0; $i < $file_count && count($uploaded_images) < 3; $i++) {
            $tmp_name = $_FILES['item_images']['tmp_name'][$i] ?? '';
            $file_size = $_FILES['item_images']['size'][$i] ?? 0;
            $file_name = $_FILES['item_images']['name'][$i] ?? '';
            $error = $_FILES['item_images']['error'][$i] ?? UPLOAD_ERR_NO_FILE;

            if ($error === UPLOAD_ERR_OK && $file_size > 0 && $file_size <= 5242880) {
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $new_filename = $item_id . '_' . (count($uploaded_images) + 1) . '_' . uniqid() . '.' . $ext;
                    $destination = $upload_dir . $new_filename;
                    if (move_uploaded_file($tmp_name, $destination)) {
                        $uploaded_images[] = $new_filename;
                    }
                }
            }
        }
    }

    $images_json = json_encode($uploaded_images, JSON_UNESCAPED_UNICODE);

    try {
        $pdo->beginTransaction();

        // 🟢 5. เพิ่มข้อมูลลงตาราง items

        // ลบ category_name ออกจากการ INSERT ประวัติ
        $stmtTrans = $pdo->prepare("
        INSERT INTO inventory_transactions 
        (item_id, transaction_type, quantity, previous_stock, current_stock, remark, created_by, created_at)
        VALUES (?, 'สร้างรายการ', ?, 0, ?, 'ลงทะเบียนเพิ่มสิ่งของใหม่ในระบบ', ?, NOW())
        ");
        $stmtTrans->execute([$item_id, $initial_stock, $initial_stock, $user_id]);

        // 🟢 6. บันทึกประวัติการสร้างสิ่งของลงตาราง inventory_transactions
        $stmtTrans = $pdo->prepare("
            INSERT INTO inventory_transactions 
            (item_id, category_name, transaction_type, quantity, previous_stock, current_stock, remark, created_by, created_at)
            VALUES (?, ?, 'สร้างสิ่งของ', ?, 0, ?, 'ลงทะเบียนเพิ่มสิ่งของใหม่ในระบบ', ?, NOW())
        ");
        $stmtTrans->execute([$item_id, $category_name, $initial_stock, $initial_stock, $user_id]);

        $pdo->commit();
        $_SESSION['success'] = 'เพิ่มสิ่งของเรียบร้อยแล้ว';
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage();
    }

    header("Location: ../../index.php?page=manage_items");
    exit;
}
