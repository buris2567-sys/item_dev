<?php
if (!defined('APP_RUNNING')) exit('Forbidden');
$request_id =$_GET['id'] ?? '';

// 1. ดึงข้อมูลทั่วไปของคำร้อง[cite: 31, 36]
$stmt =$pdo->prepare("
    SELECT r.*, u.full_name, u.phone_number, d.department_name 
    FROM requests r 
    LEFT JOIN users u ON r.user_id = u.user_id 
    LEFT JOIN departments d ON u.department_id = d.department_id
    WHERE r.request_id = ?
");
$stmt->execute([$request_id]);
$request =$stmt->fetch();

// 2. ดึงรายการสิ่งของในคำร้องนี้[cite: 31, 36]
$itemStmt =$pdo->prepare("
    SELECT ri.*, i.name, i.current_stock, i.images 
    FROM request_items ri 
    JOIN items i ON ri.item_id = i.item_id 
    WHERE ri.request_id = ?
");
$itemStmt->execute([$request_id]);
$items =$itemStmt->fetchAll();
?>

<div class="p-4 flex-grow-1" style="font-family: 'Prompt', sans-serif;">
    
    <form action="actions/admin/request_approve.php" method="POST" id="approvalForm">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <div class="d-flex justify-content-between align-items-center mb-4 pb-2">
            <h3 class="fw-bolder bg-warning border border-dark px-3 py-2 d-inline-block">พิจารณาอนุมัติคำขอเบิกสิ่งของ[cite: 36]</h3>
            <span class="badge bg-warning text-dark border border-dark px-4 py-2 fs-6 rounded-0" id="headerStatusBadge">รออนุมัติ</span>
        </div>

        <!-- 📦 ส่วนที่ 1: ข้อมูลทั่วไป[cite: 36] -->
        <div class="card border-dark rounded-0 mb-4" style="border-width: 2px !important;">
            <div class="card-header bg-light border-dark py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-person-fill text-primary me-2"></i>ข้อมูลคำขอเบิก</h6>
                <span class="border border-dark px-2 py-1 bg-white small fw-bold">ID: <?= htmlspecialchars($request_id) ?></span>
            </div>
            <div class="card-body bg-light p-4">
                <div class="row bg-white border border-dark p-3 mb-3 mx-0">
                    <div class="col-md-6">
                        <p class="mb-1 text-muted small">ผู้เบิก: <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($request['full_name']) ?></span></p>
                        <p class="mb-0 text-muted small">เบอร์โทรศัพท์: <span class="fw-bold text-dark"><?= htmlspecialchars($request['phone_number']) ?></span></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 text-muted small">วันที่ส่งคำขอ: <span class="fw-bold text-dark"><?= date('d/m/Y - H:i', strtotime($request['request_date'])) ?></span></p>
                        <p class="mb-0 text-muted small">วันที่ต้องการใช้งาน: <span class="fw-bold text-dark border border-dark px-2 py-1 bg-warning bg-opacity-25"><?= date('d/m/Y', strtotime($request['use_date'])) ?></span></p>
                    </div>
                </div>
                <div class="bg-white border border-dark p-3 mx-0">
                    <p class="text-muted small mb-1">ชื่องาน/โครงการ:</p>
                    <h6 class="fw-bold mb-3"><?= htmlspecialchars($request['event_name']) ?></h6>
                    <p class="text-muted small mb-1">สถานที่นำไปใช้: <span class="fw-bold text-dark"><?= htmlspecialchars($request['location']) ?></span></p>
                    <p class="text-muted small mb-1">วัตถุประสงค์: <span class="fw-bold text-dark"><?= nl2br(htmlspecialchars($request['purpose'])) ?></span></p>
                    <p class="text-muted small mb-0 mt-3">หมายเหตุ: <br><span class="text-dark"><?= nl2br(htmlspecialchars($request['user_note'] ?? '-')) ?></span></p>
                </div>
            </div>
        </div>

        <!-- 📦 ส่วนที่ 2: รายการสิ่งของและฟอร์มปรับจำนวนอนุมัติ[cite: 36, 37] -->
        <h6 class="fw-bold mb-3"><i class="bi bi-box-seam text-warning me-2"></i>รายการสิ่งของที่ขอเบิก (พิจารณาอนุมัติ) <span class="float-end border border-dark px-2 bg-light"><?= count($items) ?> รายการ</span></h6>
        
        <div class="mb-4">
            <?php foreach ($items as$index => $item):$images = json_decode($item['images'], true);$imgSrc = !empty($images) ? 'assets/uploads/items/' .$images[0] : 'assets/images/placeholder.jpg';
                $reqQty = (int)$item['requested_qty'];
                $curStock = (int)$item['current_stock'];
                // ค่าตั้งต้นให้ช่องอนุมัติเท่ากับจำนวนที่ขอ (แต่ไม่เกินสต็อก)
                $defaultApprove = min($reqQty,$curStock); 
            ?>
            <div class="card border-dark rounded-0 mb-2" style="border-width: 2px !important;">
                <div class="card-body p-3 d-flex align-items-center bg-white">
                    <img src="<?= $imgSrc ?>" class="border border-dark p-1 me-3" style="width: 70px; height: 70px; object-fit: cover;">
                    <div class="flex-grow-1">
                        <span class="badge bg-dark rounded-0 mb-1">ของที่ระลึก</span>
                        <h6 class="fw-bolder mb-0"><?= htmlspecialchars($item['name']) ?></h6>
                    </div>
                    
                    <div class="text-center px-4 border-end border-secondary border-opacity-25">
                        <span class="d-block text-muted small">คงเหลือ</span>
                        <span class="fw-bolder text-success fs-5"><?= $curStock ?></span>
                        <span class="d-block text-muted" style="font-size: 0.7rem;">ชิ้นในคลัง</span>
                    </div>

                    <div class="text-center px-4 border-end border-secondary border-opacity-25">
                        <span class="d-block text-muted small">จำนวนที่ขอ</span>
                        <span class="fw-bolder text-warning fs-5" id="req_qty_<?= $index ?>"><?= $reqQty ?></span>
                        <span class="d-block text-muted" style="font-size: 0.7rem;">ตามใบคำขอ</span>
                    </div>

                    <div class="d-flex align-items-center px-4">
                        <span class="fw-bold me-2">อนุมัติ:</span>
                        <div class="input-group input-group-sm border border-dark" style="width: 110px;">
                            <button type="button" class="btn btn-light border-end border-dark fw-bold px-2" onclick="adjustApprove(<?= $index ?>, -1)">-</button>
                            
                            <!-- ช่องกรอกจำนวนอนุมัติ[cite: 36] -->
                            <input type="text" name="approved_qty[<?= $item['request_item_id'] ?>]" id="approve_input_<?= $index ?>" 
                                class="form-control text-center fw-bolder fs-6 approve-input" 
                                value="<?= $defaultApprove ?>" 
                                data-req="<?= $reqQty ?>" 
                                readonly>
                            
                            <button type="button" class="btn btn-light border-start border-dark fw-bold px-2" onclick="adjustApprove(<?= $index ?>, 1)">+</button>
                        </div>
                    </div>

                    <!-- ป้ายสถานะของแต่ละรายการ[cite: 36, 37] -->
                    <div style="width: 120px;" class="text-end">
                        <span class="badge border border-dark rounded-pill py-2 w-100 item-status-badge" id="item_badge_<?= $index ?>">
                            <i class="bi bi-circle-fill small me-1"></i> <span id="item_text_<?= $index ?>"></span>
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- 📦 ส่วนที่ 3: สรุปผลและปุ่มยืนยัน[cite: 37] -->
        <div class="card border-dark rounded-0 mb-4 bg-warning bg-opacity-10" style="border-width: 2px !important;">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-4"><i class="bi bi-pencil-square me-2"></i>สรุปผลการพิจารณา (ระบบคำนวณอัตโนมัติ)[cite: 37]</h6>
                
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-dark">
                    <span class="fw-bold me-3">สถานะคำร้องภาพรวม:</span>
                    <span class="badge bg-warning text-dark border border-dark px-3 py-2 fs-6 rounded-0 me-3" id="summaryOverallBadge">รออนุมัติ</span>
                    <span class="fw-bold bg-white border border-dark px-3 py-2" id="summaryTextInfo">(กำลังประมวลผล...)</span>
                </div>

                <div class="mb-3">
                    <label class="fw-bold mb-2">ความคิดเห็นเพิ่มเติม (ไม่บังคับ):[cite: 37]</label>
                    <textarea name="admin_comment" class="form-control border-dark rounded-0" rows="3"></textarea>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-3 mb-5">
            <a href="index.php?page=request_list" class="btn btn-outline-dark fw-bold px-4 py-2 bg-white rounded-0">กลับ</a>
            <button type="submit" class="btn btn-warning border-dark fw-bolder px-5 py-2 shadow-sm rounded-0">
                บันทึกสิ้นสุดกระบวนการ
            </button>
        </div>
    </form>
</div>

<!-- ⚙️ JavaScript ควบคุมการคำนวณสถานะอัตโนมัติ[cite: 37] -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        calculateAllStatuses(); // คำนวณครั้งแรกเมื่อโหลดหน้า
    });

    // ฟังก์ชันปรับตัวเลขด้วยปุ่ม + / -
    function adjustApprove(index, change) {
        let input = document.getElementById(`approve_input_${index}`);
        let reqQty = parseInt(input.getAttribute('data-req'));
        let currentVal = parseInt(input.value) || 0;
        let newVal = currentVal + change;
        
        // กันไม่ให้ต่ำกว่า 0 และไม่ให้เกินจำนวนที่ขอ
        if (newVal < 0) newVal = 0;
        if (newVal > reqQty) newVal = reqQty;
        
        input.value = newVal;
        calculateAllStatuses(); // คำนวณสถานะใหม่ทุกครั้งที่กด
    }

    // ฟังก์ชันคำนวณสถานะแต่ละรายการ และภาพรวม[cite: 37]
    function calculateAllStatuses() {
        let inputs = document.querySelectorAll('.approve-input');
        
        let countFull = 0;
        let countPartial = 0;
        let countReject = 0;
        let totalItems = inputs.length;

        inputs.forEach((input, index) => {
            let approved = parseInt(input.value);
            let requested = parseInt(input.getAttribute('data-req'));
            let badge = document.getElementById(`item_badge_${index}`);
            let textSpan = document.getElementById(`item_text_${index}`);
            let circle = badge.querySelector('i');

            // ล้างคลาสเดิม
            badge.className = 'badge border border-dark rounded-pill py-2 w-100 item-status-badge';
            
            // เช็คเงื่อนไขสถานะแต่ละแถว[cite: 37]
            if (approved === requested) {
                badge.classList.add('bg-success');
                circle.className = 'bi bi-circle-fill text-white small me-1';
                textSpan.innerText = 'อนุมัติเต็ม';
                textSpan.className = 'text-white';
                countFull++;
            } else if (approved === 0) {
                badge.classList.add('bg-danger');
                circle.className = 'bi bi-circle-fill text-white small me-1';
                textSpan.innerText = 'ไม่อนุมัติ';
                textSpan.className = 'text-white';
                countReject++;
            } else {
                badge.classList.add('bg-warning');
                circle.className = 'bi bi-circle-fill text-danger small me-1';
                textSpan.innerText = 'อนุมัติบางส่วน';
                textSpan.className = 'text-dark';
                countPartial++;
            }
        });

        // สรุปสถานะภาพรวมคำร้องด้านล่าง[cite: 37]
        let overallBadge = document.getElementById('summaryOverallBadge');
        let headerBadge = document.getElementById('headerStatusBadge');
        let summaryText = document.getElementById('summaryTextInfo');

        let finalStatusClass = '';
        let finalStatusText = '';

        if (countFull === totalItems) {
            finalStatusClass = 'bg-success text-white';
            finalStatusText = 'อนุมัติ';
        } else if (countReject === totalItems) {
            finalStatusClass = 'bg-danger text-white';
            finalStatusText = 'ไม่อนุมัติ';
        } else {
            // ถ้ามีการผสมกัน หรือเป็นอนุมัติบางส่วนทั้งหมด
            finalStatusClass = 'bg-warning text-dark';
            finalStatusText = 'อนุมัติบางส่วน';
        }

        // อัปเดต UI ภาพรวม
        overallBadge.className = `badge border border-dark px-3 py-2 fs-6 rounded-0 me-3 ${finalStatusClass}`;
        overallBadge.innerText = finalStatusText;
        
        headerBadge.className = `badge border border-dark px-4 py-2 fs-6 rounded-0 ${finalStatusClass}`;
        headerBadge.innerText = finalStatusText;

        // อัปเดตข้อความสรุปการคำนวณ[cite: 37]
        summaryText.innerText = `(อนุมัติเต็ม ${countFull} รายการ, บางส่วน ${countPartial} รายการ, ไม่อนุมัติ ${countReject} รายการ)`;
    }
</script>