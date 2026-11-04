<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    die("Access Denied");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = trim($_POST['user_id'] ?? '');
    $action = $_POST['action'] ?? '';

    if (empty($user_id)) {
        die("เกิดข้อผิดพลาด: ไม่พบรหัสผู้ใช้งาน");
    }

    try {
        if ($action === 'approve') {
             // 1 = อนุมัติ
             $stmt = $pdo->prepare("UPDATE users SET approve_user = 1 WHERE user_id = ?");
             $stmt->execute([$user_id]);
             $_SESSION['success'] = 'อนุมัติผู้ใช้งานเรียบร้อยแล้ว';
        } elseif ($action === 'reject') {
             // 2 = ไม่อนุมัติ
             $stmt = $pdo->prepare("UPDATE users SET approve_user = 2 WHERE user_id = ?");
             $stmt->execute([$user_id]);
             $_SESSION['success'] = 'ไม่อนุมัติผู้ใช้งานเรียบร้อยแล้ว';
        }
        
        header("Location: ../../index.php?page=request_register"); // เปลี่ยนชื่อเพจให้ตรงกับของคุณ
        exit;

    } catch (Exception $e) {
        die("Database Error: " . $e->getMessage());
    }
}