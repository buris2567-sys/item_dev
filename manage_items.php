<?php
require_once 'config/db.php';

// ตรวจสอบสิทธิ์ (ต้องเป็น Admin เท่านั้น)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    $_SESSION['error'] = 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถเข้าถึงหน้านี้ได้';
    header("Location: index.php");
    exit;
}

// ดึงรายการหมวดหมู่
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// ดึงรายการสิ่งของ
$items = $pdo->query("
    SELECT i.*, c.name as category_name 
    FROM items i 
    LEFT JOIN categories c ON i.category_id = c.category_id 
    ORDER BY i.created_at DESC
")->fetchAll();

// ดึงประวัติการเคลื่อนไหวล่าสุด (Transaction Log)
$transactions = $pdo->query("
    SELECT t.*, i.name as item_name, c.name as category_name, u.username
    FROM inventory_transactions t
    LEFT JOIN items i ON t.item_id = i.item_id
    LEFT JOIN categories c ON i.category_id = c.category_id
    LEFT JOIN users u ON t.created_by = u.user_id
    ORDER BY t.created_at DESC LIMIT 50
")->fetchAll();

$pageTitle = "จัดการสิ่งของ - ระบบสิ่งพิมพ์และของที่ระลึก";
include 'includes/header.php';
?>

<div class="container-fluid p-0">
    <div class="row g-0 flex-nowrap">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="col d-flex flex-column" style="min-height: 100vh; background-color: var(--bg-main);">
            <div class="p-4 flex-grow-1">
                
                <!-- ส่วนหัว -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">จัดการสิ่งของ</h4>
                    <button class="btn btn-warning fw-bold px-4" data-bs-toggle="modal" data-bs-target="#addItemModal">
                        <i class="bi bi-plus-lg me-1"></i> เพิ่ม
                    </button>
                </div>

                <!-- 1. ตารางรายการสิ่งของ -->
                <div class="card border-warning mb-4 shadow-sm">
                    <div class="card-header bg-warning text-dark fw-bold py-3">
                        รายการสิ่งของ
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border">
                                <thead class="table-light">
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>ประเภท</th>
                                        <th>ชื่อรายการ</th>
                                        <th class="text-center">จำนวนปัจจุบัน</th>
                                        <th class="text-center">คำสั่ง</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $index => $item): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($item['category_name']) ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($item['name']) ?></td>
                                        <td class="text-center">
                                            <div class="d-inline-flex align-items-center bg-light border px-3 py-1 rounded">
                                                <span class="fs-5 me-2 fw-bold" id="stock-val-<?= $item['item_id'] ?>">
                                                    <?= number_format($item['current_stock']) ?>
                                                </span>
                                                <button class="btn btn-sm btn-link text-dark p-0" 
                                                        onclick="openUpdateStockModal('<?= $item['item_id'] ?>', '<?= htmlspecialchars($item['name']) ?>', <?= $item['current_stock'] ?>)">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-dark"><i class="bi bi-pencil"></i></button>
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 2. ตารางประวัติการเปลี่ยนแปลง (Transaction Log) -->
                <div class="card border-warning shadow-sm">
                    <div class="card-header bg-warning text-dark fw-bold py-3">
                        ประวัติการเปลี่ยนแปลงล่าสุด
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border text-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>ประเภท</th>
                                        <th>ชื่อรายการ</th>
                                        <th class="text-center">จำนวน</th>
                                        <th>วันที่-เวลา</th>
                                        <th>รายละเอียด</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $index => $log): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($log['category_name']) ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($log['item_name']) ?></td>
                                        <td class="text-center fw-bold <?= $log['transaction_type'] === 'OUT' || $log['quantity'] < 0 ? 'text-danger' : 'text-success' ?>">
                                            <?= ($log['quantity'] > 0 ? '+' : '') . number_format($log['quantity']) ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                                        <td class="text-muted small">
                                            <?= htmlspecialchars($log['remark']) ?> (โดย <?= htmlspecialchars($log['username']) ?>)
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal: เพิ่มสิ่งของ -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="actions/item_add.php" method="POST" enctype="multipart/form-data" class="modal-content border-0">
            <div class="modal-header bg-light border-bottom-0">
                <h5 class="modal-title fw-bold">เพิ่มสิ่งของ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-danger">ประเภท *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">เลือกประเภท</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-danger">ชื่อ *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">จำนวนเริ่มต้น</label>
                            <input type="number" name="initial_stock" class="form-control" value="0" min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">คำอธิบาย</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">รูปภาพประกอบ (สูงสุด 3 รูป)</label>
                        <input type="file" name="images[]" class="form-control mb-2" accept=".jpg, .png" multiple>
                        <small class="text-muted">รองรับไฟล์ JPG, PNG ขนาดไม่เกิน 5MB</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-warning fw-bold px-4">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: อัปเดตสต็อก -->
