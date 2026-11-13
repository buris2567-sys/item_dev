<?php
require_once 'includes/mailer.php';

// 🔴 เปลี่ยนเป็นอีเมลส่วนตัวของคุณเอง เพื่อรอรับอีเมลทดสอบ
$to = 'buris2567@gmail.com'; 
$subject = 'ทดสอบระบบแจ้งเตือนจาก IT Helpdesk';
$body = '
    <h3>สวัสดีครับ</h3>
    <p>นี่คืออีเมลทดสอบการทำงานของระบบส่งอีเมลด้วย PHPMailer</p>
    <p>ระบบทำงานได้สมบูรณ์ครับ!</p>
';

if (sendEmailSystem($to, $subject, $body)) {
    echo "ส่งอีเมลสำเร็จ! ตรวจสอบที่กล่องจดหมายของคุณได้เลย";
} else {
    echo "เกิดข้อผิดพลาดในการส่งอีเมล (ลองเปิด echo ในไฟล์ mailer.php เพื่อดู Error เพิ่มเติม)";
}
?>