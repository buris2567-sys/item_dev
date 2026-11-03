<?php
if (!defined('APP_RUNNING')) exit('Forbidden');

$stmtUser = $pdo->prepare('
    SELECT u.*, d.department_name, sd.sub_department_name, et.type_name
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.department_id
    LEFT JOIN sub_departments sd ON u.sub_department_id = sd.sub_department_id
    LEFT JOIN employment_types et ON u.employment_type_id = et.employment_type_id
    WHERE u.user_id = ?
');
$stmtUser->execute([$_SESSION['user_id']]);
$user = $stmtUser->fetch();
$success = $_SESSION['profile_success'] ?? '';
unset($_SESSION['profile_success']);
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2" style="border-bottom: 2px solid #e9ecef;">
    <div>
        <h3 class="fw-bolder mb-1 text-dark">หน้าหลัก</h3>
        <span class="text-muted small">ระบบเบิกสิ่งพิมพ์และของที่ระลึก</span>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<section class="card border-0 rounded-4 shadow-sm mb-4 border-primary border-top border-4 bg-white overflow-hidden">
    <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3 gap-md-4">
            <div class="profile-avatar"><i class="bi bi-person"></i></div>
            <div>
                <h3 class="fw-bolder text-dark mb-1"><?= htmlspecialchars($user['full_name'] ?? 'ไม่ทราบชื่อ') ?></h3>
                <span class="badge bg-dark rounded-pill px-3 py-1 shadow-sm"><?= htmlspecialchars($user['role'] ?? 'User') ?></span>
                <div class="profile-summary mt-2">
                    <span><i class="bi bi-envelope"></i><?= htmlspecialchars($user['email'] ?? '-') ?></span>
                    <span><i class="bi bi-briefcase"></i><?= htmlspecialchars($user['position'] ?? '-') ?></span>
                    <span><i class="bi bi-building"></i><?= htmlspecialchars($user['department_name'] ?? '-') ?></span>
                    <span><i class="bi bi-diagram-3"></i><?= htmlspecialchars($user['sub_department_name'] ?? '-') ?></span>
                </div>
            </div>
        </div>
        <a href="index.php?page=profile" class="btn btn-outline-dark fw-bold rounded-pill px-4 py-2 shadow-sm">
            <i class="bi bi-pencil-square me-1"></i> แก้ไขข้อมูลส่วนตัว
        </a>
    </div>
</section>

<section class="card border-0 shadow-sm rounded-4 bg-white p-4 p-xl-5 mb-5 border-warning border-top border-4">
    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-grid-3x3-gap-fill fs-5 text-warning me-2"></i>
        <h5 class="fw-bold mb-0 text-dark">เมนูการใช้งานระบบ</h5>
    </div>
    <div class="row g-4 justify-content-start">
        <div class="col-sm-6 col-lg-5">
            <a href="index.php?page=create_request" class="text-decoration-none">
                <div class="card border border-light rounded-4 h-100 text-center p-5 hover-shadow transition-all bg-white shadow-sm">
                    <div class="d-flex justify-content-center mb-3"><div class="bg-warning bg-opacity-25 rounded-circle d-flex justify-content-center align-items-center" style="width: 80px; height: 80px;"><i class="bi bi-cart-plus fs-1 text-warning"></i></div></div>
                    <h5 class="fw-bolder text-dark mb-2">สร้างคำขอเบิกสิ่งของ</h5>
                    <p class="text-muted small mb-0">ทำรายการเบิกพัสดุเข้าคลังย่อยของแผนก</p>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-5">
            <a href="index.php?page=my_requests" class="text-decoration-none">
                <div class="card border border-light rounded-4 h-100 text-center p-5 hover-shadow transition-all bg-white shadow-sm">
                    <div class="d-flex justify-content-center mb-3"><div class="bg-success bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 80px; height: 80px;"><i class="bi bi-printer fs-1 text-success"></i></div></div>
                    <h5 class="fw-bolder text-dark mb-2">ติดตามสถานะคำขอ</h5>
                    <p class="text-muted small mb-0">ตรวจสอบประวัติคำร้องและพิมพ์แบบฟอร์ม</p>
                </div>
            </a>
        </div>
    </div>
</section>

<style>
.profile-avatar { width: 80px; height: 80px; display: grid; place-items: center; border: 1px solid #dce1e6; border-radius: 1.25rem; background: #f8f9fa; color: #6c757d; font-size: 2.15rem; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); }
.profile-summary { display: flex; flex-wrap: wrap; gap: .45rem 1rem; color: #5f6770; font-size: .83rem; font-weight: 500; }
.profile-summary span { white-space: nowrap; }
.profile-summary i { margin-right: .35rem; color: #6c757d; }
.hover-shadow { transition: transform .2s ease-in-out, box-shadow .2s ease-in-out; }
.hover-shadow:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important; }
</style>
