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

// ดึงข้อมูลหมวดหมู่ทั้งหมด
$categories =$pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll();

// ดึงรายการสิ่งของทั้งหมดที่พร้อมให้เบิก
$items =$pdo->query("
    SELECT i.*, c.name as category_name 
    FROM items i 
    LEFT JOIN categories c ON i.category_id = c.category_id 
    WHERE i.current_stock > 0 
    ORDER BY c.name ASC, i.name ASC
")->fetchAll();
?>

<!-- 🟢 Header Section (เนื้อหาเริ่มตรงนี้เลย ไม่ต้องกาง Layout แล้ว) -->
<div class="mb-4 pb-2" style="border-bottom: 2px solid #e9ecef;">
    <h3 class="fw-bolder mb-1 text-dark">แบบฟอร์มสร้างคำขอ</h3>
    <span class="text-muted small"></span>
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

            <div class="d-flex gap-2">
                <select id="categoryFilter" class="form-select rounded-3 shadow-sm text-dark border-light bg-light" style="width: 200px;">
                    <option value="all">ทุกหมวดหมู่</option>
                    <?php foreach ($categories as$cat): ?>
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
            <!-- กริดสินค้า -->
            <div class="row g-3" id="itemsGrid">
                <?php foreach ($items as $item):$imgData = json_decode($item['images'] ?? '[]', true);$imgSrc = (is_array($imgData) && !empty($imgData)) ? 'assets/uploads/items/' . $imgData[0] : 'assets/images/placeholder.jpg';$imgJson = htmlspecialchars(json_encode(is_array($imgData) ?$imgData : []), ENT_QUOTES, 'UTF-8');
                ?>
                    <div class="col-md-3 item-card-wrapper" data-name="<?= htmlspecialchars(strtolower($item['name'])) ?>" data-category="<?= htmlspecialchars($item['category_name'] ?? '') ?>">
                        <div class="card h-100 border-0 rounded-4 shadow-sm bg-white" style="transition: 0.2s;" onmouseover="this.classList.add('shadow')" onmouseout="this.classList.remove('shadow')">
                            <div class="d-flex p-3 gap-3 h-100 align-items-center">
                                
                                <div style="width: 42%; flex-shrink: 0;" class="position-relative">
                                    <img src="<?= $imgSrc ?>" class="rounded-3 object-fit-cover border border-light w-100 shadow-sm"
                                        style="aspect-ratio: 4/3; max-height: 110px; cursor: pointer; transition: 0.2s;"
                                        onmouseover="this.style.opacity='0.8'"
                                        onmouseout="this.style.opacity='1'"
                                        onclick="openGalleryModal('<?= $imgJson ?>', '<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>')"
                                        title="คลิกเพื่อขยายรูปภาพ">
                                    <span class="position-absolute bottom-0 end-0 bg-dark text-white rounded-start-pill small px-2 py-1 opacity-75" style="font-size: 0.65rem; pointer-events: none;"><i class="bi bi-arrows-angle-expand"></i></span>
                                </div>
                                
                                <div class="d-flex flex-column justify-content-between h-100 flex-grow-1" style="min-width: 0;">
                                    <div>
                                        <div class="fw-bold text-dark text-truncate w-100" title="<?= htmlspecialchars($item['name']) ?>">
                                            <?= htmlspecialchars($item['name']) ?>
                                        </div>
                                        <div class="small text-muted text-truncate"><?= htmlspecialchars($item['category_name']) ?></div>
                                        <div class="small mt-1 text-success fw-bolder">คงเหลือ: <?= $item['current_stock'] ?> ชิ้น</div>
                                    </div>

                                    <div class="mt-2">
                                        <div class="input-group input-group-sm mb-2 shadow-sm rounded-2">
                                            <button class="btn btn-light border-secondary border-opacity-25 px-2 fw-bold text-dark" type="button" onclick="adjustInputQty('<?= $item['item_id'] ?>', -1)">-</button>
                                            <input type="number" id="input_qty_<?= $item['item_id'] ?>" class="form-control text-center fw-bold px-0 border-secondary border-opacity-25" value="1" min="1" max="<?= $item['current_stock'] ?>">
                                            <button class="btn btn-light border-secondary border-opacity-25 px-2 fw-bold text-dark" type="button" onclick="adjustInputQty('<?= $item['item_id'] ?>', 1, <?=$item['current_stock'] ?>)">+</button>
                                        </div>
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
            <span class="badge bg-success text-white rounded-pill px-3 py-2 fs-6 shadow-sm" id="cartTotalCount">0 รายการ</span>
        </div>
        <div class="card-body p-0">
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

<!-- Modal แสดง Gallery รูปภาพ (3 รูป) -->
<div class="modal fade" id="imageGalleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg bg-light">
            <div class="modal-header border-bottom-0 px-4 py-3">
                <h5 class="modal-title fw-bold text-dark" id="galleryTitle">รายละเอียดภาพ</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center bg-dark position-relative overflow-hidden" style="border-radius: 0 0 1rem 1rem;">
                <div id="itemCarousel" class="carousel slide" data-bs-ride="false">
                    <div class="carousel-inner" id="carouselImagesContainer">
                        <!-- JS จะแทรกรูปที่นี่ -->
                    </div>
                    <!-- ปุ่มเลื่อน -->
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

<script src="assets/js/cart_system.js"></script>

<!-- Script สำหรับ Filter & Pagination หน้าสินค้า -->
<script>
    const itemsPerPage = 8;
    let currentPage = 1;
    let allItemElements = [];
    let filteredItems = [];

    window.addEventListener('DOMContentLoaded', () => {
        allItemElements = Array.from(document.querySelectorAll('.item-card-wrapper'));
        document.getElementById('itemSearch').addEventListener('keyup', filterItems);
        document.getElementById('categoryFilter').addEventListener('change', filterItems);
        filterItems();
    });

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

        currentPage = 1; 
        renderPagination();
    }

    function renderPagination() {
        allItemElements.forEach(item => item.classList.add('d-none'));

        const totalItems = filteredItems.length;
        let totalPages = Math.ceil(totalItems / itemsPerPage);
        if (totalPages === 0) totalPages = 1;

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;

        for (let i = startIndex; i < endIndex && i < totalItems; i++) {
            filteredItems[i].classList.remove('d-none');
        }
        drawPaginationButtons(totalPages);
    }

    function drawPaginationButtons(totalPages) {
        const container = document.getElementById('paginationControls');

        if (filteredItems.length === 0) {
            container.innerHTML = `<div class="text-center py-5 text-muted">
                                    <i class="bi bi-box-seam fs-1 d-block mb-3 opacity-50"></i>
                                    <h5>ไม่พบสิ่งของที่ค้นหา</h5>
                                   </div>`;
            return;
        }

        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<ul class="pagination mb-0 shadow-sm">';
        html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link text-dark fw-bold bg-white" href="#" onclick="changePage(event, ${currentPage - 1})">&laquo;</a>
                 </li>`;

        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${currentPage === i ? 'active' : ''}">
                        <a class="page-link ${currentPage === i ? 'bg-warning border-warning text-dark' : 'text-dark bg-white'}" 
                           href="#" onclick="changePage(event, ${i})">${i}</a>
                     </li>`;
        }

        html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link text-dark fw-bold bg-white" href="#" onclick="changePage(event, ${currentPage + 1})">&raquo;</a>
                 </li>`;
        html += '</ul>';
        container.innerHTML = html;
    }

    function changePage(event, newPage) {
        event.preventDefault(); 
        currentPage = newPage;
        renderPagination();
        document.getElementById('itemsGrid').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Modal รูปภาพ
    function openGalleryModal(imgJsonStr, itemName) {
        document.getElementById('galleryTitle').innerText = itemName;
        let images = [];
        try { images = JSON.parse(imgJsonStr); } catch(e) {}
        
        const container = document.getElementById('carouselImagesContainer');
        container.innerHTML = ''; 

        if (images.length === 0) {
            container.innerHTML = `<div class="carousel-item active"><img src="assets/images/placeholder.jpg" class="d-block mx-auto img-fluid p-2" style="max-height: 60vh; object-fit: contain;"></div>`;
        } else {
            images.forEach((img, idx) => {
                const activeClass = idx === 0 ? 'active' : '';
                container.innerHTML += `<div class="carousel-item ${activeClass}"><img src="assets/uploads/items/${img}" class="d-block mx-auto img-fluid p-2" style="max-height: 60vh; object-fit: contain;"></div>`;
            });
        }
        var myModal = new bootstrap.Modal(document.getElementById('imageGalleryModal'));
        myModal.show();
    }
</script>