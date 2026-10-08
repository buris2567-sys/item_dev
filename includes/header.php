<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- [Refactored] Replaced hardcoded fallback title 'ระบบเบิกจ่ายพัสดุ' with APP_DEFAULT_TITLE constant -->
    <title><?= isset($pageTitle) ? $pageTitle : APP_DEFAULT_TITLE ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">


    <!-- 🟢 ปรับปรุง: นำเข้าฟอนต์ Prompt จาก Google Fonts และเขียน CSS บังคับใช้ทั้งหน้า -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* บังคับใช้ฟอนต์ Prompt กับทุกส่วนประกอบ */
        body, .container-fluid, .card, .btn, table, input, select {
            font-family: 'Prompt', sans-serif !important;
        }
        /* บังคับสีข้อความในช่อง Placeholder (ช่องค้นหา) ให้เข้มขึ้น */
        ::placeholder {
            color: #6c757d !important;
            opacity: 1 !important;
        }
        /* สีแถบตารางสลับ (Zebra) */
        .custom-striped > tbody > tr:nth-of-type(odd) > td {
            background-color: #f4f6f8 !important; 
        }
        .custom-striped > tbody > tr:hover > td {
            background-color: #e9ecef !important;
        }
    </style>

    <!-- [Refactored] PHP-generated JS config block: exposes PHP constants (UPLOAD_URL, PLACEHOLDER_URL,
         APP_BASE_URL) to all JavaScript files so they don't need hardcoded paths. -->
    <script>
        window.AppConfig = {
            // [Refactored] Replaced hardcoded 'assets/uploads/items/' with UPLOAD_URL constant (via PHP)
            uploadUrl:      '<?= UPLOAD_URL ?>',
            // [Refactored] Replaced hardcoded 'assets/images/placeholder.jpg' with PLACEHOLDER_URL constant (via PHP)
            placeholderUrl: '<?= PLACEHOLDER_URL ?>',
            // [Refactored] Replaced hardcoded 'http://localhost/item_dev' with APP_BASE_URL constant (via PHP)
            baseUrl:        '<?= APP_BASE_URL ?>',
            // [Refactored] Replaced hardcoded 5242880 (5MB) with MAX_UPLOAD_SIZE constant (via PHP)
            maxUploadSize:  <?= MAX_UPLOAD_SIZE ?>,
            // [Refactored] Replaced hardcoded max images '3' with MAX_ITEM_IMAGES constant (via PHP)
            maxItemImages:  <?= MAX_ITEM_IMAGES ?>
        };
    </script>

    <!-- (ส่วนจัดการสิ่งของด้านบน ยังเหมือนเดิม) -->
</head>
<body>

