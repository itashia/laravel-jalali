<?php

declare(strict_types=1);

use Jalali\Jalali\JalaliDateTime;

it('computes Jalali calendar boundaries', function () {
    $date = JalaliDateTime::create(1405, 8, 19, 12);
    expect($date->startOfYear()->format())->toBe('1405/01/01');
    expect($date->endOfYear()->format('Y/m/d H:i:s.u'))->toBe('1405/12/29 23:59:59.999999');
    expect($date->startOfQuarter()->format())->toBe('1405/07/01');
    expect($date->endOfQuarter()->format())->toBe('1405/09/30');
    expect($date->startOfWeek()->dayOfWeek())->toBe(0);
    expect($date->endOfWeek()->dayOfWeek())->toBe(6);
    expect($date->addYears(1)->subYears(1)->equals($date))->toBeTrue();
    expect($date->addWeeks(2)->subWeeks(2)->equals($date))->toBeTrue();
});

it('distinguishes local days from elapsed hours across DST', function () {
    $date = JalaliDateTime::fromDateTime(new DateTimeImmutable('2024-03-09 12:00:00', new DateTimeZone('America/New_York')));
    expect($date->diffInSeconds($date->addDays(1)))->toBe(82800.0);
    expect($date->diffInSeconds($date->addHours(24)))->toBe(86400.0);
    expect($date->addSeconds(1)->subSeconds(1)->equals($date))->toBeTrue();
});
