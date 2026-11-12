<?php
// กำหนด Path ให้ตรงกับโฟลเดอร์ที่คุณเอา PHPMailer ไปวางไว้
require_once __DIR__ . '/../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * ฟังก์ชันสำหรับส่งอีเมล
 * @param string $toEmail อีเมลผู้รับ
 * @param string $subject หัวข้ออีเมล
 * @param string $htmlBody เนื้อหาอีเมล (รองรับ HTML)
 * @return bool ส่งสำเร็จหรือไม่
 */
function sendEmailSystem($toEmail, $subject, $htmlBody) {
    $mail = new PHPMailer(true);

    try {
        // 1. ตั้งค่าเซิร์ฟเวอร์ SMTP (ตัวอย่างใช้ Gmail แต่ถ้ามีของหน่วยงานให้เปลี่ยนตรงนี้)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';         // เปลี่ยนเป็นเซิร์ฟเวอร์อีเมลของ OAP หากมี
        $mail->SMTPAuth   = true;                     // เปิดการยืนยันตัวตน
        $mail->Username   = 'your_email@gmail.com';   // **ใส่อีเมลของคุณ**
        $mail->Password   = 'your_app_password';      // **ใส่รหัสผ่าน (หากใช้ Gmail ต้องใช้ App Password 16 หลัก)**
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port       = 587;                      // พอร์ต SMTP
        $mail->CharSet    = 'UTF-8';                  // ตั้งค่าให้รองรับภาษาไทย

        // 2. ตั้งค่าผู้ส่งและผู้รับ
        $mail->setFrom('your_email@gmail.com', 'IT Helpdesk System'); // ชื่อผู้ส่ง
        $mail->addAddress($toEmail);

        // 3. เนื้อหาอีเมล
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        
        // ข้อความกรณีผู้รับใช้โปรแกรมอีเมลที่ไม่รองรับ HTML
        $mail->AltBody = strip_tags($htmlBody);

        $mail->send();
        return true;
    } catch (Exception $e) {
        // หากต้องการดู error ให้ uncomment บรรทัดล่าง
        // error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>