<?php
/**
 * components/ui/page_header.php
 * ส่วนหัวของหน้า (Page Header) — ใช้ร่วมกันทุกหน้า
 *
 * รับค่าผ่าน $pageHeader array:
 * [
 *   'icon'     => 'bi-boxes',              // Bootstrap Icon class (required)
 *   'title'    => 'จัดการสิ่งของ',          // ชื่อหน้า (required)
 *   'subtitle' => 'ระบบสิ่งพิมพ์...',       // คำอธิบาย (optional, มี default)
 *   'action'   => [                         // ปุ่มฝั่งขวา (optional)
 *       'label'  => 'เพิ่มรายการใหม่',
 *       'icon'   => 'bi-plus-circle-fill',
 *       'type'   => 'modal',                // 'modal' หรือ 'link'
 *       'target' => '#addItemModal',        // modal id หรือ URL
 *   ]
 * ]
 */

$_ph_icon     = $pageHeader['icon']     ?? 'bi-grid';
$_ph_title    = $pageHeader['title']    ?? 'หน้าหลัก';
$_ph_subtitle = $pageHeader['subtitle'] ?? 'ระบบสิ่งพิมพ์และของที่ระลึก';
$_ph_action   = $pageHeader['action']   ?? null;
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 pb-2" style="border-bottom: 2px solid #e9ecef;">
    <div>
        <h3 class="fw-bolder mb-1 text-dark">
            <i class="bi <?= htmlspecialchars($_ph_icon) ?> text-warning me-2"></i>
            <?= htmlspecialchars($_ph_title) ?>
        </h3>
        <span class="text-muted small"><?= htmlspecialchars($_ph_subtitle) ?></span>
    </div>

    <?php if ($_ph_action): ?>
        <?php if (($_ph_action['type'] ?? 'modal') === 'link'): ?>
            <a href="<?= htmlspecialchars($_ph_action['target']) ?>"
               class="btn btn-warning rounded-pill fw-bold px-4 py-2 shadow-sm border-0 d-flex align-items-center gap-2">
                <i class="bi <?= htmlspecialchars($_ph_action['icon'] ?? 'bi-plus-circle-fill') ?> fs-5"></i>
                <span><?= htmlspecialchars($_ph_action['label']) ?></span>
            </a>
        <?php else: ?>
            <button class="btn btn-warning rounded-pill fw-bold px-4 py-2 shadow-sm border-0 d-flex align-items-center gap-2"
                    data-bs-toggle="modal"
                    data-bs-target="<?= htmlspecialchars($_ph_action['target']) ?>">
                <i class="bi <?= htmlspecialchars($_ph_action['icon'] ?? 'bi-plus-circle-fill') ?> fs-5"></i>
                <span><?= htmlspecialchars($_ph_action['label']) ?></span>
            </button>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php unset($_ph_icon, $_ph_title, $_ph_subtitle, $_ph_action); ?>

