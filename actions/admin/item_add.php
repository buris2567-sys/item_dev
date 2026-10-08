<?php
session_start();
require_once '../../config/db.php';

// ตรวจสอบสิทธิ์ (ต้องเป็น Admin)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    exit('Unauthorized access');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
// เพื่อสร้าง รหัสสิ่งของที่ไม่ซ้ำกันอัตโนมัติ (Unique ID) โดยที่ผู้ใช้ไม่ต้องนั่งคิดรหัสเอง
// $item_id       = 'ITM' . time();  แทน Auto Increment ID ของฐานข้อมูล
    $item_id       = 'ITM' . time(); 
    $category_id   = $_POST['category_id']; 
    $name          = trim($_POST['name']); 
    $initial_stock = (int)$_POST['initial_stock']; 
    $description   = trim($_POST['description']); 
    $created_by    = $_SESSION['user_id']; 
    
    // 🟢 1. ดึง "ชื่อผู้ทำรายการ" เตรียมไว้บันทึก
    $userStmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
    $userStmt->execute([$created_by]);
    $username = $userStmt->fetchColumn() ?: 'System';

    // 🟢 2. ดึง "ชื่อหมวดหมู่" เตรียมไว้บันทึก Snapshot
    $catStmt = $pdo->prepare("SELECT name FROM categories WHERE category_id = ?");
    $catStmt->execute([$category_id]);
    $category_name = $catStmt->fetchColumn() ?: '-';

    $uploaded_images = []; 
    // [Refactored] Replaced hardcoded '../../assets/uploads/items/' with UPLOAD_PATH constant
    $upload_dir = UPLOAD_PATH; 
    
    if (!is_dir($upload_dir)) {
        // [Refactored] Replaced hardcoded 0777 permission with UPLOAD_DIR_PERMISSION constant
        mkdir($upload_dir, UPLOAD_DIR_PERMISSION, true);
    }

    if (isset($_FILES['item_images']['name']) && is_array($_FILES['item_images']['name'])) {
        $file_count = count($_FILES['item_images']['name']); 
        
        // [Refactored] Replaced hardcoded max images limit '3' with MAX_ITEM_IMAGES constant
        for ($i = 0; $i < $file_count && count($uploaded_images) < MAX_ITEM_IMAGES; $i++) {
            $tmp_name  = $_FILES['item_images']['tmp_name'][$i] ?? ''; 
            $file_size = $_FILES['item_images']['size'][$i] ?? 0;        
            $file_name = $_FILES['item_images']['name'][$i] ?? '';        
            $error     = $_FILES['item_images']['error'][$i] ?? UPLOAD_ERR_NO_FILE; 
            
            // [Refactored] Replaced hardcoded 5242880 (5MB) with MAX_UPLOAD_SIZE constant
            if ($error === UPLOAD_ERR_OK && $file_size > 0 && $file_size <= MAX_UPLOAD_SIZE) {
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION)); 
                
                // [Refactored] Replaced hardcoded ['jpg', 'jpeg', 'png'] with ALLOWED_IMAGE_TYPES constant
                if (in_array($ext, ALLOWED_IMAGE_TYPES)) {
                    $new_filename = $item_id . '_' . (count($uploaded_images) + 1) . '_' . uniqid() . '.' . $ext; 
                    $destination = $upload_dir . $new_filename; 
                    
                    if (move_uploaded_file($tmp_name, $destination)) {
                        $uploaded_images[] = $new_filename; 
                    }
                }
            }
        }
    }
    
    $images_json = !empty($uploaded_images) ? json_encode($uploaded_images) : null;

  
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO items (item_id, category_id, name, description, current_stock, images) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$item_id, $category_id, $name, $description, $initial_stock, $images_json]);

        // เตรียมข้อมูลสำหรับการบันทึกประวัติการเพิ่ม
        $previous_stock = 0;
        $current_stock = $initial_stock;
        
        // 🟢 3. สร้างข้อความ Remark ให้มี "ชื่อคนทำ + ชื่อสิ่งของ + ประเภท" อย่างครบถ้วน
        $logRemark = "เพิ่มสิ่งของใหม่โดย {$username}";


    
        // บันทึก Snapshot ลงตารางประวัติ*******************************************
        $logStmt = $pdo->prepare("
            INSERT INTO inventory_transactions 
            (item_id, item_name, category_name, transaction_type, quantity, previous_stock, current_stock, remark, created_by) 
            VALUES (?, ?, ?, 'สร้างสิ่งของ', ?, ?, ?, ?, ?)
        ");
        $logStmt->execute([$item_id, $name, $category_name, $initial_stock, $previous_stock, $current_stock, $logRemark, $created_by]);

        $pdo->commit();
        $_SESSION['success'] = "เพิ่มสิ่งของสำเร็จ"; 
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $e->getMessage(); 
    }
    
    header("Location: ../../index.php?page=manage_items");
    exit;
}
?>