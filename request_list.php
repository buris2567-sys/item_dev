<?php
// require_once 'config/db.php';
$pageTitle = "หน้าหลัก - ระบบเบิกจ่าย";
$currentPage = 'home';
// include 'includes/header.php'; // สมมติว่าดึง CSS และ Bootstrap มาจากไฟล์นี้
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=1.1"> <!-- เชื่อมไฟล์ style.css ที่เราเพิ่งแก้ -->
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0 flex-nowrap">
        
        <!-- Sidebar (สีดำทึบ) -->
        <div class="col-auto col-md-3 col-xl-2 px-0 sidebar d-flex flex-column">
            <div class="p-3 d-flex align-items-center text-white border-bottom border-secondary">
                <div class="bg-secondary rounded-circle me-2" style="width: 30px; height: 30px;"></div>
                <span class="fw-bold fs-5">OAP</span>
            </div>
            <ul class="nav flex-column mt-2">
                <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                <li class="nav-item"><a href="#" class="nav-link active"><i class="bi bi-list-ul me-2"></i> รายการคำขอ</a></li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-check-square me-2"></i> พิจารณาคำขอ</a></li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-box-seam me-2"></i> จัดการสิ่งของ</a></li>
            </ul>
        </div>

        <!-- Main Content Area -->
        <div class="col d-flex flex-column" style="min-height: 100vh;">
            
            <!-- 1. Top Navbar (สีเหลือง) -->
            <div class="topbar px-3 d-flex justify-content-between align-items-center w-100">
                <button class="btn btn-sm border-0 fs-4"><i class="bi bi-list"></i></button>
                <div class="d-flex align-items-center">
                    <span class="fw-bold me-3">บุรีศร์ S.</span>
                    <div class="bg-white rounded-circle text-center pt-1" style="width: 35px; height: 35px;">
                        <i class="bi bi-person-fill text-secondary fs-5"></i>
                    </div>
                </div>
            </div>

            <!-- 2. Sub-header (แถบสีเทาอ่อน บอกชื่อหน้า) -->
            <div class="sub-header w-100">
                รายการคำขอ
            </div>

            <!-- 3. Content Body (พื้นที่สีเทาอ่อนสุด) -->
            <div class="p-4 flex-grow-1">
                
                <!-- การ์ดข้อมูล (กรอบสีขาว มีหัวสีดำ) -->
                <div class="data-card">
                    <!-- หัวการ์ดสีดำทึบ -->
                    <div class="data-card-header">
                        คำขอทั้งหมด
                    </div>
                    
                    <div class="p-4">
                        <!-- แถบค้นหา และปุ่มเพิ่ม (เลียนแบบภาพ) -->
                        <div class="d-flex justify-content-between align-items-end mb-4">
                            <div class="w-25">
                                <input type="text" class="form-control input-material" placeholder="ค้นหา...">
                            </div>
                            <button class="btn btn-yellow"><i class="bi bi-plus-lg me-1"></i> เพิ่ม</button>
                        </div>

                        <!-- ตารางข้อมูล -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>หมายเลขคำขอ</th>
                                        <th>วันที่ขอ</th>
                                        <th>วันที่อนุมัติ</th>
                                        <th>สถานะ</th>
                                        <th>หมายเหตุ</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>69000029</td>
                                        <td>08/09/2569</td>
                                        <td>12/09/2569</td>
                                        <td class="text-warning fw-bold">ยกเลิก</td>
                                        <td>ทดลอง</td>
                                        <td><button class="btn btn-sm btn-info text-white"><i class="bi bi-search"></i></button></td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>69000028</td>
                                        <td>03/09/2569</td>
                                        <td>03/09/2569</td>
                                        <td class="text-danger fw-bold">ไม่อนุมัติ</td>
                                        <td>ทดสอบ</td>
                                        <td><button class="btn btn-sm btn-info text-white"><i class="bi bi-search"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination พื้นฐาน -->
                        <div class="d-flex justify-content-end align-items-center mt-3 small text-muted">
                            <span class="me-3">Items per page: 
                                <select class="border-0 bg-transparent ms-1 outline-none"><option>5</option></select>
                            </span>
                            <span>1 - 2 of 2</span>
                            <div class="ms-3">
                                <i class="bi bi-chevron-left me-2 text-secondary"></i>
                                <i class="bi bi-chevron-right text-secondary"></i>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
</body>
</html>