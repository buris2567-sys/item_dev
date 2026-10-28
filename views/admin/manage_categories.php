<?php
require_once 'config/db.php';

// ดึงตัวเลขนับสถานะ
$countAll = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$countActive = $pdo->query("SELECT COUNT(*) FROM categories WHERE is_active = 1")->fetchColumn();
$countInactive = $pdo->query("SELECT COUNT(*) FROM categories WHERE is_active = 0")->fetchColumn();

// ดึงข้อมูลประเภทสิ่งของทั้งหมด
$stmt = $pdo->query("SELECT * FROM categories ORDER BY category_id DESC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- เพิ่ม CSS ควบคุมสีสวิตช์ไว้ด้านบน -->
<style>
    /* สีสวิตช์ตอนเปิด (เหลือง) */
    .form-switch .form-check-input:checked {
        background-color: #ffc107 !important;
        border-color: #ffc107 !important;
    }
    /* สีสวิตช์ตอนปิด Bootstrap จะทำเป็นสีเทาให้อัตโนมัติ */
</style>

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
                <!-- ปุ่มทั้งหมด (สถานะกำลังเลือก) -->
                <button type="button" class="btn btn-warning text-dark fw-bold px-3 py-1 rounded-pill filter-btn active" data-status="all">
                    ทั้งหมด <span class="badge text-dark ms-1 rounded-pill fs-6"><?= $countAll ?></span>
                </button>
                <!-- ปุ่มเปิดใช้งาน (สถานะไม่ได้เลือก: พื้นขาว ขอบเทา) -->
                <button type="button" class="btn bg-white border text-secondary fw-bold px-3 py-1 rounded-pill filter-btn" data-status="1">
                    เปิดใช้งาน <span class="badge bg-success bg-opacity-25 text-success ms-1 rounded-pill fs-6"><?= $countActive ?></span>
                </button>
                <!-- ปุ่มปิดใช้งาน (สถานะไม่ได้เลือก: พื้นขาว ขอบเทา) -->
                <button type="button" class="btn bg-white border text-secondary fw-bold px-3 py-1 rounded-pill filter-btn" data-status="0">
                    ปิดใช้งาน <span class="badge bg-danger bg-opacity-25 text-danger ms-1 rounded-pill fs-6"><?= $countInactive ?></span>
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
            <table class="table table-hover align-middle mb-0 text-center border-white" id="categoryTable">
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
                            <tr class="category-row <?= $i % 2 == 0 ? 'bg-light bg-opacity-50' : '' ?>" data-status="<?= $cat['is_active'] ?>">
                                <td class="text-muted fw-semibold py-3 row-number"><?= $i++ ?></td>
                                <td class="text-start fw-bold text-dark py-3 category-name"><?= htmlspecialchars($cat['name']) ?></td>
                                <td class="text-muted small py-3"><?= date('d M Y', strtotime($cat['created_at'])) ?></td>
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
                                <td class="py-3">
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        <div class="form-check form-switch m-0">
                                            <!-- เอาคลาส bg-warning border-warning ออก เพื่อให้สลับสีเทาได้ -->
                                            <input class="form-check-input toggle-category-btn"
                                                type="checkbox" role="switch"
                                                data-id="<?= $cat['category_id'] ?>"
                                                <?= $cat['is_active'] == 1 ? 'checked' : '' ?>
                                                style="width: 35px; height: 18px; cursor: pointer;">
                                        </div>
                                        <button type="button"
                                            class="btn btn-sm btn-light text-primary border rounded-circle shadow-sm edit-category-btn"
                                            data-id="<?= $cat['category_id'] ?>"
                                            data-name="<?= htmlspecialchars($cat['name']) ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editCategoryModal"
                                            style="width: 32px; height: 32px;">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr id="emptyRow">
                            <td colspan="5" class="text-muted py-4">ยังไม่มีข้อมูลประเภทสิ่งของ</td>
                        </tr>
                    <?php endif; ?>
                    <tr id="noMatchRow" style="display: none;">
                        <td colspan="5" class="text-muted py-4">ไม่พบข้อมูลที่ค้นหา</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Components -->
<?php include 'components/modals/add_category_modal.php'; ?>
<?php include 'components/modals/edit_category_modal.php'; ?>

<script>
    // 1. ตรวจสอบชื่อซ้ำ & บันทึกเพิ่มหมวดหมู่
    document.getElementById('saveAddCategoryBtn')?.addEventListener('click', function() {
        const form = document.getElementById('addCategoryForm');
        const nameInput = document.getElementById('addCategoryName');
        const name = nameInput.value.trim();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'กำลังตรวจสอบ...';

        fetch('actions/admin/check_category_name.php?name=' + encodeURIComponent(name))
            .then(res => res.json())
            .then(data => {
                if (data.exists) {
                    alert('❌ ไม่สามารถเพิ่มได้: มีประเภทสิ่งของชื่อ "' + name + '" ในระบบอยู่แล้ว (หรือถูกปิดใช้งานอยู่)');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    nameInput.focus();
                } else {
                    form.submit();
                }
            }).catch(err => {
                alert('เกิดข้อผิดพลาดในการตรวจสอบชื่อซ้ำ');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
    });

    // 2. ดึงข้อมูลใส่ Modal แก้ไข (แก้ปัญหาคลิกโดนไอคอนแล้วข้อมูลไม่มา)
    document.querySelectorAll('.edit-category-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const targetBtn = e.target.closest('.edit-category-btn');
            document.getElementById('editCategoryId').value = targetBtn.getAttribute('data-id');
            document.getElementById('editCategoryName').value = targetBtn.getAttribute('data-name');
        });
    });

    // 3. ตรวจสอบชื่อซ้ำ & บันทึกแก้ไขหมวดหมู่
    document.getElementById('saveEditCategoryBtn')?.addEventListener('click', function() {
        const form = document.getElementById('editCategoryForm');
        const nameInput = document.getElementById('editCategoryName');
        const idInput = document.getElementById('editCategoryId');

        const name = nameInput.value.trim();
        const id = idInput.value;

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'กำลังตรวจสอบ...';

        fetch(`actions/admin/check_category_name.php?name=${encodeURIComponent(name)}&id=${encodeURIComponent(id)}`)
            .then(res => res.json())
            .then(data => {
                if (data.exists) {
                    alert('❌ ไม่สามารถเปลี่ยนชื่อได้: มีประเภทสิ่งของชื่อ "' + name + '" ในระบบอยู่แล้ว');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    nameInput.focus();
                } else {
                    form.submit();
                }
            }).catch(err => {
                alert('เกิดข้อผิดพลาดในการตรวจสอบชื่อซ้ำ');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
    });

    // 4. สวิตช์ Toggle เปิด/ปิด สถานะ
    document.querySelectorAll('.toggle-category-btn').forEach(btn => {
        btn.addEventListener('change', function() {
            const catId = this.getAttribute('data-id');
            const status = this.checked ? 1 : 0;
            const switchElement = this;

            switchElement.disabled = true;

            fetch('actions/admin/category_toggle_status.php?id=' + encodeURIComponent(catId) + '&status=' + encodeURIComponent(status))
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('❌ เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถเปลี่ยนสถานะได้'));
                        switchElement.checked = !switchElement.checked;
                        switchElement.disabled = false;
                    }
                })
                .catch(err => {
                    alert('ไม่สามารถติดต่อเซิร์ฟเวอร์ได้');
                    switchElement.checked = !switchElement.checked;
                    switchElement.disabled = false;
                });
        });
    });

    // 5. ค้นหาแบบเรียลไทม์ & ตัวกรองสถานะ (ปรับสีปุ่มเวลากดสลับให้มีกรอบชัดเจน)
    let currentStatusFilter = 'all';

    function applyFilterAndSearch() {
        const keyword = document.getElementById('searchInput').value.toLowerCase().trim();
        const rows = document.querySelectorAll('.category-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            const rowName = row.querySelector('.category-name').textContent.toLowerCase();

            const matchStatus = (currentStatusFilter === 'all' || rowStatus === currentStatusFilter);
            const matchKeyword = rowName.includes(keyword);

            if (matchStatus && matchKeyword) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const noMatchRow = document.getElementById('noMatchRow');
        if (noMatchRow) {
            noMatchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }
    }

    document.getElementById('searchInput')?.addEventListener('input', applyFilterAndSearch);

    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            
            // รีเซ็ตทุกปุ่มให้เป็นพื้นขาว ขอบเทา (สถานะยังไม่ถูกเลือก)
            document.querySelectorAll('.filter-btn').forEach(b => {
                b.classList.remove('btn-warning', 'text-dark', 'btn-success', 'btn-danger', 'text-white', 'active');
                b.classList.add('bg-white', 'border', 'text-secondary');
            });

            // ใส่สีให้ปุ่มที่ถูกคลิก
            this.classList.remove('bg-white', 'border', 'text-secondary');
            this.classList.add('active');
            
            currentStatusFilter = this.getAttribute('data-status');

            if (currentStatusFilter === 'all') {
                this.classList.add('btn-warning', 'text-dark');
            } else if (currentStatusFilter === '1') {
                this.classList.add('btn-success', 'text-white');
            } else {
                this.classList.add('btn-danger', 'text-white');
            }

            applyFilterAndSearch();
        });
    });
</script>