<?php
// ป้องกันการเข้าไฟล์โดยตรง และตรวจสอบสิทธิ์ Admin
if (!defined('APP_RUNNING')) exit('Forbidden');
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') exit('Unauthorized');

// ดึงข้อมูลคำขอทั้งหมดจากฐานข้อมูล
$requests = $pdo->query("
    SELECT r.request_id, r.request_no, u.username as requester_name, r.request_date, r.approved_at, r.status 
    FROM requests r
    LEFT JOIN users u ON r.user_id = u.user_id
    ORDER BY r.request_date DESC
")->fetchAll();
?>

<?php
$pageHeader = [
    'icon'  => 'bi-list-task',
    'title' => 'รายการคำขอ',
];
include 'components/ui/page_header.php';
?>

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
                    <th class="fw-bold py-3 border-0">หมายเลขคำขอ</th>
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
                            $badgeClass .= 'bg-primary text-white';
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
                            <td class="fw-bold">
                                <span class="px-2 py-1 bg-light rounded-3 border text-dark">
                                    <?= htmlspecialchars($req['request_no']) ?>
                                </span>
                            </td>
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
                                    <a href="index.php?page=request_view&id=<?= $req['request_id'] ?>"
                                       class="btn btn-sm btn-light rounded-circle shadow-sm text-info d-flex align-items-center justify-content-center"
                                       style="width: 36px; height: 36px;" title="พิจารณา/ดูรายละเอียด">
                                        <i class="bi bi-search"></i>
                                    </a>
                                    <?php if ($req['status'] === 'อนุมัติ' || $req['status'] === 'อนุมัติบางส่วน'): ?>
                                        <a href="actions/export_pdf.php?id=<?= $req['request_id'] ?>"
                                           class="btn btn-sm btn-light rounded-circle shadow-sm text-dark d-flex align-items-center justify-content-center"
                                           style="width: 36px; height: 36px;" title="พิมพ์เอกสาร">
                                            <i class="bi bi-printer"></i>
                                        </a>
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
