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
    <!-- ... (แสดงข้อมูลทั่วไปของผู้เบิก) ... -->

    <h6 class="fw-bold mb-3"><i class="bi bi-box-seam text-warning me-2"></i>รายการสิ่งของที่ขอเบิก</h6>
    <div class="mb-4">
        <?php foreach ($items as $index => $item): 
            $reqQty = (int)$item['requested_qty'];
            $appQty = (int)$item['approved_qty'];
            
            // กำหนดสถานะแสดงผลล่วงหน้าด้วย PHP เพราะไม่มี JS ช่วยคำนวณแล้ว[cite: 51]
            $statusText = 'รออนุมัติ'; $badgeClass = 'bg-warning text-dark';
            if ($request['status'] !== 'รออนุมัติ') {
                if ($appQty === $reqQty) { $statusText = 'อนุมัติเต็ม'; $badgeClass = 'bg-success text-white'; }
                elseif ($appQty === 0) { $statusText = 'ไม่อนุมัติ'; $badgeClass = 'bg-danger text-white'; }
                else { $statusText = 'อนุมัติบางส่วน'; $badgeClass = 'bg-warning text-dark'; }
            }
        ?>
        <div class="card border-dark rounded-0 mb-2">
            <div class="card-body p-3 d-flex align-items-center bg-white">
                <div class="flex-grow-1">
                    <h6 class="fw-bolder mb-0"><?= htmlspecialchars($item['name']) ?></h6>
                </div>
                
                <div class="text-center px-4 border-end">
                    <span class="d-block text-muted small">จำนวนที่ขอ</span>
                    <span class="fw-bolder text-warning fs-5"><?= $reqQty ?></span>
                </div>

                <!-- 🟢 สำหรับ User แสดงจำนวนที่ได้รับอนุมัติเป็นข้อความธรรมดา (ไม่มีปุ่ม +/-)[cite: 51] -->
                <div class="text-center px-4 border-end">
                    <span class="d-block text-muted small">จำนวนที่อนุมัติ</span>
                    <span class="fw-bolder text-success fs-5"><?= $request['status'] === 'รออนุมัติ' ? '-' : $appQty ?></span>
                </div>

                <div style="width: 120px;" class="text-end ps-3">
                    <span class="badge border border-dark rounded-pill py-2 w-100 <?= $badgeClass ?>">
                        <?= $statusText ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- สรุปผล -->
    <div class="card border-dark rounded-0 mb-4 bg-warning bg-opacity-10">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-4">สรุปผลการพิจารณา</h6>
            <div class="mb-3">
                <label class="fw-bold mb-2">สถานะคำร้องภาพรวม:</label>
                <span class="badge border border-dark px-3 py-2 fs-6 rounded-0 bg-warning text-dark"><?= htmlspecialchars($request['status']) ?></span>
            </div>
            <div class="mb-3">
                <label class="fw-bold mb-2">ความคิดเห็นจากเจ้าหน้าที่ :</label>
                <!-- 🟢 สำหรับ User เป็นกล่องข้อความอ่านอย่างเดียว -->
                <div class="p-3 bg-white border border-dark" style="min-height: 80px;">
                    <?= nl2br(htmlspecialchars($request['admin_comment'] ?? 'ไม่มีความเห็นเพิ่มเติม')) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 🟢 สำหรับ User มีแค่ปุ่มกลับ[cite: 51] -->
    <div class="d-flex justify-content-end mb-5">
        <a href="index.php?page=my_requests" class="btn btn-outline-dark fw-bold px-5 py-2 bg-white rounded-0">[ กลับ ]</a>
    </div>
</div>