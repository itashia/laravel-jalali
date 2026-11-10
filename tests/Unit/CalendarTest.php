<?php

declare(strict_types=1);

use Jalali\Jalali\Calendar\PersianCalendar;
use Jalali\Jalali\Exceptions\InvalidDate;

it('converts reference dates', function (array $jalali, array $gregorian) {
    $calendar = new PersianCalendar;
    expect($calendar->toGregorian(...$jalali))->toBe($gregorian);
    expect($calendar->fromGregorian(...$gregorian))->toBe($jalali);
})->with([
    [[1395, 1, 1], [2016, 3, 20]],
    [[1403, 1, 1], [2024, 3, 20]],
    [[1404, 1, 1], [2025, 3, 21]],
    [[1405, 8, 19], [2026, 11, 10]],
]);

it('rejects invalid dates', function (int $year, int $month, int $day) {
    (new PersianCalendar)->toGregorian($year, $month, $day);
})->with([[0, 1, 1], [3178, 1, 1], [1404, 12, 30], [1404, 0, 1], [1404, 1, 0], [1404, 7, 31]])->throws(InvalidDate::class);

it('round trips every day across a deterministic 200 year sample', function () {
    $calendar = new PersianCalendar;
    for ($year = 1300; $year < 1500; $year++) {
        for ($month = 1; $month <= 12; $month++) {
            for ($day = 1; $day <= $calendar->daysInMonth($year, $month); $day++) {
                expect($calendar->fromGregorian(...$calendar->toGregorian($year, $month, $day)))->toBe([$year, $month, $day]);
            }
        }
    }
});

it('round trips year boundaries throughout the entire supported range', function () {
    $calendar = new PersianCalendar;
    for ($year = 1; $year <= 3177; $year++) {
        foreach ([[1, 1], [12, $calendar->daysInMonth($year, 12)]] as [$month, $day]) {
            expect($calendar->fromGregorian(...$calendar->toGregorian($year, $month, $day)))->toBe([$year, $month, $day]);
        }
    }
    expect(fn () => $calendar->fromGregorian(2026, 2, 30))->toThrow(InvalidDate::class);
    expect(fn () => $calendar->fromGregorian(3799, 12, 31))->toThrow(InvalidDate::class);
    expect(fn () => $calendar->fromGregorian(622, 1, 1))->toThrow(InvalidDate::class);
});
