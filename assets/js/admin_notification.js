let previousNotiCount = 0; // จำตัวเลขรอบก่อนหน้าเพื่อใช้กับ SweetAlert2

function fetchAdminNotifications() {
    fetch('actions/admin/get_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'success') return;

            // ดึง ID ของจุดต่างๆ บนหน้าจอ
            const navBadge = document.getElementById('nav-noti-badge');
            const cardBadge = document.getElementById('card-noti-badge');
            const notiList = document.getElementById('nav-noti-list');
            
            // 🟢 เพิ่มตัวนี้เข้าไป เพื่ออ้างอิงถึงป้ายบน Sidebar
            const sidebarBadge = document.getElementById('sidebar-noti-badge');

            if (data.count > 0) {
                // 1. อัปเดตตัวเลขแจ้งเตือนบน Navbar
                if (navBadge) {
                    navBadge.innerText = data.count > 99 ? '99+' : data.count;
                    navBadge.classList.remove('d-none');
                }
                
                // 2. อัปเดตตัวเลขแจ้งเตือนบนการ์ดเมนู
                if (cardBadge) {
                    cardBadge.innerText = data.count + ' รายการใหม่';
                    cardBadge.classList.remove('d-none');
                }

                // 3. 🟢 อัปเดตตัวเลขแจ้งเตือนบน Sidebar
                if (sidebarBadge) {
                    sidebarBadge.innerText = data.count > 99 ? '99+' : data.count;
                    sidebarBadge.classList.remove('d-none');
                }

                // 4. แจ้งเตือนเด้งมุมจอ (SweetAlert2) เมื่อมีคำขอเข้ามาใหม่
                if (data.count > previousNotiCount && previousNotiCount !== 0) {
                    let newItems = data.count - previousNotiCount; 
                    const Toast = Swal.mixin({
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: "info",
                        title: `มีคำขอเบิกพัสดุใหม่ ${newItems} รายการ!`
                    });
                }

                // 5. วาดรายการคำขอใหม่ลงใน Dropdown ของกระดิ่ง
                if (notiList) {
                    document.querySelectorAll('.noti-item').forEach(el => el.remove());
                    let htmlString = '';
                    data.items.forEach(item => {
                        htmlString += `
                            <li class="noti-item">
                                <a class="dropdown-item py-2 border-bottom" href="index.php?page=approve_requests">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold text-dark small"><i class="bi bi-file-earmark-text text-warning me-1"></i> ${item.request_no}</span>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.7rem;">ใช้วันที่: ${item.use_date}</div>
                                </a>
                            </li>
                        `;
                    });
                    const header = notiList.querySelector('.dropdown-header').parentElement;
                    header.insertAdjacentHTML('afterend', htmlString);
                }
            } else {
                // ถ้าไม่มีรายการใหม่ ให้ซ่อนป้ายแดงทั้งหมด
                if (navBadge) navBadge.classList.add('d-none');
                if (cardBadge) cardBadge.classList.add('d-none');
                if (sidebarBadge) sidebarBadge.classList.add('d-none'); // 🟢 ซ่อนป้าย Sidebar
                
                if (notiList) {
                    document.querySelectorAll('.noti-item').forEach(el => el.remove());
                    const header = notiList.querySelector('.dropdown-header').parentElement;
                    header.insertAdjacentHTML('afterend', '<li class="noti-item"><span class="dropdown-item text-muted small text-center py-3">ไม่มีรายการใหม่</span></li>');
                }
            }
            
            previousNotiCount = data.count; // อัปเดตค่าไว้เช็ครอบถัดไป
        })
        .catch(error => console.error('Error fetching notifications:', error));
}

// โหลดครั้งแรก และตั้งเวลาเช็คทุกๆ 30 วินาที
document.addEventListener('DOMContentLoaded', () => {
    fetchAdminNotifications();
    setInterval(fetchAdminNotifications, 30000);
});