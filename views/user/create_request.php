<?php
if (!defined('APP_RUNNING')) exit('Forbidden');

// ดึงข้อมูลผู้ใช้ปัจจุบันและแผนก
$stmtUser = $pdo->prepare("
    SELECT u.*, d.department_name 
    FROM users u 
    LEFT JOIN departments d ON u.department_id = d.department_id 
    WHERE u.user_id = ?
");
$stmtUser->execute([$_SESSION['user_id']]);
$user =$stmtUser->fetch();

// 🟢 ดึงข้อมูลหมวดหมู่ทั้งหมด
$categories =$pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll();

// 🟢 ดึงรายการสิ่งของทั้งหมดที่พร้อมให้เบิก
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
            <span class="text-muted small">กรอกข้อมูลวัตถุประสงค์และรายละเอียดการนำไปใช้ก่อนเลือกรายการพัสดุ</span>

            <form action="actions/user/submit_request.php" method="POST" id="requestForm" class="mt-4">
                
                <!-- ข้อมูลทั่วไป -->
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
                                    <option value="โครงการ">โครงการ</option>
                                    <option value="งาน">งาน</option>
                                    <option value="สัมมนา">สัมมนา</option>
                                    <option value="อบรม">อบรม</option>
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

                <!-- 🟢 ส่วนที่ 2: เลือกสิ่งของ -->
                <div class="card border-0 rounded-4 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-box-seam me-2"></i>เลือกสิ่งของ</h5>
                        
                        <div class="d-flex gap-2">
                            <!-- Dropdown กรองหมวดหมู่ -->
                            <select id="categoryFilter" class="form-select rounded-pill shadow-none fw-bold text-dark border-dark" style="width: 200px;">
                                <option value="all">ทุกหมวดหมู่</option>
                                <?php foreach ($categories as$cat): ?>
                                    <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <!-- ช่องค้นหา -->
                            <div class="input-group" style="width: 250px;">
                                <span class="input-group-text bg-white rounded-start-pill border-end-0 border-dark"><i class="bi bi-search"></i></span>
                                <input type="text" id="itemSearch" class="form-control rounded-end-pill border-start-0 border-dark shadow-none" placeholder="ค้นหาชื่อ...">
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4 bg-light">
                        <!-- กริดสินค้า -->
                        <div class="row g-3" id="itemsGrid">
                            <?php foreach ($items as$item): ?>
                                <?php
                                $imgData = json_decode($item['images'] ?? '[]', true);
                                $imgSrc = (is_array($imgData) && !empty($imgData)) ? 'assets/uploads/items/' .$imgData[0] : 'assets/images/placeholder.jpg';
                                ?>
                                <!-- คลาส item-card-wrapper สำหรับให้ JS จัดการ -->
                                <div class="col-md-3 item-card-wrapper" data-name="<?= htmlspecialchars(strtolower($item['name'])) ?>" data-category="<?= htmlspecialchars($item['category_name'] ?? '') ?>">
                                    <div class="card h-100 border-secondary border-opacity-25 rounded-4 shadow-sm bg-white">
                                        <div class="d-flex p-3 gap-3 h-100">
                                            <img src="<?= $imgSrc ?>" class="rounded-3 object-fit-cover border" style="width: 80px; height: 100px;">
                                            <div class="d-flex flex-column justify-content-between w-100">
                                                <div>
                                                    <div class="fw-bold text-dark text-truncate" style="max-width: 120px;" title="<?= htmlspecialchars($item['name']) ?>"><?= htmlspecialchars($item['name']) ?></div>
                                                    <div class="small text-muted"><?= htmlspecialchars($item['category_name']) ?></div>
                                                    <div class="small mt-1 text-success fw-semibold">คงเหลือ: <?= $item['current_stock'] ?> ชิ้น</div>
                                                </div>
                                                
                                                <div class="mt-2">
                                                    <div class="input-group input-group-sm mb-2">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="adjustInputQty('<?= $item['item_id'] ?>', -1)">-</button>
                                                        <input type="number" id="input_qty_<?= $item['item_id'] ?>" class="form-control text-center fw-bold" value="1" min="1" max="<?= $item['current_stock'] ?>">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="adjustInputQty('<?= $item['item_id'] ?>', 1, <?=$item['current_stock'] ?>)">+</button>
                                                    </div>
                                                    <button type="button" class="btn btn-warning w-100 fw-bold rounded-3 btn-sm shadow-sm"
                                                        onclick="addToCart('<?= $item['item_id'] ?>', '<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($item['category_name'] ?? '', ENT_QUOTES) ?>', '<?= $imgSrc ?>', <?= $item['current_stock'] ?>)">
                                                        เพิ่มลงรายการ
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- จุดแสดงปุ่มเปลี่ยนหน้า -->
                        <div id="paginationControls" class="mt-4"></div>
                    </div>
                </div>

                <!-- ส่วนที่ 3: ตะกร้ารายการที่ขอเบิก -->
                <div class="card border-0 rounded-4 shadow-sm mb-5 border-warning border-top border-4">
                    <div class="card-header bg-warning bg-opacity-10 py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-cart-check me-2"></i>รายการที่ขอเบิกทั้งหมด</h5>
                        <span class="badge bg-warning text-dark rounded-pill fs-6" id="cartTotalCount">0 รายการ</span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0" id="cartTable">
                            <tbody id="cartBody">
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

                <div id="hiddenCartInputs"></div>

                <div class="d-flex justify-content-between mb-5">
                    <a href="index.php?page=home" class="btn btn-outline-dark fw-bold px-4 py-2 rounded-3 shadow-sm bg-white">
                        <i class="bi bi-arrow-left me-2"></i>ยกเลิก
                    </a>
                    <button type="button" class="btn btn-warning fw-bold px-5 py-2 rounded-3 shadow" onclick="submitRequest()">
                        บันทึก <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="assets/js/cart_system.js"></script>

<!-- 🟢 Script สำหรับ Filter & Pagination แบบเสถียรที่สุด -->
<script>
    const itemsPerPage = 8; // จำนวนการ์ดต่อ 1 หน้า
    let currentPage = 1;
    let allItemElements = [];
    let filteredItems = [];

    // เมื่อโหลดหน้าเว็บเสร็จ
    window.addEventListener('DOMContentLoaded', () => {
        allItemElements = Array.from(document.querySelectorAll('.item-card-wrapper'));
        
        // ผูก Event Listener เข้ากับช่องค้นหา และ Dropdown
        document.getElementById('itemSearch').addEventListener('keyup', filterItems);
        document.getElementById('categoryFilter').addEventListener('change', filterItems);
        
        // เรียกใช้ครั้งแรกเพื่อจัดหน้า
        filterItems();
    });

    // 1. ฟังก์ชันคัดกรองข้อมูล
    function filterItems() {
        const searchText = document.getElementById('itemSearch').value.toLowerCase().trim();
        const category = document.getElementById('categoryFilter').value.trim();

        filteredItems = allItemElements.filter(item => {
            const itemName = (item.getAttribute('data-name') || '').toLowerCase();
            const itemCat = (item.getAttribute('data-category') || '').trim();
            
            const matchSearch = itemName.includes(searchText);
            const matchCat = (category === 'all') || (itemCat === category);
            
            return matchSearch && matchCat;
        });

        currentPage = 1; // เวลากรองใหม่ ให้กลับไปหน้า 1 เสมอ
        renderPagination();
    }

    // 2. ฟังก์ชันแสดงผลการ์ดและปุ่มหน้า
    function renderPagination() {
        // ซ่อนการ์ดทั้งหมดก่อน โดยใช้คลาส d-none ของ Bootstrap
        allItemElements.forEach(item => {
            item.classList.add('d-none');
        });

        const totalItems = filteredItems.length;
        let totalPages = Math.ceil(totalItems / itemsPerPage);
        if (totalPages === 0) totalPages = 1;

        // คำนวณช่วงของ Index ที่จะแสดง
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;

        // ถอดคลาส d-none ออก เฉพาะตัวที่อยู่ในช่วงหน้าปัจจุบัน
        for (let i = startIndex; i < endIndex && i < totalItems; i++) {
            filteredItems[i].classList.remove('d-none');
        }

        // วาดปุ่ม Pagination
        drawPaginationButtons(totalPages);
    }

    // 3. ฟังก์ชันวาดปุ่มกดเปลี่ยนหน้า
    function drawPaginationButtons(totalPages) {
        const container = document.getElementById('paginationControls');
        
        // ถ้าค้นหาไม่เจออะไรเลย
        if (filteredItems.length === 0) {
            container.innerHTML = `<div class="text-center py-5 text-muted">
                                    <i class="bi bi-box-seam fs-1 d-block mb-3 opacity-50"></i>
                                    <h5>ไม่พบสิ่งของที่ค้นหา</h5>
                                   </div>`;
            return;
        }

        // ถ้ามีแค่หน้าเดียว ไม่ต้องแสดงปุ่ม
        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<ul class="pagination justify-content-center mb-0 shadow-sm">';
        
        // ปุ่ม ย้อนกลับ (Prev)
        html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link text-dark fw-bold bg-light" href="#" onclick="changePage(event, ${currentPage - 1})">&laquo; ก่อนหน้า</a>
                 </li>`;

        // ปุ่มตัวเลข (Numbers)
        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${currentPage === i ? 'active' : ''}">
                        <a class="page-link ${currentPage === i ? 'bg-warning border-warning text-dark fw-bold' : 'text-dark bg-light'}" 
                           href="#" onclick="changePage(event, ${i})">${i}</a>
                     </li>`;
        }

        // ปุ่ม ถัดไป (Next)
        html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link text-dark fw-bold bg-light" href="#" onclick="changePage(event, ${currentPage + 1})">ถัดไป &raquo;</a>
                 </li>`;
                 
        html += '</ul>';
        container.innerHTML = html;
    }

    // 4. ฟังก์ชันเมื่อกดเปลี่ยนหน้า
    function changePage(event, newPage) {
        event.preventDefault(); // ป้องกันหน้าเว็บกระตุก หรือรีเฟรช
        currentPage = newPage;
        renderPagination();
        
        // เลื่อนจอไปที่ตารางสินค้าเบาๆ
        document.getElementById('itemsGrid').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
</script>

<?php include 'includes/footer.php'; ?>