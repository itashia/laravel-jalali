<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Jalali\Jalali\Calendar\PersianCalendar;
use Jalali\Jalali\JalaliDateTime;

$calendar = new PersianCalendar;
$date = JalaliDateTime::create(1405, 8, 19);
$operations = [
    'conversion' => fn () => $calendar->toGregorian(1405, 8, 19),
    'parsing' => fn () => JalaliDateTime::parse('1405/08/19'),
    'formatting' => fn () => $date->format('Y/m/d H:i:s'),
    'creation' => fn () => JalaliDateTime::create(1405, 8, 19),
];
foreach ($operations as $name => $operation) {
    for ($i = 0; $i < 1000; $i++) {
        $operation();
    }
    $start = hrtime(true);
    for ($i = 0; $i < 10000; $i++) {
        $operation();
    }
    printf("%s: %.2f ns/op\n", $name, (hrtime(true) - $start) / 10000);
}
printf("PHP %s; peak process memory %d bytes\n", PHP_VERSION, memory_get_peak_usage(true));
