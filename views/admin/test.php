<!-- ดึง Sidebar สีเข้มมาแสดง -->
        <?php include 'includes/sidebar_admin.php'; ?>

        <!-- 🟢 ปรับปรุง: เปลี่ยนสีพื้นหลังเพจจาก #f5f6f8 เป็น #eef1f5 (เทาอมฟ้า) และเพิ่มมิติความลึกให้ตัดกับการ์ดสีขาวชัดเจนขึ้น -->
        <div class="col d-flex flex-column" style="min-height: 100vh; background-color: #eef1f5;">
            <div class="p-4 flex-grow-1">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold mb-1 text-dark">จัดการสิ่งของ</h4>
                        <span class="text-muted small">เพิ่ม แก้ไข และดูรายการสิ่งของทั้งหมดในคลัง</span>
                    </div>
                    <button class="btn btn-warning rounded-pill fw-bold px-4 py-2 shadow-sm border-0" data-bs-toggle="modal" data-bs-target="#addItemModal">
                        <i class="bi bi-plus-lg me-2"></i> เพิ่มรายการใหม่
                    </button>
                </div>

                <!-- 🟢 ปรับปรุง: เพิ่มระดับเงาจากการ์ด shadow-sm เป็น shadow ธรรมดา เพื่อให้ลอยเด่นจากพื้นหลังที่ปรับใหม่ -->
                <div class="card border-0 rounded-4 mb-5 shadow overflow-hidden bg-white">
                    
                    <div class="card-header bg-warning border-0 py-3 d-flex align-items-center px-4">
                        <div class="bg-dark bg-opacity-10 text-dark rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm" style="width: 42px; height: 42px;">
                            <i class="bi bi-box-seam fs-5"></i>
                        </div>
                        <h5 class="fw-bold mb-0 text-dark" style="letter-spacing: 0.5px;">รายการสิ่งของทั้งหมด</h5>
                    </div>
                    
                    <div class="card-body p-0">

                        <!-- แถบเครื่องมือ: ตัวกรอง และ ค้นหา -->
                        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom border-light">
                            <div class="d-flex align-items-center gap-2">
                                <label class="fw-bold small mb-0 text-muted">ประเภท:</label>
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
                            <!-- 🟢 ปรับปรุง: เติมคลาส table-striped เพื่อสร้างสีสลับแถว (Zebra striping) ให้อ่านข้อมูลง่ายขึ้น -->
                            <table class="table table-hover table-striped align-middle text-center mb-0 border-0" id="itemsTable">
                                <thead class="text-muted">
                                    <tr style="border-bottom: 2px solid #eef1f5;">
                                        <th class="text-start ps-3 fw-bold py-3 border-0" style="width: 40%;">ชื่อรายการ</th>
                                        <th class="fw-bold py-3 border-0" style="width: 15%;">รูปภาพ</th>
                                        <th class="fw-bold py-3 border-0" style="width: 25%;">จำนวนปัจจุบัน</th>
                                        <th class="fw-bold py-3 border-0" style="width: 20%;">คำสั่ง</th>
                                    </tr>
                                </thead>
                                <tbody class="border-top-0">
                                    <?php foreach ($items as $index => $item): ?>
                                        <!-- 🟢 ปรับปรุง: ลบ inline style border ออก เพราะ table-striped ทำหน้าที่แยกบรรทัดให้ชัดเจนแล้ว -->
                                        <tr class="item-row" data-category="<?= htmlspecialchars($item['category_name']) ?>">
                                            <td class="text-start ps-3 fw-bold text-dark border-0 py-3">
                                                <span class="badge bg-white text-secondary rounded-circle me-3 d-inline-flex justify-content-center align-items-center shadow-sm border border-light" style="width: 28px; height: 28px;"><?= $index + 1 ?></span> 
                                                <?= htmlspecialchars($item['name']) ?>
                                            </td>
                                            <td class="border-0 py-3">
                                                <?php
                                                $images = json_decode($item['images'], true);
                                                // [Refactored] Replaced hardcoded 'assets/uploads/items/' with UPLOAD_URL constant
                                                // [Refactored] Replaced hardcoded 'assets/images/placeholder.jpg' with PLACEHOLDER_URL constant
                                                $imgSrc = !empty($images) ? UPLOAD_URL . $images[0] : PLACEHOLDER_URL;
                                                ?>
                                                <img src="<?= $imgSrc ?>" class="rounded-4 shadow-sm object-fit-cover bg-white p-1" style="width: 55px; height: 55px;">
                                            </td>
                                            <td class="border-0 py-3">
                                                <div class="d-inline-flex align-items-center bg-white rounded-pill px-4 py-2 shadow-sm border border-light">
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
                                                    <!-- 🟢 ปรับปรุง: เปลี่ยนพื้นหลังปุ่มเป็นสีขาวล้วนให้ลอยเด่นขึ้นมาจากพื้นหลังลายทางของตาราง -->
                                                    <button type="button" class="btn btn-sm bg-white rounded-circle shadow-sm text-info d-flex justify-content-center align-items-center border border-light"
                                                        style="width: 36px; height: 36px; cursor: pointer;" title="ดูรายละเอียด"
                                                        data-id="<?= $item['item_id'] ?>"
                                                        data-name="<?= htmlspecialchars($item['name']) ?>"
                                                        data-desc="<?= htmlspecialchars($item['description']) ?>"
                                                        data-stock="<?= $item['current_stock'] ?>"
                                                        data-images="<?= $imgJson ?>"
                                                        onclick="openViewModal(this)">
                                                        <i class="bi bi-search"></i>
                                                    </button>
                                                    
                                                    <button type="button" class="btn btn-sm bg-white rounded-circle shadow-sm text-dark d-flex justify-content-center align-items-center border border-light"
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

                                                    <button type="button" class="btn btn-sm bg-white rounded-circle shadow-sm text-danger d-flex justify-content-center align-items-center border border-light"
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
                                            <div class="bg-white border rounded-circle d-inline-flex justify-content-center align-items-center mb-3 shadow-sm" style="width: 70px; height: 70px;">
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
                            <?php include 'includes/pagination.php'; ?>
                        </div>

                    </div>
                </div>