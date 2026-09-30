// <!-- 🟢 สคริปต์ระบบตะกร้า (Cart Logic) -->

let cart = {}; // เก็บข้อมูลตะกร้า รูปแบบ { item_id: {name, category, img, qty, maxStock} }

// 1. ฟังก์ชันปุ่มบวกลบก่อนกดหยิบใส่ตะกร้า
function adjustInputQty(itemId, change, maxStock) {
    let input = document.getElementById('input_qty_' + itemId);
    let currentVal = parseInt(input.value) || 1;
    let newVal = currentVal + change;
    
    if (newVal < 1) newVal = 1;
    if (maxStock !== undefined && newVal > maxStock) newVal = maxStock;
    input.value = newVal;
}

// 2. ฟังก์ชันเพิ่มลงตะกร้า
function addToCart(id, name, category, img, maxStock) {
    let qtyToAdd = parseInt(document.getElementById('input_qty_' + id).value) || 1;

    if (cart[id]) {
        if (cart[id].qty + qtyToAdd > maxStock) {
            alert('คุณเลือกเกินจำนวนคงเหลือในคลัง!');
            return;
        }
        cart[id].qty += qtyToAdd;
    } else {
        cart[id] = { id: id, name: name, category: category, img: img, qty: qtyToAdd, maxStock: maxStock };
    }
    
    document.getElementById('input_qty_' + id).value = 1; // Reset ค่าช่อง input
    renderCart();
}

// 3. ฟังก์ชันปรับจำนวนในตะกร้า[cite: 34]
function updateCartQty(id, change) {
    if (cart[id]) {
        let newVal = cart[id].qty + change;
        if (newVal < 1) return; // ไม่ให้ต่ำกว่า 1 (ถ้าจะลบให้กดปุ่มกากบาท)
        if (newVal > cart[id].maxStock) {
            alert('เกินจำนวนคงเหลือในคลัง');
            return;
        }
        cart[id].qty = newVal;
        renderCart();
    }
}

// 4. ฟังก์ชันลบออกจากตะกร้า[cite: 34]
function removeFromCart(id) {
    delete cart[id];
    renderCart();
}

// 5. เรนเดอร์ตะกร้าให้แสดงบนหน้าจอ
function renderCart() {
    let tbody = document.getElementById('cartBody');
    let hiddenContainer = document.getElementById('hiddenCartInputs');
    let countBadge = document.getElementById('cartTotalCount');
    
    tbody.innerHTML = '';
    hiddenContainer.innerHTML = '';
    
    let totalItems = Object.keys(cart).length;
    countBadge.innerText = totalItems + ' รายการ';

    if (totalItems === 0) {
        tbody.innerHTML = `
            <tr id="emptyCartRow">
                <td colspan="4" class="text-center py-5 text-muted border-0">
                    <i class="bi bi-cart-x fs-1 d-block mb-2 opacity-50"></i>
                    ยังไม่มีรายการสิ่งของในตะกร้า
                </td>
            </tr>`;
        return;
    }

    Object.values(cart).forEach(item => {
        // วาดตาราง[cite: 34]
        tbody.innerHTML += `
            <tr class="bg-white border-bottom">
                <td style="width: 80px;" class="ps-4">
                    <img src="${item.img}" class="rounded border" style="width: 60px; height: 60px; object-fit: cover;">
                </td>
                <td>
                    <div class="small text-muted">${item.category}</div>
                    <div class="fw-bold text-dark fs-6">${item.name}</div>
                </td>
                <td style="width: 150px;">
                    <div class="input-group input-group-sm">
                        <button class="btn btn-outline-secondary" type="button" onclick="updateCartQty('${item.id}', -1)">-</button>
                        <input type="text" class="form-control text-center fw-bold bg-light" value="${item.qty}" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="updateCartQty('${item.id}', 1)">+</button>
                    </div>
                </td>
                <td style="width: 80px;" class="text-center pe-4">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-3" onclick="removeFromCart('${item.id}')">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </td>
            </tr>
        `;

        // สร้าง Input ซ่อน เพื่อส่งไปประมวลผลตอนกดบันทึก
        hiddenContainer.innerHTML += `
            <input type="hidden" name="items[${item.id}]" value="${item.qty}">
        `;
    });
}

// 6. ตรวจสอบก่อน Submit แบบฟอร์ม
function submitRequest() {
    if (Object.keys(cart).length === 0) {
        alert('กรุณาเลือกสิ่งของอย่างน้อย 1 รายการ');
        return;
    }
    
    // ตรวจสอบว่ากรอกข้อมูลครบไหม
    const form = document.getElementById('requestForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // ส่งข้อมูลไปที่ backend
    form.submit();
}
