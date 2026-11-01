<?php
/**
 * components/ui/card_header.php
 * ส่วนหัวของการ์ดตาราง (Card Header) — ใช้ร่วมกันทุกหน้า
 *
 * รับค่าผ่าน $cardHeader array:
 * [
 *   'icon'    => 'bi-list-task',           // Bootstrap Icon class (required)
 *   'title'   => 'คำขอทั้งหมด',            // ชื่อหัวตาราง (required)
 *   'search'  => [                          // ช่องค้นหา (optional)
 *       'id'          => 'searchInput',
 *       'placeholder' => 'ค้นหา...',
 *   ],
 *   'extra'   => '<div>...HTML...</div>',   // slot HTML พิเศษ เช่น filter buttons (optional)
 * ]
 */

$_ch_icon   = $cardHeader['icon']   ?? 'bi-grid';
$_ch_title  = $cardHeader['title']  ?? '';
$_ch_search = $cardHeader['search'] ?? null;
$_ch_extra  = $cardHeader['extra']  ?? null;
?>

<div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3 rounded-top-4">

    <!-- ชื่อตาราง + ไอคอนวงกลม -->
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center">
            <div class="bg-warning bg-opacity-25 text-warning rounded-circle d-inline-flex justify-content-center align-items-center me-2"
                 style="width: 35px; height: 35px; flex-shrink: 0;">
                <i class="bi <?= htmlspecialchars($_ch_icon) ?>"></i>
            </div>
            <?= htmlspecialchars($_ch_title) ?>
        </h5>

        <?php if ($_ch_extra): ?>
            <!-- Extra slot (filter buttons, tabs, etc.) -->
            <?= $_ch_extra ?>
        <?php endif; ?>
    </div>

    <?php if ($_ch_search): ?>
        <!-- ช่องค้นหา -->
        <div class="input-group shadow-sm" style="width: 250px; flex-shrink: 0;">
            <span class="input-group-text bg-light border-0 rounded-start-pill text-muted">
                <i class="bi bi-search"></i>
            </span>
            <input type="text"
                   id="<?= htmlspecialchars($_ch_search['id']) ?>"
                   class="form-control bg-light border-0 rounded-end-pill shadow-none"
                   placeholder="<?= htmlspecialchars($_ch_search['placeholder'] ?? 'ค้นหา...') ?>">
        </div>
    <?php endif; ?>

</div>
<?php unset($_ch_icon, $_ch_title, $_ch_search, $_ch_extra); ?>

