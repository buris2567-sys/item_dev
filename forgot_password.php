<?php
session_start();
require_once 'config/db.php';
require_once 'includes/mailer.php';

$message = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $status = 'error';
        $message = 'กรุณากรอกอีเมลของท่าน';
    } else {
        $stmt = $pdo->prepare("SELECT user_id, full_name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $new_password = substr(bin2hex(random_bytes(4)), 0, 8); 
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $updateStmt->execute([$hashed_password, $user['user_id']]);

            $subject = "แจ้งรหัสผ่านใหม่สำหรับระบบเบิกสิ่งพิมพ์และของที่ระลึก";
            $body = "
                <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                    <p>ใช้รหัสผ่านต่อไปนี้ในการล็อกอิน: <b style='color: #b45309; font-size: 18px;'>{$new_password}</b></p>
                    <p>เมื่อล็อกอินแล้ว กรุณาเปลี่ยนเป็นรหัสผ่านที่คุณต้องการ ได้จากเมนู <b>เปลี่ยนรหัสผ่าน</b></p>
                </div>
            ";

            if (sendEmailSystem($email, $subject, $body)) {
                $status = 'success';
                $message = 'ระบบได้ส่งรหัสผ่านใหม่ไปยังอีเมลของท่านเรียบร้อยแล้ว';
            } else {
                $status = 'error';
                $message = 'เกิดข้อผิดพลาดในการส่งอีเมล กรุณาติดต่อผู้ดูแลระบบ';
            }
        } else {
            $status = 'error';
            $message = 'ไม่พบข้อมูลอีเมลนี้ในระบบ';
        }
    }
}

$pageTitle = 'ลืมรหัสผ่าน - ระบบเบิกสิ่งพิมพ์และของที่ระลึก';
include 'includes/header.php'; // นำเข้า Header เพื่อโหลด Bootstrap และ CSS หลัก
?>

<main class="auth-shell">
    <section class="auth-card p-4">
        
        <!-- ส่วนหัว (โลโก้ และชื่อหน้า) -->
        <div class="auth-brand text-center d-flex flex-column align-items-center mb-4">
            <div class="auth-brand-icon mb-3">
                <img src="assets/images/logo.png" alt="OAP Logo" style="width: 80px; height: auto;">
            </div>
            <div class="mb-1">
                <h1 class="auth-title-badge m-0">ลืมรหัสผ่าน</h1>
            </div>
            <div class="mb-1">
                <span class="auth-kicker"><h6>ระบบเบิกสิ่งพิมพ์และของที่ระลึก</h6></span>
            </div>
        </div>

        <!-- แจ้งเตือนสถานะ -->
        <?php if ($message): ?>
            <div class="alert alert-<?= $status === 'success' ? 'success' : 'danger' ?> border-0 rounded-3 mb-4 d-flex align-items-center" role="alert">
                <i class="bi <?= $status === 'success' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger' ?> me-2 fs-5"></i>
                <div><?= htmlspecialchars($message) ?></div>
            </div>
        <?php endif; ?>

        <!-- ฟอร์มกรอกอีเมล -->
        <form method="POST" action="" class="auth-form">
            <div class="mb-4">
                <label class="form-label small fw-bold text-dark" for="email">อีเมลที่ลงทะเบียนไว้</label>
                <div class="input-group input-group-auth shadow-none">
                    <span class="input-group-text bg-white"><i class="bi bi-envelope text-muted"></i></span>
                    <input id="email" type="email" name="email" class="form-control" placeholder="example@email.com" required autofocus>
                </div>
            </div>
            
            <button type="submit" class="btn btn-warning rounded-pill w-100 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                <span>รีเซ็ตรหัสผ่าน</span> <i class="bi bi-send"></i>
            </button>
        </form>

        <!-- เส้นคั่นและปุ่มกลับหน้าเข้าสู่ระบบ -->
        <div class="auth-divider"><span>หรือ</span></div>
        <a class="btn btn-light rounded-pill border w-100 py-2 fw-semibold text-secondary d-flex align-items-center justify-content-center gap-2" href="login.php">
            <i class="bi bi-arrow-left"></i> <span>กลับไปยังหน้าเข้าสู่ระบบ</span>
        </a>

    </section>
</main>

<?php include 'includes/footer.php'; ?>

<!-- สไตล์เฉพาะหน้านี้ (ใช้แบบเดียวกับหน้า Login) -->
<style>
    body {
        background: url('assets/images/bg-login.jpg') no-repeat center center fixed !important;
        background-size: cover !important;
        min-height: 100vh;
    }
    .auth-shell {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }
    .auth-card {
        background-color: rgba(255, 255, 255, 0.92) !important;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25) !important;
        border-radius: 20px !important;
        border: 1px solid rgba(255, 255, 255, 0.6) !important;
        max-width: 420px;
        width: 100%;
    }
    .auth-kicker {
        display: inline-block;
        padding: 0.25rem 0.8rem;
        border-radius: 999px;
        background-color: #fef3c7;
        color: #b45309;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .auth-title-badge {
        display: inline-block;
        padding: 0.35rem 1.4rem;
        border-radius: 999px;
        font-size: 1.4rem;
        font-weight: 700;
        line-height: 1.3;
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.2), rgba(245, 158, 11, 0.28));
        color: #92400e;
        border: 1px solid rgba(245, 158, 11, 0.35);
        box-shadow: 0 4px 10px rgba(217, 119, 6, 0.1);
    }
    .auth-divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 1.5rem 0;
        color: #adb5bd;
    }
    .auth-divider::before, .auth-divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #dee2e6;
    }
    .auth-divider span {
        padding: 0 10px;
        font-size: 0.875rem;
    }
</style>