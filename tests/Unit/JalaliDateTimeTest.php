<?php

declare(strict_types=1);

use Jalali\Jalali\Exceptions\InvalidDate;
use Jalali\Jalali\Jalali;
use Jalali\Jalali\JalaliDateTime;
use Jalali\Jalali\Localization\Digits;

it('parses all three digit systems strictly', function () {
    foreach (['1405/08/19', '۱۴۰۵/۰۸/۱۹', '١٤٠٥/٠٨/١٩'] as $input) {
        expect(JalaliDateTime::parse($input)->formatGregorian('Y-m-d'))->toBe('2026-11-10');
    }
    expect(Digits::persian('1405'))->toBe('۱۴۰۵');
});

it('rejects malformed input', function (string $input) {
    JalaliDateTime::parse($input);
})->with(['1405/8/19', '1405/08/19 extra', '1404/12/30', '', '1405-08-19'])->throws(InvalidDate::class);

it('preserves instants and microseconds without mutating native inputs', function () {
    $native = new DateTime('2026-11-10 22:30:01.123456', new DateTimeZone('UTC'));
    $date = JalaliDateTime::fromDateTime($native);
    $tehran = $date->timezone('Asia/Tehran');
    expect($tehran->format('Y/m/d H:i:s.u'))->toBe('1405/08/20 02:00:01.123456');
    expect($date->compare($tehran))->toBe(0);
    $native->modify('+1 day');
    expect($date->format('Y/m/d'))->toBe('1405/08/19');
});

it('clamps month arithmetic immutably', function () {
    $date = JalaliDateTime::create(1404, 6, 31);
    expect($date->addMonths(6)->format())->toBe('1404/12/29');
    expect($date->format())->toBe('1404/06/31');
    expect($date->addDays(1)->format())->toBe('1404/07/01');
});

it('uses an instance clock', function () {
    $factory = new Jalali(fn (): DateTimeImmutable => new DateTimeImmutable('2026-11-10T00:00:00Z'));
    expect($factory->now()->format())->toBe('1405/08/19');
    expect($factory->toGregorian('1405/08/19')->format('Y-m-d'))->toBe('2026-11-10');
});

it('rejects unsupported format tokens', function () {
    JalaliDateTime::create(1405, 1, 1)->format('W');
})->throws(InvalidDate::class);
