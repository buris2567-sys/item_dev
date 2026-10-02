<?php
if (!defined('APP_RUNNING')) exit('Forbidden');

// ดึงข้อมูลผู้ใช้ปัจจุบันและแผนก
$stmtUser =$pdo->prepare("
    SELECT u.*, d.department_name 
    FROM users u 
    LEFT JOIN departments d ON u.department_id = d.department_id 
    WHERE u.user_id = ?
");
$stmtUser->execute([$_SESSION['user_id']]);
$user =$stmtUser->fetch();

// ดึงรายการสิ่งของทั้งหมดที่พร้อมให้เบิก (สต็อก > 0)[]
$items =$pdo->query("
    SELECT i.*, c.name as category_name 
    FROM items i 
    LEFT JOIN categories c ON i.category_id = c.category_id 
    WHERE i.current_stock > 0 
    ORDER BY c.name ASC, i.name ASC
")->fetchAll();

$pageTitle = "สร้างคำขอเบิกสิ่งของ";
include 'includes/header.php';
?>

<div class="container-fluid p-0" style="background-color: #f4f6f8; min-height: 100vh;">
    <div class="row g-0 flex-nowrap">
        <?php include 'includes/sidebar_user.php'; ?>

        <div class="col p-4 flex-grow-1">
            <h3 class="fw-bolder mb-1 text-dark">แบบฟอร์มขอเบิกพัสดุและสื่อสิ่งพิมพ์</h3>
            <span class="text-muted small">กรอกข้อมูลวัตถุประสงค์และรายละเอียดการนำไปใช้ก่อนเลือกรายการพัสดุ[]</span>

            <form action="actions/user/submit_request.php" method="POST" id="requestForm" class="mt-4">
                
                <!-- 🟢 ส่วนที่ 1: ข้อมูลทั่วไป (General Info)[] -->
                <div class="card border-0 rounded-4 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white py-3 rounded-top-4">
                        <h5 class="fw-bold mb-0"><i class="bi bi-file-earmark-text me-2"></i>ข้อมูลทั่วไป</h5>
                    </div>
                    <div class="card-body p-4 bg-white">
                        <div class="row mb-3">
                            <div class="col-md-3 text-end fw-bold text-muted">สถานะ:</div>
                            <div class="col-md-9"><span class="badge bg-warning text-dark px-3 py-2 rounded-pill">รอพิจารณา</span></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3 text-end fw-bold text-muted">วันที่:</div>
                            <div class="col-md-9 fw-semibold"><?= date('d/m/Y') ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3 text-end fw-bold text-muted">ผู้เบิก:</div>
                            <div class="col-md-9 fw-semibold"><?= htmlspecialchars($user['full_name'] ?? '-') ?></div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-3 text-end fw-bold text-muted">สังกัด/หน่วยงาน:</div>
                            <div class="col-md-9 fw-semibold"><?= htmlspecialchars($user['department_name'] ?? '-') ?></div>
                        </div>

                        <hr class="text-muted opacity-25">

                        <div class="row mb-3 align-items-center">
                            <div class="col-md-3 text-end fw-bold"><span class="text-danger">*</span> ใช้วันที่:</div>
                            <div class="col-md-6">
                                <input type="date" name="use_date" class="form-control rounded-3" required>
                            </div>
                        </div>
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-3 text-end fw-bold"><span class="text-danger">*</span> ประเภทของงาน:</div>
                            <div class="col-md-6">
                                <select name="event_type" class="form-select rounded-3" required>
                                    <option value="">เลือกประเภทงาน...</option>
                                    <option value="ประชุม/สัมมนา">ประชุม/สัมมนา</option>
                                    <option value="จัดนิทรรศการ">จัดนิทรรศการ</option>
                                    <option value="อื่นๆ">อื่นๆ</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-3 text-end fw-bold"><span class="text-danger">*</span> ชื่องาน/โครงการ:</div>
                            <div class="col-md-9">
                                <input type="text" name="event_name" class="form-control rounded-3" required>
                            </div>
                        </div>
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-3 text-end fw-bold"><span class="text-danger">*</span> สถานที่นำไปใช้:</div>
                            <div class="col-md-9">
                                <input type="text" name="location" class="form-control rounded-3" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3 text-end fw-bold"><span class="text-danger">*</span> วัตถุประสงค์เพื่อใช้งาน:</div>
                            <div class="col-md-9">
                                <textarea name="purpose" class="form-control rounded-3" rows="3" placeholder="เพื่อ..." required></textarea>
                            </div>
                        </div>
                          <div class="row mb-3">
                            <div class="col-md-3 text-end fw-bold">หมายเหตุ:</div>
                            <div class="col-md-9">
                                <textarea name="user_note" class="form-control rounded-3" rows="3" placeholder="เพิ่มหมายเหตุ..." ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 🟢 ส่วนที่ 2: รายการสิ่งของให้เลือก[] -->
                <div class="card border-0 rounded-4 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-search me-2"></i>ค้นหาสิ่งของ</h5>
                        <input type="text" id="itemSearch" class="form-control w-25 rounded-pill shadow-none" placeholder="ค้นหา...">
                    </div>
                    <div class="card-body p-4 bg-light">
                        <div class="row g-3" id="itemsGrid">
                            <?php foreach ($items as$item): ?>
                                <?php
                                $images = json_decode($item['images'], true);$imgSrc = !empty($images) ? 'assets/uploads/items/' .$images[0] : 'assets/images/placeholder.jpg';
                                ?>
                                <div class="col-md-3 item-card-wrapper" data-name="<?= strtolower($item['name']) ?>" data-category="<?= $item['category_name'] ?>">
                                    <div class="card h-100 border-secondary border-opacity-25 rounded-4 shadow-sm">
                                        <div class="d-flex p-3 gap-3 h-100">
                                            <img src="<?= $imgSrc ?>" class="rounded-3 object-fit-cover border" style="width: 80px; height: 100px;">
                                            <div class="d-flex flex-column justify-content-between w-100">
                                                <div>
                                                    <div class="fw-bold text-dark text-truncate" style="max-width: 120px;" title="<?= htmlspecialchars($item['name']) ?>"><?= htmlspecialchars($item['name']) ?></div>
                                                    <div class="small text-muted"><?= htmlspecialchars($item['category_name']) ?></div>
                                                    <div class="small mt-1 text-success fw-semibold">คงเหลือ: <?= $item['current_stock'] ?> ชิ้น</div>
                                                </div>
                                                
                                                <!-- ตัวควบคุมจำนวนและปุ่มเพิ่ม -->
                                                <div class="mt-2">
                                                    <div class="input-group input-group-sm mb-2">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="adjustInputQty('<?= $item['item_id'] ?>', -1)">-</button>
                                                        <input type="number" id="input_qty_<?= $item['item_id'] ?>" class="form-control text-center fw-bold" value="1" min="1" max="<?= $item['current_stock'] ?>">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="adjustInputQty('<?= $item['item_id'] ?>', 1, <?=$item['current_stock'] ?>)">+</button>
                                                    </div>
                                                    <button type="button" class="btn btn-warning w-100 fw-bold rounded-3 btn-sm shadow-sm"
                                                        onclick="addToCart('<?= $item['item_id'] ?>', '<?= htmlspecialchars($item['name']) ?>', '<?= htmlspecialchars($item['category_name']) ?>', '<?= $imgSrc ?>', <?= $item['current_stock'] ?>)">
                                                        เพิ่มลงรายการ
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- 🟢 ส่วนที่ 3: ตะกร้ารายการที่ขอเบิก[] -->
                <div class="card border-0 rounded-4 shadow-sm mb-5 border-warning border-top border-4">
                    <div class="card-header bg-warning bg-opacity-10 py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-cart-check me-2"></i>รายการที่ขอเบิกทั้งหมด</h5>
                        <span class="badge bg-warning text-dark rounded-pill fs-6" id="cartTotalCount">0 รายการ</span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0" id="cartTable">
                            <tbody id="cartBody">
                                <!-- รายการในตะกร้าจะถูกเพิ่มตรงนี้ด้วย JavaScript -->
                                <tr id="emptyCartRow">
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-cart-x fs-1 d-block mb-2 opacity-50"></i>
                                        ยังไม่มีรายการสิ่งของในตะกร้า
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- คอนเทนเนอร์สำหรับใส่ Input Hidden ก่อน Submit -->
                <div id="hiddenCartInputs"></div>

                <!-- 🟢 ปุ่มดำเนินการ[] -->
                <div class="d-flex justify-content-between mb-5">
                    <a href="index.php?page=home" class="btn btn-outline-dark fw-bold px-4 py-2 rounded-3 shadow-sm bg-white">
                        <i class="bi bi-arrow-left me-2"></i>ยกเลิก
                    </a>
                    <button type="button" class="btn btn-warning fw-bold px-5 py-2 rounded-3 shadow" onclick="submitRequest()">
                        บันทึกและดำเนินการเลือกสิ่งของ <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="assets/js/cart_system.js"></script>


<?php include 'includes/footer.php'; ?>
