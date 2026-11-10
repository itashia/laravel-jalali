<?php

declare(strict_types=1);

use Jalali\Jalali\Exceptions\InvalidDate;
use Jalali\Jalali\Jalali;
use Jalali\Jalali\JalaliDateTime;

if (! function_exists('jalali')) {
    /**
     * Return now, parse a strict Jalali date string, or convert a Gregorian date-time.
     * Strings are Jalali YYYY/MM/DD; DateTimeInterface objects represent Gregorian instants.
     *
     * @throws InvalidDate
     */
    function jalali(string|DateTimeInterface|null $value = null, ?string $timezone = null): JalaliDateTime
    {
        if (is_string($value)) {
            return JalaliDateTime::parse($value, $timezone ?? 'UTC');
        }

        if ($value instanceof DateTimeInterface) {
            $date = JalaliDateTime::fromDateTime($value);

            return $timezone === null ? $date : $date->timezone($timezone);
        }

        return app(Jalali::class)->now($timezone ?? (string) config('laravel-jalali.timezone', 'UTC'));
    }
}
