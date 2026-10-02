<?php
if (!defined('APP_RUNNING')) exit('Forbidden');
$request_id = $_GET['id'] ?? '';

// ดึงข้อมูลคำร้องและผู้เบิก
$stmt = $pdo->prepare("
    SELECT r.*, u.full_name, u.phone_number 
    FROM requests r 
    LEFT JOIN users u ON r.user_id = u.user_id 
    WHERE r.request_id = ?
");
$stmt->execute([$request_id]);
$request = $stmt->fetch();

if (!$request) {
    echo "<div class='p-4 text-center mt-5'><h5>ไม่พบข้อมูลคำขอ</h5><a href='index.php?page=request_list' class='btn btn-dark mt-3'>กลับ</a></div>";
    exit;
}

// ดึงรายการสิ่งของในคำร้อง
$itemStmt = $pdo->prepare("
    SELECT ri.*, i.current_stock 
    FROM request_items ri 
    LEFT JOIN items i ON ri.item_id = i.item_id 
    WHERE ri.request_id = ?
");
$itemStmt->execute([$request_id]);
$items = $itemStmt->fetchAll();

$isPending = ($request['status'] === 'รออนุมัติ' || $request['status'] === 'Pending');

// 🟢 กำหนดสีป้ายสถานะให้สวยงาม
$statusText = $request['status'];
$badgeStyle = 'border-width: 2px !important; min-width: 110px; ';

if ($statusText === 'รออนุมัติ' || $statusText === 'Pending') {
    $badgeStyle .= 'background-color: #ffc107; color: #000;';
    $statusText = 'รออนุมัติ';
} elseif (strpos($statusText, 'อนุมัติบางส่วน') !== false) {
    $badgeStyle .= 'background-color: #fd7e14; color: #fff;';
} elseif (strpos($statusText, 'ไม่อนุมัติ') !== false) {
    $badgeStyle .= 'background-color: #dc3545; color: #fff;';
} elseif (strpos($statusText, 'อนุมัติ') !== false) {
    $badgeStyle .= 'background-color: #28a745; color: #fff;';
} else {
    $badgeStyle .= 'background-color: #6c757d; color: #fff;';
}
?>

<!-- 🟢 1. ครอบ container ป้องกัน Scrollbar แนวนอนโผล่ -->
<div class="container-fluid p-0 overflow-x-hidden">
    <div class="row g-0 flex-nowrap">

        <!-- ดึง Sidebar มาแสดง -->
        <?php include 'includes/sidebar_admin.php'; ?>
<!-- 🟢 2. ให้เลื่อนสกรอลล์ (overflow-y-auto) เฉพาะฝั่งขวา -->
        <div class="col vh-100 overflow-y-auto p-4 flex-grow-1 d-flex flex-column transition-all" style="min-width: 0; background-color: #f5f6f8; font-family: 'Prompt', sans-serif;">
        <!-- 🟢 2. กำหนด col และ min-width: 0 ล็อกขนาดไม่ให้ล้นจอ -->
        <div class="col p-4 flex-grow-1 d-flex flex-column transition-all" style="min-width: 0; min-height: 100vh; background-color: #f5f6f8; font-family: 'Prompt', sans-serif;">

            <form action="actions/admin/request_approve.php" method="POST" id="approvalForm">
                <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

                <!-- หัวข้อหน้า -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2">
                    <h3 class="fw-bolder bg-warning border border-dark px-3 py-2 d-inline-block mb-0">พิจารณาอนุมัติคำขอเบิกสิ่งของ</h3>
                </div>

                <!-- 📦 ส่วนที่ 1: ข้อมูลคำขอเบิก (จัดระเบียบใหม่) -->
                <div class="card border-dark rounded-0 mb-4" style="border-width: 2px !important;">
                    <div class="card-header bg-light border-dark py-3 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0"><i class="bi bi-person-fill text-primary me-2"></i>ข้อมูลคำขอเบิก</h6>
                        <span class="border border-dark px-2 py-1 bg-white small fw-bold">ID: <?= htmlspecialchars($request_id) ?></span>
                    </div>
                    <div class="card-body bg-light p-4">

                        <!-- สรุปข้อมูลผู้เบิก & สถานะ แบบ Grid 4 คอลัมน์สมดุล -->
                        <div class="bg-white border border-dark p-3 mb-3 mx-0">
                            <div class="row g-3 align-items-center">
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="text-muted small">ผู้เบิก</div>
                                    <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($request['full_name'] ?? 'ไม่ทราบชื่อ') ?></div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="text-muted small">เบอร์โทรศัพท์</div>
                                    <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($request['phone_number'] ?? '-') ?></div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="text-muted small">วันที่ส่ง </div>
                                    <div class="fw-bold text-dark small">
                                        <?= date('d/m/Y - H:i', strtotime($request['request_date'])) ?>

                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="text-muted small mb-1">สถานะคำขอ</div>
                                    <span class="badge border border-dark px-3 py-2 fs-6 rounded-0 shadow-sm d-inline-flex align-items-center justify-content-center" style="<?= $badgeStyle ?>" id="headerStatusBadge">
                                        <i class="bi bi-circle-fill small me-1" style="font-size: 0.55rem;"></i>
                                        <?= htmlspecialchars($statusText) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- รายละเอียดโครงการ/งาน แบบบรรทัดเรียงสวยงาม -->
                        <div class="bg-white border border-dark p-3 mx-0">
                            <div class="row g-2">
                                <div class="col-12 border-bottom pb-2">
                                    <span class="text-muted small d-inline-block" style="width: 120px;"> วันที่ใช้งาน</span>
                                    <span class="badge border border-dark text-dark bg-warning bg-opacity-50 " style="font-size: 1rem;"><?= date('d/m/Y', strtotime($request['use_date'])) ?></span>
                                </div>
                                <div class="col-12 border-bottom pb-2">
                                    <span class="text-muted small d-inline-block" style="width: 120px;">ประเภทของงาน:</span>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($request['event_type']) ?></span>
                                </div>
                                <div class="col-12 border-bottom pb-2">
                                    <span class="text-muted small d-inline-block" style="width: 120px;">ชื่องาน/โครงการ:</span>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($request['event_name']) ?></span>
                                </div>
                                <div class="col-12 border-bottom pb-2">
                                    <span class="text-muted small d-inline-block" style="width: 120px;">สถานที่นำไปใช้:</span>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($request['location']) ?></span>
                                </div>
                                <div class="col-12 border-bottom pb-2">
                                    <span class="text-muted small d-inline-block align-top" style="width: 120px;">วัตถุประสงค์:</span>
                                    <span class="fw-bold text-dark d-inline-block" style="max-width: calc(100% - 130px);"><?= nl2br(htmlspecialchars($request['purpose'])) ?></span>
                                </div>
                                <div class="col-12">
                                    <span class="text-muted small d-inline-block align-top" style="width: 120px;">หมายเหตุ:</span>
                                    <span class="text-dark d-inline-block" style="max-width: calc(100% - 130px);"><?= nl2br(htmlspecialchars($request['user_note'] ?: '-')) ?></span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- 📦 ส่วนที่ 2: รายการสิ่งของ -->
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-box-seam text-warning me-2"></i>รายการสิ่งของที่ขอเบิก (พิจารณาอนุมัติ)
                    <span class="float-end border border-dark px-2 bg-light"><?= count($items) ?> รายการ</span>
                </h6>

                <div class="mb-4">
                    <?php foreach ($items as $index => $item): $images = json_decode($item['images'] ?? '[]', true);
                        $imgSrc = !empty($images) ? 'assets/uploads/items/' . $images[0] : 'assets/images/placeholder.jpg';

                        $reqQty = (int)$item['requested_qty'];
                        $curStock = (int)($item['current_stock'] ?? 0);
                        $appQty = $isPending ? min($reqQty, $curStock) : (int)$item['approved_qty'];
                    ?>
                        <div class="card border-dark rounded-0 mb-2" style="border-width: 2px !important;">
                            <div class="card-body p-3 d-flex align-items-center bg-white">
                                <img src="<?= $imgSrc ?>" class="border border-dark p-1 me-3" style="width: 70px; height: 70px; object-fit: cover;">
                                <div class="flex-grow-1">
                                    <span class="badge bg-dark rounded-0 mb-1"><?= htmlspecialchars($item['category'] ?? 'ไม่ระบุ') ?></span>
                                    <h6 class="fw-bolder mb-0"><?= htmlspecialchars($item['item_name']) ?></h6>
                                </div>

                                <div class="text-center px-4 border-end border-secondary border-opacity-25">
                                    <span class="d-block text-muted small">คงเหลือ</span>
                                    <span class="fw-bolder text-success fs-5"><?= $curStock ?></span>
                                    <span class="d-block text-muted" style="font-size: 0.7rem;">ชิ้นในคลัง</span>
                                </div>

                                <div class="text-center px-4 border-end border-secondary border-opacity-25">
                                    <span class="d-block text-muted small">จำนวนที่ขอ</span>
                                    <span class="fw-bolder text-dark fs-5"><?= $reqQty ?></span>
                                    <span class="d-block text-muted" style="font-size: 0.7rem;">ตามใบคำขอ</span>
                                </div>

                                <div class="d-flex align-items-center px-4">
                                    <span class="fw-bold me-2">อนุมัติ:</span>
                                    <div class="input-group input-group-sm border border-dark" style="width: 110px;">
                                        <button type="button" class="btn btn-light border-end border-dark fw-bold px-2" onclick="adjustApprove(<?= $index ?>, -1)" <?= !$isPending ? 'disabled' : '' ?>>-</button>
                                        <input type="text" name="approved_qty[<?= $item['request_item_id'] ?>]" id="approve_input_<?= $index ?>"
                                            class="form-control text-center fw-bolder fs-6 approve-input"
                                            value="<?= $appQty ?>" data-req="<?= $reqQty ?>" data-stock="<?= $curStock ?>" <?= !$isPending ? 'readonly' : '' ?>>
                                        <button type="button" class="btn btn-light border-start border-dark fw-bold px-2" onclick="adjustApprove(<?= $index ?>, 1)" <?= !$isPending ? 'disabled' : '' ?>>+</button>
                                    </div>
                                </div>

                                <div style="width: 130px;" class="text-end">
                                    <span class="badge border border-dark rounded-pill py-2 w-100 item-status-badge d-inline-flex align-items-center justify-content-center" id="item_badge_<?= $index ?>">
                                        <i class="bi bi-circle-fill small me-1"></i> <span id="item_text_<?= $index ?>"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- 📦 ส่วนที่ 3: สรุปผล -->
                <div class="card border-dark rounded-0 mb-4 bg-warning bg-opacity-10" style="border-width: 2px !important;">
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

                <!-- ปุ่มการจัดการ -->
                <div class="d-flex justify-content-end gap-3 mb-5">
                    <a href="index.php?page=request_list" class="btn btn-outline-dark fw-bold px-4 py-2 bg-white rounded-0 shadow-sm">[ กลับ ]</a>

                    <?php if ($isPending): ?>
                        <button type="submit" name="action" value="approve" class="btn btn-warning border-dark fw-bolder px-5 py-2 shadow-sm rounded-0">
                            <i class="bi bi-save me-2"></i>บันทึก
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-info bg-opacity-25 border-dark fw-bolder px-4 py-2 rounded-0 shadow-sm"><i class="bi bi-file-earmark-pdf me-2"></i>Export PDF</button>
                        <button type="submit" name="action" value="cancel" class="btn btn-danger border-dark fw-bolder px-5 py-2 shadow-sm rounded-0" onclick="return confirm('ต้องการยกเลิกคำร้องนี้และคืนสต็อกใช่หรือไม่?');">
                            <i class="bi bi-x-circle me-2"></i>ยกเลิกใบเบิกนี้
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", calculateAllStatuses);

    function adjustApprove(index, change) {
        let input = document.getElementById(`approve_input_${index}`);
        if (input.hasAttribute('readonly')) return;
        let reqQty = parseInt(input.getAttribute('data-req'));
        let stockQty = parseInt(input.getAttribute('data-stock'));
        let currentVal = parseInt(input.value) || 0;

        let maxAllowed = Math.min(reqQty, stockQty);
        let newVal = Math.max(0, Math.min(maxAllowed, currentVal + change));

        input.value = newVal;
        calculateAllStatuses();
    }

    function calculateAllStatuses() {
        let inputs = document.querySelectorAll('.approve-input');
        let counts = {
            full: 0,
            partial: 0,
            reject: 0
        };

        inputs.forEach((input, index) => {
            let approved = parseInt(input.value);
            let requested = parseInt(input.getAttribute('data-req'));
            let badge = document.getElementById(`item_badge_${index}`);
            let textSpan = document.getElementById(`item_text_${index}`);
            let circle = badge.querySelector('i');

            badge.className = 'badge border border-dark rounded-pill py-2 w-100 item-status-badge d-inline-flex align-items-center justify-content-center';

            if (approved === requested) {
                badge.classList.add('bg-success');
                circle.className = 'bi bi-circle-fill text-white small me-1';
                textSpan.innerText = 'อนุมัติเต็ม';
                textSpan.className = 'text-white';
                counts.full++;
            } else if (approved === 0) {
                badge.classList.add('bg-danger');
                circle.className = 'bi bi-circle-fill text-white small me-1';
                textSpan.innerText = 'ไม่อนุมัติ';
                textSpan.className = 'text-white';
                counts.reject++;
            } else {
                badge.classList.add('bg-warning');
                circle.className = 'bi bi-circle-fill text-danger small me-1';
                textSpan.innerText = 'อนุมัติบางส่วน';
                textSpan.className = 'text-dark';
                counts.partial++;
            }
        });

        let overallBadge = document.getElementById('summaryOverallBadge');
        let headerBadge = document.getElementById('headerStatusBadge');
        let finalStatus = '';
        let finalClass = '';
        let headerStyle = 'border-width: 2px !important; min-width: 110px; ';

        if (counts.full === inputs.length) {
            finalClass = 'bg-success text-white';
            finalStatus = 'อนุมัติ';
            headerStyle += 'background-color: #28a745; color: #fff;';
        } else if (counts.reject === inputs.length) {
            finalClass = 'bg-danger text-white';
            finalStatus = 'ไม่อนุมัติ';
            headerStyle += 'background-color: #dc3545; color: #fff;';
        } else {
            finalClass = 'bg-warning text-dark';
            finalStatus = 'อนุมัติบางส่วน';
            headerStyle += 'background-color: #fd7e14; color: #fff;';
        }

        overallBadge.className = `badge border border-dark px-3 py-2 fs-6 rounded-0 me-3 ${finalClass}`;
        overallBadge.innerText = finalStatus;

        let firstInput = document.getElementById('approve_input_0');
        if (firstInput && !firstInput.hasAttribute('readonly')) {
            headerBadge.className = `badge border border-dark px-3 py-2 fs-6 rounded-0 shadow-sm d-inline-flex align-items-center justify-content-center`;
            headerBadge.style.cssText = headerStyle;
            headerBadge.innerHTML = `<i class="bi bi-circle-fill small me-1" style="font-size: 0.55rem;"></i> ${finalStatus}`;
        }

        document.getElementById('summaryTextInfo').innerText = `(อนุมัติเต็ม ${counts.full} รายการ, บางส่วน ${counts.partial} รายการ, ไม่อนุมัติ ${counts.reject} รายการ)`;
    }
</script>