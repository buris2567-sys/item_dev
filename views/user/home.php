<?php
// 🟢 [คงเดิม] ป้องกันการเข้าถึงไฟล์โดยตรง และดึงข้อมูลผู้ใช้งาน
if (!defined('APP_RUNNING')) exit('Forbidden');

$stmtUser = $pdo->prepare("
    SELECT u.*, d.department_name 
    FROM users u 
    LEFT JOIN departments d ON u.department_id = d.department_id 
    WHERE u.user_id = ?
");
$stmtUser->execute([$_SESSION['user_id']]);
$user = $stmtUser->fetch();
?>

<!-- 🟢 [ปรับปรุง] หัวข้อหน้า (Theme ใหม่) -->
<div class="d-flex justify-content-between align-items-center mb-4 pb-2" style="border-bottom: 2px solid #e9ecef;">
    <div>
        <h3 class="fw-bolder mb-1 text-dark">หน้าหลัก</h3>
        <span class="text-muted small">ระบบสิ่งพิมพ์และของที่ระลึก</span>
    </div>
</div>

<!-- 🟢 [ปรับปรุง] Profile Card สไตล์เดียวกับ Admin (rounded-4) -->
<!-- <div class="card border-0 shadow-sm mb-4 rounded-4 bg-white"> -->
<div class="card border-0 rounded-4 shadow-sm mb-5 border-primary border-top border-4 bg-white">
    <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-4">
            <div class="border rounded-4 d-flex justify-content-center align-items-center bg-light shadow-sm" style="width: 80px; height: 80px;">
                <i class="bi bi-person fs-1 text-secondary"></i>
            </div>
            <div>
                <h3 class="fw-bolder text-dark mb-1"><?= htmlspecialchars($user['full_name'] ?? 'ไม่ทราบชื่อ') ?></h3>
                <span class="badge bg-dark rounded-pill px-3 py-1 mb-2 shadow-sm">User</span>
                <div class="text-muted small fw-bold"><i class="bi bi-building me-1"></i> Department: <?= htmlspecialchars($user['department_name'] ?? '-') ?></div>
            </div>
        </div>
        <button class="btn btn-outline-dark fw-bold rounded-pill px-4 py-2 shadow-sm">แก้ไขข้อมูลส่วนตัว</button>
    </div>
</div>

<!-- 🟢 [ปรับปรุง] กล่องครอบเมนูหลัก (สไตล์เดียวกับ Admin เลยครับ) -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-xl-5 mb-5 border-warning border-top border-4">

    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-grid-3x3-gap-fill fs-5 text-warning me-2"></i>
        <h5 class="fw-bold mb-0 text-dark">เมนูการใช้งานระบบ</h5>
    </div>

    <!-- Grid เมนู: จัดความสมดุล ใช้ col-lg-6 เพื่อให้กล่องใหญ่ขึ้นและแสดง 2 อันต่อแถว -->
    <div class="row g-4 justify-content-start">

        <!-- เมนู 1: สร้างคำขอเบิกสิ่งของ -->
        <div class="col-sm-6 col-lg-5">
            <a href="index.php?page=create_request" class="text-decoration-none">
                <div class="card border border-light rounded-4 h-100 text-center p-5 hover-shadow transition-all bg-white shadow-sm">
                    <div class="d-flex justify-content-center mb-3">
                        <div class="bg-warning bg-opacity-25 rounded-circle d-flex justify-content-center align-items-center" style="width: 80px; height: 80px;">
                            <i class="bi bi-cart-plus fs-1 text-warning"></i>
                        </div>
                    </div>
                    <h5 class="fw-bolder text-dark mb-2">สร้างคำขอเบิกสิ่งของ</h5>
                    <p class="text-muted small mb-0">ทำรายการเบิกพัสดุเข้าคลังย่อยของแผนก</p>
                </div>
            </a>
        </div>

        <!-- เมนู 2: ติดตามสถานะคำขอ -->
        <div class="col-sm-6 col-lg-5">
            <a href="index.php?page=my_requests" class="text-decoration-none">
                <div class="card border border-light rounded-4 h-100 text-center p-5 hover-shadow transition-all bg-white shadow-sm">
                    <div class="d-flex justify-content-center mb-3">
                        <div class="bg-success bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 80px; height: 80px;">
                            <i class="bi bi-printer fs-1 text-success"></i>
                        </div>
                    </div>
                    <h5 class="fw-bolder text-dark mb-2">ติดตามสถานะคำขอ</h5>
                    <p class="text-muted small mb-0">ตรวจสอบประวัติคำร้องและพิมพ์แบบฟอร์ม</p>
                </div>
            </a>
        </div>

    </div>
</div>

<!-- 🟢 CSS Hover Effect -->
<style>
    .hover-shadow {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
    }
</style>