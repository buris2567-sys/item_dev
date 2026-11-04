<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit;
}

$fields = ['full_name', 'username', 'email', 'phone_number', 'position', 'department_id', 'sub_department_id', 'employment_type_id'];
$old = []; 
foreach ($fields as $field) {
    $old[$field] = trim((string) ($_POST[$field] ?? ''));
}
$_SESSION['register_old'] = $old;

function backWithRegisterError(string $message): void {
    $_SESSION['register_error'] = $message;
    header('Location: ../register.php');
    exit;
}
// CSRF Token เพื่อตรวจสอบว่าข้อมูลฟอร์มที่ส่งมานั้น ถูกส่งมาจากหน้าเว็บของเราจริงๆ ไม่ใช่ถูกแฮกเกอร์เขียนสคริปต์ยิงข้อมูลมาจากเว็บอื่น
if (!hash_equals($_SESSION['form_token'] ?? '', $_POST['form_token'] ?? '')) {
    backWithRegisterError('ไม่สามารถส่งแบบฟอร์มได้ กรุณาลองใหม่อีกครั้ง');
}

$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
if (in_array('', $old, true) || $password === '' || $confirmPassword === '') {
    backWithRegisterError('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
}
if (mb_strlen($old['full_name']) > 100 || mb_strlen($old['username']) > 50 || mb_strlen($old['email']) > 150 || mb_strlen($old['phone_number']) > 20 || mb_strlen($old['position']) > 100) {
    backWithRegisterError('ข้อมูลบางรายการมีความยาวเกินกำหนด');
}
if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
    backWithRegisterError('รูปแบบอีเมลไม่ถูกต้อง');
}
if (strlen($password) < 8) {
    backWithRegisterError('รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร');
}
if ($password !== $confirmPassword) {
    backWithRegisterError('ยืนยันรหัสผ่านไม่ตรงกัน');
}
if (!ctype_digit($old['department_id']) || !ctype_digit($old['sub_department_id']) || !ctype_digit($old['employment_type_id'])) {
    backWithRegisterError('ข้อมูลหน่วยงานไม่ถูกต้อง');
}

try {
    $stmt = $pdo->prepare('SELECT user_id FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$old['username']]);
    if ($stmt->fetch()) {
        backWithRegisterError('ชื่อผู้ใช้งานนี้ถูกใช้แล้ว');
    }
    $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$old['email']]);
    if ($stmt->fetch()) {
        backWithRegisterError('อีเมลนี้ถูกใช้แล้ว');
    }

    $stmt = $pdo->prepare('SELECT sub_department_id FROM sub_departments WHERE sub_department_id = ? AND department_id = ?');
    $stmt->execute([(int) $old['sub_department_id'], (int) $old['department_id']]);
    if (!$stmt->fetch()) {
        backWithRegisterError('กลุ่มที่เลือกไม่อยู่ในกองที่เลือก');
    }

    $stmt = $pdo->prepare('SELECT employment_type_id FROM employment_types WHERE employment_type_id = ?');
    $stmt->execute([(int) $old['employment_type_id']]);
    if (!$stmt->fetch()) {
        backWithRegisterError('ประเภทบุคลากรไม่ถูกต้อง');
    }

    $stmt = $pdo->prepare('INSERT INTO users (username, password, full_name, email, department_id, sub_department_id, employment_type_id, phone_number, position, role, approve_user, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "User", 0, NOW())');
    $stmt->execute([
        $old['username'], password_hash($password, PASSWORD_DEFAULT), $old['full_name'], $old['email'],
        (int) $old['department_id'], (int) $old['sub_department_id'], (int) $old['employment_type_id'],
        $old['phone_number'], $old['position']
    ]);

    unset($_SESSION['register_old'], $_SESSION['form_token']);
    $_SESSION['success'] = 'ลงทะเบียนสำเร็จ ';
    header('Location: ../login.php');
} catch (PDOException $exception) {
    backWithRegisterError('ไม่สามารถลงทะเบียนได้ในขณะนี้ กรุณาลองใหม่อีกครั้ง');
}


// ป้องกัน SQL Injection ได้อย่างเด็ดขาดเพราะใช้เทคนิค Prepared Statement ซึ่งมีหัวใจสำคัญคือการ "แยกโครงสร้างคำสั่ง SQL ออกจากข้อมูลของผู้ใช้" ครับ

// เตรียมโครงสร้าง (Prepare): ครงสร้างคำสั่ง SQL ไปให้ฐานข้อมูลตรวจสอบและคอมไพล์ (Compile) ไว้ล่วงหน้า โดยใส่เครื่องหมาย ? (Placeholder) แทนที่ข้อมูลที่ยังไม่รู้
// ณ จุดนี้ฐานข้อมูลจะล็อกโครงสร้างไว้แล้วว่า "นี่คือคำสั่งดึงข้อมูล (SELECT) ด้วยเงื่อนไข username

// เติมข้อมูล (Execute): ในบรรทัด $stmt->execute(...) ระบบถึงจะส่งข้อมูลที่ผู้ใช้พิมพ์จริงๆ (
    // $old['username']) ตามไปเติมในช่อง ?

    // ctype_digit คือฟังก์ชันใน PHP ที่ใช้ตรวจสอบว่าตัวอักษรทั้งหมดในสตริงที่กำหนดเป็นตัวเลข (0-9) หรือไม่ โดยจะคืนค่า TRUE หากทุกตัวอักษรเป็นตัวเลข และ FALSE หากมีตัวอักษรใดที่ไม่ใช่ตัวเลข