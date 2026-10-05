<?php
// 🟢 [คงเดิม] ตรวจสอบสิทธิ์การเข้าถึง และดึงข้อมูลจากฐานข้อมูล
if (!defined('APP_RUNNING')) exit('Forbidden');

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

// ดึงประวัติการเปลี่ยนแปลงล่าสุด
$transactions = $pdo->query("
    SELECT t.*, u.username
    FROM inventory_transactions t
    LEFT JOIN users u ON t.created_by = u.user_id
    ORDER BY t.created_at DESC
")->fetchAll();
?>

<!-- 🟢 [ปรับปรุง] เริ่มต้นที่เนื้อหาเลย ไม่ต้องมี container-fluid และ sidebar เพราะ index.php คลุมให้แล้ว -->

<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4 pb-2" style="border-bottom: 2px solid #e9ecef;">
    <div>
        <h3 class="fw-bolder mb-1 text-dark"><i class="bi bi-boxes text-warning me-2"></i>จัดการสิ่งของ</h3>
          <span class="text-muted small">ระบบสิ่งพิมพ์และของที่ระลึก</span>
    </div>
    <!-- 🟢 [ปรับปรุง] ปุ่มเพิ่มข้อมูล เปลี่ยนเป็นขอบมน (rounded-pill) -->
    <button class="btn btn-warning rounded-pill fw-bold px-4 py-2 shadow-sm border-0 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addItemModal">
        <i class="bi bi-plus-circle-fill fs-5"></i> <span>เพิ่มรายการใหม่</span>
    </button>
</div>

<!-- 📦 ส่วนที่ 1: ตารางรายการสิ่งของทั้งหมด -->
<!-- 🟢 [ปรับปรุง] การ์ดหุ้มตาราง เปลี่ยนเป็นขอบมน (rounded-4), ไร้ขอบดำ, มีขีดสีเหลืองด้านบน -->
<div class="card border-0 rounded-4 shadow-sm mb-5 border-warning border-top border-4 bg-white">
    
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3 rounded-top-4">
        <h5 class="fw-bold mb-0 text-dark">
            <!-- 🟢 [ปรับปรุง] ไอคอนวงกลมหน้าหัวข้อ -->
            <div class="bg-warning bg-opacity-25 text-warning rounded-circle d-inline-flex justify-content-center align-items-center me-2" style="width: 35px; height: 35px;">
                <i class="bi bi-box-seam"></i>
            </div>
            รายการสิ่งของทั้งหมด
        </h5>

        <!-- 🟢 [ปรับปรุง] ตัวกรองและช่องค้นหา เปลี่ยนเป็นทรงแคปซูล (rounded-pill) ให้ดูทันสมัย -->
        <div class="d-flex gap-2 align-items-center">
            <select id="categoryFilter" class="form-select rounded-pill shadow-sm text-dark border-light bg-light" style="width: 180px;">
                <option value="all">ทุกหมวดหมู่</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <div class="input-group shadow-sm" style="width: 250px;">
                <span class="input-group-text bg-light border-0 rounded-start-pill text-muted"><i class="bi bi-search"></i></span>
                <input type="text" id="searchInput" class="form-control bg-light border-0 rounded-end-pill shadow-none" placeholder="ค้นหาชื่อรายการ...">
            </div>
        </div>
    </div>

    <div class="card-body p-0 table-responsive">
        <!-- 🟢 [คงเดิม] CSS Counter สำหรับรันเลขลำดับอัตโนมัติ -->
        <style>
            table#itemsTable { counter-reset: rowNumber; }
            table#itemsTable tbody tr.item-row:not([style*="display: none"]) { counter-increment: rowNumber; }
            table#itemsTable tbody tr.item-row:not([style*="display: none"]) .row-number::before { content: counter(rowNumber); }
        </style>

        <table class="table table-hover align-middle text-center mb-0 border-0" id="itemsTable">
            <thead class="table-light text-muted border-bottom">
                <tr>
                    <th class="text-start ps-4 py-3 border-0 fw-bold" style="width: 40%;">ชื่อรายการ</th>
                    <th class="py-3 border-0 fw-bold" style="width: 15%;">รูปภาพ</th>
                    <th class="py-3 border-0 fw-bold" style="width: 25%;">จำนวนปัจจุบัน</th>
                    <th class="py-3 border-0 fw-bold" style="width: 20%;">จัดการ</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                <?php foreach ($items as $item): ?>
                    <tr class="item-row" data-category="<?= htmlspecialchars($item['category_name']) ?>">
                        
                        <td class="text-start ps-4 fw-bold text-dark border-light py-3">
                            <!-- ลำดับจาก CSS Counter -->
                            <span class="badge bg-light text-secondary rounded-circle me-3 d-inline-flex justify-content-center align-items-center shadow-sm row-number" style="width: 28px; height: 28px;"></span>
                            <?= htmlspecialchars($item['name']) ?>
                        </td>
                        
                        <td class="border-light py-3">
                            <?php
                            $images = json_decode($item['images'], true);
                            $imgSrc = !empty($images) ? 'assets/uploads/items/' . $images[0] : 'assets/images/placeholder.jpg';
                            ?>
                            <img src="<?= $imgSrc ?>" class="rounded-3 shadow-sm object-fit-cover border border-light" style="width: 60px; height: 60px;">
                        </td>
                        
                        <td class="border-light py-3">
                            <!-- 🟢 [ปรับปรุง] กล่องจำนวนปัจจุบันและปุ่มปรับสต็อก ดูนุ่มนวลขึ้น -->
                            <div class="d-inline-flex align-items-center bg-light rounded-pill px-4 py-2 shadow-sm border border-white">
                                <span class="me-3 fw-bolder text-success fs-5" id="stock-val-<?= $item['item_id'] ?>">
                                    <?= number_format($item['current_stock']) ?>
                                </span>
                                <div class="vr mx-2 bg-secondary opacity-25"></div>
                                <button type="button" class="btn btn-sm btn-link text-warning p-0 ms-1 border-0" 
                                        title="ปรับจำนวนสต็อก"
                                        onclick="openUpdateStockModal('<?= $item['item_id'] ?>', '<?= htmlspecialchars($item['name']) ?>', <?= $item['current_stock'] ?>)">
                                    <i class="bi bi-plus-slash-minus fs-5"></i>
                                </button>
                            </div>
                        </td>
                        
                        <td class="border-light py-3">
                            <?php $imgJson = htmlspecialchars($item['images'] ?? '[]', ENT_QUOTES, 'UTF-8'); ?>
                            <!-- 🟢 [ปรับปรุง] กลุ่มปุ่ม Action ดูสะอาดตาขึ้นด้วย rounded-circle และ shadow-sm -->
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-sm btn-light rounded-circle shadow-sm text-info d-flex justify-content-center align-items-center border-0"
                                    style="width: 38px; height: 38px;" title="ดูรายละเอียด"
                                    data-id="<?= $item['item_id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>"
                                    data-desc="<?= htmlspecialchars($item['description']) ?>" data-stock="<?= $item['current_stock'] ?>"
                                    data-images="<?= $imgJson ?>" onclick="openViewModal(this)">
                                    <i class="bi bi-search"></i>
                                </button>

                                <button type="button" class="btn btn-sm btn-light rounded-circle shadow-sm text-dark d-flex justify-content-center align-items-center border-0"
                                    style="width: 38px; height: 38px;" title="แก้ไขข้อมูล"
                                    data-id="<?= $item['item_id'] ?>" data-category="<?= $item['category_id'] ?>"
                                    data-name="<?= htmlspecialchars($item['name']) ?>" data-stock="<?= $item['current_stock'] ?>"
                                    data-desc="<?= htmlspecialchars($item['description']) ?>" data-images="<?= $imgJson ?>"
                                    onclick="openEditModal(this)">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <button type="button" class="btn btn-sm btn-light rounded-circle shadow-sm text-danger d-flex justify-content-center align-items-center border-0"
                                    style="width: 38px; height: 38px;" title="ลบรายการ"
                                    onclick="openDeleteModal('<?= $item['item_id'] ?>', '<?= htmlspecialchars($item['name']) ?>')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>

                    </tr>
                <?php endforeach; ?>
                
                <!-- แถวกรณีค้นหาไม่เจอข้อมูล -->
                <tr id="noDataRow" style="display: none;">
                    <td colspan="4" class="text-center py-5 text-muted border-0">
                        <div class="bg-light rounded-circle d-inline-flex justify-content-center align-items-center mb-3 shadow-sm" style="width: 70px; height: 70px;">
                            <i class="bi bi-box-seam fs-2 text-secondary opacity-50"></i>
                        </div>
                        <h6 class="fw-bold mb-0">ไม่พบข้อมูลรายการสิ่งของ</h6>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- แถบแบ่งหน้า (Pagination) -->
    <div class="card-footer bg-white border-0 py-3 px-4 rounded-bottom-4 d-flex justify-content-end">
        <div id="mainItemsPagination" class="w-100">
            <?php include 'includes/pagination.php'; ?>
        </div>
    </div>
</div>

<!-- 📦 ส่วนที่ 2: นำเข้าตารางประวัติการเปลี่ยนแปลงล่าสุด (Component แยก) -->
<?php include 'components/tables/recent_transactions_table.php'; ?>

<!-- 🟢 นำเข้า Modal ต่างๆ ที่ใช้ในหน้านี้ -->
<?php include 'components/modals/add_item_modal.php'; ?>
<?php include 'components/modals/update_stock_modal.php'; ?>
<?php include 'components/modals/view_item_modal.php'; ?>
<?php include 'components/modals/delete_item_modal.php'; ?>
<?php include 'components/modals/edit_item_modal.php'; ?>

<!-- 🟢 สคริปต์ที่เกี่ยวข้องกับการอัปเดตสต็อกและธุรกรรม -->
<script src="assets/js/transactions.js"></script>
<script src="assets/js/table_pagination.js"></script>

<!-- 🟢 สคริปต์สั่งทำงาน Pagination สำหรับตารางรายการสิ่งของ -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const catFilter = document.getElementById('categoryFilter');
        const searchBox = document.getElementById('searchInput');

        if (typeof initTablePagination === 'function') {
            initTablePagination({
                rowSelector: '.item-row', 
                wrapperSelector: '#mainItemsPagination', 
                noDataSelector: '#noDataRow', 
                triggerInputs: [catFilter, searchBox], 

                filterLogic: function(row) {
                    const search = searchBox.value.toLowerCase().trim();
                    const cat = catFilter.value;
                    const rowCat = row.getAttribute('data-category');
                    const rowName = row.querySelector('td:first-child').textContent.toLowerCase();

                    return (cat === 'all' || rowCat === cat) && rowName.includes(search);
                }
            });
        }
    });
</script>