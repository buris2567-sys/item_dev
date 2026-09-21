<?php
session_start();
require_once '../../config/db.php';

// ตรวจสอบสิทธิ์ (ต้องเป็น Admin)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    exit('Unauthorized access');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. รับค่าจากฟอร์ม
    $item_id       = 'ITM' . time(); // สร้างรหัสสินค้าอัตโนมัติเบื้องต้น (สามารถปรับเปลี่ยนรูปแบบได้)
    $category_id   = $_POST['category_id'];
    $name          = trim($_POST['name']);
    $initial_stock = (int)$_POST['initial_stock'];
    $description   = trim($_POST['description']);
    $created_by    = $_SESSION['user_id'];
    
    // 2. จัดการอัปโหลดไฟล์รูปภาพ
    $uploaded_images = [];
    $upload_dir = '../../assets/uploads/items/';
    
    // ตรวจสอบและสร้างโฟลเดอร์ถ้ายังไม่มี
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (isset($_FILES['item_images']) && !empty($_FILES['item_images']['name'][0])) {
        $file_count = count($_FILES['item_images']['name']);
        $max_files = min($file_count, 3); // บังคับอัปโหลดสูงสุดแค่ 3 รูป

        for ($i = 0; $i < $max_files; $i++) {
            $tmp_name = $_FILES['item_images']['tmp_name'][$i];
            $file_size = $_FILES['item_images']['size'][$i];
            $file_name = $_FILES['item_images']['name'][$i];
            
            // เช็คขนาดไฟล์ (ไม่เกิน 5MB)
            if ($file_size > 0 && $file_size <= 5242880) {
                // เปลี่ยนชื่อไฟล์ป้องกันชื่อซ้ำ
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $new_filename = $item_id . '_' . ($i+1) . '_' . uniqid() . '.' . $ext;
                    $destination = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($tmp_name, $destination)) {
                        $uploaded_images[] = $new_filename;
                    }
                }
            }
        }
    }
    
    // แปลง Array รายชื่อรูปภาพเป็น JSON เพื่อเก็บลงคอลัมน์ images
    $images_json = !empty($uploaded_images) ? json_encode($uploaded_images) : null;

    // 3. บันทึกลงฐานข้อมูล
    try {
        $pdo->beginTransaction();

        // 3.1 บันทึกข้อมูลพัสดุ
        $stmt = $pdo->prepare("INSERT INTO items (item_id, category_id, name, description, current_stock, images) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$item_id, $category_id, $name, $description, $initial_stock, $images_json]);

        // 3.2 บันทึกประวัติ Transaction (ถ้ายอดเริ่มต้นมากกว่า 0)
        if ($initial_stock > 0) {
            $logStmt = $pdo->prepare("INSERT INTO inventory_transactions (item_id, transaction_type, quantity, remark, created_by) VALUES (?, 'IN', ?, 'รับยอดยกมาเริ่มต้น', ?)");
            $logStmt->execute([$item_id, $initial_stock, $created_by]);
        }

        $pdo->commit();
        $_SESSION['success'] = "เพิ่มสิ่งของสำเร็จ";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $e->getMessage();
    }
    
    // เด้งกลับไปหน้าจัดการสิ่งของ
    header("Location: ../../index.php?page=manage_items");
    exit;
}
?>