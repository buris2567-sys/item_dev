<?php $categories = $categories ?? []; ?>
<!-- Modal ป๊อปอัปสำหรับเพิ่มสิ่งของ (Bootstrap Modal) -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">

        <!-- ฟอร์มส่งข้อมูล: 
             - action: กำหนดพาธไปยังไฟล์หลังบ้านเพื่อประมวลผล
             - method="POST": ส่งข้อมูลแบบซ่อนใน Background
             - enctype="multipart/form-data": จำเป็นต้องใส่เสมอเมื่อมีการส่งไฟล์อัปโหลด -->
        <form action="actions/admin/item_add.php" method="POST" enctype="multipart/form-data" class="modal-content border-dark rounded-0" style="border-width: 2px !important;">

            <!-- ส่วนหัวของ Modal -->
            <div class="modal-header border-dark" style="border-bottom-width: 2px !important;">
                <h5 class="modal-title fw-bold">เพิ่มสิ่งของ</h5>
                <!-- ปุ่มปิดป๊อปอัป (X) -->
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- ส่วนเนื้อหาของ Modal -->
            <div class="modal-body p-4 bg-light">
                <div class="row g-5">

                    <!-- ==========================================
                         ฝั่งซ้าย: ฟอร์มสำหรับกรอกข้อมูลพัสดุ
                         ========================================== -->
                    <div class="col-md-6 border-end border-secondary">

                        <!-- 1. ดร็อปดาวน์เลือกหมวดหมู่/ประเภท -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-danger">ประเภท *</label>
                            <select name="category_id" class="form-select border-dark rounded-0" required>
                                <option value="">เลือกประเภท...</option>
                                <!-- วนลูปนำข้อมูลจาก DB มาแสดงใน Dropdown -->
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- 2. ช่องกรอกชื่อสิ่งของ -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-danger">ชื่อ *</label>
                            <input type="text" name="name" class="form-control border-dark rounded-0" placeholder="เช่น Printer หน้าห้อง" required>
                        </div>

                        <!-- 3. ช่องกรอกจำนวนสต็อกเริ่มต้น -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small">จำนวนเริ่มต้น</label>
                            <input type="number" name="initial_stock" class="form-control border-dark rounded-0" value="0" min="0">
                        </div>

                        <!-- 4. ช่องกรอกรายละเอียด/คำอธิบาย -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small">คำอธิบาย</label>
                            <textarea name="description" class="form-control border-dark rounded-0" rows="4"></textarea>
                        </div>
                    </div>

                    <!-- ==========================================
                         ฝั่งขวา: บล็อกอัปโหลดรูปภาพ 3 ช่อง
                         ========================================== -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold small mb-3">รูปภาพประกอบ (สูงสุด 3 รูป):</label>

                        <div class="d-flex flex-column gap-3 mb-3">
                            <!-- วนลูปสร้างบล็อกอัปโหลดรูปภาพจำนวน 3 ช่อง (index: $slot = 0, 1, 2) -->
                            <?php for ($slot = 0; $slot < 3; $slot++): ?>

                                <!-- บล็อกอัปโหลดรูปภาพช่องที่ $slot + 1 -->
                                <div class="d-flex align-items-center gap-3 p-2 bg-white border border-secondary border-opacity-25 rounded shadow-sm">

                                    <!-- ฝั่งซ้ายของบล็อก: ปุ่มกดเลือกไฟล์ (ซ่อน <input type="file"> ไว้ แล้วใช้ปุ่มแทน) -->
                                    <!-- <?= $slot ?> คือ index ของช่องอัปโหลด (0, 1, 2) ซึ่งใช้เป็น ID และ data-slot เพื่อแยกแต่ละช่องออกจากกัน -->
                                    <div class="text-center" style="width: 110px;">
                                        <!-- Input File หลักสำหรับรับรูปภาพ (ตั้งชื่อเป็น Array: item_images[]) ซ่อนไว้ด้วย d-none -->
                                        <input type="file" name="item_images[]" id="fileInput_<?= $slot ?>"
                                            accept=".jpg, .jpeg, .png" class="d-none slot-file-input" data-slot="<?= $slot ?>">

                                        <!-- ปุ่มแสดงผลให้ผู้ใช้คลิกเพื่อเปิด File Browser -->
                                        <button type="button" class="btn btn-outline-dark border-secondary border-dashed rounded-3 px-3 py-2 w-100 btn-upload-trigger"
                                            data-slot="<?= $slot ?>" title="คลิกเพื่อเลือกรูป">
                                            <i class="bi bi-arrow-up fs-4 d-block"></i>
                                            <span class="small fw-semibold">เลือกรูปที่ <?= $slot + 1 ?></span>
                                        </button>
                                    </div>

                                    <!-- ฝั่งขวาของบล็อก: กล่องสำหรับแสดงตัวอย่างรูปภาพ (Preview) -->
                                    <div class="flex-grow-1 position-relative">
                                        <!-- กล่องพรีวิว (เมื่อเลือกรูปแล้วจะใส่รูปภาพเป็น background-image) -->
                                        <div id="previewBox_<?= $slot ?>" class="border border-secondary border-opacity-25 bg-white d-flex flex-column justify-content-center align-items-center text-muted rounded" style="height: 120px; background-size: contain; background-repeat: no-repeat; background-position: center;">
                                            <!-- ข้อความ/ไอคอนเริ่มต้นเมื่อยังไม่ได้เลือกรูป -->
                                            <div id="placeholderText_<?= $slot ?>" class="text-center text-secondary opacity-75">
                                                <i class="bi bi-image fs-3 d-block mb-1"></i>
                                                <span class="fw-bold small">No image available</span>
                                            </div>
                                        </div>

                                        <!-- ปุ่มลบรูปภาพ (ซ่อนไว้เป็น d-none ก่อน จะแสดงเมื่อมีการเลือกรูปแล้วเท่านั้น) -->
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle p-0 d-none btn-clear-image" id="clearBtn_
                                    <?= $slot ?>" data-slot="<?= $slot ?>" style="width: 24px; height: 24px; line-height: 1;" title="ลบรูปนี้">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>

                                </div>
                            <?php endfor; ?>
                        </div>

                        <!-- คำแนะนำข้อจำกัดการอัปโหลด -->
                        <small class="text-muted d-block"><i class="bi bi-info-circle me-1"></i>รองรับไฟล์ JPG, PNG ขนาดไม่เกิน 5MB ต่อไฟล์</small>
                    </div>

                </div>
            </div>

            <!-- ส่วนท้ายของ Modal: ปุ่มยกเลิก และ ปุ่มบันทึก -->
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary border-dark rounded-0 fw-bold px-4" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-warning border-dark rounded-0 fw-bold px-5 shadow-sm">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================
     สคริปต์ JavaScript จัดการการอัปโหลดและพรีวิวรูปภาพ
     ========================================== -->
