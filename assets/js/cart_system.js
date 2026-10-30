// 🟢 สคริปต์ระบบตะกร้า (Cart Logic)
let cart = {}; // ตัวแปรออบเจ็กต์สำหรับเก็บข้อมูลสินค้าในตะกร้า 

function adjustInputQty(itemId, change) {
    let input = document.getElementById('input_qty_' + itemId);
    let currentVal = parseInt(input.value) || 1;
    let newVal = currentVal + change;
    if (newVal < 1) newVal = 1;
    input.value = newVal;
}

function addToCart(id, name, category, img) {
    let qtyToAdd = parseInt(document.getElementById('input_qty_' + id).value) || 1;

    if (cart[id]) {
        cart[id].qty += qtyToAdd;
    } else {
        cart[id] = { id: id, name: name, category: category, img: img, qty: qtyToAdd };
    }

    document.getElementById('input_qty_' + id).value = 1; 
    renderCart();
}

function updateCartQty(id, change) {
    if (cart[id]) {
        let newVal = cart[id].qty + change;
        if (newVal < 1) return; 
        cart[id].qty = newVal;
        renderCart();
    }
}

// 🟢 ฟังก์ชันใหม่: รับค่าจำนวนที่ผู้ใช้พิมพ์กรอกเองในตะกร้า
function manualUpdateCartQty(id, newValue) {
    if (cart[id]) {
        let parsedValue = parseInt(newValue, 10);
        
        // ตรวจสอบว่าค่าที่พิมพ์มาถูกต้องไหม ถ้ากรอกตัวอักษรหรือติดลบ ให้บังคับเป็น 1
        if (isNaN(parsedValue) || parsedValue < 1) {
            parsedValue = 1;
        }

        // อัปเดตค่าลงในตะกร้า
        cart[id].qty = parsedValue;
        
        // วาดตะกร้าใหม่เพื่ออัปเดต Hidden Input ให้พร้อมส่ง
        renderCart();
    }
}

function removeFromCart(id) {
    delete cart[id];
    renderCart();
}

function renderCart() {
    let tbody = document.getElementById('cartBody');
    let hiddenContainer = document.getElementById('hiddenCartInputs');
    let countBadge = document.getElementById('cartTotalCount');

    tbody.innerHTML = '';
    hiddenContainer.innerHTML = '';

    let totalItems = Object.keys(cart).length;
    if (countBadge) countBadge.innerText = totalItems + ' รายการ';

    if (totalItems === 0) {
        tbody.innerHTML = `
            <tr id="emptyCartRow">
                <td colspan="4" class="text-center py-5 text-muted border-0">
                    <div class="bg-light rounded-circle d-inline-flex justify-content-center align-items-center mb-3 shadow-sm" style="width: 70px; height: 70px;">
                        <i class="bi bi-cart-x fs-2 text-secondary opacity-50"></i>
                    </div>
                    <h6 class="fw-bold mb-0">ยังไม่มีรายการสิ่งของในตะกร้า</h6>
                </td>
            </tr>`;
        return;
    }

    Object.values(cart).forEach(item => {
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
                        
                        <!-- 🟢 แก้ไขตรงนี้: เปลี่ยนเป็น type="number" เอา readonly ออก และเพิ่ม onchange -->
                        <input type="number" class="form-control text-center fw-bold bg-light" 
                               value="${item.qty}" min="1" 
                               onchange="manualUpdateCartQty('${item.id}', this.value)">
                        
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

        // Input สำหรับส่งค่าไปบันทึก (PHP จะได้รับจำนวนที่อัปเดตล่าสุดเสมอ)
        hiddenContainer.innerHTML += `
            <input type="hidden" name="item_id[]" value="${item.id}">
            <input type="hidden" name="qty[]" value="${item.qty}">
        `;
    });
}

function submitRequest() {
    if (Object.keys(cart).length === 0) {
        alert('กรุณาเลือกสิ่งของอย่างน้อย 1 รายการ');
        return;
    }

    const form = document.getElementById('requestForm');
    if (!form.checkValidity()) {
        form.reportValidity(); 
        return;
    }

    form.submit();
}