# یکپارچگی با لاراول

سرویس‌پروایدر از طریق قابلیت package discovery کامپوزر ثبت می‌شود. Facade
به‌نام `Jalali` همان فکتوری پیکربندی‌شده را برمی‌گرداند. کلید تنظیمات
`laravel-jalali.timezone` پیش‌فرض آن UTC است؛ تنظیمات را می‌توان با دستور
`php artisan vendor:publish --tag=laravel-jalali` منتشر کرد و با کش‌کردن
تنظیمات لاراول (`php artisan config:cache`) سازگار است.

از `JalaliDateTimeCast` برای ستون‌های تاریخ‌وزمان استفاده کنید، به‌صورت
اختیاری با پارامتر منطقهٔ زمانی ذخیره‌سازی. این کاست قالب تاریخ مدل میلادی را
می‌خواند/می‌نویسد و به‌صورت ISO-8601 با UTC سریال‌سازی می‌کند. از
`JalaliDateCast` برای ستون‌های فقط‌تاریخ استفاده کنید؛ این کاست قالب میلادی
`YYYY-MM-DD` را ذخیره می‌کند و اجزای تاریخ ارائه‌شده را بدون تغییر منطقهٔ
زمانی حفظ می‌کند. مقدار `JalaliDateTime`، `DateTimeInterface` یا `null` را
انتساب دهید؛ رشته‌ها را به‌صورت صریح تجزیه کنید.

```php
protected function casts(): array
{
    return [
        'occurred_at' => \Jalali\Jalali\Laravel\Casts\JalaliDateTimeCast::class.':UTC',
        'birthday' => \Jalali\Jalali\Laravel\Casts\JalaliDateCast::class,
    ];
}
```

`new JalaliDate(timezone: 'UTC', minimum: $min, maximum: $max)` یک قاعدهٔ
اعتبارسنجی دقیق لاراول است. بازه‌ها شامل مقدار ابتدا و انتها هستند. آن را با
`required` یا `nullable` در صورت نیاز ترکیب کنید. پیام‌ها به فارسی و انگلیسی
موجود هستند.

```php
use Jalali\Jalali\Laravel\Rules\JalaliDate;
use Jalali\Jalali\JalaliDateTime;

$rules = ['birthday' => ['required', new JalaliDate(
    minimum: JalaliDateTime::parse('1300/01/01'),
    maximum: JalaliDateTime::parse('1405/12/29'),
)]];
```

برای تست‌های قطعی، یک `Closure(): DateTimeImmutable` به `new Jalali($clock)`
تزریق کنید. مسیر بدون‌آرگومان تابع سراسری به کانتینر لاراول نیاز دارد. فایل
تابع کامپوزر از برخورد محافظت می‌کند و هرگز یک `jalali()` از پیش موجود را
بازنویسی نمی‌کند. تبدیل رشته و `DateTimeInterface` مستقل از لاراول کار
می‌کند؛ استفادهٔ بدون‌آرگومان به کمک‌توابع `app()` و `config()` لاراول نیاز
دارد.

دستورهای `composer test`، `composer analyse` و `composer lint:check` را اجرا
کنید. `composer audit --locked` هشدارهای امنیتی وابستگی‌ها را بررسی می‌کند.

برای مرجع کامل تاریخ و زمان به [مرجع API](api.md) مراجعه کنید.
