<?php
// ป้องกันการเข้าถึงไฟล์โดยตรง และดึงข้อมูลคำขอ
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

// 🟢 2. [อัปเดต] ดึงรายการสิ่งของด้วย LEFT JOIN เพื่อเอา item_name, category_name และ images มาแสดง
$itemStmt = $pdo->prepare("
    SELECT 
        ri.*, 
        i.name AS item_name, 
        i.images, 
        c.name AS category_name
    FROM request_items ri 
    LEFT JOIN items i ON ri.item_id = i.id 
    LEFT JOIN categories c ON i.category_id = c.category_id
    WHERE ri.request_id = ?
");
$itemStmt->execute([$request_id]);
$items = $itemStmt->fetchAll();

// กำหนดสีป้ายสถานะให้สอดคล้องกับ Theme ใหม่
$statusText = $request['status'];
$badgeStyle = 'min-width: 110px; ';

if ($statusText === 'รออนุมัติ' || $statusText === 'Pending') {
    $badgeStyle .= 'background-color: #fbff00; color: #000;'; // เหลืองสด
    $statusText = 'รออนุมัติ';
} elseif (strpos($statusText, 'อนุมัติบางส่วน') !== false) {
    $badgeStyle .= 'background-color: #fd7e14; color: #fff;'; // ส้ม
} elseif (strpos($statusText, 'ไม่อนุมัติ') !== false) {
    $badgeStyle .= 'background-color: #dc3545; color: #fff;'; // แดง
} elseif (strpos($statusText, 'อนุมัติ') !== false) {
    $badgeStyle .= 'background-color: #28a745; color: #fff;'; // เขียว
} else {
    $badgeStyle .= 'background-color: #6c757d; color: #fff;'; // เทา (ยกเลิก)
}
?>

<!-- หัวข้อหน้าและปุ่มย้อนกลับ -->
<div class="d-flex justify-content-between align-items-center mb-4 pb-2" style="border-bottom: 2px solid #e9ecef;">
    <div>
        <h3 class="fw-bolder mb-1 text-dark">รายละเอียดคำขอเบิกสิ่งของ</h3>
        <span class="text-muted small">ระบบสิ่งพิมพ์และของที่ระลึก</span>
    </div>

    <!-- 🟢 [เพิ่ม] กลุ่มปุ่มจัดการด้านบนขวา (กลับ, PDF, Excel) -->

</div>

<!-- 📦 ส่วนที่ 1: ข้อมูลคำขอเบิก -->
<div class="card border-0 rounded-4 shadow-sm mb-4 border-warning border-top border-4 bg-white">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-file-earmark-text me-2 text-warning"></i>ข้อมูลคำขอเบิก</h5>
        <span class="text-muted small">รหัสอ้างอิง: <span class="fw-bold text-primary"><?= htmlspecialchars($request['request_no']) ?></span></span>
    </div>
    <div class="card-body p-4">

        <!-- สรุปข้อมูลผู้เบิก & สถานะ แบบ Grid -->
        <div class="row g-3 align-items-center mb-4 pb-3 border-bottom">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="text-muted small mb-1">ชื่อ-นามสกุล</div>
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
                <span class="badge px-3 py-2 fs-6 rounded-pill shadow-sm d-inline-flex align-items-center justify-content-center" style="<?= $badgeStyle ?>">
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

<div class="mb-5">
    <?php foreach ($items as $index => $item):
        // 🟢 อ่านรูปภาพและชื่อจาก JOIN
        $images = json_decode($item['images'] ?? '[]', true);
        $imgSrc = !empty($images) ? 'assets/uploads/items/' . $images[0] : 'assets/images/placeholder.jpg';

        $reqQty = (int)$item['requested_qty'];
        $appQty = (int)$item['approved_qty'];

        $itemStatusText = 'รออนุมัติ';
        $itemBadgeClass = 'bg-warning bg-opacity-25 text-dark';
        $iconClass = 'text-warning';
        if ($request['status'] !== 'รออนุมัติ' && $request['status'] !== 'Pending') {
            if ($appQty === $reqQty) {
                $itemStatusText = 'อนุมัติเต็ม';
                $itemBadgeClass = 'bg-success text-white';
                $iconClass = 'text-white';
            } elseif ($appQty === 0) {
                $itemStatusText = 'ไม่อนุมัติ';
                $itemBadgeClass = 'bg-danger text-white';
                $iconClass = 'text-white';
            } else {
                $itemStatusText = 'อนุมัติบางส่วน';
                $itemBadgeClass = 'bg-warning text-dark';
                $iconClass = 'text-danger';
            }
        }

        // ถ้าใบเบิกโดนยกเลิก
        if ($request['status'] === 'ยกเลิก') {
            $itemStatusText = 'ยกเลิก';
            $itemBadgeClass = 'bg-secondary text-white';
            $iconClass = 'text-white';
        }
    ?>
        <div class="card border-0 rounded-4 shadow-sm mb-3 bg-white">
            <div class="card-body p-3 d-flex align-items-center flex-wrap gap-3">

                <img src="<?= $imgSrc ?>" class="rounded-3 object-fit-cover border border-light shadow-sm" style="width: 80px; height: 80px;">

                <div class="flex-grow-1" style="min-width: 200px;">
                    <span class="badge bg-secondary bg-opacity-25 text-dark rounded-pill mb-1"><?= htmlspecialchars($item['category_name'] ?? 'ไม่ระบุ') ?></span>
                    <h6 class="fw-bolder mb-0 text-dark"><?= htmlspecialchars($item['item_name'] ?? 'ไม่ทราบชื่อ') ?></h6>
                </div>

                <div class="text-center px-4 border-end border-light d-none d-sm-block">
                    <span class="d-block text-muted small">จำนวนที่ขอ</span>
                    <span class="fw-bolder text-dark fs-5"><?= $reqQty ?></span>
                </div>

                <div class="text-center px-4 d-none d-sm-block" style="min-width: 120px;">
                    <span class="d-block text-muted small">จำนวนที่อนุมัติ</span>
                    <span class="fw-bolder text-success fs-5"><?= ($request['status'] === 'รออนุมัติ' || $request['status'] === 'Pending') ? '-' : $appQty ?></span>
                </div>

                <div style="min-width: 140px;" class="text-end ps-3">
                    <span class="badge rounded-pill py-2 w-100 fs-6 fw-bold px-4 py-2 <?= $itemBadgeClass ?> d-inline-flex align-items-center justify-content-center shadow-sm">
                        <i class="bi bi-circle-fill small me-1 <?= $iconClass ?>" style="font-size: 0.55rem;"></i> <?= $itemStatusText ?>
                    </span>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- 📦 ส่วนที่ 3: สรุปผล -->
<div class="card border-0 rounded-4 shadow-sm mb-5 bg-warning bg-opacity-10">
    <div class="card-body p-4">
        <h6 class="fw-bold mb-4 text-dark"><i class="bi bi-pencil-square me-2 text-warning"></i>สรุปผลการพิจารณาจากเจ้าหน้าที่</h6>

        <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-warning border-opacity-25">
            <span class="fw-bold text-dark me-3">สถานะคำร้องภาพรวม:</span>
            <span class="badge px-3 py-2 fs-6 rounded-pill shadow-sm d-inline-flex align-items-center justify-content-center" style="<?= $badgeStyle ?>">
                <i class="bi bi-circle-fill small me-1" style="font-size: 0.55rem;"></i> <?= htmlspecialchars($statusText) ?>
            </span>
        </div>

        <div class="mb-2">
            <label class="fw-bold mb-2 text-dark">ความคิดเห็นจากเจ้าหน้าที่ :</label>
            <div class="p-3 bg-white rounded-4 shadow-sm text-dark" style="min-height: 80px;">
                <?= nl2br(htmlspecialchars($request['admin_comment'] ?: 'ไม่มีความคิดเห็นเพิ่มเติม')) ?>
            </div>
        </div>
    </div>
</div>

<!-- 📦 ปุ่มจัดการด้านล่างสุด (กลับ, Export) -->
<div class="d-flex justify-content-end gap-3 mb-5 flex-wrap">

    <a href="index.php?page=my_requests" class="btn btn-outline-secondary fw-bold px-2 py-2 bg-white rounded-pill shadow-sm">
        <i class="bi bi-arrow-left me-2"></i>กลับหน้ารายการ
    </a>


    <!-- ปุ่มแก้ไข (แสดงเฉพาะสถานะ "รออนุมัติ") -->
    <?php if ($request['status'] === 'รออนุมัติ'): ?>
        <!-- 🟢 แก้ไขตรง href ให้วิ่งไปที่หน้า create_request พร้อมส่ง id ไปด้วย -->
        <a href="index.php?page=create_request&id=<?= $request['request_id'] ?>" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-pencil-square me-1"></i> แก้ไขคำร้อง
        </a>
    <?php endif; ?>

    <?php if ($request['status'] !== 'รออนุมัติ' && $request['status'] !== 'ยกเลิก'): ?>
        <!-- ปุ่ม Export จะแสดงก็ต่อเมื่อรายการถูกพิจารณาแล้ว และไม่ได้ถูกยกเลิก -->
        <button type="button" class="btn btn-success fw-bolder px-4 py-2 rounded-pill shadow-sm" onclick="alert('ฟังก์ชัน Export Excel กำลังพัฒนา');">
            <i class="bi bi-file-earmark-excel-fill me-2"></i>Export Excel
        </button>
        <!-- อนุมัติไปแล้ว หรือ ยกเลิกไปแล้ว: แสดงปุ่ม Export PDF -->
        <a href="actions/export_pdf.php?id=<?= $request['request_id'] ?>" target="_blank" class="btn btn-info text-white fw-bolder px-4 py-2 rounded-pill shadow-sm">
            <i class="bi bi-file-earmark-pdf me-2"></i>Export PDF
        </a>
    <?php endif; ?>

</div>