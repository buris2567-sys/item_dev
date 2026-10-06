<?php
require_once 'config/db.php';

// 1. ระบุชื่อผู้ใช้ที่ต้องการเปลี่ยน และรหัสผ่านใหม่
$target_username = 'Chai.s'; // เปลี่ยนเป็น username ที่ต้องการ
$new_password    = '1234';   // รหัสผ่านใหม่ที่ต้องการตั้ง

// 2. เข้ารหัสรหัสผ่านด้วย Bcrypt
$hashed = password_hash($new_password, PASSWORD_BCRYPT);

// 3. อัปเดตเฉพาะ user ที่ระบุด้วย WHERE
$stmt = $pdo->prepare("UPDATE users SET password = :pass WHERE username = :username");
$result = $stmt->execute([
    ':pass'     => $hashed,
    ':username' => $target_username
]);

if ($result && $stmt->rowCount() > 0) {
    echo "<h2 style='color:green;'>เปลี่ยนรหัสผ่านของ '{$target_username}' เป็น '{$new_password}' สำเร็จแล้ว!</h2>";
} else {
    echo "<h2 style='color:red;'>ไม่พบชื่อผู้ใช้ '{$target_username}' หรือเปลี่ยนรหัสผ่านไม่สำเร็จ</h2>";
}
?>