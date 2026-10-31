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
                                <span class="me-3 fw-bolder text-success fs-5" id="stock-val-<?= $item['id'] ?>">
                                    <?= number_format($item['current_stock']) ?>
                                </span>
                                <div class="vr mx-2 bg-secondary opacity-25"></div>
                                <button type="button" class="btn btn-sm btn-link text-warning p-0 ms-1 border-0" 
                                        title="ปรับจำนวนสต็อก"
                                        onclick="openUpdateStockModal('<?= $item['id'] ?>', '<?= htmlspecialchars($item['name']) ?>', <?= $item['current_stock'] ?>)">
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
                                    data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>"
                                    data-desc="<?= htmlspecialchars($item['description']) ?>" data-stock="<?= $item['current_stock'] ?>"
                                    data-images="<?= $imgJson ?>" onclick="openViewModal(this)">
                                    <i class="bi bi-search"></i>
                                </button>

                                <button type="button" class="btn btn-sm btn-light rounded-circle shadow-sm text-dark d-flex justify-content-center align-items-center border-0"
                                    style="width: 38px; height: 38px;" title="แก้ไขข้อมูล"
                                    data-id="<?= $item['id'] ?>" data-category="<?= $item['category_id'] ?>"
                                    data-name="<?= htmlspecialchars($item['name']) ?>" data-stock="<?= $item['current_stock'] ?>"
                                    data-desc="<?= htmlspecialchars($item['description']) ?>" data-images="<?= $imgJson ?>"
                                    onclick="openEditModal(this)">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <button type="button" class="btn btn-sm btn-light rounded-circle shadow-sm text-danger d-flex justify-content-center align-items-center border-0"
                                    style="width: 38px; height: 38px;" title="ลบรายการ"
                                    onclick="openDeleteModal('<?= $item['id'] ?>', '<?= htmlspecialchars($item['name']) ?>')">
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