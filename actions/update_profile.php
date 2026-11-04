<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

function backWithProfileError(string $message): void {
    $_SESSION['profile_error'] = $message;
    header('Location: ../index.php?page=profile');
    exit;
}

if (!hash_equals($_SESSION['profile_form_token'] ?? '', $_POST['form_token'] ?? '')) {
    backWithProfileError('ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่อีกครั้ง');
}

$fullName = trim((string) ($_POST['full_name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phoneNumber = trim((string) ($_POST['phone_number'] ?? ''));
$employmentTypeId = trim((string) ($_POST['employment_type_id'] ?? ''));
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$_SESSION['profile_old'] = ['full_name' => $fullName, 'email' => $email, 'phone_number' => $phoneNumber, 'employment_type_id' => $employmentTypeId];

if ($fullName === '' || $email === '' || $phoneNumber === '' || !ctype_digit($employmentTypeId)) {
    backWithProfileError('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
}
if (mb_strlen($fullName) > 100 || mb_strlen($email) > 150 || mb_strlen($phoneNumber) > 20 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    backWithProfileError('ข้อมูลบางรายการมีความยาวเกินกำหนด');
}
if (($newPassword !== '' || $confirmPassword !== '') && (strlen($newPassword) < 8 || $newPassword !== $confirmPassword)) {
    backWithProfileError('รหัสผ่านใหม่ต้องมีอย่างน้อย 8 ตัวอักษรและยืนยันให้ตรงกัน');
}

try {
    $stmt = $pdo->prepare('SELECT employment_type_id FROM employment_types WHERE employment_type_id = ?');
    $stmt->execute([(int) $employmentTypeId]);
    if (!$stmt->fetch()) {
        backWithProfileError('ประเภทบุคลากรไม่ถูกต้อง');
    }
    $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = ? AND user_id <> ? LIMIT 1');
    $stmt->execute([$email, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        backWithProfileError('อีเมลนี้ถูกใช้แล้ว');
    }

    if ($newPassword !== '') {
        $stmt = $pdo->prepare('UPDATE users SET full_name = ?, email = ?, phone_number = ?, employment_type_id = ?, password = ? WHERE user_id = ?');
        $stmt->execute([$fullName, $email, $phoneNumber, (int) $employmentTypeId, password_hash($newPassword, PASSWORD_DEFAULT), $_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare('UPDATE users SET full_name = ?, email = ?, phone_number = ?, employment_type_id = ? WHERE user_id = ?');
        $stmt->execute([$fullName, $email, $phoneNumber, (int) $employmentTypeId, $_SESSION['user_id']]);
    }
    $_SESSION['full_name'] = $fullName;
    unset($_SESSION['profile_old'], $_SESSION['profile_form_token']);
    $_SESSION['profile_success'] = 'บันทึกข้อมูลส่วนตัวเรียบร้อยแล้ว';
    header('Location: ../index.php?page=home');
} catch (PDOException $exception) {
    backWithProfileError('ไม่สามารถบันทึกข้อมูลได้ในขณะนี้ กรุณาลองใหม่อีกครั้ง');
}
