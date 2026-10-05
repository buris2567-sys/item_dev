<?php
if (!defined('APP_RUNNING')) exit('Forbidden');
$user_id = $_SESSION['user_id'] ?? 0;

// ดึงข้อมูลคำร้องทั้งหมดของ User นี้
$stmt = $pdo->prepare("SELECT request_id, request_date, approved_at, status FROM requests WHERE user_id = ? ORDER BY request_date DESC");
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll();
?>

<!-- 🟢 [ปรับปรุง] เพิ่ม Container และ Sidebar ให้ตรงกับ Layout หลักของระบบ -->
<div class="container-fluid p-0" style="background-color: #f4f6f8; min-height: 100vh;">
    <div class="row g-0 flex-nowrap">

        <!-- ดึง Sidebar -->
        <?php include 'includes/sidebar_user.php'; ?>

        <!-- พื้นที่ Content หลัก -->
        <div class="col p-4 flex-grow-1" style="font-family: 'Prompt', sans-serif;">

            <!-- 🟢 [ปรับปรุง] Header ปรับดีไซน์ช่องค้นหาและปุ่มให้โค้งมนดูทันสมัย -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bolder mb-1 text-dark">รายการคำขอของฉัน</h3>

                </div>

                <div class="d-flex gap-3 align-items-center">
                    <!-- ช่องค้นหา (Search) -->
                    <div class="input-group shadow-sm" style="width: 250px;">
                        <span class="input-group-text bg-white border-0 rounded-start-pill text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="searchRequestInput" class="form-control border-0 rounded-end-pill shadow-none" placeholder="ค้นหาเลขที่, สถานะ...">
                    </div>

                    <a href="index.php?page=create_request" class="btn btn-warning rounded-pill fw-bold px-4 py-2 shadow-sm border-0 d-flex align-items-center gap-2">
                        <i class="bi bi-plus-circle-fill fs-5"></i> <span>สร้างคำขอใหม่</span>
                    </a>
                </div>
            </div>

            <!-- 🟢 [ปรับปรุง] การ์ดหุ้มตาราง เปลี่ยนเป็นขอบมน (rounded-4) ไร้เส้นขอบดำ และมีแถบสีเหลืองด้านบน (border-top) แบบภาพอ้างอิง -->
            <div class="card border-0 rounded-4 shadow-sm mb-5 border-warning border-top border-4">

                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center rounded-top-4">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-warning"></i>ประวัติคำขอทั้งหมด</h5>
                </div>

                <div class="card-body p-0 table-responsive">
                    <!-- 🟢 [ปรับปรุง] ใช้ CSS Counter เพื่อนับลำดับแถว (1,2,3...) อัตโนมัติ โดยข้ามแถวที่ถูกซ่อน (d-none) ไว้ -->
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

                    <!-- 🟢 [ปรับปรุง] ตารางเอาขอบดำหนาออก ใช้สีเทาอ่อนแบ่งแถวแทน -->
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
                                    // 🟢 [ปรับปรุง] ป้ายสถานะเปลี่ยนเป็นขอบมน (rounded-pill) ให้เข้ากับ Theme ใหม่
                                    $badgeClass = 'rounded-pill px-4 py-2 fw-bold shadow-sm ';

                                    if ($status === 'รออนุมัติ') {
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
                                    <!-- 🟢 [ปรับปรุง] เพิ่ม Class `req-row` และ `data-*` ไว้ให้ JavaScript ใช้ค้นหาข้อมูล -->
                                    <tr class="req-row"
                                        data-reqid="<?= htmlspecialchars(strtolower($req['request_id'])) ?>"
                                        data-status="<?= htmlspecialchars(strtolower($status)) ?>">

                                        <!-- ลำดับ CSS Counter -->
                                        <td class="fw-bold text-muted py-3 border-light"><span class="row-number bg-light rounded-circle d-inline-flex justify-content-center align-items-center" style="width: 30px; height: 30px;"></span></td>

                                        <td class="fw-bold border-light">
                                            <span class="px-2 py-1 bg-light rounded-3 border text-dark">
                                                <?= htmlspecialchars($req['request_id']) ?>
                                            </span>
                                        </td>
                                        <td class="border-light text-muted"><?= date('d/m/Y', strtotime($req['request_date'])) ?></td>
                                        <td class="border-light text-muted"><?= $req['approved_at'] ? date('d/m/Y', strtotime($req['approved_at'])) : '-' ?></td>
                                        <td class="border-light">
                                            <span class="badge fs-6 fw-bold px-4 py-2 <?= $badgeClass ?>">
                                                <?= htmlspecialchars($status) ?>
                                            </span>
                                        </td>
                                        <td class="border-light">
                                            <!-- ปุ่มดูรายละเอียด ปรับเป็นวงกลม -->
                                            <a href="index.php?page=request_view&id=<?= $req['request_id'] ?>" class="btn btn-sm btn-light rounded-circle shadow-sm text-info d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="ดูรายละเอียด">
                                                <i class="bi bi-search"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- แถวกรณีค้นหาไม่เจอ (ถูกซ่อนไว้โดย JS ในตอนแรก) -->
                            <tr id="noDataSearchRow" class="d-none">
                                <td colspan="6" class="text-center py-5 text-muted border-0">
                                    <h6 class="fw-bold mb-0">ไม่พบรายการที่ค้นหา</h6>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 🟢 [ปรับปรุง] ส่วนแสดงผลปุ่ม Pagination -->
                <div class="px-4 pb-4 pt-2">
                    <?php include 'includes/pagination.php'; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- 🟢 [ปรับปรุง] โค้ด JavaScript สำหรับทำระบบ ค้นหา และ แบ่งหน้า (Pagination) ภายในหน้าเดียว -->
<script>
    const reqPerPage = 5; // กำหนดจำนวนรายการต่อ 1 หน้า
    let reqCurrentPage = 1;
    let allReqRows = [];
    let filteredReqRows = [];

    // เมื่อหน้าเว็บโหลดเสร็จ ให้เริ่มทำงาน
    window.addEventListener('DOMContentLoaded', () => {
        allReqRows = Array.from(document.querySelectorAll('.req-row')); // ดึงข้อมูลทุกแถวเก็บไว้ใน Array

        // ดักจับการพิมพ์ในช่องค้นหา
        const searchInput = document.getElementById('searchRequestInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', filterRequests);
        }

        filterRequests(); // รันครั้งแรกเพื่อจัดหน้าและแบ่งหน้า
    });

    // ฟังก์ชันกรองข้อมูล (ค้นหา)
    function filterRequests() {
        const searchText = document.getElementById('searchRequestInput').value.toLowerCase().trim();

        // คัดกรองข้อมูลเฉพาะแถวที่มี เลขคำขอ หรือ สถานะ ตรงกับที่พิมพ์
        filteredReqRows = allReqRows.filter(row => {
            const reqId = row.getAttribute('data-reqid') || '';
            const status = row.getAttribute('data-status') || '';
            return reqId.includes(searchText) || status.includes(searchText);
        });

        // ควบคุมการแสดงข้อความ "ไม่พบข้อมูล"
        const noDataRow = document.getElementById('noDataSearchRow');
        if (noDataRow) {
            if (filteredReqRows.length === 0 && allReqRows.length > 0) {
                noDataRow.classList.remove('d-none');
            } else {
                noDataRow.classList.add('d-none');
            }
        }

        reqCurrentPage = 1; // เมื่อค้นหาใหม่ ให้เด้งกลับไปหน้า 1 เสมอ
        renderReqPagination();
    }

    // ฟังก์ชันคำนวณและแสดงผลแถวในตารางตามหน้าปัจจุบัน
    function renderReqPagination() {
        // ซ่อนทุกแถวก่อน (ใช้คลาส d-none ของ Bootstrap)
        allReqRows.forEach(row => row.classList.add('d-none'));

        const totalItems = filteredReqRows.length;
        let totalPages = Math.ceil(totalItems / reqPerPage);
        if (totalPages === 0) totalPages = 1;

        // คำนวณจุดเริ่มต้นและจุดสิ้นสุดของข้อมูลในหน้านั้นๆ
        const startIndex = (reqCurrentPage - 1) * reqPerPage;
        const endIndex = startIndex + reqPerPage;

        // ถอดคลาส d-none ออก เฉพาะแถวที่ต้องแสดงในหน้านี้
        for (let i = startIndex; i < endIndex && i < totalItems; i++) {
            filteredReqRows[i].classList.remove('d-none');
        }

        drawReqPagination(totalPages); // วาดปุ่มตัวเลข
    }

    // ฟังก์ชันสร้างปุ่มตัวเลข Pagination ด้านล่าง
    function drawReqPagination(totalPages) {
        const container = document.getElementById('requestPaginationControls');

        if (filteredReqRows.length === 0 || totalPages <= 1) {
            container.innerHTML = ''; // ถ้ามีหน้าเดียว หรือค้นหาไม่เจอ ไม่ต้องโชว์ปุ่ม
            return;
        }

        let html = '<ul class="pagination pagination-sm mb-0 shadow-sm">';

        // ปุ่มก่อนหน้า
        html += `<li class="page-item ${reqCurrentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link text-dark fw-bold bg-light border-light" href="#" onclick="changeReqPage(event, ${reqCurrentPage - 1})">&laquo;</a>
                 </li>`;

        // วนลูปสร้างปุ่มตัวเลข
        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${reqCurrentPage === i ? 'active' : ''}">
                        <a class="page-link ${reqCurrentPage === i ? 'bg-warning border-warning text-dark' : 'text-dark bg-white border-light'}" 
                           href="#" onclick="changeReqPage(event, ${i})">${i}</a>
                     </li>`;
        }

        // ปุ่มถัดไป
        html += `<li class="page-item ${reqCurrentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link text-dark fw-bold bg-light border-light" href="#" onclick="changeReqPage(event, ${reqCurrentPage + 1})">&raquo;</a>
                 </li>`;

        html += '</ul>';
        container.innerHTML = html;
    }

    // ฟังก์ชันสำหรับเปลี่ยนหน้า
    function changeReqPage(event, newPage) {
        event.preventDefault(); // ป้องกันการรีเฟรชหน้าเว็บ
        reqCurrentPage = newPage;
        renderReqPagination(); // อัปเดตตาราง
    }
</script>

<?php include 'includes/footer.php'; ?>