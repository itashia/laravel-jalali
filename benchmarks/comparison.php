<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';
require ($argv[1] ?? '/tmp/jalali-competitors').'/vendor/autoload.php';

use Hekmatinasser\Verta\Verta;
use Jalali\Jalali\Calendar\PersianCalendar;
use Jalali\Jalali\JalaliDateTime;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;

date_default_timezone_set('UTC');
$calendar = new PersianCalendar;
$ours = JalaliDateTime::create(1405, 8, 19);
$verta = Verta::parseFormat('Y/m/d', '1405/08/19');
$morilog = Jalalian::fromFormat('Y/m/d', '1405/08/19');
$operations = [
    'ours format' => fn () => $ours->format('Y/m/d'),
    'verta format' => fn () => $verta->format('Y/m/d'),
    'morilog format' => fn () => $morilog->format('Y/m/d'),
    'ours creation' => fn () => JalaliDateTime::create(1405, 8, 19),
    'verta creation' => fn () => Verta::create(1405, 8, 19, 0, 0, 0),
    'morilog creation' => fn () => new Jalalian(1405, 8, 19, 0, 0, 0),
    'ours conversion' => fn () => $calendar->toGregorian(1405, 8, 19),
    'verta conversion' => fn () => Verta::jalaliToGregorian(1405, 8, 19),
    'morilog conversion' => fn () => CalendarUtils::toGregorian(1405, 8, 19),
    'ours parse' => fn () => JalaliDateTime::parse('1405/08/19'),
    'verta parse' => fn () => Verta::parseFormat('Y/m/d', '1405/08/19'),
    'morilog parse' => fn () => Jalalian::fromFormat('Y/m/d', '1405/08/19'),
];
foreach ($operations as $label => $operation) {
    for ($i = 0; $i < 1000; $i++) {
        $operation();
    }
    $samples = [];
    for ($round = 0; $round < 5; $round++) {
        $start = hrtime(true);
        for ($i = 0; $i < 10000; $i++) {
            $operation();
        }
        $samples[] = (hrtime(true) - $start) / 10000;
    }
    sort($samples);
    printf("%s median %.0f ns/op\n", $label, $samples[2]);
}
printf("PHP %s, peak process memory %d bytes\n", PHP_VERSION, memory_get_peak_usage(true));
