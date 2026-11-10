# لاراول جلالی

بسته‌ای برای تبدیل تاریخ جلالی و میلادی با اشیای تغییرناپذیر، یکپارچگی با لاراول و پشتیبانی از Carbon. [English](README.md)

```sh
composer require itashia/laravel-jalali
```

نیازمند PHP نسخهٔ ۸٫۳ به بالا و Laravel نسخهٔ ۱۲ یا ۱۳ است. لاراول سرویس‌پروایدر و Facade را خودکار شناسایی می‌کند.

```php
$date = jalali('1405/08/19');
echo $date->format('Y/m/d H:i:s');
$fromCarbon = jalali(now());
$fromNative = jalali(new DateTimeImmutable());
$tomorrow = $date->addDays(1); // شیء اصلی تغییر نمی‌کند
$carbon = $date->toCarbon();
```

بدون ورودی، `jalali()` زمان فعلی را با منطقهٔ زمانی تنظیم‌شده برمی‌گرداند. رشته‌ها تاریخ جلالی با قالب دقیق `YYYY/MM/DD` هستند؛ اشیای `DateTimeInterface` تاریخ میلادی و یک لحظهٔ زمانی را نشان می‌دهند. ورودی نامعتبر خطای `InvalidDate` ایجاد می‌کند.

راهنمای کامل: [مستندات فارسی](docs/fa/README.md) و [English documentation](docs/en/README.md).

```php
use Jalali\Jalali\Facades\Jalali;
Jalali::now()->format('Y/m/d');
```

برای زمان‌های Eloquent از `JalaliDateTimeCast` و برای ستون‌های فقط‌تاریخ از `JalaliDateCast` استفاده کنید. اعتبارسنجی با `new JalaliDate()` انجام می‌شود. تنظیمات و ترجمه‌ها را با `php artisan vendor:publish --tag=laravel-jalali` منتشر کنید.

آزمون‌ها: `composer test`. مجوز: MIT.
