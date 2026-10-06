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
$user = $stmtUser->fetch();

// ดึงข้อมูลหมวดหมู่ทั้งหมด
$categories = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll();

// ดึงรายการสิ่งของทั้งหมดที่พร้อมให้เบิก
$items = $pdo->query("
    SELECT i.*, c.name as category_name 
    FROM items i 
    LEFT JOIN categories c ON i.category_id = c.category_id 
    WHERE i.current_stock > 0 
    ORDER BY c.name ASC, i.name ASC
")->fetchAll();
?>

<!-- 🟢 1. สร้างคลาส CSS เฉพาะกิจ สำหรับจัดวาง 5 กล่องต่อแถว บนหน้าจอขนาดใหญ่ (lg ขึ้นไป) -->
<style>
    @media (min-width: 992px) {
        .col-lg-20 {
            flex: 0 0 auto;
            width: 20%;
        }
    }
</style>

<!-- Header Section (ไม่มีการคลุม Layout ตามระบบ Master Layout ของคุณ) -->
<div class="mb-4 pb-2" style="border-bottom: 2px solid #e9ecef;">
    <h3 class="fw-bolder mb-1 text-dark">แบบฟอร์มสร้างคำขอ</h3>
    <span class="text-muted small">ระบบสิ่งพิมพ์และของที่ระลึก</span>
</div>

<form action="actions/user/submit_request.php" method="POST" id="requestForm">

    <!-- 📦 ส่วนที่ 1: ข้อมูลทั่วไป -->
    <div class="card border-0 rounded-4 shadow-sm mb-5 border-warning border-top border-4 bg-white">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center rounded-top-4">
            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-file-earmark-text me-2 text-warning"></i>ข้อมูลทั่วไป</h5>
        </div>
        <div class="card-body p-4">
            
            <div class="row mb-3">
                <div class="col-md-3 text-md-end fw-bold text-muted pt-1">สถานะ:</div>
                <div class="col-md-9"><span class="badge bg-warning text-dark px-3 py-2 rounded-pill shadow-sm">รอพิจารณา</span></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3 text-md-end fw-bold text-muted pt-1">วันที่:</div>
                <div class="col-md-9 fw-semibold"><?= date('d/m/Y') ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3 text-md-end fw-bold text-muted pt-1">ผู้เบิก:</div>
                <div class="col-md-9 fw-semibold"><?= htmlspecialchars($user['full_name'] ?? '-') ?></div>
            </div>
            <div class="row mb-4">
                <div class="col-md-3 text-md-end fw-bold text-muted pt-1">สังกัด/หน่วยงาน:</div>
                <div class="col-md-9 fw-semibold"><?= htmlspecialchars($user['department_name'] ?? '-') ?></div>
            </div>

            <hr class="text-muted opacity-25 mb-4">

            <div class="row mb-3 align-items-center">
                <div class="col-md-3 text-md-end fw-bold text-dark"><span class="text-danger">*</span> ใช้วันที่:</div>
                <div class="col-md-6">
                    <input type="date" name="use_date" class="form-control rounded-3 border-secondary border-opacity-25" required>
                </div>
            </div>
            <div class="row mb-3 align-items-center">
                <div class="col-md-3 text-md-end fw-bold text-dark"><span class="text-danger">*</span> ประเภทของงาน:</div>
                <div class="col-md-6">
                    <select name="event_type" class="form-select rounded-3 border-secondary border-opacity-25" required>
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
                <div class="col-md-3 text-md-end fw-bold text-dark"><span class="text-danger">*</span> ชื่องาน/โครงการ:</div>
                <div class="col-md-9">
                    <input type="text" name="event_name" class="form-control rounded-3 border-secondary border-opacity-25" required>
                </div>
            </div>
            <div class="row mb-3 align-items-center">
                <div class="col-md-3 text-md-end fw-bold text-dark"><span class="text-danger">*</span> สถานที่นำไปใช้:</div>
                <div class="col-md-9">
                    <input type="text" name="location" class="form-control rounded-3 border-secondary border-opacity-25" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3 text-md-end fw-bold text-dark pt-1"><span class="text-danger">*</span> วัตถุประสงค์เพื่อใช้งาน:</div>
                <div class="col-md-9">
                    <textarea name="purpose" class="form-control rounded-3 border-secondary border-opacity-25" rows="3" placeholder="ระบุวัตถุประสงค์..." required></textarea>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3 text-md-end fw-bold text-dark pt-1">หมายเหตุ (ไม่บังคับ):</div>
                <div class="col-md-9">
                    <textarea name="user_note" class="form-control rounded-3 border-secondary border-opacity-25" rows="2" placeholder="เพิ่มหมายเหตุเพิ่มเติม..."></textarea>
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
            <!-- 🟢 2. เรียกใช้คลาส col-lg-20 ที่เรากำหนด CSS ไว้ด้านบนเพื่อให้เรียง 5 กล่องต่อแถว -->
            <div class="row g-3" id="itemsGrid">
                <?php foreach ($items as $item):
                    // ดึงภาพจากฐานข้อมูล และจัดรูปแบบให้อยู่ในรูป JSON สำหรับส่งให้ Popup Gallery
                    $imgData = json_decode($item['images'] ?? '[]', true);
                    $imgSrc = (is_array($imgData) && !empty($imgData)) ? 'assets/uploads/items/' . $imgData[0] : 'assets/images/placeholder.jpg';
                    $imgJson = htmlspecialchars(json_encode(is_array($imgData) ? $imgData : []), ENT_QUOTES, 'UTF-8');
                ?>
                    <!-- กำหนดคลาสเพื่อการค้นหาและการจัดเรียง Responsive -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-20 item-card-wrapper" data-name="<?= htmlspecialchars(strtolower($item['name'])) ?>" data-category="<?= htmlspecialchars($item['category_name'] ?? '') ?>">
                        <div class="card h-100 border-0 rounded-4 shadow-sm bg-white" style="transition: 0.2s;" onmouseover="this.classList.add('shadow')" onmouseout="this.classList.remove('shadow')">
                            
                            <!-- จัด Layout กล่องสินค้าเป็นแนวตั้ง เพื่อให้เหมาะสมกับความแคบของกล่อง (5 กล่องต่อแถว) -->
                            <div class="d-flex flex-column p-3 h-100">
                                
                                <!-- โซนรูปภาพ (กดเพื่อขยาย) -->
                                <div class="position-relative w-100 mb-3 text-center">
                                    <img src="<?= $imgSrc ?>" class="rounded-3 object-fit-cover border border-light w-100 shadow-sm"
                                        style="aspect-ratio: 4/3; max-height: 120px; cursor: pointer; transition: 0.2s;"
                                        onmouseover="this.style.opacity='0.8'"
                                        onmouseout="this.style.opacity='1'"
                                        onclick="openGalleryModal('<?= $imgJson ?>', '<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>')"
                                        title="คลิกเพื่อขยายรูปภาพ">
                                    <span class="position-absolute bottom-0 end-0 bg-dark text-white rounded-start-pill small px-2 py-1 opacity-75" style="font-size: 0.65rem; pointer-events: none;"><i class="bi bi-arrows-angle-expand"></i></span>
                                </div>
                                
                                <!-- โซนรายละเอียดและปุ่มหยิบใส่ตะกร้า -->
                                <div class="d-flex flex-column justify-content-between flex-grow-1" style="min-width: 0;">
                                    <div class="mb-2">
                                        <!-- แสดงชื่อสินค้าแบบตัดคำอัตโนมัติเมื่อยาวเกิน -->
                                        <div class="fw-bold text-dark text-truncate w-100" title="<?= htmlspecialchars($item['name']) ?>">
                                            <?= htmlspecialchars($item['name']) ?>
                                        </div>
                                        <div class="small text-muted text-truncate"><?= htmlspecialchars($item['category_name']) ?></div>
                                    </div>

                                    <div class="mt-auto">
                                        <!-- กลุ่มปุ่มเพิ่ม/ลด จำนวนก่อนหยิบ -->
                                        <div class="input-group input-group-sm mb-2 shadow-sm rounded-2">
                                            <!-- ส่ง maxStock ให้ฟังก์ชันลดจำนวนด้วย เพื่อควบคุม -->
                                            <button class="btn btn-light border-secondary border-opacity-25 px-2 fw-bold text-dark" type="button" onclick="adjustInputQty('<?= $item['item_id'] ?>', -1, <?= $item['current_stock'] ?>)">-</button>
                                            
                                            <!-- กล่องตัวเลขระบุจำนวน และจำกัดไม่ให้พิมพ์ตัวเลขเกินจำนวนสต๊อก -->
                                            <input type="number" id="input_qty_<?= $item['item_id'] ?>" class="form-control text-center fw-bold px-0 border-secondary border-opacity-25" value="1" min="1" max="<?= $item['current_stock'] ?>">
                                            
                                            <!-- ส่ง maxStock ให้ฟังก์ชันเพิ่มจำนวน -->
                                            <button class="btn btn-light border-secondary border-opacity-25 px-2 fw-bold text-dark" type="button" onclick="adjustInputQty('<?= $item['item_id'] ?>', 1, <?=$item['current_stock'] ?>)">+</button>
                                        </div>
                                        
                                        <!-- 🟢 3. ปุ่มเพิ่มลงรายการ แก้ไขฟังก์ชันให้ส่งค่าของ maxStock เข้าไปด้วย! -->
                                        <button type="button" class="btn btn-warning w-100 fw-bold rounded-2 btn-sm shadow-sm" onclick="addToCart('<?= $item['item_id'] ?>', '<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($item['category_name'] ?? '', ENT_QUOTES) ?>', '<?= $imgSrc ?>', <?= $item['current_stock'] ?>)">
                                            <i class="bi bi-plus-lg me-1"></i> เพิ่มลงรายการ
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- จุดแสดงปุ่มเปลี่ยนหน้า -->
            <div id="paginationControls" class="mt-4 d-flex justify-content-center"></div>
        </div>
    </div>

    <!-- 📦 ส่วนที่ 3: ตะกร้ารายการที่ขอเบิก -->
    <div class="card border-0 rounded-4 shadow-sm mb-5 border-success border-top border-4 bg-white">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-cart-check me-2 text-success"></i>รายการในตะกร้า</h5>
            <!-- ป้ายนับจำนวนรวม -->
            <span class="badge bg-success text-white rounded-pill px-3 py-2 fs-6 shadow-sm" id="cartTotalCount">0 รายการ</span>
        </div>
        <div class="card-body p-0">
            <!-- ตารางแสดงรายการตะกร้า (ถูกจัดการเนื้อหาภายในโดย cart_system.js) -->
            <table class="table table-hover align-middle mb-0" id="cartTable">
                <tbody id="cartBody">
                    <tr id="emptyCartRow">
                        <td colspan="4" class="text-center py-5 text-muted border-0">
                            <div class="bg-light rounded-circle d-inline-flex justify-content-center align-items-center mb-3 shadow-sm" style="width: 70px; height: 70px;">
                                <i class="bi bi-cart-x fs-2 text-secondary opacity-50"></i>
                            </div>
                            <h6 class="fw-bold mb-0">ยังไม่มีรายการสิ่งของในตะกร้า</h6>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- พื้นที่สำหรับสร้าง Input (Hidden) ไว้ส่งค่าตะกร้าไปยัง PHP เมื่อกดปุ่ม Submit -->
    <div id="hiddenCartInputs"></div>

    <div class="d-flex justify-content-end gap-3 mb-5">
        <a href="index.php?page=home" class="btn btn-outline-secondary fw-bold px-4 py-2 rounded-pill shadow-sm bg-white">
            <i class="bi bi-arrow-left me-2"></i>ยกเลิก
        </a>
        <button type="button" class="btn btn-warning fw-bold px-5 py-2 rounded-pill shadow" onclick="submitRequest()">
            <i class="bi bi-save me-2"></i>บันทึกคำขอ
        </button>
    </div>

</form>

<!-- 🖼️ Modal แสดง Gallery รูปภาพ (สามารถเลื่อนดูได้ 3 รูป) -->
<div class="modal fade" id="imageGalleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg bg-light">
            <div class="modal-header border-bottom-0 px-4 py-3">
                <h5 class="modal-title fw-bold text-dark" id="galleryTitle">รายละเอียดภาพ</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center bg-dark position-relative overflow-hidden" style="border-radius: 0 0 1rem 1rem;">
                <div id="itemCarousel" class="carousel slide" data-bs-ride="false">
                    
                    <!-- ส่วนคอนเทนเนอร์บรรจุภาพ (จะถูกเติมด้วย JavaScript ฟังก์ชัน openGalleryModal) -->
                    <div class="carousel-inner" id="carouselImagesContainer">
                        <!-- JS จะแทรกรูปที่นี่ -->
                    </div>
                    
                    <!-- ปุ่มเลื่อน ซ้าย/ขวา -->
                    <button class="carousel-control-prev" type="button" data-bs-target="#itemCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#itemCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- เรียกใช้ไฟล์ระบบตะกร้าที่แก้ไขใหม่เรียบร้อยแล้ว -->
<script src="assets/js/cart_system.js"></script>

<!-- Script สำหรับ Filter ค้นหา และ Pagination (แบ่งหน้าแสดงผลสินค้า) -->
<script>
    const itemsPerPage = 10; // กำหนดให้แสดงหน้าละ 10 ชิ้น (สามารถเปลี่ยนเป็น 15 หรือ 20 ได้)
    let currentPage = 1;
    let allItemElements = [];
    let filteredItems = [];

    // เมื่อโหลดหน้าเว็บเสร็จ
    window.addEventListener('DOMContentLoaded', () => {
        // ค้นหากล่องสินค้าทั้งหมดเพื่อเตรียมข้อมูลทำ Filter และ Pagination
        allItemElements = Array.from(document.querySelectorAll('.item-card-wrapper'));
        
        // ผูก Event ทันทีที่ผู้ใช้พิมพ์ค้นหา หรือเปลี่ยนหมวดหมู่ 
        document.getElementById('itemSearch').addEventListener('keyup', filterItems);
        document.getElementById('categoryFilter').addEventListener('change', filterItems);
        
        // รันฟังก์ชันครั้งแรกเพื่อจัดหน้า
        filterItems();
    });

    // ฟังก์ชันในการค้นหา และคัดแยกสินค้า
    function filterItems() {
        const searchText = document.getElementById('itemSearch').value.toLowerCase().trim();
        const category = document.getElementById('categoryFilter').value.trim();

        // กรองเอาเฉพาะข้อมูลที่มีชื่อหรือหมวดหมู่ตรงกับที่ต้องการค้นหา
        filteredItems = allItemElements.filter(item => {
            const itemName = (item.getAttribute('data-name') || '').toLowerCase();
            const itemCat = (item.getAttribute('data-category') || '').trim();
            
            const matchSearch = itemName.includes(searchText);
            const matchCat = (category === 'all') || (itemCat === category);
            
            return matchSearch && matchCat;
        });

        // หลังจากค้นหาเสร็จให้กลับมาหน้า 1 เสมอ
        currentPage = 1; 
        renderPagination(); // เรียกใช้วาดหน้า
    }

    // ฟังก์ชันคำนวณและแสดงผลกล่องสินค้าสำหรับหน้าปัจจุบัน
    function renderPagination() {
        // สั่งซ่อนการ์ดทั้งหมดก่อนเพื่อรอจัดระเบียบ
        allItemElements.forEach(item => item.classList.add('d-none'));

        const totalItems = filteredItems.length;
        let totalPages = Math.ceil(totalItems / itemsPerPage);
        if (totalPages === 0) totalPages = 1;

        // คำนวณขอบเขตในการดึงข้อมูลมาแสดงตามหน้า
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;

        // ถอดคลาส d-none เฉพาะสินค้าที่จะแสดงในหน้านี้
        for (let i = startIndex; i < endIndex && i < totalItems; i++) {
            filteredItems[i].classList.remove('d-none');
        }
        
        // สร้างปุ่มเปลี่ยนหน้า
        drawPaginationButtons(totalPages);
    }

    // ฟังก์ชันสร้างปุ่ม Pagination (1, 2, 3...)
    function drawPaginationButtons(totalPages) {
        const container = document.getElementById('paginationControls');

        // ถ้าค้นหาไม่พบ จะแสดงข้อความแจ้งเตือนแทน
        if (filteredItems.length === 0) {
            container.innerHTML = `<div class="text-center py-5 text-muted">
                                    <i class="bi bi-box-seam fs-1 d-block mb-3 opacity-50"></i>
                                    <h5>ไม่พบสิ่งของที่ค้นหา</h5>
                                   </div>`;
            return;
        }

        // ถ้ามีแค่หน้าเดียว (สินค้าไม่เกินเกณฑ์) ให้ซ่อนปุ่มไว้
        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<ul class="pagination mb-0 shadow-sm">';
        
        // ปุ่มหน้าก่อนหน้า (ย้อนกลับ)
        html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link text-dark fw-bold bg-white" href="#" onclick="changePage(event, ${currentPage - 1})">&laquo;</a>
                 </li>`;

        // ปุ่มตัวเลขเรียงหน้า
        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${currentPage === i ? 'active' : ''}">
                        <a class="page-link ${currentPage === i ? 'bg-warning border-warning text-dark' : 'text-dark bg-white'}" 
                           href="#" onclick="changePage(event, ${i})">${i}</a>
                     </li>`;
        }

        // ปุ่มหน้าถัดไป
        html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link text-dark fw-bold bg-white" href="#" onclick="changePage(event, ${currentPage + 1})">&raquo;</a>
                 </li>`;
        html += '</ul>';
        container.innerHTML = html;
    }

    // ฟังก์ชันจัดการจังหวะที่ผู้ใช้คลิกเปลี่ยนหน้า
    function changePage(event, newPage) {
        event.preventDefault(); 
        currentPage = newPage;
        renderPagination();
        // เลื่อนหน้าจอกลับมาด้านบนสุดของโซนสินค้าอย่างนุ่มนวล
        document.getElementById('itemsGrid').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // 🟢 ฟังก์ชัน Modal สำหรับแสดง Gallery รูปภาพ
    function openGalleryModal(imgJsonStr, itemName) {
        // เปลี่ยนชื่อหัวข้อ Modal ให้ตรงกับสินค้าที่กด
        document.getElementById('galleryTitle').innerText = itemName;
        
        let images = [];
        // ป้องกันข้อผิดพลาด (try-catch) เวลาแปลง JSON
        try { images = JSON.parse(imgJsonStr); } catch(e) {}
        
        const container = document.getElementById('carouselImagesContainer');
        container.innerHTML = ''; // ล้างข้อมูลเก่าทิ้ง

        // ถ้าไม่มีภาพ ให้แสดงรูป Placeholder ชั่วคราว
        if (images.length === 0) {
            container.innerHTML = `<div class="carousel-item active"><img src="assets/images/placeholder.jpg" class="d-block mx-auto img-fluid p-2" style="max-height: 60vh; object-fit: contain;"></div>`;
        } else {
            // ถ้ามีรูป ให้วนลูปสร้างคลาส carousel-item รองรับกี่รูปก็ได้ (ตามข้อมูลที่มีในฐานข้อมูล)
            images.forEach((img, idx) => {
                const activeClass = idx === 0 ? 'active' : '';
                container.innerHTML += `<div class="carousel-item ${activeClass}"><img src="assets/uploads/items/${img}" class="d-block mx-auto img-fluid p-2" style="max-height: 60vh; object-fit: contain;"></div>`;
            });
        }
        
        // คำสั่งสั่งเปิด Modal ด้วย Bootstrap API
        var myModal = new bootstrap.Modal(document.getElementById('imageGalleryModal'));
        myModal.show();
    }
</script>