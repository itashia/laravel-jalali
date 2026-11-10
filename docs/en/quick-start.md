# Quick start

Install with Composer. `jalali()` reads the configured clock; use a string for strict Jalali date input and a native `DateTimeInterface`/Carbon object for a Gregorian instant. Chain methods safely because values are immutable.

```php
jalali()->format('Y/m/d H:i:s');
jalali('۱۴۰۵/۰۸/۱۹')->addDays(10)->subMonths(2);
jalali(now())->timezone('Asia/Tehran')->format('Y/m/d H:i');
```

See [API](api.md), [Laravel integration](laravel.md), and [Persian quick start](../fa/quick-start.md).
