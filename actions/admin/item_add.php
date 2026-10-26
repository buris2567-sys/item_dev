<?php
session_start();
require_once '../../config/db.php';

// 1. ตรวจสอบสิทธิ์ผู้ใช้งาน
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    die("เกิดข้อผิดพลาด: คุณไม่มีสิทธิ์ หรือ Session หมดอายุ กรุณาล็อกอินใหม่");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = trim($_POST['category_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $initial_stock = (int)($_POST['initial_stock'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $user_id = $_SESSION['user_id'];

    if (empty($category_id) || empty($name)) {
        die("เกิดข้อผิดพลาด: กรุณากรอกข้อมูลหมวดหมู่และชื่อสิ่งของให้ครบถ้วน");
    }

    // 🟢 [แก้ไข] สร้างรหัสเพื่อใช้เป็น "ป้ายชื่อ (item_code)" ไว้สำหรับโชว์เท่านั้น ไม่ใช่ Primary Key แล้ว
    $item_code = 'ITM' . time() . rand(100, 999);

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
                    // ตั้งชื่อไฟล์รูปภาพใหม่โดยใช้ item_code เพื่อให้เชื่อมโยงได้
                    $new_filename = $item_code . '_' . (count($uploaded_images) + 1) . '_' . uniqid() . '.' . $ext;
                    $destination = $upload_dir . $new_filename;
                    if (move_uploaded_file($tmp_name, $destination)) {
                        $uploaded_images[] = $new_filename;
                    }
                }
            }
        }
    }

    $name = trim($_POST['name']);

    // เช็คชื่อซ้ำที่ Backend อีกรอบก่อนบันทึก
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM items WHERE name = ? ");
    $checkStmt->execute([$name]);
    if ($checkStmt->fetchColumn() > 0) {
        // ถ้าชื่อซ้ำ ให้ส่งกลับไปหน้าเดิมพร้อมแจ้งเตือน Error
        $_SESSION['error'] = "ไม่สามารถเพิ่มได้: มีสิ่งของชื่อนี้ในระบบอยู่แล้ว";
        header("Location: ../../index.php?page=item_list");
        exit();
    }

    // ถ้าไม่ซ้ำ ถึงจะรันคำสั่ง INSERT INTO ... ต่อไป

    $images_json = json_encode($uploaded_images, JSON_UNESCAPED_UNICODE);

    try {
        $pdo->beginTransaction();

        // 🟢 [แก้ไข] INSERT ข้อมูลลงตาราง items (ไม่ต้องใส่ id เพราะเป็น Auto Increment)
        $stmtInsert = $pdo->prepare("
            INSERT INTO items (item_code, category_id, name, description, current_stock, images, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmtInsert->execute([$item_code, $category_id, $name, $description, $initial_stock, $images_json]);

        // 🟢 [แก้ไข] ดึงรหัสตัวเลข (id) ที่เพิ่งรันขึ้นมาใหม่สุดของตาราง items
        $new_item_id = $pdo->lastInsertId();

        // บันทึกประวัติลงตาราง inventory_transactions โดยใช้ id แบบตัวเลข ($new_item_id)
        $stmtTrans = $pdo->prepare("
            INSERT INTO inventory_transactions 
            (item_id, transaction_type, quantity, previous_stock, current_stock, remark, created_by, created_at)
            VALUES (?, 'สร้างสิ่งของ', ?, 0, ?, 'ลงทะเบียนเพิ่มสิ่งของใหม่ในระบบ', ?, NOW())
        ");
        $stmtTrans->execute([$new_item_id, $initial_stock, $initial_stock, $user_id]);

        $pdo->commit();
        $_SESSION['success'] = 'เพิ่มสิ่งของเรียบร้อยแล้ว';

        header("Location: ../../index.php?page=manage_items");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("<div style='color:red; padding: 20px; font-size: 20px;'><b>Database Error:</b> " . $e->getMessage() . "<br><br><a href='../../index.php?page=manage_items'>กลับไปหน้าจัดการสิ่งของ</a></div>");
    }
}
