<?php
require 'includes/mailer.php';

$to = 'buris007@hotmail.com';
$subject = 'ทดสอบระบบแจ้งเตือนจาก IT Helpdesk';
$body = '
    <h3>สวัสดีครับ</h3>
    <p>นี่คืออีเมลทดสอบการทำงานของระบบส่งอีเมลด้วย PHPMailer</p>
    <p>ระบบทำงานได้สมบูรณ์ครับ!</p>
';

if (sendEmailSystem($to, $subject, $body)) {
    echo "ส่งอีเมลสำเร็จ!";
} else {
    echo "เกิดข้อผิดพลาดในการส่งอีเมล";
}
?>