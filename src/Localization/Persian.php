<?php

declare(strict_types=1);

namespace Jalali\Jalali\Localization;

use Jalali\Jalali\JalaliDateTime;

final class Persian
{
    public const array MONTHS = [1 => 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];

    public const array WEEKDAYS = ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه'];

    public static function relative(JalaliDateTime $date, JalaliDateTime $reference): string
    {
        $seconds = $date->diffInSeconds($reference);
        $absolute = abs($seconds);

        if ($absolute < 1) {
            return 'هم‌اکنون';
        }
        [$divisor, $unit] = match (true) {
            $absolute >= 86400 => [86400, 'روز'],
            $absolute >= 3600 => [3600, 'ساعت'],
            $absolute >= 60 => [60, 'دقیقه'],
            default => [1, 'ثانیه'],
        };

        return Digits::persian((string) (int) floor($absolute / $divisor)).' '.$unit.($seconds > 0 ? ' پیش' : ' دیگر');
    }
}
