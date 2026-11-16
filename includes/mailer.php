<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/SMTP.php';

function sendEmailSystem($toEmail, $subject, $htmlBody)
{
    $mail = new PHPMailer(true);

    try {
        // 1. ตั้งค่าเซิร์ฟเวอร์ SMTP ตามโค้ดใหม่
        $mail->isSMTP();
        $mail->SMTPAuth   = true;
        $mail->Host       = 'mailrelay.uc-workd.com';
        $mail->Port       = 465;
        $mail->Username   = 'noreply@oap.go.th';
        $mail->Password   = 'NzlEN0I4QkIRjE1NiRkQ0UIyM0QMDI5QkVFRTREQ0Qx';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->CharSet    = 'UTF-8';
        $mail->Encoding   = 'base64'; // เพิ่มตามโค้ดใหม่
        $mail->SMTPDebug  = 0;        // เปลี่ยนเป็น 2 หากต้องการดู Log ทะลุทะลวง

        // ข้ามการตรวจสอบ SSL
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // 2. ตั้งค่าผู้ส่งและผู้รับ
        $mail->setFrom('noreply@oap.go.th', 'กลุ่มเทคโนโลยีสารสนเทศ กองยุทธศาสตร์และแผนงาน สำนักงานปรมาณูเพื่อสันติ');

        // รับค่า $toEmail มาจากระบบตอนที่มีการเรียกใช้ฟังก์ชัน
        $mail->addAddress($toEmail);

        // 3. เนื้อหาอีเมล
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);

        $mail->send();
        return true;
    } catch (Exception $e) {
        // หากต้องการดู Error ตอนทดสอบ ให้เอาคอมเมนต์บรรทัดล่างออก
        echo "เกิดข้อผิดพลาดในการส่งอีเมล: {$mail->ErrorInfo}";
        return false;
    }
}

// $mail = new PHPMailer(true);

// // Debug สำหรับทดสอบเท่านั้น
// $mail->SMTPAuth   = true;
// $mail->isSMTP();
// $mail->Port       =  465;
// $mail->Host       = "mailrelay.uc-workd.com";
// $mail->Username   = 'noreply@oap.go.th';
// $mail->Password   = 'NzlEN0I4QkIRjE1NiRkQ0UIyM0QMDI5QkVFRTREQ0Qx';
// $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
// $mail->CharSet  = 'UTF-8';
// $mail->Encoding = 'base64';
// $mail->SMTPDebug = 0;
// $mail->SMTPOptions = array(
//     'ssl' => array(
//         'verify_peer' => false,
//         'verify_peer_name' => false,
//         'allow_self_signed' => true
//     )
// );
     // Recipients

// $mail->setFrom(
//     'noreply@oap.go.th',
//     'กลุ่มเทคโนโลยีสารสนเทศ กองยุทธศาสตร์และแผนงาน สำนักงานปรมาณูเพื่อสันติ'
// );

// $mail->addAddress('it@oap.go.th', 'Recipient Name');
// // Content (supports Blade views via view()->render())
// $mail->isHTML(true);
// $mail->Subject = 'แจ้ง Login เข้าสู่ระบบครุภัณฑ์คอมพิวเตอร์เพื่อใช้งาน ';
// $mail->Body = '                                            
//                                             <p> ท่านกำลังเข้าสู่ระบบครุภัณฑ์คอมพิวเตอร์ เมื่อวันที่ : ' . date('Y-m-d H:i:s') . ' IP : ' . $_SERVER['REMOTE_ADDR'] . ' </p>
//                                             <p> ระบบส่งอีเมลอัตโนมัติ โปรดอย่าตอบกลับอีเมลนี้ </p>
//                                         ';
// $mail->AltBody = 'Plain-text fallback body';
// $mail->send();
