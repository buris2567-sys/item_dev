<?php
// actions/admin/check_item_name.php
require_once '../../config/db.php'; 

$name = trim($_GET['name'] ?? '');
$response = ['exists' => false];

if ($name !== '') {
    try {
        // ค้นหาว่ามีชื่อนี้ในระบบและยังมีสถานะเปิดใช้งานอยู่หรือไม่
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM items WHERE name = ? AND is_active = 1");
        $stmt->execute([$name]);
        
        if ($stmt->fetchColumn() > 0) {
            $response['exists'] = true;
        }
    } catch (Exception $e) {
        // หากมี Error จากฐานข้อมูล ให้คืนค่าเป็น false เพื่อไม่ให้ระบบค้าง
        $response['exists'] = false; 
    }
}

// ส่งค่ากลับไปเป็น JSON ให้ JavaScript อ่านค่า
header('Content-Type: application/json');
echo json_encode($response);
?>
