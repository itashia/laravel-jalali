---
name: laravel-jalali-development
description: >
  Configure and apply the Laravel Jalali package in Laravel applications.
license: MIT
metadata:
  author: Arshia Mohammadei
---

# Laravel Jalali

Use this skill when a Laravel application needs to integrate the Laravel Jalali package.

## Primary Goal

- apply the `itashia/laravel-jalali` package's public API in the smallest correct way

## Workflow

### 1. Inspect the Laravel app context

- confirm the app is a Laravel project
- inspect the target code paths where the package should be applied

### 2. Apply the package's public API

Use `Jalali\Jalali\Facades\Jalali::now('Asia/Tehran')` or `Jalali\Jalali\JalaliDateTime::parse('1405/08/19')`. Parsing is strict YYYY/MM/DD. Operations are immutable. The implementation is not release ready; use `Laravel\Casts\JalaliDateTimeCast` for Gregorian timestamp storage and `Laravel\Rules\JalaliDate` for strict date input. See `docs/api.md` for date-only semantics and timezone defaults.

## Rules, References, and Templates

Read before executing:

- no additional resource files for this skill

## Examples

- Format a native Carbon value with `Jalali::fromDateTime($model->created_at)->format('Y/m/d')`; this does not change storage or serialization.

## Anti-patterns

- do not document package internals here; keep the skill focused on adoption in Laravel apps
