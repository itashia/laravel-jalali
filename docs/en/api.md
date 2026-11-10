# Date and time API

## Values and clocks

`JalaliDateTime` is immutable. Factories: `create`, `parse`, `parseFormat`,
`fromDateTime`, `fromCarbon`, `fromTimestamp`. Native/Carbon input preserves
microseconds and timezone. `toDateTime` and `toCarbon` return immutable values.
The container factory (`Jalali\Jalali\Jalali`) and the `Jalali` facade expose
`now`, `today`, `yesterday`, `tomorrow`, `create`, `parse`, `fromDateTime`,
`fromCarbon`, `toGregorian`.

```php
use Jalali\Jalali\JalaliDateTime;

JalaliDateTime::parse('1405/08/19');
JalaliDateTime::fromDateTime(new DateTimeImmutable('2026-11-10 09:00:00'));
JalaliDateTime::fromCarbon(\Carbon\Carbon::now());
```

The factory uses `laravel-jalali.timezone` (UTC by default). Explicit timezone
arguments override it. Config is captured when the singleton resolves; use
explicit arguments for request-specific settings in long-running workers.
Inject a closure into the factory (`new Jalali($clock)`) to control current
time in tests; there is no global test clock.

The engine's `PersianCalendar` supports Jalali years 1–3177 and provides
`toGregorian`, `fromGregorian`, `isLeapYear`, and `daysInMonth`. It follows
the documented break-year algorithm in [research](../research.md).

## Helpers

```php
jalali()->format('Y/m/d H:i:s');                    // configured clock
jalali('1405/08/19');                                // strict Jalali string
jalali(new DateTimeImmutable());                     // Gregorian instant
jalali(\Carbon\Carbon::now(), 'Asia/Tehran');         // Carbon, explicit timezone
```

`jalali(string|DateTimeInterface|null $value = null, ?string $timezone = null): JalaliDateTime`
interprets strings as Jalali `YYYY/MM/DD`; `DateTimeInterface` (including
Carbon) values are Gregorian instants. Null resolves to the current time
through the container factory. The helper is loaded through Composer's
`autoload.files`, guarded by `function_exists`, so it never replaces a
pre-existing global `jalali()`.

## Parsing and conversion

`parse` requires `YYYY/MM/DD`. `parseFormat(format, input, timezone)` supports
`Y m d H i s u`: respectively four-digit year, two-digit month/day/hour/minute/
second and six-digit microseconds. `Y`/`m`/`d` must each appear exactly once;
time components default to zero. Escaped literals and punctuation match
exactly. Unsupported tokens, duplicates, malformed input and invalid dates
throw `InvalidDate`. All three digit scripts (ASCII, Persian, Arabic-Indic)
are accepted, even mixed. No whitespace is trimmed.

```php
JalaliDateTime::parseFormat('Y-m-d H:i', '1405-08-19 09:30');
JalaliDateTime::parse('۱۴۰۵/۰۸/۱۹'); // Persian digits
```

## Formatting

Jalali `format()` supports `Y m n d j F l z t L`; time tokens `H G h g i s u a A`;
timezone tokens `P O e`; and timestamp `U`. `F`/`l` are Persian month/weekday
names. `z` is zero-based day-of-year, `t` month length, `L` leap flag.
Punctuation is literal and backslash escapes the next character. Unsupported
ASCII letters throw `InvalidDate`. Set the second argument `true` for Persian
digits. AM/PM tokens remain English.

```php
jalali('1405/08/19')->format('l j F Y', true); // Persian weekday, day, month, year
jalali('1405/08/19')->diffForHumans(jalali('1405/08/20'));
```

`diffForHumans(reference)` produces Persian relative text, truncating elapsed
seconds to days/hours/minutes/seconds; days mean 86400 seconds, not calendar-day
boundaries. It requires an explicit reference to avoid hidden global clock
dependencies.

## Arithmetic and comparison

`addDays/Weeks/Months/Years/Hours/Minutes/Seconds` and corresponding `sub*`
methods return new values. Negative arguments reverse direction. Days/weeks use
local calendar arithmetic; hours/minutes/seconds use elapsed UTC time.
Months/years clamp to the final valid day. Arithmetic rejects out-of-range
results; reversing a clamped month change need not restore the original day.

