<?php
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'เข้าสู่ระบบ - ระบบเบิกสิ่งพิมพ์และของที่ระลึก';
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
include 'includes/header.php';
?>

<main class="auth-shell">
    <!-- กล่องฟอร์มล็อกอินเดิม ใช้คลาส auth-card -->
    <section class="auth-card">
        <div class="auth-brand text-center d-flex flex-column align-items-center mb-4">
            <!-- 1. โลโก้ -->
            <div class="auth-brand-icon mb-3">
                <img src="assets/images/logo.png" alt="OAP Logo" style="width: 80px; height: auto;">
            </div>



            <!-- 3. หัวข้อเข้าสู่ระบบแบบไฮไลต์ (ขึ้นบรรทัดเดี่ยว) -->
            <div class="mb-1">
                <h1 class="auth-title-badge m-0">เข้าสู่ระบบ</h1>
            </div>
            <!-- 2. ป้ายชื่อระบบย่อย (ขึ้นบรรทัดเดี่ยว) -->
            <div class="mb-1">
                <span class="auth-kicker"> <h6>ระบบเบิกสิ่งพิมพ์และของที่ระลึก</h6></span>
            </div>

        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger border-0 rounded-3 mb-4" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success border-0 rounded-3 mb-4" role="alert"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form action="actions/auth_login.php" method="POST" class="auth-form">
            <div class="mb-3">
                <label class="form-label small fw-bold text-dark" for="username">ชื่อผู้ใช้งาน</label>
                <div class="input-group input-group-auth shadow-none">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input id="username" type="text" name="username" class="form-control" placeholder="กรอกชื่อผู้ใช้งาน" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold text-dark" for="password">รหัสผ่าน</label>
                <div class="input-group input-group-auth shadow-none">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input id="password" type="password" name="password" class="form-control" placeholder="กรอกรหัสผ่าน" required>
                </div>
            </div>
            <button type="submit" class="btn btn-warning rounded-pill w-100 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                <span>เข้าสู่ระบบ</span> <i class="bi bi-arrow-right"></i>
            </button>
        </form>

        <div class="auth-divider"><span>หรือ</span></div>
        <a class="btn btn-light rounded-pill border w-100 py-2 fw-semibold text-secondary d-flex align-items-center justify-content-center gap-2" href="register.php">
            <i class="bi bi-person-plus text-warning fs-5"></i> <span>ลงทะเบียนผู้ใช้งานใหม่</span>
        </a>
    </section>
</main>

<?php include 'includes/footer.php'; ?>

<style>
    /* 1. ตั้งค่าภาพพื้นหลังทั้งหน้าจอ */
    body {
        background: url('assets/images/bg-login.jpg') no-repeat center center fixed !important;
        background-size: cover !important;
        min-height: 100vh;
    }

    /* 2. จัดพื้นที่ครอบกลางจอ */
    .auth-shell {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }

    /* 3. ตกแต่งการ์ดล็อกอินเดิม (.auth-card) ให้เป็นกระจกโปร่งแสง */
    .auth-card {
        background-color: rgba(255, 255, 255, 0.92) !important;
        /* ขาวโปร่งแสง */
        backdrop-filter: blur(10px);
        /* เอฟเฟกต์เบลอพื้นหลังด้านหลังการ์ด */
        -webkit-backdrop-filter: blur(10px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25) !important;
        border-radius: 20px !important;
        border: 1px solid rgba(255, 255, 255, 0.6) !important;
        max-width: 420px;
        width: 100%;
    }
</style>



<style>
    /* บังคับให้ป้ายชื่อระบบย่อยอยู่ตรงกลาง */
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

    /* ไฮไลต์หัวข้อเข้าสู่ระบบ */
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
</style>