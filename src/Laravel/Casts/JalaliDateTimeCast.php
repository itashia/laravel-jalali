<?php

declare(strict_types=1);

namespace Jalali\Jalali\Laravel\Casts;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use Jalali\Jalali\Exceptions\InvalidDate;
use Jalali\Jalali\JalaliDateTime;

/**
 * Gregorian storage in an explicit timezone; serialization is Gregorian ISO 8601.
 *
 * @implements CastsAttributes<JalaliDateTime|null, mixed>
 */
final class JalaliDateTimeCast implements CastsAttributes, SerializesCastableAttributes
{
    public bool $withoutObjectCaching = true;

    public function __construct(private readonly string $timezone = 'UTC')
    {
        new DateTimeZone($timezone);
    }

    /** @param array<string, mixed> $attributes */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?JalaliDateTime
    {
        if ($value === null) {
            return null;
        }

        if (! is_string($value)) {
            throw new InvalidDate('Stored datetime must be a Gregorian string or null.');
        }
        $format = $model->getDateFormat();
        $date = DateTimeImmutable::createFromFormat('!'.$format, $value, new DateTimeZone($this->timezone));

        if ($date === false || $date->format($format) !== $value) {
            throw new InvalidDate('Stored datetime does not match the model date format.');
        }

        return JalaliDateTime::fromDateTime($date);
    }

    /** @param array<string, mixed> $attributes */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof JalaliDateTime) {
            $value = $value->toDateTime();
        }

        if (! $value instanceof DateTimeInterface) {
            throw new InvalidDate('Assign a JalaliDateTime, DateTimeInterface or null; parse input explicitly.');
        }

        return DateTimeImmutable::createFromInterface($value)->setTimezone(new DateTimeZone($this->timezone))->format($model->getDateFormat());
    }

    /** @param array<string, mixed> $attributes */
    public function serialize(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! $value instanceof JalaliDateTime) {
            throw new InvalidDate('Unexpected cast value.');
        }

        return $value->timezone('UTC')->formatGregorian('Y-m-d\TH:i:s.u\Z');
    }
}
