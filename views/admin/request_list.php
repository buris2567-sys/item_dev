<?php
if (!defined('APP_RUNNING')) exit('Forbidden');
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') exit('Unauthorized');

$requests = $pdo->query("
    SELECT r.request_id, u.username as requester_name, r.request_date, r.approved_at, r.status 
    FROM requests r
    LEFT JOIN users u ON r.user_id = u.user_id
    ORDER BY r.request_date DESC
")->fetchAll();
?>

<!-- เพิ่ม overflow-x-hidden เพื่อล็อกไม่ให้หน้าจอล้นขอบขวา -->
<div class="container-fluid p-0 overflow-x-hidden">
    <div class="row g-0 flex-nowrap">

        <!-- ดึง Sidebar สีเข้มมาแสดง -->
        <?php include 'includes/sidebar_admin.php'; ?>

        <!-- ฝั่งเนื้อหาขวา: ใช้ col เพื่อกินพื้นที่ที่เหลือ และใส่ min-width: 0 ล็อกขนาดไม่ให้ตารางดันจนล้นจอ -->
        <div class="col p-4 flex-grow-1 d-flex flex-column" style="min-width: 0; min-height: 100vh; background-color: #f5f6f8; font-family: 'Prompt', sans-serif;">
            
            <!-- หัวข้อ และ ส่วนค้นหา/กรองข้อมูล -->
            <div class="d-flex justify-content-between align-items-end mb-4 pb-3" style="border-bottom: 2px solid #e9ecef;">
                <div>
                    <h3 class="fw-bolder mb-1 text-dark">พิจารณาสถานะคำขอ</h3>
                    <span class="text-muted small">ตรวจสอบสถานะและประวัติการเบิกอุปกรณ์</span>
                </div>
                <div class="d-flex gap-2">
                    <div class="input-group border border-dark rounded-0" style="width: 250px; border-width: 2px !important;">
                        <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-0 shadow-none" placeholder="ค้นหาเลขที่ใบเบิก...">
                    </div>
                    <button class="btn btn-white border-dark rounded-0 fw-bold px-3" style="border-width: 2px !important;">
                        <i class="bi bi-filter-right fs-5 me-1"></i> กรองข้อมูล
                    </button>
                </div>
            </div>

            <!-- Card ตารางรายการคำขอ -->
            <div class="card border-dark rounded-0 mb-5 shadow-sm" style="border-width: 3px !important;">
                <div class="card-header bg-white border-dark py-3 px-4 d-flex align-items-center" style="border-bottom-width: 2px !important;">
                    <i class="bi bi-list-task fs-5 me-2"></i>
                    <h6 class="fw-bold mb-0 text-dark">คำขอทั้งหมด</h6>
                </div>
                
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle text-center mb-0 custom-striped">
                        <thead class="text-black">
                            <tr style="border-bottom: 2px solid #adb5bd;">
                                <th class="fw-bolder py-3 border-0">ลำดับ</th>
                                <th class="fw-bolder py-3 border-0">หมายเลขคำขอ</th>
                                <th class="fw-bolder py-3 border-0">ผู้ขอ</th>
                                <th class="fw-bolder py-3 border-0">วันที่ขอ</th>
                                <th class="fw-bolder py-3 border-0">วันที่อนุมัติ</th>
                                <th class="fw-bolder py-3 border-0">สถานะ</th>
                                <th class="fw-bolder py-3 border-0">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $index => $req):
                                $status = $req['status'];
                                $badgeStyle = 'border-width: 2px !important; min-width: 120px; ';

                                if ($status === 'รออนุมัติ' || $status === 'Pending') {
                                    $badgeStyle .= 'background-color: #ffc107; color: #000;';
                                    $status = 'รออนุมัติ';
                                } elseif (strpos($status, 'อนุมัติบางส่วน') !== false) {
                                    $badgeStyle .= 'background-color: #fd7e14; color: #fff;';
                                } elseif (strpos($status, 'ไม่อนุมัติ') !== false) {
                                    $badgeStyle .= 'background-color: #dc3545; color: #fff;';
                                } elseif (strpos($status, 'อนุมัติ') !== false) {
                                    $badgeStyle .= 'background-color: #28a745; color: #fff;';
                                } else {
                                    $badgeStyle .= 'background-color: #6c757d; color: #fff;';
                                }
                            ?>
                                <tr style="border-bottom: 1px solid #f1f3f5;">
                                    <td class="fw-bold text-muted"><?= $index + 1 ?></td>
                                    <td class="fw-bold"><span class="border border-dark px-2 py-1 bg-white" style="border-width: 2px !important;"><?= htmlspecialchars($req['request_id']) ?></span></td>
                                    <td><?= htmlspecialchars($req['requester_name']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($req['request_date'])) ?></td>
                                    <td><?= $req['approved_at'] ? date('d/m/Y', strtotime($req['approved_at'])) : '-' ?></td>
                                    <td>
                                        <span class="badge border border-dark rounded-0 px-3 py-2 shadow-sm" style="<?= $badgeStyle ?>">
                                            <i class="bi bi-circle-fill small me-1"></i> <?= htmlspecialchars($status) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="index.php?page=request_view&id=<?= $req['request_id'] ?>" class="btn btn-sm btn-info rounded-0 border-dark d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-width: 2px !important;">
                                                <i class="bi bi-search text-white"></i>
                                            </a>
                                            <?php if ($status !== 'รออนุมัติ'): ?>
                                                <button class="btn btn-sm btn-dark rounded-0 border-dark d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-width: 2px !important;">
                                                    <i class="bi bi-printer"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-top-0 py-3 px-4 text-muted small fw-bold">
                    แสดง 1-<?= count($requests) ?> จาก <?= count($requests) ?> รายการ
                </div>
            </div>

        </div> <!-- ปิดฝั่งเนื้อหาขวา .col -->

    </div> <!-- ปิด .row -->
</div> <!-- ปิด .container-fluid -->