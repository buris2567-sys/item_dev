<div class="modal fade" id="viewItemModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-dark rounded-0" style="border-width: 2px !important;">
            <div class="modal-header bg-warning border-dark" style="border-bottom-width: 2px !important;">
                <h5 class="modal-title fw-bold text-dark">รายละเอียดสิ่งของ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                
                <!-- ส่วนแสดงรูปภาพ (Carousel) -->
                <div id="itemImageCarousel" class="carousel slide mb-4 border border-dark bg-light" data-bs-ride="carousel" style="height: 250px;">
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
                <div class="d-flex justify-content-between align-items-center p-3 bg-light border border-secondary rounded">
                    <span class="fw-bold text-muted">จำนวนปัจจุบันในคลัง:</span>
                    <span class="fs-4 fw-bold text-dark" id="view_item_stock">0</span>
                </div>

            </div>
            <div class="modal-footer border-0 bg-light d-flex justify-content-center">
                <button type="button" class="btn btn-dark rounded-0 fw-bold px-5" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

<script>
function openViewModal(button) {
    // ดึงค่าจาก Data Attributes ของปุ่ม
    const name = button.getAttribute('data-name');
    const stock = button.getAttribute('data-stock');
    const imagesStr = button.getAttribute('data-images');
    
    document.getElementById('view_item_name').innerText = name;
    document.getElementById('view_item_stock').innerText = parseInt(stock).toLocaleString();

    // จัดการรูปภาพ
    const container = document.getElementById('carouselImagesContainer');
    container.innerHTML = ''; // ล้างของเก่า
    
    let images = [];
    try { images = JSON.parse(imagesStr); } catch(e) {}

    if (images && images.length > 0) {
        images.forEach((img, index) => {
            const activeClass = index === 0 ? 'active' : '';
            container.innerHTML += `
                <div class="carousel-item h-100 ${activeClass}">
                    <div class="w-100 h-100" style="background-image: url('assets/uploads/items/${img}'); background-size: contain; background-position: center; background-repeat: no-repeat;"></div>
                </div>
            `;
        });
    } else {
        container.innerHTML = `
            <div class="carousel-item h-100 active">
                <div class="w-100 h-100 d-flex flex-column justify-content-center align-items-center text-muted">
                    <i class="bi bi-image fs-1 mb-2"></i><span>ไม่มีรูปภาพประกอบ</span>
                </div>
            </div>`;
    }

    new bootstrap.Modal(document.getElementById('viewItemModal')).show();
}
</script>