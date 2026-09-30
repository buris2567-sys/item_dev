<?php
if (!defined('APP_RUNNING')) exit('Forbidden');
// ตรวจสอบสิทธิ์ Admin (เขียนไว้ใน index.php หรือบนสุดของไฟล์)

// ดึงข้อมูลคำร้องทั้งหมดเรียงจากล่าสุด[cite: 31]
$requests = $pdo->query("
    SELECT r.request_id, u.username as requester_name, r.request_date, r.approved_at, r.status 
    FROM requests r
    LEFT JOIN users u ON r.user_id = u.user_id
    ORDER BY r.request_date DESC
")->fetchAll();
?>

<div class="p-4 flex-grow-1" style="font-family: 'Prompt', sans-serif;">
    <div class="d-flex justify-content-between align-items-end mb-4 pb-3" style="border-bottom: 2px solid #e9ecef;">
        <div>
            <h3 class="fw-bolder mb-1 text-dark">พิจารณาสถานะคำขอ</h3>
            <span class="text-muted small">ตรวจสอบสถานะและประวัติการเบิกอุปกรณ์[cite: 35]</span>
        </div>
    </div>

    <div class="card border-dark rounded-0 mb-5 shadow-sm" style="border-width: 2px !important;">
        <div class="card-header bg-white border-dark py-3 px-4" style="border-bottom-width: 2px !important;">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-list-task me-2"></i>คำขอทั้งหมด[cite: 35]</h6>
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
                        // จัดการสีป้ายสถานะ[cite: 35]
                        $badgeClass = 'bg-warning text-dark'; // รออนุมัติ
                        if ($req['status'] === 'อนุมัติ') $badgeClass = 'bg-success';
                        elseif ($req['status'] === 'ไม่อนุมัติ') $badgeClass = 'bg-danger';
                        elseif ($req['status'] === 'อนุมัติบางส่วน') $badgeClass = 'bg-warning text-dark'; // สีส้ม/เหลือง
                        elseif ($req['status'] === 'ยกเลิก') $badgeClass = 'bg-secondary';
                    ?>
                        <tr>
                            <td class="fw-bold text-muted"><?= $index + 1 ?></td>
                            <td class="fw-bold"><span class="border border-dark px-2 py-1"><?= htmlspecialchars($req['request_id']) ?></span></td>
                            <td><?= htmlspecialchars($req['requester_name']) ?></td>
                            <td><?= date('d/m/Y', strtotime($req['request_date'])) ?></td>
                            <td><?= $req['approved_at'] ? date('d/m/Y', strtotime($req['approved_at'])) : '-' ?></td>
                            <td>
                                <span class="badge border border-dark rounded-0 px-3 py-2 <?= $badgeClass ?>">
                                    <?= htmlspecialchars($req['status']) ?>
                                </span>
                            </td>
                            <td>
                                <!-- ปุ่มแว่นขยาย พาไปหน้า request_view.php พร้อมส่ง id ไปทาง URL[cite: 35] -->
                                <a href="index.php?page=request_view&id=<?= $req['request_id'] ?>" class="btn btn-sm btn-info rounded-0 border-dark shadow-sm">
                                    <i class="bi bi-search text-white"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>