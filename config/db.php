<?php
/**
 * config/db.php — PDO Database Connection
 * Requires config.php to be loaded first (done by index.php entry point).
 * For action files (actions/**) that include this directly, we bootstrap
 * config.php here as well via a relative path so the constants are available.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// [Refactored] Load central config if constants are not yet defined
//              (handles direct inclusion from actions/* sub-directories)
if (!defined('DB_HOST')) {
    // Resolve config.php relative to this file's directory (config/ → root)
    require_once __DIR__ . '/../config.php';
}

// [Refactored] Replaced hardcoded '127.0.0.1' with DB_HOST constant
// [Refactored] Replaced hardcoded 'item_db'   with DB_NAME constant
// [Refactored] Replaced hardcoded 'utf8mb4'   with DB_CHARSET constant
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

// [Refactored] Replaced hardcoded PDO options array (inline) — values unchanged,
//              but credentials now come from DB_USER / DB_PASS constants
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // [Refactored] Replaced hardcoded 'root' / '' with DB_USER / DB_PASS constants
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>