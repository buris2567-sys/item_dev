<?php
if (!defined('APP_RUNNING')) exit('Forbidden');
$currentPage = $_GET['page'] ?? 'home';
?>

<style>
    /* 🟢 ตกแต่ง Sidebar ให้ดูทันสมัยขึ้น (คลาสเดียวกันกับ User) */
    .sidebar-wrapper {
        background-color: #1e1e24; 
        border-right: 1px solid rgba(255,255,255,0.05);
    }
    .sidebar-link {
        transition: all 0.2s ease-in-out;
        border-radius: 0.75rem; 
        padding: 0.8rem 1.2rem;
        color: #a1a1aa; 
        font-weight: 500;
        display: flex;
        align-items: center;
        text-decoration: none;
    }
    .sidebar-link i { font-size: 1.2rem; }
    .sidebar-link:hover {
        color: #ffffff;
        background-color: rgba(255,255,255,0.05);
        transform: translateX(4px); 
    }
    .sidebar-link.active {
        color: #ffc107 !important; 
        background-color: rgba(255, 193, 7, 0.1); 
        font-weight: 700;
    }
    .sidebar-divider {
        border-top: 1px solid rgba(255,255,255,0.1);
        margin: 1rem 0;
    }
    .sidebar-logout { color: #ef4444; }
    .sidebar-logout:hover {
        background-color: rgba(239, 68, 68, 0.1);
        color: #f87171;
    }
</style>

<div class="col-auto px-0 position-sticky top-0 vh-100 d-flex flex-column sidebar-wrapper shadow" style="width: 260px; z-index: 1000;">
    
    <!-- โลโก้และชื่อระบบ -->
    <div class="d-flex align-items-center p-4 mb-2">
        <div class="rounded-circle bg-secondary shadow-sm me-3 flex-shrink-0" style="width: 45px; height: 45px;"></div>
        <div class="fw-bold lh-sm text-white fs-6">
            ระบบสิ่งพิมพ์<br>
            <span class="fw-normal text-white-50" style="font-size: 0.8rem;">และของที่ระลึก</span>
        </div>
    </div>

    <!-- รายการเมนู -->
    <div class="px-3 overflow-y-auto mb-auto">
        <a href="index.php?page=home" class="sidebar-link mb-2 <?= $currentPage === 'home' ? 'active' : '' ?>">
            <i class="bi bi-house-door me-3"></i> หน้าหลัก
        </a>
        
        <a href="index.php?page=request_list" class="sidebar-link mb-2 <?= ($currentPage === 'request_list' || $currentPage === 'request_view') ? 'active' : '' ?>">
            <i class="bi bi-list-ul me-3"></i> รายการคำขอ
        </a>
        
        <a href="index.php?page=manage_items" class="sidebar-link mb-2 <?= $currentPage === 'manage_items' ? 'active' : '' ?>">
            <i class="bi bi-box me-3"></i> เพิ่มสิ่งของ
        </a>
    </div>

    <!-- ปุ่ม Logout -->
    <div class="p-3">
        <div class="sidebar-divider mt-0 mb-3"></div>
        <a href="actions/auth_logout.php" class="sidebar-link sidebar-logout">
            <i class="bi bi-box-arrow-right me-3"></i> Logout
        </a>
    </div>
</div>