<div class="modal fade" id="updateStockModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="actions/item_update_stock.php" method="POST" class="modal-content border-0">
            <div class="modal-header bg-light border-bottom-0">
                <h5 class="modal-title fw-bold" id="updateStockTitle">แก้ไขจำนวนสต็อก</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="item_id" id="us_item_id">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-bold small">จำนวนปัจจุบันในคลัง:</span>
                    <div class="bg-light border px-4 py-2 rounded fw-bold fs-5" id="us_current_stock_display">0</div>
                    <input type="hidden" id="us_current_stock" value="0">
                </div>

                <div class="mb-4">
                    <label class="fw-bold small mb-2">เลือกดำเนินการ:</label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="action_type" id="act_add" value="add" checked onchange="calcNetStock()">
                        <label class="form-check-label fw-bold" for="act_add">เพิ่มสต็อก (+)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="action_type" id="act_reduce" value="reduce" onchange="calcNetStock()">
                        <label class="form-check-label fw-bold" for="act_reduce">ลดสต็อก (-)</label>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-bold small">จำนวนที่ต้องการเพิ่ม/ลด:</span>
                    <input type="number" name="quantity" id="us_quantity" class="form-control text-center w-50 fw-bold border-warning" value="0" min="1" required oninput="calcNetStock()">
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold small">จำนวนสุทธิหลังการปรับปรุง:</span>
                    <div class="bg-info bg-opacity-25 border border-info px-4 py-2 rounded fw-bold fs-5 text-dark" id="us_net_stock_display">0</div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0 d-flex justify-content-center">
                <button type="submit" class="btn btn-warning fw-bold px-5">บันทึก</button>
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>

<script>
// ฟังก์ชันเปิด Modal แก้ไขสต็อกและเซ็ตค่าเริ่มต้น
function openUpdateStockModal(itemId, itemName, currentStock) {
    document.getElementById('updateStockTitle').innerText = 'แก้ไขจำนวนสต็อก: ' + itemName;
    document.getElementById('us_item_id').value = itemId;
    document.getElementById('us_current_stock').value = currentStock;
    document.getElementById('us_current_stock_display').innerText = currentStock;
    document.getElementById('us_quantity').value = 0;
    
    calcNetStock();
    new bootstrap.Modal(document.getElementById('updateStockModal')).show();
}

// ฟังก์ชันคำนวณยอดสุทธิแบบ Real-time
function calcNetStock() {
    let current = parseInt(document.getElementById('us_current_stock').value) || 0;
    let qty = parseInt(document.getElementById('us_quantity').value) || 0;
    let isAdd = document.getElementById('act_add').checked;
    
    let net = isAdd ? (current + qty) : (current - qty);
    if(net < 0) net = 0; // ป้องกันการแสดงผลติดลบ
    
    document.getElementById('us_net_stock_display').innerText = net;
}
</script>

<?php include 'includes/footer.php'; ?>