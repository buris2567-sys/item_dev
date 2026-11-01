<?php
/**
 * components/ui/card_footer_pagination.php
 * ส่วนเท้าการ์ด + Pagination — ใช้ร่วมกันทุกหน้า
 *
 * รับค่าผ่าน $paginationWrapperId string:
 *   $paginationWrapperId = 'requestPaginationWrapper';
 *
 * และ $paginationAlign (optional):
 *   $paginationAlign = 'end';   // 'start' | 'center' | 'end' (default: 'between')
 */

$_pfp_id    = $paginationWrapperId ?? 'paginationWrapper';
$_pfp_align = $paginationAlign     ?? 'between';
$_pfp_class = match($_pfp_align) {
    'start'  => 'justify-content-start',
    'center' => 'justify-content-center',
    'end'    => 'justify-content-end',
    default  => 'justify-content-between',
};
?>

<div class="card-footer bg-white border-0 py-3 px-4 rounded-bottom-4 d-flex <?= $_pfp_class ?> align-items-center">
    <div id="<?= htmlspecialchars($_pfp_id) ?>" class="w-100">
        <?php include 'includes/pagination.php'; ?>
    </div>
</div>
<?php unset($_pfp_id, $_pfp_align, $_pfp_class); ?>

