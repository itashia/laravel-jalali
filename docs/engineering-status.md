# Engineering status

The implementation has advanced substantially. Release approval remains pending;
this document does not certify all original acceptance requirements as complete.

## Implemented

Immutable date-time API; Gregorian/Jalali conversions; strict date and format
parsing; three digit systems; Persian month/weekday names and relative text;
calendar and elapsed arithmetic; day/week/month/quarter/year boundaries;
comparisons, native intervals and explicit reference-clock predicates;
Carbon interoperability; Laravel factory config, facade, translations,
Gregorian datetime and date-only Eloquent casts; strict bounded validation;
the global `jalali()` helper (string/DateTimeInterface/Carbon/null input,
Composer `autoload.files`-loaded, `function_exists`-guarded); bilingual
(English/Persian) API and Laravel integration guides under `docs/en/` and
`docs/fa/`.
No routes, database dependency, global date settings or Carbon macros were added.

## Actual validation

- PHP 8.5.4 / Laravel 13.40.0, this session: `composer dump-autoload`,
  `composer validate --strict`, `composer test` (PHPStan, Pint, 100%
  type-coverage, Pest), and `composer audit` all pass: 54 tests, 80,969
  assertions, 0 failures, no advisories.
- Line coverage: **98.5%** (prior measurement; no coverage driver — Xdebug/PCOV —
  was available in this session to re-measure). Calendar engine line coverage: **100%**.
- Exhaustive round trips: **1,160,374** valid dates, Jalali years 1–3177.
- PHPStan level 7, Pint and type-coverage checks pass.
- Composer metadata validates strictly; current advisory audit found no vulnerabilities.
- Isolated Laravel 12 installation on PHP 8.5: all 48 tests and 79,540 assertions pass; PHPStan passes.
- CI defines PHP 8.3/8.4/8.5 × Laravel 12/13 × Linux/Windows × dependency lanes,
  plus a 95% line-coverage gate. Hosted CI has not run on these uncommitted changes.
- Fixed a PHPStan `notIdentical.alwaysFalse` finding in `src/helpers.php` (dead
  `$value !== null` branch after type narrowing).
- Replaced the weak config-cache test (which only re-read runtime config inside
  the same Testbench process) with a genuine, process-isolated integration test
  (`tests/Feature/ConfigCacheIntegrationTest.php`): it builds a real, standalone
  Laravel 13 application (`Application::configure()->withProviders()`) in a temp
  directory, runs `php artisan config:cache` in a separate PHP subprocess,
  inspects the generated `bootstrap/cache/config.php` file for the exact
  `laravel-jalali` key/values, then boots a second fresh subprocess against the
  cached config and confirms the package reads it back correctly. Root cause of
  the earlier failure: Laravel's `ConfigCacheCommand` reboots a second
  application by requiring `bootstrap/app.php` and that reboot swaps the global
  container/facade root; inside Testbench, that second application is the
  generic skeleton (no knowledge of the package-under-test's own
  `getPackageProviders()`), so the package's default config was silently
  dropped from the cache. The new test exercises the real mechanism a consuming
  app goes through instead of relying on Testbench's single-process shortcuts.

Round trips establish internal consistency, not independent astronomical accuracy.
The engine follows Borkowski's published model and its documented uncertainty.

## Reproducible comparison

Install competitors separately:

```sh
mkdir -p /tmp/jalali-competitors
composer require --working-dir=/tmp/jalali-competitors hekmatinasser/verta:9.3 morilog/jalali:3.5.0
php benchmarks/comparison.php /tmp/jalali-competitors
```

PHP 8.5.4, UTC, same date 1405/08/19; 1,000 warmups, five batches of 10,000
operations, median nanoseconds per operation:

| Operation | This package | Verta 9.3 | Morilog 3.5.0 |
| --- | ---: | ---: | ---: |
| Formatting | 634 | 2,581 | 15,090 |
| Creation | 9,470 | 93,134 | 1,459 |
| Conversion | 2,621 | 816 | 1,874 |
| Parsing | 9,472 | 186,844 | 13,927 |

Peak whole-process memory: 10 MiB. This is not per-object allocation.
Parsing grammars and constructor validation differ between packages; these
numbers describe the specific public operations tested, not universal superiority.
All packages share one process/runtime; dependencies may therefore be shared.
Conversion performance is slower than both competitors. Larger datasets,
isolated memory measurements and Laravel overhead benchmarks remain desirable.

## Remaining release gates and limitations

- Hosted compatibility CI and PHP 8.3/8.4/Windows execution are unverified.
- Xdebug branch/path instrumentation was attempted but stopped due to runtime overhead; no branch percentage is claimed. Engine line coverage is 100%.
- Independent reference corpus needs expansion beyond current reference anchors.
- Configuration caching is now verified against a real, standalone Laravel
  application booted twice in isolated subprocesses (see above); a full
  `composer require`-based install into a separate project remains unverified
  in this sandbox (network-independent by design, but not exercised here).
- Locale selection currently means Persian names with per-call digit choice;
  English month-name localization is not provided. AM/PM remains English.
- Parser deliberately supports numeric Y/m/d/H/i/s/u, not named months or 12-hour input.
- Ambiguous local times follow PHP offset selection; nonexistent explicit wall times
  are rejected. Native boundary operations follow PHP's DST normalization.
- Timestamp behavior on 32-bit runtimes is unverified.
- Generic floating-point second differences have finite precision.
- Full release audit and public API review remain required before tagging.

**Packagist readiness: not yet approved.** No tag, push or publication was performed.
