<?php
/**
 * ============================================================
 *  config.php — Central Application Configuration
 *  ระบบเบิกสิ่งพิมพ์และของที่ระลึก
 * ============================================================
 *  This file centralizes ALL environment-specific values:
 *   - Database credentials
 *   - Base URL & asset paths
 *   - Upload directories
 *   - Environment flag (development / production)
 *   - Application-wide settings
 *
 *  Include this file ONCE at the application entry point.
 *  All other files that need these values must require this.
 * ============================================================
 */

// ------------------------------------------------------------
// 1. ENVIRONMENT FLAG
//    Switch to 'production' when deploying to a live server.
//    Controls error reporting behavior.
// ------------------------------------------------------------
define('APP_ENV', 'development'); // 'development' | 'production'

if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// ------------------------------------------------------------
// 2. DATABASE CREDENTIALS
// ------------------------------------------------------------
// [Refactored] Replaced hardcoded '127.0.0.1' with DB_HOST constant
define('DB_HOST',    '127.0.0.1');

// [Refactored] Replaced hardcoded 'item_db' with DB_NAME constant
define('DB_NAME',    'item_db');

// [Refactored] Replaced hardcoded 'root' with DB_USER constant
define('DB_USER',    'root');

// [Refactored] Replaced hardcoded '' (empty password) with DB_PASS constant
define('DB_PASS',    '');

// [Refactored] Replaced hardcoded 'utf8mb4' with DB_CHARSET constant
define('DB_CHARSET', 'utf8mb4');

// ------------------------------------------------------------
// 3. BASE URL
//    Change this to your production domain when going live.
//    No trailing slash.
// ------------------------------------------------------------
// [Refactored] Replaced hardcoded 'http://localhost' with BASE_URL constant
define('BASE_URL', 'http://localhost');

// [Refactored] Replaced hardcoded project sub-folder name with APP_FOLDER constant
define('APP_FOLDER', '/item_dev');

// Full application base URL: BASE_URL + APP_FOLDER
// Example: http://localhost/item_dev
define('APP_BASE_URL', BASE_URL . APP_FOLDER);

// ------------------------------------------------------------
// 4. FILE SYSTEM PATHS
//    __DIR__ resolves to the directory of THIS file (project root).
// ------------------------------------------------------------
// [Refactored] Replaced hardcoded absolute path with APP_ROOT constant
define('APP_ROOT', __DIR__);

// [Refactored] Replaced hardcoded '../../assets/uploads/items/' with UPLOAD_PATH constant
define('UPLOAD_PATH', APP_ROOT . '/assets/uploads/items/');

// [Refactored] Replaced hardcoded 'assets/uploads/items/' (web-relative) with UPLOAD_URL constant
define('UPLOAD_URL', 'assets/uploads/items/');

// [Refactored] Replaced hardcoded 'assets/images/placeholder.jpg' with PLACEHOLDER_URL constant
define('PLACEHOLDER_URL', 'assets/images/placeholder.jpg');

// [Refactored] Replaced hardcoded file permission 0777 with UPLOAD_DIR_PERMISSION constant
define('UPLOAD_DIR_PERMISSION', 0755);

// ------------------------------------------------------------
// 5. APPLICATION SETTINGS
// ------------------------------------------------------------
// [Refactored] Replaced hardcoded max upload size 5242880 (5MB) with MAX_UPLOAD_SIZE constant
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB in bytes

// [Refactored] Replaced hardcoded allowed extensions array with ALLOWED_IMAGE_TYPES constant
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png']);

// [Refactored] Replaced hardcoded max images per item (3) with MAX_ITEM_IMAGES constant
define('MAX_ITEM_IMAGES', 3);

// [Refactored] Replaced hardcoded app name string with APP_NAME constant
define('APP_NAME', 'ระบบเบิกสิ่งพิมพ์และของที่ระลึก');

// [Refactored] Replaced hardcoded default page title with APP_DEFAULT_TITLE constant
define('APP_DEFAULT_TITLE', 'ระบบเบิกจ่ายพัสดุ');

