<?php
// 🟢 [คงเดิม] ป้องกันการเข้าถึงไฟล์โดยตรง และดึงข้อมูลผู้ใช้งาน
if (!defined('APP_RUNNING')) exit('Forbidden');

$stmt = $pdo->prepare("SELECT u.*, d.department_name, sd.sub_department_name FROM users u LEFT JOIN departments d ON u.department_id = d.department_id LEFT JOIN sub_departments sd ON u.sub_department_id = sd.sub_department_id WHERE u.user_id = :user_id LIMIT 1");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$currentUser = $stmt->fetch();
?>

<!-- ส่วนหัวของหน้า -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h3 class="fw-bolder mb-1 text-dark">
            <i class="bi bi-house-door text-warning me-2"></i>หน้าหลักผู้ดูแลระบบ
        </h3>
        <span class="text-muted small">ระบบสิ่งพิมพ์และของที่ระลึก</span>
    </div>

    <!-- 🟢 โค้ดกระดิ่งแจ้งเตือน เอามาวางตรงนี้ครับ -->
    
</div>

<!-- 🟢 [ปรับปรุง] Profile Card ปรับเป็นขอบมน (rounded-4) และซ่อนขอบดำ -->
<!-- <div class="card border-0 shadow-sm mb-4 rounded-4 bg-white"> -->
<div class="card border-0 shadow-sm rounded-4 mb-4" style="border-top: 4px solid #0d6efd !important;">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">

            <!-- ส่วนรูปและข้อมูลบุคคล -->
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 bg-light d-flex align-items-center justify-content-center text-secondary border flex-shrink-0" style="width: 72px; height: 72px;">
                    <i class="bi bi-person fs-1"></i>
                </div>

                <div>
                    <!-- ชื่อ + Badge สิทธิ์ -->
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <h4 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($currentUser['full_name']) ?></h4>
                        <span class="badge bg-dark rounded-pill px-2.5 py-1 fw-semibold small"><?= htmlspecialchars($currentUser['role']) ?></span>
                    </div>

                    <!-- แบ่งเป็น 2 คอลัมน์กว้างเท่ากัน 50/50 ตรงกันทุกบรรทัด -->
                    <div class="row g-2 text-muted small mt-2" style="max-width: 620px;">
                        <!-- บรรทัดที่ 1: ฝั่งซ้าย (กอง) -->
                        <div class="col-6 d-flex align-items-center">
                            <i class="bi bi-building text-primary me-2 flex-shrink-0"></i>
                            <span><strong class="text-secondary">กอง:</strong> <?= htmlspecialchars($currentUser['department_name'] ?? '-') ?></span>
                        </div>

                        <!-- บรรทัดที่ 1: ฝั่งขวา (กลุ่ม) -->
                        <div class="col-6 d-flex align-items-center">
                            <i class="bi bi-diagram-3 text-info me-2 flex-shrink-0"></i>
                            <span><strong class="text-secondary">กลุ่ม:</strong> <?= htmlspecialchars($currentUser['sub_department_name'] ?? '-') ?></span>
                        </div>

                        <!-- บรรทัดที่ 2: ฝั่งซ้าย (ตำแหน่ง) -->
                        <div class="col-6 d-flex align-items-center">
                            <i class="bi bi-briefcase text-secondary me-2 flex-shrink-0"></i>
                            <span><strong class="text-secondary">ตำแหน่ง:</strong> <?= htmlspecialchars($currentUser['position'] ?? '-') ?></span>
                        </div>

                        <!-- บรรทัดที่ 2: ฝั่งขวา (อีเมล) -->
                        <div class="col-6 d-flex align-items-center">
                            <i class="bi bi-envelope text-secondary me-2 flex-shrink-0"></i>
                            <span><?= htmlspecialchars($currentUser['email'] ?? '-') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ปุ่มแก้ไขด้านขวา -->
            <div class="ms-md-auto align-self-md-center flex-shrink-0">
                <a href="index.php?page=profile" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 btn-sm fw-medium d-flex align-items-center gap-1.5 shadow-sm">
                    <i class="bi bi-pencil-square"></i>
                    <span>แก้ไขข้อมูลส่วนตัว</span>
                </a>
            </div>

        </div>
    </div>
</div>

<style>
    .profile-summary {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem 1rem;
        color: #5f6770;
        font-size: .83rem;
        font-weight: 500;
    }

    .profile-summary span {
        white-space: nowrap;
    }

    .profile-summary i {
        margin-right: .35rem;
        color: #6c757d;
    }
</style>

