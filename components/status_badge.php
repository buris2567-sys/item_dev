<?php
// รับค่าตัวแปร $status_text เข้ามา
$badgeClass = 'rounded-pill px-3 py-2 fw-bold shadow-sm '; 
$displayStatus = $status_text;

if ($status_text === 'รออนุมัติ' || $status_text === 'Pending') {
    $badgeClass .= 'bg-warning text-dark';
    $displayStatus = 'รออนุมัติ';
} elseif (strpos($status_text, 'อนุมัติบางส่วน') !== false) {
    $badgeClass .= 'bg-warning text-dark';
} elseif (strpos($status_text, 'ไม่อนุมัติ') !== false) {
    $badgeClass .= 'bg-danger text-white';
} elseif (strpos($status_text, 'อนุมัติ') !== false) {
    $badgeClass .= 'bg-success text-white';
} else {
    $badgeClass .= 'bg-secondary text-white';
}
?>
<span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($displayStatus) ?></span>