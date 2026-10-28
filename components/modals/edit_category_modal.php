<!-- Modal แก้ไขประเภทสิ่งของ -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="editCategoryForm" action="actions/admin/category_edit.php" method="POST" class="modal-content border-0 rounded-4 shadow">

            <!-- 🟢 ซ่อน ID ของหมวดหมู่ที่กำลังแก้ไข -->
            <input type="hidden" id="editCategoryId" name="category_id">

            <div class="modal-header border-bottom-0 pb-0 mt-2 mx-2">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
                    <button type="button" class="btn btn-light rounded-circle me-3 shadow-sm" data-bs-dismiss="modal" style="width: 40px; height: 40px;">
                        <i class="bi bi-arrow-left"></i>
                    </button>
                    แก้ไขประเภทสิ่งของ
                </h5>
            </div>

            <div class="modal-body p-4">
                <div class="row align-items-center g-3">
                    <div class="col-md-3">
                        <label class="fw-bold text-dark mb-0 fs-6">ชื่อประเภทสิ่งของ</label>
                    </div>
                    <div class="col-md-5">
                        <input type="text" id="editCategoryName" name="name" class="form-control rounded-pill bg-light border-0 shadow-sm px-4 py-2" required>
                    </div>
                    <div class="col-md-4 d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-light rounded-pill px-4 shadow-sm" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="button" id="saveEditCategoryBtn" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm">บันทึก</button>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

