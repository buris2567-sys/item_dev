


// <!-- Script สำหรับ Filter ค้นหา และ Pagination -->

    const itemsPerPage = 10;
    let currentPage = 1;
    let allItemElements = [];
    let filteredItems = [];

    window.addEventListener('DOMContentLoaded', () => {
        allItemElements = Array.from(document.querySelectorAll('.item-card-wrapper'));
        document.getElementById('itemSearch').addEventListener('keyup', filterItems);
        document.getElementById('categoryFilter').addEventListener('change', filterItems);
        filterItems();
    });

    function filterItems() {
        const searchText = document.getElementById('itemSearch').value.toLowerCase().trim();
        const category = document.getElementById('categoryFilter').value.trim();

        filteredItems = allItemElements.filter(item => {
            const itemName = (item.getAttribute('data-name') || '').toLowerCase();
            const itemCat = (item.getAttribute('data-category') || '').trim();
            const matchSearch = itemName.includes(searchText);
            const matchCat = (category === 'all') || (itemCat === category);
            return matchSearch && matchCat;
        });

        currentPage = 1;
        renderPagination();
    }

    function renderPagination() {
        allItemElements.forEach(item => item.classList.add('d-none'));

        const totalItems = filteredItems.length;
        let totalPages = Math.ceil(totalItems / itemsPerPage);
        if (totalPages === 0) totalPages = 1;

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;

        for (let i = startIndex; i < endIndex && i < totalItems; i++) {
            filteredItems[i].classList.remove('d-none');
        }

        drawPaginationButtons(totalPages);
    }

    function drawPaginationButtons(totalPages) {
        const container = document.getElementById('paginationControls');

        if (filteredItems.length === 0) {
            container.innerHTML = `<div class="text-center py-5 text-muted">
                                    <i class="bi bi-box-seam fs-1 d-block mb-3 opacity-50"></i>
                                    <h5>ไม่พบสิ่งของที่ค้นหา</h5>
                                   </div>`;
            return;
        }

        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<ul class="pagination mb-0 shadow-sm">';
        html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link text-dark fw-bold bg-white" href="#" onclick="changePage(event, ${currentPage - 1})">&laquo;</a>
                 </li>`;

        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${currentPage === i ? 'active' : ''}">
                        <a class="page-link ${currentPage === i ? 'bg-warning border-warning text-dark' : 'text-dark bg-white'}" 
                           href="#" onclick="changePage(event, ${i})">${i}</a>
                     </li>`;
        }

        html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link text-dark fw-bold bg-white" href="#" onclick="changePage(event, ${currentPage + 1})">&raquo;</a>
                 </li>`;
        html += '</ul>';
        container.innerHTML = html;
    }

    function changePage(event, newPage) {
        event.preventDefault();
        currentPage = newPage;
        renderPagination();
        document.getElementById('itemsGrid').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
