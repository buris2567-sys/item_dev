<?php
if (!defined('APP_RUNNING')) exit('Forbidden');

// ตรวจสอบสิทธิ์ว่าเป็น User
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'User') {$_SESSION['error'] = 'กรุณาเข้าสู่ระบบ';
    header("Location: index.php");
    exit;
}

$user_id =$_SESSION['user_id'];

// ดึงข้อมูลผู้ใช้ และ แผนก
$stmt =$pdo->prepare("
    SELECT u.full_name, d.department_name, sd.sub_department_name 
    FROM users u 
    LEFT JOIN departments d ON u.department_id = d.department_id
    LEFT JOIN sub_departments sd ON u.sub_department_id = sd.sub_department_id
    WHERE u.user_id = ?
");
$stmt->execute([$user_id]);$userInfo = $stmt->fetch();$dept_name = ($userInfo['sub_department_name'] ?? '') . ' ' . ($userInfo['department_name'] ?? '');

// ดึงหมวดหมู่
$categories =$pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll();

// ดึงรายการสิ่งของที่มีสต็อกมากกว่า 0
$items =$pdo->query("
    SELECT i.*, c.name as category_name 
    FROM items i 
    LEFT JOIN categories c ON i.category_id = c.category_id 
    WHERE i.current_stock > 0
    ORDER BY i.name ASC
")->fetchAll();

$pageTitle = "แบบฟอร์มขอเบิกพัสดุ";
include 'includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    body, .container-fluid, .card, .btn, input, select, textarea { font-family: 'Prompt', sans-serif !important; }
    .step-box { border: 2px solid #e9ecef; color: #adb5bd; transition: 0.3s; }
    .step-box.active { border-color: #ffc107; color: #000; background-color: #fff8e1; font-weight: bold; }
    .item-card:hover { border-color: #ffc107 !important; box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    #cartPanel { bottom: 0; left: 0; right: 0; z-index: 1000; box-shadow: 0 -4px 10px rgba(0,0,0,0.1); }
</style>

<div class="container-fluid p-0">
    <div class="row g-0 flex-nowrap">
        <?php include 'includes/sidebar_user.php'; ?> <!-- สมมติว่ามี sidebar สำหรับ user -->

        <div class="col d-flex flex-column" style="min-height: 100vh; background-color: #e9ecef; position: relative;">
            
            <form id="requestForm" action="actions/user/submit_request.php" method="POST" class="p-4 flex-grow-1 pb-5 mb-5">
                
                <h3 class="fw-bolder mb-1 text-dark">แบบฟอร์มขอเบิกพัสดุและสื่อสิ่งพิมพ์</h3>
                <span class="text-muted small">กรอกข้อมูลวัตถุประสงค์และรายละเอียดการนำไปใช้ก่อนเลือกรายการพัสดุ</span>

                <!-- ขั้นตอน (Step Indicators) -->
                <div class="d-flex gap-3 my-4">
                    <div id="indicatorStep1" class="step-box active rounded-3 p-3 flex-fill bg-white shadow-sm d-flex align-items-center">
                        <span class="badge bg-warning text-dark fs-5 me-3 rounded-1">1</span>
                        <div>
                            <div class="small text-dark fw-bold">ขั้นตอนที่ 1</div>
                            <div class="fs-6">ข้อมูลทั่วไป (General Info)</div>
                        </div>
                    </div>
                    <div id="indicatorStep2" class="step-box rounded-3 p-3 flex-fill bg-white shadow-sm d-flex align-items-center">
                        <span class="badge bg-secondary text-white fs-5 me-3 rounded-1" id="badgeStep2">2</span>
                        <div>
                            <div class="small">ขั้นตอนที่ 2</div>
                            <div class="fs-6">เลือกสิ่งของ/ของที่ระลึก</div>
                        </div>
                    </div>
                </div>

                <!-- ================= STEP 1: ข้อมูลทั่วไป ================= -->
                <div id="step1Content" class="card border-0 rounded-4 shadow-sm bg-white">
                    <div class="card-header bg-primary text-white border-0 py-3 rounded-top-4 px-4">
                        <h5 class="fw-bold mb-0"><i class="bi bi-file-earmark-text me-2"></i>ข้อมูลทั่วไป</h5>
                    </div>
                    <div class="card-body p-5">
                        
                        <!-- ข้อมูลผู้เบิก (Auto Fill) -->
                        <div class="row mb-4">
                            <div class="col-md-2 text-end fw-bold text-muted">สถานะ:</div>
                            <div class="col-md-10"><span class="badge bg-warning text-dark px-3 py-2 rounded-pill">รอผู้รับผิดชอบพิจารณา</span></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-2 text-end fw-bold text-muted">วันที่:</div>
                            <div class="col-md-10 fw-bolder"><?= date('d/m/Y') ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-2 text-end fw-bold text-muted">ผู้เบิก:</div>
                            <div class="col-md-10 fw-bolder text-dark"><?= htmlspecialchars($userInfo['full_name']) ?></div>
                        </div>
                        <div class="row mb-5 pb-4 border-bottom">
                            <div class="col-md-2 text-end fw-bold text-muted">สังกัด/หน่วยงาน:</div>
                            <div class="col-md-10 fw-bolder text-dark"><?= htmlspecialchars($dept_name) ?></div>
                        </div>

                        <!-- ฟอร์มกรอกข้อมูล -->
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-3 text-end fw-bold"><span class="text-danger">*</span> ใช้ในวันที่:</div>
                            <div class="col-md-7"><input type="date" name="use_date" class="form-control" required></div>
                        </div>
                        
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-3 text-end fw-bold"><span class="text-danger">*</span> ประเภทของงาน:</div>
                            <div class="col-md-7">
                                <select name="event_type" class="form-select" required>
                                    <option value="">เลือกประเภทของงาน...</option>
                                    <option value="ประชุม/สัมมนา">ประชุม/สัมมนา</option>
                                    <option value="จัดนิทรรศการ">จัดนิทรรศการ</option>
                                    <option value="แจกผู้เยี่ยมชม">แจกผู้เยี่ยมชม</option>
                                    <option value="อื่นๆ">อื่นๆ</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <div class="col-md-3 text-end fw-bold"><span class="text-danger">*</span> ชื่องาน/โครงการ:</div>
                            <div class="col-md-7"><input type="text" name="event_name" class="form-control" placeholder="ระบุชื่องาน..." required></div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <div class="col-md-3 text-end fw-bold"><span class="text-danger">*</span> สถานที่นำไปใช้:</div>
                            <div class="col-md-7"><input type="text" name="location" class="form-control" required></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3 text-end fw-bold pt-2"><span class="text-danger">*</span> วัตถุประสงค์เพื่อใช้งาน:</div>
                            <div class="col-md-7"><textarea name="purpose" class="form-control" rows="3" required></textarea></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3 text-end fw-bold pt-2">หมายเหตุ:</div>
                            <div class="col-md-7"><textarea name="user_note" class="form-control" rows="2"></textarea></div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-warning fw-bolder px-5 rounded-pill shadow-sm" onclick="goToStep(2)">ถัดไป: เลือกสิ่งของ <i class="bi bi-arrow-right"></i></button>
                        </div>
                    </div>
                </div>

                <!-- ================= STEP 2: เลือกสิ่งของ ================= -->
                <div id="step2Content" class="d-none">
                    
                    <div class="card border-0 rounded-4 shadow-sm bg-white mb-4">
                        <div class="card-body p-4">
                            
                            <!-- แท็บหมวดหมู่ และ ค้นหา -->
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                                <ul class="nav nav-pills gap-2" id="categoryTabs">
                                    <li class="nav-item">
                                        <button class="nav-link active rounded-pill text-dark fw-bold px-4" data-category="all" type="button" onclick="filterItems('all')">ทั้งหมด</button>
                                    </li>
                                    <?php foreach ($categories as$cat): ?>
                                    <li class="nav-item">
                                        <button class="nav-link rounded-pill text-dark fw-bold px-4 bg-light" data-category="<?= $cat['category_id'] ?>" type="button" onclick="filterItems('<?= $cat['category_id'] ?>')"><?= htmlspecialchars($cat['name']) ?></button>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <div class="input-group w-auto">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                                    <input type="text" id="searchItem" class="form-control border-start-0" placeholder="ค้นหาสิ่งของ..." oninput="searchItemGrid()">
                                </div>
                            </div>

                            <!-- Grid สินค้า -->
                            <div class="row g-4" id="itemGrid">
                                <?php foreach ($items as $item):$images = json_decode($item['images'], true);$imgSrc = !empty($images) ? 'assets/uploads/items/' .$images[0] : 'assets/images/placeholder.jpg';
                                ?>
                                <div class="col-md-4 col-lg-3 item-card-wrapper" data-category="<?= $item['category_id'] ?>" data-name="<?= strtolower($item['name']) ?>">
                                    <div class="card h-100 border-2 rounded-3 item-card transition-all">
                                        <img src="<?= $imgSrc ?>" class="card-img-top p-3 object-fit-contain" style="height: 180px;">
                                        <div class="card-body border-top bg-light pt-3 d-flex flex-column">
                                            <h6 class="fw-bolder text-dark mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                            <small class="text-muted mb-3 d-block">คงเหลือ: <?= number_format($item['current_stock']) ?> ชิ้น</small>
                                            
                                            <div class="mt-auto">
                                                <div class="input-group input-group-sm mb-2">
                                                    <button class="btn btn-outline-secondary" type="button" onclick="adjustInputQty('<?= $item['item_id'] ?>', -1)">-</button>
                                                    <input type="number" id="qty_<?= $item['item_id'] ?>" class="form-control text-center fw-bold" value="1" min="1" max="<?= $item['current_stock'] ?>">
                                                    <button class="btn btn-outline-secondary" type="button" onclick="adjustInputQty('<?= $item['item_id'] ?>', 1)">+</button>
                                                </div>
                                                <button type="button" class="btn btn-warning w-100 fw-bold rounded-1 shadow-sm" 
                                                        onclick="addToCart('<?= $item['item_id'] ?>', '<?= htmlspecialchars($item['name']) ?>', '<?= $imgSrc ?>', <?= $item['current_stock'] ?>)">
                                                    เพิ่มลงรายการ
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                        </div>
                    </div>

                    <div class="text-start">
                        <button type="button" class="btn btn-secondary fw-bolder px-5 rounded-pill shadow-sm" onclick="goToStep(1)"><i class="bi bi-arrow-left"></i> ย้อนกลับไปแก้ไขข้อมูล</button>
                    </div>
                </div>

                <!-- ================= ตะกร้าสินค้า (Bottom Panel) ================= -->
                <div id="cartPanel" class="position-fixed bg-white border-top border-warning border-4 p-3 d-none">
                    <div class="container-fluid">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bolder mb-0"><i class="bi bi-basket-fill text-warning me-2"></i>รายการที่ขอเบิก ทั้งหมด (<span id="cartTotalCount">0</span> รายการ)</h5>
                            <button type="submit" class="btn btn-success fw-bolder px-5 rounded-pill shadow">บันทึกและส่งคำขอเบิก <i class="bi bi-check-circle ms-1"></i></button>
                        </div>
                        
                        <div id="cartList" class="d-flex flex-column gap-2" style="max-height: 200px; overflow-y: auto;">
                            <!-- รายการตะกร้าจะถูกแสดงที่นี่ผ่าน JS -->
                        </div>
                        
                        <!-- พื้นที่สำหรับสร้าง hidden input ส่งไป backend -->
                        <div id="hiddenInputsContainer"></div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
// --- ระบบสลับ Step ---
function goToStep(step) {
    if (step === 2) {
        // Basic validation ก่อนไป step 2
        const form = document.getElementById('requestForm');
        if(!form.reportValidity()) return; 

        document.getElementById('step1Content').classList.add('d-none');
        document.getElementById('step2Content').classList.remove('d-none');
        
        document.getElementById('indicatorStep1').classList.remove('active');
        document.getElementById('indicatorStep2').classList.add('active');
        document.getElementById('badgeStep2').classList.replace('bg-secondary', 'bg-warning');
        document.getElementById('badgeStep2').classList.replace('text-white', 'text-dark');
        
        updateCartUI(); // แสดงตะกร้าถ้ามีของ
    } else {
        document.getElementById('step2Content').classList.add('d-none');
        document.getElementById('step1Content').classList.remove('d-none');
        
        document.getElementById('indicatorStep2').classList.remove('active');
        document.getElementById('indicatorStep1').classList.add('active');
        document.getElementById('badgeStep2').classList.replace('bg-warning', 'bg-secondary');
        document.getElementById('badgeStep2').classList.replace('text-dark', 'text-white');
        
        document.getElementById('cartPanel').classList.add('d-none'); // ซ่อนตะกร้าตอนอยู่ step 1
    }
}

// --- ระบบ Filter & Search UI ---
function filterItems(categoryId) {
    // จัดการปุ่ม Active
    document.querySelectorAll('#categoryTabs .nav-link').forEach(btn => {
        btn.classList.remove('active', 'bg-warning', 'bg-light');
        if(btn.getAttribute('data-category') === categoryId) {
            btn.classList.add('active', 'bg-warning');
        } else {
            btn.classList.add('bg-light');
        }
    });

    // กรอง Card
    const cards = document.querySelectorAll('.item-card-wrapper');
    cards.forEach(card => {
        if (categoryId === 'all' || card.getAttribute('data-category') === categoryId) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function searchItemGrid() {
    const keyword = document.getElementById('searchItem').value.toLowerCase();
    const cards = document.querySelectorAll('.item-card-wrapper');
    cards.forEach(card => {
        const name = card.getAttribute('data-name');
        card.style.display = name.includes(keyword) ? 'block' : 'none';
    });
}

function adjustInputQty(itemId, amount) {
    const input = document.getElementById('qty_' + itemId);
    let val = parseInt(input.value) + amount;
    const max = parseInt(input.getAttribute('max'));
    if (val < 1) val = 1;
    if (val > max) val = max;
    input.value = val;
}

// --- ระบบ Cart Logic ---
let cart = {}; // Store items as { itemId: {name, img, qty, maxStock} }

function addToCart(itemId, name, img, maxStock) {
    const input = document.getElementById('qty_' + itemId);
    const qtyToAdd = parseInt(input.value);

    if (cart[itemId]) {
        let newQty = cart[itemId].qty + qtyToAdd;
        if (newQty > maxStock) newQty = maxStock;
        cart[itemId].qty = newQty;
    } else {
        cart[itemId] = { name, img, qty: qtyToAdd, maxStock };
    }
    
    // Reset input back to 1
    input.value = 1;
    updateCartUI();
}

function removeCartItem(itemId) {
    delete cart[itemId];
    updateCartUI();
}

function updateCartQty(itemId, amount) {
    let newQty = cart[itemId].qty + amount;
    if(newQty < 1) newQty = 1;
    if(newQty > cart[itemId].maxStock) newQty = cart[itemId].maxStock;
    cart[itemId].qty = newQty;
    updateCartUI();
}

function updateCartUI() {
    const cartPanel = document.getElementById('cartPanel');
    const cartList = document.getElementById('cartList');
    const hiddenContainer = document.getElementById('hiddenInputsContainer');
    const totalCountSpan = document.getElementById('cartTotalCount');
    
    const itemIds = Object.keys(cart);
    totalCountSpan.innerText = itemIds.length;

    if (itemIds.length === 0) {
        cartPanel.classList.add('d-none');
        cartList.innerHTML = '';
        hiddenContainer.innerHTML = '';
        return;
    }

    // เปิดตะกร้าถ้าอยู่ step 2
    if(!document.getElementById('step2Content').classList.contains('d-none')) {
        cartPanel.classList.remove('d-none');
    }

    cartList.innerHTML = '';
    hiddenContainer.innerHTML = '';

    itemIds.forEach(id => {
        const item = cart[id];
        
        // 1. สร้าง UI ตะกร้า
        cartList.innerHTML += `
            <div class="d-flex align-items-center justify-content-between border rounded p-2 bg-light">
                <div class="d-flex align-items-center gap-3">
                    <img src="${item.img}" class="rounded border" style="width: 50px; height: 50px; object-fit: contain; background: white;">
                    <div>
                        <div class="fw-bolder text-dark">${item.name}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="input-group input-group-sm" style="width: 120px;">
                        <button class="btn btn-outline-dark" type="button" onclick="updateCartQty('${id}', -1)">-</button>
                        <input type="text" class="form-control text-center fw-bold bg-white" value="${item.qty}" readonly>
                        <button class="btn btn-outline-dark" type="button" onclick="updateCartQty('${id}', 1)">+</button>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-1" onclick="removeCartItem('${id}')"><i class="bi bi-x-lg"></i></button>
                </div>
            </div>
        `;

        // 2. สร้าง Hidden Input สำหรับส่งไป Backend (Array รูปแบบ: items[item_id] = qty)
        hiddenContainer.innerHTML += `<input type="hidden" name="items[${id}]" value="${item.qty}">`;
    });
}
</script>

<?php include 'includes/footer.php'; ?>