<?php
// ป้องกันการเข้าไฟล์โดยตรง และตรวจสอบสิทธิ์ Admin
if (!defined('APP_RUNNING')) exit('Forbidden');
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') exit('Unauthorized');

// ดึงข้อมูลคำขอทั้งหมดจากฐานข้อมูล
$requests = $pdo->query("
    SELECT r.request_id, u.username as requester_name, r.request_date, r.approved_at, r.status 
    FROM requests r
    LEFT JOIN users u ON r.user_id = u.user_id
    ORDER BY r.request_date DESC
")->fetchAll();
$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>

<?php
$pageHeader = [
    'icon'  => 'bi-list-task',
    'title' => 'รายการคำขอ ลงทะเบียนบัญชีผู้ใช้',
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
    $cardHeader = [
        'icon'   => 'bi-list-task',
        'title'  => 'คำขอทั้งหมด',
        'search' => ['id' => 'searchRequestInput', 'placeholder' => 'ค้นหาเลขที่, สถานะ...'],
    ];
    include 'components/ui/card_header.php';
    ?>

    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle text-center mb-0">
            <thead class="table-light text-muted">
                <tr>
                    <th class="fw-bold py-3 border-0">ลำดับ</th>
                    
                    <th class="fw-bold py-3 border-0">ผู้ขอ</th>
                    <th class="fw-bold py-3 border-0">วันที่ขอ</th>
                    <th class="fw-bold py-3 border-0">วันที่อนุมัติ</th>
                    <th class="fw-bold py-3 border-0">สถานะ</th>
                    <th class="fw-bold py-3 border-0">การจัดการ</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted border-0">
                            <div class="bg-light rounded-circle d-inline-flex justify-content-center align-items-center mb-3 shadow-sm" style="width: 70px; height: 70px;">
                                <i class="bi bi-inbox fs-2 text-secondary opacity-50"></i>
                            </div>
                            <h6 class="fw-bold mb-0">ยังไม่มีรายการคำขอในระบบ</h6>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $index => $req):
                        $status = $req['status'];
                        $badgeClass = 'rounded-pill px-3 py-2 fw-bold shadow-sm ';

                        if ($status === 'รออนุมัติ' || $status === 'Pending') {
                            $badgeClass .= 'bg-warning text-dark';
                            $status = 'รออนุมัติ';
                        } elseif (strpos($status, 'อนุมัติบางส่วน') !== false) {
                            $badgeClass .= 'bg-warning text-dark';
                        } elseif (strpos($status, 'ไม่อนุมัติ') !== false) {
                            $badgeClass .= 'bg-danger text-white';
                        } elseif (strpos($status, 'อนุมัติ') !== false) {
                            $badgeClass .= 'bg-success text-white';
                        } else {
                            $badgeClass .= 'bg-secondary text-white';
                        }
                    ?>
                        <tr class="req-row"
                            data-reqid="<?= htmlspecialchars(strtolower($req['request_id'])) ?>"
                            data-status="<?= htmlspecialchars(strtolower($status)) ?>"
                            style="border-bottom: 1px solid #f8f9fa;">
                            <td class="fw-bold text-muted py-3"><?= $index + 1 ?></td>
                            
                            <td class="text-dark fw-semibold"><?= htmlspecialchars($req['requester_name']) ?></td>
                            <td class="text-muted"><?= date('d/m/Y', strtotime($req['request_date'])) ?></td>
                            <td class="text-muted"><?= $req['approved_at'] ? date('d/m/Y', strtotime($req['approved_at'])) : '-' ?></td>
                            <td>
                                <span class="badge fs-6 fw-bold px-1 py-2 <?= $badgeClass ?>">
                                    <?= htmlspecialchars($status) ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="index.php?page=request_view&id=<?= $req['request_id'] ?>" class="btn btn-sm btn-warning fw-bold px-3 shadow-sm">
                                        ดูรายละเอียด
                                    </a>

                                    <?php if ($req['status'] === 'รออนุมัติ' || $req['status'] === 'Pending'): ?>
                                        <form action="actions/admin/request_approve.php" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการอนุมัติคำขอนี้และตัดสต็อกตามจำนวนที่ขอ?');">
                                            <input type="hidden" name="request_id" value="<?= htmlspecialchars($req['request_id']) ?>">
                                            <input type="hidden" name="action" value="approve_all">
                                            <input type="hidden" name="return_to_list" value="1">
                                            <button type="submit" class="btn btn-sm btn-success fw-bold px-3 shadow-sm">อนุมัติ</button>
                                        </form>
                                        <form action="actions/admin/request_approve.php" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการไม่อนุมัติคำขอนี้?');">
                                            <input type="hidden" name="request_id" value="<?= htmlspecialchars($req['request_id']) ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="return_to_list" value="1">
                                            <button type="submit" class="btn btn-sm btn-danger fw-bold px-3 shadow-sm">ไม่อนุมัติ</button>
                                        </form>
                                    <?php endif; ?>
                                   
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="noDataSearchRow" style="display: none;">
                        <td colspan="7" class="text-center py-5 text-muted border-0">
                            <h6 class="fw-bold mb-0">ไม่พบรายการที่ค้นหา</h6>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php
    $paginationWrapperId = 'requestPaginationWrapper';
    include 'components/ui/card_footer_pagination.php';
    ?>
</div>

<script src="assets/js/table_pagination.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchBox = document.getElementById('searchRequestInput');

        if (typeof initTablePagination === 'function') {
            initTablePagination({
                rowSelector: '.req-row',
                wrapperSelector: '#requestPaginationWrapper',
                noDataSelector: '#noDataSearchRow',
                triggerInputs: searchBox ? [searchBox] : [],
                filterLogic: function(row) {
                    if (!searchBox) return true;
                    const search = searchBox.value.toLowerCase().trim();
                    const reqId = row.getAttribute('data-reqid') || '';
                    const status = row.getAttribute('data-status') || '';
                    return reqId.includes(search) || status.includes(search);
                }
            });
        }
    });
</script>


<!-- request_view  ให้ใช้ฟอร์มเดียวกับที่ดู profile -->