<!-- 🟢 [ปรับปรุง] กล่องครอบเมนูหลัก (Wrapper Container)  -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-xl-5 mb-5 border-warning border-top border-4">
    <!-- <div class="card border-0 rounded-4 shadow-sm mb-5 border-warning border-top border-4 bg-white"> -->
    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-grid-3x3-gap-fill fs-5 text-primary me-2"></i>
        <h5 class="fw-bold mb-0 text-dark">เมนูการจัดการระบบ</h5>
    </div>

    <!-- Grid เมนู: จัดความสมดุล 3 กล่องต่อ 1 แถว บนจอใหญ่ (col-lg-4) -->
    <div class="row g-4">

        <!-- เมนู 1 -->
        <div class="col-sm-6 col-lg-4">
            <a href="#" class="text-decoration-none">
                <div class="card border border-light rounded-4 h-100 text-center p-4 hover-shadow transition-all bg-white shadow-sm">
                   <!-- ป้ายตัวเลขแจ้งเตือน -->
                <span id="card-noti-badge" shadow-sm d-none class="badge bg-danger position-absolute top-0 end-0 mt-3 me-3 rounded-pill px-1     ">0</span>

                    <div class="d-flex justify-content-center mb-3">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 70px; height: 70px;">
                            <i class="bi bi-person-lines-fill fs-2 text-primary"></i>
                        </div>
                    </div>
                    <h6 class="fw-bolder text-dark mb-2">อนุมัติลงทะเบียน</h6>
                    <p class="text-muted small mb-0" style="font-size: 0.85rem;">ตรวจสอบและอนุมัติผู้ใช้งานใหม่</p>
                </div>
            </a>
        </div>

        <!-- เมนู 2 -->
        <div class="col-sm-6 col-lg-4">
            <a href="index.php?page=request_list" class="text-decoration-none">
                <div class="card border border-light rounded-4 h-100 text-center p-4 hover-shadow transition-all bg-white shadow-sm">
                    <!-- ป้ายตัวเลขแจ้งเตือน -->
                    <span id="card-noti-badge" shadow-sm d-none class="badge bg-danger position-absolute top-0 end-0 mt-3 me-3 rounded-pill px-1     ">0</span>


                    <!-- <span id="card-noti-badge" class="badge bg-danger shadow-sm d-none" style="font-size: 0.75rem;">0</span> -->
                    <div class="d-flex justify-content-center mb-3">
                        <div class="bg-warning bg-opacity-25 rounded-circle d-flex justify-content-center align-items-center" style="width: 70px; height: 70px;">
                            <i class="bi bi-ui-checks fs-2 text-warning"></i>
                        </div>
                    </div>
                    <h6 class="fw-bolder text-dark mb-2">อนุมัติคำขอ </h6>
                    <p class="text-muted small mb-0" style="font-size: 0.85rem;">พิจารณาอนุมัติใบเบิกพัสดุ</p>
                </div>
            </a>
        </div>

        <!-- เมนู 3 -->
        <div class="col-sm-6 col-lg-4">
            <a href="index.php?page=manage_items" class="text-decoration-none">
                <div class="card border border-light rounded-4 h-100 text-center p-4 hover-shadow transition-all bg-white shadow-sm">
                    <div class="d-flex justify-content-center mb-3">
                        <div class="bg-success bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 70px; height: 70px;">
                            <i class="bi bi-boxes fs-2 text-success"></i>
                        </div>
                    </div>
                    <h6 class="fw-bolder text-dark mb-2">เพิ่มสิ่งของ</h6>
                    <p class="text-muted small mb-0" style="font-size: 0.85rem;">จัดการหมวดหมู่และตั้งต้นสต็อก</p>
                </div>
            </a>
        </div>


        <!-- เมนู 5 -->
        <div class="col-sm-6 col-lg-4">
            <a href="index.php?page=manage_categories" class="text-decoration-none">
                <div class="card border border-light rounded-4 h-100 text-center p-4 hover-shadow transition-all bg-white shadow-sm">
                    <div class="d-flex justify-content-center mb-3">
                        <div class="bg-secondary bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 70px; height: 70px;">
                            <i class="bi bi-archive fs-2 text-secondary"></i>
                        </div>
                    </div>
                    <h6 class="fw-bolder text-dark mb-2">แคตตาล็อกพัสดุ</h6>
                    <p class="text-muted small mb-0" style="font-size: 0.85rem;">ดูรายการสิ่งของทั้งหมดในระบบ</p>
                </div>
            </a>
        </div>

        <!-- เมนู 6 -->
        <div class="col-sm-6 col-lg-4">
            <a href="index.php?page=inventory_report" class="text-decoration-none">
                <div class="card border border-light rounded-4 h-100 text-center p-4 hover-shadow transition-all position-relative bg-white shadow-sm">
                    <div class="d-flex justify-content-center mb-3">
                        <div class="bg-danger bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 70px; height: 70px;">
                            <i class="bi bi-file-earmark-text fs-2 text-danger"></i>
                        </div>
                    </div>
                    <h6 class="fw-bolder text-dark mb-2">สรุปรายงาน</h6>
                    <p class="text-muted small mb-0" style="font-size: 0.85rem;">ออกรายงานเบิกจ่าย PDF/EXCEL</p>
                </div>
            </a>
        </div>

        <!-- เมนู 4 -->
        <div class="col-sm-6 col-lg-4">
            <a href="#" class="text-decoration-none">
                <div class="card border border-light rounded-4 h-100 text-center p-4 hover-shadow transition-all bg-white shadow-sm">
                    <div class="d-flex justify-content-center mb-3">
                        <div class="bg-info bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 70px; height: 70px;">
                            <i class="bi bi-pencil-square fs-2 text-info"></i>
                        </div>
                    </div>
                    <h6 class="fw-bolder text-dark mb-2">coming soon</h6>
                    <!-- <p class="text-muted small mb-0" style="font-size: 0.85rem;"></p> -->
                </div>
            </a>
        </div>
    </div>
</div>

<!-- 🟢 [คงเดิม] CSS Hover Effect -->
<style>
    .hover-shadow {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
    }
</style>

<script src="assets/js/admin_notification.js"></script>