<?php

declare(strict_types=1);

use Jalali\Jalali\Jalali;

it('resolves the singleton', function () {
    expect(app(Jalali::class))->toBeInstanceOf(Jalali::class);
});

it('returns the same instance from the container', function () {
    expect(app(Jalali::class))->toBe(app(Jalali::class));
});

it('merges the package config', function () {
    expect(config('laravel-jalali.placeholder'))->toBe('default');
});

it('loads the package translations', function () {
    expect(trans('laravel-jalali::messages.placeholder'))->toBe('Jalali placeholder translation.');
});
