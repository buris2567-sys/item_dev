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

// 2. ดึงรายการสิ่งของ (แก้ไข SQL ให้ดึงจากคอลัมน์ใหม่แล้ว)
$itemStmt = $pdo->prepare("
    SELECT ri.*, i.current_stock 
    FROM request_items ri 
    LEFT JOIN items i ON ri.item_id = i.item_id 
    WHERE ri.request_id = ?
");
$itemStmt->execute([$request_id]);
$items = $itemStmt->fetchAll();

// 🟢 กำหนดสีป้ายสถานะให้สอดคล้องกับตารางหน้าแรก
$statusText = $request['status'];
$badgeStyle = 'border-width: 2px !important; min-width: 110px; ';

if ($statusText === 'รออนุมัติ' || $statusText === 'Pending') {
    $badgeStyle .= 'background-color: #f1ead4; color: #000;';
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

<!-- 🟢 โครงสร้าง Layout เพื่อล็อกขนาดไม่ให้ล้นจอ -->
<div class="container-fluid p-0 overflow-x-hidden">
    <div class="row g-0 flex-nowrap">

        <!-- สมมติว่าหน้า User มี Sidebar (ปรับตามไฟล์ของคุณ) -->
        <?php include 'includes/sidebar_user.php'; ?>

        <div class="col p-4 flex-grow-1 d-flex flex-column transition-all" style="min-width: 0; min-height: 100vh; background-color: #f5f6f8; font-family: 'Prompt', sans-serif;">
            
            <!-- หัวข้อหน้า -->
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2">
                <h3 class="fw-bolder bg-warning border border-dark px-3 py-2 d-inline-block mb-0">รายละเอียดคำขอเบิกสิ่งของ</h3>
            </div>

            <!-- 📦 ส่วนที่ 1: ข้อมูลคำขอเบิก -->
            <div class="card border-dark rounded-0 mb-4" style="border-width: 2px !important;">
                <div class="card-header bg-light border-dark py-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi bi-person-fill text-primary me-2"></i>ข้อมูลคำขอเบิก</h6>
                    <span class="border border-dark px-2 py-1 bg-white small fw-bold">ID: <?= htmlspecialchars($request_id) ?></span>
                </div>
                <div class="card-body bg-light p-4">

                    <!-- สรุปข้อมูลผู้เบิก & สถานะ แบบ Grid -->
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
                                <!-- ป้ายสถานะ มุมขวาบน -->
                                <span class="badge border border-dark px-3 py-2 fs-6 rounded-0 shadow-sm d-inline-flex align-items-center justify-content-center" style="<?= $badgeStyle ?>">
                                    <i class="bi bi-circle-fill small me-1" style="font-size: 0.55rem;"></i>
                                    <?= htmlspecialchars($statusText) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- รายละเอียดโครงการ/งาน -->
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
                <i class="bi bi-box-seam text-warning me-2"></i>รายการสิ่งของที่ขอเบิก
                <span class="float-end border border-dark px-2 bg-light"><?= count($items) ?> รายการ</span>
            </h6>
            
            <div class="mb-4">
                <?php foreach ($items as $index => $item): 
                    // 🟢 ดึงรูปภาพ (ri.images)
                    $images = json_decode($item['images'] ?? '[]', true);
                    $imgSrc = !empty($images) ? 'assets/uploads/items/' . $images[0] : 'assets/images/placeholder.jpg';
                    
                    $reqQty = (int)$item['requested_qty'];
                    $appQty = (int)$item['approved_qty'];
                    
                    // ประเมินสถานะของแต่ละรายการ
                    $itemStatusText = 'รออนุมัติ'; $itemBadgeClass = 'bg-warning bg-opacity-50 text-dark'; $iconClass = 'text-warning';
                    if ($request['status'] !== 'รออนุมัติ' && $request['status'] !== 'Pending') {
                        if ($appQty === $reqQty) { $itemStatusText = 'อนุมัติเต็ม'; $itemBadgeClass = 'bg-success text-white'; $iconClass = 'text-white'; }
                        elseif ($appQty === 0) { $itemStatusText = 'ไม่อนุมัติ'; $itemBadgeClass = 'bg-danger text-white'; $iconClass = 'text-white'; }
                        else { $itemStatusText = 'อนุมัติบางส่วน'; $itemBadgeClass = 'bg-warning text-dark'; $iconClass = 'text-danger'; }
                    }
                ?>
                <div class="card border-dark rounded-0 mb-2" style="border-width: 2px !important;">
                    <div class="card-body p-3 d-flex align-items-center bg-white">
                        <img src="<?= $imgSrc ?>" class="border border-dark p-1 me-3" style="width: 70px; height: 70px; object-fit: cover;">
                        <div class="flex-grow-1">
                            <span class="badge bg-dark rounded-0 mb-1"><?= htmlspecialchars($item['category'] ?? 'ไม่ระบุ') ?></span>
                            <h6 class="fw-bolder mb-0"><?= htmlspecialchars($item['item_name']) ?></h6>
                        </div>
                        
                        <div class="text-center px-4 border-end border-secondary border-opacity-25">
                            <span class="d-block text-muted small">จำนวนที่ขอ</span>
                            <span class="fw-bolder text-dark fs-5"><?= $reqQty ?></span>
                            <span class="d-block text-muted" style="font-size: 0.7rem;">ตามใบคำขอ</span>
                        </div>

                        <!-- 🟢 ฝั่ง User จะแสดงแค่ตัวเลขที่อนุมัติ ไม่มีปุ่ม +/- -->
                        <div class="text-center px-4 border-end border-secondary border-opacity-25" style="min-width: 120px;">
                            <span class="d-block text-muted small">จำนวนที่อนุมัติ</span>
                            <span class="fw-bolder text-success fs-5"><?= ($request['status'] === 'รออนุมัติ' || $request['status'] === 'Pending') ? '-' : $appQty ?></span>
                            <span class="d-block text-muted" style="font-size: 0.7rem;">ชิ้น</span>
                        </div>

                        <div style="width: 130px;" class="text-end ps-3">
                            <span class="badge border border-dark rounded-pill py-2 w-100 <?= $itemBadgeClass ?> d-inline-flex align-items-center justify-content-center">
                                <i class="bi bi-circle-fill small me-1 <?= $iconClass ?>"></i> <?= $itemStatusText ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- 📦 ส่วนที่ 3: สรุปผล -->
            <div class="card border-dark rounded-0 mb-4 bg-warning bg-opacity-10" style="border-width: 2px !important;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4"><i class="bi bi-pencil-square me-2"></i>สรุปผลการพิจารณาจากเจ้าหน้าที่</h6>
                    
                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-dark">
                        <span class="fw-bold me-3">สถานะคำร้องภาพรวม:</span>
                        <span class="badge border border-dark px-3 py-2 fs-6 rounded-0 me-3 shadow-sm d-inline-flex align-items-center justify-content-center" style="<?= $badgeStyle ?>">
                            <i class="bi bi-circle-fill small me-1" style="font-size: 0.55rem;"></i> <?= htmlspecialchars($statusText) ?>
                        </span>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold mb-2">ความคิดเห็นจากเจ้าหน้าที่ :</label>
                        <div class="p-3 bg-white border border-dark text-dark" style="min-height: 80px;">
                            <?= nl2br(htmlspecialchars($request['admin_comment'] ?: 'ไม่มีความคิดเห็นเพิ่มเติม')) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ปุ่มกลับ สำหรับ User (ไม่มีปุ่มอนุมัติ/ยกเลิก) -->
            <div class="d-flex justify-content-end mb-5">
                <a href="index.php?page=my_requests" class="btn btn-outline-dark fw-bold px-5 py-2 bg-white rounded-0 shadow-sm">[ กลับ ]</a>
            </div>

        </div>
    </div>
</div>