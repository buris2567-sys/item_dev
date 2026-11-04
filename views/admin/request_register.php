<?php
// ป้องกันการเข้าไฟล์โดยตรง และตรวจสอบสิทธิ์ Admin
if (!defined('APP_RUNNING')) exit('Forbidden');
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') exit('Unauthorized');

require_once 'config/db.php';

// ดึงตัวเลขนับสถานะ
$countAll      = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'User'")->fetchColumn();
$countWait     = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'User' AND approve_user = 0")->fetchColumn();
$countActive   = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'User' AND approve_user = 1")->fetchColumn();
$countInactive = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'User' AND approve_user = 2")->fetchColumn();

// ดึงข้อมูลผู้ใช้งาน
$users = $pdo->query("
    SELECT user_id, full_name, created_at, approve_user, role 
    FROM users 
    WHERE role = 'User' 
    ORDER BY created_at DESC 
")->fetchAll();

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>

<?php
$pageHeader = [
    'icon'  => 'bi-person-plus',
    'title' => 'จัดการคำร้องลงทะเบียนผู้ใช้ใหม่',
];
include 'components/ui/page_header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<!-- Card ตารางรายการคำขอ -->
<div class="card border-0 rounded-4 shadow-sm mb-5 border-warning border-top border-4 bg-white">
    <?php
    // ปุ่ม Filter สถานะ (ทั้งหมด / รอการพิจารณา / อนุมัติแล้ว / ไม่อนุมัติ)
    ob_start(); ?>
    <button type="button" class="btn btn-warning text-dark fw-bold px-3 py-1 rounded-pill filter-btn active" data-status="all">
        ทั้งหมด <span class="badge text-dark ms-1 rounded-pill fs-6"><?= $countAll ?></span>
    </button>
    <button type="button" class="btn bg-white border text-secondary fw-bold px-3 py-1 rounded-pill filter-btn" data-status="0">
        รอการพิจารณา <span class="badge bg-warning bg-opacity-25 text-warning ms-1 rounded-pill fs-6"><?= $countWait ?></span>
    </button>
    <button type="button" class="btn bg-white border text-secondary fw-bold px-3 py-1 rounded-pill filter-btn" data-status="1">
        อนุมัติแล้ว <span class="badge bg-success bg-opacity-25 text-success ms-1 rounded-pill fs-6"><?= $countActive ?></span>
    </button>
    <button type="button" class="btn bg-white border text-secondary fw-bold px-3 py-1 rounded-pill filter-btn" data-status="2">
        ไม่อนุมัติ <span class="badge bg-danger bg-opacity-25 text-danger ms-1 rounded-pill fs-6"><?= $countInactive ?></span>
    </button>
    <?php $extraHtml = ob_get_clean();

    $cardHeader = [
        'icon'   => 'bi-person-plus',
        'title'  => 'รายการคำร้องลงทะเบียนทั้งหมด',
        'search' => ['id' => 'searchUserInput', 'placeholder' => 'ค้นหาชื่อ-นามสกุล...'],
        'extra'  => $extraHtml,
    ];
    include 'components/ui/card_header.php';
    ?>

    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle text-center mb-0">
            <thead class="table-light text-muted">
                <tr>
                    <th class="fw-bold py-3 border-0">ลำดับ</th>
                    <th class="fw-bold py-3 border-0 text-start">ชื่อ-นามสกุล</th>
                    <th class="fw-bold py-3 border-0">วันที่สมัคร</th>
                    <th class="fw-bold py-3 border-0">สถานะ</th>
                    <th class="fw-bold py-3 border-0" style="width: 280px;">การจัดการ</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted border-0">
                            <div class="bg-light rounded-circle d-inline-flex justify-content-center align-items-center mb-3 shadow-sm" style="width: 70px; height: 70px;">
                                <i class="bi bi-people fs-2 text-secondary opacity-50"></i>
                            </div>
                            <h6 class="fw-bold mb-0">ยังไม่มีรายการคำขอลงทะเบียน</h6>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $index => $user):
                        $statusVal = (int)($user['approve_user'] ?? 0);
                        $badgeClass = 'rounded-pill px-3 py-2 fw-bold shadow-sm ';

                        if ($statusVal === 0) {
                            $statusText = 'รอการพิจารณา';
                            $badgeClass .= 'bg-warning text-dark';
                        } elseif ($statusVal === 1) {
                            $statusText = 'อนุมัติแล้ว';
                            $badgeClass .= 'bg-success text-white';
                        } else {
                            $statusText = 'ไม่อนุมัติ';
                            $badgeClass .= 'bg-danger text-white';
                        }
                    ?>
                        <tr class="user-row" 
                            data-status="<?= $statusVal ?>" 
                            style="border-bottom: 1px solid #f8f9fa;">
                            
                            <td class="fw-bold text-muted py-3"><?= $index + 1 ?></td>
                            
                            <!-- คลาส user-fullname สำหรับค้นหา -->
                            <td class="text-dark fw-semibold text-start user-fullname">
                                <?= htmlspecialchars($user['full_name'] ?? '') ?>
                            </td>
                            
                            <td class="text-muted"><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                            
                            <td>
                                <span class="badge fs-6 fw-bold px-1 py-2 <?= $badgeClass ?>" style="min-width: 100px;">
                                    <?= htmlspecialchars($statusText) ?>
                                </span>
                            </td>
                            
                            <td>
                                <div class="row g-2 justify-content-center">
                                    <div class="col-2">
                                        <a href="index.php?page=request_view_user&id=<?= $user['user_id'] ?>" class="btn btn-sm  fw-bold w-100 shadow-sm" title="ดูรายละเอียด">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                    <?php if ($statusVal === 0): ?>
                                        <div class="col-4">
                                            <form action="actions/admin/request_register_backend.php" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการอนุมัติผู้ใช้งานนี้?');">
                                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['user_id']) ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn btn-sm  fw-bold w-100 shadow-sm"> <i class="bi bi-check2"></i>  อนุมัติ</button>
                                            </form>
                                        </div>
                                        <div class="col-5">
                                            <form action="actions/admin/request_register_backend.php" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการไม่อนุมัติผู้ใช้งานนี้?');">
                                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['user_id']) ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="btn btn-sm  fw-bold w-100 shadow-sm"> <i class="bi bi-x"></i>  ไม่อนุมัติ</button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <div class="col-4"><button class="btn btn-sm btn-success fw-bold w-100 shadow-sm disabled" style="opacity: 0;">-</button></div>
                                        <div class="col-4"><button class="btn btn-sm btn-danger fw-bold w-100 shadow-sm disabled" style="opacity: 0;">-</button></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <tr id="noDataSearchRowUser" style="display: none;">
                    <td colspan="5" class="text-center py-5 text-muted border-0">
                        <h6 class="fw-bold mb-0">ไม่พบรายการที่ค้นหา</h6>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php
    $paginationWrapperId = 'userPaginationWrapper';
    include 'components/ui/card_footer_pagination.php';
    ?>
