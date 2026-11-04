<?php
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$departments = $pdo->query('SELECT department_id, department_name FROM departments ORDER BY department_name')->fetchAll();
$subDepartments = $pdo->query('SELECT sub_department_id, department_id, sub_department_name FROM sub_departments ORDER BY sub_department_name')->fetchAll();
$employmentTypes = $pdo->query('SELECT employment_type_id, type_name FROM employment_types ORDER BY type_name')->fetchAll();
$error = $_SESSION['register_error'] ?? '';
$old = $_SESSION['register_old'] ?? [];
unset($_SESSION['register_error'], $_SESSION['register_old']);
if (empty($_SESSION['form_token'])) {
    $_SESSION['form_token'] = bin2hex(random_bytes(32));
}

$pageTitle = 'ลงทะเบียนผู้ใช้งาน - ระบบเบิกสิ่งพิมพ์และของที่ระลึก';
include 'includes/header.php';
?>

<main class="register-shell py-4 py-lg-5">
    <section class="register-card">
        <div class="register-heading d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between pb-4 mb-4 border-bottom">
            <div class="d-flex align-items-center gap-3">
                <div class="register-mark">
                    <i class="bi bi-person-plus-fill"></i>
                </div>
                <div>
                    
                    <h1 class="fw-bold fs-3 text-dark mb-1">ลงทะเบียนผู้ใช้งานระบบ</h1>
                    <p class="mb-0 text-muted small">กรอกข้อมูลเพื่อสร้างบัญชีสำหรับขอเบิกสิ่งพิมพ์และของที่ระลึก</p>
                </div>
            </div>
            <a href="login.php" class="btn btn-light rounded-pill border px-3 py-1 text-secondary small fw-semibold mt-2 mt-sm-0 d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-arrow-left"></i> กลับไปเข้าสู่ระบบ
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger border-0 rounded-3 mb-4 shadow-sm" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="actions/auth_register.php" method="POST" class="row g-4">
            <input type="hidden" name="form_token" value="<?= htmlspecialchars($_SESSION['form_token']) ?>">
            <div class="col-lg-5">
                <div class="form-section h-100">
                    <h2 class="fw-bold fs-6 text-dark mb-3"><i class="bi bi-person-vcard text-warning fs-5"></i> ข้อมูลส่วนตัวและบัญชี</h2>
                    <div class="mb-3">
                        <label class="form-label" for="full_name">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                        <input class="form-control" id="full_name" name="full_name" maxlength="100" value="<?= htmlspecialchars($old['full_name'] ?? '') ?>" placeholder="ระบุชื่อ-นามสกุล" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="register_username">ชื่อผู้ใช้งาน <span class="text-danger">*</span></label>
                        <input class="form-control" id="register_username" name="username" maxlength="50" value="<?= htmlspecialchars($old['username'] ?? '') ?>" placeholder="เช่น name.s" required>
                        <div class="form-text small text-muted">ใช้สำหรับเข้าสู่ระบบ</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="phone_number">เบอร์ติดต่อ <span class="text-danger">*</span></label>
                        <input class="form-control" id="phone_number" name="phone_number" maxlength="20" value="<?= htmlspecialchars($old['phone_number'] ?? '') ?>" placeholder="08X-XXX-XXXX" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="register_password">รหัสผ่าน <span class="text-danger">*</span></label>
                        <input class="form-control" id="register_password" type="password" name="password" minlength="8" placeholder="อย่างน้อย 8 ตัวอักษร" required>
                    </div>
                    <div>
                        <label class="form-label" for="confirm_password">ยืนยันรหัสผ่าน <span class="text-danger">*</span></label>
                        <input class="form-control" id="confirm_password" type="password" name="confirm_password" minlength="8" placeholder="กรอกรหัสผ่านอีกครั้ง" required>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="form-section h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h2 class="fw-bold fs-6 text-dark mb-3"><i class="bi bi-briefcase text-warning fs-5"></i> ตำแหน่งและหน่วยงาน</h2>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="employment_type_id">ประเภทบุคลากร <span class="text-danger">*</span></label>
                                <select class="form-select" id="employment_type_id" name="employment_type_id" required>
                                    <option value="">เลือกประเภทบุคลากร</option>
                                    <?php foreach ($employmentTypes as $type): ?>
                                        <option value="<?= (int) $type['employment_type_id'] ?>" <?= (($old['employment_type_id'] ?? '') == $type['employment_type_id']) ? 'selected' : '' ?>><?= htmlspecialchars($type['type_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="department_id">กอง <span class="text-danger">*</span></label>
                                <select class="form-select" id="department_id" name="department_id" required>
                                    <option value="">เลือกกอง</option>
                                    <?php foreach ($departments as $department): ?>
                                        <option value="<?= (int) $department['department_id'] ?>" <?= (($old['department_id'] ?? '') == $department['department_id']) ? 'selected' : '' ?>><?= htmlspecialchars($department['department_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="sub_department_id">กลุ่ม <span class="text-danger">*</span></label>
                                <select class="form-select" id="sub_department_id" name="sub_department_id" required>
                                    <option value="">เลือกกลุ่ม</option>
                                    <?php foreach ($subDepartments as $subDepartment): ?>
                                        <option value="<?= (int) $subDepartment['sub_department_id'] ?>" data-department="<?= (int) $subDepartment['department_id'] ?>" <?= (($old['sub_department_id'] ?? '') == $subDepartment['sub_department_id']) ? 'selected' : '' ?>><?= htmlspecialchars($subDepartment['sub_department_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="position">ตำแหน่ง <span class="text-danger">*</span></label>
                                <input class="form-control" id="position" name="position" maxlength="100" value="<?= htmlspecialchars($old['position'] ?? '') ?>" placeholder="ระบุชื่อตำแหน่ง" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="email">อีเมล <span class="text-danger">*</span></label>
                                <input class="form-control" id="email" type="email" name="email" maxlength="150" value="<?= htmlspecialchars($old['email'] ?? '') ?>" placeholder="name@oap.go.th" required>
                            </div>
                        </div>
                        <div class="form-note mt-4 d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle-fill text-warning fs-6"></i>
                            <span>กรุณาตรวจสอบกอง กลุ่ม และตำแหน่งให้ถูกต้องก่อนลงทะเบียน เพราะจะแก้ไขข้อมูลไม่ได้ในภายหลัง</span>
                        </div>
                    </div>

                    <div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 pt-4 mt-4 border-top">
                        <a href="login.php" class="btn btn-light rounded-pill border px-4 py-2 text-secondary fw-semibold">ยกเลิก</a>
                        <button class="btn btn-warning rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2" type="submit">
                            <span>ยืนยันการลงทะเบียน</span> <i class="bi bi-check-circle-fill"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </section>
</main>

<script>
(() => {
    const department = document.getElementById('department_id');
    const subgroup = document.getElementById('sub_department_id');
    const options = Array.from(subgroup.options).map(option => option.cloneNode(true));
    function filterGroups(keepValue = true) {
        const selected = keepValue ? subgroup.value : '';
        subgroup.innerHTML = '';
        options.forEach(option => {
            if (!option.value || option.dataset.department === department.value) subgroup.appendChild(option.cloneNode(true));
        });
        subgroup.value = selected;
        if (subgroup.value !== selected) subgroup.value = '';
    }
    department.addEventListener('change', () => filterGroups(false));
    filterGroups(true);
})();
</script>
<?php include 'includes/footer.php'; ?>
