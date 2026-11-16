<?php
session_start();
require_once '../../config/db.php';
// 🟢 เพิ่ม require_once สำหรับ mailer.php
require_once __DIR__ . '/../../includes/mailer.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    die("Access Denied");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 🟢 (Security) บังคับ user_id เป็นตัวเลข
    $approve_user_id = intval($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($approve_user_id <= 0) {
        die("เกิดข้อผิดพลาด: รหัสผู้ใช้งานไม่ถูกต้อง");
    }

    try {
        if ($action === 'approve') {
            // 1 = อนุมัติ
            $stmt = $pdo->prepare("UPDATE users SET approve_user = 1 WHERE user_id = ?");
            $stmt->execute([$approve_user_id]);
            $_SESSION['success'] = 'อนุมัติผู้ใช้งานเรียบร้อยแล้ว';
            $is_approved = true;
        } elseif ($action === 'reject') {
            // 2 = ไม่อนุมัติ
            $stmt = $pdo->prepare("UPDATE users SET approve_user = 2 WHERE user_id = ?");
            $stmt->execute([$approve_user_id]);
            $_SESSION['success'] = 'ไม่อนุมัติผู้ใช้งานเรียบร้อยแล้ว';
            $is_approved = false;
        }

        // 🟢 --- เริ่มต้นระบบส่งอีเมลแจ้งเตือนผู้สมัคร ---
        // แก้ไขเป็น full_name
        $stmtUser = $pdo->prepare("SELECT email, full_name FROM users WHERE user_id = ?");
        $stmtUser->execute([$approve_user_id]);
        $userData = $stmtUser->fetch();

        if ($userData && !empty($userData['email'])) {
            $to = $userData['email'];

            if (isset($is_approved) && $is_approved === true) {
                $subject = "ยินดีต้อนรับ! บัญชีของคุณได้รับการอนุมัติแล้ว - IT Helpdesk";
                $body = "
                    <div style='font-family: Arial, sans-serif; color: #333;'>
                        <h3 style='color: #198754;'>สวัสดีคุณ {$userData['full_name']}</h3>
                        <p>บัญชีผู้ใช้งานของคุณได้รับการ <b style='color: #198754;'>อนุมัติ</b> เรียบร้อยแล้ว</p>
                        <p>คุณสามารถคลิกที่ปุ่มด้านล่างเพื่อเข้าสู่ระบบ IT Helpdesk ได้ทันที</p>
                        <p style='margin-top: 20px;'>
                            <a href='https://newitem.oap.go.th/index.php?page=login' 
                               style='padding: 10px 15px; background-color: #0d6efd; color: #fff; text-decoration: none; border-radius: 5px; display: inline-block;'>
                               เข้าสู่ระบบ
                            </a>
                        </p>
                        <br><br><p>ขอบคุณครับ<br>IT Helpdesk System</p>
                    </div>
                ";
            } else {
                $subject = "แจ้งผลการลงทะเบียนบัญชีผู้ใช้ - IT Helpdesk";
                $body = "
                    <div style='font-family: Arial, sans-serif; color: #333;'>
                        <h3 style='color: #dc3545;'>สวัสดีคุณ {$userData['full_name']}</h3>
                        <p>ขออภัย บัญชีผู้ใช้งานของคุณ <b style='color: #dc3545;'>ไม่ได้รับการอนุมัติ</b> ให้เข้าใช้งานระบบในขณะนี้</p>
                        <br><br><p>ขอบคุณครับ<br>IT Helpdesk System</p>
                    </div>
                ";
            }
            // 🟢 1. รับค่าผลลัพธ์การส่งอีเมลมาตรวจสอบ
            $is_sent = sendEmailSystem($to, $subject, $body);

            // 🟢 2. แสดงผลบนหน้าจอให้เห็นชัดๆ
            if ($is_sent) {
                echo "<h1 style='color: green;'>✅ ส่งอีเมลสำเร็จ! ส่งไปที่: {$to}</h1>";
            } else {
                echo "<h1 style='color: red;'>❌ ส่งอีเมลไม่สำเร็จ! ลองเช็ค Error จาก PHPMailer ด้านบนครับ</h1>";
            }
        } else {
            // กรณีดึงข้อมูลมาแล้วไม่เจออีเมล
            echo "<h1 style='color: orange;'>⚠️ ไม่พบข้อมูลอีเมลของผู้ใช้รหัส {$approve_user_id} ในฐานข้อมูล</h1>";
        }
        // 🟢 --- สิ้นสุดระบบส่งอีเมล ---

        echo "<br><a href='../../index.php?page=request_register' style='font-size: 20px;'>คลิกเพื่อกลับไปหน้าหลัก</a>";

        // 🟢 3. ปิดคำสั่ง Redirect เปลี่ยนหน้าชั่วคราว (ใส่ // ข้างหน้า)
        // header("Location: ../../index.php?page=request_register");
        // exit;

    } catch (Exception $e) {
        die("Database Error: " . $e->getMessage());
    }
}
