<!-- Modal เพิ่มประเภทสิ่งของ -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <!-- 🟢 ใช้ rounded-4 และ border-0 ให้เข้ากับหน้าหลัก -->
        <form id="addCategoryForm" action="actions/admin/category_add.php" method="POST" class="modal-content border-0 rounded-4 shadow">
            
            <div class="modal-header border-bottom-0 pb-0 mt-2 mx-2">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
                    <!-- ปุ่มย้อนกลับแบบวงกลม -->
                    <button type="button" class="btn btn-light rounded-circle me-3 shadow-sm" data-bs-dismiss="modal" style="width: 40px; height: 40px;">
                        <i class="bi bi-arrow-left"></i>
                    </button>
                    เพิ่มประเภทสิ่งของ
                </h5>
            </div>

            <div class="modal-body p-4">
                <!-- จัดเรียงแนวนอน (Inline) ตามแบบในภาพ -->
                <div class="row align-items-center g-3">
                    <div class="col-md-3">
                        <label class="fw-bold text-dark mb-0 fs-6">ชื่อประเภทสิ่งของ</label>
                    </div>
                    <div class="col-md-5">
                        <input type="text" id="addCategoryName" name="name" class="form-control rounded-pill bg-light border-0 shadow-sm px-4 py-2" placeholder="กรุณากรอก...." required>
                    </div>
                    <div class="col-md-4 d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-light rounded-pill px-4 shadow-sm" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="button" id="saveAddCategoryBtn" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm">บันทึก</button>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>