<!-- Modal ป๊อปอัปสำหรับแก้ไขสิ่งของ -->
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <form action="actions/admin/item_edit.php" method="POST" enctype="multipart/form-data" class="modal-content border-dark rounded-0" style="border-width: 2px !important;">
            
            <div class="modal-header border-dark" style="border-bottom-width: 2px !important;">
                <h5 class="modal-title fw-bold">แก้ไขข้อมูลสิ่งของ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <input type="hidden" name="item_id" id="edit_item_id">

                <div class="row g-5">
                    <!-- ฝั่งซ้าย: ข้อมูล -->
                    <div class="col-md-6 border-end border-secondary">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-danger">ประเภท *</label>
                            <select name="category_id" id="edit_category_id" class="form-select border-dark rounded-0" required>
                                <option value="">เลือกประเภท...</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as$cat): ?>
                                        <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-danger">ชื่อ *</label>
                            <input type="text" name="name" id="edit_name" class="form-control border-dark rounded-0" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">จำนวนปัจจุบัน (ไม่สามารถแก้ไขที่นี่ได้)</label>
                            <input type="number" id="edit_stock" class="form-control border-dark rounded-0 bg-light text-muted" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">คำอธิบาย</label>
                            <textarea name="description" id="edit_desc" class="form-control border-dark rounded-0" rows="4"></textarea>
                        </div>
                    </div>

                    <!-- ฝั่งขวา: รูปภาพประกอบ (ดึง UI อัปโหลดรูปภาพมาใช้) -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold small mb-3">รูปภาพประกอบ (อัปโหลดรูปใหม่เพื่อแทนที่):</label>

                        <div class="d-flex flex-column gap-3 mb-3">
                            <?php for ($slot = 0; $slot < 3; $slot++): ?>
                                <div class="d-flex align-items-center gap-3 p-2 bg-white border border-secondary border-opacity-25 rounded shadow-sm">
                                    <div class="text-center" style="width: 110px;">
                                        <input type="file" name="item_images[]" id="editFileInput_<?= $slot ?>"
                                            accept=".jpg, .jpeg, .png" class="d-none edit-slot-file-input" data-slot="<?= $slot ?>">

                                        <button type="button" class="btn btn-outline-dark border-secondary border-dashed rounded-3 px-3 py-2 w-100 edit-btn-upload-trigger"
                                            data-slot="<?= $slot ?>" title="คลิกเพื่อเลือกรูปใหม่">
                                            <i class="bi bi-arrow-up fs-4 d-block"></i>
                                            <span class="small fw-semibold">เปลี่ยนรูปที่ <?= $slot + 1 ?></span>
                                        </button>
                                    </div>

                                    <div class="flex-grow-1 position-relative">
                                        <div id="editPreviewBox_<?= $slot ?>" class="border border-secondary border-opacity-25 bg-white d-flex flex-column justify-content-center align-items-center text-muted rounded" style="height: 120px; background-size: contain; background-repeat: no-repeat; background-position: center;">
                                            <div id="editPlaceholderText_<?= $slot ?>" class="text-center text-secondary opacity-75">
                                                <i class="bi bi-image fs-3 d-block mb-1"></i>
                                                <span class="fw-bold small">No image available</span>
                                            </div>
                                        </div>

                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle p-0 d-none edit-btn-clear-image" id="editClearBtn_<?= $slot ?>" data-slot="<?= $slot ?>" style="width: 24px; height: 24px; line-height: 1;" title="ลบรูปนี้">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                        <small class="text-muted d-block"><i class="bi bi-info-circle me-1"></i>รองรับไฟล์ JPG, PNG ขนาดไม่เกิน 5MB</small>
                    </div>

                </div>
            </div>

            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary border-dark rounded-0 fw-bold px-4" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-warning border-dark rounded-0 fw-bold px-5 shadow-sm">บันทึกการแก้ไข</button>
            </div>
        </form>
    </div>
</div>

<script>
// จัดการการดึงข้อมูลมาแสดงตอนเปิด Modal (รวมถึงการพรีวิวรูปเก่า)
function openEditModal(btn) {
    const id = btn.getAttribute('data-id');
    const category = btn.getAttribute('data-category');
    const name = btn.getAttribute('data-name');
    const desc = btn.getAttribute('data-desc');
    const stock = btn.getAttribute('data-stock');
    const imagesJson = btn.getAttribute('data-images');

    document.getElementById('edit_item_id').value = id;
    document.getElementById('edit_category_id').value = category;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_desc').value = desc;
    document.getElementById('edit_stock').value = stock;

    // เคลียร์ค่ารูปภาพทั้งหมดก่อน
    for(let i=0; i<3; i++) {
        document.getElementById('editFileInput_' + i).value = '';
        document.getElementById('editPreviewBox_' + i).style.backgroundImage = '';
        document.getElementById('editPlaceholderText_' + i).classList.remove('d-none');
        document.getElementById('editClearBtn_' + i).classList.add('d-none');
    }

    // นำรูปภาพเก่ามาแสดง
    if (imagesJson) {
        try {
            const images = JSON.parse(imagesJson);
            if (Array.isArray(images)) {
                images.forEach((img, index) => {
                    if (index < 3 && img) {
                        const previewBox = document.getElementById('editPreviewBox_' + index);
                        const placeholder = document.getElementById('editPlaceholderText_' + index);
                        
                        previewBox.style.backgroundImage = `url('assets/uploads/items/${img}')`;
                        placeholder.classList.add('d-none');
                    }
                });
            }
        } catch (e) {
            console.error("Error parsing images JSON", e);
        }
    }

    new bootstrap.Modal(document.getElementById('editItemModal')).show();
}

// จัดการการอัปโหลดไฟล์ (เหมือนหน้า Add)
document.querySelectorAll('.edit-btn-upload-trigger').forEach(btn => {
    btn.addEventListener('click', function() {
        const slot = this.getAttribute('data-slot');
        document.getElementById('editFileInput_' + slot).click();
    });
});

document.querySelectorAll('.edit-slot-file-input').forEach(input => {
    input.addEventListener('change', function(e) {
        const slot = this.getAttribute('data-slot');
        const previewBox = document.getElementById('editPreviewBox_' + slot);
        const placeholder = document.getElementById('editPlaceholderText_' + slot);
        const clearBtn = document.getElementById('editClearBtn_' + slot);
        const file = e.target.files[0];

        if (file) {
            if (file.size > 5242880) {
                alert('ไฟล์มีขนาดเกิน 5MB กรุณาเลือกไฟล์ใหม่');
                this.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = function(event) {
                previewBox.style.backgroundImage = `url('${event.target.result}')`;
                placeholder.classList.add('d-none');
                clearBtn.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }
    });
});

document.querySelectorAll('.edit-btn-clear-image').forEach(btn => {
    btn.addEventListener('click', function() {
        const slot = this.getAttribute('data-slot');
        document.getElementById('editFileInput_' + slot).value = '';
        document.getElementById('editPreviewBox_' + slot).style.backgroundImage = '';
        document.getElementById('editPlaceholderText_' + slot).classList.remove('d-none');
        this.classList.add('d-none');
    });
});
</script>