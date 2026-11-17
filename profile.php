<?php
if (!defined('APP_RUNNING')) exit('Forbidden');

$stmt = $pdo->prepare('
    SELECT u.*, d.department_name, sd.sub_department_name, et.type_name
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.department_id
    LEFT JOIN sub_departments sd ON u.sub_department_id = sd.sub_department_id
    LEFT JOIN employment_types et ON u.employment_type_id = et.employment_type_id
    WHERE u.user_id = ?
');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();



// โค้ดเดิมของคุณ
$error = $_SESSION['profile_error'] ?? '';
$old = $_SESSION['profile_old'] ?? [];

// 🟢 1. เพิ่มบรรทัดนี้เพื่อรับค่าแจ้งเตือนสีเขียว
$success = $_SESSION['profile_success'] ?? ''; 

// 🟢 2. เพิ่ม $_SESSION['profile_success'] ลงใน unset เพื่อเคลียร์ค่าหลังจากแสดงผลแล้ว
unset($_SESSION['profile_error'], $_SESSION['profile_old'], $_SESSION['profile_success']); 

if (empty($_SESSION['profile_form_token'])) {
    $_SESSION['profile_form_token'] = bin2hex(random_bytes(32));
}
unset($_SESSION['profile_error'], $_SESSION['profile_old']);
if (empty($_SESSION['profile_form_token'])) {
    $_SESSION['profile_form_token'] = bin2hex(random_bytes(32));
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2" style="border-bottom: 2px solid #e9ecef;">
    <div>
        <h3 class="fw-bolder mb-1 text-dark">แก้ไขข้อมูลส่วนตัว</h3>
        <span class="text-muted small">ปรับปรุงข้อมูลสำหรับติดต่อและข้อมูลบัญชี</span>
    </div>
    <a href="index.php?page=home" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i> กลับหน้าหลัก</a>
</div>

<!-- แจ้งเตือนสีแดง (กรณีมี Error) ของเดิม -->
<?php if ($error): ?>
    <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 d-flex justify-content-between align-items-center" role="alert">
        <div><i class="bi bi-exclamation-circle-fill me-2"></i> <?= htmlspecialchars($error) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- 🟢 เพิ่มแจ้งเตือนสีเขียว (กรณี Success) ตรงนี้ครับ -->
<?php if ($success): ?>
    <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4 d-flex justify-content-between align-items-center" style="background-color: #d1e7dd; color: #0f5132;" role="alert">
        <div><i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($success) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>



<form action="actions/update_profile.php" method="POST" class="row g-4">
    <input type="hidden" name="form_token" value="<?= htmlspecialchars($_SESSION['profile_form_token']) ?>">
    <div class="col-lg-5">
        <div class="form-section h-100 bg-white shadow-sm">
            <h2><i class="bi bi-person-vcard"></i> ข้อมูลส่วนตัวและบัญชี</h2>
            <div class="mb-3">
                <label class="form-label" for="full_name">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                <input class="form-control" id="full_name" name="full_name" maxlength="100" value="<?= htmlspecialchars($old['full_name'] ?? $user['full_name']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="username">ชื่อผู้ใช้งาน</label>
                <input class="form-control bg-light" id="username" value="<?= htmlspecialchars($user['username']) ?>" readonly>
                <div class="form-text">ชื่อผู้ใช้งานไม่สามารถเปลี่ยนได้</div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="email">อีเมล <span class="text-danger">*</span></label>
                <input class="form-control" id="email" type="email" name="email" maxlength="150" value="<?= htmlspecialchars($old['email'] ?? $user['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="phone_number">เบอร์ติดต่อ <span class="text-danger">*</span></label>
                <input class="form-control" id="phone_number" name="phone_number" maxlength="20" value="<?= htmlspecialchars($old['phone_number'] ?? $user['phone_number']) ?>" required>
            </div>
            <!-- <div class="mb-3">
                <label class="form-label" for="new_password">รหัสผ่านใหม่ (เว้นว่างหากไม่ต้องการเปลี่ยน)</label>
                <input class="form-control" id="new_password" type="password" name="new_password" minlength="8" placeholder="เว้นว่างหากไม่ต้องการเปลี่ยน">
            </div>
            <div>
                <label class="form-label" for="confirm_password">ยืนยันรหัสผ่านใหม่</label>
                <input class="form-control" id="confirm_password" type="password" name="confirm_password" minlength="8" placeholder="กรอกรหัสผ่านใหม่อีกครั้ง">
            </div> -->

            
        </div>
    </div>
    <div class="col-lg-7">
        <div class="form-section h-100 bg-white shadow-sm">
            <h2><i class="bi bi-briefcase"></i> ตำแหน่งและหน่วยงาน</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="employment_type_id">ประเภทบุคลากร </label>
                    <input class="form-control bg-light" id="department" value="<?= htmlspecialchars($user['type_name'] ?? '-') ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="department">กอง</label>
                    <input class="form-control bg-light" id="department" value="<?= htmlspecialchars($user['department_name'] ?? '-') ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="sub_department">กลุ่ม</label>
                    <input class="form-control bg-light" id="sub_department" value="<?= htmlspecialchars($user['sub_department_name'] ?? '-') ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="position">ตำแหน่ง</label>
                    <input class="form-control bg-light" id="position" value="<?= htmlspecialchars($user['position'] ?? '-') ?>" readonly>
                </div>
            </div>
            <div class="form-note mt-4"><i class="bi bi-lock"></i> กอง กลุ่ม และตำแหน่งถูกล็อกไว้ หากต้องการเปลี่ยนแปลง กรุณาติดต่อผู้ดูแลระบบ</div>
       
       
        </div>
    </div>

    
    <!-- <div class="col-12 d-flex justify-content-end gap-2 pt-2">
        <a href="index.php?page=home" class="btn btn-light border px-4 py-2">ยกเลิก</a>
        <button class="btn btn-dark px-4 py-2" type="submit">บันทึกข้อมูล <i class="bi bi-check-lg ms-1"></i></button>
    </div> -->

    <!-- แถบปุ่มด้านล่าง (แทนที่อันเดิม) -->
    <div class="col-12 d-flex justify-content-between align-items-center pt-3 mt-2" style="border-top: 1px solid #e9ecef;">
        <div>
            <!-- ปุ่มเปลี่ยนรหัสผ่านเรียก Modal -->
            <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                <i class="bi bi-key"></i> เปลี่ยนรหัสผ่าน
            </button>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php?page=home" class="btn btn-light border px-4 py-2">ยกเลิก</a>
            <button class="btn btn-dark px-4 py-2" type="submit">บันทึกข้อมูล <i class="bi bi-check-lg ms-1"></i></button>
        </div>
    </div>

</form>


<!-- ================= Modal เปลี่ยนรหัสผ่าน ================= -->
<!-- ================= Modal เปลี่ยนรหัสผ่าน ================= -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="changePasswordModalLabel">
                    <i class="bi bi-shield-lock me-2"></i>เปลี่ยนรหัสผ่าน
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <!-- 🟢 ฟอร์มนี้แหละครับที่ใช้ส่งข้อมูลไปหาไฟล์เปลี่ยนรหัสผ่าน -->
                <form id="changePasswordForm" action="actions/change_password.php" method="POST">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">รหัสผ่านเดิม</label>
                        <input type="password" name="old_password" class="form-control bg-light" required autocomplete="current-password">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">รหัสผ่านใหม่</label>
                        <input type="password" name="new_password" class="form-control bg-light" required minlength="8" autocomplete="new-password">
                        <div class="form-text" style="font-size: 0.8rem;">รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร</div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">ยืนยันรหัสผ่านใหม่</label>
                        <input type="password" name="confirm_password" class="form-control bg-light" required minlength="8" autocomplete="new-password">
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-dark py-2" style="border-radius: 8px;">อัปเดตรหัสผ่าน</button>
                        <button type="button" class="btn btn-light border py-2" data-bs-dismiss="modal" style="border-radius: 8px;">ยกเลิก</button>
                    </div>
                    
                </form>
            </div>

        </div>
    </div>
</div>
<!-- ================= จบ Modal ================= -->
<!-- ================= จบ Modal ================= -->