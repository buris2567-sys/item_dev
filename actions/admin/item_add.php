<?php
session_start();
require_once '../../config/db.php';

// ตรวจสอบสิทธิ์ (ต้องเป็น Admin)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    exit('Unauthorized access');
}

// ตรวจสอบว่ามีการส่งข้อมูลมาด้วย Method POST (กดปุ่มบันทึกมาจากฟอร์ม) หรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    /* =========================================================
     * 1. รับค่าจากฟอร์ม และจัดเตรียมตัวแปร (Data Sanitization)
     * ========================================================= */
    // สร้างรหัสสินค้าอัตโนมัติ เช่น ITM1712345678 (นำคำว่า ITM มาต่อด้วย Timestamp ปัจจุบัน)
    $item_id       = 'ITM' . time(); 
    
    // รับค่า ID หมวดหมู่พัสดุ
    $category_id   = $_POST['category_id']; 
    
    // รับชื่อสิ่งของ พร้อมใช้ trim() ตัดช่องว่างหัว-ท้ายออก
    $name          = trim($_POST['name']); 
    
    // รับจำนวนสต็อกเริ่มต้น และแปลงค่าเป็นตัวเลขจำนวนเต็ม (int) ป้องกันการส่งค่าขยะ
    $initial_stock = (int)$_POST['initial_stock']; 
    
    // รับคำอธิบาย พร้อมตัดช่องว่างหัว-ท้าย
    $description   = trim($_POST['description']); 
    
    // ดึง user_id ของ Admin ที่กำลังล็อกอินอยู่จาก Session เพื่อเก็บประวัติว่าใครเป็นคนเพิ่ม
    $created_by    = $_SESSION['user_id']; 
    
    
    /* =========================================================
     * 2. จัดการการอัปโหลดไฟล์รูปภาพ (File Upload Processing)
     * ========================================================= */
    $uploaded_images = []; // ตัวแปร Array สำหรับเก็บชื่อไฟล์รูปที่อัปโหลดสำเร็จ
    $upload_dir = '../../assets/uploads/items/'; // กำหนดโฟลเดอร์ปลายทางที่จะเซฟรูป
    
    // ตรวจสอบโฟลเดอร์ปลายทาง ถ้ายังไม่มีให้สร้างขึ้นอัตโนมัติ (สิทธิ์ 0777)
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // เช็กว่ามีการส่งไฟล์รูปภาพมาจาก Input ที่ชื่อ item_images[] หรือไม่
    if (isset($_FILES['item_images']['name']) && is_array($_FILES['item_images']['name'])) {
        $file_count = count($_FILES['item_images']['name']); // นับจำนวนไฟล์ทั้งหมดที่ส่งมา
        
        // วนลูปอ่านทีละช่อง (จำกัดไม่เกิน 3 รูปตามเงื่อนไข)
        for ($i = 0; $i < $file_count && count($uploaded_images) < 3; $i++) {
            // ดึงข้อมูลของคุณสมบัติไฟล์แต่ละตัว
            $tmp_name  = $_FILES['item_images']['tmp_name'][$i] ?? ''; // พาธไฟล์ชั่วคราวบนเซิร์ฟเวอร์
            $file_size = $_FILES['item_images']['size'][$i] ?? 0;        // ขนาดไฟล์ (Bytes)
            $file_name = $_FILES['item_images']['name'][$i] ?? '';        // ชื่อไฟล์ดั้งเดิม
            $error     = $_FILES['item_images']['error'][$i] ?? UPLOAD_ERR_NO_FILE; // รหัสข้อผิดพลาด
            
            // ตรวจสอบเงื่อนไขความปลอดภัย: ไม่มีข้อผิดพลาด, มีไฟล์จริง, และขนาดไม่เกิน 5MB (5,242,880 Bytes)
            if ($error === UPLOAD_ERR_OK && $file_size > 0 && $file_size <= 5242880) {
                
                // ดึงนามสกุลไฟล์มาแปลงเป็นตัวพิมพ์เล็ก
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION)); 
                
                // อนุญาตเฉพาะไฟล์รูปภาพประเภท jpg, jpeg, png เท่านั้น
                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    // ตั้งชื่อไฟล์ใหม่สุ่มขึ้นมาป้องกันชื่อซ้ำ เช่น ITM1712345678_1_65f1a2b3c4d5e.jpg
                    $new_filename = $item_id . '_' . (count($uploaded_images) + 1) . '_' . uniqid() . '.' . $ext; 
                    $destination = $upload_dir . $new_filename; // พาธปลายทางเต็ม
                    
                    // ย้ายไฟล์จากโฟลเดอร์ชั่วคราวไปยังโฟลเดอร์จริง
                    if (move_uploaded_file($tmp_name, $destination)) {
                        $uploaded_images[] = $new_filename; // เพิ่มชื่อไฟล์ใหม่ลงใน Array
                    }
                }
            }
        }
    }
    
    // แปลง Array ชื่อรูปภาพเป็นข้อความ JSON เช่น ["ITM_1.jpg", "ITM_2.jpg"] เพื่อเก็บในช่องเดียวของ DB (ถ้าไม่มีรูปให้เป็น null)
    $images_json = !empty($uploaded_images) ? json_encode($uploaded_images) : null;


    /* =========================================================
     * 3. บันทึกลงฐานข้อมูล (Database Transaction)
     * ========================================================= */
    try {
        // เริ่ม Transaction เพื่อความปลอดภัย (ถ้าจุดไหนพัง สามารถ Rollback ยกเลิกทั้งหมดได้)
        $pdo->beginTransaction();

        // 3.1 เพิ่มข้อมูลสิ่งของชิ้นใหม่ลงในตาราง items
        $stmt = $pdo->prepare("INSERT INTO items (item_id, category_id, name, description, current_stock, images) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$item_id, $category_id, $name, $description, $initial_stock, $images_json]);

        // 3.2 ถ้าระบุจำนวนสต็อกเริ่มต้น > 0 ให้ลงประวัติในตาราง inventory_transactions เพื่อบันทึกเป็นยอดยกมาเข้าคลัง
        if ($initial_stock > 0) {
            $logStmt = $pdo->prepare("INSERT INTO inventory_transactions (item_id, transaction_type, quantity, remark, created_by) VALUES (?, 'IN', ?, 'รับยอดยกมาเริ่มต้น', ?)");
            $logStmt->execute([$item_id, $initial_stock, $created_by]);
        }

        // หากทำงานสำเร็จทุกขั้นตอน ให้บันทึกการเปลี่ยนแปลงลง Database จริง
        $pdo->commit();
        $_SESSION['success'] = "เพิ่มสิ่งของสำเร็จ"; // ฝากข้อความแจ้งเตือนสำเร็จลง Session
        
    } catch (Exception $e) {
        // หากเกิดข้อผิดพลาดระหว่างทาง ให้ยกเลิกคำสั่ง SQL ทั้งหมดทันที
        $pdo->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $e->getMessage(); // ฝากข้อความแจ้งเตือนข้อผิดพลาด
    }
    
    /* =========================================================
     * 4. รีไดเรกต์กลับหน้าหลัก
     * ========================================================= */
    // ส่งผู้ใช้อย่างปลอดภัยกลับไปที่หน้าจัดการสิ่งของ เพื่อป้องกันการยิง submit ซ้ำเมื่อกด F5
    header("Location: ../../index.php?page=manage_items");
    exit;

}
?>