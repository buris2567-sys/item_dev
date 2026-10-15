<?php
// เริ่ม session หากยังไม่ได้ประกาศใน db.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        // ค้นหาผู้ใช้ตาม username
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        // ตรวจสอบ Hashing Password
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']       = $user['user_id'];
            $_SESSION['full_name']     = $user['full_name'];
            $_SESSION['role']          = $user['role'];
            $_SESSION['department_id'] = $user['department_id'];
            
            // 🟢 เพิ่มบรรทัดนี้: บันทึกเวลาที่ล็อกอินหรือขยับเมาส์ล่าสุด
            $_SESSION['last_activity'] = time(); 

            header("Location: ../index.php");
            exit;
        } else {
            $_SESSION['error'] = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
        }
    } else {
        $_SESSION['error'] = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    }
}

header("Location: ../login.php");
exit;