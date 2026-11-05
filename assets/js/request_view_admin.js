/**
 * request_view_admin.js
 * Handles "Approve All" and "Reject All" quick-action buttons on the admin request_view page.
 */

// ===== APPROVE ALL =====
function approveAll() {
    var insufficientItems = [];

    document.querySelectorAll('.approve-input').forEach(function (input) {
        if (input.hasAttribute('readonly')) return;

        var reqQty   = parseInt(input.getAttribute('data-req'))   || 0;
        var stockQty = parseInt(input.getAttribute('data-stock')) || 0;
        var itemName = input.getAttribute('data-name')            || 'ไม่ทราบชื่อ';

        if (stockQty < reqQty) {
            // Stock not enough – record for popup, set to max available
            insufficientItems.push({
                name:      itemName,
                requested: reqQty,
                stock:     stockQty
            });
            input.value = stockQty;
        } else {
            input.value = reqQty;
        }
    });

    // Re-calculate status badges after filling
    if (typeof calculateAllStatuses === 'function') calculateAllStatuses();

    // Show warning popup if any item had insufficient stock
    if (insufficientItems.length > 0) {
        showInsufficientStockModal(insufficientItems);
    }
}

// ===== REJECT ALL =====
function rejectAll() {
    if (!confirm('ต้องการตั้งค่าจำนวนอนุมัติทุกรายการเป็น 0 (ไม่อนุมัติ) ใช่หรือไม่?')) return;

    document.querySelectorAll('.approve-input').forEach(function (input) {
        if (input.hasAttribute('readonly')) return;
        input.value = 0;
    });

    if (typeof calculateAllStatuses === 'function') calculateAllStatuses();
}

// ===== MODAL: Insufficient Stock Warning =====
// แสดงตาราง 2 คอลัมน์: คงเหลือ | จำนวนที่ขอ — ไม่มี badge สี
function showInsufficientStockModal(items) {
    var rowsHtml = items.map(function (it, idx) {
        return '<tr>' +
                   '<td class="text-center text-muted ps-3" style="width:36px;">' + (idx + 1) + '</td>' +
                   '<td class="fw-semibold text-dark">' + escapeHtml(it.name) + '</td>' +
                   '<td class="text-center text-success fw-bold">' + it.stock + '</td>' +
                   '<td class="text-center text-dark fw-bold pe-3">' + it.requested + '</td>' +
               '</tr>';
    }).join('');

    document.getElementById('insufficientStockBody').innerHTML = rowsHtml;

    var bsModal = new bootstrap.Modal(document.getElementById('insufficientStockModal'));
    bsModal.show();
}

// Simple HTML escape helper
function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
