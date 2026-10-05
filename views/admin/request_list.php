<?php
// 🟢 [คงเดิม] ป้องกันการเข้าไฟล์โดยตรง และตรวจสอบสิทธิ์ Admin
if (!defined('APP_RUNNING')) exit('Forbidden');
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') exit('Unauthorized');

// 🟢 [คงเดิม] ดึงข้อมูลคำขอทั้งหมดจากฐานข้อมูล
$requests = $pdo->query("
    SELECT r.request_id, u.username as requester_name, r.request_date, r.approved_at, r.status 
    FROM requests r
    LEFT JOIN users u ON r.user_id = u.user_id
    ORDER BY r.request_date DESC
")->fetchAll();
?>

<!-- 🟢 [ปรับปรุง] เริ่มต้นเนื้อหาเลย ตัด div หุ้ม Layout (container, row, col) และ include sidebar ทิ้งไป เพราะ index.php จัดการให้แล้ว -->

<!-- หัวข้อ และ ส่วนค้นหา/กรองข้อมูล -->
<div class="d-flex justify-content-between align-items-center mb-4 pb-2" style="border-bottom: 2px solid #e9ecef;">
    <div>
        <h3 class="fw-bolder mb-1 text-dark"><i class="bi bi-boxes text-warning me-2"></i>รายการคำขอ</h3>
          <span class="text-muted small">ระบบสิ่งพิมพ์และของที่ระลึก</span>    
    </div>
</div>

<!-- 🟢 [ปรับปรุง] Card ตารางรายการคำขอ เปลี่ยนเป็นขอบมน (rounded-4), ไร้ขอบดำ (border-0), มีเงา (shadow-sm) และขอบบนสีเหลือง -->
<div class="card border-0 rounded-4 shadow-sm mb-5 border-warning border-top border-4 bg-white">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center rounded-top-4">
        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-list-task me-2 text-warning"></i>คำขอทั้งหมด</h5>
    </div>
    
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
                        // 🟢 [คงเดิม] โลจิกเช็คสถานะ แต่อัปเดตคลาสให้เป็นป้ายแบบขอบมน (rounded-pill) เข้ากับ Theme
                        $status = $req['status'];
                        $badgeClass = 'rounded-pill px-4 py-2 fw-bold shadow-sm '; 

                        if ($status === 'รออนุมัติ' || $status === 'Pending') {
                            $badgeClass .= 'bg-warning text-dark'; $status = 'รออนุมัติ';
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
                        <tr style="border-bottom: 1px solid #f8f9fa;">
                            <td class="fw-bold text-muted py-3"><?= $index + 1 ?></td>
                            <td class="fw-bold">
                                <span class="px-2 py-1 bg-light rounded-3 border text-dark">
                                    <?= htmlspecialchars($req['request_id']) ?>
                                </span>
                            </td>
                            <td class="text-dark fw-semibold"><?= htmlspecialchars($req['requester_name']) ?></td>
                            <td class="text-muted"><?= date('d/m/Y', strtotime($req['request_date'])) ?></td>
                            <td class="text-muted"><?= $req['approved_at'] ? date('d/m/Y', strtotime($req['approved_at'])) : '-' ?></td>
                            <td>
                                <!-- 🟢 [ปรับปรุง] แสดงป้ายสถานะ -->
                                <span class="badge fs-6 fw-bold px-4 py-2 <?= $badgeClass ?>" style="min-width: 120px;">
                                    <i class="bi bi-circle-fill small me-1" style="font-size: 0.55rem;"></i> <?= htmlspecialchars($status) ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <!-- 🟢 [ปรับปรุง] ปุ่มจัดการ ปรับเป็นปุ่มวงกลมมีเงา ดูสะอาดตาขึ้น -->
                                    <a href="index.php?page=request_view&id=<?= $req['request_id'] ?>" class="btn btn-sm btn-light rounded-circle shadow-sm text-info d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="พิจารณา/ดูรายละเอียด">
                                        <i class="bi bi-search"></i>
                                    </a>
                                    <?php if ($status !== 'รออนุมัติ'): ?>
                                        <button class="btn btn-sm btn-light rounded-circle shadow-sm text-dark d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="พิมพ์เอกสาร">
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
    
    <!-- 🟢 [ปรับปรุง] Footer ของ Card -->
    <div class="card-footer bg-white border-0 py-3 px-4 text-muted small fw-bold rounded-bottom-4 d-flex justify-content-end">
        แสดง 1-<?= count($requests) ?> จาก <?= count($requests) ?> รายการ
    </div>
</div>