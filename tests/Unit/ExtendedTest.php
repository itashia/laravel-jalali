<?php

declare(strict_types=1);

use Jalali\Jalali\Exceptions\InvalidDate;
use Jalali\Jalali\Jalali;
use Jalali\Jalali\JalaliDateTime;

it('parses explicit formats and localizes deterministically', function () {
    $date = JalaliDateTime::parseFormat('Y-m-d H:i:s.u', '۱۴۰۵-۰۸-۱۹ ۱۲:۳۰:۴۵.۱۲۳۴۵۶');
    expect($date->format('Y/m/d H:i:s.u'))->toBe('1405/08/19 12:30:45.123456');
    expect($date->format('F l'))->toBe('آبان سه‌شنبه');
    expect($date->format('z t L'))->toBe('234 30 0');
    expect($date->diffForHumans($date->addDays(3)))->toBe('۳ روز پیش');
    expect($date->diffForHumans($date->subDays(3)))->toBe('۳ روز دیگر');
    expect($date->diffForHumans($date))->toBe('هم‌اکنون');
    foreach ([1, 60, 3600] as $seconds) {
        expect($date->diffForHumans($date->addSeconds($seconds)))->toBeString();
    }
    expect($date->format('Y', true))->toBe('۱۴۰۵');
    expect($date->format('\\Y'))->toBe('Y');
});

it('rejects invalid format grammars', function (string $format, string $input) {
    JalaliDateTime::parseFormat($format, $input);
})->with([['Y/m/d Y', '1405/08/19 1405'], ['Y/m', '1405/08'], ['Y/m/d\\', '1405/08/19'], ['Y/n/d', '1405/8/19'], ['Y/m/d', '1405/08/19x']])->throws(InvalidDate::class);

it('preserves Carbon interop and factory clock semantics', function () {
    $factory = new Jalali(fn (): DateTimeImmutable => new DateTimeImmutable('2026-11-10T12:00:00Z'));
    $date = $factory->now();
    expect($factory->today()->format('H'))->toBe('00');
    expect($factory->yesterday()->format())->toBe('1405/08/18');
    expect($factory->tomorrow()->format())->toBe('1405/08/20');
    expect($factory->create(1405, 8, 19)->format())->toBe('1405/08/19');
    expect($factory->fromCarbon($date->toCarbon())->equals($date))->toBeTrue();
    expect(JalaliDateTime::fromTimestamp($date->toDateTime()->getTimestamp())->equals($date))->toBeTrue();
    expect($date->isToday($factory->today()))->toBeTrue();
    expect($date->isPast($factory->tomorrow()))->toBeTrue();
    expect($date->isFuture($factory->yesterday()))->toBeTrue();
    expect($date->diff($date->addDays(2))->days)->toBe(2);
    expect($date->addMinutes(2)->subMinutes(2)->equals($date))->toBeTrue();
    expect($date->addHours(2)->subHours(2)->equals($date))->toBeTrue();
    expect($date->addMonths(2)->subMonths(2)->equals($date))->toBeTrue();
});

it('rejects arithmetic overflow and invalid local time', function () {
    $date = JalaliDateTime::create(1405, 8, 19);
    foreach (['subDays', 'subWeeks', 'subMonths', 'subYears', 'subHours', 'subMinutes', 'subSeconds'] as $method) {
        expect(fn () => $date->$method(PHP_INT_MIN))->toThrow(InvalidDate::class);
    }
    foreach (['addWeeks', 'addMonths', 'addYears', 'addHours', 'addMinutes', 'addSeconds'] as $method) {
        expect(fn () => $date->$method(PHP_INT_MAX))->toThrow(InvalidDate::class);
    }
    expect(fn () => JalaliDateTime::create(1, 1, 1)->subMonths(1))->toThrow(InvalidDate::class);
    expect(fn () => JalaliDateTime::create(1405, 1, 1, 24))->toThrow(InvalidDate::class);
    expect(fn () => JalaliDateTime::create(1405, 1, 1)->format('Y\\'))->toThrow(InvalidDate::class);
    expect(fn () => JalaliDateTime::create(1402, 12, 20, 2, 30, timezone: 'America/New_York'))->toThrow(InvalidDate::class);
});

it('supports escaped parser literals and native time format tokens', function () {
    $date = JalaliDateTime::parseFormat('Y/m/d\\T H:i:s', '1405/08/19T 12:30:45');
    expect($date->format('H:i:s'))->toBe('12:30:45');
    expect($date->format('g G h a A P O e U'))->toBe('12 12 12 pm PM +00:00 +0000 UTC 1794313845');
    expect((new Jalali)->fromDateTime($date->toDateTime())->equals($date))->toBeTrue();
});
