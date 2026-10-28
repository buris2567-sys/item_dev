<?php
// ไฟล์: actions/admin/category_add.php
session_start();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');

    if ($name !== '') {
        try {
            // เพิ่มหมวดหมู่ใหม่ โดยให้ค่าเริ่มต้น is_active = 1 (เปิดใช้งาน)
            $stmt = $pdo->prepare("INSERT INTO categories (name, is_active) VALUES (?, 1)");
            $stmt->execute([$name]);
            
            $_SESSION['success'] = "เพิ่มประเภทสิ่งของเรียบร้อยแล้ว";
        } catch (Exception $e) {
            $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "กรุณากรอกชื่อประเภทสิ่งของ";
    }
    
    // เปลี่ยนตรง manage_categories ให้ตรงกับชื่อหน้า URL ปัจจุบันของคุณ
    header("Location: ../../index.php?page=manage_categories"); 
    exit();
}
?>