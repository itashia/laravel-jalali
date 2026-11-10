# Changelog

## v1.0.0 - 2026-11-10

### Laravel Jalali v1.0.0

The first stable release of **Laravel Jalali**, a PHP and Laravel package for seamless Jalali (Persian) date conversion, manipulation, and integration.

#### ✨ Features

- **Date Conversion:** Convert between Jalali (Persian) and Gregorian calendars, supporting Jalali years 1–3177.
- **Date & Time Utilities:** Comprehensive date parsing, formatting, arithmetic, and Persian numeral support.
- **Developer-Friendly API:** Global `jalali()` helper, Laravel Facade, and Carbon compatibility.
- **Eloquent Integration:** Custom date and datetime casts for Laravel Eloquent models.
- **Validation:** Built-in validation rule for Jalali dates.
- **Localization:** Configurable timezone and Persian/English translations.
- **Documentation:** Comprehensive bilingual documentation in English and Persian.
- **Testing:** Extensive automated test coverage for reliability and accuracy.

#### 📦 Requirements

- PHP 8.3 or higher
- Laravel 12 or 13

#### 🚀 Installation

```bash
composer require itashia/laravel-jalali

```
#### 📚 Documentation

See the README and documentation files for installation instructions, configuration, examples, and API references.


---

**Laravel Jalali v1.0.0 — Bringing Persian date handling to modern Laravel applications.**

## Unreleased

- Implement Borkowski calendar conversion and bounded date validation.
- Add immutable dates, timezone and Carbon interoperability, strict parsers,
  Persian localization, explicit clock references and calendar arithmetic.
- Add Gregorian datetime and date-only Eloquent casts and Jalali validation.
- Add deterministic property tests, measured coverage and comparison benchmarks.
- Preserve native provider discovery and resource publishing.
- Add the `jalali()` helper and a genuine, process-isolated config-cache
  integration test that boots a fresh Laravel application, caches
  configuration, and verifies it reads back correctly.
- Finish the English and Persian API and Laravel integration guides under
  `docs/en/` and `docs/fa/`.

No production release has been approved; see docs/engineering-status.md.
