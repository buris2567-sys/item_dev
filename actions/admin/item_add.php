<?php
session_start();
require_once '../../config/db.php';

// 1. ตรวจสอบสิทธิ์ผู้ใช้งาน (ป้องกัน FK created_by เป็น NULL)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    die("เกิดข้อผิดพลาด: คุณไม่มีสิทธิ์ หรือ Session หมดอายุ กรุณาล็อกอินใหม่");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = trim($_POST['category_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $initial_stock = (int)($_POST['initial_stock'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $user_id = $_SESSION['user_id'];

    // 2. ตรวจสอบข้อมูลบังคับเพื่อป้องกัน NOT NULL Violation
    if (empty($category_id) || empty($name)) {
        die("เกิดข้อผิดพลาด: กรุณากรอกข้อมูลหมวดหมู่และชื่อสิ่งของให้ครบถ้วน");
    }

    // สร้างรหัสสินค้าแบบสุ่มเพื่อใช้เป็น Primary Key (item_id)
    $item_id = 'ITM' . time() . rand(100, 999);

    // 3. จัดการอัปโหลดรูปภาพ (เก็บเป็น JSON Array ไม่เกิน 3 รูป)
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

        // 4. เพิ่มข้อมูลลงตาราง items 
        // (is_active จะถูกกำหนดเป็น 1 อัตโนมัติจาก Default ของ Database)
        $stmtInsert = $pdo->prepare("
            INSERT INTO items (item_id, category_id, name, description, current_stock, images, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmtInsert->execute([$item_id, $category_id, $name, $description, $initial_stock, $images_json]);

        // 5. บันทึกประวัติการสร้างสิ่งของลงตาราง inventory_transactions
        // 🟢 นำ category_name ออกจากการ INSERT เรียบร้อยแล้ว (อ้างอิงผ่าน FK item_id แทน)
        $stmtTrans = $pdo->prepare("
            INSERT INTO inventory_transactions 
            (item_id, transaction_type, quantity, previous_stock, current_stock, remark, created_by, created_at)
            VALUES (?, 'สร้างสิ่งของ', ?, 0, ?, 'ลงทะเบียนเพิ่มสิ่งของใหม่ในระบบ', ?, NOW())
        ");
        $stmtTrans->execute([$item_id, $initial_stock, $initial_stock, $user_id]);

        $pdo->commit();
        $_SESSION['success'] = 'เพิ่มสิ่งของเรียบร้อยแล้ว';
        
        // เมื่อสำเร็จให้พากลับไปหน้าจัดการสิ่งของ
        header("Location: ../../index.php?page=manage_items");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        // 🟢 โชว์ Error ทันที ถ้าระบบหยุดกะทันหัน เช่น FK มีปัญหา (หมวดหมู่ไม่ตรง)
        die("<div style='color:red; padding: 20px; font-size: 20px;'><b>Database Error:</b> " . $e->getMessage() . "<br><br><a href='../../index.php?page=manage_items'>กลับไปหน้าจัดการสิ่งของ</a></div>");
    }
}