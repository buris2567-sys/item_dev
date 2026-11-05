<?php
if (!defined('APP_RUNNING')) exit('Forbidden');
$user_id = $_SESSION['user_id'] ?? 0;

// ดึงข้อมูลคำร้องทั้งหมดของ User นี้ (รวม is_read_user เพื่อตรวจจับสถานะแจ้งเตือน)
$stmt = $pdo->prepare("SELECT request_id, request_no, request_date, approved_at, status, is_read_user FROM requests WHERE user_id = ? ORDER BY request_date DESC");
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll();

// 🟢 [ปรับปรุง] คำนวณจำนวนสถานะคำขอต่างๆ เพื่อทำปุ่ม Filter Badge สไตล์เดียวกับหน้ารายการประเภทสิ่งของ
$countAll = count($requests);
$countNew = 0;
$countPending = 0;
$countApproved = 0;

foreach ($requests as $r) {
    $st = trim($r['status'] ?? '');
    if ($st !== 'รออนุมัติ' && strtolower($st) !== 'pending' && empty($r['is_read_user'])) {
        $countNew++;
    }
    if ($st === 'รออนุมัติ' || strtolower($st) === 'pending') {
        $countPending++;
    } elseif ($st === 'อนุมัติ' || strpos($st, 'อนุมัติบางส่วน') !== false) {
        $countApproved++;
    }
}
?>

<?php
$pageHeader = [
    'icon'   => 'bi-clock-history',
    'title'  => 'รายการคำขอของฉัน',
    'action' => [
        'label'  => 'สร้างคำขอใหม่',
        'icon'   => 'bi-plus-circle-fill',
        'type'   => 'link',
        'target' => 'index.php?page=create_request',
    ],
];
include 'components/ui/page_header.php';
?>

<!-- การ์ดตารางประวัติ -->
<div class="card border-0 rounded-4 shadow-sm mb-5 border-warning border-top border-4 bg-white">

<?php
// 🟢 [ปรับปรุง] ปุ่ม Filter Pills ที่หัวการ์ดตาราง (สไตล์เดียวกับหน้ารายการประเภทสิ่งของ มีปุ่มสีเหลืองและป้ายจำนวนที่สมดุล)
ob_start(); ?>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <button type="button" class="btn btn-warning text-dark fw-bold px-3 py-1 rounded-pill filter-btn active" data-filter="all">
            ทั้งหมด <span class="badge text-dark ms-1 rounded-pill fs-6"><?= $countAll ?></span>
        </button>
        <?php if ($countNew > 0): ?>
            <button type="button" class="btn bg-white border text-danger fw-bold px-3 py-1 rounded-pill filter-btn" data-filter="new">
                ยังไม่อ่าน <span class="badge bg-danger text-white ms-1 rounded-pill fs-6"><?= $countNew ?></span>
            </button>
        <?php endif; ?>
        <button type="button" class="btn bg-white border text-secondary fw-bold px-3 py-1 rounded-pill filter-btn" data-filter="pending">
            รออนุมัติ <span class="badge bg-warning bg-opacity-25 text-dark ms-1 rounded-pill fs-6"><?= $countPending ?></span>
        </button>
        <button type="button" class="btn bg-white border text-secondary fw-bold px-3 py-1 rounded-pill filter-btn" data-filter="approved">
            อนุมัติแล้ว <span class="badge bg-success bg-opacity-25 text-success ms-1 rounded-pill fs-6"><?= $countApproved ?></span>
        </button>
    </div>
<?php $userExtraHtml = ob_get_clean();

