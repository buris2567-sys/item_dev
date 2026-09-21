<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <!-- ต้องมี enctype="multipart/form-data" เสมอเมื่อมีการอัปโหลดไฟล์ -->
        <form action="actions/admin/item_add.php" method="POST" enctype="multipart/form-data" class="modal-content border-dark rounded-0" style="border-width: 2px !important;">
            <div class="modal-header border-dark" style="border-bottom-width: 2px !important;">
                <h5 class="modal-title fw-bold">เพิ่มสิ่งของ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="row g-5">
                    
                    <!-- ฝั่งซ้าย: ฟอร์มข้อมูล -->
                    <div class="col-md-6 border-end border-secondary">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-danger">ประเภท *</label>
                            <select name="category_id" class="form-select border-dark rounded-0" required>
                                <option value="">เลือกประเภท...</option>
                                <!-- วนลูปนำข้อมูลจาก DB มาแสดงใน Dropdown -->
                                <?php foreach ($categories as$cat): ?>
                                    <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-danger">ชื่อ *</label>
                            <input type="text" name="name" class="form-control border-dark rounded-0" placeholder="เช่น Printer หน้าห้อง" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">จำนวนเริ่มต้น</label>
                            <input type="number" name="initial_stock" class="form-control border-dark rounded-0" value="0" min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">คำอธิบาย</label>
                            <textarea name="description" class="form-control border-dark rounded-0" rows="4"></textarea>
                        </div>
                    </div>
                    
                    <!-- ฝั่งขวา: อัปโหลดรูปภาพ 3 บล็อกแยกตามภาพ -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold small mb-3">รูปภาพประกอบ (สูงสุด 3 รูป):</label>
                        
                        <div class="d-flex flex-column gap-3 mb-3">
                            <?php for ($slot = 0; $slot < 3; $slot++): ?>
                            <!-- บล็อกอัปโหลดรูปที่ <?= $slot + 1 ?> -->
                            <div class="d-flex align-items-center gap-3 p-2 bg-white border border-secondary border-opacity-25 rounded shadow-sm">
                                <!-- ปุ่มสำหรับกดเลือกรูปภาพ -->
                                <div class="text-center" style="width: 110px;">
                                    <input type="file" name="item_images[]" id="fileInput_<?= $slot ?>" accept=".jpg, .jpeg, .png" class="d-none slot-file-input" data-slot="<?= $slot ?>">
                                    <button type="button" class="btn btn-outline-dark border-secondary border-dashed rounded-3 px-3 py-2 w-100 btn-upload-trigger" data-slot="<?= $slot ?>" title="คลิกเพื่อเลือกรูป">
                                        <i class="bi bi-arrow-up fs-4 d-block"></i>
                                        <span class="small fw-semibold">เลือกรูปที่ <?= $slot + 1 ?></span>
                                    </button>
                                </div>

                                <!-- กล่องแสดงตัวอย่างรูป (เริ่มต้นเป็น No image available) -->
                                <div class="flex-grow-1 position-relative">
                                    <div id="previewBox_<?= $slot ?>" class="border border-secondary border-opacity-25 bg-white d-flex flex-column justify-content-center align-items-center text-muted rounded" style="height: 120px; background-size: contain; background-repeat: no-repeat; background-position: center;">
                                        <div id="placeholderText_<?= $slot ?>" class="text-center text-secondary opacity-75">
                                            <i class="bi bi-image fs-3 d-block mb-1"></i>
                                            <span class="fw-bold small">No image available</span>
                                        </div>
                                    </div>
                                    <!-- ปุ่มลบ/ยกเลิกรูปภาพในช่องนี้ -->
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle p-0 d-none btn-clear-image" id="clearBtn_<?= $slot ?>" data-slot="<?= $slot ?>" style="width: 24px; height: 24px; line-height: 1;" title="ลบรูปนี้">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                        
                        <small class="text-muted d-block"><i class="bi bi-info-circle me-1"></i>รองรับไฟล์ JPG, PNG ขนาดไม่เกิน 5MB ต่อไฟล์</small>
                    </div>

                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary border-dark rounded-0 fw-bold px-4" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-warning border-dark rounded-0 fw-bold px-5 shadow-sm">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
// สคริปต์จัดการการเลือกรูปภาพแยกทีละบล็อก (3 บล็อก)
document.querySelectorAll('.btn-upload-trigger').forEach(btn => {
    btn.addEventListener('click', function() {
        const slot = this.getAttribute('data-slot');
        document.getElementById('fileInput_' + slot).click();
    });
});

// ดักจับการเปลี่ยนไฟล์ในแต่ละช่องเพื่อพรีวิว
document.querySelectorAll('.slot-file-input').forEach(input => {
    input.addEventListener('change', function(e) {
        const slot = this.getAttribute('data-slot');
        const previewBox = document.getElementById('previewBox_' + slot);
        const placeholder = document.getElementById('placeholderText_' + slot);
        const clearBtn = document.getElementById('clearBtn_' + slot);
        const file = e.target.files[0];

        if (file) {
            // ตรวจสอบขนาดไฟล์ (ไม่เกิน 5MB)
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

// ฟังก์ชันล้างรูปภาพในช่องนั้นๆ
document.querySelectorAll('.btn-clear-image').forEach(btn => {
    btn.addEventListener('click', function() {
        const slot = this.getAttribute('data-slot');
        const fileInput = document.getElementById('fileInput_' + slot);
        const previewBox = document.getElementById('previewBox_' + slot);
        const placeholder = document.getElementById('placeholderText_' + slot);

        fileInput.value = ''; // ล้างค่าไฟล์
        previewBox.style.backgroundImage = '';
        placeholder.classList.remove('d-none');
        this.classList.add('d-none');
    });
});
</script>