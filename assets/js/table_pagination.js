// ไฟล์: assets/js/table_pagination.js

function initTablePagination(config) {
    // 1. ค้นหาแถวและตัวหุ้ม Pagination ตามที่ส่งค่ามา
    const rows = Array.from(document.querySelectorAll(config.rowSelector));
    const wrapper = document.querySelector(config.wrapperSelector);
    const noDataRow = document.querySelector(config.noDataSelector);

    if (!wrapper || rows.length === 0) return;

    // 2. ค้นหาปุ่มต่างๆ ภายใน Wrapper นี้เท่านั้น (เพื่อไม่ให้ชนกับตารางอื่น)
    const itemsPerPageSelect = wrapper.querySelector('.items-per-page-select');
    const prevPageBtn = wrapper.querySelector('.prev-page-btn');
    const nextPageBtn = wrapper.querySelector('.next-page-btn');
    const pageInfo = wrapper.querySelector('.page-info-text');

    let currentPage = 1;
    let itemsPerPage = parseInt(itemsPerPageSelect.value);
    let filteredRows = [...rows];

    function updateTable() {
        // ใช้เงื่อนไขการกรอง (Filter) ที่ส่งมาจากหน้าหลัก (ถ้ามี)
        if (typeof config.filterLogic === 'function') {
            filteredRows = rows.filter(config.filterLogic);
        } else {
            filteredRows = [...rows];
        }

        // ซ่อนทุกแถว
        rows.forEach(row => row.style.display = 'none');

        // คำนวณหน้า
        const totalItems = filteredRows.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, totalItems);

        // แสดงเฉพาะแถวในหน้าปัจจุบัน
        for (let i = startIndex; i < endIndex; i++) {
            filteredRows[i].style.display = '';
        }

        // อัปเดตข้อความและสถานะปุ่ม
        if (totalItems === 0) {
            if (noDataRow) noDataRow.style.display = '';
            pageInfo.textContent = `0 - 0 of 0`;
        } else {
            if (noDataRow) noDataRow.style.display = 'none';
            pageInfo.textContent = `${startIndex + 1} - ${endIndex} of ${totalItems}`;
        }

        prevPageBtn.disabled = currentPage === 1;
        nextPageBtn.disabled = currentPage === totalPages;
    }

    // 3. จัดการ Event (กดปุ่ม, เปลี่ยนจำนวนต่อหน้า)
    itemsPerPageSelect.addEventListener('change', (e) => {
        itemsPerPage = parseInt(e.target.value);
        currentPage = 1;
        updateTable();
    });

    prevPageBtn.addEventListener('click', (e) => {
        e.preventDefault();
        if (currentPage > 1) { currentPage--; updateTable(); }
    });

    nextPageBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const totalPages = Math.ceil(filteredRows.length / itemsPerPage);
        if (currentPage < totalPages) { currentPage++; updateTable(); }
    });

    // 4. ผูกช่อง Search/Filter เข้ากับฟังก์ชันอัปเดตตาราง
    if (config.triggerInputs && config.triggerInputs.length > 0) {
        config.triggerInputs.forEach(inputEl => {
            if (inputEl) {
                inputEl.addEventListener('input', () => { currentPage = 1; updateTable(); });
                inputEl.addEventListener('change', () => { currentPage = 1; updateTable(); });
            }
        });
    }

    // ทำงานครั้งแรก
    updateTable();
}



