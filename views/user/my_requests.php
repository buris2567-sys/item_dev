<?php
if (!defined('APP_RUNNING')) exit('Forbidden');

$user_id = $_SESSION['user_id'] ?? 0;

// ดึงข้อมูลคำร้องเฉพาะของ User คนนี้
$stmt = $pdo->prepare("
    SELECT request_id, request_date, approved_at, status 
    FROM requests 
    WHERE user_id = ? 
    ORDER BY request_date DESC
");
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll();
?>

<div class="p-4 flex-grow-1" style="font-family: 'Prompt', sans-serif;">
    
    <div class="d-flex justify-content-between align-items-end mb-4 pb-3" style="border-bottom: 2px solid #e9ecef;">
        <div>
            <h3 class="fw-bolder mb-1 text-dark">รายการคำขอของฉัน</h3>
            <span class="text-muted small">ติดตามสถานะและประวัติการเบิกอุปกรณ์ของคุณ</span>
        </div>
        
        <!-- ปุ่มสร้างคำขอใหม่ฝั่ง User -->
        <a href="index.php?page=create_request" class="btn btn-warning rounded-pill fw-bold px-4 py-2 shadow-sm border-0 d-flex align-items-center gap-2">
            <i class="bi bi-plus-circle-fill fs-5"></i> <span>สร้างคำขอใหม่</span>
        </a>
    </div>

    <!-- การ์ดตารางประวัติ -->
    <div class="card border-dark rounded-0 mb-5 shadow-sm" style="border-width: 3px !important;">
        <div class="card-header bg-white border-dark py-3 px-4 d-flex align-items-center" style="border-bottom-width: 2px !important;">
            <i class="bi bi-clock-history fs-5 me-2"></i>
            <h6 class="fw-bold mb-0 text-dark">ประวัติคำขอทั้งหมด</h6>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle text-center mb-0 custom-striped">
                <thead class="text-black">
                    <tr style="border-bottom: 2px solid #adb5bd;">
                        <th class="fw-bolder py-3 border-0">ลำดับ</th>
                        <th class="fw-bolder py-3 border-0">หมายเลขคำขอ</th>
                        <th class="fw-bolder py-3 border-0">วันที่ขอ</th>
                        <th class="fw-bolder py-3 border-0">วันที่อนุมัติ</th>
                        <th class="fw-bolder py-3 border-0">สถานะ</th>
                        <th class="fw-bolder py-3 border-0">รายละเอียด</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr><td colspan="6" class="py-5 text-muted">คุณยังไม่มีประวัติการส่งคำขอ</td></tr>
                    <?php else: ?>
                        <?php foreach ($requests as $index => $req): 
                            $status = $req['status'];
                            $badgeClass = ''; 
                            
                            if ($status === 'รออนุมัติ' || $status === 'Pending') {
                                $badgeClass = 'bg-warning text-dark'; $status = 'รออนุมัติ';
                            } elseif (strpos($status, 'อนุมัติบางส่วน') !== false) {
                                $badgeClass = 'bg-warning text-dark'; 
                            } elseif (strpos($status, 'ไม่อนุมัติ') !== false) {
                                $badgeClass = 'bg-danger text-white'; 
                            } elseif (strpos($status, 'อนุมัติ') !== false) {
                                $badgeClass = 'bg-success text-white'; 
                            } else {
                                $badgeClass = 'bg-secondary text-white'; 
                            }
                        ?>
                            <tr style="border-bottom: 1px solid #f1f3f5;">
                                <td class="fw-bold text-muted"><?= $index + 1 ?></td>
                                <td class="fw-bold">
                                    <span class="border border-dark px-2 py-1 bg-white" style="border-width: 2px !important;">
                                        <?= htmlspecialchars($req['request_id']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($req['request_date'])) ?></td>
                                <td><?= $req['approved_at'] ? date('d/m/Y', strtotime($req['approved_at'])) : '-' ?></td>
                                <td>
                                    <span class="badge border border-dark rounded-0 px-3 py-2 <?= $badgeClass ?>" style="border-width: 2px !important; min-width: 120px;">
                                        <?= htmlspecialchars($status) ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- ปุ่มแว่นขยายสำหรับ User พาไปหน้าดูรายละเอียดแบบอ่านอย่างเดียว -->
                                    <a href="index.php?page=request_view&id=<?= $req['request_id'] ?>" class="btn btn-sm btn-info rounded-0 border-dark d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-width: 2px !important;" title="ดูรายละเอียด">
                                        <i class="bi bi-search text-white"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>