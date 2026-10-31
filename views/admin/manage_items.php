<?php
// 🟢 [คงเดิม] ตรวจสอบสิทธิ์การเข้าถึง และดึงข้อมูลจากฐานข้อมูล
if (!defined('APP_RUNNING')) exit('Forbidden');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    $_SESSION['error'] = 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถเข้าถึงหน้านี้ได้';
    header("Location: index.php");
    exit;
}

// ดึงรายการหมวดหมู่
// ดึงรายการหมวดหมู่ (เฉพาะที่เปิดใช้งานอยู่)
$categories = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll();

// ดึงรายการสิ่งของทั้งหมดที่ยังเปิดใช้งานอยู่ (is_active = 1) 
// 🟢 [แก้ไข] เพิ่มการดึง item_code มาแสดงด้วย และอ้างอิง PK คือ id
$items = $pdo->query("
    SELECT i.*, c.name as category_name 
    FROM items i 
    LEFT JOIN categories c ON i.category_id = c.category_id 
    WHERE i.is_active = 1 
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
<div class="d-flex justify-content-between   align-items-center mb-4 pb-2" style="border-bottom: 2px solid #e9ecef;">
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
        <?php include 'components/tables/manage_items_table.php'; ?>
        
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