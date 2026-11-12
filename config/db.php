<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = '10.2.5.56'; // หรือ IP 127.0.0.1 ตามที่ผู้ดูแลระบบแจ้ง (ส่วนใหญ่มักใช้ localhost ได้)
$db   = 'newitem';             // ชื่อฐานข้อมูลบนเซิร์ฟเวอร์
$user = 'Bin$NewItem_2569';    // Username สำหรับเข้าฐานข้อมูลบนเซิร์ฟเวอร์
$pass = 'OAP!2569@Newitem';    // Password สำหรับเข้าฐานข้อมูลบนเซิร์ฟเวอร์
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Database connection failed: " . $e->getMessage());
}
?>

