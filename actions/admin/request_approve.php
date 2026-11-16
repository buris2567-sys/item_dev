<?php
// เริ่ม session เลยทันที โดยไม่ต้องเช็ค APP_RUNNING เพราะนี่คือไฟล์ Action แยกต่างหาก
session_start();
require_once '../../config/db.php';
require_once __DIR__ . '/../../includes/mailer.php';

// ตรวจสอบสิทธิ์ว่าใช่ Admin หรือไม่
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    die("<div style='color:red; padding: 20px;'><b>Access Denied:</b> คุณไม่มีสิทธิ์เข้าถึงหน้านี้ หรือ Session หมดอายุ กรุณาเข้าสู่ระบบใหม่ <a href='../../login.php'>คลิกที่นี่</a></div>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = trim($_POST['request_id'] ?? '');
    $action = $_POST['action'] ?? '';
    $admin_comment = trim($_POST['admin_comment'] ?? '');
    $admin_id = $_SESSION['user_id'];
    $approved_qty = $_POST['approved_qty'] ?? [];

    if (empty($request_id)) {
        die("เกิดข้อผิดพลาด: ไม่พบรหัสคำร้อง (Request ID)");
    }

    try {
        $pdo->beginTransaction();

        // 🔴 กรณีที่ 1: ยกเลิกคำร้อง (Cancel) 
        if ($action === 'cancel') {

            $stmtCheck = $pdo->prepare("SELECT status FROM requests WHERE request_id = ?");
            $stmtCheck->execute([$request_id]);
            $currentStatus = $stmtCheck->fetchColumn();

            // ถ้าเคย "อนุมัติ" ไปแล้ว ต้องคืนสต็อกสินค้ากลับเข้าคลัง
            if ($currentStatus === 'อนุมัติ' || $currentStatus === 'อนุมัติบางส่วน') {
                $stmtGetItems = $pdo->prepare("SELECT item_id, approved_qty FROM request_items WHERE request_id = ?");
                $stmtGetItems->execute([$request_id]);
                $itemsToReturn = $stmtGetItems->fetchAll();

                foreach ($itemsToReturn as $item) {
                    if ($item['approved_qty'] > 0) {
                        // คืนสต็อกกลับไปที่ items
                        $pdo->prepare("UPDATE items SET current_stock = current_stock + ? WHERE id = ?")
                            ->execute([$item['approved_qty'], $item['item_id']]);

                        // หาข้อมูลสต็อกล่าสุดมาบันทึกประวัติ
                        $stmtStock = $pdo->prepare("SELECT current_stock FROM items WHERE id = ?");
                        $stmtStock->execute([$item['item_id']]);
                        $new_stock = $stmtStock->fetchColumn();

                        // 🟢 [ปรับปรุง] บันทึกในตารางใหม่ ใช้ request_id แทน reference_id และตัวเลขเป็นบวก (เพราะได้ของคืน)
                        $pdo->prepare("
                            INSERT INTO inventory_transactions 
                            (item_id, transaction_type, quantity, previous_stock, current_stock, request_id, remark, created_by, created_at)
                            VALUES (?, 'ยกเลิกคำร้อง', ?, ?, ?, ?, 'คืนสต็อกจากการยกเลิกคำขอ', ?, NOW())
                        ")->execute([
                            $item['item_id'],
                            $item['approved_qty'],
                            $new_stock - $item['approved_qty'],
                            $new_stock,
                            $request_id,
                            $admin_id
                        ]);
                    }
                }
            }

            // ปรับยอดอนุมัติให้เป็น 0 และอัปเดตสถานะใบเบิก
            $pdo->prepare("UPDATE request_items SET approved_qty = 0, item_status = 'ยกเลิก' WHERE request_id = ?")->execute([$request_id]);
            $stmtUpdateReq = $pdo->prepare("UPDATE requests SET status = 'ยกเลิก', admin_comment = ?, approved_by = ?, approved_at = NOW(), is_read_user = 0 WHERE request_id = ?");
            $stmtUpdateReq->execute([$admin_comment, $admin_id, $request_id]);

            $_SESSION['success'] = 'ยกเลิกคำร้องและคืนสต็อกสำเร็จ';

            // 🟢 กรณีที่ 2: พิจารณาอนุมัติ (Approve)
        } elseif ($action === 'approve') {
            $all_full = true;
            $all_zero = true;

            foreach ($approved_qty as $req_item_id => $qty) {
                $qty = (int)$qty;

                $stmtItem = $pdo->prepare("SELECT item_id, requested_qty FROM request_items WHERE request_item_id = ?");
                $stmtItem->execute([$req_item_id]);
                $reqItem = $stmtItem->fetch();

                if ($reqItem) {
                    if ($qty > 0) $all_zero = false;
                    if ($qty < $reqItem['requested_qty']) $all_full = false;

                    $itemStatus = 'อนุมัติบางส่วน';
                    if ($qty === 0) $itemStatus = 'ไม่อนุมัติ';
                    elseif ($qty === (int)$reqItem['requested_qty']) $itemStatus = 'อนุมัติเต็ม';

                    $pdo->prepare("UPDATE request_items SET approved_qty = ?, item_status = ? WHERE request_item_id = ?")
                        ->execute([$qty, $itemStatus, $req_item_id]);

                    if ($qty > 0) {
                        // ดึงสต็อกจากตาราง items โดยใช้ PK ใหม่ (id)
                        $stmtInfo = $pdo->prepare("SELECT current_stock FROM items WHERE id = ?");
                        $stmtInfo->execute([$reqItem['item_id']]);
                        $prev_stock = $stmtInfo->fetchColumn();

                        $new_stock = $prev_stock - $qty;
                        if ($new_stock < 0) {
                            throw new Exception("สต็อกไม่เพียงพอ (ไอเทม: {$reqItem['item_id']} มีเหลือ $prev_stock แต่อยากให้ $qty)");
                        }

                        $pdo->prepare("UPDATE items SET current_stock = ? WHERE id = ?")
                            ->execute([$new_stock, $reqItem['item_id']]);

                        // 🟢 [ปรับปรุง] บันทึกในตารางใหม่ จ่ายออก quantity ต้องติดลบ (-qty)
                        $pdo->prepare("
                            INSERT INTO inventory_transactions 
                            (item_id, transaction_type, quantity, previous_stock, current_stock, request_id, remark, created_by, created_at)
                            VALUES (?, 'ถอนจากคำร้อง', ?, ?, ?, ?, 'จ่ายออกตามคำขอเบิก', ?, NOW())
                        ")->execute([
                            $reqItem['item_id'],
                            -$qty,
                            $prev_stock,
                            $new_stock,
                            $request_id,
                            $admin_id
                        ]);
                    }
                }
            }

            $final_status = 'อนุมัติบางส่วน';
            if ($all_zero) $final_status = 'ไม่อนุมัติ';
            elseif ($all_full) $final_status = 'อนุมัติ';

            $stmtUpdateReq = $pdo->prepare("UPDATE requests SET status = ?, admin_comment = ?, approved_by = ?, approved_at = NOW(), is_read_user = 0 WHERE request_id = ?");
            $stmtUpdateReq->execute([$final_status, $admin_comment, $admin_id, $request_id]);

            $_SESSION['success'] = 'บันทึกผลการพิจารณาสำเร็จ';
        }

        // 🟢 --- เริ่มต้นระบบส่งอีเมลแจ้งเตือนผู้เบิก ---
        // แก้ไขเป็น request_no, full_name และ JOIN ด้วย u.user_id
        $stmtReq = $pdo->prepare("
            SELECT r.request_no, r.status, u.email, u.full_name 
            FROM requests r 
            JOIN users u ON r.user_id = u.user_id 
            WHERE r.request_id = ?
        ");
        $stmtReq->execute([$request_id]);
        $reqData = $stmtReq->fetch();

        if ($reqData && !empty($reqData['email'])) {
            $to = $reqData['email'];

            // เปลี่ยนข้อความแจ้งเตือนตาม Action (ยกเลิก / อนุมัติ / ไม่อนุมัติ)
            if ($action === 'cancel') {
                $subject = "แจ้งผล: คำร้องถูกยกเลิก (ใบเบิก {$reqData['request_no']})";
                $statusText = "<b style='color: #dc3545;'>ถูกยกเลิก</b>";
            } else {
                $subject = "แจ้งผล: อนุมัติใบเบิก {$reqData['request_no']} - IT Helpdesk";
                $statusColor = ($reqData['status'] === 'ไม่อนุมัติ') ? '#dc3545' : '#198754';
                $statusText = "<b style='color: {$statusColor};'>{$reqData['status']}</b>";
            }

            $body = "
                <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; line-height: 1.6;'>
                    <h3 style='color: #0d6efd;'>สวัสดีคุณ {$reqData['full_name']}</h3>
                    <p>ระบบขอแจ้งผลการพิจารณาคำร้องขอเบิก/ยืมสิ่งของ หมายเลข <b>{$reqData['request_no']}</b> ของคุณ</p>
                    <p>สถานะปัจจุบัน: {$statusText}</p>
                    <p><b>หมายเหตุจาก Admin:</b> " . (!empty($admin_comment) ? htmlspecialchars($admin_comment) : "-") . "</p>
                    <hr style='border: 1px solid #eee; margin: 20px 0;'>
                    <p>คุณสามารถเข้าสู่ระบบเพื่อตรวจสอบรายการที่ได้รับอนุมัติได้เลยครับ</p>
                    <p><br>ขอบคุณครับ<br>IT Helpdesk System</p>
                </div>
            ";

            sendEmailSystem($to, $subject, $body);
        }
        // 🟢 --- สิ้นสุดระบบส่งอีเมล ---

        $pdo->commit();
        header("Location: ../../index.php?page=request_view&id=" . $request_id);
        exit;



        

    } catch (Exception $e) {
        $pdo->rollBack();
        die("<div style='color:red; padding: 20px; font-size: 20px;'><b>Database Error:</b> " . $e->getMessage() . "<br><br><a href='javascript:history.back()'>ย้อนกลับ</a></div>");
    }
}
