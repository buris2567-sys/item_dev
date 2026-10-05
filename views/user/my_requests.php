<?php
if (!defined('APP_RUNNING')) exit('Forbidden');
$user_id = $_SESSION['user_id'] ?? 0;

// ดึงข้อมูลคำร้องทั้งหมดของ User นี้
$stmt = $pdo->prepare("SELECT request_id, request_date, approved_at, status FROM requests WHERE user_id = ? ORDER BY request_date DESC");
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll();
?>

<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4 pb-2" style="border-bottom: 2px solid #e9ecef;">
    <div>
        <h3 class="fw-bolder mb-1 text-dark">รายการคำขอของฉัน</h3>
        <span class="text-muted small"></span>
    </div>


</div>

<!-- การ์ดตารางประวัติ -->
<div class="card border-0 rounded-4 shadow-sm mb-5 border-warning border-top border-4 bg-white">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center rounded-top-4">
        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-warning"></i>ประวัติคำขอทั้งหมด</h5>
    </div>

    <!-- //ช่องค้นหา -->
    <div class="d-flex gap-3 align-items-center justify-content-between p-3 border-bottom">
        <!-- ช่องค้นหา -->
        <div class="input-group shadow-sm" style="width: 250px;">
            <span class="input-group-text bg-white border-0 rounded-start-pill text-muted"><i class="bi bi-search"></i></span>
            <input type="text" id="searchRequestInput" class="form-control border-0 rounded-end-pill shadow-none" placeholder="ค้นหาเลขที่, สถานะ...">
        </div>
        <a href="index.php?page=create_request" class="btn btn-warning rounded-pill fw-bold px-4 py-2 shadow-sm border-0 d-flex align-items-center gap-2">
            <i class="bi bi-plus-circle-fill fs-5"></i> <span>สร้างคำขอใหม่</span>
        </a>
    </div>

    <div class="card-body p-0 table-responsive">
        <style>
            table#requestsTable {
                counter-reset: rowNumber;
            }

            table#requestsTable tbody tr.req-row:not(.d-none) {
                counter-increment: rowNumber;
            }

            table#requestsTable tbody tr.req-row:not(.d-none) .row-number::before {
                content: counter(rowNumber);
            }
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
                            $badgeClass .= 'bg-warning text-dark';
                        } elseif (strpos($status, 'ไม่อนุมัติ') !== false) {
                            $badgeClass .= 'bg-danger text-white';
                        } elseif (strpos($status, 'อนุมัติ') !== false) {
                            $badgeClass .= 'bg-success text-white';
                        } else {
                            $badgeClass .= 'bg-secondary text-white';
                        }
                    ?>
                        <tr class="req-row" data-reqid="<?= htmlspecialchars(strtolower($req['request_id'])) ?>" data-status="<?= htmlspecialchars(strtolower($status)) ?>">
                            <td class="fw-bold text-muted py-3 border-light"><span class="row-number bg-light rounded-circle d-inline-flex justify-content-center align-items-center" style="width: 30px; height: 30px;"></span></td>
                            <td class="fw-bold border-light"><span class="px-3 py-2 bg-light rounded-3 border text-dark"><?= htmlspecialchars($req['request_id']) ?></span></td>
                            <td class="border-light text-muted"><?= date('d/m/Y', strtotime($req['request_date'])) ?></td>
                            <td class="border-light text-muted"><?= $req['approved_at'] ? date('d/m/Y', strtotime($req['approved_at'])) : '-' ?></td>
                            <td class="border-light"><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span></td>
                            <td class="border-light">
                                <a href="index.php?page=request_view&id=<?= $req['request_id'] ?>" class="btn btn-sm btn-light rounded-circle shadow-sm text-info d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="ดูรายละเอียด">
                                    <i class="bi bi-search"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                <tr id="noDataSearchRow" class="d-none">
                    <td colspan="6" class="text-center py-5 text-muted border-0">
                        <h6 class="fw-bold mb-0">ไม่พบรายการที่ค้นหา</h6>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination Wrapper -->
    <div class="card-footer bg-white border-0 py-3 px-4 rounded-bottom-4 d-flex justify-content-center align-items-center">
        <div id="requestPaginationWrapper">
            <?php include 'includes/pagination.php'; ?>
        </div>
    </div>
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
                triggerInputs: [searchBox],
                filterLogic: function(row) {
                    const search = searchBox.value.toLowerCase().trim();
                    const reqId = row.getAttribute('data-reqid') || '';
                    const status = row.getAttribute('data-status') || '';
                    return reqId.includes(search) || status.includes(search);
                }
            });
        }
    });
</script>