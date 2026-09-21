<div class="modal fade" id="updateStockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="actions/admin/item_update_stock.php" method="POST" class="modal-content border-dark rounded-0" style="border-width: 2px !important;">
            <div class="modal-header border-dark bg-white" style="border-bottom-width: 2px !important;">
                <h5 class="modal-title fw-bold" id="us_title">แก้ไขจำนวนสต็อก: โมเดลรถ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <input type="hidden" name="item_id" id="us_item_id">
                
                <!-- จำนวนปัจจุบัน -->
                <div class="row align-items-center mb-4">
                    <div class="col-6 fw-bold small">จำนวนปัจจุบันในคลัง:</div>
                    <div class="col-6">
                        <div class="bg-light border border-secondary text-center py-2 fw-bold" id="us_current_display">32</div>
                        <input type="hidden" id="us_current_val" value="32">
                    </div>
                </div>

                <!-- เลือกดำเนินการ -->
                <div class="row mb-4">
                    <div class="col-12 fw-bold small mb-2">เลือกดำเนินการ:</div>
                    <div class="col-12 ps-4">
                        <div class="form-check mb-2">
                            <input class="form-check-input border-dark" type="radio" name="action_type" id="act_add" value="add" checked onchange="calcStock()">
                            <label class="form-check-label fw-bold" for="act_add">เพิ่มสต็อก (+)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input border-dark" type="radio" name="action_type" id="act_reduce" value="reduce" onchange="calcStock()">
                            <label class="form-check-label fw-bold" for="act_reduce">ลดสต็อก (-)</label>
                        </div>
                    </div>
                </div>

                <!-- จำนวนที่ต้องการ -->
                <div class="row align-items-center mb-4">
                    <div class="col-6 fw-bold small">จำนวนที่ต้องการเพิ่ม/ลด:</div>
                    <div class="col-6 d-flex align-items-center">
                        <i class="bi bi-dash-circle fs-4 me-2 cursor-pointer" onclick="adjustQty(-1)"></i>
                        <input type="number" name="quantity" id="us_qty" class="form-control text-center border-warning border-2 fw-bold rounded-0" value="20" min="1" oninput="calcStock()">
                        <i class="bi bi-plus-circle fs-4 ms-2 cursor-pointer" onclick="adjustQty(1)"></i>
                    </div>
                </div>

                <!-- จำนวนสุทธิ -->
                <div class="row align-items-center">
                    <div class="col-6 fw-bold small">จำนวนสุทธิหลังการปรับปรุง:</div>
                    <div class="col-6">
                        <div class="bg-info bg-opacity-25 border border-dark text-center py-2 fw-bold fs-5" id="us_net_display">52</div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer bg-light border-dark justify-content-center" style="border-top-width: 2px !important;">
                <button type="submit" class="btn btn-warning border-dark rounded-0 fw-bold px-4 shadow-sm">บันทึก</button>
                <button type="button" class="btn btn-light border-dark rounded-0 fw-bold px-4 shadow-sm" data-bs-dismiss="modal">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>

<script>
// สคริปต์คำนวณบวกลบเลขหน้า UI
function openUpdateStockModal(id, name, current) {
    document.getElementById('us_title').innerText = 'แก้ไขจำนวนสต็อก: ' + name;
    document.getElementById('us_item_id').value = id;
    document.getElementById('us_current_val').value = current;
    document.getElementById('us_current_display').innerText = current;
    document.getElementById('us_qty').value = 0;
    calcStock();
    new bootstrap.Modal(document.getElementById('updateStockModal')).show();
}

function adjustQty(amount) {
    let input = document.getElementById('us_qty');
    let val = parseInt(input.value) || 0;
    if(val + amount >= 0) input.value = val + amount;
    calcStock();
}

function calcStock() {
    let current = parseInt(document.getElementById('us_current_val').value) || 0;
    let qty = parseInt(document.getElementById('us_qty').value) || 0;
    let isAdd = document.getElementById('act_add').checked;
    
    let net = isAdd ? (current + qty) : (current - qty);
    document.getElementById('us_net_display').innerText = net < 0 ? 0 : net;
}
</script>