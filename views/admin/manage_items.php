<?php
if (!defined('APP_RUNNING')) exit('Forbidden');

// ตรวจสอบสิทธิ์
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {$_SESSION['error'] = 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถเข้าถึงหน้านี้ได้';
    header("Location: index.php");
    exit;
}

// ดึงรายการหมวดหมู่
$categories =$pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// ดึงรายการสิ่งของ
$items =$pdo->query("
    SELECT i.*, c.name as category_name 
    FROM items i 
    LEFT JOIN categories c ON i.category_id = c.category_id 
    ORDER BY i.created_at DESC
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
                                    <?php foreach ($categories as$cat): ?>
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
                                    <?php foreach ($items as $index =>$item): ?>
                                    <!-- ซ่อน category ไว้ใน data attribute สำหรับให้ JS ใช้กรองข้อมูล -->
                                    <tr class="item-row" data-category="<?= htmlspecialchars($item['category_name']) ?>">
                                        <td class="text-start ps-4 fw-bold">
                                            <span class="me-3 text-muted"><?= $index + 1 ?></span> <?= htmlspecialchars($item['name']) ?>
                                        </td>
                                        <td>
                                            <!-- สมมติว่ามีรูปภาพ ถ้าไม่มีใช้ placeholder -->
                                            <?php 
                                                $images = json_decode($item['images'], true);$imgSrc = !empty($images) ? 'assets/uploads/items/' .$images[0] : 'assets/images/placeholder.jpg';
                                            ?>
                                            <img src="<?= $imgSrc ?>" height="40" class="border border-dark object-fit-cover" style="width: 60px;">
                                        </td>
                                        <td>
                                            <div class="d-inline-flex align-items-center border border-dark rounded-0 bg-white px-3 py-1">
                                                <span class="me-3 fw-bold" id="stock-val-<?= $item['item_id'] ?>">
                                                    <?= number_format($item['current_stock']) ?>
                                                </span>
                                                <i class="bi bi-pencil cursor-pointer" onclick="openUpdateStockModal('<?= $item['item_id'] ?>', '<?= htmlspecialchars($item['name']) ?>', <?=$item['current_stock'] ?>)"></i>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-dark rounded-0"><i class="bi bi-pencil"></i></button>
                                            <button class="btn btn-sm btn-outline-danger rounded-0"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr id="noDataRow" style="display: none;">
                                        <td colspan="4" class="text-center py-4 text-muted">ไม่พบข้อมูล</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- แถบแบ่งหน้า (Pagination) -->
                        <div class="d-flex justify-content-between align-items-center p-3 border-top">
                            <div class="d-flex align-items-center">
                                <span class="small text-muted me-2">Items per page:</span>
                                <select id="itemsPerPage" class="form-select form-select-sm border-dark rounded-0" style="width: 70px;">
                                    <option value="5">5</option>
                                    <option value="10" selected>10</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="small text-muted me-3" id="pageInfo">1 - 10 of 0</span>
                                <button class="btn btn-sm btn-light border-dark rounded-0 me-1" id="prevPage"><i class="bi bi-chevron-left"></i></button>
                                <button class="btn btn-sm btn-light border-dark rounded-0" id="nextPage"><i class="bi bi-chevron-right"></i></button>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal เพิ่มสิ่งของ และ อัปเดตสต็อก (ดึงไฟล์เดิมมาใช้) -->
<?php include 'components/modals/add_item_modal.php'; ?>
<?php include 'components/modals/update_stock_modal.php'; ?>

<!-- Script จัดการการกรองข้อมูลและแบ่งหน้า -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const rows = Array.from(document.querySelectorAll('.item-row'));
    const categoryFilter = document.getElementById('categoryFilter');
    const searchInput = document.getElementById('searchInput');
    const itemsPerPageSelect = document.getElementById('itemsPerPage');
    const prevPageBtn = document.getElementById('prevPage');
    const nextPageBtn = document.getElementById('nextPage');
    const pageInfo = document.getElementById('pageInfo');
    const noDataRow = document.getElementById('noDataRow');

    let currentPage = 1;
    let itemsPerPage = parseInt(itemsPerPageSelect.value);
    let filteredRows = [...rows];

    // ฟังก์ชันอัปเดตตาราง
    function updateTable() {
        const cat = categoryFilter.value;
        const search = searchInput.value.toLowerCase();

        // 1. กรองข้อมูล (หมวดหมู่ และ คำค้นหา)
        filteredRows = rows.filter(row => {
            const rowCat = row.getAttribute('data-category');
            const rowName = row.querySelector('td:first-child').textContent.toLowerCase();
            const matchCat = (cat === 'all' || rowCat === cat);
            const matchSearch = rowName.includes(search);
            return matchCat && matchSearch;
        });

        // 2. ซ่อนทุกแถวก่อน
        rows.forEach(row => row.style.display = 'none');

        // 3. คำนวณการแบ่งหน้า
        const totalItems = filteredRows.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, totalItems);

        // 4. แสดงเฉพาะแถวในหน้าที่เลือก
        for (let i = startIndex; i < endIndex; i++) {
            filteredRows[i].style.display = '';
        }

        // 5. อัปเดตข้อความ Pagination และปุ่ม
        if (totalItems === 0) {
            noDataRow.style.display = '';
            pageInfo.textContent = `0 - 0 of 0`;
        } else {
            noDataRow.style.display = 'none';
            pageInfo.textContent = `${startIndex + 1} - ${endIndex} of ${totalItems}`;
        }

        prevPageBtn.disabled = currentPage === 1;
        nextPageBtn.disabled = currentPage === totalPages;
    }

    // เพิ่ม Event Listeners ให้ส่วนควบคุมต่างๆ
    categoryFilter.addEventListener('change', () => { currentPage = 1; updateTable(); });
    searchInput.addEventListener('input', () => { currentPage = 1; updateTable(); });
    itemsPerPageSelect.addEventListener('change', (e) => { 
        itemsPerPage = parseInt(e.target.value); 
        currentPage = 1; 
        updateTable(); 
    });
    
    prevPageBtn.addEventListener('click', () => {
        if (currentPage > 1) { currentPage--; updateTable(); }
    });
    
    nextPageBtn.addEventListener('click', () => {
        const totalPages = Math.ceil(filteredRows.length / itemsPerPage);
        if (currentPage < totalPages) { currentPage++; updateTable(); }
    });

    // เรียกทำงานครั้งแรก
    updateTable();
});
</script>

<?php include 'includes/footer.php'; ?>

<!-- ดึงไฟล์ Modal เข้ามาใช้งาน -->
<?php include 'components/modals/add_item_modal.php'; ?>
<?php include 'components/modals/update_stock_modal.php'; ?>
<?php include 'includes/footer.php'; ?>