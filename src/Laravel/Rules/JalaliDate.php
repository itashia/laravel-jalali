<?php

declare(strict_types=1);

namespace Jalali\Jalali\Laravel\Rules;

use Closure;
use DateTimeZone;
use Illuminate\Contracts\Validation\ValidationRule;
use Jalali\Jalali\Exceptions\InvalidDate;
use Jalali\Jalali\JalaliDateTime;

/** Strict YYYY/MM/DD; combine with Laravel required/nullable as appropriate. */
final readonly class JalaliDate implements ValidationRule
{
    public function __construct(private string $timezone = 'UTC', private ?JalaliDateTime $minimum = null, private ?JalaliDateTime $maximum = null)
    {
        new DateTimeZone($timezone);

        if ($minimum !== null && $maximum !== null && $minimum->compare($maximum) > 0) {
            throw new InvalidDate('Minimum must not follow maximum.');
        }
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('laravel-jalali::messages.invalid_date')->translate();

            return;
        }

        try {
            $date = JalaliDateTime::parse($value, $this->timezone);
        } catch (InvalidDate) {
            $fail('laravel-jalali::messages.invalid_date')->translate();

            return;
        }

        if (($this->minimum !== null && $date->compare($this->minimum) < 0) || ($this->maximum !== null && $date->compare($this->maximum) > 0)) {
            $fail('laravel-jalali::messages.out_of_range')->translate();
        }
    }
}
