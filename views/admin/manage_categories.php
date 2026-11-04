<?php
// ตรวจสอบสิทธิ์การเข้าถึง และดึงข้อมูลจากฐานข้อมูล
if (!defined('APP_RUNNING')) exit('Forbidden');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    $_SESSION['error'] = 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถเข้าถึงหน้านี้ได้';
    header("Location: index.php");
    exit;
}
require_once 'config/db.php';

// ดึงตัวเลขนับสถานะ
$countAll      = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$countActive   = $pdo->query("SELECT COUNT(*) FROM categories WHERE is_active = 1")->fetchColumn();
$countInactive = $pdo->query("SELECT COUNT(*) FROM categories WHERE is_active = 0")->fetchColumn();

// ดึงข้อมูลประเภทสิ่งของทั้งหมด
$stmt       = $pdo->query("SELECT * FROM categories ORDER BY category_id DESC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>


<!-- CSS ควบคุมสีสวิตช์ -->
<style>
    .form-switch .form-check-input:checked {
        background-color: #ffc107 !important;
        border-color: #ffc107 !important;
    }
</style>

<?php
$pageHeader = [
    'icon'   => 'bi-tags',
    'title'  => 'จัดการประเภทสิ่งของ',
    'action' => [
        'label'  => 'เพิ่มรายการใหม่',
        'icon'   => 'bi-plus-circle-fill',
        'type'   => 'modal',
        'target' => '#addCategoryModal',
    ],
];
include 'components/ui/page_header.php';
?>
<?php if ($success): ?>
    <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<!-- Card ตารางประเภทสิ่งของ -->
<div class="card border-0 rounded-4 shadow-sm mb-5 border-warning border-top border-4 bg-white">

    <?php
    // Extra slot: filter buttons (ทั้งหมด / เปิดใช้งาน / ปิดใช้งาน)
    ob_start(); ?>
    <button type="button" class="btn btn-warning text-dark fw-bold px-3 py-1 rounded-pill filter-btn active" data-status="all">
        ทั้งหมด <span class="badge text-dark ms-1 rounded-pill fs-6"><?= $countAll ?></span>
    </button>
    <button type="button" class="btn bg-white border text-secondary fw-bold px-3 py-1 rounded-pill filter-btn" data-status="1">
        เปิดใช้งาน <span class="badge bg-success bg-opacity-25 text-success ms-1 rounded-pill fs-6"><?= $countActive ?></span>
    </button>
    <button type="button" class="btn bg-white border text-secondary fw-bold px-3 py-1 rounded-pill filter-btn" data-status="0">
        ปิดใช้งาน <span class="badge bg-danger bg-opacity-25 text-danger ms-1 rounded-pill fs-6"><?= $countInactive ?></span>
    </button>
    <?php $extraHtml = ob_get_clean();

    $cardHeader = [
        'icon'   => 'bi-box-seam',
        'title'  => 'รายการประเภทสิ่งของ',
        'search' => ['id' => 'searchInput', 'placeholder' => 'ค้นหาชื่อประเภท...'],
        'extra'  => $extraHtml,
    ];
    include 'components/ui/card_header.php';
    ?>

    <!-- ตารางข้อมูล -->
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle mb-0 text-center border-white" id="categoryTable">
            <thead class="table-light text-muted">
                <tr>
                    <th class="fw-bold py-3 border-0">ลำดับ</th>
                    <th class="fw-bold py-3 border-0 text-start">ชื่อประเภท</th>
                    <th class="fw-bold py-3 border-0">วันที่สร้าง</th>
                    <th class="fw-bold py-3 border-0">สถานะ</th>
                    <th class="fw-bold py-3 border-0" style="width: 180px;">การจัดการ</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                <?php if (count($categories) > 0): ?>
                    <?php $i = 1;
                    foreach ($categories as $cat): ?>
                        <tr class="category-row" data-status="<?= $cat['is_active'] ?>" style="border-bottom: 1px solid #f8f9fa;">
                            <td class="fw-bold text-muted py-3 row-number"><?= $i++ ?></td>
                            <td class="text-dark fw-semibold text-start py-3 category-name"><?= htmlspecialchars($cat['name']) ?></td>
                            <td class="text-muted py-3"><?= date('d/m/Y H:i', strtotime($cat['created_at'])) ?></td>
                            <td>
                                <?php if ($cat['is_active'] == 1): ?>
                                    <span class="badge bg-success text-white px-3 py-2 rounded-pill shadow-sm" style="min-width: 100px;">
                                        <i class="bi bi-circle-fill small me-1" style="font-size: 0.4rem;"></i> เปิดใช้งาน
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary text-white px-3 py-2 rounded-pill shadow-sm" style="min-width: 100px;">
                                        <i class="bi bi-circle-fill small me-1" style="font-size: 0.4rem;"></i> ปิดใช้งาน
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- ใช้ Grid เพื่อให้พื้นที่การจัดการกว้างเท่ากัน -->
                                <div class="row g-2 justify-content-center align-items-center">
                                    <div class="col-6 d-flex justify-content-end">
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input toggle-category-btn shadow-sm"
                                                type="checkbox" role="switch"
                                                data-id="<?= $cat['category_id'] ?>"
                                                <?= $cat['is_active'] == 1 ? 'checked' : '' ?>
                                                style="width: 38px; height: 20px; cursor: pointer;">
                                        </div>
                                    </div>
                                    <div class="col-6 d-flex justify-content-start">
                                        <button type="button"
                                            class="btn btn-sm  fw-bold shadow-sm edit-category-btn w-100"
                                            data-id="<?= $cat['category_id'] ?>"
                                            data-name="<?= htmlspecialchars($cat['name']) ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editCategoryModal"
                                            title="แก้ไขข้อมูล">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr id="emptyRow">
                        <td colspan="5" class="text-center py-5 text-muted border-0">
                            <div class="bg-light rounded-circle d-inline-flex justify-content-center align-items-center mb-3 shadow-sm" style="width: 70px; height: 70px;">
                                <i class="bi bi-tags fs-2 text-secondary opacity-50"></i>
                            </div>
                            <h6 class="fw-bold mb-0">ยังไม่มีข้อมูลประเภทสิ่งของ</h6>
                        </td>
                    </tr>
                <?php endif; ?>
                
                <tr id="noMatchRow" style="display: none;">
                    <td colspan="5" class="text-center py-5 text-muted border-0">
                        <h6 class="fw-bold mb-0">ไม่พบข้อมูลที่ค้นหา</h6>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php
    $paginationWrapperId = 'categoryPaginationWrapper';
    include 'components/ui/card_footer_pagination.php';
    ?>
</div>

<!-- Modal Components -->
<?php include 'components/modals/add_category_modal.php'; ?>
<?php include 'components/modals/edit_category_modal.php'; ?>

<script src="assets/js/table_pagination.js"></script>
<script>
    // 1. Pagination สำหรับตารางประเภทสิ่งของ
    document.addEventListener("DOMContentLoaded", function() {
        const searchBox = document.getElementById('searchInput');

        if (typeof initTablePagination === 'function') {
            initTablePagination({
                rowSelector: '.category-row',
                wrapperSelector: '#categoryPaginationWrapper',
                noDataSelector: '#noMatchRow',
                triggerInputs: searchBox ? [searchBox] : [],
                filterLogic: function(row) {
                    const keyword = searchBox ? searchBox.value.toLowerCase().trim() : '';
                    const rowStatus = row.getAttribute('data-status');
                    const rowName = row.querySelector('.category-name')?.textContent.toLowerCase() || '';
                    const matchStatus = (currentStatusFilter === 'all' || rowStatus === currentStatusFilter);
                    const matchKeyword = rowName.includes(keyword);
                    return matchStatus && matchKeyword;
                }
            });
        }
    });

    // 2. ตรวจสอบชื่อซ้ำ & บันทึกเพิ่มหมวดหมู่
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
            }).catch(() => {
                alert('เกิดข้อผิดพลาดในการตรวจสอบชื่อซ้ำ');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
    });

    // 3. ดึงข้อมูลใส่ Modal แก้ไข
    document.querySelectorAll('.edit-category-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const targetBtn = e.target.closest('.edit-category-btn');
            document.getElementById('editCategoryId').value = targetBtn.getAttribute('data-id');
            document.getElementById('editCategoryName').value = targetBtn.getAttribute('data-name');
        });
    });

    // 4. ตรวจสอบชื่อซ้ำ & บันทึกแก้ไขหมวดหมู่
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
            }).catch(() => {
                alert('เกิดข้อผิดพลาดในการตรวจสอบชื่อซ้ำ');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
    });

    // 5. สวิตช์ Toggle เปิด/ปิด สถานะ
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
                }).catch(() => {
                    alert('ไม่สามารถติดต่อเซิร์ฟเวอร์ได้');
                    switchElement.checked = !switchElement.checked;
                    switchElement.disabled = false;
                });
        });
    });

    // 6. ค้นหา & ตัวกรองสถานะ (ต้องเป็น let เพราะ filterLogic ของ pagination อ่านค่านี้ด้วย)
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
        if (noMatchRow) noMatchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
    }

    document.getElementById('searchInput')?.addEventListener('input', applyFilterAndSearch);

    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => {
                b.classList.remove('btn-warning', 'text-dark', 'btn-success', 'btn-danger', 'text-white', 'active');
                b.classList.add('bg-white', 'border', 'text-secondary');
            });
            this.classList.remove('bg-white', 'border', 'text-secondary');
            this.classList.add('active');
            currentStatusFilter = this.getAttribute('data-status');

            if (currentStatusFilter === 'all') this.classList.add('btn-warning', 'text-dark');
            else if (currentStatusFilter === '1') this.classList.add('btn-success', 'text-white');
            else this.classList.add('btn-danger', 'text-white');

            applyFilterAndSearch();
        });
    });
</script>