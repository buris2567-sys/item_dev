<?php
// เชื่อมต่อฐานข้อมูล
require_once 'config/db.php'; 

// นับจำนวนสถานะต่างๆ
$countAll = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$countActive = $pdo->query("SELECT COUNT(*) FROM categories WHERE is_active = 1")->fetchColumn();
$countInactive = $pdo->query("SELECT COUNT(*) FROM categories WHERE is_active = 0")->fetchColumn();

// ดึงข้อมูลประเภทสิ่งของทั้งหมด
$stmt = $pdo->query("SELECT * FROM categories ORDER BY category_id DESC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid p-4 bg-light" style="min-height: 100vh;">
    <!-- ส่วนหัวและปุ่มเพิ่ม -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0 text-dark">
            <i class="bi bi-tags me-2 text-warning"></i>ประเภทสิ่งของทั้งหมด
        </h4>
        <button class="btn btn-warning fw-bold px-4 rounded-pill shadow-sm text-dark" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-plus-lg me-1"></i> เพิ่ม
        </button>
    </div>

    <!-- กล่อง Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="bg-warning" style="height: 4px;"></div>
        
        <!-- แถบตัวกรองและช่องค้นหา -->
        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex gap-2">
                <button class="btn btn-dark fw-bold px-3 py-1 rounded-pill">
                    ทั้งหมด <span class="badge bg-warning text-dark ms-1 rounded-pill"><?= $countAll ?></span>
                </button>
                <button class="btn btn-light border text-dark fw-bold px-3 py-1 rounded-pill">
                    เปิดใช้งาน <span class="badge bg-success bg-opacity-25 text-success ms-1 rounded-pill"><?= $countActive ?></span>
                </button>
                <button class="btn btn-light border text-dark fw-bold px-3 py-1 rounded-pill">
                    ปิดใช้งาน <span class="badge bg-secondary bg-opacity-25 text-secondary ms-1 rounded-pill"><?= $countInactive ?></span>
                </button>
            </div>

            <div class="input-group" style="max-width: 280px;">
                <span class="input-group-text bg-white border-end-0 rounded-start-pill text-muted">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="searchInput" class="form-control border-start-0 rounded-end-pill ps-0" placeholder="ค้นหาชื่อประเภท...">
            </div>
        </div>

        <!-- ตารางข้อมูล -->
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0 text-center border-white">
                <thead class="bg-light text-muted small fw-normal">
                    <tr>
                        <th class="py-3 fw-semibold border-0">ลำดับ</th>
                        <th class="py-3 text-start fw-semibold border-0">ชื่อประเภท</th>
                        <th class="py-3 fw-semibold border-0">วันที่สร้าง</th>
                        <th class="py-3 fw-semibold border-0">สถานะ</th>
                        <th class="py-3 fw-semibold border-0">การจัดการ</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    
                    <?php if (count($categories) > 0): ?>
                        <?php $i = 1; foreach ($categories as $cat): ?>
                        <!-- สลับสีพื้นหลังแถวคู่/คี่ให้อ่านง่าย -->
                        <tr class="<?= $i % 2 == 0 ? 'bg-light bg-opacity-50' : '' ?>">
                            <td class="text-muted fw-semibold py-3"><?= $i++ ?></td>
                            <td class="text-start fw-bold text-dark py-3"><?= htmlspecialchars($cat['name']) ?></td>
                            
                            <!-- แปลงรูปแบบวันที่ -->
                            <td class="text-muted small py-3">
                                <?= date('d M Y', strtotime($cat['created_at'])) ?>
                            </td>
                            
                            <!-- ป้ายสถานะ -->
                            <td class="py-3">
                                <?php if ($cat['is_active'] == 1): ?>
                                    <span class="badge bg-success text-white px-3 py-2 rounded-pill shadow-sm">
                                        <i class="bi bi-circle-fill small me-1" style="font-size: 0.4rem;"></i> เปิดใช้งาน
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary text-white px-3 py-2 rounded-pill shadow-sm">
                                        <i class="bi bi-circle-fill small me-1" style="font-size: 0.4rem;"></i> ปิดใช้งาน
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- ปุ่มสวิตช์และแก้ไข -->
                            <td class="py-3">
                                <div class="d-flex justify-content-center align-items-center gap-2">
                                    <div class="form-check form-switch m-0">
                                        <!-- ฝัง data-id เพื่อส่งไปให้ JavaScript -->
                                        <input class="form-check-input bg-warning border-warning toggle-category-btn" 
                                               type="checkbox" role="switch" 
                                               data-id="<?= $cat['category_id'] ?>" 
                                               <?= $cat['is_active'] == 1 ? 'checked' : '' ?> 
                                               style="width: 35px; height: 18px; cursor: pointer;">
                                    </div>
                                    <button class="btn btn-sm btn-light text-primary border rounded-circle shadow-sm" style="width: 32px; height: 32px;">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-muted py-4">ยังไม่มีข้อมูลประเภทสิ่งของ</td>
                        </tr>
                    <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- สคริปต์ JavaScript จัดการสวิตช์ เปิด/ปิด -->
<script>
    document.querySelectorAll('.toggle-category-btn').forEach(btn => {
        btn.addEventListener('change', function() {
            let catId = this.getAttribute('data-id');
            let status = this.checked ? 1 : 0;
            let currentBtn = this;
            
            // ล็อกปุ่มไว้ชั่วคราวกันกดซ้ำ
            currentBtn.disabled = true;

            // ส่งข้อมูลไปที่ไฟล์หลังบ้าน
            fetch('/item_dev/actions/admin/category_toggle_status.php?id=' + catId + '&status=' + status)
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // ถ้าระบบปิด/เปิดสำเร็จ ให้รีเฟรชหน้าเพื่อให้ตัวเลขและป้ายเปลี่ยน
                        window.location.reload();
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + data.message);
                        currentBtn.checked = !currentBtn.checked; // ดีดสวิตช์กลับ
                        currentBtn.disabled = false;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                    currentBtn.checked = !currentBtn.checked; // ดีดสวิตช์กลับ
                    currentBtn.disabled = false;
                });
        });
    });
</script>