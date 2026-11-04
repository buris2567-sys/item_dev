<?php
if (!defined('APP_RUNNING')) exit('Forbidden');

// ตรวจสอบสิทธิ์
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    $_SESSION['error'] = 'เฉพาะผู้ดูแลระบบเท่านั้น';
    header("Location: index.php");
    exit;
}

// รับค่า ID ที่ส่งมาจาก URL
$target_user_id = $_GET['id'] ?? null;
if (!$target_user_id) {
    header("Location: index.php?page=request_register");
    exit;
}


// ดึงข้อมูลผู้ใช้งานที่ต้องการดูรายละเอียด
$stmt = $pdo->prepare('
    SELECT u.*, d.department_name, sd.sub_department_name, et.type_name
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.department_id
    LEFT JOIN sub_departments sd ON u.sub_department_id = sd.sub_department_id
    LEFT JOIN employment_types et ON u.employment_type_id = et.employment_type_id
    WHERE u.user_id = ?
');
$stmt->execute([$target_user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "<div class='alert alert-danger m-4'>ไม่พบข้อมูลผู้ใช้งานนี้</div>";
    exit;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2" style="border-bottom: 2px solid #e9ecef;">
    <div>
        <h3 class="fw-bolder mb-1 text-dark">รายละเอียดคำร้องลงทะเบียน</h3>
        <span class="text-muted small">ตรวจสอบข้อมูลเพื่อพิจารณาอนุมัติสิทธิ์การใช้งาน</span>
    </div>
    <a href="index.php?page=request_register" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i> กลับหน้ารายการ</a>
</div>

<div class="row g-4">
    <!-- ส่วนข้อมูลส่วนตัว (ฝั่งซ้าย) -->
    <div class="col-lg-5">
        <div class="form-section h-100 bg-white shadow-sm p-4 rounded-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-person-vcard text-primary me-2"></i> ข้อมูลส่วนตัวและบัญชี</h5>
            
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold">ชื่อ-นามสกุล</label>
                <input class="form-control bg-light fw-semibold text-dark" value="<?= htmlspecialchars($user['full_name'] ?? '-') ?>" readonly>
            </div>
            
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold">ชื่อผู้ใช้งาน (Username)</label>
                <input class="form-control bg-light fw-semibold text-dark" value="<?= htmlspecialchars($user['username'] ?? '-') ?>" readonly>
            </div>
            
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold">อีเมล</label>
                <input class="form-control bg-light fw-semibold text-dark" value="<?= htmlspecialchars($user['email'] ?? '-') ?>" readonly>
            </div>
            
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold">เบอร์ติดต่อ</label>
                <input class="form-control bg-light fw-semibold text-dark" value="<?= htmlspecialchars($user['phone_number'] ?? '-') ?>" readonly>
            </div>
            
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold">วันที่สมัคร</label>
                <input class="form-control bg-light fw-semibold text-dark" value="<?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>" readonly>
            </div>
            <!-- ตัดช่องรหัสผ่านออกไปทั้งหมด -->
        </div>
    </div>

    <!-- ส่วนหน่วยงานและตำแหน่ง (ฝั่งขวา) -->
    <div class="col-lg-7">
        <div class="form-section h-100 bg-white shadow-sm p-4 rounded-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-briefcase text-warning me-2"></i> ตำแหน่งและหน่วยงาน</h5>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-bold">ประเภทบุคลากร</label>
                    <input class="form-control bg-light fw-semibold text-dark" value="<?= htmlspecialchars($user['type_name'] ?? '-') ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-bold">ตำแหน่ง</label>
                    <input class="form-control bg-light fw-semibold text-dark" value="<?= htmlspecialchars($user['position'] ?? '-') ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-bold">กอง</label>
                    <input class="form-control bg-light fw-semibold text-dark" value="<?= htmlspecialchars($user['department_name'] ?? '-') ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-bold">กลุ่ม / ฝ่าย</label>
                    <input class="form-control bg-light fw-semibold text-dark" value="<?= htmlspecialchars($user['sub_department_name'] ?? '-') ?>" readonly>
                </div>
                
                <div class="col-12 mt-4">
                    <label class="form-label text-muted small fw-bold">สถานะปัจจุบัน</label>
                    <div>
                        <?php 
                            $statusVal = (int)($user['approve_user'] ?? 0);
                            if ($statusVal === 0) {
                                echo '<span class="badge bg-warning text-dark px-3 py-2 fs-6">รอการพิจารณา</span>';
                            } elseif ($statusVal === 1) {
                                echo '<span class="badge bg-success text-white px-3 py-2 fs-6">อนุมัติแล้ว</span>';
                            } else {
                                echo '<span class="badge bg-danger text-white px-3 py-2 fs-6">ไม่อนุมัติ</span>';
                            }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ส่วนปุ่มจัดการ -->
    <div class="col-12 d-flex justify-content-end gap-2 pt-3">
        <a href="index.php?page=request_register" class="btn btn-light border px-4 py-2 fw-bold shadow-sm">ย้อนกลับ</a>
        
        <?php if ($statusVal === 0): // โชว์ปุ่มเฉพาะคนที่ยังรอพิจารณา ?>
            <form action="actions/admin/request_register_backend.php" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการไม่อนุมัติ?');">
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['user_id']) ?>">
                <input type="hidden" name="action" value="reject">
                <button type="submit" class="btn btn-danger px-4 py-2 fw-bold shadow-sm"><i class="bi bi-x-lg me-1"></i> ไม่อนุมัติ</button>
            </form>
            
            <form action="actions/admin/request_register_backend.php" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการอนุมัติ?');">
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['user_id']) ?>">
                <input type="hidden" name="action" value="approve">
                <button type="submit" class="btn btn-success px-4 py-2 fw-bold shadow-sm"><i class="bi bi-check-lg me-1"></i> อนุมัติ</button>
            </form>
        <?php endif; ?>
    </div>
</div>

