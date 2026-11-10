# Documentation

- [Installation, quick start and helpers](quick-start.md)
- [API reference: parsing, conversion, formatting, arithmetic and timezone](api.md)
- [Laravel: configuration, Eloquent, validation, testing and exceptions](laravel.md)

## Installation

Require `itashia/laravel-jalali` with PHP 8.3+ and Laravel 12/13. Package discovery registers the service provider. No manual provider registration is normally needed.

## Quick start

```php
$date = jalali('1405/08/19');
echo $date->format('Y/m/d H:i:s');
echo jalali(now())->format('Y/m/d');
echo jalali(new DateTimeImmutable())->toCarbon();
```

`jalali(string|DateTimeInterface|null $value = null, ?string $timezone = null): JalaliDateTime` interprets strings as Jalali `YYYY/MM/DD`; native/Carbon objects are Gregorian instants. Null means current time. `InvalidDate` signals malformed or unsupported calendar input. A pre-existing global `jalali()` is left intact by Composer's `function_exists` guard.

## Laravel

```php
use Jalali\Jalali\Facades\Jalali;
use Jalali\Jalali\Laravel\Casts\JalaliDateTimeCast;
use Jalali\Jalali\Laravel\Casts\JalaliDateCast;
use Jalali\Jalali\Laravel\Rules\JalaliDate;

Jalali::now();
// Model casts: 'created_at' => JalaliDateTimeCast::class, 'birthday' => JalaliDateCast::class
// Rule: ['birthday' => ['required', new JalaliDate()]]
```

Datetime storage remains Gregorian. Date-only storage preserves calendar components without timezone shifting. Publish configuration/translations with `php artisan vendor:publish --tag=laravel-jalali`. See [API](api.md) and [Laravel details](laravel.md).
