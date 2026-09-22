<div class="modal fade" id="deleteItemModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form action="actions/admin/item_delete.php" method="POST" class="modal-content border-danger rounded-0" style="border-width: 2px !important;">
            
            <div class="modal-body p-4 text-center bg-white">
                <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                <h5 class="fw-bold mt-3 mb-2 text-dark">ต้องการที่จะลบ?</h5>
                <p class="text-muted small mb-4">สิ่งของ: <strong id="del_item_name_display" class="text-dark"></strong><br>ข้อมูลนี้จะถูกลบออกจากระบบทันที</p>
                
                <!-- ส่งค่าซ่อนไปให้ Backend -->
                <input type="hidden" name="item_id" id="del_item_id_input">
                <input type="hidden" name="item_name" id="del_item_name_input">
                
                <div class="d-flex justify-content-center gap-2">
                    <button type="submit" class="btn btn-danger rounded-0 fw-bold w-50">ใช่, ลบเลย</button>
                    <button type="button" class="btn btn-secondary rounded-0 fw-bold w-50" data-bs-dismiss="modal">ไม่</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openDeleteModal(itemId, itemName) {
    document.getElementById('del_item_name_display').innerText = itemName;
    document.getElementById('del_item_id_input').value = itemId;
    document.getElementById('del_item_name_input').value = itemName; // ส่งชื่อไปบันทึก Log ด้วย
    new bootstrap.Modal(document.getElementById('deleteItemModal')).show();
}
</script>