<?php
session_start();
require_once '../config/db.php'; // ตรวจสอบ path ให้ตรงกับโครงสร้างของคุณ

// 1. ตรวจสอบสิทธิ์และการเข้าถึง
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

// ฟังก์ชันสำหรับเด้งกลับพร้อมส่งข้อความ Error
function redirectWithError($message) {
    // ใช้ Session เก็บข้อความ Error เพื่อนำไปแสดงเป็น Alert 
    // (สามารถเปลี่ยนชื่อตัวแปร profile_error เป็นอย่างอื่นได้ถ้าในหน้า UI ใช้ชื่ออื่น)
    $_SESSION['profile_error'] = $message;
    header('Location: ../index.php?page=profile');
    exit;
}

// 2. รับค่าจากฟอร์ม
$oldPassword = $_POST['old_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// 3. ตรวจสอบความถูกต้องเบื้องต้น (Validation)
if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
    redirectWithError('กรุณากรอกข้อมูลรหัสผ่านให้ครบทุกช่อง');
}

if (strlen($newPassword) < 8) {
    redirectWithError('รหัสผ่านใหม่ต้องมีอย่างน้อย 8 ตัวอักษร');
}

if ($newPassword !== $confirmPassword) {
    redirectWithError('รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน');
}

if ($oldPassword === $newPassword) {
    redirectWithError('รหัสผ่านใหม่ต้องไม่ซ้ำกับรหัสผ่านเดิม');
}

try {
    // 4. ดึงข้อมูลรหัสผ่านที่เข้ารหัสไว้ (Hash) จากฐานข้อมูลมาตรวจสอบ
    $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        redirectWithError('ไม่พบข้อมูลบัญชีผู้ใช้งานในระบบ');
    }

    // 5. นำรหัสผ่านเดิมที่พิมพ์มา เทียบกับรหัสผ่านใน Database
    if (!password_verify($oldPassword, $user['password'])) {
        redirectWithError('รหัสผ่านเดิมไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง');
    }

    // 6. ถ้ารหัสผ่านเดิมถูกต้อง ให้เข้ารหัสผ่านใหม่ (Hash) แล้วบันทึกลง Database
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $updateStmt->execute([$hashedPassword, $_SESSION['user_id']]);

    // 7. บันทึกสำเร็จ ส่งข้อความแจ้งเตือนและเด้งกลับหน้าหลัก/หน้าโปรไฟล์
    $_SESSION['profile_success'] = 'เปลี่ยนรหัสผ่านเสร็จสมบูรณ์เรียบร้อยแล้ว';
    header('Location: ../index.php?page=profile'); // หรือ ../index.php?page=home แล้วแต่คุณออกแบบไว้
    exit;

} catch (PDOException $e) {
    // กรณี Database มีปัญหา
    redirectWithError('เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล กรุณาลองใหม่อีกครั้งในภายหลัง');
}
?>