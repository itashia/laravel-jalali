<?php

declare(strict_types=1);

use Carbon\Carbon;
use Jalali\Jalali\Exceptions\InvalidDate;
use Jalali\Jalali\Jalali;

it('exposes the Composer loaded helper for supported inputs', function () {
    expect(function_exists('jalali'))->toBeTrue();
    expect(jalali('1405/08/19')->format())->toBe('1405/08/19');
    expect(jalali(new DateTimeImmutable('2026-11-10 09:00:00.123456', new DateTimeZone('UTC')))->format('Y/m/d H:i:s.u'))->toBe('1405/08/19 09:00:00.123456');
    expect(jalali(new DateTime('2026-11-10 09:00:00', new DateTimeZone('UTC')))->format())->toBe('1405/08/19');
    expect(jalali(Carbon::parse('2026-11-10 09:00:00.123456', 'UTC'))->format('u'))->toBe('123456');
    expect(jalali(new DateTimeImmutable('2026-11-10T00:00:00Z'), 'Asia/Tehran')->format('H'))->toBe('03');
});

it('delegates no argument to the configured clock and preserves immutability', function () {
    app()->instance(Jalali::class, new Jalali(fn () => new DateTimeImmutable('2026-11-10T00:00:00Z')));
    $date = jalali();
    expect($date->format())->toBe('1405/08/19');
    expect($date->addDays(1)->format())->toBe('1405/08/20');
    expect($date->format())->toBe('1405/08/19');
});

it('rejects malformed and wrong typed inputs', function () {
    expect(fn () => jalali('2026-11-10'))->toThrow(InvalidDate::class);
    expect(fn () => jalali('1405/12/30'))->toThrow(InvalidDate::class);
    expect(fn () => jalali(42))->toThrow(TypeError::class);
});

it('does not replace a pre-existing global helper', function () {
    expect(function_exists('j'))->toBeFalse();
});
