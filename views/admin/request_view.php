<?php
// 🟢 [คงเดิม] ตรวจสอบสิทธิ์และการเข้าถึง
if (!defined('APP_RUNNING')) exit('Forbidden');

$request_id = $_GET['id'] ?? '';

// ดึงข้อมูลคำร้องและผู้เบิก
$stmt =$pdo->prepare("
    SELECT r.*, u.full_name, u.phone_number 
    FROM requests r 
    LEFT JOIN users u ON r.user_id = u.user_id 
    WHERE r.request_id = ?
");
$stmt->execute([$request_id]);
$request =$stmt->fetch();

if (!$request) {
    echo "<div class='p-4 text-center mt-5'><h5>ไม่พบข้อมูลคำขอ</h5><a href='index.php?page=request_list' class='btn btn-dark mt-3'>กลับ</a></div>";
    exit;
}

// ดึงรายการสิ่งของในคำร้อง
$itemStmt =$pdo->prepare("
    SELECT ri.*, i.current_stock 
    FROM request_items ri 
    LEFT JOIN items i ON ri.item_id = i.item_id 
    WHERE ri.request_id = ?
");
$itemStmt->execute([$request_id]);
$items =$itemStmt->fetchAll();

$isPending = ($request['status'] === 'รออนุมัติ' || $request['status'] === 'Pending');

// 🟢 [ปรับปรุง] กำหนดสีป้ายสถานะให้สวยงาม และไม่มีขอบดำ (border)
$statusText = $request['status'];$badgeStyle = 'min-width: 110px; ';

if ($statusText === 'รออนุมัติ' || $statusText === 'Pending') {$badgeStyle .= 'background-color: #ffc107; color: #000;'; // สีเหลือง
    $statusText = 'รออนุมัติ';
} elseif (strpos($statusText, 'อนุมัติบางส่วน') !== false) {
    $badgeStyle .= 'background-color: #fd7e14; color: #fff;'; // สีส้ม
} elseif (strpos($statusText, 'ไม่อนุมัติ') !== false) {
    $badgeStyle .= 'background-color: #dc3545; color: #fff;'; // สีแดง
} elseif (strpos($statusText, 'อนุมัติ') !== false) {
    $badgeStyle .= 'background-color: #28a745; color: #fff;'; // สีเขียว
} else {
    $badgeStyle .= 'background-color: #6c757d; color: #fff;'; // สีเทา
}
?>

<!-- 🟢 [ปรับปรุง] เริ่มโครงสร้างเนื้อหาเลย ตัด container/row/sidebar ทิ้ง -->
<form action="actions/admin/request_approve.php" method="POST" id="approvalForm">
    <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

    <!-- หัวข้อหน้า -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2" style="border-bottom: 2px solid #e9ecef;">
        <div>
            <!-- <h3 class="fw-bolder mb-1 text-dark">พิจารณาอนุมัติคำขอเบิกสิ่งของ</h3> -->
             <h3 class="fw-bolder mb-1 text-dark"><i class="bi bi-boxes text-warning me-2"></i>พิจารณาอนุมัติคำขอ</h3>
             <span class="text-muted small">ระบบสิ่งพิมพ์และของที่ระลึก</span>    
        </div>
    </div>

    <!-- 📦 ส่วนที่ 1: ข้อมูลคำขอเบิก -->
    <!-- 🟢 [ปรับปรุง] เปลี่ยน Card ให้โค้งมน ไร้ขอบดำ (border-0, rounded-4) -->
    <div class="card border-0 rounded-4 shadow-sm mb-4 border-warning border-top border-4 bg-white">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-person-fill text-warning me-2"></i>ข้อมูลคำขอเบิก</h5>
        </div>
        <div class="card-body p-4">

            <!-- สรุปข้อมูลผู้เบิก & สถานะ แบบ Grid -->
            <div class="row g-3 align-items-center mb-4 pb-3 border-bottom">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="text-muted small mb-1">ผู้เบิก</div>
                    <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($request['full_name'] ?? 'ไม่ทราบชื่อ') ?></div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="text-muted small mb-1">เบอร์โทรศัพท์</div>
                    <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($request['phone_number'] ?? '-') ?></div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="text-muted small mb-1">วันที่ส่ง</div>
                    <div class="fw-bold text-dark small">
                        <?= date('d/m/Y - H:i', strtotime($request['request_date'])) ?>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="text-muted small mb-1">สถานะคำขอ</div>
                    <!-- 🟢 [ปรับปรุง] ป้ายสถานะทรงมน -->
                    <span class="badge px-3 py-2 fs-6 rounded-pill shadow-sm d-inline-flex align-items-center justify-content-center" style="<?= $badgeStyle ?>" id="headerStatusBadge">
                        <i class="bi bi-circle-fill small me-1" style="font-size: 0.55rem;"></i>
                        <?= htmlspecialchars($statusText) ?>
                    </span>
                </div>
            </div>

            <!-- รายละเอียดโครงการ/งาน -->
            <div class="row g-2">
                <div class="col-12 mb-2">
                    <span class="text-muted small d-inline-block" style="width: 120px;">วันที่ใช้งาน:</span>
                    <span class="badge bg-warning text-dark bg-opacity-25 px-2 py-1 fs-6 rounded-3"><?= date('d/m/Y', strtotime($request['use_date'])) ?></span>
                </div>
                <div class="col-12 mb-2">
                    <span class="text-muted small d-inline-block" style="width: 120px;">ประเภทของงาน:</span>
                    <span class="fw-bold text-dark"><?= htmlspecialchars($request['event_type']) ?></span>
                </div>
                <div class="col-12 mb-2">
                    <span class="text-muted small d-inline-block" style="width: 120px;">ชื่องาน/โครงการ:</span>
                    <span class="fw-bold text-dark"><?= htmlspecialchars($request['event_name']) ?></span>
                </div>
                <div class="col-12 mb-2">
                    <span class="text-muted small d-inline-block" style="width: 120px;">สถานที่นำไปใช้:</span>
                    <span class="fw-bold text-dark"><?= htmlspecialchars($request['location']) ?></span>
                </div>
                <div class="col-12 mb-2 d-flex">
                    <span class="text-muted small flex-shrink-0" style="width: 120px;">วัตถุประสงค์:</span>
                    <span class="fw-bold text-dark"><?= nl2br(htmlspecialchars($request['purpose'])) ?></span>
                </div>
                <div class="col-12 d-flex">
                    <span class="text-muted small flex-shrink-0" style="width: 120px;">หมายเหตุ:</span>
                    <span class="text-dark"><?= nl2br(htmlspecialchars($request['user_note'] ?: '-')) ?></span>
                </div>
            </div>

        </div>
    </div>

    <!-- 📦 ส่วนที่ 2: รายการสิ่งของ -->
    <h5 class="fw-bold mb-3 mt-5 text-dark">
        <i class="bi bi-box-seam text-info me-2"></i>รายการสิ่งของที่ขอเบิก
        <span class="float-end badge bg-light text-dark border rounded-pill px-3"><?= count($items) ?> รายการ</span>
    </h5>

    <div class="mb-4">
        <?php foreach ($items as$index => $item):$images = json_decode($item['images'] ?? '[]', true);$imgSrc = !empty($images) ? 'assets/uploads/items/' .$images[0] : 'assets/images/placeholder.jpg';

            $reqQty = (int)$item['requested_qty'];$curStock = (int)($item['current_stock'] ?? 0);$appQty = $isPending ? min($reqQty, $curStock) : (int)$item['approved_qty'];
        ?>
            <!-- 🟢 [ปรับปรุง] การ์ดรายการแต่ละชิ้น โค้งมนและไร้กรอบดำ -->
            <div class="card border-0 rounded-4 shadow-sm mb-3 bg-white">
                <div class="card-body p-3 d-flex align-items-center flex-wrap gap-3">
                    
                    <img src="<?= $imgSrc ?>" class="rounded-3 object-fit-cover border border-light shadow-sm" style="width: 80px; height: 80px;">
                    
                    <div class="flex-grow-1" style="min-width: 150px;">
                        <span class="badge bg-secondary bg-opacity-25 text-dark rounded-pill mb-1"><?= htmlspecialchars($item['category'] ?? 'ไม่ระบุ') ?></span>
                        <h6 class="fw-bolder mb-0 text-dark"><?= htmlspecialchars($item['item_name']) ?></h6>
                    </div>

                    <div class="text-center px-4 border-end border-light d-none d-md-block">
                        <span class="d-block text-muted small">คงเหลือ</span>
                        <span class="fw-bolder text-success fs-5"><?= $curStock ?></span>
                    </div>

                    <div class="text-center px-4 border-end border-light d-none d-sm-block">
                        <span class="d-block text-muted small">จำนวนที่ขอ</span>
                        <span class="fw-bolder text-dark fs-5"><?= $reqQty ?></span>
                    </div>

                    <div class="d-flex align-items-center px-3" style="min-width: 150px;">
                        <span class="fw-bold me-2 text-dark small ">อนุมัติ:</span>
                        <!-- 🟢 [ปรับปรุง] ปุ่มปรับจำนวน (Input group) ใช้ขอบสีอ่อนแทน -->
                        <div class="input-group input-group-sm shadow-sm rounded-2">
                            <button type="button" class="btn  btn-light border-secondary border-opacity-25 fw-bold px-2 text-dark" onclick="adjustApprove(<?= $index ?>, -1)" <?= !$isPending ? 'disabled' : '' ?>>-</button>
                            <input type="text" name="approved_qty[<?= $item['request_item_id'] ?>]" id="approve_input_<?= $index ?>"
                                class="form-control text-center fw-bolder fs-6 approve-input border-secondary border-opacity-25"
                                value="<?= $appQty ?>" data-req="<?= $reqQty ?>" data-stock="<?= $curStock ?>" <?= !$isPending ? 'readonly' : '' ?>>
                            <button type="button" class="btn btn-light border-secondary border-opacity-25 fw-bold px-2 text-dark" onclick="adjustApprove(<?= $index ?>, 1)" <?= !$isPending ? 'disabled' : '' ?>>+</button>
                        </div>
                    </div>

                    <div style="min-width: 140px;" class="text-end ps-3 ">
                        <span class="badge rounded-pill py-2 w-100 item-status-badge d-inline-flex align-items-center justify-content-center shadow-sm " id="item_badge_<?= $index ?>">
                            <i class="bi bi-circle-fill small me-1" style="font-size: 0.55rem;"></i> <span id="item_text_<?= $index ?>"></span>
                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- 📦 ส่วนที่ 3: สรุปผล -->
    <!-- 🟢 [ปรับปรุง] กล่องสรุปผล ใช้พื้นหลังสีเหลืองอ่อนขอบมน -->
    <div class="card border-0 rounded-4 shadow-sm mb-5 bg-warning bg-opacity-10">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-4 text-dark"><i class="bi bi-pencil-square me-2 text-warning"></i>สรุปผลการพิจารณา (ระบบคำนวณอัตโนมัติ)</h6>
            
            <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-warning border-opacity-25 flex-wrap gap-2">
                <span class="fw-bold text-dark me-2">สถานะคำร้องภาพรวม:</span>
                <span class="badge px-3 py-2 fs-6 rounded-pill shadow-sm" id="summaryOverallBadge">รออนุมัติ</span>
                <!-- 🟢 [ปรับปรุง] กล่องบอกจำนวนอนุมัติ ขอบมนสวยงาม -->
                <span class="fw-bold bg-white text-muted px-3 py-2 rounded-pill shadow-sm small" id="summaryTextInfo"></span>
            </div>
            
            <div class="mb-2">
                <label class="fw-bold mb-2 text-dark">ความคิดเห็นเพิ่มเติม (ไม่บังคับ) :</label>
                <!-- 🟢 [ปรับปรุง] ช่องกรอกความคิดเห็น -->
                <textarea name="admin_comment" class="form-control border-light shadow-sm rounded-4 p-3" rows="3" <?= !$isPending ? 'readonly' : '' ?> placeholder="พิมพ์ความคิดเห็นถึงผู้เบิก (ถ้ามี)..."><?= htmlspecialchars($request['admin_comment'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- ปุ่มการจัดการ -->
    <div class="d-flex justify-content-end gap-3 mb-5 flex-wrap">
        <!-- 🟢 [ปรับปรุง] ปุ่มทั้งหมดปรับให้มน (rounded-pill) เข้ากับสไตล์ใหม่ -->
        <a href="index.php?page=request_list" class="btn btn-outline-secondary fw-bold px-4 py-2 bg-white rounded-pill shadow-sm">
            <i class="bi bi-arrow-left me-2"></i>กลับ
        </a>

        <?php if ($isPending): ?>
            <!-- สถานะรออนุมัติ: แสดงปุ่มบันทึก -->
            <button type="submit" name="action" value="approve" class="btn btn-warning fw-bolder px-5 py-2 shadow rounded-pill">
                <i class="bi bi-save me-2"></i>บันทึกผลพิจารณา
            </button>
        <?php else: ?>
            <!-- อนุมัติไปแล้ว หรือ ยกเลิกไปแล้ว: แสดงปุ่ม Export PDF -->
            <button type="button" class="btn btn-info text-white fw-bolder px-4 py-2 rounded-pill shadow-sm">
                <i class="bi bi-file-earmark-pdf me-2"></i>Export PDF
            </button>
            
            <?php if ($request['status'] !== 'ยกเลิก' && $request['status'] !== 'ไม่อนุมัติ'): ?>
            <button type="submit" name="action" value="cancel" class="btn btn-danger fw-bolder px-4 py-2 shadow-sm rounded-pill" onclick="return confirm('ต้องการยกเลิกคำร้องนี้และคืนสต็อกใช่หรือไม่?');">
                <i class="bi bi-x-circle me-2"></i>ยกเลิกคำร้อง
            </button>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</form>

<!-- 🟢 [คงเดิม] โค้ด JavaScript จัดการบวกลบสินค้าและการเปลี่ยนสีสถานะ -->
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
        let counts = { full: 0, partial: 0, reject: 0 };

        inputs.forEach((input, index) => {
            let approved = parseInt(input.value);
            let requested = parseInt(input.getAttribute('data-req'));
            let badge = document.getElementById(`item_badge_${index}`);
            let textSpan = document.getElementById(`item_text_${index}`);
            let circle = badge.querySelector('i');

            // 🟢 [ปรับปรุง] คลาสพื้นฐานของป้ายสถานะ (ไร้ขอบดำ)
            badge.className = 'badge rounded-pill py-2 w-100 item-status-badge d-inline-flex align-items-center justify-content-center shadow-sm';

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
                badge.classList.add('bg-warning', 'bg-opacity-25');
                circle.className = 'bi bi-circle-fill text-warning small me-1';
                textSpan.innerText = 'อนุมัติบางส่วน';
                textSpan.className = 'text-dark';
                counts.partial++;
            }
        });

        let overallBadge = document.getElementById('summaryOverallBadge');
        let finalStatus = '';
        let finalClass = '';

        if (counts.full === inputs.length) {
            finalClass = 'bg-success text-white';
            finalStatus = 'อนุมัติ';
        } else if (counts.reject === inputs.length) {
            finalClass = 'bg-danger text-white';
            finalStatus = 'ไม่อนุมัติ';
        } else {
            finalClass = 'bg-warning text-dark';
            finalStatus = 'อนุมัติบางส่วน';
        }

        overallBadge.className = `badge px-4 py-2 fs-6 rounded-pill me-3 shadow-sm ${finalClass}`;
        overallBadge.innerText = finalStatus;

        document.getElementById('summaryTextInfo').innerText = `อนุมัติเต็ม ${counts.full} รายการ, บางส่วน ${counts.partial} รายการ, ไม่อนุมัติ ${counts.reject} รายการ`;
    }
</script>