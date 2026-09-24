<!-- Modal ป๊อปอัปสำหรับดูรายละเอียด -->
<div class="modal fade" id="viewItemModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- 🟢 ขยายขนาด Modal เป็นขนาดใหญ่ (lg) -->
        <div class="modal-content border-dark rounded-0" style="border-width: 2px !important;">
            <div class="modal-header bg-warning border-dark" style="border-bottom-width: 2px !important;">
                <h5 class="modal-title fw-bold text-dark">รายละเอียดสิ่งของ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                
                <!-- ส่วนแสดงรูปภาพ (Carousel) -->
                <!-- 🟢 ปรับความสูงเป็น 400px ให้ภาพใหญ่ขึ้น -->
                <div id="itemImageCarousel" class="carousel slide mb-4 border border-dark bg-light rounded" data-bs-ride="carousel" style="height: 400px;">
                    <div class="carousel-inner h-100" id="carouselImagesContainer">
                        <!-- รูปภาพจะถูกแทรกที่นี่ด้วย JS -->
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#itemImageCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#itemImageCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
                    </button>
                </div>

                <!-- ส่วนแสดงข้อมูล -->
                <h4 class="fw-bold mb-3" id="view_item_name">-</h4>
                
                <!-- 🟢 ส่วนแสดงคำอธิบาย (เพิ่มใหม่) -->
                <div class="mb-4">
                    <label class="fw-bold text-muted small mb-1">คำอธิบายรายละเอียด:</label>
                    <div id="view_item_desc" class="p-3 bg-light border border-secondary border-opacity-25 rounded text-dark" style="white-space: pre-wrap; min-height: 80px;">-</div>
                </div>

                <div class="d-flex justify-content-between align-items-center p-3 bg-white border border-secondary rounded shadow-sm">
                    <span class="fw-bold text-muted">จำนวนปัจจุบันในคลัง:</span>
                    <span class="fs-3 fw-bold text-dark" id="view_item_stock">0</span>
                </div>

            </div>
            <div class="modal-footer border-0 bg-light d-flex justify-content-center">
                <button type="button" class="btn btn-dark rounded-0 fw-bold px-5" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

<!-- 🟢 Modal สำหรับขยายรูปภาพเต็มจอ (Fullscreen Image Viewer) -->
<div class="modal fade" id="fullImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen p-4">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-header border-0 justify-content-end pb-0">
                <!-- ปุ่มปิด (X) สีขาว -->
                <button type="button" class="btn-close btn-close-white fs-4" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex justify-content-center align-items-center" onclick="bootstrap.Modal.getInstance(document.getElementById('fullImageModal')).hide()">
                <!-- รูปภาพที่จะขยาย -->
                <img id="fullImageDisplay" src="" class="img-fluid rounded shadow" style="max-height: 90vh; object-fit: contain; cursor: zoom-out;">
            </div>
        </div>
    </div>
</div>

<!-- popup script -->
<script>
function openViewModal(button) {
    // 1. ดึงค่าจาก Data Attributes ของปุ่ม
    const name = button.getAttribute('data-name');
    const stock = button.getAttribute('data-stock');
    const desc = button.getAttribute('data-desc'); // ดึงคำอธิบาย
    const imagesStr = button.getAttribute('data-images');
    
    // 2. นำข้อมูลไปแสดงใน Modal
    document.getElementById('view_item_name').innerText = name;
    document.getElementById('view_item_stock').innerText = parseInt(stock).toLocaleString();
    
    // ตรวจสอบว่ามีคำอธิบายไหม ถ้าไม่มีให้แสดงคำว่า "ไม่มีคำอธิบาย"
    document.getElementById('view_item_desc').innerText = (desc && desc.trim() !== '') ? desc : 'ไม่มีคำอธิบายระบุไว้';

    // 3. จัดการรูปภาพ
    const container = document.getElementById('carouselImagesContainer');
    container.innerHTML = ''; 
    
    let images = [];
    try { images = JSON.parse(imagesStr); } catch(e) {}

    if (images && images.length > 0) {
        images.forEach((img, index) => {
            const activeClass = index === 0 ? 'active' : '';
            const imgUrl = `assets/uploads/items/${img}`;
            
            // 🟢 เปลี่ยนจาก CSS Background เป็นแท็ก <img> เพื่อรักษาสัดส่วน และใส่ฟังก์ชันขยายรูปเมื่อคลิก
            container.innerHTML += `
                <div class="carousel-item h-100 ${activeClass}">
                    <div class="w-100 h-100 d-flex justify-content-center align-items-center bg-white">
                        <img src="${imgUrl}" class="h-100 w-100" style="object-fit: contain; cursor: zoom-in;" onclick="viewFullImage('${imgUrl}')" title="คลิกเพื่อขยายรูปภาพ">
                    </div>
                </div>
            `;
        });
    } else {
        container.innerHTML = `
            <div class="carousel-item h-100 active">
                <div class="w-100 h-100 d-flex flex-column justify-content-center align-items-center text-muted bg-white">
                    <i class="bi bi-image fs-1 mb-2"></i><span>ไม่มีรูปภาพประกอบ</span>
                </div>
            </div>`;
    }

    new bootstrap.Modal(document.getElementById('viewItemModal')).show();
}

// 🟢 ฟังก์ชันสำหรับเปิดรูปขยายเต็มหน้าจอ
function viewFullImage(imgUrl) {
    document.getElementById('fullImageDisplay').src = imgUrl; // เอารูปไปใส่ใน Fullscreen Modal
    new bootstrap.Modal(document.getElementById('fullImageModal')).show(); // โชว์ Modal ขยายภาพ
}
</script>