<script>
    // 1. ส่งการคลิกจากปุ่มไปให้ <input type="file"> ของช่องนั้นๆ
    document.querySelectorAll('.btn-upload-trigger').forEach(btn => {
        btn.addEventListener('click', function() {
            const slot = this.getAttribute('data-slot'); // ดึงเลขสล็อต (0, 1, 2)
            document.getElementById('fileInput_' + slot).click(); // จำลองการคลิก Input File
        });
    });

    // 2. ดักจับเมื่อมีการเลือกไฟล์ใน Input เพื่อแสดงรูปตัวอย่าง (Preview)
    document.querySelectorAll('.slot-file-input').forEach(input => {
        input.addEventListener('change', function(e) {
            const slot = this.getAttribute('data-slot');
            const previewBox = document.getElementById('previewBox_' + slot);
            const placeholder = document.getElementById('placeholderText_' + slot);
            const clearBtn = document.getElementById('clearBtn_' + slot);
            const file = e.target.files[0];

            if (file) {
                // ตรวจสอบขนาดไฟล์ ป้องกันไฟล์ขนาดเกิน 5MB (5,242,880 Bytes)
                if (file.size > 5242880) {
                    alert('ไฟล์มีขนาดเกิน 5MB กรุณาเลือกไฟล์ใหม่');
                    this.value = ''; // ล้างค่าที่เลือกไว้
                    return;
                }

                // อ่านไฟล์รูปภาพมาแปลงเป็น DataURL เพื่อนำมาแสดงในกล่องพรีวิว
                const reader = new FileReader();
                reader.onload = function(event) {
                    previewBox.style.backgroundImage = `url('${event.target.result}')`; // แสดงรูปเป็นพื้นหลัง
                    placeholder.classList.add('d-none'); // ซ่อนข้อความ No image available
                    clearBtn.classList.remove('d-none'); // แสดงปุ่มลบรูปภาพ
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // 3. ฟังก์ชันสำหรับกดปุ่มลบรูปภาพในช่องนั้นๆ
    document.querySelectorAll('.btn-clear-image').forEach(btn => {
        btn.addEventListener('click', function() {
            const slot = this.getAttribute('data-slot');
            const fileInput = document.getElementById('fileInput_' + slot);
            const previewBox = document.getElementById('previewBox_' + slot);
            const placeholder = document.getElementById('placeholderText_' + slot);

            fileInput.value = ''; // ล้างค่าไฟล์ใน <input type="file">
            previewBox.style.backgroundImage = ''; // ลบรูปพื้นหลังออก
            placeholder.classList.remove('d-none'); // ดึงข้อความ No image available กลับมาแสดง
            this.classList.add('d-none'); // ซ่อนปุ่มลบรูปภาพ
        });
    });
</script>