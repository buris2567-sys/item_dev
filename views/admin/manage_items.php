<?php
// ตรวจสอบสิทธิ์การเข้าถึง และดึงข้อมูลจากฐานข้อมูล
if (!defined('APP_RUNNING')) exit('Forbidden');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    $_SESSION['error'] = 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถเข้าถึงหน้านี้ได้';
    header("Location: index.php");
    exit;
}

// ดึงรายการหมวดหมู่ (เฉพาะที่เปิดใช้งานอยู่)
$categories = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll();

// ดึงรายการสิ่งของทั้งหมดที่ยังเปิดใช้งานอยู่
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

<?php
$pageHeader = [
    'icon'   => 'bi-boxes',
    'title'  => 'จัดการสิ่งของ',
    'action' => [
        'label'  => 'เพิ่มรายการใหม่',
        'icon'   => 'bi-plus-circle-fill',
        'type'   => 'modal',
        'target' => '#addItemModal',
    ],
];
include 'components/ui/page_header.php';
?>

<!-- ส่วนที่ 1: ตารางรายการสิ่งของทั้งหมด -->
<div class="card border-0 rounded-4 shadow-sm mb-5 border-warning border-top border-4 bg-white">

<?php
// Extra slot: dropdown หมวดหมู่ + ช่องค้นหา
ob_start(); ?>
    <select id="categoryFilter" class="form-select rounded-pill shadow-sm text-dark border-light bg-light" style="width: 180px;">
        <option value="all">ทุกหมวดหมู่</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
    </select>
<?php $extraHtml = ob_get_clean();

$cardHeader = [
    'icon'   => 'bi-box-seam',
    'title'  => 'รายการสิ่งของทั้งหมด',
    'search' => ['id' => 'searchInput', 'placeholder' => 'ค้นหาชื่อรายการ...'],
    'extra'  => $extraHtml,
];
include 'components/ui/card_header.php';
?>

    <?php include 'components/tables/manage_items_table.php'; ?>

</div>

<!-- ส่วนที่ 2: ตารางประวัติการเปลี่ยนแปลงล่าสุด -->
<?php include 'components/tables/recent_transactions_table.php'; ?>

<!-- Modal ต่างๆ -->
<?php include 'components/modals/add_item_modal.php'; ?>
<?php include 'components/modals/update_stock_modal.php'; ?>
<?php include 'components/modals/view_item_modal.php'; ?>
<?php include 'components/modals/delete_item_modal.php'; ?>
<?php include 'components/modals/edit_item_modal.php'; ?>

<!-- สคริปต์ -->
<script src="assets/js/transactions.js"></script>
<script src="assets/js/table_pagination.js"></script>

<!-- สคริปต์สั่งทำงาน Pagination สำหรับตารางรายการสิ่งของ -->
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
