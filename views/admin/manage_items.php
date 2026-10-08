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

        <!-- 🟢 ปรับสีพื้นหลังให้เข้มขึ้น (#ced4da) เพื่อตัดกับสีขาวของการ์ดอย่างชัดเจน -->
        <div class="col d-flex flex-column" style="min-height: 100vh; background-color: #ced4da;">
            <div class="p-4 flex-grow-1">

                <!-- 🟢 ปรับปรุง: ออกแบบส่วนหัว (Header) ใหม่ เพิ่มไอคอน เส้นคั่นใต้หัวข้อให้ดูเป็นสัดส่วน ไม่โล่งตา -->
                <div class="d-flex justify-content-between align-items-end mb-4 pb-3" style="border-bottom: 2px solid #e9ecef;">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">
                            <i class="bi bi-boxes text-warning me-2"></i>จัดการสิ่งของ
                        </h3>
                        <span class="text-muted small">บริหารจัดการ เพิ่ม ลบ แก้ไข รายการสิ่งของทั้งหมดในคลัง</span>
                    </div>
                    <!-- 🟢 ปรับปรุง: ปุ่มเพิ่มข้อมูลขยายขนาดขึ้น ใส่ไอคอนทึบ และดันเงาให้ลอยเด่นน่ากด -->
                    <button class="btn btn-warning rounded-pill fw-bold px-4 py-2 shadow border-0 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addItemModal">
                        <i class="bi bi-plus-circle-fill fs-5"></i> <span>เพิ่มรายการใหม่</span>
                    </button>
                </div>

                <!-- (ส่วนของการ์ดรายการสิ่งของทั้งหมด อยู่ต่อจากตรงนี้ตามเดิม...) -->

                <!-- 🟢 ปรับปรุง: การ์ดตาราง ลบเส้นขอบดำออก (border-0) ใช้ขอบโค้งมนระดับ 4 (rounded-4) และเพิ่มเงา (shadow-sm) -->
                <div class="card border-0 rounded-4 mb-5 shadow-sm overflow-hidden bg-white">
                    <!-- 🟢 ปรับปรุง: นำแถบสีเหลือง (bg-warning) กลับมา เพื่อให้หัวกล่องมีน้ำหนักสายตา บาลานซ์กับ Sidebar ด้านซ้าย -->
                    <div class="card-header bg-warning border-0 py-3 d-flex align-items-center px-4">
                        <!-- 🟢 ปรับปรุง: เปลี่ยนสีพื้นหลังของวงกลมไอคอนเป็นสีดำโปร่งแสง (bg-dark bg-opacity-10) เพื่อให้ตัดกับพื้นหลังสีเหลือง -->
                        <div class="bg-dark bg-opacity-10 text-dark rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm" style="width: 42px; height: 42px;"> <i class="bi bi-box-seam fs-5"></i>
                        </div>
                        <h5 class="fw-bold mb-0 text-dark">รายการสิ่งของทั้งหมด</h5>
                    </div>
                    <div class="card-body p-0">

                        <!-- แถบเครื่องมือ: ตัวกรอง และ ค้นหา -->
                        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom border-light">
                            <div class="d-flex align-items-center gap-2">
                                <!-- 🟢 ปรับปรุง: เปลี่ยนจาก text-muted เป็น text-dark เพื่อให้หัวข้อ "ประเภท:" ดูกระทบสายตาชัดเจนขึ้น -->
                                <label class="fw-bold small mb-0 text-dark">ประเภท:</label>
                                <select id="categoryFilter" class="form-select form-select-sm border-0 rounded-pill shadow-sm px-3 py-2 text-muted" style="width: 180px; background-color: #f8f9fa;">
                                    <option value="all">ทั้งหมด</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="input-group input-group-sm shadow-sm rounded-pill overflow-hidden" style="width: 280px; background-color: #f8f9fa;">
                                <span class="input-group-text bg-transparent border-0 pe-2 ps-3">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" id="searchInput" class="form-control bg-transparent border-0 shadow-none ps-0 py-2 text-muted" placeholder="ค้นหาชื่อรายการ...">
                            </div>
                        </div>

                        <!-- ตารางข้อมูล -->
                        <div class="table-responsive px-4 pb-3 pt-2">
                            <!-- 🟢 เพิ่ม Style สำหรับ CSS Counter ตรงตาราง -->
                            <style>
                                table#itemsTable {
                                    counter-reset: rowNumber;
                                }

                                table#itemsTable tbody tr.item-row:not([style*="display: none"]) {
                                    counter-increment: rowNumber;
                                }

                                table#itemsTable tbody tr.item-row:not([style*="display: none"]) .row-number::before {
                                    content: counter(rowNumber);
                                }
                            </style>

                            <table class="table table-hover align-middle text-center mb-0 border-0 custom-striped" id="itemsTable">
                                <thead style="border-bottom: 2px solid #adb5bd;">
                                    <tr>
                                        <th class="text-start ps-4 py-3 border-0 fs-6" style="width: 40%; color: #000 !important; font-weight: 700 !important;">ชื่อรายการ</th>
                                        <th class="py-3 border-0 fs-6" style="width: 15%; color: #000 !important; font-weight: 700 !important;">รูปภาพ</th>
                                        <th class="py-3 border-0 fs-6" style="width: 25%; color: #000 !important; font-weight: 700 !important;">จำนวนปัจจุบัน</th>
                                        <th class="py-3 border-0 fs-6" style="width: 20%; color: #000 !important; font-weight: 700 !important;">คำสั่ง</th>
                                    </tr>
                                </thead>
                                <tbody class="border-top-0">
                                    <?php foreach ($items as $item): // เอา $index ออก เพราะเราไม่ใช้แล้ว 
                                    ?>
                                        <tr class="item-row" data-category="<?= htmlspecialchars($item['category_name']) ?>">
                                            <td class="text-start ps-3 fw-bold text-dark border-0 py-3">
                                                <!-- 🟢 คำสั่ง ใน span เป็นคลาส row-number เพื่อให้ CSS รันเลขอัตโนมัติ -->
                                                <span class="badge bg-light text-secondary rounded-circle me-3 d-inline-flex justify-content-center align-items-center shadow-sm row-number" style="width: 28px; height: 28px;"></span>
                                                <?= htmlspecialchars($item['name']) ?>
                                            </td>
                                            <td class="border-0 py-3">
                                                <?php
                                                $images = json_decode($item['images'], true);
                                                // [Refactored] Replaced hardcoded 'assets/uploads/items/' with UPLOAD_URL constant
                                                // [Refactored] Replaced hardcoded 'assets/images/placeholder.jpg' with PLACEHOLDER_URL constant
                                                $imgSrc = !empty($images) ? UPLOAD_URL . $images[0] : PLACEHOLDER_URL;
                                                ?>
                                                <img src="<?= $imgSrc ?>" class="rounded-4 shadow-sm object-fit-cover" style="width: 55px; height: 55px;">
                                            </td>
                                            <td class="border-0 py-3">
                                                <div class="d-inline-flex align-items-center bg-light rounded-pill px-4 py-2 shadow-sm">
                                                    <span class="me-3 fw-bold text-dark fs-6" id="stock-val-<?= $item['item_id'] ?>">
                                                        <?= number_format($item['current_stock']) ?>
                                                    </span>
                                                    <div class="vr mx-2 bg-secondary opacity-25"></div>
                                                    <i class="bi bi-plus-slash-minus text-warning fs-5 ms-1"
                                                        style="cursor: pointer; transition: 0.2s;"
                                                        title="คลิกเพื่อปรับจำนวนสต็อก"
                                                        onmouseover="this.classList.add('text-dark')" onmouseout="this.classList.remove('text-dark')"
                                                        onclick="openUpdateStockModal('<?= $item['item_id'] ?>', '<?= htmlspecialchars($item['name']) ?>', <?= $item['current_stock'] ?>)">
                                                    </i>
                                                </div>
                                            </td>
                                            <td class="border-0 py-3">
                                                <?php $imgJson = htmlspecialchars($item['images'] ?? '[]', ENT_QUOTES, 'UTF-8'); ?>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-sm text-info d-flex justify-content-center align-items-center border-0"
                                                        style="width: 36px; height: 36px; cursor: pointer;" title="ดูรายละเอียด"
                                                        data-id="<?= $item['item_id'] ?>"
                                                        data-name="<?= htmlspecialchars($item['name']) ?>"
                                                        data-desc="<?= htmlspecialchars($item['description']) ?>"
                                                        data-stock="<?= $item['current_stock'] ?>"
                                                        data-images="<?= $imgJson ?>"
                                                        onclick="openViewModal(this)">
                                                        <i class="bi bi-search"></i>
                                                    </button>

                                                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-sm text-dark d-flex justify-content-center align-items-center border-0"
                                                        style="width: 36px; height: 36px; cursor: pointer;" title="แก้ไขข้อมูลสิ่งของ"
                                                        data-id="<?= $item['item_id'] ?>"
                                                        data-category="<?= $item['category_id'] ?>"
                                                        data-name="<?= htmlspecialchars($item['name']) ?>"
                                                        data-stock="<?= $item['current_stock'] ?>"
                                                        data-desc="<?= htmlspecialchars($item['description']) ?>"
                                                        data-images="<?= $imgJson ?>"
                                                        onclick="openEditModal(this)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>

                                                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-sm text-danger d-flex justify-content-center align-items-center border-0"
                                                        style="width: 36px; height: 36px; cursor: pointer;" title="ลบสิ่งของ"
                                                        onclick="openDeleteModal('<?= $item['item_id'] ?>', '<?= htmlspecialchars($item['name']) ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
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
                        <div class="px-4 pb-4 pt-2">
                            <!-- แทรก Pagination เข้าไปใต้ตาราง และตั้ง ID ให้ Wrapper เช่น #mainItemsPagination -->
                            <div id="mainItemsPagination">
                                <?php include 'includes/pagination.php'; ?>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- นำเข้าการ์ดตารางประวัติการเปลี่ยนแปลงล่าสุด (Component แยก) -->
                <?php include 'components/tables/recent_transactions_table.php'; ?>

            </div>
        </div>
    </div>
