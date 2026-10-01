<?php
if (!defined('APP_RUNNING')) exit('Forbidden');

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    exit('Unauthorized access');
}

// ดึงข้อมูลคำร้องทั้งหมดเรียงจากล่าสุด
$requests = $pdo->query("
    SELECT r.request_id, u.username as requester_name, r.request_date, r.approved_at, r.status 
    FROM requests r
    LEFT JOIN users u ON r.user_id = u.user_id
    ORDER BY r.request_date DESC
")->fetchAll();
?>

<div class="p-4 flex-grow-1" style="font-family: 'Prompt', sans-serif;">
    
    <!-- ส่วนหัวหน้าจอ[cite: 26] -->
    <div class="d-flex justify-content-between align-items-end mb-4 pb-3" style="border-bottom: 2px solid #e9ecef;">
        <div>
            <h3 class="fw-bolder mb-1 text-dark">พิจารณาสถานะคำขอ</h3>
            <span class="text-muted small">ตรวจสอบสถานะและประวัติการเบิกอุปกรณ์</span>
        </div>
        
        <!-- แถบค้นหาและตัวกรอง[cite: 26] -->
        <div class="d-flex gap-2">
            <div class="input-group border border-dark rounded-0" style="width: 250px; border-width: 2px !important;">
                <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                <input type="text" id="searchRequest" class="form-control border-0 shadow-none" placeholder="ค้นหาเลขที่ใบเบิก...">
            </div>
            <button class="btn btn-white border-dark rounded-0 fw-bold px-3 d-flex align-items-center" style="border-width: 2px !important;">
                <i class="bi bi-filter-right fs-5 me-1"></i> กรองข้อมูล
            </button>
        </div>
    </div>

    <!-- การ์ดตาราง[cite: 26] -->
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
                    <?php if (empty($requests)): ?>
                        <tr><td colspan="7" class="py-5 text-muted">ไม่พบข้อมูลคำขอ</td></tr>
                    <?php else: ?>
                        <?php foreach ($requests as $index => $req): 
                            // กำหนดสีป้ายสถานะและไอคอนตามภาพตัวอย่าง[cite: 26]
                            $status = $req['status'];
                            $badgeClass = ''; $iconClass = '';
                            
                            if ($status === 'รออนุมัติ' || $status === 'Pending') {
                                $badgeClass = 'bg-warning text-dark'; $iconClass = 'text-warning'; $status = 'รออนุมัติ';
                            } elseif (strpos($status, 'อนุมัติบางส่วน') !== false) {
                                $badgeClass = 'bg-warning text-dark'; $iconClass = 'text-warning'; // สีส้ม/เหลือง
                            } elseif (strpos($status, 'ไม่อนุมัติ') !== false) {
                                $badgeClass = 'bg-danger text-white'; $iconClass = 'text-danger';
                            } elseif (strpos($status, 'อนุมัติ') !== false) {
                                $badgeClass = 'bg-success text-white'; $iconClass = 'text-success';
                            } else {
                                $badgeClass = 'bg-secondary text-white'; $iconClass = 'text-secondary';
                            }
                        ?>
                            <tr style="border-bottom: 1px solid #f1f3f5;">
                                <td class="fw-bold text-muted"><?= $index + 1 ?></td>
                                <td class="fw-bold">
                                    <span class="border border-dark px-2 py-1 bg-white" style="border-width: 2px !important;">
                                        <?= htmlspecialchars($req['request_id']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($req['requester_name']) ?></td>
                                <td><?= date('d/m/Y', strtotime($req['request_date'])) ?></td>
                                <td><?= $req['approved_at'] ? date('d/m/Y', strtotime($req['approved_at'])) : '-' ?></td>
                                <td>
                                    <!-- ป้ายสถานะขอบดำหนา[cite: 26] -->
                                    <span class="badge border border-dark rounded-0 px-3 py-2 <?= $badgeClass ?>" style="border-width: 2px !important; min-width: 120px;">
                                        <i class="bi bi-circle-fill small me-1 <?= $badgeClass === 'bg-warning text-dark' && $status === 'รออนุมัติ' ? 'text-warning' : 'text-white' ?>" style="filter: brightness(1.2); text-shadow: 0 0 2px black;"></i> 
                                        <?= htmlspecialchars($status) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <!-- ปุ่มแว่นขยาย พาไปหน้าพิจารณาอนุมัติ[cite: 26] -->
                                        <a href="index.php?page=request_view&id=<?= $req['request_id'] ?>" class="btn btn-sm btn-info rounded-0 border-dark d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-width: 2px !important;" title="พิจารณาคำขอ">
                                            <i class="bi bi-search text-white"></i>
                                        </a>
                                        
                                        <!-- ปุ่ม Print ปรากฏเมื่อไม่ได้อยู่ในสถานะรออนุมัติ[cite: 26] -->
                                        <?php if ($status !== 'รออนุมัติ'): ?>
                                            <button type="button" class="btn btn-sm btn-dark rounded-0 border-dark d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-width: 2px !important;" title="พิมพ์เอกสาร">
                                                <i class="bi bi-printer"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- แถบ Pagination ด้านล่าง[cite: 26] -->
        <div class="card-footer bg-white border-top-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <span class="text-muted small fw-bold">แสดง <?= count($requests) > 0 ? '1-'.count($requests) : '0' ?> จาก <?= count($requests) ?> รายการ</span>
            <!-- (ใส่โค้ด Include Pagination ของคุณตรงนี้ได้เลย) -->
        </div>
    </div>
</div>