```php
jalali('1405/08/19')->addDays(10)->subMonths(2);
jalali('1405/01/31')->addMonths(1); // clamps to 1405/02/31 -> last valid day
```

`startOf*` / `endOf*` support Day, Week, Month, Quarter, Year. Weeks start
Saturday. End boundaries use 23:59:59.999999. Local midnight uses PHP timezone
resolution; nonexistent midnight may resolve to the first valid time. Explicit
`create` rejects nonexistent wall times. Repeated wall times use PHP's offset
selection.

`compare`, `equals`, `isBefore`, `isAfter` compare instants. `diff` returns
native `DateInterval`; `diffInSeconds` returns a signed floating-point elapsed
difference (other minus this), with floating-point precision limits for remote
dates. `isPast`, `isFuture`, `isToday` require an explicit reference. `dayOfWeek`
is Saturday=0; `dayOfYear` is zero-based. `daysInMonth` and `isLeapYear` use the
engine.

## Carbon interoperability

```php
$carbon = jalali('1405/08/19')->toCarbon();          // CarbonImmutable, Gregorian instant
$jalali = JalaliDateTime::fromCarbon($carbon);
```

`toCarbon()` returns `CarbonImmutable`. `fromCarbon(CarbonInterface $date)` and
`fromDateTime(DateTimeInterface $date)` both treat the input as a Gregorian
instant and preserve its timezone and microseconds.

## Localization

Digit scripts (ASCII, Persian, Arabic-Indic) are selected per `format()` call
via the second boolean argument; parsing accepts all three, even mixed, with
no configuration needed. Persian month and weekday names (`F`, `l`) and
`diffForHumans` relative text are provided through `src/Localization/Persian.php`
and `lang/{en,fa}/messages.php`; AM/PM remains English. There is no global
locale switch — localization is explicit per call or per translation key.

## Laravel casts

```php
protected function casts(): array
{
    return [
        'occurred_at' => \Jalali\Jalali\Laravel\Casts\JalaliDateTimeCast::class.':UTC',
        'birthday' => \Jalali\Jalali\Laravel\Casts\JalaliDateCast::class,
    ];
}
```

Datetime storage uses the model's date format and explicit storage timezone.
Set `$dateFormat = 'Y-m-d H:i:s.u'` and use a database column supporting fractional
seconds when precision matters. Otherwise the configured storage format loses
fractional precision deliberately. JSON serialization is Gregorian UTC ISO 8601.
The date-only cast stores YYYY-MM-DD using supplied local date components, with
no timezone shift; reading returns a UTC-midnight carrier value. Its JSON value
is Gregorian YYYY-MM-DD. No database or global serialization behavior is changed.
Both casts accept JalaliDateTime, DateTimeInterface or null on write. Parse strings
explicitly. Invalid stored data and ambiguous string assignments throw InvalidDate.

## Validation

```php
use Jalali\Jalali\Laravel\Rules\JalaliDate;
use Jalali\Jalali\JalaliDateTime;

$rules = ['birthday' => ['required', new JalaliDate(
    timezone: 'UTC',
    minimum: JalaliDateTime::parse('1300/01/01'),
    maximum: JalaliDateTime::parse('1405/12/29'),
)]];
```

Bounds compare instants inclusively. Use midnight bounds in the same timezone
for date-only validation. Combine with Laravel required/nullable; the rule is
not implicit. Error messages are available in English and Persian. Invalid
constructor timezone or reversed bounds throw immediately as configuration errors.

## Configuration

Config key `laravel-jalali.timezone` (default `UTC`) controls the factory's
default timezone, read when the `Jalali` singleton first resolves. Publish it
with `php artisan vendor:publish --tag=laravel-jalali` to override it in
`config/laravel-jalali.php`. The configuration is compatible with
`php artisan config:cache`. See [Laravel integration](laravel.md) for details.

Invalid values, unsupported years, invalid formats and overflow throw
`Jalali\Jalali\Exceptions\InvalidDate`.
