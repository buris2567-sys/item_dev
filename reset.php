<?php
require_once 'config/db.php';

// กำหนดรหัสผ่านใหม่ที่ต้องการที่นี่
$new_password = '1234';
$hashed = password_hash($new_password, PASSWORD_BCRYPT);

// อัปเดตรหัสผ่านให้ user ทั้งหมด
$stmt = $pdo->prepare("UPDATE users SET password = ?");
$result = $stmt->execute([$hashed]);

if ($result) {
    echo "<h2 style='color:green;'> เปลี่ยนรหัสผ่านเป็น '{$new_password}' สำเร็จเรียบร้อยแล้ว!</h2>";
    echo "<p>ลองกลับไปล็อกอินที่หน้า <a href='login.php'>login.php</a> ได้เลยครับ</p>";
} else {
    echo "<h2 style='color:red;'>เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล</h2>";
}
?>