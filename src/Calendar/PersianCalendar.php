<?php

declare(strict_types=1);

namespace Jalali\Jalali\Calendar;

use Jalali\Jalali\Exceptions\InvalidDate;

/** Borkowski's break-year model; years 1 through 3177 inclusive. */
final class PersianCalendar
{
    /** @return array{int, int, int} */
    public function toGregorian(int $year, int $month, int $day): array
    {
        [$march, $leap] = $this->yearInfo($year);

        if ($month < 1 || $month > 12 || $day < 1 || $day > ($month <= 6 ? 31 : ($month < 12 || $leap === 0 ? 30 : 29))) {
            throw new InvalidDate('Invalid Jalali date.');
        }
        $offset = ($month <= 7 ? ($month - 1) * 31 : 186 + ($month - 7) * 30) + $day - 1;
        $gregorianYear = $year + 621;
        $gregorianMonth = 3;
        $gregorianDay = $march + $offset;
        while ($gregorianDay > ($length = $this->gregorianMonthLength($gregorianYear, $gregorianMonth))) {
            $gregorianDay -= $length;
            $gregorianMonth++;

            if ($gregorianMonth === 13) {
                $gregorianYear++;
                $gregorianMonth = 1;
            }
        }

        return [$gregorianYear, $gregorianMonth, $gregorianDay];
    }

    /** @return array{int, int, int} */
    public function fromGregorian(int $year, int $month, int $day): array
    {
        if (! checkdate($month, $day, $year) || $year < 622 || $year > 3799) {
            throw new InvalidDate('Invalid or unsupported Gregorian date.');
        }
        $jalaliYear = min($year - 621, 3177);
        [$march, $leap] = $this->yearInfo($jalaliYear);
        $offset = $this->ordinal($year, $month, $day) - $this->ordinal($jalaliYear + 621, 3, $march);

        if ($offset < 0) {
            $jalaliYear--;
            [$march, $leap] = $this->yearInfo($jalaliYear);
            $offset = $this->ordinal($year, $month, $day) - $this->ordinal($jalaliYear + 621, 3, $march);
        }

        if ($offset >= ($leap === 0 ? 366 : 365)) {
            throw new InvalidDate('Gregorian date exceeds the supported Jalali range.');
        }

        return $offset < 186
            ? [$jalaliYear, intdiv($offset, 31) + 1, $offset % 31 + 1]
            : [$jalaliYear, intdiv($offset - 186, 30) + 7, ($offset - 186) % 30 + 1];
    }

    private function gregorianMonthLength(int $year, int $month): int
    {
        return match ($month) {
            2 => $this->gregorianLeap($year) ? 29 : 28,
            4, 6, 9, 11 => 30,
            default => 31,
        };
    }

    private function gregorianLeap(int $year): bool
    {
        return $year % 4 === 0 && ($year % 100 !== 0 || $year % 400 === 0);
    }

    /** Proleptic Gregorian day number; only differences are used. */
    private function ordinal(int $year, int $month, int $day): int
    {
        $previous = $year - 1;
        $before = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334][$month - 1];

        return 365 * $previous + intdiv($previous, 4) - intdiv($previous, 100) + intdiv($previous, 400)
            + $before + ($month > 2 && $this->gregorianLeap($year) ? 1 : 0) + $day;
    }

    public function daysInMonth(int $year, int $month): int
    {
        $leap = $this->isLeapYear($year);

        if ($month < 1 || $month > 12) {
            throw new InvalidDate('Jalali month must be between 1 and 12.');
        }

        return $month <= 6 ? 31 : ($month < 12 || $leap ? 30 : 29);
    }

    public function isLeapYear(int $year): bool
    {
        return $this->yearInfo($year)[1] === 0;
    }

    /** @return array{int, int} March day and years since the previous leap year. */
    private function yearInfo(int $year): array
    {
        if ($year < 1 || $year > 3177) {
            throw new InvalidDate('Jalali year must be between 1 and 3177.');
        }
        $previous = -61;
        $leaps = -14;
        $jump = 0;
        foreach ([9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210, 1635, 2060, 2097, 2192, 2262, 2324, 2394, 2456, 3178] as $boundary) {
            $jump = $boundary - $previous;

            if ($year < $boundary) {
                break;
            }
            $leaps += intdiv($jump, 33) * 8 + intdiv($jump % 33, 4);
            $previous = $boundary;
        }
        $elapsed = $year - $previous;
        $leaps += intdiv($elapsed, 33) * 8 + intdiv($elapsed % 33 + 3, 4);

        if ($jump % 33 === 4 && $jump - $elapsed === 4) {
            $leaps++;
        }
        $gregorian = $year + 621;
        $march = 20 + $leaps - (intdiv($gregorian, 4) - intdiv((intdiv($gregorian, 100) + 1) * 3, 4) - 150);

        if ($jump - $elapsed < 6) {
            $elapsed = $elapsed - $jump + intdiv($jump + 4, 33) * 33;
        }
        $leap = (($elapsed + 1) % 33 - 1) % 4;

        return [$march, $leap === -1 ? 4 : $leap];
    }
}
