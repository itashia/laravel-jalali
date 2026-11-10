# Laravel Jalali

Immutable Jalali date/time conversion for Laravel and PHP. [فارسی](README.fa.md)

```sh
composer require itashia/laravel-jalali
```

Requires PHP 8.3+ and Laravel 12 or 13. Composer discovers the provider and `Jalali` facade.

```php
$date = jalali('1405/08/19');       // strict Jalali YYYY/MM/DD
$date->format('Y/m/d H:i:s');
$fromCarbon = jalali(now());        // Carbon is a Gregorian instant
$fromNative = jalali(new DateTimeImmutable());
$date->addDays(10)->subMonths(2);   // immutable
$date->toCarbon();
```

`jalali()` without arguments uses the package factory and configured timezone. Strings are Jalali dates; `DateTimeInterface` values are Gregorian. Invalid strings raise `InvalidDate`. See [complete English docs](docs/en/README.md) and [Persian docs](docs/fa/README.md).

```php
use Jalali\Jalali\Facades\Jalali;
Jalali::now()->format('Y/m/d');
```

Eloquent casts are `JalaliDateTimeCast` for timestamps and `JalaliDateCast` for date-only storage. Validation uses `new JalaliDate()`. Publish settings and translations with `php artisan vendor:publish --tag=laravel-jalali`.

Run checks with `composer test`; run `composer audit --locked` for dependency advisories. License: MIT.
