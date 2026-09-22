<!-- การ์ดตารางประวัติการเปลี่ยนแปลงล่าสุด -->
<div class="card border-dark rounded-0 mb-4 shadow-sm" style="border-width: 1px !important;">
    
    <!-- ส่วนหัวของการ์ด (Header) -->
    <div class="card-header bg-warning border-dark rounded-0 fw-bold py-3" style="border-bottom-width: 1px !important;">
        ประวัติการเปลี่ยนแปลงล่าสุด
    </div>
    
    <div class="card-body p-0">
        
        <!-- แถบเครื่องมือ: ช่องค้นหาข้อมูลประวัติแบบ Real-time -->
        <div class="d-flex justify-content-end align-items-center p-3 border-bottom">
            <div class="d-flex align-items-center">
                <i class="bi bi-search me-2 text-muted"></i>
                <!-- Input ค้นหาข้อมูล (เชื่อมกับ JavaScript ผ่าน id="searchTxInput") -->
                <input type="text" id="searchTxInput" class="form-control form-control-sm border-dark rounded-0" style="width: 250px;" placeholder="ค้นหา...">
            </div>
        </div>

        <!-- ตารางแสดงข้อมูล Transaction -->
        <div class="table-responsive">
            <table class="table table-bordered border-dark align-middle text-center mb-0" id="txTable">
                <!-- หัวตาราง -->
                <thead class="table-light">
                    <tr>
                        <th class="text-muted small fw-bold" style="width: 80px;">ลำดับ</th>
                        <th class="text-muted small fw-bold" style="width: 130px;">ประเภท</th>
                        <th class="text-start ps-4 text-muted small fw-bold">ชื่อรายการ</th>
                        <th class="text-muted small fw-bold" style="width: 100px;">จำนวน</th>
                        <th class="text-muted small fw-bold" style="width: 120px;">วันที่</th>
                        <th class="text-muted small fw-bold" style="width: 100px;">เวลา</th>
                        <th class="text-start ps-3 text-muted small fw-bold">รายละเอียด</th>
                    </tr>
                </thead>
                
                <!-- เนื้อหาตาราง -->
                <tbody>
                    <!-- ตรวจสอบว่ามีข้อมูลประวัติส่งมาจาก Database หรือไม่ -->
                    <?php if (!empty($transactions)): ?>
                        <?php foreach ($transactions as $index =>$tx): ?>
                            <?php 
                                // เช็กประเภทรายการ: ถ้าเป็น 'IN' ให้เป็นสีเขียวและเครื่องหมาย (+) ถ้าไม่ใช่ให้เป็นสีแดงและเครื่องหมาย (-)
                                $isAdd = ($tx['transaction_type'] === 'IN');
                                $qtySign =$isAdd ? '+' : '-';
                                $qtyClass =$isAdd ? 'text-success' : 'text-danger';
                                
                                // แยกแปลงรูปแบบ วันที่ และ เวลา จากฟิลด์ created_at
                                $txDate = !empty($tx['created_at']) ? date('d/m/Y', strtotime($tx['created_at'])) : '-';$txTime = !empty($tx['created_at']) ? date('H:i:s', strtotime($tx['created_at'])) : '-';
                            ?>
                            
                            <!-- แถวข้อมูลประวัติ: ใส่ attribute 'data-search-text' รวมข้อความทั้งหมดไว้ใช้ในการค้นหาด้วย JS -->
                            <tr class="tx-row" data-search-text="<?= htmlspecialchars(strtolower(($tx['category_name'] ?? '') . ' ' . ($tx['item_name'] ?? '') . ' ' . ($tx['remark'] ?? '') . ' ' . ($tx['username'] ?? ''))) ?>">
                                <td class="text-muted"><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($tx['category_name'] ?? '-') ?></td>
                                <td class="text-start ps-4 fw-bold text-dark">
                                    <?= htmlspecialchars($tx['item_name'] ?? '-') ?>
                                </td>
                                <!-- แสดงตัวเลขจำนวนพร้อมสีระบุประเภท (+สีเขียว / -สีแดง) -->
                                <td class="fw-bold <?= $qtyClass ?>">
                                    <?= $qtySign . number_format($tx['quantity']) ?>
                                </td>
                                <td><?= $txDate ?></td>
                                <td><?= $txTime ?></td>
                                <td class="text-start ps-3 text-muted small">
                                    <?= htmlspecialchars($tx['remark'] ?? '-') ?>
                                    <?php if (!empty($tx['username'])): ?>
                                        <span class="text-secondary">(โดย <?= htmlspecialchars($tx['username']) ?>)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- แสดงข้อความนี้เมื่อฐานข้อมูลไม่มีประวัติเลย -->
                        <tr id="noTxDataRow">
                            <td colspan="7" class="text-center py-4 text-muted">ไม่พบประวัติการเปลี่ยนแปลง</td>
                        </tr>
                    <?php endif; ?>
                    
                    <!-- แสดงข้อความนี้เมื่อค้นหาด้วยคีย์เวิร์ดแล้วไม่พบข้อมูล (ควบคุมเปิด/ปิดด้วย JS) -->
                    <tr id="noTxFilterRow" style="display: none;">
                        <td colspan="7" class="text-center py-4 text-muted">ไม่พบข้อมูลที่ค้นหา</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- แถบควบคุมการแบ่งหน้า (Pagination Controls) -->
        <div class="d-flex justify-content-between align-items-center p-3 border-top">
            <div class="d-flex align-items-center">
                <span class="small text-muted me-2">Items per page:</span>
                <!-- ตัวเลือกจำนวนรายการต่อหน้า (5, 10, 50, 100) -->
                <select id="txPerPage" class="form-select form-select-sm border-dark rounded-0" style="width: 70px;">
                    <option value="5" selected>5</option>
                    <option value="10">10</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div class="d-flex align-items-center">
                <!-- แสดงตัวเลขบอกตำแหน่งช่วงข้อมูล เช่น 1 - 5 of 20 -->
                <span class="small text-muted me-3" id="txPageInfo">1 - 5 of 0</span>
                <!-- ปุ่มย้อนกลับและปุ่มถัดไป -->
                <button class="btn btn-sm btn-light border-dark rounded-0 me-1" id="txPrevPage"><i class="bi bi-chevron-left"></i></button>
                <button class="btn btn-sm btn-light border-dark rounded-0" id="txNextPage"><i class="bi bi-chevron-right"></i></button>
            </div>
        </div>

    </div>
