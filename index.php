<?php
session_start();
define('APP_RUNNING', true); // สร้างตัวแปรป้องกันการเข้าไฟล์ตรงๆ
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$page = $_GET['page'] ?? 'home';
$role = $_SESSION['role'] ?? 'User';

// ตารางแจกจ่ายเส้นทาง
$routes = [];
if ($role === 'Admin') {
    $routes = [
        'home'         => ['file' => 'views/admin/home.php', 'title' => 'หน้าหลักผู้ดูแลระบบ'],
        'manage_items' => ['file' => 'views/admin/manage_items.php', 'title' => 'จัดการสิ่งของ']
    ];
} else {
    $routes = [
        'home'         => ['file' => 'views/user/home.php', 'title' => 'หน้าหลัก']
    ];
}

// โหลดหน้าจอ
if (array_key_exists($page, $routes)) {
    $current_file = $routes[$page]['file'];
    $pageTitle = $routes[$page]['title'];
    
    include 'includes/header.php';
    include $current_file;   // ทำไมออกแบบมาแบบนี้
    include 'includes/footer.php';
} else {
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
}
?>