</div>

<script src="assets/js/table_pagination.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // --- 1. ระบบค้นหาและตัวกรองสถานะ (แบบเดียวกับหมวดหมู่) ---
    let currentStatusFilter = 'all';
    const searchBox = document.getElementById('searchUserInput');
    const rows = document.querySelectorAll('.user-row');
    const noMatchRow = document.getElementById('noDataSearchRowUser');

    function applyFilterAndSearch() {
        const keyword = searchBox ? searchBox.value.toLowerCase().trim() : '';
        let visibleCount = 0;

        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            const rowName = row.querySelector('.user-fullname').textContent.toLowerCase();
            
            const matchStatus = (currentStatusFilter === 'all' || rowStatus === currentStatusFilter);
            const matchKeyword = rowName.includes(keyword);

            if (matchStatus && matchKeyword) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (noMatchRow) {
            noMatchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }
    }

    // จับ Event การพิมพ์ค้นหา
    if (searchBox) {
        searchBox.addEventListener('input', applyFilterAndSearch);
    }

    // จับ Event การคลิกปุ่มกรองสถานะ
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // ล้างสีปุ่มเดิมทั้งหมด
            document.querySelectorAll('.filter-btn').forEach(b => {
                b.classList.remove('btn-warning', 'text-dark', 'btn-success', 'btn-danger', 'text-white', 'active');
                b.classList.add('bg-white', 'border', 'text-secondary');
            });
            
            // ใส่สีให้ปุ่มที่ถูกคลิก
            this.classList.remove('bg-white', 'border', 'text-secondary');
            this.classList.add('active');
            
            currentStatusFilter = this.getAttribute('data-status');

            if (currentStatusFilter === 'all') this.classList.add('btn-warning', 'text-dark');
            else if (currentStatusFilter === '1') this.classList.add('btn-success', 'text-white');
            else if (currentStatusFilter === '2') this.classList.add('btn-danger', 'text-white');
            else this.classList.add('btn-warning', 'text-dark');

            applyFilterAndSearch();
        });
    });

    // --- 2. ระบบแบ่งหน้า (Pagination) ---
    if (typeof initTablePagination === 'function') {
        initTablePagination({
            rowSelector: '.user-row',
            wrapperSelector: '#userPaginationWrapper',
            noDataSelector: '#noDataSearchRowUser',
            triggerInputs: searchBox ? [searchBox] : [],
            filterLogic: function(row) {
                // ผูกเข้ากับตรรกะด้านบน
                if (!searchBox) return true;
                const keyword = searchBox.value.toLowerCase().trim();
                const rowStatus = row.getAttribute('data-status');
                const rowName = row.querySelector('.user-fullname').textContent.toLowerCase();
                
                const matchStatus = (currentStatusFilter === 'all' || rowStatus === currentStatusFilter);
                const matchKeyword = rowName.includes(keyword);
                
                return matchStatus && matchKeyword;
            }
        });
    }
});
</script>