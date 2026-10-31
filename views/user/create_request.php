<?php
if (!defined('APP_RUNNING')) exit('Forbidden');

// ==========================================
// 1. ส่วนเช็คโหมด (สร้างใหม่ / แก้ไข)
// ==========================================
$edit_id = $_GET['id'] ?? null;
$is_edit_mode = false;
$request_data = [];
$request_items_json = '[]'; // สำหรับส่งให้ JS

if ($edit_id) {
    $is_edit_mode = true;

    // ดึงข้อมูลคำร้อง
    $stmtReq = $pdo->prepare("SELECT * FROM requests WHERE request_id = ? AND user_id = ? AND status = 'รออนุมัติ'");
    $stmtReq->execute([$edit_id, $_SESSION['user_id']]);
    $request_data = $stmtReq->fetch(PDO::FETCH_ASSOC);

    if (!$request_data) {
        echo "<script>alert('ไม่พบคำร้อง หรือคำร้องนี้ไม่สามารถแก้ไขได้แล้ว'); window.location.href='index.php?page=my_requests';</script>";
        exit;
    }

    // ดึงรายการสิ่งของในตะกร้าเดิม
    $stmtItems = $pdo->prepare("
        SELECT ri.item_id as id, ri.requested_qty as qty, i.name, i.images, c.name as category_name, i.current_stock
        FROM request_items ri 
        JOIN items i ON ri.item_id = i.id 
        LEFT JOIN categories c ON i.category_id = c.category_id
        WHERE ri.request_id = ?
    ");
    $stmtItems->execute([$edit_id]);
    $old_items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    $formatted_items = [];
    foreach ($old_items as $itm) {
        $imgData = json_decode($itm['images'] ?? '[]', true);
        $imgSrc = (is_array($imgData) && !empty($imgData)) ? 'assets/uploads/items/' . $imgData[0] : 'assets/images/placeholder.jpg';

        $formatted_items[] = [
            'id'       => (string)$itm['id'], // 🟢 1. บังคับแปลง id ให้เป็นตัวอักษร (String) เพื่อให้ JS ค้นหาเจอ
            'name'     => $itm['name'],
            'category' => $itm['category_name'],
            'image'    => $imgSrc,            // 🟢 2. หว่านแหชื่อตัวแปรเผื่อไว้ 3 แบบเลย ป้องกันรูปภาพหลุด
            'img'      => $imgSrc,
            'imgSrc'   => $imgSrc,
            'qty'      => (int)$itm['qty'],
            'maxStock' => (int)$itm['current_stock']
        ];
    }
    $request_items_json = json_encode($formatted_items);
}

// ดึงข้อมูลผู้ใช้ปัจจุบันและแผนก
$stmtUser = $pdo->prepare("
    SELECT u.*, d.department_name 
    FROM users u 
    LEFT JOIN departments d ON u.department_id = d.department_id 
    WHERE u.user_id = ?
");
$stmtUser->execute([$_SESSION['user_id']]);
$user = $stmtUser->fetch();

// ดึงข้อมูลหมวดหมู่ทั้งหมด
$categories = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll();

// ดึงรายการสิ่งของทั้งหมด
$items = $pdo->query("
    SELECT i.*, c.name as category_name 
    FROM items i 
    LEFT JOIN categories c ON i.category_id = c.category_id 
    WHERE i.is_active = 1 
    ORDER BY c.name ASC, i.name ASC
")->fetchAll();
?>

<style>
    @media (min-width: 992px) {
        .col-lg-20 {
            flex: 0 0 auto;
            width: 20%;
        }
    }
</style>

<div class="mb-4 pb-2" style="border-bottom: 2px solid #e9ecef;">
    <h3 class="fw-bolder mb-1 text-dark">
        <?= $is_edit_mode ? 'แก้ไขแบบฟอร์มคำขอ' : 'แบบฟอร์มสร้างคำขอ' ?>
    </h3>
    <span class="text-muted small">ระบบสิ่งพิมพ์และของที่ระลึก</span>
</div>

<form action="<?= $is_edit_mode ? 'actions/user/request_update.php' : 'actions/user/submit_request.php' ?>" method="POST" id="requestForm">

    <?php if ($is_edit_mode): ?>
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($edit_id) ?>">
    <?php endif; ?>

    <!-- 📦 ส่วนที่ 1: ข้อมูลทั่วไป -->
    <div class="card border-0 rounded-4 shadow-sm mb-5 border-warning border-top border-4 bg-white">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center rounded-top-4">
            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-file-earmark-text me-2 text-warning"></i>ข้อมูลทั่วไป</h5>
        </div>
        <div class="card-body p-4">

            <div class="row mb-3 align-items-center">
                <div class="col-md-3 text-md-end fw-bold text-dark"><span class="text-danger">*</span> ใช้วันที่:</div>
                <div class="col-md-6">
                    <input type="date" name="use_date" class="form-control rounded-3 border-secondary border-opacity-25"
                        value="<?= htmlspecialchars($request_data['use_date'] ?? '') ?>" required>
                </div>
            </div>

            <div class="row mb-3 align-items-center">
                <div class="col-md-3 text-md-end fw-bold text-dark"><span class="text-danger">*</span> ประเภทของงาน:</div>
                <div class="col-md-6">
                    <select name="event_type" class="form-select rounded-3 border-secondary border-opacity-25" required>
                        <option value="">เลือกประเภทงาน...</option>
                        <?php
                        $types = ['โครงการ', 'งาน', 'สัมมนา', 'อบรม', 'จัดนิทรรศการ', 'อื่นๆ'];
                        $selected_type = $request_data['event_type'] ?? '';
                        foreach ($types as $t) {
                            $sel = ($selected_type === $t) ? 'selected' : '';
                            echo "<option value=\"$t\" $sel>$t</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3 align-items-center">
                <div class="col-md-3 text-md-end fw-bold text-dark"><span class="text-danger">*</span> ชื่องาน/โครงการ:</div>
                <div class="col-md-9">
                    <input type="text" name="event_name" class="form-control rounded-3 border-secondary border-opacity-25"
                        value="<?= htmlspecialchars($request_data['event_name'] ?? '') ?>" required>
                </div>
            </div>

            <div class="row mb-3 align-items-center">
                <div class="col-md-3 text-md-end fw-bold text-dark"><span class="text-danger">*</span> สถานที่นำไปใช้:</div>
                <div class="col-md-9">
                    <input type="text" name="location" class="form-control rounded-3 border-secondary border-opacity-25"
                        value="<?= htmlspecialchars($request_data['location'] ?? '') ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 text-md-end fw-bold text-dark pt-1"><span class="text-danger">*</span> วัตถุประสงค์เพื่อใช้งาน:</div>
                <div class="col-md-9">
                    <textarea name="purpose" class="form-control rounded-3 border-secondary border-opacity-25" rows="3" required><?= htmlspecialchars($request_data['purpose'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 text-md-end fw-bold text-dark pt-1">หมายเหตุ (ไม่บังคับ):</div>
                <div class="col-md-9">
                    <textarea name="user_note" class="form-control rounded-3 border-secondary border-opacity-25" rows="2"><?= htmlspecialchars($request_data['user_note'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- 📦 ส่วนที่ 2: เลือกสิ่งของ -->
    <div class="card border-0 rounded-4 shadow-sm mb-4 bg-white border-info border-top border-4">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3 rounded-top-4">
            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-box-seam me-2 text-info"></i>เลือกสิ่งของ</h5>

            <!-- แถบตัวกรองและค้นหา -->
            <div class="d-flex gap-2">
                <select id="categoryFilter" class="form-select rounded-3 shadow-sm text-dark border-light bg-light" style="width: 200px;">
                    <option value="all">ทุกหมวดหมู่</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <div class="input-group shadow-sm" style="width: 250px;">
                    <span class="input-group-text bg-light border-0 rounded-start-3 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="itemSearch" class="form-control bg-light border-0 rounded-end-3 shadow-none" placeholder="ค้นหาชื่อรายการ...">
                </div>
            </div>
        </div>

        <div class="card-body p-4 bg-light bg-opacity-50">
            <div class="row g-3" id="itemsGrid">
                <?php foreach ($items as $item):
                    $imgData = json_decode($item['images'] ?? '[]', true);
                    $imgSrc = (is_array($imgData) && !empty($imgData)) ? 'assets/uploads/items/' . $imgData[0] : 'assets/images/placeholder.jpg';
                    $imgJson = htmlspecialchars(json_encode(is_array($imgData) ? $imgData : []), ENT_QUOTES, 'UTF-8');
                ?>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-20 item-card-wrapper" data-name="<?= htmlspecialchars(strtolower($item['name'])) ?>" data-category="<?= htmlspecialchars($item['category_name'] ?? '') ?>">
                        <div class="card h-100 border-0 rounded-4 shadow-sm bg-white" style="transition: 0.2s;" onmouseover="this.classList.add('shadow')" onmouseout="this.classList.remove('shadow')">
                            <div class="d-flex flex-column p-3 h-100">

                                <div class="position-relative w-100 mb-3 text-center">
                                    <img src="<?= $imgSrc ?>" class="rounded-3 object-fit-cover border border-light w-100 shadow-sm"
                                        style="aspect-ratio: 4/3; max-height: 120px; cursor: pointer; transition: 0.2s;"
                                        onmouseover="this.style.opacity='0.8'"
                                        onmouseout="this.style.opacity='1'"
                                        data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>"
                                        data-desc="<?= htmlspecialchars($item['description']) ?>" data-stock="<?= $item['current_stock'] ?>"
                                        data-images="<?= $imgJson ?>" onclick="openViewModal(this)"
                                        title="คลิกเพื่อขยายรูปภาพ">
                                    <span class="position-absolute bottom-0 end-0 bg-dark text-white rounded-start-pill small px-2 py-1 opacity-75" style="font-size: 0.65rem; pointer-events: none;"><i class="bi bi-arrows-angle-expand"></i></span>
                                </div>

                                <div class="d-flex flex-column justify-content-between flex-grow-1" style="min-width: 0;">
                                    <div class="mb-2">
                                        <div class="fw-bold text-dark text-truncate w-100" title="<?= htmlspecialchars($item['name']) ?>">
                                            <?= htmlspecialchars($item['name']) ?>
                                        </div>
                                        <div class="small text-muted text-truncate"><?= htmlspecialchars($item['category_name']) ?></div>
                                    </div>

                                    <div class="mt-auto">
                                        <div class="input-group input-group-sm mb-2 shadow-sm rounded-2">
                                            <button class="btn btn-light border-secondary border-opacity-25 px-2 fw-bold text-dark" type="button" onclick="adjustInputQty('<?= $item['id'] ?>', -1, <?= $item['current_stock'] ?>)">-</button>
                                            <input type="number" id="input_qty_<?= $item['id'] ?>" class="form-control text-center fw-bold px-0 border-secondary border-opacity-25" value="1" min="1">
                                            <button class="btn btn-light border-secondary border-opacity-25 px-2 fw-bold text-dark" type="button" onclick="adjustInputQty('<?= $item['id'] ?>', 1, <?= $item['current_stock'] ?>)">+</button>
                                        </div>

                                        <button type="button" class="btn btn-warning w-100 fw-bold rounded-2 btn-sm shadow-sm" onclick="addToCart('<?= $item['id'] ?>', '<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($item['category_name'] ?? '', ENT_QUOTES) ?>', '<?= $imgSrc ?>', <?= $item['current_stock'] ?>)">
                                            <i class="bi bi-plus-lg me-1"></i> เพิ่มลงรายการ
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="paginationControls" class="mt-4 d-flex justify-content-center"></div>
        </div>
    </div>

    <!-- 📦 ส่วนที่ 3: ตะกร้ารายการที่ขอเบิก -->
    <div class="card border-0 rounded-4 shadow-sm mb-5 border-success border-top border-4 bg-white">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-cart-check me-2 text-success"></i>รายการในตะกร้า</h5>
            <span class="badge bg-success text-white rounded-pill px-3 py-2 fs-6 shadow-sm" id="cartTotalCount">0 รายการ</span>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0" id="cartTable">
                <tbody id="cartBody">
                </tbody>
            </table>
        </div>
    </div>

    <div id="hiddenCartInputs"></div>

    <div class="d-flex justify-content-end gap-3 mb-5">
        <a href="index.php?page=<?= $is_edit_mode ? 'my_requests' : 'home' ?>" class="btn btn-outline-secondary fw-bold px-4 py-2 rounded-pill shadow-sm bg-white">
            <i class="bi bi-arrow-left me-2"></i>ยกเลิก
        </a>
        <button type="button" class="btn btn-warning fw-bold px-5 py-2 rounded-pill shadow" onclick="submitRequest()">
            <i class="bi bi-save me-2"></i> <?= $is_edit_mode ? 'บันทึกการแก้ไข' : 'ยืนยันคำขอ' ?>
        </button>
    </div>

</form>



<!-- เรียกใช้ไฟล์ระบบตะกร้า -->
<script src="assets/js/cart_system.js"></script>

<!-- 🟢 สคริปต์โหลดของเก่าเข้าตะกร้า -->
 <script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($is_edit_mode && $request_items_json != '[]'): ?>
            let oldCartItems = <?= $request_items_json ?>;

            if (typeof cart !== 'undefined') {
                // 1. 🟢 กำหนดให้เป็น Object {} เพื่อให้ตรงกับ cart_system.js
                cart = {};

                // 2. วนลูปยัดของใส่ Object โดยใช้ id เป็นคีย์หลัก
                oldCartItems.forEach(item => {
                    let itemId = String(item.id);
                    cart[itemId] = {
                        id: itemId,
                        name: String(item.name),
                        category: String(item.category),
                        img: String(item.image), // ใช้ img ให้ตรงกับที่ JS รอรับ
                        qty: parseInt(item.qty, 10)
                    };
                });

                // 3. สั่งวาด UI ตะกร้า
                if (typeof renderCart === 'function') renderCart();
            }
        <?php endif; ?>
    });

</script>
<script src="assets/js/cart_take.js"></script>



<?php include 'components/modals/view_item_modal.php'; ?>