</div>

<!-- Modal เพิ่มสิ่งของ และ อัปเดตสต็อก -->
<?php include 'components/modals/add_item_modal.php'; ?>
<?php include 'components/modals/update_stock_modal.php'; ?>
<?php include 'components/modals/view_item_modal.php'; ?>
<?php include 'components/modals/delete_item_modal.php'; ?>
<?php include 'components/modals/edit_item_modal.php'; ?>

<!-- 🟢 ดึงไฟล์ JavaScript แยกส่วน (Components) เข้ามาทำงาน -->
<!-- <script src="assets/js/manage_items.js"></script> -->
<script src="assets/js/transactions.js"></script>

<?php include 'includes/footer.php'; ?>




<!-- ดึงสคริปต์กลางมาใช้ -->
<script src="assets/js/table_pagination.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const catFilter = document.getElementById('categoryFilter');
        const searchBox = document.getElementById('searchInput');

        // เรียกใช้งานฟังก์ชัน Pagination
        initTablePagination({
            rowSelector: '.item-row', // Class ของแถวในตารางที่จะทำแบ่งหน้า
            wrapperSelector: '#mainItemsPagination', // ID ของตัวหุ้ม Pagination ชุดนี้
            noDataSelector: '#noDataRow', // ID ของแถวที่โชว์ตอนค้นหาไม่เจอ
            triggerInputs: [catFilter, searchBox], // ช่อง Input ที่พิมพ์ปุ๊บตารางต้องอัปเดตปั๊บ

            // โลจิกการกรอง (ใส่หรือไม่ใส่ก็ได้)
            filterLogic: function(row) {
                const search = searchBox.value.toLowerCase();
                const cat = catFilter.value;
                const rowCat = row.getAttribute('data-category');
                const rowName = row.querySelector('td:first-child').textContent.toLowerCase();

                return (cat === 'all' || rowCat === cat) && rowName.includes(search);
            }
        });
    });
</script>