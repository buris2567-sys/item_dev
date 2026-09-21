<?php
if (!defined('APP_RUNNING')) exit('Forbidden');

// ดึงข้อมูลผู้ใช้งาน
$stmt = $pdo->prepare("SELECT u.*, d.department_name FROM users u LEFT JOIN departments d ON u.department_id = d.department_id WHERE u.user_id = :user_id LIMIT 1");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$currentUser = $stmt->fetch();

$pageTitle = "หน้าหลัก - ฝ่ายผู้ใช้งาน";
include 'includes/header.php';
?>

<div class="container-fluid p-0">
    <div class="row g-0 flex-nowrap">
        
        <!-- ดึง Sidebar สีเข้มมาใช้ -->
        <?php include 'includes/sidebar_user.php'; ?>

        <div class="col d-flex flex-column" style="min-height: 100vh; background-color: #f5f6f8;">
            
            <!-- แถบ Header สีเหลืองด้านบน -->
            <div class="bg-warning px-4 py-3 d-flex justify-content-between align-items-center">
                <h4 class="mb-0 fw-bold text-dark">หน้าหลัก</h4>
                <a href="actions/auth_logout.php" class="text-dark fw-bold text-decoration-none">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </a>
            </div>

            <!-- แถบ Sub-header -->
            <div class="bg-light px-4 py-2 border-bottom text-muted small">
                ระบบจัดการและเบิกจ่ายพัสดุ
            </div>

            <!-- พื้นที่เนื้อหาหลัก -->
            <div class="p-4 flex-grow-1">
                
                <!-- การ์ดข้อมูลส่วนตัว (Profile Card) -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center p-4">
                        <div class="d-flex align-items-center mb-3 mb-md-0">
                            <div class="border rounded-3 p-3 me-4 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background-color: #f8f9fa;">
                                <i class="bi bi-person text-secondary" style="font-size: 2.5rem;"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-1 text-dark"><?= mb_strtoupper(htmlspecialchars($currentUser['full_name'])) ?></h3>
                                <div class="mb-2">
                                    <span class="badge bg-dark text-white px-2 py-1">
                                        <?= htmlspecialchars($currentUser['role'] ?? 'User') ?>
                                    </span>
                                </div>
                                <p class="mb-0 text-muted small fw-bold">
                                    <i class="bi bi-building me-1"></i> Department: <?= htmlspecialchars($currentUser['department_name'] ?? 'ไม่ระบุ') ?>
                                </p>
                            </div>
                        </div>
                        <div>
                            <button class="btn btn-outline-dark fw-bold rounded-2 px-4 py-2">แก้ไขข้อมูลส่วนตัว</button>
                        </div>
                    </div>
                </div>

                <!-- การ์ดเมนูการทำงาน (Action Cards) -->
                <div class="row g-4">
                    
                    <!-- การ์ด 1: สร้างคำขอเบิกสิ่งของ (สีเหลือง) -->
                    <div class="col-md-6">
                        <a href="index.php?page=create_request" class="text-decoration-none">
                            <div class="card border-0 shadow-sm rounded-3 h-100 p-4" style="background-color: #ffc107;">
                                <div class="bg-white rounded-2 d-inline-flex justify-content-center align-items-center mb-4" style="width: 50px; height: 50px;">
                                    <i class="bi bi-cart-plus fs-4 text-dark"></i>
                                </div>
                                <h4 class="fw-bold text-dark mb-2">สร้างคำขอเบิกสิ่งของ</h4>
                                <span class="text-primary fw-bold text-decoration-none small">ทำรายการเบิกพัสดุเข้าคลังย่อย</span>
                            </div>
                        </a>
                    </div>

                    <!-- การ์ด 2: ติดตามสถานะคำขอ (สีขาว) -->
                    <div class="col-md-6">
                        <a href="index.php?page=my_requests" class="text-decoration-none">
                            <div class="card border-0 shadow-sm rounded-3 h-100 p-4 bg-white d-flex flex-column">
                                <div class="bg-dark rounded-2 d-inline-flex justify-content-center align-items-center mb-4" style="width: 50px; height: 50px;">
                                    <i class="bi bi-printer fs-4 text-white"></i>
                                </div>
                                <h4 class="fw-bold text-dark mb-4">ติดตามสถานะคำขอ</h4>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <span class="text-muted small">+ พิมพ์แบบฟอร์ม</span>
                                    <i class="bi bi-arrow-right fs-4 text-dark"></i>
                                </div>
                            </div>
                        </a>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>