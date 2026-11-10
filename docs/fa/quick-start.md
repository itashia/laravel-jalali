# شروع سریع

پس از نصب بسته، برای تاریخ فعلی `jalali()`، برای تاریخ جلالی رشتهٔ دقیق `YYYY/MM/DD` و برای تبدیل تاریخ میلادی یک شیء `DateTimeInterface` یا Carbon را به تابع بدهید.

```php
jalali()->format('Y/m/d H:i:s');
jalali('۱۴۰۵/۰۸/۱۹')->addDays(10)->subMonths(2);
jalali(now())->timezone('Asia/Tehran')->format('Y/m/d H:i');
```

تاریخ‌ها تغییرناپذیرند؛ متدهای محاسباتی شیء جدید برمی‌گردانند. برای جزئیات [مرجع API](api.md) و [راهنمای لاراول](laravel.md) را ببینید.
