<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ใช้ __DIR__ ป้องกันปัญหา Path หาไฟล์ไม่เจอเมื่อถูกเรียกจากโฟลเดอร์อื่น
require_once __DIR__ . '/../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/SMTP.php';

function sendEmailSystem($toEmail, $subject, $htmlBody) {
    $mail = new PHPMailer(true);

    try {
        // 1. ตั้งค่าเซิร์ฟเวอร์ SMTP (ตัวอย่าง Gmail)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        // 🔴 แก้ไขข้อมูล 2 บรรทัดนี้ เป็นของ "อีเมลระบบ" ที่คุณเตรียมไว้
        $mail->Username   = 'itemoap@gmail.com'; 
        $mail->Password   = 'rsgbytkwlhvtgkah';  // ใช้ App Password 16 หลัก
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // 2. ตั้งค่าผู้ส่งและผู้รับ
        // 🔴 แก้ไขอีเมลในวงเล็บแรกให้ตรงกับ $mail->Username ด้านบน
        $mail->setFrom('itemoap@gmail.com', 'IT Helpdesk System'); 
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
        // หากต้องการดู Error ตอนทดสอบ ให้เอาคอมเมนต์บรรทัดล่างออก
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}
?>