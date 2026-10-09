<?php
session_start();
define('APP_RUNNING', true); // สร้างตัวแปรป้องกันการเข้าไฟล์ตรงๆ
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
    
}

// ถ้าไม่มี  $_GET['page'] 
$page = $_GET['page'] ?? 'home';
$role = $_SESSION['role'] ?? 'User';

// ตารางแจกจ่ายเส้นทาง (Front Controller Pattern)
$routes = [];
if ($role === 'Admin') {
    $routes = [
        'home'           => ['file' => 'views/admin/home.php', 'title' => 'หน้าหลักผู้ดูแลระบบ'],
        'manage_items'   => ['file' => 'views/admin/manage_items.php', 'title' => 'จัดการสิ่งของ'],
        'request_list'   => ['file' => 'views/admin/request_list.php', 'title' => 'รายการคำขอเบิกสิ่งของ'],
        'request_view'   => ['file' => 'views/admin/request_view.php', 'title' => 'รายละเอียดคำขอเบิกสิ่งของ']
    ];
} else {
    $routes = [
        'home'           => ['file' => 'views/user/home.php', 'title' => 'หน้าหลัก'],
        'create_request' => ['file' => 'views/user/create_request.php', 'title' => 'สร้างคำขอเบิกสิ่งของ'],
        'my_requests'    => ['file' => 'views/user/my_requests.php', 'title' => 'ติดตามสถานะคำขอ'],
        'request_view'   => ['file' => 'views/user/request_view.php', 'title' => 'รายละเอียดคำขอเบิกสิ่งของ']
    ];
}

// ตรวจสอบว่ามีหน้าที่ระบุในระบบหรือไม่
if (array_key_exists($page, $routes)) {
    $current_file = $routes[$page]['file'];
    $pageTitle = $routes[$page]['title'];
    
    // 🟢 1. โหลด Header (พวกแท็ก <head>, CSS)
    include 'includes/header.php';
    ?>

    <!-- 🟢 2. โครงสร้าง Layout หลัก (Master Layout) คลุมทั้งระบบ -->
    <div class="container-fluid p-0" style="background-color: #f4f6f8; min-height: 100vh;">
        <div class="row g-0 flex-nowrap">
            
            <!-- 🟢 3. ดึง Sidebar มาแสดงทางซ้าย อัตโนมัติตามสิทธิ์ (Role) -->
            <?php 
            if ($role === 'Admin') {
   
                include 'includes/sidebar_admin.php';
            } else {
                include 'includes/sidebar_user.php';
            }
            ?>

            <!-- 🟢 4. พื้นที่ Content ตรงกลางที่จะเปลี่ยนเนื้อหาไปตาม URL -->
            <div class="col p-4 flex-grow-1 d-flex flex-column transition-all" style="font-family: 'Prompt', sans-serif; min-width: 0;">
                
                <?php 
                // 🟢 5. แทรกไฟล์เนื้อหา (เช่น home.php หรือ my_requests.php) มาลงตรงนี้!
                include $current_file; 
                ?>

            </div>

        </div>
    </div>

    <?php
    // 🟢 6. โหลด Footer (ปิดแท็ก </body> และโหลด JS)
    include 'includes/footer.php';

} else {
    // กรณีพิมพ์ URL ผิด
    http_response_code(404);
    echo "<div style='text-align: center; margin-top: 50px; font-family: Prompt, sans-serif;'>";
    echo "<h1>404 Not Found</h1>";
    echo "<p>ไม่พบหน้าที่คุณต้องการ</p>";
    echo "<a href='index.php' class='btn btn-dark mt-3'>กลับหน้าหลัก</a>";
    echo "</div>";
}
?>