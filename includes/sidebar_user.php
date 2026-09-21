<?php
// อ่านชื่อไฟล์ปัจจุบันอัตโนมัติ (เช่น index.php, request_list.php)
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="col-auto col-md-3 col-xl-2 px-0 sidebar d-flex flex-column justify-content-between">
    <div>
        <div class="p-4 text-center border-bottom border-secondary mb-3">
            <div class="p-3 d-flex align-items-center text-white border-bottom border-secondary">
                <div class="bg-secondary rounded-circle me-2" style="width: 30px; height: 30px;"></div>
                
                <h5 class="fw-bold mb-0 text-white">ระบบสิ่งพิมพ์<br>และของที่ระลึก</h5>
          
            </div>
        </div>

        <ul class="nav flex-column">
            <!-- เช็คตรงๆ กับชื่อไฟล์ index.php -->
            <li class="nav-item">
                <a href="index.php" class="nav-link py-3 <?= ($current_page === 'index.php') ? 'active' : '' ?>">
                    <i class="bi bi-house-door me-3"></i> หน้าหลัก
                </a>
            </li>
            <!-- เช็คตรงๆ กับชื่อไฟล์ request_list.php -->
            <li class="nav-item">
                <a href="request_list.php" class="nav-link py-3 <?= ($current_page === 'request_list.php') ? 'active' : '' ?>">
                    <i class="bi bi-list-ul me-3"></i> รายการคำขอ
                </a>
            </li>
            <!-- เช็คตรงๆ กับชื่อไฟล์ add_item.php -->
            <li class="nav-item">
                <a href="add_item.php" class="nav-link py-3 <?= ($current_page === 'add_item.php') ? 'active' : '' ?>">
                    <i class="bi bi-box-seam me-3"></i> เพิ่มสิ่งของ
                </a>
            </li>
        </ul>
    </div>

    <!-- ปุ่ม Logout ชี้ไปโฟลเดอร์ actions/ -->
    <div class="p-3 border-top border-secondary">
        <a href="actions/auth_logout.php" class="nav-link text-white-50">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
        </a>
    </div>
</div>