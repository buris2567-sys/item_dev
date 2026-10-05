<!-- ไฟล์: includes/pagination.php -->
<div class="d-flex justify-content-between align-items-center p-3 border-top pagination-wrapper bg-white">
    <div class="d-flex align-items-center">
        <span class="small text-muted me-2">รายการต่อหน้า:</span>
        <!-- เปลี่ยนมาใช้คลาส items-per-page-select -->
        <select class="form-select form-select-sm border-dark rounded-0 items-per-page-select" style="width: 70px;">
            <option value="5" selected>5</option>
            <option value="10">10</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
    </div>
    <div class="d-flex align-items-center">
        <!-- เปลี่ยนมาใช้คลาส page-info-text, prev-page-btn, next-page-btn -->
        <span class="small text-muted me-3 page-info-text">0 - 0 จาก 0</span>
        <button class="btn btn-sm btn-light border-dark rounded-0 me-1 prev-page-btn"><i class="bi bi-chevron-left"></i></button>
        <button class="btn btn-sm btn-light border-dark rounded-0 next-page-btn"><i class="bi bi-chevron-right"></i></button>
    </div>
</div>

<!-- // Script จัดการการกรองข้อมูลและแบ่งหน้า ของตารางกลาง รายการสิ่งของ (Manage Items) ในหน้า views/admin/manage_items.php -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const rows = Array.from(document.querySelectorAll('.item-row'));
        const categoryFilter = document.getElementById('categoryFilter');
        const searchInput = document.getElementById('searchInput');
        const itemsPerPageSelect = document.getElementById('itemsPerPage');
        const prevPageBtn = document.getElementById('prevPage');
        const nextPageBtn = document.getElementById('nextPage');
        const pageInfo = document.getElementById('pageInfo');
        const noDataRow = document.getElementById('noDataRow');

        let currentPage = 1;
        let itemsPerPage = parseInt(itemsPerPageSelect.value);
        let filteredRows = [...rows];

        // ฟังก์ชันอัปเดตตาราง
        function updateTable() {
            const cat = categoryFilter.value;
            const search = searchInput.value.toLowerCase();

            // 1. กรองข้อมูล (หมวดหมู่ และ คำค้นหา)
            filteredRows = rows.filter(row => {
                const rowCat = row.getAttribute('data-category');
                const rowName = row.querySelector('td:first-child').textContent.toLowerCase();
                const matchCat = (cat === 'all' || rowCat === cat);
                const matchSearch = rowName.includes(search);
                return matchCat && matchSearch;
            });

            // 2. ซ่อนทุกแถวก่อน
            rows.forEach(row => row.style.display = 'none');

            // 3. คำนวณการแบ่งหน้า
            const totalItems = filteredRows.length;
            const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, totalItems);

            // 4. แสดงเฉพาะแถวในหน้าที่เลือก
            for (let i = startIndex; i < endIndex; i++) {
                filteredRows[i].style.display = '';
            }

            // 5. อัปเดตข้อความ Pagination และปุ่ม
            if (totalItems === 0) {
                noDataRow.style.display = '';
                pageInfo.textContent = `0 - 0 of 0`;
            } else {
                noDataRow.style.display = 'none';
                
                pageInfo.textContent = `${startIndex + 1} - ${endIndex} of ${totalItems}`;
            }

            prevPageBtn.disabled = currentPage === 1;
            nextPageBtn.disabled = currentPage === totalPages;
        }

        // เพิ่ม Event Listeners ให้ส่วนควบคุมต่างๆ
        categoryFilter.addEventListener('change', () => {
            currentPage = 1;
            updateTable();
        });
        searchInput.addEventListener('input', () => {
            currentPage = 1;
            updateTable();
        });
        itemsPerPageSelect.addEventListener('change', (e) => {
            itemsPerPage = parseInt(e.target.value);
            currentPage = 1;
            updateTable();
        });

        prevPageBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                updateTable();
            }
        });

        nextPageBtn.addEventListener('click', () => {
            const totalPages = Math.ceil(filteredRows.length / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                updateTable();
            }
        });

        // เรียกทำงานครั้งแรก
        updateTable();
    });
</script>