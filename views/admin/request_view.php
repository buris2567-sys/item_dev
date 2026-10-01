<?php
if (!defined('APP_RUNNING')) exit('Forbidden');
$request_id =$_GET['id'] ?? '';

// ดึงข้อมูลคำร้องและผู้เบิก
$stmt =$pdo->prepare("SELECT r.*, u.full_name, u.phone_number FROM requests r JOIN users u ON r.user_id = u.user_id WHERE r.request_id = ?");
$stmt->execute([$request_id]);
$request =$stmt->fetch();

// ดึงรายการสิ่งของในคำร้อง
$itemStmt =$pdo->prepare("SELECT ri.*, i.name, i.current_stock, i.images FROM request_items ri JOIN items i ON ri.item_id = i.item_id WHERE ri.request_id = ?");
$itemStmt->execute([$request_id]);
$items =$itemStmt->fetchAll();

// ตรวจสอบสถานะเพื่อคุมปุ่มและช่องกรอก
$isPending = ($request['status'] === 'รออนุมัติ' || $request['status'] === 'Pending');
?>

<div class="p-4 flex-grow-1" style="font-family: 'Prompt', sans-serif;">
    <form action="actions/admin/request_process.php" method="POST" id="approvalForm">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- ข้อมูลทั่วไป (โค้ดส่วนแสดงผลข้อมูลผู้ขอเบิกตามภาพ) -->
        <!-- ... (ใช้โค้ดแสดงผลข้อมูลผู้เบิก วันที่ ชื่องาน ตามปกติ) ... -->

        <h6 class="fw-bold mb-3"><i class="bi bi-box-seam text-warning me-2"></i>รายการสิ่งของที่ขอเบิก (พิจารณาอนุมัติ)</h6>
        <div class="mb-4">
            <?php foreach ($items as $index =>$item): 
                $reqQty = (int)$item['requested_qty'];
                $curStock = (int)$item['current_stock'];
                // ดึงค่าที่เคยอนุมัติมาแสดง (ถ้ายังรออนุมัติ ให้ตั้งค่าเริ่มต้นเท่ากับจำนวนที่ขอแต่ไม่เกินสต็อก)
                $appQty = $isPending ? min($reqQty, $curStock) : (int)$item['approved_qty'];
            ?>
            <div class="card border-dark rounded-0 mb-2">
                <div class="card-body p-3 d-flex align-items-center bg-white">
                    <div class="flex-grow-1">
                        <h6 class="fw-bolder mb-0"><?= htmlspecialchars($item['name']) ?></h6>
                    </div>
                    
                    <div class="text-center px-4 border-end">
                        <span class="d-block text-muted small">คงเหลือ</span>
                        <span class="fw-bolder text-success fs-5"><?= $curStock ?></span>
                    </div>
                    <div class="text-center px-4 border-end">
                        <span class="d-block text-muted small">จำนวนที่ขอ</span>
                        <span class="fw-bolder text-warning fs-5"><?= $reqQty ?></span>
                    </div>

                    <div class="d-flex align-items-center px-4">
                        <span class="fw-bold me-2">อนุมัติ:</span>
                        <div class="input-group input-group-sm border border-dark" style="width: 110px;">
                            <button type="button" class="btn btn-light border-end border-dark fw-bold px-2" onclick="adjustApprove(<?= $index ?>, -1)" <?= !$isPending ? 'disabled' : '' ?>>-</button>
                            
                            <!-- 🟢 ช่องกรอกจำนวนอนุมัติ (ถ้าอนุมัติแล้วจะ Readonly) -->
                            <input type="text" name="approved_qty[<?= $item['request_item_id'] ?>]" id="approve_input_<?= $index ?>" 
                                class="form-control text-center fw-bolder approve-input" 
                                value="<?= $appQty ?>" data-req="<?= $reqQty ?>" <?= !$isPending ? 'readonly' : '' ?>>
                            
                            <button type="button" class="btn btn-light border-start border-dark fw-bold px-2" onclick="adjustApprove(<?= $index ?>, 1)" <?= !$isPending ? 'disabled' : '' ?>>+</button>
                        </div>
                    </div>

                    <!-- ป้ายสถานะรายชิ้น -->
                    <div style="width: 120px;" class="text-end">
                        <span class="badge border border-dark rounded-pill py-2 w-100 item-status-badge" id="item_badge_<?= $index ?>">
                            <i class="bi bi-circle-fill small me-1"></i> <span id="item_text_<?= $index ?>"></span>
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- สรุปผลพิจารณา -->
        <div class="card border-dark rounded-0 mb-4 bg-warning bg-opacity-10">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-4">สรุปผลการพิจารณา (ระบบคำนวณอัตโนมัติ)</h6>
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-dark">
                    <span class="fw-bold me-3">สถานะคำร้องภาพรวม:</span>
                    <span class="badge border border-dark px-3 py-2 fs-6 rounded-0 me-3" id="summaryOverallBadge">รออนุมัติ</span>
                    <span class="fw-bold bg-white border border-dark px-3 py-2" id="summaryTextInfo"></span>
                </div>
                <div class="mb-3">
                    <label class="fw-bold mb-2">ความคิดเห็นเพิ่มเติม (ไม่บังคับ) :</label>
                    <textarea name="admin_comment" class="form-control border-dark rounded-0" rows="3" <?= !$isPending ? 'readonly' : '' ?>><?= htmlspecialchars($request['admin_comment'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- 🟢 ปุ่มดำเนินการ (แสดงตามสถานะคำร้อง) -->
        <div class="d-flex justify-content-end gap-3 mb-5">
            <a href="index.php?page=request_list" class="btn btn-outline-dark fw-bold px-4 py-2 bg-white rounded-0">[ กลับ ]</a>
            
            <?php if ($isPending): ?>
                <!-- กรณีรออนุมัติ: แสดงปุ่มบันทึก[cite: 48] -->
                <button type="submit" name="action" value="approve" class="btn btn-warning border-dark fw-bolder px-5 py-2 shadow-sm rounded-0">
                    <i class="bi bi-save me-2"></i>บันทึก
                </button>
            <?php else: ?>
                <!-- กรณีอนุมัติไปแล้ว: แสดงปุ่มยกเลิกสีแดง และปุ่ม Export[cite: 49, 50] -->
                <button type="button" class="btn btn-info bg-opacity-25 border-dark fw-bolder px-4 py-2 rounded-0"><i class="bi bi-file-earmark-pdf me-2"></i>Export PDF</button>
                <button type="submit" name="action" value="cancel" class="btn btn-danger border-dark fw-bolder px-5 py-2 shadow-sm rounded-0" onclick="return confirm('ต้องการยกเลิกคำร้องนี้และคืนสต็อกใช่หรือไม่?');">
                    <i class="bi bi-x-circle me-2"></i>ยกเลิก
                </button>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", calculateAllStatuses);

    function adjustApprove(index, change) {
        let input = document.getElementById(`approve_input_${index}`);
        if(input.hasAttribute('readonly')) return;
        let reqQty = parseInt(input.getAttribute('data-req'));
        let currentVal = parseInt(input.value) || 0;
        let newVal = Math.max(0, Math.min(reqQty, currentVal + change));
        input.value = newVal;
        calculateAllStatuses();
    }

    function calculateAllStatuses() {
        let inputs = document.querySelectorAll('.approve-input');
        let counts = { full: 0, partial: 0, reject: 0 };

        inputs.forEach((input, index) => {
            let approved = parseInt(input.value);
            let requested = parseInt(input.getAttribute('data-req'));
            let badge = document.getElementById(`item_badge_${index}`);
            let textSpan = document.getElementById(`item_text_${index}`);
            let circle = badge.querySelector('i');

            badge.className = 'badge border border-dark rounded-pill py-2 w-100 item-status-badge';
            
            // เงื่อนไขสถานะ[cite: 48, 49]
            if (approved === requested) {
                badge.classList.add('bg-success'); circle.className = 'bi bi-circle-fill text-white small me-1'; textSpan.innerText = 'อนุมัติเต็ม'; textSpan.className = 'text-white'; counts.full++;
            } else if (approved === 0) {
                badge.classList.add('bg-danger'); circle.className = 'bi bi-circle-fill text-white small me-1'; textSpan.innerText = 'ไม่อนุมัติ'; textSpan.className = 'text-white'; counts.reject++;
            } else {
                badge.classList.add('bg-warning'); circle.className = 'bi bi-circle-fill text-danger small me-1'; textSpan.innerText = 'อนุมัติบางส่วน'; textSpan.className = 'text-dark'; counts.partial++;
            }
        });

        // สรุปภาพรวม[cite: 48, 49, 50]
        let overallBadge = document.getElementById('summaryOverallBadge');
        if (counts.full === inputs.length) {
            overallBadge.className = 'badge border border-dark px-3 py-2 fs-6 rounded-0 me-3 bg-success text-white'; overallBadge.innerText = 'อนุมัติเต็มจำนวน';
        } else if (counts.reject === inputs.length) {
            overallBadge.className = 'badge border border-dark px-3 py-2 fs-6 rounded-0 me-3 bg-danger text-white'; overallBadge.innerText = 'ไม่อนุมัติ';
        } else {
            overallBadge.className = 'badge border border-dark px-3 py-2 fs-6 rounded-0 me-3 bg-warning text-dark'; overallBadge.innerText = 'อนุมัติบางส่วน';
        }

        document.getElementById('summaryTextInfo').innerText = `(อนุมัติเต็ม ${counts.full} รายการ, บางส่วน ${counts.partial} รายการ, ไม่อนุมัติ ${counts.reject} รายการ)`;
    }
</script>