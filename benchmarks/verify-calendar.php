<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Jalali\Jalali\Calendar\PersianCalendar;

$calendar = new PersianCalendar;
$count = 0;
for ($year = 1; $year <= 3177; $year++) {
    for ($month = 1; $month <= 12; $month++) {
        for ($day = 1; $day <= $calendar->daysInMonth($year, $month); $day++) {
            if ($calendar->fromGregorian(...$calendar->toGregorian($year, $month, $day)) !== [$year, $month, $day]) {
                throw new RuntimeException("Round trip failed: $year/$month/$day");
            }
            $count++;
        }
    }
}
printf("Verified %d valid Jalali dates.\n", $count);
