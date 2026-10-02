<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'] ?? '';
    $action = $_POST['action'] ?? ''; // 'approve' หรือ 'cancel'
    $admin_comment = $_POST['admin_comment'] ?? '';
    $approved_qtys = $_POST['approved_qty'] ?? []; // Array [ request_item_id => qty ]
    $admin_id = $_SESSION['user_id'];

    if (!$request_id) {
        $_SESSION['error'] = "ไม่พบรหัสคำขอ";
        header("Location: ../../index.php?page=request_list");
        exit;
    }

    try {
        $pdo->beginTransaction();

        if ($action === 'approve') {
            $all_full = true;
            $all_zero = true;

            foreach ($approved_qtys as $req_item_id => $app_qty) {
                $app_qty = (int)$app_qty;
                
                // 🟢 ดึงข้อมูลเดิมมาเปรียบเทียบ (ใช้ชื่อคอลัมน์ item_name)[cite: 42]
                $stmt = $pdo->prepare("SELECT item_id, requested_qty, item_name, category FROM request_items WHERE request_item_id = ?");
                $stmt->execute([$req_item_id]);
                $reqItem = $stmt->fetch();
                
                if($reqItem) {
                    $req_qty = (int)$reqItem['requested_qty'];
                    $item_id = $reqItem['item_id'];
                    $item_name = $reqItem['item_name'];
                    $category = $reqItem['category'];

                    if ($app_qty < $req_qty) $all_full = false;
                    if ($app_qty > 0) $all_zero = false;

                    $item_status = ($app_qty === $req_qty) ? 'อนุมัติเต็ม' : (($app_qty === 0) ? 'ไม่อนุมัติ' : 'อนุมัติบางส่วน');

                    // อัปเดตตาราง request_items
                    $updItem = $pdo->prepare("UPDATE request_items SET approved_qty = ?, item_status = ? WHERE request_item_id = ?");
                    $updItem->execute([$app_qty, $item_status, $req_item_id]);

                    // ตัดสต็อกและเก็บ Log ถ้ายอดอนุมัติ > 0
                    if ($app_qty > 0) {
                        $st = $pdo->prepare("SELECT current_stock FROM items WHERE item_id = ?");
                        $st->execute([$item_id]);
                        $itemData = $st->fetch();
                        
                        if ($itemData) {
                            $current_stock = (int)$itemData['current_stock'];
                            $new_stock = $current_stock - $app_qty;

                            // ตัดสต็อกหลัก
                            $pdo->prepare("UPDATE items SET current_stock = ? WHERE item_id = ?")->execute([$new_stock, $item_id]);

                            // บันทึก Log ลง inventory_transactions
                            $logStmt = $pdo->prepare("INSERT INTO inventory_transactions (item_id, item_name, category_name, transaction_type, quantity, previous_stock, current_stock, reference_id, remark, created_by) VALUES (?, ?, ?, 'OUT', ?, ?, ?, ?, ?, ?)");
                            $logStmt->execute([$item_id, $item_name, $category, -$app_qty, $current_stock, $new_stock, $request_id, "จ่ายออกตามคำขอเบิก $request_id", $admin_id]);
                        }
                    }
                }
            }

            // คำนวณสถานะภาพรวม
            $overall_status = 'อนุมัติบางส่วน';
            if ($all_full) $overall_status = 'อนุมัติ';
            if ($all_zero) $overall_status = 'ไม่อนุมัติ';

            // อัปเดตตาราง requests
            $updReq = $pdo->prepare("UPDATE requests SET status = ?, admin_comment = ?, approved_at = NOW(), approved_by = ? WHERE request_id = ?");
            $updReq->execute([$overall_status, $admin_comment, $admin_id, $request_id]);

            $_SESSION['success'] = "บันทึกการอนุมัติคำขอ $request_id เรียบร้อยแล้ว";
            
        } elseif ($action === 'cancel') {
            // โลจิกสำหรับยกเลิกคำขอที่อนุมัติไปแล้ว (คืนสต็อก)
            $stmt = $pdo->prepare("SELECT * FROM request_items WHERE request_id = ?");
            $stmt->execute([$request_id]);
            $reqItems = $stmt->fetchAll();

            foreach ($reqItems as $ri) {
                $app_qty = (int)$ri['approved_qty'];
                $item_id = $ri['item_id'];
                
                if ($app_qty > 0) {
                    $st = $pdo->prepare("SELECT current_stock FROM items WHERE item_id = ?");
                    $st->execute([$item_id]);
                    $itemData = $st->fetch();

                    if ($itemData) {
                        $current_stock = (int)$itemData['current_stock'];
                        $new_stock = $current_stock + $app_qty; // คืนสต็อก

                        $pdo->prepare("UPDATE items SET current_stock = ? WHERE item_id = ?")->execute([$new_stock, $item_id]);

                        $logStmt = $pdo->prepare("INSERT INTO inventory_transactions (item_id, item_name, category_name, transaction_type, quantity, previous_stock, current_stock, reference_id, remark, created_by) VALUES (?, ?, ?, 'IN', ?, ?, ?, ?, ?, ?)");
                        $logStmt->execute([$item_id, $ri['item_name'], $ri['category'], $app_qty, $current_stock, $new_stock, $request_id, "คืนสต็อกจากการยกเลิกคำขอ $request_id", $admin_id]);
                    }
                }
            }

            // เปลี่ยนสถานะเป็นยกเลิก
            $pdo->prepare("UPDATE requests SET status = 'ยกเลิก', admin_comment = ? WHERE request_id = ?")->execute([$admin_comment, $request_id]);
            $_SESSION['success'] = "ยกเลิกคำขอ $request_id และคืนสต็อกเรียบร้อยแล้ว";
        }

        $pdo->commit();
        header("Location: ../../index.php?page=request_list");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        header("Location: ../../index.php?page=request_view&id=" . $request_id);
        exit;
    }
}
?>