<?php
session_start();
define('APP_RUNNING', true); // สร้างตัวแปรป้องกันการเข้าไฟล์ตรงๆ

// [Refactored] Load central config.php before db.php so all constants are available app-wide
require_once 'config.php';
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$page = $_GET['page'] ?? 'home';
$role = $_SESSION['role'] ?? 'User';

// ตารางแจกจ่ายเส้นทาง
// $routes = [];
// if ($role === 'Admin') {
//     $routes = [
//         'home'         => ['file' => 'views/admin/home.php', 'title' => 'หน้าหลักผู้ดูแลระบบ'],
//         'manage_items' => ['file' => 'views/admin/manage_items.php', 'title' => 'จัดการสิ่งของ']
//     ];
// } else {
//     $routes = [
//         'home'         => ['file' => 'views/user/home.php', 'title' => 'หน้าหลัก']
//     ];
// }
// ตารางแจกจ่ายเส้นทาง
// Front Controller Pattern
$routes = [];
if ($role === 'Admin') {
    $routes = [
        'home'         => ['file' => 'views/admin/home.php', 'title' => 'หน้าหลักผู้ดูแลระบบ'],
        'manage_items' => ['file' => 'views/admin/manage_items.php', 'title' => 'จัดการสิ่งของ'],
        'request_list'   => ['file' => 'views/admin/request_list.php', 'title' => 'รายการคำขอเบิกสิ่งของ'],
        'request_view'   => ['file' => 'views/admin/request_view.php', 'title' => 'รายละเอียดคำขอเบิกสิ่งของ']
        
        ];
} else {
    $routes = [
        'home'           => ['file' => 'views/user/home.php', 'title' => 'หน้าหลัก'],
        // 🟢 เพิ่มบรรทัดด้านล่างนี้ เพื่อให้ระบบอนุญาตและรู้จักหน้าสร้างคำขอ
        'create_request' => ['file' => 'views/user/create_request.php', 'title' => 'สร้างคำขอเบิกสิ่งของ'],
        'my_requests'    => ['file' => 'views/user/my_requests.php', 'title' => 'ติดตามสถานะคำขอ'],
        'request_view'   => ['file' => 'views/user/request_view.php', 'title' => 'รายละเอียดคำขอเบิกสิ่งของ']

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

<!-- route แบบ  php -->
<!-- $page = $_GET['page'] ?? 'home';
$role = $_SESSION['role'] ?? 'User';

// 🟢 ระบบ Routing แยกหน้าตาม Role
if ($page === 'request_view') {
    if ($role === 'Admin') {
        include 'views/admin/request_view.php';
    } else {
        include 'views/user/request_view.php';
    }
} 
elseif ($role === 'Admin') {
    switch ($page) {
        case 'request_list': include 'views/admin/request_list.php'; break;
        case 'manage_items': include 'views/admin/manage_items.php'; break;
        default: include 'views/admin/home.php'; break;
    }
} 
else {
    switch ($page) {
        case 'my_requests': include 'views/user/my_requests.php'; break;
        case 'create_request': include 'views/user/create_request.php'; break;
        default: include 'views/user/home.php'; break;
    }
} -->