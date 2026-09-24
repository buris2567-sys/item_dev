<!-- แถบแบ่งหน้า (Pagination) -->
<div class="d-flex justify-content-between align-items-center p-3 border-top">
    <div class="d-flex align-items-center">
        <span class="small text-muted me-2">Items per page:</span>
        <select id="itemsPerPage" class="form-select form-select-sm border-dark rounded-0" style="width: 70px;">
            <option value="5">5</option>
            <option value="10" selected>10</option>
            <option value="100">100</option>
        </select>
    </div>
    <div class="d-flex align-items-center">
        <span class="small text-muted me-3" id="pageInfo">1 - 10 of 0</span>
        <button class="btn btn-sm btn-light border-dark rounded-0 me-1" id="prevPage"><i class="bi bi-chevron-left"></i></button>
        <button class="btn btn-sm btn-light border-dark rounded-0" id="nextPage"><i class="bi bi-chevron-right"></i></button>
    </div>
</div>