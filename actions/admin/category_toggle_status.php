<?php
// ไฟล์: actions/admin/category_toggle_status.php
error_reporting(0);
require_once '../../config/db.php'; 

// รับค่าจาก AJAX
$id = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null;

$response = ['success' => false, 'message' => ''];

if ($id !== null && $status !== null) {
    try {
        // เริ่มต้น Transaction (ถ้าพัง ให้ยกเลิกทั้งหมด ป้องกันข้อมูลอัปเดตไม่ครบ)
        $pdo->beginTransaction();

        // 1. อัปเดตสถานะของหมวดหมู่ (Category)
        $stmtCat = $pdo->prepare("UPDATE categories SET is_active = ? WHERE category_id = ?");
        $stmtCat->execute([$status, $id]);

        // 2. 🟢 อัปเดตสถานะของสิ่งของ (Items) ทั้งหมดที่อยู่ในหมวดหมู่นี้ตามไปด้วย (Cascading Soft Delete)
        $stmtItem = $pdo->prepare("UPDATE items SET is_active = ? WHERE category_id = ?");
        $stmtItem->execute([$status, $id]);

        // ยืนยันการบันทึกข้อมูล
        $pdo->commit();
        $response['success'] = true;

    } catch (Exception $e) {
        // ถ้าระบบฐานข้อมูลมีปัญหา ให้ย้อนกลับ (Rollback)
        $pdo->rollBack();
        $response['message'] = 'เกิดข้อผิดพลาดในฐานข้อมูล: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'ข้อมูลที่ส่งมาไม่ครบถ้วน';
}

// ล้างขยะหน้าจอและส่ง JSON กลับไป
ob_clean();
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>