<?php
// ไฟล์: actions/admin/category_edit.php
session_start();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'] ?? '';
    $name = trim($_POST['name'] ?? '');

    if ($category_id !== '' && $name !== '') {
        try {
            // อัปเดตชื่อหมวดหมู่ตาม ID
            $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE category_id = ?");
            $stmt->execute([$name, $category_id]);
            
            $_SESSION['success'] = "แก้ไขชื่อประเภทสิ่งของเรียบร้อยแล้ว";
        } catch (Exception $e) {
            $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "ข้อมูลไม่ครบถ้วน";
    }
    
    // เปลี่ยนตรง manage_categories ให้ตรงกับชื่อหน้า URL ปัจจุบันของคุณ
    header("Location: ../../index.php?page=manage_categories"); 
    exit();
}
?>