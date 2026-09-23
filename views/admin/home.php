<?php
if (!defined('APP_RUNNING')) exit('Forbidden');

// สมมติการดึงข้อมูล User
$stmt = $pdo->prepare("SELECT u.*, d.department_name FROM users u LEFT JOIN departments d ON u.department_id = d.department_id WHERE u.user_id = :user_id LIMIT 1");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$currentUser = $stmt->fetch();

$pageTitle = "หน้าหลักผู้ดูแลระบบ";
// include 'includes/header.php';
?>

<div class="container-fluid p-0">


    <div class="row g-0 flex-nowrap">

        <!-- ดึง Sidebar สีเข้มมาแสดง -->
        <?php include 'includes/sidebar_admin.php'; ?>

        <!-- พื้นที่เนื้อหาหลัก พื้นหลังสีเทาอ่อน -->
        <div class="col d-flex flex-column" style="min-height: 100vh; background-color: #f5f6f8;">

            <!-- Header แถบสีเหลือง -->
            <div class="bg-warning px-4 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">หน้าหลัก</h5>
                <a href="actions/auth_logout.php" class="text-dark fw-bold text-decoration-none small">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </a>
            </div>

            <div class="bg-light border-bottom px-4 py-2 text-muted small">
                ระบบจัดการและเบิกจ่ายพัสดุ
            </div>

            <div class="p-4 flex-grow-1">

                <!-- Profile Card -->
                <div class="card border-0 shadow-sm mb-4 rounded-3">
                    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center p-4">
                        <div class="d-flex align-items-center mb-3 mb-md-0">
                            <div class="border rounded d-flex justify-content-center align-items-center me-4" style="width: 70px; height: 70px; background-color: #f8f9fa;">
                                <i class="bi bi-person fs-1 text-secondary"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-1 text-dark"><?= mb_strtoupper(htmlspecialchars($currentUser['full_name'] ?? 'BURIS S.')) ?></h3>
                                <div class="mb-2">
                                    <span class="badge bg-dark fw-normal px-2 py-1">Admin</span>
                                </div>
                                <p class="mb-0 text-muted small fw-bold">
                                    <i class="bi bi-building me-1"></i> Department: <?= htmlspecialchars($currentUser['department_name'] ?? 'กองเทคโนโลยี') ?>
                                </p>
                            </div>
                        </div>
                        <div>
                            <button class="btn btn-outline-dark fw-bold px-4 py-2 rounded-2">แก้ไขข้อมูลส่วนตัว</button>
                        </div>
                    </div>
                </div>

                <!-- กล่องครอบเมนูหลัก (Wrapper Container) -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-xl-5">

                    <!-- หัวข้อของกล่องครอบ -->
                    <div class="d-flex align-items-center mb-4">
                        <i class="bi bi-grid-3x3-gap-fill fs-5 text-primary me-2"></i>
                        <h5 class="fw-bold mb-0 text-dark">เมนูการจัดการระบบ</h5>
                    </div>

                    <!-- Grid เมนู (ใช้ col-xl-3 เพื่อให้แสดง 4 อันต่อแถวบนจอใหญ่ หรือ col-lg-4 สำหรับ 3 อัน) -->
                    <div class="row g-4">

                        <!-- เมนู 1 -->
                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <div class="card border rounded-3 h-100 text-center p-4 hover-shadow transition-all">
                                <div class="d-flex justify-content-center mb-3">
                                    <div class="bg-light rounded-circle d-flex justify-content-center align-items-center" style="width: 60px; height: 60px;">
                                        <i class="bi bi-person-lines-fill fs-3 text-primary"></i>
                                    </div>
                                </div>
                                <h6 class="fw-bold text-dark mb-2">อนุมัติลงทะเบียน</h6>
                                <p class="text-muted small mb-4" style="font-size: 0.8rem;">ตรวจสอบและอนุมัติผู้ใช้งานใหม่</p>
                                <!-- <a href="index.php?page=approve_reg" class="btn btn-outline-primary btn-sm w-100 mt-auto rounded-pill fw-bold">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> เข้าสู่ระบบ
                                </a> -->
                            </div>
                        </div>

                        <!-- เมนู 2 -->
                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <div class="card border rounded-3 h-100 text-center p-4 hover-shadow transition-all">
                                <div class="d-flex justify-content-center mb-3">
                                    <div class="bg-light rounded-circle d-flex justify-content-center align-items-center" style="width: 60px; height: 60px;">
                                        <i class="bi bi-ui-checks fs-3 text-warning"></i>
                                    </div>
                                </div>
                                <h6 class="fw-bold text-dark mb-2">อนุมัติคำขอ</h6>
                                <p class="text-muted small mb-4" style="font-size: 0.8rem;">พิจารณาอนุมัติใบเบิกพัสดุ</p>
                                <!-- <a href="index.php?page=approve_req" class="btn btn-outline-primary btn-sm w-100 mt-auto rounded-pill fw-bold">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> เข้าสู่ระบบ
                                </a> -->
                            </div>
                        </div>

                        <!-- เมนู 3 -->
                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <div class="card border rounded-3 h-100 text-center p-4 hover-shadow transition-all">
                                <div class="d-flex justify-content-center mb-3">
                                    <div class="bg-light rounded-circle d-flex justify-content-center align-items-center" style="width: 60px; height: 60px;">
                                        <i class="bi bi-boxes fs-3 text-success"></i>
                                    </div>
                                </div>
                                <h6 class="fw-bold text-dark mb-2">เพิ่มสิ่งของ</h6>
                                <p class="text-muted small mb-4" style="font-size: 0.8rem;">จัดการหมวดหมู่และตั้งต้นสต็อก</p>
                                <!-- <a href="index.php?page=manage_items" class="btn btn-outline-primary btn-sm w-100 mt-auto rounded-pill fw-bold">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> เข้าสู่ระบบ
                                </a> -->
                            </div>
                        </div>

                        <!-- เมนู 4 -->
                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <div class="card border rounded-3 h-100 text-center p-4 hover-shadow transition-all">
                                <div class="d-flex justify-content-center mb-3">
                                    <div class="bg-light rounded-circle d-flex justify-content-center align-items-center" style="width: 60px; height: 60px;">
                                        <i class="bi bi-pencil-square fs-3 text-info"></i>
                                    </div>
                                </div>
                                <h6 class="fw-bold text-dark mb-2">แก้ไขข้อมูลพัสดุ</h6>
                                <p class="text-muted small mb-4" style="font-size: 0.8rem;">ปรับปรุงรายละเอียดสิ่งของ</p>
                                <!-- <a href="index.php?page=edit_items" class="btn btn-outline-primary btn-sm w-100 mt-auto rounded-pill fw-bold">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> เข้าสู่ระบบ
                                </a> -->
                            </div>
                        </div>

                        <!-- เมนู 5 -->
                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <div class="card border rounded-3 h-100 text-center p-4 hover-shadow transition-all">
                                <div class="d-flex justify-content-center mb-3">
                                    <div class="bg-light rounded-circle d-flex justify-content-center align-items-center" style="width: 60px; height: 60px;">
                                        <i class="bi bi-archive fs-3 text-secondary"></i>
                                    </div>
                                </div>
                                <h6 class="fw-bold text-dark mb-2">แคตตาล็อกพัสดุ</h6>
                                <p class="text-muted small mb-4" style="font-size: 0.8rem;">ดูรายการสิ่งของทั้งหมดในระบบ</p>
                                <!-- <a href="index.php?page=view_all_items" class="btn btn-outline-primary btn-sm w-100 mt-auto rounded-pill fw-bold">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> เข้าสู่ระบบ
                                </a> -->
                            </div>
                        </div>

                        <!-- เมนู 6 -->
                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <div class="card border rounded-3 h-100 text-center p-4 hover-shadow transition-all position-relative">
                                <!-- Badge สีเขียวแบบในรูป -->
                                <span class="badge bg-success position-absolute top-0 end-0 mt-3 me-3">รายงาน</span>
                                <div class="d-flex justify-content-center mb-3">
                                    <div class="bg-light rounded-circle d-flex justify-content-center align-items-center" style="width: 60px; height: 60px;">
                                        <i class="bi bi-file-earmark-text fs-3 text-danger"></i>
                                    </div>
                                </div>
                                <h6 class="fw-bold text-dark mb-2">สรุปรายงาน</h6>
                                <p class="text-muted small mb-4" style="font-size: 0.8rem;">ออกรายงานเบิกจ่าย PDF/EXCEL</p>
                                <!-- <a href="index.php?page=reports" class="btn btn-outline-primary btn-sm w-100 mt-auto rounded-pill fw-bold">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> เข้าสู่ระบบ
                                </a> -->
                            </div>
                        </div>

                    </div>
                </div> <!-- สิ้นสุดกล่องครอบ -->

            </div>
        </div>
    </div>
</div>

<!-- เพิ่ม CSS เล็กน้อยสำหรับ Hover Effect ให้สวยงาม -->
<style>
    .hover-shadow {
        transition: box-shadow 0.3s ease-in-out;
    }

    .hover-shadow:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }
</style>

<?php include 'includes/footer.php'; ?>