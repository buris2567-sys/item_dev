<?php
require_once 'db.php';

$message = '';

// ตรวจสอบว่ามีการกดปุ่ม Submit (ส่งฟอร์ม) มาหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจากฟอร์ม
    $item_id = trim($_POST['item_id']);
    $category_id = $_POST['category_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $current_stock = (int)$_POST['current_stock'];
    $low_stock_threshold = (int)$_POST['low_stock_threshold'];

    // ป้องกันการกรอกรหัสซ้ำ และบันทึกข้อมูล
    try {
        $sql = "INSERT INTO Items (item_id, category_id, name, description, current_stock, low_stock_threshold) 
                VALUES (:item_id, :category_id, :name, :description, :current_stock, :low_stock_threshold)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':item_id' => $item_id,
            ':category_id' => $category_id,
            ':name' => $name,
            ':description' => $description,
            ':current_stock' => $current_stock,
            ':low_stock_threshold' => $low_stock_threshold
        ]);

        $message = '<div class="alert alert-success">บันทึกข้อมูลสิ่งของใหม่เรียบร้อยแล้ว! <a href="index.php" class="alert-link">กลับหน้าหลัก</a></div>';
    } catch (PDOException $e) {
        // ดักจับ Error กรณีรหัส item_id ซ้ำกับที่มีอยู่แล้ว
        if ($e->getCode() == 23000) {
            $message = '<div class="alert alert-danger">เกิดข้อผิดพลาด: รหัสสิ่งของ (Item ID) นี้มีอยู่ในระบบแล้ว</div>';
        } else {
            $message = '<div class="alert alert-danger">เกิดข้อผิดพลาด: ' . $e->getMessage() . '</div>';
        }
    }
}

// ดึงข้อมูลหมวดหมู่ที่เปิดใช้งานอยู่ เพื่อนำมาสร้าง Dropdown
$cat_stmt = $pdo->query("SELECT category_id, name FROM Categories WHERE is_active = 1");
$categories = $cat_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มสิ่งของใหม่ - ระบบเบิกจ่ายพัสดุ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">ระบบจัดการพัสดุ (Inventory)</span>
            <div class="d-flex">
                <a href="index.php" class="btn btn-outline-light btn-sm">กลับหน้ารายการ</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header text-bg-warning">
                        <h5 class="card-title mb-0">ฟอร์มเพิ่มรายการสิ่งของ</h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- แสดงข้อความแจ้งเตือนเมื่อบันทึกข้อมูล -->
                        <?= $message ?>

                        <form action="add_item.php" method="POST">
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="item_id" class="form-label">รหัสพัสดุ <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="item_id" name="item_id" placeholder="เช่น P004" required>
                                </div>
                                <div class="col-md-8">
                                    <label for="category_id" class="form-label">ประเภท/หมวดหมู่ <span class="text-danger">*</span></label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="" selected disabled>-- เลือกหมวดหมู่ --</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= htmlspecialchars($cat['category_id']) ?>">
                                                <?= htmlspecialchars($cat['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">ชื่อสิ่งของ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="ระบุชื่อรายการสิ่งของ" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">คำอธิบายรายละเอียด</label>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="ระบุรายละเอียดเพิ่มเติม (ไม่บังคับ)"></textarea>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="current_stock" class="form-label">จำนวนสต็อกเริ่มต้น <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="current_stock" name="current_stock" min="0" value="0" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="low_stock_threshold" class="form-label">จุดแจ้งเตือนของใกล้หมด <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="low_stock_threshold" name="low_stock_threshold" min="0" value="10" required>
                                    <div class="form-text">ระบบจะแสดงป้ายเตือนเมื่อสต็อกน้อยกว่าหรือเท่ากับตัวเลขนี้</div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="index.php" class="btn btn-light border">ยกเลิก</a>
                                <button type="submit" class="btn btn-warning px-4">บันทึกข้อมูล</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>