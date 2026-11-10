# راهنمای لاراول جلالی

- [نصب، شروع سریع و تابع سراسری](quick-start.md)
- [مرجع تاریخ، قالب‌بندی، تجزیه، تبدیل و محاسبه](api.md)
- [یکپارچگی با لاراول، Eloquent و اعتبارسنجی](laravel.md)

## نصب

```sh
composer require itashia/laravel-jalali
```

به PHP نسخهٔ ۸٫۳ به بالا و Laravel نسخهٔ ۱۲ یا ۱۳ نیاز دارید. لاراول سرویس‌پروایدر را از طریق قابلیت package discovery شناسایی می‌کند.

## شروع سریع

```php
$date = jalali('۱۴۰۵/۰۸/۱۹');
echo $date->format('Y/m/d H:i:s');
$fromCarbon = jalali(now());
$fromNative = jalali(new DateTimeImmutable());
$tomorrow = $date->addDays(1);
```

رشته ورودی تاریخ جلالی است؛ شیء `DateTimeInterface` یا Carbon یک لحظهٔ زمانی در تقویم میلادی است. عملیات روی تاریخ شیء تازه‌ای می‌سازند و مقدار اولیه را تغییر نمی‌دهند. برای راهنمای کامل به [مرجع API](api.md) مراجعه کنید.
