<?php
require_once 'config/db.php';

// ถ้าล็อกอินอยู่แล้ว ให้เด้งไปหน้าหลัก
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$pageTitle = "เข้าสู่ระบบ - ระบบสิ่งพิมพ์และของที่ระลึก";
include 'includes/header.php';

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']); // ลบแจ้งเตือนหลังแสดงผล
?>

<div class="d-flex justify-content-center align-items-center vh-100" style="background-color: var(--bg-main);">
    <div class="data-card p-5" style="max-width: 440px; width: 100%;">
        
        <div class="text-center mb-4">
            <h3 class="fw-bold mb-1 text-dark">ระบบเบิกสิ่งพิมพ์<br>และของที่ระลึก</h3>
            <small class="text-muted">Department Portal</small>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 fs-6 mb-3" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="actions/auth_login.php" method="POST">
            <div class="mb-3">
                <label class="form-label text-muted small mb-0 fw-bold">ชื่อผู้ใช้งาน</label>
                <div class="d-flex align-items-center">
                    <i class="bi bi-person text-muted me-2"></i>
                    <input type="text" name="username" class="form-control input-material" placeholder="กรอกชื่อผู้ใช้" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label text-muted small mb-0 fw-bold">รหัสผ่าน</label>
                <div class="d-flex align-items-center">
                    <i class="bi bi-lock text-muted me-2"></i>
                    <input type="password" name="password" class="form-control input-material" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-yellow w-100 py-2 fs-6 mb-3">เข้าสู่ระบบ</button>