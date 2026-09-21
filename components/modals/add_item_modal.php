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
                    
                    <!-- ฝั่งขวา: อัปโหลดรูปภาพ -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">รูปภาพประกอบ (สูงสุด 3 รูป)</label>
                        
                        <!-- Input File (ซ่อนไว้ แต่จะถูกเรียกผ่าน JS) -->
                        <input type="file" name="item_images[]" id="fileInput" accept=".jpg, .jpeg, .png" multiple class="d-none">
                        
                        <div class="row g-3 mb-2" id="imagePreviewContainer">
                            <!-- ปุ่มสำหรับคลิกเพิ่มรูปภาพ (เลียนแบบ UI เดิม) -->
                            <div class="col-12" id="uploadTrigger" style="cursor: pointer;">
                                <div class="border border-secondary border-dashed bg-white d-flex flex-column justify-content-center align-items-center text-muted" style="height: 200px;">
                                    <i class="bi bi-image fs-1 mb-2"></i>
                                    <span>คลิกเพื่อเลือกรูปภาพ (เลือกได้พร้อมกัน 3 รูป)</span>
                                </div>
                            </div>
                            <!-- พื้นที่แสดงตัวอย่างรูป จะถูกแทรกตรงนี้ด้วย JS -->
                        </div>
                        <small class="text-muted">รองรับไฟล์ JPG, PNG ขนาดไม่เกิน 5MB ต่อไฟล์</small>
                    </div>

                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="submit" class="btn btn-warning border-dark rounded-0 fw-bold px-5 shadow-sm">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
// สคริปต์สำหรับคลิกกล่องแล้วเปิด File Browser และแสดงตัวอย่างรูป
document.getElementById('uploadTrigger').addEventListener('click', function() {
    document.getElementById('fileInput').click();
});

document.getElementById('fileInput').addEventListener('change', function(e) {
    const container = document.getElementById('imagePreviewContainer');
    // ลบเนื้อหาเดิมออกก่อน (รวมถึงปุ่มคลิก)
    container.innerHTML = ''; 
    
    let files = Array.from(e.target.files).slice(0, 3); // จำกัดแค่ 3 รูป
    
    files.forEach((file, index) => {
        let reader = new FileReader();
        reader.onload = function(e) {
            // ถ้ารูปแรกให้ใหญ่หน่อย (col-12) รูปต่อไปเล็ก (col-6)
            let colClass = index === 0 ? 'col-12' : 'col-6';
            let height = index === 0 ? '200px' : '120px';
            
            container.innerHTML += `
                <div class="${colClass}">
                    <div class="border border-dark bg-white" style="height: ${height}; background-image: url('${e.target.result}'); background-size: cover; background-position: center;"></div>
                </div>
            `;
        }
        reader.readAsDataURL(file);
    });
});
</script>