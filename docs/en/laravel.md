# Laravel integration

The service provider is registered through Composer package discovery. The `Jalali` facade resolves the configured factory. Config key `laravel-jalali.timezone` defaults to UTC; settings can be published with `php artisan vendor:publish --tag=laravel-jalali` and are compatible with Laravel config caching.

Use `JalaliDateTimeCast` for timestamps, optionally with a storage timezone parameter. It reads/writes Gregorian model date format and serializes UTC ISO-8601. Use `JalaliDateCast` for date-only columns; it stores Gregorian `YYYY-MM-DD` while preserving the supplied date components without timezone shift. Assign `JalaliDateTime`, `DateTimeInterface`, or null; parse strings explicitly.

`new JalaliDate(timezone: 'UTC', minimum: $min, maximum: $max)` is a strict Laravel validation rule. Bounds are inclusive. Combine with `required` or `nullable` as needed. Messages exist in English and Persian.

For deterministic tests, inject `Closure(): DateTimeImmutable` into `new Jalali($clock)`. The helper's no-argument path resolves the container in Laravel. Composer's function file guards collisions and never replaces an existing `jalali()` function. Standalone string and DateTime conversion works; no-argument use requires Laravel's `app()` and `config()` helpers.

Run `composer test`, `composer analyse`, and `composer lint:check`. `composer audit --locked` checks dependency advisories.
