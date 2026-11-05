<?php
/**
 * includes/inventory_report_helper.php
 * ฟังก์ชันกลางสำหรับดึงข้อมูลสรุปรายงานการเบิกจ่ายพัสดุ (Inventory Report Data Service)
 * ใช้ร่วมกันระหว่าง:
 * 1. views/admin/inventory_report.php
 * 2. actions/admin/export_inventory_pdf.php
 * 3. actions/admin/export_inventory_excel.php
 */

function getInventoryReportData(PDO $pdo, ?string $mode = null, ?int $year = null, ?int $month = null): array {
    $thaiMonths = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
        5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
        9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
    ];
    $reportMode = ($mode ?? $_GET['report_mode'] ?? 'monthly') === 'fiscal' ? 'fiscal' : 'monthly';

    // ตรวจสอบช่วงเวลาที่มีข้อมูลจริงในระบบ
    $availability = $pdo->query("SELECT MIN(activity_at) AS first_at, MAX(activity_at) AS last_at FROM (
        SELECT created_at AS activity_at FROM items WHERE created_at IS NOT NULL
        UNION ALL
        SELECT created_at AS activity_at FROM inventory_transactions WHERE created_at IS NOT NULL
    ) AS inventory_dates")->fetch(PDO::FETCH_ASSOC);

    $firstDataTimestamp = strtotime($availability['first_at'] ?? 'now');
    $lastDataTimestamp  = strtotime($availability['last_at'] ?? 'now');
    $firstCalendarYear  = (int)date('Y', $firstDataTimestamp) + 543;
    $lastCalendarYear   = (int)date('Y', $lastDataTimestamp) + 543;
    $firstMonth         = (int)date('n', $firstDataTimestamp);
    $lastMonth          = (int)date('n', $lastDataTimestamp);
    $fiscalYearFor      = static fn(int $timestamp): int => (int)date('Y', $timestamp) + 543 + ((int)date('n', $timestamp) >= 10 ? 1 : 0);
    $firstFiscalYear    = $fiscalYearFor($firstDataTimestamp);
    $lastFiscalYear     = $fiscalYearFor($lastDataTimestamp);

    $minYear = $reportMode === 'fiscal' ? $firstFiscalYear : $firstCalendarYear;
    $maxYear = $reportMode === 'fiscal' ? $lastFiscalYear : $lastCalendarYear;
    $defaultYear  = $maxYear;
    $defaultMonth = $lastMonth;

    $selectedYear = (int)($year ?? $_GET['year'] ?? $defaultYear);
    if ($selectedYear < $minYear || $selectedYear > $maxYear) $selectedYear = $defaultYear;

    $selectedMonth = (int)($month ?? $_GET['month'] ?? $defaultMonth);
    if ($selectedMonth < 1 || $selectedMonth > 12) $selectedMonth = $defaultMonth;

    if ($reportMode === 'monthly') {
        if ($selectedYear === $firstCalendarYear && $selectedMonth < $firstMonth) $selectedMonth = $firstMonth;
        if ($selectedYear === $lastCalendarYear && $selectedMonth > $lastMonth) $selectedMonth = $lastMonth;
    }

    $monthOptions = array_filter($thaiMonths, static function ($number) use ($selectedYear, $firstCalendarYear, $lastCalendarYear, $firstMonth, $lastMonth) {
        return !($selectedYear === $firstCalendarYear && $number < $firstMonth)
            && !($selectedYear === $lastCalendarYear && $number > $lastMonth);
    }, ARRAY_FILTER_USE_KEY);

    $yearOptions = range($maxYear, $minYear);
    $gregorianYear = $selectedYear - 543;

    if ($reportMode === 'fiscal') {
        $periodStart = sprintf('%04d-10-01 00:00:00', $gregorianYear - 1);
        $periodEnd   = sprintf('%04d-10-01 00:00:00', $gregorianYear);
        $periodLabel = 'ปีงบประมาณ พ.ศ. ' . $selectedYear;
    } else {
        $periodStart = sprintf('%04d-%02d-01 00:00:00', $gregorianYear, $selectedMonth);
        $periodEnd   = date('Y-m-d H:i:s', strtotime($periodStart . ' +1 month'));
        $periodLabel = $thaiMonths[$selectedMonth] . ' พ.ศ. ' . $selectedYear;
    }

    $categories = $pdo->query('SELECT category_id, name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

    $ledgerStmt = $pdo->prepare("SELECT i.id, i.item_code, i.name, i.current_stock, i.low_stock_threshold, i.is_active, COALESCE(c.name, 'ไม่ระบุหมวดหมู่') AS category_name,
        COALESCE((SELECT t.current_stock FROM inventory_transactions t WHERE t.item_id = i.id AND t.created_at < :start_before ORDER BY t.created_at DESC, t.transaction_id DESC LIMIT 1),
        (SELECT t.previous_stock FROM inventory_transactions t WHERE t.item_id = i.id AND t.created_at >= :start_first AND t.created_at < :end_first ORDER BY t.created_at ASC, t.transaction_id ASC LIMIT 1), i.current_stock) AS opening_stock,
        COALESCE(SUM(CASE WHEN tx.quantity > 0 THEN tx.quantity ELSE 0 END), 0) AS qty_in,
        COALESCE(SUM(CASE WHEN tx.quantity < 0 THEN ABS(tx.quantity) ELSE 0 END), 0) AS qty_out
        FROM items i LEFT JOIN categories c ON c.category_id = i.category_id
        LEFT JOIN inventory_transactions tx ON tx.item_id = i.id AND tx.created_at >= :start_tx AND tx.created_at < :end_tx
        GROUP BY i.id, i.item_code, i.name, i.current_stock, i.low_stock_threshold, i.is_active, c.name ORDER BY i.name ASC");
    $ledgerStmt->execute([
        ':start_before' => $periodStart,
        ':start_first'  => $periodStart,
        ':end_first'    => $periodEnd,
        ':start_tx'     => $periodStart,
        ':end_tx'       => $periodEnd
    ]);
    $ledger = $ledgerStmt->fetchAll(PDO::FETCH_ASSOC);

    $totals = ['opening' => 0, 'in' => 0, 'out' => 0, 'closing' => 0];
    foreach ($ledger as &$item) {
        foreach (['opening_stock', 'qty_in', 'qty_out'] as $key) $item[$key] = (int)$item[$key];
        $item['closing_stock'] = $item['opening_stock'] + $item['qty_in'] - $item['qty_out'];
        $totals['opening'] += $item['opening_stock'];
        $totals['in']      += $item['qty_in'];
        $totals['out']     += $item['qty_out'];
        $totals['closing'] += $item['closing_stock'];
    }
    unset($item);

    $topItems = array_values(array_filter($ledger, static fn($item) => $item['qty_out'] > 0));
    usort($topItems, static fn($a, $b) => $b['qty_out'] <=> $a['qty_out']);
    $topItems = array_slice($topItems, 0, 5);
    $topMax = max(array_column($topItems, 'qty_out') ?: [1]);
    $lowStockCount = count(array_filter($ledger, static fn($item) => $item['closing_stock'] <= (int)$item['low_stock_threshold']));

    return [
        'thaiMonths'         => $thaiMonths,
        'reportMode'         => $reportMode,
        'firstDataTimestamp' => $firstDataTimestamp,
        'lastDataTimestamp'  => $lastDataTimestamp,
        'firstCalendarYear'  => $firstCalendarYear,
        'lastCalendarYear'   => $lastCalendarYear,
        'firstMonth'         => $firstMonth,
        'lastMonth'          => $lastMonth,
        'minYear'            => $minYear,
        'maxYear'            => $maxYear,
        'selectedYear'       => $selectedYear,
        'selectedMonth'      => $selectedMonth,
        'monthOptions'       => $monthOptions,
        'yearOptions'        => $yearOptions,
        'periodStart'        => $periodStart,
        'periodEnd'          => $periodEnd,
        'periodLabel'        => $periodLabel,
        'categories'         => $categories,
        'ledger'             => $ledger,
        'totals'             => $totals,
        'topItems'           => $topItems,
        'topMax'             => $topMax,
        'lowStockCount'      => $lowStockCount
    ];
}

