// 🟢 สคริปต์ระบบตะกร้า (Cart Logic)
let cart = {}; // ตัวแปรออบเจ็กต์สำหรับเก็บข้อมูลสินค้าในตะกร้า 

// 1. ฟังก์ชันปุ่มเพิ่ม-ลดจำนวนสินค้า (ในหน้าเลือกสินค้าก่อนหยิบใส่ตะกร้า)
// 🟢 [แก้ไข] ตัดการรับค่าและเช็ค maxStock ออกทั้งหมด ให้บวกเลขขึ้นได้เรื่อยๆ ไม่มีลิมิต
function adjustInputQty(itemId, change) {
    let input = document.getElementById('input_qty_' + itemId);
    let currentVal = parseInt(input.value) || 1;
    let newVal = currentVal + change;

    // ตรวจสอบไม่ให้จำนวนลดลงต่ำกว่า 1 
    if (newVal < 1) newVal = 1;
    
    // อัปเดตค่ากลับลงไปในช่องกรอก
    input.value = newVal;
}

// 2. ฟังก์ชันเพิ่มสินค้าลงตะกร้า
// 🟢 [แก้ไข] ลบการส่งค่าและการเช็ค Alert สต๊อกเต็มออก อนุญาตให้กดใส่ตะกร้าได้ไม่จำกัดจำนวน
function addToCart(id, name, category, img) {
    // ดึงจำนวนที่ผู้ใช้ต้องการเพิ่มจากช่อง input ของสินค้านั้นๆ
    let qtyToAdd = parseInt(document.getElementById('input_qty_' + id).value) || 1;

    // ตรวจสอบว่ามีสินค้านี้อยู่ในตะกร้าแล้วหรือไม่
    if (cart[id]) {
        // 🟢 [แก้ไข] ถ้ามีอยู่แล้ว ให้บวกจำนวนเพิ่มเข้าไปเลย ไม่ต้องมีเงื่อนไขเช็คสต๊อก
        cart[id].qty += qtyToAdd;
    } else {
        // 🟢 [แก้ไข] สร้างข้อมูลสินค้าใหม่ในตะกร้า โดยไม่จำเป็นต้องเก็บค่า maxStock อีกต่อไป
        cart[id] = { id: id, name: name, category: category, img: img, qty: qtyToAdd };
    }

    // รีเซ็ตค่าช่อง input กลับเป็น 1 หลังจากหยิบใส่ตะกร้าสำเร็จ
    document.getElementById('input_qty_' + id).value = 1; 
    
    // สั่งให้วาดตะกร้าใหม่บนหน้าจอ
    renderCart();
}

// 3. ฟังก์ชันปุ่มเพิ่ม-ลดจำนวนสินค้า (ในหน้าตะกร้าสินค้า)
function updateCartQty(id, change) {
    if (cart[id]) {
        let newVal = cart[id].qty + change;
        
        // ถ้ากดลดจนเหลือ 0 จะไม่ลดต่อ (ต้องกดปุ่มกากบาทเพื่อลบออกแทน)
        if (newVal < 1) return; 
        
        // 🟢 [แก้ไข] ลบเงื่อนไข if (newVal > cart[id].maxStock) ทิ้งไป เพื่อไม่ให้มันเด้งบล็อกผู้ใช้
        cart[id].qty = newVal;
        
        // วาดตะกร้าใหม่
        renderCart();
    }
}

// 4. ฟังก์ชันลบสินค้าออกจากตะกร้า
// 🟢 [คงเดิม] ใช้สำหรับลบของที่ไม่ต้องการออกจากตะกร้า
function removeFromCart(id) {
    // ลบข้อมูลสินค้ารหัสนี้ออกจากออบเจ็กต์ cart
    delete cart[id];
    // วาดตะกร้าใหม่
    renderCart();
}

// 5. ฟังก์ชันวาด (Render) ตะกร้าสินค้าแสดงบนหน้าจอ
function renderCart() {
    let tbody = document.getElementById('cartBody');
    let hiddenContainer = document.getElementById('hiddenCartInputs');
    let countBadge = document.getElementById('cartTotalCount');

    // เคลียร์ข้อมูลเก่าทิ้งก่อนวาดใหม่
    tbody.innerHTML = '';
    hiddenContainer.innerHTML = '';

    // นับจำนวนรายการสินค้าทั้งหมดในตะกร้า (ไม่นับจำนวนชิ้นรวม)
    let totalItems = Object.keys(cart).length;
    countBadge.innerText = totalItems + ' รายการ';

    // ถ้าไม่มีสินค้าในตะกร้า ให้แสดงข้อความว่างเปล่า
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

    // นำข้อมูลในตะกร้ามาวนลูปเพื่อสร้างตารางแสดงผล
    Object.values(cart).forEach(item => {
        // 🟢 [คงเดิม] สร้าง HTML แถวตารางแสดงรายการสินค้าในตะกร้า
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
                        <!-- เรียกฟังก์ชัน updateCartQty เพื่อลดจำนวน -->
                        <button class="btn btn-outline-secondary" type="button" onclick="updateCartQty('${item.id}', -1)">-</button>
                        <!-- แสดงจำนวนสินค้า -->
                        <input type="text" class="form-control text-center fw-bold bg-light" value="${item.qty}" readonly>
                        <!-- เรียกฟังก์ชัน updateCartQty เพื่อเพิ่มจำนวน -->
                        <button class="btn btn-outline-secondary" type="button" onclick="updateCartQty('${item.id}', 1)">+</button>
                    </div>
                </td>
                <td style="width: 80px;" class="text-center pe-4">
                    <!-- เรียกฟังก์ชัน removeFromCart เพื่อลบสินค้า -->
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-3" onclick="removeFromCart('${item.id}')">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </td>
            </tr>
        `;

        // 🟢 [คงเดิม] สร้าง Input แบบซ่อน (Hidden) ไว้ในฟอร์ม เพื่อให้ส่งข้อมูลกลับไปที่ Backend (PHP) เมื่อกดปุ่มบันทึก
        hiddenContainer.innerHTML += `
            <input type="hidden" name="items[${item.id}]" value="${item.qty}">
        `;
    });
}

// 6. ฟังก์ชันตรวจสอบก่อนกดบันทึกคำขอ (Submit Form)
function submitRequest() {
    // เช็คว่าตะกร้าว่างเปล่าไหม
    if (Object.keys(cart).length === 0) {
        alert('กรุณาเลือกสิ่งของอย่างน้อย 1 รายการ');
        return;
    }

    // เช็คว่ากรอกข้อมูลในฟอร์ม (ชื่องาน, วันที่ ฯลฯ) ครบถ้วนและถูกต้องตามเงื่อนไข (Required) หรือไม่
    const form = document.getElementById('requestForm');
    if (!form.checkValidity()) {
        form.reportValidity(); // แสดงคำเตือนของ Browser ให้กรอกข้อมูลให้ครบ
        return;
    }

    // ส่งฟอร์มไปยัง backend
    form.submit();
}