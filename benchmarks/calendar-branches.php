<?php

declare(strict_types=1);
use Jalali\Jalali\Calendar\PersianCalendar;
use Jalali\Jalali\Exceptions\InvalidDate;

require __DIR__.'/../vendor/autoload.php';
xdebug_set_filter(XDEBUG_FILTER_CODE_COVERAGE, XDEBUG_PATH_INCLUDE, [realpath(__DIR__.'/../src/Calendar').'/']);
xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE | XDEBUG_CC_BRANCH_CHECK);
$c = new PersianCalendar;
foreach ([1, 5, 8, 9, 37, 38, 198, 199, 425, 426, 685, 686, 755, 756, 817, 818, 1110, 1111, 1180, 1181, 1209, 1210, 1399, 1400, 1403, 1404, 1634, 1635, 2059, 2060, 2096, 2097, 2191, 2192, 2261, 2262, 2323, 2324, 2393, 2394, 2455, 2456, 3177] as $y) {
    foreach ([[1, 1], [6, 31], [7, 1], [12, $c->daysInMonth($y, 12)]] as [$m,$d]) {
        if ($c->fromGregorian(...$c->toGregorian($y, $m, $d)) !== [$y, $m, $d]) {
            throw new RuntimeException('Mismatch');
        }
    }
}
foreach ([[0, 1, 1], [3178, 1, 1], [1404, 0, 1], [1404, 13, 1], [1404, 12, 30], [1404, 1, 0]] as $d) {
    try {
        $c->toGregorian(...$d);
    } catch (InvalidDate) {
    }
}
foreach ([[2026, 2, 30], [3799, 12, 31], [622, 1, 1], [621, 1, 1], [3800, 1, 1]] as $d) {
    try {
        $c->fromGregorian(...$d);
    } catch (InvalidDate) {
    }
}
foreach ([1600, 1700, 1800, 1900, 2000, 2100, 2400] as $y) {
    $c->fromGregorian($y, 3, 1);
}
foreach ([0, 13] as $m) {
    try {
        $c->daysInMonth(1405, $m);
    } catch (InvalidDate) {
    }
}
$c->daysInMonth(1405, 6);
$c->fromGregorian(2026, 1, 1);
$data = xdebug_get_code_coverage();
xdebug_stop_code_coverage();
foreach ($data as $file => $coverage) {
    if (! str_ends_with($file, 'PersianCalendar.php')) {
        continue;
    }
    $total = $hit = 0;
    foreach ($coverage['functions'] as $function) {
        foreach ($function['branches'] ?? [] as $branch) {
            $total++;

            if ($branch['hit'] > 0) {
                $hit++;
            }
        }
    }
    echo "Calendar branches: $hit/$total\n";
    file_put_contents($argv[1] ?? sys_get_temp_dir().'/jalali-engine-coverage.json', json_encode($coverage, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
}
