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



$error = $_SESSION['profile_error'] ?? '';
$old = $_SESSION['profile_old'] ?? [];
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

<?php if ($error): ?>
    <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4" role="alert"><?= htmlspecialchars($error) ?></div>
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
            <div class="mb-3">
                <label class="form-label" for="new_password">รหัสผ่านใหม่ (เว้นว่างหากไม่ต้องการเปลี่ยน)</label>
                <input class="form-control" id="new_password" type="password" name="new_password" minlength="8" placeholder="เว้นว่างหากไม่ต้องการเปลี่ยน">
            </div>
            <div>
                <label class="form-label" for="confirm_password">ยืนยันรหัสผ่านใหม่</label>
                <input class="form-control" id="confirm_password" type="password" name="confirm_password" minlength="8" placeholder="กรอกรหัสผ่านใหม่อีกครั้ง">
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="form-section h-100 bg-white shadow-sm">
            <h2><i class="bi bi-briefcase"></i> ตำแหน่งและหน่วยงาน</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="employment_type_id">ประเภทบุคลากร <span class="text-danger">*</span></label>
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
    <div class="col-12 d-flex justify-content-end gap-2 pt-2">
        <a href="index.php?page=home" class="btn btn-light border px-4 py-2">ยกเลิก</a>
        <button class="btn btn-dark px-4 py-2" type="submit">บันทึกข้อมูล <i class="bi bi-check-lg ms-1"></i></button>
    </div>
</form>