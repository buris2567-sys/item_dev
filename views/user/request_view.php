<?php
if (!defined('APP_RUNNING')) exit('Forbidden');

$request_id = $_GET['id'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

// 1. ดึงข้อมูลคำร้อง (WHERE user_id บังคับให้ดูได้เฉพาะของตัวเอง)
$stmt = $pdo->prepare("
    SELECT r.*, u.full_name, u.phone_number 
    FROM requests r 
    JOIN users u ON r.user_id = u.user_id 
    WHERE r.request_id = ? AND r.user_id = ?
");
$stmt->execute([$request_id, $user_id]);
$request = $stmt->fetch();

if (!$request) {
    echo "<div class='p-4 text-center mt-5'><h5><i class='bi bi-exclamation-triangle text-danger'></i> ไม่พบข้อมูลคำขอ หรือคุณไม่มีสิทธิ์เข้าถึง</h5><a href='index.php?page=my_requests' class='btn btn-dark mt-3'>กลับไปหน้าประวัติ</a></div>";
    exit;
}

// 2. ดึงรายการสิ่งของ
$itemStmt = $pdo->prepare("
    SELECT ri.*, COALESCE(i.name, ri.item_name_snapshot) as display_name, i.current_stock, i.images 
    FROM request_items ri 
    LEFT JOIN items i ON ri.item_id = i.item_id 
    WHERE ri.request_id = ?
");
$itemStmt->execute([$request_id]);
$items = $itemStmt->fetchAll();
?>

<div class="p-4 flex-grow-1" style="font-family: 'Prompt', sans-serif;">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2">
        <h3 class="fw-bolder bg-warning border border-dark px-3 py-2 d-inline-block">รายละเอียดคำขอเบิกสิ่งของ</h3>
        <?php
            $headStatusClass = 'bg-warning text-dark';
            if ($request['status'] === 'อนุมัติ') $headStatusClass = 'bg-success text-white';
            elseif ($request['status'] === 'ไม่อนุมัติ') $headStatusClass = 'bg-danger text-white';
            elseif ($request['status'] === 'ยกเลิก') $headStatusClass = 'bg-secondary text-white';
        ?>
        <span class="badge border border-dark px-4 py-2 fs-6 rounded-0 <?= $headStatusClass ?>"><?= htmlspecialchars($request['status']) ?></span>
    </div>

    <!-- ข้อมูลผู้ขอเบิก -->
    <div class="card border-dark rounded-0 mb-4" style="border-width: 2px !important;">
        <div class="card-header bg-light border-dark py-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="bi bi-person-fill text-primary me-2"></i>ข้อมูลคำขอเบิก</h6>
            <span class="border border-dark px-2 py-1 bg-white small fw-bold">ID: <?= htmlspecialchars($request_id) ?></span>
        </div>
        <div class="card-body bg-light p-4">
            <div class="row bg-white border border-dark p-3 mb-3 mx-0">
                <div class="col-md-6">
                    <p class="mb-1 text-muted small">ผู้เบิก: <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($request['full_name']) ?></span></p>
                    <p class="mb-0 text-muted small">เบอร์โทรศัพท์: <span class="fw-bold text-dark"><?= htmlspecialchars($request['phone_number'] ?? '-') ?></span></p>
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
                <p class="text-muted small mb-0 mt-3">หมายเหตุ: <br><span class="text-dark"><?= nl2br(htmlspecialchars($request['user_note'] ?: '-')) ?></span></p>
            </div>
        </div>
    </div>

    <!-- รายการสิ่งของ -->
    <h6 class="fw-bold mb-3"><i class="bi bi-box-seam text-warning me-2"></i>รายการสิ่งของที่ขอเบิก</h6>
    <div class="mb-4">
        <?php foreach ($items as $index => $item): 
            $images = json_decode($item['images'] ?? '[]', true);
            $imgSrc = !empty($images) ? 'assets/uploads/items/' . $images[0] : 'assets/images/placeholder.jpg';
            $reqQty = (int)$item['requested_qty'];
            $appQty = (int)$item['approved_qty'];
            
            $statusText = 'รออนุมัติ'; $badgeClass = 'bg-warning text-dark'; $iconClass = 'text-warning';
            if ($request['status'] !== 'รออนุมัติ' && $request['status'] !== 'Pending') {
                if ($appQty === $reqQty) { $statusText = 'อนุมัติเต็ม'; $badgeClass = 'bg-success text-white'; $iconClass = 'text-white'; }
                elseif ($appQty === 0) { $statusText = 'ไม่อนุมัติ'; $badgeClass = 'bg-danger text-white'; $iconClass = 'text-white'; }
                else { $statusText = 'อนุมัติบางส่วน'; $badgeClass = 'bg-warning text-dark'; $iconClass = 'text-danger'; }
            }
        ?>
        <div class="card border-dark rounded-0 mb-2" style="border-width: 2px !important;">
            <div class="card-body p-3 d-flex align-items-center bg-white">
                <img src="<?= $imgSrc ?>" class="border border-dark p-1 me-3" style="width: 70px; height: 70px; object-fit: cover;">
                <div class="flex-grow-1">
                    <h6 class="fw-bolder mb-0"><?= htmlspecialchars($item['display_name']) ?></h6>
                </div>
                <div class="text-center px-4 border-end border-secondary border-opacity-25">
                    <span class="d-block text-muted small">จำนวนที่ขอ</span>
                    <span class="fw-bolder text-warning fs-5"><?= $reqQty ?></span>
                </div>
                <div class="text-center px-4 border-end border-secondary border-opacity-25">
                    <span class="d-block text-muted small">จำนวนที่อนุมัติ</span>
                    <span class="fw-bolder text-success fs-5"><?= ($request['status'] === 'รออนุมัติ' || $request['status'] === 'Pending') ? '-' : $appQty ?></span>
                </div>
                <div style="width: 130px;" class="text-end ps-3">
                    <span class="badge border border-dark rounded-pill py-2 w-100 <?= $badgeClass ?>">
                        <i class="bi bi-circle-fill small me-1 <?= $iconClass ?>"></i> <?= $statusText ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- สรุปผล -->
    <div class="card border-dark rounded-0 mb-4 bg-warning bg-opacity-10" style="border-width: 2px !important;">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-4">สรุปผลการพิจารณาจากเจ้าหน้าที่</h6>
            <div class="mb-3">
                <label class="fw-bold mb-2">สถานะคำร้องภาพรวม:</label>
                <span class="badge border border-dark px-3 py-2 fs-6 rounded-0 <?= $headStatusClass ?>"><?= htmlspecialchars($request['status']) ?></span>
            </div>
            <div class="mb-3">
                <label class="fw-bold mb-2">ความคิดเห็นจากเจ้าหน้าที่ :</label>
                <div class="p-3 bg-white border border-dark text-dark" style="min-height: 80px;">
                    <?= nl2br(htmlspecialchars($request['admin_comment'] ?: 'ไม่มีความคิดเห็นเพิ่มเติม')) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-5">
        <a href="index.php?page=my_requests" class="btn btn-outline-dark fw-bold px-5 py-2 bg-white rounded-0 shadow-sm">[ กลับ ]</a>
    </div>
</div>