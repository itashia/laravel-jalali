<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Jalali\Jalali\Exceptions\InvalidDate;
use Jalali\Jalali\Facades\Jalali;
use Jalali\Jalali\JalaliDateTime;
use Jalali\Jalali\Laravel\Casts\JalaliDateCast;
use Jalali\Jalali\Laravel\Casts\JalaliDateTimeCast;
use Jalali\Jalali\Laravel\Rules\JalaliDate;

it('casts Gregorian storage without changing instants or microseconds', function () {
    $model = new class extends Model
    {
        protected $dateFormat = 'Y-m-d H:i:s.u';

        protected $casts = ['occurred_at' => JalaliDateTimeCast::class];
    };
    $model->occurred_at = JalaliDateTime::create(1405, 8, 19, 12, 30, 0, 123456, 'Asia/Tehran');
    expect($model->getAttributes()['occurred_at'])->toBe('2026-11-10 09:00:00.123456');
    expect($model->occurred_at->format('Y/m/d H:i:s.u'))->toBe('1405/08/19 09:00:00.123456');
    expect($model->toArray()['occurred_at'])->toBe('2026-11-10T09:00:00.123456Z');
    $model->occurred_at = null;
    expect($model->occurred_at)->toBeNull();
});

it('rejects ambiguous cast writes and invalid stored dates', function () {
    $cast = new JalaliDateTimeCast;
    $model = new class extends Model {};
    expect(fn () => $cast->set($model, 'date', '1405/08/19', []))->toThrow(InvalidDate::class);
    expect(fn () => $cast->get($model, 'date', '2026-02-30 00:00:00', []))->toThrow(InvalidDate::class);
});

it('validates strict inputs and bounds through Laravel', function () {
    $rule = new JalaliDate(minimum: JalaliDateTime::parse('1405/01/01'), maximum: JalaliDateTime::parse('1405/12/29'));
    foreach (['۱۴۰۵/۰۸/۱۹', '1405/01/01', '1405/12/29'] as $value) {
        expect(Validator::make(['date' => $value], ['date' => ['required', $rule]])->passes())->toBeTrue();
    }
    foreach (['1404/12/30', '1406/01/01', '1405/8/19', [], 1405] as $value) {
        expect(Validator::make(['date' => $value], ['date' => ['required', $rule]])->fails())->toBeTrue();
    }
});

it('resolves facade and rejects misconfigured rule bounds', function () {
    expect(Jalali::parse('1405/08/19')->format())->toBe('1405/08/19');
    expect(fn () => new JalaliDate(minimum: JalaliDateTime::parse('1405/02/01'), maximum: JalaliDateTime::parse('1405/01/01')))->toThrow(InvalidDate::class);
    $cast = new JalaliDateTimeCast;
    $model = new class extends Model {};
    expect($cast->serialize($model, 'date', null, []))->toBeNull();
    expect(fn () => $cast->serialize($model, 'date', 'invalid', []))->toThrow(InvalidDate::class);
    expect(fn () => $cast->get($model, 'date', 12, []))->toThrow(InvalidDate::class);
});

it('preserves date-only components without timezone shifting', function () {
    $cast = new JalaliDateCast;
    $model = new class extends Model {};
    $date = JalaliDateTime::create(1405, 8, 19, timezone: 'Pacific/Kiritimati');
    expect($cast->set($model, 'date', $date, []))->toBe('2026-11-10');
    expect($cast->set($model, 'date', $date->toDateTime(), []))->toBe('2026-11-10');
    expect($cast->get($model, 'date', '2026-11-10', [])->format())->toBe('1405/08/19');
    expect($cast->serialize($model, 'date', $date, []))->toBe('2026-11-10');
    expect($cast->get($model, 'date', null, []))->toBeNull();
    expect($cast->set($model, 'date', null, []))->toBeNull();
    expect(fn () => $cast->set($model, 'date', '1405/08/19', []))->toThrow(InvalidDate::class);
    expect(fn () => $cast->get($model, 'date', 1, []))->toThrow(InvalidDate::class);
    expect(fn () => $cast->get($model, 'date', '2026-02-30', []))->toThrow(InvalidDate::class);
});

it('uses configured timezone while allowing an explicit override', function () {
    config(['laravel-jalali.timezone' => 'Asia/Tehran']);
    app()->forgetInstance(\Jalali\Jalali\Jalali::class);
    $factory = app(\Jalali\Jalali\Jalali::class);
    expect($factory->parse('1405/08/19')->format('e'))->toBe('Asia/Tehran');
    expect($factory->parse('1405/08/19', 'UTC')->format('e'))->toBe('UTC');
});