</div>

<!-- สคริปต์ JavaScript ประมวลผลการค้นหาและการแบ่งหน้าแบบ Client-Side -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. อ้างอิง DOM Elements ทั้งหมดที่ต้องใช้งาน
    const txRows = Array.from(document.querySelectorAll('.tx-row')); // ดึงทุกแถวที่มีคลาส .tx-row
    const searchTxInput = document.getElementById('searchTxInput');
    const txPerPageSelect = document.getElementById('txPerPage');
    const txPrevPageBtn = document.getElementById('txPrevPage');
    const txNextPageBtn = document.getElementById('txNextPage');
    const txPageInfo = document.getElementById('txPageInfo');
    const noTxFilterRow = document.getElementById('noTxFilterRow');

    // 2. ตั้งค่าตัวแปรเริ่มต้น
    let currentTxPage = 1; // หน้าปัจจุบันเริ่มต้นที่หน้า 1
    let txPerPage = parseInt(txPerPageSelect.value); // จำนวนรายการต่อหน้า
    let filteredTxRows = [...txRows]; // Array สำหรับเก็บแถวที่ผ่านการกรองข้อมูล

    // 3. ฟังก์ชันหลักสำหรับคำนวณและอัปเดตตาราง
    function updateTxTable() {
        const query = searchTxInput.value.trim().toLowerCase(); // รับคำค้นหา แปลงเป็นอักษรพิมพ์เล็ก

        // 3.1 ค้นหาและกรองข้อมูลจาก attribute 'data-search-text'
        filteredTxRows = txRows.filter(row => {
            const text = row.getAttribute('data-search-text') || '';
            return text.includes(query);
        });

        // 3.2 ซ่อนทุกแถวข้อมูลก่อนคำนวณ
        txRows.forEach(row => row.style.display = 'none');

        // 3.3 คำนวณขอบเขตการแสดงผลตามหน้า
        const totalItems = filteredTxRows.length;
        const totalPages = Math.ceil(totalItems / txPerPage) || 1;
        
        // ปรับดักไม่ให้เลขหน้าเกินช่วงที่มีอยู่จริง
        if (currentTxPage > totalPages) currentTxPage = totalPages;
        if (currentTxPage < 1) currentTxPage = 1;

        const startIndex = (currentTxPage - 1) * txPerPage;
        const endIndex = Math.min(startIndex + txPerPage, totalItems);

        // 3.4 วนลูปเปิดแสดงผล (display: '') เฉพาะแถวที่อยู่ในหน้าปัจจุบัน
        for (let i = startIndex; i < endIndex; i++) {
            filteredTxRows[i].style.display = '';
        }

        // 3.5 อัปเดตการแสดงผลข้อความสถานะและปุ่มกด
        if (totalItems === 0) {
            if (noTxFilterRow) noTxFilterRow.style.display = ''; // แสดงแถว 'ไม่พบข้อมูล'
            txPageInfo.textContent = '0 - 0 of 0';
        } else {
            if (noTxFilterRow) noTxFilterRow.style.display = 'none'; // ซ่อนแถว 'ไม่พบข้อมูล'
            txPageInfo.textContent = `${startIndex + 1} - ${endIndex} of ${totalItems}`;
        }

        // ปิดการใช้งานปุ่มเมื่ออยู่หน้าแรก หรือ หน้าสุดท้าย
        txPrevPageBtn.disabled = (currentTxPage === 1 || totalItems === 0);
        txNextPageBtn.disabled = (currentTxPage === totalPages || totalItems === 0);
    }

    // 4. ดักจับ Event เมื่อพิมพ์ในช่องค้นหา
    if (searchTxInput) {
        searchTxInput.addEventListener('input', () => {
            currentTxPage = 1; // รีเซ็ตกลับไปเริ่มหน้า 1 เมื่อมีการค้นหาใหม่
            updateTxTable();
        });
    }

    // 5. ดักจับ Event เมื่อเปลี่ยนจำนวนรายการต่อหน้า (5, 10, 50, 100)
    if (txPerPageSelect) {
        txPerPageSelect.addEventListener('change', (e) => {
            txPerPage = parseInt(e.target.value);
            currentTxPage = 1; // รีเซ็ตกลับไปเริ่มหน้า 1
            updateTxTable();
        });
    }

    // 6. ดักจับ Event ปุ่มย้อนกลับ (Prev)
    if (txPrevPageBtn) {
        txPrevPageBtn.addEventListener('click', () => {
            if (currentTxPage > 1) {
                currentTxPage--;
                updateTxTable();
            }
        });
    }

    // 7. ดักจับ Event ปุ่มถัดไป (Next)
    if (txNextPageBtn) {
        txNextPageBtn.addEventListener('click', () => {
            const totalPages = Math.ceil(filteredTxRows.length / txPerPage);
            if (currentTxPage < totalPages) {
                currentTxPage++;
                updateTxTable();
            }
        });
    }

    // 8. สั่งประมวลผลคำนวณตารางครั้งแรกเมื่อโหลดหน้าเว็บสำเร็จ
    if (txRows.length > 0) {
        updateTxTable();
    }
});
</script>