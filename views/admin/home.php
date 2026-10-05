<?php
// 🟢 [คงเดิม] ป้องกันการเข้าถึงไฟล์โดยตรง และดึงข้อมูลผู้ใช้งาน
if (!defined('APP_RUNNING')) exit('Forbidden');

$stmt = $pdo->prepare("SELECT u.*, d.department_name FROM users u LEFT JOIN departments d ON u.department_id = d.department_id WHERE u.user_id = :user_id LIMIT 1");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$currentUser = $stmt->fetch();
?>

<!-- 🟢 [ปรับปรุง] หัวข้อหน้า (Theme ใหม่ ตัดแถบเหลืองเต็มจอออก) -->
<div class="d-flex justify-content-between align-items-center mb-4 pb-2" style="border-bottom: 2px solid #e9ecef;">
    <div>
        <h3 class="fw-bolder mb-1 text-dark "><i class="bi bi-house text-warning me-2"></i>หน้าหลักผู้ดูแลระบบ</h3> 
          <!-- <h3 class="fw-bolder mb-1 text-dark"><i class="bi bi-boxes text-warning me-2"></i>จัดการสิ่งของ</h3> -->
        <span class="text-muted small">ระบบสิ่งพิมพ์และของที่ระลึก</span>
    </div>
</div>

<!-- 🟢 [ปรับปรุง] Profile Card ปรับเป็นขอบมน (rounded-4) และซ่อนขอบดำ -->
<!-- <div class="card border-0 shadow-sm mb-4 rounded-4 bg-white"> -->
<div class="card border-0 rounded-4 shadow-sm mb-5 border-primary border-top border-4 bg-white">
    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center p-4">
        <div class="d-flex align-items-center mb-3 mb-md-0 gap-4">
            <div class="border rounded-4 d-flex justify-content-center align-items-center" style="width: 80px; height: 80px; background-color: #f8f9fa;">
                <i class="bi bi-person fs-1 text-secondary"></i>
            </div>
            <div>
                <h3 class="fw-bolder mb-1 text-dark"><?= mb_strtoupper(htmlspecialchars($currentUser['full_name'] ?? 'BURIS S.')) ?></h3>
                <div class="mb-2">
                    <span class="badge bg-dark fw-bold px-3 py-1 rounded-pill shadow-sm">Admin</span>
                </div>
                <p class="mb-0 text-muted small fw-bold">
                    <i class="bi bi-building me-1"></i> Department: <?= htmlspecialchars($currentUser['department_name'] ?? 'กองเทคโนโลยี') ?>
                </p>
            </div>
        </div>
        <div>
            <button class="btn btn-outline-dark fw-bold px-4 py-2 rounded-pill shadow-sm">แก้ไขข้อมูลส่วนตัว</button>
        </div>
    </div>
</div>

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
                    <div class="d-flex justify-content-center mb-3">
                        <div class="bg-warning bg-opacity-25 rounded-circle d-flex justify-content-center align-items-center" style="width: 70px; height: 70px;">
                            <i class="bi bi-ui-checks fs-2 text-warning"></i>
                        </div>
                    </div>
                    <h6 class="fw-bolder text-dark mb-2">อนุมัติคำขอ</h6>
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

        <!-- เมนู 4 -->
        <div class="col-sm-6 col-lg-4">
            <a href="#" class="text-decoration-none">
                <div class="card border border-light rounded-4 h-100 text-center p-4 hover-shadow transition-all bg-white shadow-sm">
                    <div class="d-flex justify-content-center mb-3">
                        <div class="bg-info bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 70px; height: 70px;">
                            <i class="bi bi-pencil-square fs-2 text-info"></i>
                        </div>
                    </div>
                    <h6 class="fw-bolder text-dark mb-2">แก้ไขข้อมูลพัสดุ</h6>
                    <p class="text-muted small mb-0" style="font-size: 0.85rem;">ปรับปรุงรายละเอียดสิ่งของ</p>
                </div>
            </a>
        </div>

        <!-- เมนู 5 -->
        <div class="col-sm-6 col-lg-4">
            <a href="#" class="text-decoration-none">
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
            <a href="#" class="text-decoration-none">
                <div class="card border border-light rounded-4 h-100 text-center p-4 hover-shadow transition-all position-relative bg-white shadow-sm">
                    <span class="badge bg-success position-absolute top-0 end-0 mt-3 me-3 rounded-pill px-3 shadow-sm">รายงาน</span>
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