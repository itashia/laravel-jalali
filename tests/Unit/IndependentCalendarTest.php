<?php

declare(strict_types=1);

use Jalali\Jalali\Calendar\PersianCalendar;

it('agrees with independent ICU Persian fields around modern Nowruz and Gregorian leap boundaries', function () {
    $reference = IntlCalendar::createInstance('UTC', 'en_US@calendar=persian');
    $calendar = new PersianCalendar;
    foreach (range(1900, 2100) as $year) {
        foreach ([[2, 28], [3, 1], [3, 19], [3, 20], [3, 21], [3, 22], [12, 31]] as [$month, $day]) {
            $date = new DateTimeImmutable(sprintf('%04d-%02d-%02d', $year, $month, $day), new DateTimeZone('UTC'));
            $reference->setTime($date->getTimestamp() * 1000);
            expect($calendar->fromGregorian($year, $month, $day))->toBe([
                $reference->get(IntlCalendar::FIELD_YEAR),
                $reference->get(IntlCalendar::FIELD_MONTH) + 1,
                $reference->get(IntlCalendar::FIELD_DAY_OF_MONTH),
            ]);
        }
    }
});
