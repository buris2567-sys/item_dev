
    document.addEventListener("DOMContentLoaded", function() {
        const txRows = Array.from(document.querySelectorAll('.tx-row'));
        const searchTxInput = document.getElementById('searchTxInput');
        const txPerPageSelect = document.getElementById('txPerPage');
        const txPrevPageBtn = document.getElementById('txPrevPage');
        const txNextPageBtn = document.getElementById('txNextPage');
        const txPageInfo = document.getElementById('txPageInfo');
        const noTxFilterRow = document.getElementById('noTxFilterRow');

        let currentTxPage = 1;
        let txPerPage = parseInt(txPerPageSelect.value);
        let filteredTxRows = [...txRows];

        function updateTxTable() {
            const query = searchTxInput.value.trim().toLowerCase();

            filteredTxRows = txRows.filter(row => {
                const text = row.getAttribute('data-search-text') || '';
                return text.includes(query);
            });

            txRows.forEach(row => row.style.display = 'none');

            const totalItems = filteredTxRows.length;
            const totalPages = Math.ceil(totalItems / txPerPage) || 1;
            if (currentTxPage > totalPages) currentTxPage = totalPages;
            if (currentTxPage < 1) currentTxPage = 1;

            const startIndex = (currentTxPage - 1) * txPerPage;
            const endIndex = Math.min(startIndex + txPerPage, totalItems);

            for (let i = startIndex; i < endIndex; i++) {
                filteredTxRows[i].style.display = '';
            }

            if (totalItems === 0) {
                if (noTxFilterRow) noTxFilterRow.style.display = '';
                txPageInfo.textContent = '0 - 0 of 0';
            } else {
                if (noTxFilterRow) noTxFilterRow.style.display = 'none';
                txPageInfo.textContent = `${startIndex + 1} - ${endIndex} of ${totalItems}`;
            }

            txPrevPageBtn.disabled = (currentTxPage === 1 || totalItems === 0);
            txNextPageBtn.disabled = (currentTxPage === totalPages || totalItems === 0);
        }

        if (searchTxInput) {
            searchTxInput.addEventListener('input', () => {
                currentTxPage = 1;
                updateTxTable();
            });
        }
        if (txPerPageSelect) {
            txPerPageSelect.addEventListener('change', (e) => {
                txPerPage = parseInt(e.target.value);
                currentTxPage = 1;
                updateTxTable();
            });
        }
        if (txPrevPageBtn) {
            txPrevPageBtn.addEventListener('click', () => {
                if (currentTxPage > 1) {
                    currentTxPage--;
                    updateTxTable();
                }
            });
        }
        if (txNextPageBtn) {
            txNextPageBtn.addEventListener('click', () => {
                const totalPages = Math.ceil(filteredTxRows.length / txPerPage);
                if (currentTxPage < totalPages) {
                    currentTxPage++;
                    updateTxTable();
                }
            });
        }

        if (txRows.length > 0) {
            updateTxTable();
        }
    });