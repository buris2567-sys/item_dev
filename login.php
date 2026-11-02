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
    <section class="auth-card">
        <div class="auth-brand">
            <div class="auth-brand-icon"><i class="bi bi-box-seam-fill"></i></div>
            <div>
                <p class="auth-kicker mb-1">OAP INVENTORY SYSTEM</p>
                <h1>เข้าสู่ระบบ</h1>
                <p class="mb-0 text-secondary">ระบบเบิกสิ่งพิมพ์และของที่ระลึก</p>
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
                <label class="form-label" for="username">ชื่อผู้ใช้งาน</label>
                <div class="input-group input-group-auth">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input id="username" type="text" name="username" class="form-control" placeholder="กรอกชื่อผู้ใช้งาน" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label" for="password">รหัสผ่าน</label>
                <div class="input-group input-group-auth">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input id="password" type="password" name="password" class="form-control" placeholder="กรอกรหัสผ่าน" required>
                </div>
            </div>
            <button type="submit" class="btn btn-dark w-100 py-2 fw-semibold">
                เข้าสู่ระบบ <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </form>

        <div class="auth-divider"><span>หรือ</span></div>
        <a class="btn btn-outline-dark w-100 py-2 fw-semibold" href="register.php">
            <i class="bi bi-person-plus me-1"></i> ลงทะเบียนผู้ใช้งานใหม่
        </a>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
