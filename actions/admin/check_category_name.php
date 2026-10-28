<?php
// ไฟล์: actions/admin/check_category_name.php
error_reporting(0);
require_once '../../config/db.php';

$name = trim($_GET['name'] ?? '');
$id = $_GET['id'] ?? ''; // จะมีค่าส่งมาเฉพาะตอนแก้ไข
$response = ['exists' => false];

if ($name !== '') {
    try {
        if ($id !== '') {
            // กรณี "แก้ไข": ห้ามซ้ำกับชื่อคนอื่น (แต่ใช้ชื่อเดิมของตัวเองได้)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = ? AND category_id != ?");
            $stmt->execute([$name, $id]);
        } else {
            // กรณี "เพิ่มใหม่": ห้ามซ้ำกับชื่อใดๆ ในระบบเลย
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = ?");
            $stmt->execute([$name]);
        }
        
        if ($stmt->fetchColumn() > 0) {
            $response['exists'] = true;
        }
    } catch (Exception $e) {
        $response['exists'] = false; 
    }
}

// ล้างขยะเพื่อป้องกัน Error แปลง JSON แบบครั้งก่อน
ob_clean();
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>