$cardHeader = [
    'icon'   => 'bi-clock-history',
    'title'  => 'ประวัติคำขอทั้งหมด',
    'search' => ['id' => 'searchRequestInput', 'placeholder' => 'ค้นหาเลขที่, สถานะ...'],
    'extra'  => $userExtraHtml,
];
include 'components/ui/card_header.php';
?>

    <div class="card-body p-0 table-responsive">
        <style>
            table#requestsTable { counter-reset: rowNumber; }
            table#requestsTable tbody tr.req-row:not(.d-none) { counter-increment: rowNumber; }
            table#requestsTable tbody tr.req-row:not(.d-none) .row-number::before { content: counter(rowNumber); }
        </style>

        <table class="table table-hover align-middle text-center mb-0" id="requestsTable">
            <thead class="table-light text-muted">
                <tr>
                    <th class="fw-bold py-3 border-0">ลำดับ</th>
                    <th class="fw-bold py-3 border-0">หมายเลขคำขอ</th>
                    <th class="fw-bold py-3 border-0">วันที่ขอ</th>
                    <th class="fw-bold py-3 border-0">วันที่อนุมัติ</th>
                    <th class="fw-bold py-3 border-0">สถานะ</th>
                    <th class="fw-bold py-3 border-0">รายละเอียด</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                <?php if (empty($requests)): ?>
                    <tr id="emptyRowDefault">
                        <td colspan="6" class="text-center py-5 text-muted border-0">
                            <div class="bg-light rounded-circle d-inline-flex justify-content-center align-items-center mb-3 shadow-sm" style="width: 70px; height: 70px;">
                                <i class="bi bi-file-earmark-x fs-2 text-secondary opacity-50"></i>
                            </div>
                            <h6 class="fw-bold mb-0">คุณยังไม่มีประวัติการส่งคำขอ</h6>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $req):
                        $status = $req['status'];
                        $badgeClass = 'rounded-pill px-4 py-2 fw-bold shadow-sm ';

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

                        // 🟢 ตรวจจับว่าแถวนี้เป็นผลพิจารณาใหม่ที่ยังไม่ได้เปิดดูหรือไม่
                        $isNewUpdate = ($status !== 'รออนุมัติ' && empty($req['is_read_user']));
                    ?>
                        <tr class="req-row"
                            data-reqid="<?= htmlspecialchars(strtolower($req['request_id'])) ?>"
                            data-reqno="<?= htmlspecialchars(strtolower($req['request_no'])) ?>"
                            data-new="<?= $isNewUpdate ? '1' : '0' ?>"
                            data-status="<?= htmlspecialchars(strtolower($status)) ?>">
                            <td class="fw-bold text-muted py-3 border-light">
                                <span class="row-number bg-light rounded-circle d-inline-flex justify-content-center align-items-center" style="width: 30px; height: 30px;"></span>
                            </td>
                            <td class="fw-bold border-light">
                                <!-- 🟢 [ปรับปรุง] ใช้กล่องขนาดคงที่ และวางป้าย NEW ไว้ที่มุมกล่อง ไม่ทำให้ตารางโยเย -->
                                <span class="px-3 py-2 bg-light rounded-3 border text-dark position-relative d-inline-block">
                                    <?= htmlspecialchars($req['request_no']) ?>
                                    <?php if ($isNewUpdate): ?>
                                        <span class="position-absolute top-0 start-100 translate-middle badge bg-danger rounded-pill shadow-sm"
                                              style="font-size: 0.6rem; transform: translate(-25%, -35%) !important;" title="ผลพิจารณาใหม่ ยังไม่ได้เปิดอ่าน">
                                            NEW
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="border-light text-muted"><?= date('d/m/Y', strtotime($req['request_date'])) ?></td>
                            <td class="border-light text-muted"><?= $req['approved_at'] ? date('d/m/Y', strtotime($req['approved_at'])) : '-' ?></td>
                            <td class="border-light">
                                <span class="badge fs-6 fw-bold px-4 py-2 <?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span>
                            </td>
                            <td class="py-3">
                                <div class="d-flex justify-content-center gap-2">
                                    <!-- 🟢 [ปรับปรุง] ปุ่มแว่นขยาย พร้อมจุดแดงแจ้งเตือนเมื่อยังไม่ได้เปิดดูผล -->
                                    <a href="index.php?page=request_view&id=<?= $req['request_id'] ?>"
                                       class="btn btn-sm btn-light rounded-circle shadow-sm text-info d-inline-flex align-items-center justify-content-center position-relative"
                                       style="width: 36px; height: 36px;" title="ดูรายละเอียด">
                                        <i class="bi bi-search"></i>
                                        <?php if ($isNewUpdate): ?>
                                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" title="ยังไม่ได้เปิดอ่าน">
                                                <span class="visually-hidden">New notification</span>
                                            </span>
                                        <?php endif; ?>
                                    </a>
                                    <?php if ($req['status'] === 'รออนุมัติ'): ?>
                                        <a href="index.php?page=create_request&id=<?= $req['request_id'] ?>"
                                            class="btn btn-sm btn-light text-warning border rounded-circle shadow-sm"
                                            style="width: 32px; height: 32px;" title="แก้ไขคำร้อง">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    <?php endif; ?>
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
                        <td colspan="6" class="text-center py-5 text-muted border-0">
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
        let currentStatusFilter = 'all';

        if (typeof initTablePagination === 'function') {
            initTablePagination({
                rowSelector: '.req-row',
                wrapperSelector: '#requestPaginationWrapper',
                noDataSelector: '#noDataSearchRow',
                triggerInputs: searchBox ? [searchBox] : [],
                filterLogic: function(row) {
                    const search = searchBox ? searchBox.value.toLowerCase().trim() : '';
                    const reqId = row.getAttribute('data-reqid') || '';
                    const reqNo = row.getAttribute('data-reqno') || '';
                    const status = row.getAttribute('data-status') || '';
                    const isNew = row.getAttribute('data-new') === '1';

                    let matchFilter = true;
                    if (currentStatusFilter === 'new') {
                        matchFilter = isNew;
                    } else if (currentStatusFilter === 'pending') {
                        matchFilter = (status === 'รออนุมัติ' || status === 'pending');
                    } else if (currentStatusFilter === 'approved') {
                        matchFilter = (status.includes('อนุมัติ') && !status.includes('ไม่อนุมัติ') && status !== 'รออนุมัติ');
                    }

                    const matchSearch = reqId.includes(search) || reqNo.includes(search) || status.includes(search);
                    return matchFilter && matchSearch;
                }
            });
        }

        // 🟢 ควบคุมปุ่ม Filter Pills ที่ส่วนหัวการ์ด
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => {
                    b.classList.remove('btn-warning', 'text-dark', 'active');
                    b.classList.add('bg-white', 'border', 'text-secondary');
                });
                this.classList.remove('bg-white', 'border', 'text-secondary');
                this.classList.add('btn-warning', 'text-dark', 'active');
                currentStatusFilter = this.getAttribute('data-filter') || 'all';

                if (searchBox) {
                    searchBox.dispatchEvent(new Event('input'));
                }
            });
        });
    });
</script>
