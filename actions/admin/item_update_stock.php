<?php
session_start();
require_once '../../config/db.php';

// 1. เช็คสิทธิ์ให้ชัวร์ว่ามี user_id แน่นอน (ป้องกัน FK created_by เป็น NULL)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    die("เกิดข้อผิดพลาด: คุณไม่มีสิทธิ์ หรือ Session หมดอายุ กรุณาล็อกอินใหม่");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 🟢 เปลี่ยนมารับค่าตามฟอร์มเดิมของคุณ
    $item_id = $_POST['item_id'] ?? '';
    $action_type = $_POST['action_type'] ?? ''; // 'add' หรือ 'reduce'
    $quantity = (int)($_POST['quantity'] ?? 0);
    $remark = trim($_POST['remark'] ?? '');
    $user_id = $_SESSION['user_id']; 

    // เช็คว่าส่งข้อมูลมาครบไหม และจำนวนต้องมากกว่า 0
    if (empty($item_id) || empty($action_type) || $quantity <= 0) {
        die("เกิดข้อผิดพลาด: ข้อมูลไม่ครบถ้วน หรือกรอกจำนวนไม่ถูกต้อง");
    }

    if (empty($remark)) {
        $remark = ($action_type === 'add') ? 'ปรับเพิ่มสต็อกโดยแอดมิน' : 'ปรับลดสต็อกโดยแอดมิน';
    }

    // 🟢 แปลงค่า quantity ให้เป็นบวกหรือลบตาม action_type
    $qty_change = ($action_type === 'reduce') ? -$quantity : $quantity;

    try {
        $pdo->beginTransaction();

        // 2. ดึงข้อมูลพัสดุเดิม และใช้ LEFT JOIN ดึงชื่อหมวดหมู่มาด้วย
        $stmt = $pdo->prepare("
            SELECT i.current_stock, c.name as category_name 
            FROM items i 
            LEFT JOIN categories c ON i.category_id = c.category_id 
            WHERE i.item_id = ?
        ");
        $stmt->execute([$item_id]);
        $item = $stmt->fetch();

        if (!$item) {
            throw new Exception("ไม่พบรหัสสิ่งของ: $item_id ในระบบ");
        }

        // คำนวณสต็อกใหม่
        $new_stock = $item['current_stock'] + $qty_change;
        if ($new_stock < 0) {
            throw new Exception("สต็อกติดลบไม่ได้ (ปัจจุบันมี {$item['current_stock']} ชิ้น คุณพยายามลดออก $quantity ชิ้น)");
        }

        // 3. อัปเดตตาราง items
        $stmtUpdate = $pdo->prepare("UPDATE items SET current_stock = ? WHERE item_id = ?");
        $stmtUpdate->execute([$new_stock, $item_id]);

        // 4. บันทึกลงตารางประวัติ (inventory_transactions)
        $tx_type = ($action_type === 'add') ? 'นำเข้าสิ่งของ' : 'ถอนสิ่งของ';
        
        // ลบ category_name ออกจากวงเล็บ และลบตัวแปรที่ส่งเข้าไป
$stmtTx = $pdo->prepare("
    INSERT INTO inventory_transactions 
    (item_id, transaction_type, quantity, previous_stock, current_stock, remark, created_by, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
");
$stmtTx->execute([
    $item_id, 
    $tx_type, 
    $qty_change, 
    $item['current_stock'], 
    $new_stock, 
    $remark, 
    $user_id
]);

        $pdo->commit();
        header("Location: ../../index.php?page=manage_items");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        // ถ้ามีปัญหาเรื่อง DB หรือ FK จะแสดง Error ชัดเจน
        die("<div style='color:red; padding: 20px; font-size: 20px;'><b>Database Error:</b> " . $e->getMessage() . "<br><br><a href='../../index.php?page=manage_items'>กลับไปหน้าจัดการสิ่งของ</a></div>");
    }
}