<?php
if (!defined('APP_RUNNING')) exit('Forbidden');
$currentPage = $_GET['page'] ?? 'home';
?>
<!-- ใช้พื้นหลังสีเทาเข้มเกือบดำ -->
<div class="d-flex flex-column flex-shrink-0 text-white" style="width: 250px; background-color: #1e1e24; min-height: 100vh;">
    
    <!-- ส่วนโลโก้และชื่อระบบ -->
    <div class="d-flex align-items-center p-3 border-bottom border-secondary mb-3">
        <div class="rounded-circle bg-secondary me-3" style="width: 40px; height: 40px;"></div>
        <div class="fw-bold lh-sm text-white">
            ระบบสิ่งพิมพ์<br><span class="fs-6">และของที่ระลึก</span>
        </div>
    </div>

    <!-- รายการเมนู -->
    <ul class="nav nav-pills flex-column mb-auto px-2">
        <li class="nav-item mb-1">
            <a href="index.php?page=home" class="nav-link <?= $currentPage === 'home' ? 'text-warning fw-bold' : 'text-light' ?>">
                <i class="bi bi-house-door me-3"></i> หน้าหลัก
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="index.php?page=request_list" class="nav-link <?= $currentPage === 'request_list' ? 'text-warning fw-bold' : 'text-light' ?>">
                <i class="bi bi-list-ul me-3"></i> รายการคำขอ
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="index.php?page=manage_items" class="nav-link <?= $currentPage === 'manage_items' ? 'text-warning fw-bold' : 'text-light' ?>">
                <i class="bi bi-box me-3"></i> เพิ่มสิ่งของ
            </a>
        </li>
        <!-- เพิ่มเมนูอื่นๆ ของ Admin ต่อด้านล่างนี้ได้เลย -->
          <li class="nav-item mb-1">
            <a href="index.php?page=manage_items" class="nav-link <?= $currentPage === 'manage_items' ? 'text-warning fw-bold' : 'text-light' ?>">
                <i class="bi bi-box me-3"></i> เพิ่มสิ่งของ
            </a>
        </li>
          <!-- เพิ่มเมนูอื่นๆ ของ Admin ต่อด้านล่างนี้ได้เลย -->
          <li class="nav-item mb-1">
            <a href="index.php?page=manage_items" class="nav-link <?= $currentPage === 'manage_items' ? 'text-warning fw-bold' : 'text-light' ?>">
                <i class="bi bi-box me-3"></i> เพิ่มสิ่งของ
            </a>
        </li>
          <!-- เพิ่มเมนูอื่นๆ ของ Admin ต่อด้านล่างนี้ได้เลย -->
          <li class="nav-item mb-1">
            <a href="index.php?page=manage_items" class="nav-link <?= $currentPage === 'manage_items' ? 'text-warning fw-bold' : 'text-light' ?>">
                <i class="bi bi-box me-3"></i> เพิ่มสิ่งของ
            </a>
        </li>
          <!-- เพิ่มเมนูอื่นๆ ของ Admin ต่อด้านล่างนี้ได้เลย -->
          <li class="nav-item mb-1">
            <a href="index.php?page=manage_items" class="nav-link <?= $currentPage === 'manage_items' ? 'text-warning fw-bold' : 'text-light' ?>">
                <i class="bi bi-box me-3"></i> เพิ่มสิ่งของ
            </a>
        </li>
          <!-- เพิ่มเมนูอื่นๆ ของ Admin ต่อด้านล่างนี้ได้เลย -->
          <li class="nav-item mb-1">
            <a href="index.php?page=manage_items" class="nav-link <?= $currentPage === 'manage_items' ? 'text-warning fw-bold' : 'text-light' ?>">
                <i class="bi bi-box me-3"></i> เพิ่มสิ่งของ
            </a>
        </li>
    </ul>

    <!-- ส่วน Logout ด้านล่างสุด -->
    <div class="p-3 border-top border-secondary mt-auto">
        <a href="actions/auth_logout.php" class="nav-link text-light small text-decoration-none d-flex align-items-center">
            <i class="bi bi-box-arrow-right me-3"></i> Logout
        </a>
    </div>
</div>