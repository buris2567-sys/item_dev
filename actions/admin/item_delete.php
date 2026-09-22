<?php
session_start();
require_once '../../config/db.php';

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    exit('Unauthorized access');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['item_id'])) {
    $item_id    = $_POST['item_id'];
    // 🟢 ดักจับค่าว่าง ป้องกัน Error หาก JavaScript ส่งชื่อมาไม่ทัน
    $item_name  = !empty($_POST['item_name']) ? $_POST['item_name'] : 'ไม่ทราบชื่อรายการ';
    $deleted_by = $_SESSION['user_id'];
    
    try {
        $pdo->beginTransaction();

        // Step 1: ดึงรูปภาพมาลบออกจากเซิร์ฟเวอร์
        $imgStmt = $pdo->prepare("SELECT images FROM items WHERE item_id = ?");
        $imgStmt->execute([$item_id]);
        $item = $imgStmt->fetch();
        
        if ($item && !empty($item['images'])) {
            $images = json_decode($item['images'], true);
            if (is_array($images)) {
                foreach ($images as $img) {
                    $file_path = '../../assets/uploads/items/' . $img;
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
            }
        }

        // Step 2: บันทึก Log การลบ (ใช้ 'OUT' และระบุชื่อลงไปใน remark ตรงๆ)
        $remark = "ลบรายการสิ่งของออกจากระบบ: " . $item_name;
        $logStmt = $pdo->prepare("INSERT INTO inventory_transactions (item_id, transaction_type, quantity, remark, created_by) VALUES (?, 'OUT', 0, ?, ?)");
        $logStmt->execute([$item_id, $remark, $deleted_by]);

        // Step 3: ลบรายการสิ่งของ
        $delStmt = $pdo->prepare("DELETE FROM items WHERE item_id = ?");
        $delStmt->execute([$item_id]);

        $pdo->commit();
        $_SESSION['success'] = "ลบข้อมูลสิ่งของเรียบร้อยแล้ว";

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบ: " . $e->getMessage();
    }

    // } catch (Exception $e) {
    //     $pdo->rollBack();
    //     // 🟢 หยุดการทำงานของเว็บ แล้วพ่น Error ออกมาบนหน้าจอสีขาวเลย จะได้รู้ว่า Database ติดปัญหาอะไร
    //     die("<h3 style='color:red;'>🚨 Database Error: " . $e->getMessage() . "</h3>");
    // }

    header("Location: ../../index.php?page=manage_items");
    exit;
}
?>