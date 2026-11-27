<?php
session_start();
require_once '../../config/db.php';
require_once __DIR__ . '/../../includes/mailer.php';

// ตรวจสอบสิทธิ์ว่าใช่ Admin หรือไม่
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    die("<div style='color:red; padding: 20px;'><b>Access Denied:</b> คุณไม่มีสิทธิ์เข้าถึงหน้านี้ หรือ Session หมดอายุ กรุณาเข้าสู่ระบบใหม่ <a href='../../login.php'>คลิกที่นี่</a></div>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id    = trim($_POST['request_id'] ?? '');
    $action        = $_POST['action'] ?? '';
    $admin_comment = trim($_POST['admin_comment'] ?? '');
    $admin_id      = $_SESSION['user_id'];
    $approved_qty  = $_POST['approved_qty'] ?? [];

    if (empty($request_id) || !in_array($action, ['approve', 'cancel'], true)) {
        die("เกิดข้อผิดพลาด: ข้อมูลคำร้องไม่ถูกต้อง");
    }

    try {
        $pdo->beginTransaction();

        // ตรวจสอบสถานะปัจจุบันของคำร้องก่อนดำเนินการ
        $stmtCheck = $pdo->prepare("SELECT status FROM requests WHERE request_id = ? FOR UPDATE");
        $stmtCheck->execute([$request_id]);
        $currentStatus = $stmtCheck->fetchColumn();

        if ($currentStatus === false) {
            throw new Exception("ไม่พบข้อมูลคำร้องในระบบ");
        }

        // 🔴 กรณีที่ 1: ยกเลิกคำร้อง (Cancel)
        if ($action === 'cancel') {
            if ($currentStatus === 'ยกเลิก') {
                throw new Exception("คำร้องนี้ถูกยกเลิกไปแล้ว");
            }

            // ถ้าเคย "อนุมัติ" ไปแล้ว ต้องคืนสต็อกสินค้ากลับเข้าคลัง
            if ($currentStatus === 'อนุมัติ' || $currentStatus === 'อนุมัติบางส่วน') {
                $stmtGetItems = $pdo->prepare("SELECT item_id, approved_qty FROM request_items WHERE request_id = ?");
                $stmtGetItems->execute([$request_id]);
                $itemsToReturn = $stmtGetItems->fetchAll();

                foreach ($itemsToReturn as $item) {
                    if ($item['approved_qty'] > 0) {
                        $pdo->prepare("UPDATE items SET current_stock = current_stock + ? WHERE id = ?")
                            ->execute([$item['approved_qty'], $item['item_id']]);

                        $stmtStock = $pdo->prepare("SELECT current_stock FROM items WHERE id = ?");
                        $stmtStock->execute([$item['item_id']]);
                        $new_stock = $stmtStock->fetchColumn();

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

        // 🟢 กรณีที่ 2: พิจารณาอนุมัติ (Approve)
        } elseif ($action === 'approve') {
            // ป้องกันการกดอนุมัติซ้ำหากรายการนี้พิจารณาไปแล้ว
            if ($currentStatus !== 'รออนุมัติ' && $currentStatus !== 'รอดำเนินการ') {
                throw new Exception("คำร้องนี้ได้รับการพิจารณาไปแล้ว (สถานะปัจจุบัน: {$currentStatus})");
            }

            $all_full = true;
            $all_zero = true;

            foreach ($approved_qty as $req_item_id => $qty) {
                $qty = max(0, (int)$qty);

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
                        $stmtInfo = $pdo->prepare("SELECT current_stock FROM items WHERE id = ?");
                        $stmtInfo->execute([$reqItem['item_id']]);
                        $prev_stock = $stmtInfo->fetchColumn();

                        $new_stock = $prev_stock - $qty;
                        if ($new_stock < 0) {
                            throw new Exception("สต็อกไม่เพียงพอ (ไอเทม: {$reqItem['item_id']} มีเหลือ $prev_stock แต่อยากให้ $qty)");
                        }

                        $pdo->prepare("UPDATE items SET current_stock = ? WHERE id = ?")
                            ->execute([$new_stock, $reqItem['item_id']]);

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
        }

        // 🟢 ยืนยันการบันทึกข้อมูลลงฐานข้อมูลก่อนส่งอีเมล
        $pdo->commit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("<div style='color:red; padding: 20px; font-size: 20px;'><b>Database Error:</b> " . htmlspecialchars($e->getMessage()) . "<br><br><a href='javascript:history.back()'>ย้อนกลับ</a></div>");
    }

    // 🟢 --- เริ่มต้นระบบส่งอีเมลแจ้งเตือนผู้เบิก (ทำหลัง Commit เพื่อไม่ให้กระทบสต็อก) ---
    $stmtReq = $pdo->prepare("
        SELECT r.request_no, r.status, u.email, u.full_name 
        FROM requests r 
        JOIN users u ON r.user_id = u.user_id 
        WHERE r.request_id = ?
    ");
    $stmtReq->execute([$request_id]);
    $reqData = $stmtReq->fetch();

    $is_sent = false;
    $to = $reqData['email'] ?? '';

    if ($reqData && !empty($to)) {
        if ($action === 'cancel') {
            $subject = "แจ้งผล: คำร้องถูกยกเลิก (ใบเบิก {$reqData['request_no']})";
            $statusText = "<b style='color: #dc3545;'>ถูกยกเลิก</b>";
        } else {
            $subject = "แจ้งผล: {$reqData['status']}ใบเบิก {$reqData['request_no']} - ระบบเบิกสิ่งพิมพ์และของที่ระลึก";
            $statusColor = ($reqData['status'] === 'ไม่อนุมัติ') ? '#dc3545' : '#198754';
            $statusText = "<b style='color: {$statusColor};'>{$reqData['status']}</b>";
        }

        $safeName    = htmlspecialchars($reqData['full_name']);
        $safeReqNo   = htmlspecialchars($reqData['request_no']);
        $safeComment = !empty($admin_comment) ? nl2br(htmlspecialchars($admin_comment)) : "-";

        $body = "
            <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; line-height: 1.6;'>
                <h3 style='color: #b45309;'>สวัสดีคุณ {$safeName}</h3>
                <p>ระบบขอแจ้งผลการพิจารณาคำร้องขอเบิกสิ่งพิมพ์และของที่ระลึก หมายเลข <b>{$safeReqNo}</b> ของคุณ</p>
                <p>สถานะปัจจุบัน: {$statusText}</p>
                <p><b>หมายเหตุจาก Admin:</b> {$safeComment}</p>
                <hr style='border: 1px solid #eee; margin: 20px 0;'>
                <p>คุณสามารถเข้าสู่ระบบเพื่อตรวจสอบรายการที่ได้รับอนุมัติได้เลยครับ</p>
                <p><br>ขอบคุณครับ<br>ระบบเบิกสิ่งพิมพ์และของที่ระลึก</p>
            </div>
        ";

        try {
            $is_sent = sendEmailSystem($to, $subject, $body);
        } catch (Exception $mailEx) {
            $is_sent = false;
        }
    }

    // 🟢 สร้างข้อความ Alert สำหรับนำไปแสดงในหน้าถัดไป
    $reqNoLabel = htmlspecialchars($reqData['request_no'] ?? $request_id);
    $emailLabel = htmlspecialchars($to);

    if ($is_sent) {
        $_SESSION['alert'] = [
            'type'    => 'success',
            'icon'    => 'bi-envelope-check-fill',
            'title'   => 'ดำเนินการและส่งอีเมลสำเร็จ',
            'message' => "อัปเดตสถานะใบเบิก <b>{$reqNoLabel}</b> และส่งอีเมลแจ้งเตือนไปที่ <b>{$emailLabel}</b> เรียบร้อยแล้ว"
        ];
    } else {
        $_SESSION['alert'] = [
            'type'    => 'warning',
            'icon'    => 'bi-exclamation-triangle-fill',
            'title'   => 'บันทึกสถานะเรียบร้อยแล้ว (แต่ส่งอีเมลไม่สำเร็จ)',
            'message' => "อัปเดตสถานะใบเบิก <b>{$reqNoLabel}</b> แล้ว แต่ไม่สามารถส่งอีเมลไปยัง <b>" . ($emailLabel ?: 'ผู้เบิก') . "</b> ได้"
        ];
    }

    // เก็บไว้เผื่อหน้าเก่าของคุณยังเช็ค $_SESSION['success'] อยู่
    $_SESSION['success'] = $_SESSION['alert']['title'];

    header("Location: ../../index.php?page=request_view&id=" . $request_id);
    exit;
}