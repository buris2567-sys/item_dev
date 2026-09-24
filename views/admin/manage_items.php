<?php
if (!defined('APP_RUNNING')) exit('Forbidden');

// ตรวจสอบสิทธิ์
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


// // ดึงประวัติการเปลี่ยนแปลงล่าสุด (สำคัญ: ต้องใช้ LEFT JOIN และ COALESCE เพื่อให้ดึงประวัติของของที่ถูกลบไปแล้วได้)
// $transactions = $pdo->query("
//     SELECT t.*, 
//            COALESCE(i.name, 'รายการที่ถูกลบไปแล้ว') as item_name, 
//            COALESCE(c.name, '-') as category_name, 
//            u.username
//     FROM inventory_transactions t
//     LEFT JOIN items i ON t.item_id = i.item_id
//     LEFT JOIN categories c ON i.category_id = c.category_id
//     LEFT JOIN users u ON t.created_by = u.user_id
//     ORDER BY t.created_at DESC
// ")->fetchAll();

// ดึงประวัติการเปลี่ยนแปลงล่าสุด (ใช้ข้อมูล Snapshot ตรงๆ จากตาราง ไม่ต้องพึ่งตาราง items)
$transactions = $pdo->query("
    SELECT t.*, u.username
    FROM inventory_transactions t
    LEFT JOIN users u ON t.created_by = u.user_id
    ORDER BY t.created_at DESC
")->fetchAll();

$pageTitle = "จัดการสิ่งของ";
include 'includes/header.php';
?>

<div class="container-fluid p-0">
    <div class="row g-0 flex-nowrap">
        <!-- ดึง Sidebar สีเข้มมาแสดง -->
        <?php include 'includes/sidebar_admin.php'; ?>

        <div class="col d-flex flex-column" style="min-height: 100vh; background-color: #f5f6f8;">
            <div class="p-4 flex-grow-1">

                <h4 class="fw-bold mb-4 text-dark">จัดการสิ่งของ</h4>

                <!-- การ์ดเพิ่มสิ่งของ -->
                <div class="card border-dark rounded-0 mb-4 shadow-sm" style="border-width: 1px !important;">
                    <div class="card-body d-flex justify-content-between align-items-center p-3">
                        <h6 class="fw-bold mb-0">เพิ่มสิ่งของ</h6>
                        <button class="btn btn-warning border-dark rounded-0 fw-bold px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addItemModal">
                            <i class="bi bi-plus-lg me-1"></i> เพิ่ม
                        </button>
                    </div>
                </div>

                <!-- การ์ดตารางรายการสิ่งของ -->
                <div class="card border-dark rounded-0 mb-4 shadow-sm" style="border-width: 1px !important;">
                    <div class="card-header bg-warning border-dark rounded-0 fw-bold py-3" style="border-bottom-width: 1px !important;">
                        รายการสิ่งของ
                    </div>
                    <div class="card-body p-0">

                        <!-- แถบเครื่องมือ: ตัวกรอง และ ค้นหา -->
                        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                            <div class="d-flex align-items-center">
                                <label class="fw-bold small me-2 mb-0">ประเภท:</label>
                                <select id="categoryFilter" class="form-select form-select-sm border-dark rounded-0" style="width: 200px;">
                                    <option value="all">ทั้งหมด</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-search me-2 text-muted"></i>
                                <input type="text" id="searchInput" class="form-control form-control-sm border-dark rounded-0" placeholder="ค้นหาชื่อรายการ...">
                            </div>
                        </div>

                        <!-- ตารางข้อมูล -->
                        <div class="table-responsive">
                            <table class="table table-bordered border-dark align-middle text-center mb-0" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-start ps-4 text-muted small fw-bold">ชื่อรายการ</th>
                                        <th class="text-muted small fw-bold">รูปภาพ</th>
                                        <th class="text-muted small fw-bold">จำนวนปัจจุบัน</th>
                                        <th class="text-muted small fw-bold">คำสั่ง</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $index => $item): ?>
                                        <!-- ซ่อน category ไว้ใน data attribute สำหรับให้ JS ใช้กรองข้อมูล -->
                                        <tr class="item-row" data-category="<?= htmlspecialchars($item['category_name']) ?>">
                                            <td class="text-start ps-4 fw-bold">
                                                <span class="me-3 text-muted"><?= $index + 1 ?></span> <?= htmlspecialchars($item['name']) ?>
                                            </td>
                                            <td>
                                                <!-- สมมติว่ามีรูปภาพ ถ้าไม่มีใช้ placeholder -->
                                                <?php
                                                $images = json_decode($item['images'], true);
                                                $imgSrc = !empty($images) ? 'assets/uploads/items/' . $images[0] : 'assets/images/placeholder.jpg';
                                                ?>
                                                <img src="<?= $imgSrc ?>" height="40" class="border border-dark object-fit-cover" style="width: 60px;">
                                            </td>
                                            <td>
                                                <div class="d-inline-flex align-items-center border border-dark rounded-0 bg-white px-3 py-1">
                                                    <span class="me-3 fw-bold" id="stock-val-<?= $item['item_id'] ?>">
                                                        <?= number_format($item['current_stock']) ?>
                                                    </span>
                                                    <!-- <i class="bi bi-pencil cursor-pointer" onclick="openUpdateStockModal('<?= $item['item_id'] ?>', '<?= htmlspecialchars($item['name']) ?>', <?= $item['current_stock'] ?>)"></i> -->
                                                    <!-- 🟢 เพิ่ม style="cursor: pointer;" เพื่อให้เมาส์เปลี่ยนเป็นรูปมือ และเพิ่ม title แจ้งเตือนเมื่อเอาเมาส์ชี้ -->
                                                    <i class="bi bi-pencil text-warning"
                                                        style="cursor: pointer;"
                                                        title="คลิกเพื่อแก้ไขจำนวนสต็อก"
                                                        onclick="openUpdateStockModal('<?= $item['item_id'] ?>', '<?= htmlspecialchars($item['name']) ?>', <?= $item['current_stock'] ?>)">
                                                    </i>
                                                </div>
                                            </td>
                                            <td>
                                                <?php $imgJson = htmlspecialchars($item['images'] ?? '[]', ENT_QUOTES, 'UTF-8'); ?>

                                                <!-- ปุ่มแว่นขยาย (ดูรายละเอียด) -->
                                                <button type="button" class="btn btn-sm btn-outline-info rounded-0 me-1"
                                                    data-id="<?= $item['item_id'] ?>"
                                                    data-name="<?= htmlspecialchars($item['name']) ?>"
                                                    data-stock="<?= $item['current_stock'] ?>"
                                                    data-images="<?= $imgJson ?>"
                                                    onclick="openViewModal(this)">
                                                    <i class="bi bi-search"></i>
                                                </button>

                                                <!-- ปุ่มแก้ไข -->
                                                <button type="button" class="btn btn-sm btn-outline-dark rounded-0 me-1"><i class="bi bi-pencil"></i></button>

                                                <!-- ปุ่มถังขยะ (ลบ) -->
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-0"
                                                    onclick="openDeleteModal('<?= $item['item_id'] ?>', '<?= htmlspecialchars($item['name']) ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr id="noDataRow" style="display: none;">
                                        <td colspan="4" class="text-center py-4 text-muted">ไม่พบข้อมูล</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!--  แถบแบ่งหน้า (Pagination) -->
                        
                        <?php include 'includes/pagination.php'; ?>

                    </div>
                </div>

                <!-- นำเข้าการ์ดตารางประวัติการเปลี่ยนแปลงล่าสุด (Component แยก) -->
                <?php include 'components/tables/recent_transactions_table.php'; ?>

            </div>
        </div>
    </div>
</div>

<!-- Modal เพิ่มสิ่งของ และ อัปเดตสต็อก (ดึงไฟล์เดิมมาใช้) -->
<?php include 'components/modals/add_item_modal.php'; ?>
<?php include 'components/modals/update_stock_modal.php'; ?>
<?php include 'components/modals/view_item_modal.php'; ?>
<?php include 'components/modals/delete_item_modal.php'; ?>

<!-- 🟢 ดึงไฟล์ JavaScript แยกส่วน (Components) เข้ามาทำงาน -->
<!-- เนื่องจากโปรเจกต์รันผ่าน index.php เป็นหลัก (Front Controller) จึงอ้างอิง path เริ่มจาก root -->
<script src="assets/js/manage_items.js"></script>
<script src="assets/js/transactions.js"></script>

<?php include 'includes/footer.php'; ?>