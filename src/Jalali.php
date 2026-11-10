<?php

declare(strict_types=1);

namespace Jalali\Jalali;

use Carbon\CarbonInterface;
use Closure;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

/** Instance-scoped factory; inject a clock for deterministic application tests. */
class Jalali
{
    /** @param Closure(): DateTimeImmutable|null $clock */
    public function __construct(private readonly ?Closure $clock = null, private readonly string $timezone = 'UTC') {}

    public function now(?string $timezone = null): JalaliDateTime
    {
        $date = $this->clock === null ? new DateTimeImmutable('now', new DateTimeZone('UTC')) : ($this->clock)();

        return JalaliDateTime::fromDateTime($date)->timezone($timezone ?? $this->timezone);
    }

    public function create(int $year, int $month, int $day, ?string $timezone = null): JalaliDateTime
    {
        return JalaliDateTime::create($year, $month, $day, timezone: $timezone ?? $this->timezone);
    }

    public function parse(string $input, ?string $timezone = null): JalaliDateTime
    {
        return JalaliDateTime::parse($input, $timezone ?? $this->timezone);
    }

    public function fromDateTime(DateTimeInterface $date): JalaliDateTime
    {
        return JalaliDateTime::fromDateTime($date);
    }

    public function toGregorian(string $input, ?string $timezone = null): DateTimeImmutable
    {
        return $this->parse($input, $timezone)->toDateTime();
    }

    public function today(?string $timezone = null): JalaliDateTime
    {
        return $this->now($timezone)->startOfDay();
    }

    public function yesterday(?string $timezone = null): JalaliDateTime
    {
        return $this->today($timezone)->subDays(1);
    }

    public function tomorrow(?string $timezone = null): JalaliDateTime
    {
        return $this->today($timezone)->addDays(1);
    }

    public function fromCarbon(CarbonInterface $date): JalaliDateTime
    {
        return JalaliDateTime::fromCarbon($date);
    }
}
