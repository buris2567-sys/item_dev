<?php
if (!defined('APP_RUNNING')) exit('Forbidden');

$stmt = $pdo->prepare("SELECT u.*, d.department_name FROM users u LEFT JOIN departments d ON u.department_id = d.department_id WHERE u.user_id = :user_id LIMIT 1");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$currentUser = $stmt->fetch();

$pageTitle = "หน้าหลักผู้ดูแลระบบ";
include 'includes/header.php';
?>

<div class="container-fluid p-0">
    <div class="row g-0 flex-nowrap">
        <?php include 'includes/sidebar_admin.php'; ?>

        <div class="col d-flex flex-column" style="min-height: 100vh; background-color: #f4f3ef;">
            
            <!-- Topbar Logout Bar -->
            <div class="p-3 text-end bg-white border-bottom border-dark" style="border-bottom-width: 2px !important;">
                <a href="actions/auth_logout.php" class="text-dark fw-bold text-decoration-none small">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </a>
            </div>

            <div class="p-4 flex-grow-1">
                
                <!-- Profile Banner Card -->
                <div class="card border-dark rounded-0 mb-4 shadow-sm" style="border-width: 2px !important; background-color: #fbf0d9;">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="border border-dark bg-white d-flex align-items-center justify-content-center me-4" style="width: 90px; height: 90px; border-width: 2px !important;">
                            <i class="bi bi-person fs-1"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h2 class="fw-bold mb-1 text-dark"><?= mb_strtoupper(htmlspecialchars($currentUser['full_name'] ?? 'BURIS S.')) ?></h2>
                            <span class="badge bg-dark text-white rounded-0 px-2 py-1 mb-2 fw-normal small">
                                <i class="bi bi-shield-lock me-1"></i> เจ้าหน้าที่คลัง / ADMIN
                            </span>
                            <div class="border-bottom border-dark my-2" style="max-width: 350px;"></div>
                            <p class="mb-0 text-dark small fw-bold">
                                <i class="bi bi-building me-1"></i> Department: <?= htmlspecialchars($currentUser['department_name'] ?? 'กองบริหารพัสดุกลาง') ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Section Title -->
                <h5 class="fw-bold mb-3 d-flex align-items-center">
                    <div class="bg-warning border border-dark me-2" style="width: 12px; height: 24px; border-width: 2px !important;"></div>
                    Administrative Actions
                </h5>
                
                <!-- 6 Main Action Cards -->
                <div class="row g-4">
                    
                    <!-- Card 1: พิจารณาอนุมัติการลงทะเบียน -->
                    <div class="col-md-4">
                        <a href="index.php?page=approve_reg" class="text-decoration-none">
                            <div class="card border-dark rounded-0 shadow-sm h-100 p-4 bg-white" style="border-width: 2px !important;">
                                <div class="bg-warning border border-dark d-inline-flex justify-content-center align-items-center mb-3 position-relative" style="width: 48px; height: 48px; border-width: 2px !important;">
                                    <i class="bi bi-person-lines-fill text-dark fs-4"></i>
                                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">พิจารณาอนุมัติการลงทะเบียน</h6>
                                <hr class="border-secondary my-2 border-dashed">
                                <p class="text-muted small mb-0">Review and approve pending material requests.</p>
                            </div>
                        </a>
                    </div>

                    <!-- Card 2: พิจารณาอนุมัติคำขอ -->
                    <div class="col-md-4">
                        <a href="index.php?page=approve_req" class="text-decoration-none">
                            <div class="card border-dark rounded-0 shadow-sm h-100 p-4 bg-white" style="border-width: 2px !important;">
                                <div class="bg-warning border border-dark d-inline-flex justify-content-center align-items-center mb-3 position-relative" style="width: 48px; height: 48px; border-width: 2px !important;">
                                    <i class="bi bi-card-checklist text-dark fs-4"></i>
                                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">พิจารณาอนุมัติคำขอ</h6>
                                <hr class="border-secondary my-2 border-dashed">
                                <p class="text-muted small mb-0">Review and approve pending material requests.</p>
                            </div>
                        </a>
                    </div>

                    <!-- Card 3: จัดการประเภทและเพิ่มสิ่งของ -->
                    <div class="col-md-4">
                        <a href="index.php?page=manage_items" class="text-decoration-none">
                            <div class="card border-dark rounded-0 shadow-sm h-100 p-4 bg-white" style="border-width: 2px !important;">
                                <div class="bg-light border border-dark d-inline-flex justify-content-center align-items-center mb-3" style="width: 48px; height: 48px; border-width: 2px !important;">
                                    <i class="bi bi-boxes text-dark fs-4"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">จัดการประเภทและเพิ่มสิ่งของ</h6>
                                <hr class="border-secondary my-2 border-dashed">
                                <p class="text-muted small mb-0">Manage categories and add new inventory items.</p>
                            </div>
                        </a>
                    </div>

                    <!-- Card 4: จัดการแก้ไขข้อมูลสิ่งของ -->
                    <div class="col-md-4">
                        <a href="index.php?page=edit_items" class="text-decoration-none">
                            <div class="card border-dark rounded-0 shadow-sm h-100 p-4 bg-white" style="border-width: 2px !important;">
                                <div class="bg-light border border-dark d-inline-flex justify-content-center align-items-center mb-3" style="width: 48px; height: 48px; border-width: 2px !important;">
                                    <i class="bi bi-pencil-square text-dark fs-4"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">จัดการแก้ไขข้อมูลสิ่งของ</h6>
                                <hr class="border-secondary my-2 border-dashed">
                                <p class="text-muted small mb-0">Edit existing item details and specifications.</p>
                            </div>
                        </a>
                    </div>

                    <!-- Card 5: ดูรายการสิ่งของทั้งหมด -->
                    <div class="col-md-4">
                        <a href="index.php?page=view_all_items" class="text-decoration-none">
                            <div class="card border-dark rounded-0 shadow-sm h-100 p-4 bg-white" style="border-width: 2px !important;">
                                <div class="bg-light border border-dark d-inline-flex justify-content-center align-items-center mb-3" style="width: 48px; height: 48px; border-width: 2px !important;">
                                    <i class="bi bi-archive text-dark fs-4"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">ดูรายการสิ่งของทั้งหมด</h6>
                                <hr class="border-secondary my-2 border-dashed">
                                <p class="text-muted small mb-0">View full inventory catalog and current stock levels.</p>
                            </div>
                        </a>
                    </div>

                    <!-- Card 6: สรุปรายงานเบิกจ่าย -->
                    <div class="col-md-4">
                        <a href="index.php?page=reports" class="text-decoration-none">
                            <div class="card border-dark rounded-0 shadow-sm h-100 p-4 bg-white position-relative" style="border-width: 2px !important;">
                                <div class="position-absolute top-0 end-0 p-3">
                                    <span class="badge border border-dark text-dark rounded-0 bg-white me-1">PDF</span>
                                    <span class="badge border border-dark text-dark rounded-0 bg-white">EXCEL</span>
                                </div>
                                <div class="bg-light border border-dark d-inline-flex justify-content-center align-items-center mb-3" style="width: 48px; height: 48px; border-width: 2px !important;">
                                    <i class="bi bi-file-earmark-text text-dark fs-4"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">สรุปรายงานเบิกจ่าย</h6>
                                <hr class="border-secondary my-2 border-dashed">
                                <p class="text-muted small mb-0">Generate disbursement summary reports for auditing.</p>
                            </div>
                        </a>
                    </div>

                </div>

            </div>
            
            <!-- Footer -->
            <div class="p-3 bg-white border-top border-dark d-flex justify-content-between align-items-center small text-muted" style="border-top-width: 2px !important;">
                <span>© 2024 Administrative Management System. All rights reserved.</span>
                <div>
                    <a href="#" class="text-muted text-decoration-underline me-3">Privacy Policy</a>
                    <a href="#" class="text-muted text-decoration-underline me-3">Terms of Service</a>
                    <a href="#" class="text-muted text-decoration-underline">User Manual</a>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>