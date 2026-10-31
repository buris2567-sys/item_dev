
function fetchAdminNotifications() {
    // ระบุ Path ให้ตรงกับไฟล์ API ที่สร้างไว้ในข้อ 1
    fetch('actions/admin/get_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'success') return;

            const navBadge = document.getElementById('nav-noti-badge');
            const cardBadge = document.getElementById('card-noti-badge');
            const notiList = document.getElementById('nav-noti-list');

            if (data.count > 0) {
                // 1. อัปเดตตัวเลขแจ้งเตือนและโชว์ Badge สีแดง
                if (navBadge) {
                    navBadge.innerText = data.count > 99 ? '99+' : data.count;
                    navBadge.classList.remove('d-none');
                }
                if (cardBadge) {
                    cardBadge.innerText = data.count + ' รายการใหม่';
                    cardBadge.classList.remove('d-none'); // ลบคลาส d-none ออกจาก element เพื่อให้ element นั้นแสดงผลบนหน้าเว็บ
                }

                // 2. เคลียร์รายการเก่าในกระดิ่ง แล้ววาดรายการใหม่
                if (notiList) {
                    // ลบ class noti-item เก่าออก
                    document.querySelectorAll('.noti-item').forEach(el => el.remove());
                    
                    let htmlString = '';
                    data.items.forEach(item => {
                        htmlString += `
                            <li class="noti-item">
                                <a class="dropdown-item py-2 border-bottom" href="index.php?page=approve_requests">
                                    <div class="d-flex+ justify-content-between">
                                        <span class="fw-bold text-dark small"><i class="bi bi-file-earmark-text text-warning me-1"></i> ${item.request_no}</span>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.7rem;">ใช้วันที่: ${item.use_date}</div>
                                </a>
                            </li>
                        `;
                    });
                    
                    // แทรกรายการใหม่เข้าไปหลังคำว่า "การแจ้งเตือนใหม่"
                    const header = notiList.querySelector('.dropdown-header').parentElement;
                    header.insertAdjacentHTML('afterend', htmlString);
                }
            } else {
                // ถ้าไม่มีรายการใหม่ ให้ซ่อน Badge สีแดง
                if (navBadge) navBadge.classList.add('d-none');
                if (cardBadge) cardBadge.classList.add('d-none');
                
                // แจ้งว่าไม่มีการแจ้งเตือน
                if (notiList) {
                    document.querySelectorAll('.noti-item').forEach(el => el.remove());
                    const header = notiList.querySelector('.dropdown-header').parentElement;
                    header.insertAdjacentHTML('afterend', '<li class="noti-item"><span class="dropdown-item text-muted small text-center py-3">ไม่มีรายการใหม่</span></li>');
                }
            }
        })
        .catch(error => console.error('Error fetching notifications:', error));
}

// เมื่อโหลดหน้าเว็บเสร็จ ให้ดึงข้อมูลทันที 1 ครั้ง
document.addEventListener('DOMContentLoaded', () => {
    fetchAdminNotifications();
    
    // ตั้งเวลาให้รีเฟรชข้อมูลเองทุกๆ 30 วินาที (30000 มิลลิวินาที)
    setInterval(fetchAdminNotifications, 30000);
});
