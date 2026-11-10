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
 * Date-only storage preserves calendar components; no timezone conversion occurs.
 *
 * @implements CastsAttributes<JalaliDateTime|null, mixed>
 */
final class JalaliDateCast implements CastsAttributes, SerializesCastableAttributes
{
    public bool $withoutObjectCaching = true;

    /** @param array<string, mixed> $attributes */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?JalaliDateTime
    {
        if ($value === null) {
            return null;
        }

        if (! is_string($value)) {
            throw new InvalidDate('Stored date must be a Gregorian YYYY-MM-DD string.');
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, new DateTimeZone('UTC'));

        if ($date === false || $date->format('Y-m-d') !== $value) {
            throw new InvalidDate('Invalid stored Gregorian date.');
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
            return $value->formatGregorian('Y-m-d');
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        throw new InvalidDate('Assign a parsed date object or null.');
    }

    /** @param array<string, mixed> $attributes */
    public function serialize(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $this->set($model, $key, $value, $attributes);
    }
}
