// 🟢 ระบบแจ้งเตือนฝั่งผู้ใช้ (User Notification)
let previousUserNotiCount = 0; // จำตัวเลขรอบก่อนหน้าเพื่อใช้กับ SweetAlert2

function fetchUserNotifications() {
    fetch('actions/user/get_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'success') return;

            // ดึง ID ของจุดต่างๆ บนหน้าจอ
            const navBadge = document.getElementById('nav-noti-badge');
            const cardBadge = document.getElementById('card-noti-badge');
            const notiList = document.getElementById('nav-noti-list');
            const sidebarBadge = document.getElementById('sidebar-noti-badge');

            if (data.count > 0) {
                // 1. อัปเดตตัวเลขแจ้งเตือนบน Navbar (กระดิ่ง)
                if (navBadge) {
                    navBadge.innerText = data.count > 99 ? '99+' : data.count;
                    navBadge.classList.remove('d-none');
                }
                
                // 2. อัปเดตตัวเลขแจ้งเตือนบนการ์ดเมนู
                if (cardBadge) {
                    cardBadge.innerText = data.count + ' รายการ';
                    cardBadge.classList.remove('d-none');
                }

                // 3. อัปเดตตัวเลขแจ้งเตือนบน Sidebar
                if (sidebarBadge) {
                    sidebarBadge.innerText = data.count > 99 ? '99+' : data.count;
                    sidebarBadge.classList.remove('d-none');
                }

                // 4. แจ้งเตือนเด้งมุมจอ (SweetAlert2) เมื่อมีการอัปเดตสถานะใหม่
                if (data.count > previousUserNotiCount && previousUserNotiCount !== 0 && typeof Swal !== 'undefined') {
                    let newItems = data.count - previousUserNotiCount; 
                    const Toast = Swal.mixin({
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: "info",
                        title: `มีรายการคำขออัปเดตสถานะ ${newItems} รายการ!`
                    });
                }

                // 5. วาดรายการคำขอลงใน Dropdown ของกระดิ่ง
                if (notiList) {
                    document.querySelectorAll('.user-noti-item').forEach(el => el.remove());
                    let htmlString = '';
                    data.items.forEach(item => {
                        let badgeClass = 'bg-secondary text-white';
                        if (item.status === 'อนุมัติ') {
                            badgeClass = 'bg-success text-white';
                        } else if (item.status === 'ไม่อนุมัติ') {
                            badgeClass = 'bg-danger text-white';
                        } else if (item.status === 'อนุมัติบางส่วน') {
                            badgeClass = 'bg-primary text-white';
                        } else if (item.status === 'ยกเลิก') {
                            badgeClass = 'bg-dark text-white';
                        }

                        htmlString += `
                            <li class="user-noti-item">
                                <a class="dropdown-item py-2 border-bottom" href="index.php?page=request_view&id=${item.request_id}">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold text-dark small"><i class="bi bi-file-earmark-text text-warning me-1"></i> ${item.request_no}</span>
                                        <span class="badge ${badgeClass} rounded-pill" style="font-size: 0.65rem;">${item.status}</span>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.7rem;">
                                        <i class="bi bi-clock me-1"></i>อัปเดต: ${item.date_display}
                                    </div>
                                </a>
                            </li>
                        `;
                    });
                    const header = notiList.querySelector('.dropdown-header').parentElement;
                    header.insertAdjacentHTML('afterend', htmlString);
                }
            } else {
                // ถ้าไม่มีรายการ ให้ซ่อนป้ายแดงทั้งหมด
                if (navBadge) navBadge.classList.add('d-none');
                if (cardBadge) cardBadge.classList.add('d-none');
                if (sidebarBadge) sidebarBadge.classList.add('d-none');
                
                if (notiList) {
                    document.querySelectorAll('.user-noti-item').forEach(el => el.remove());
                    const header = notiList.querySelector('.dropdown-header').parentElement;
                    header.insertAdjacentHTML('afterend', '<li class="user-noti-item"><span class="dropdown-item text-muted small text-center py-3">ไม่มีรายการแจ้งเตือน</span></li>');
                }
            }
            
            previousUserNotiCount = data.count; // อัปเดตค่าไว้เช็ครอบถัดไป
        })
        .catch(error => console.error('Error fetching user notifications:', error));
}

// โหลดครั้งแรก และตั้งเวลาเช็คทุกๆ 30 วินาที
document.addEventListener('DOMContentLoaded', () => {
    fetchUserNotifications();
    setInterval(fetchUserNotifications, 30000);
});

