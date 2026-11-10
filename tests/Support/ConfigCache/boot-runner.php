<?php

declare(strict_types=1);

require $argv[2];

use Illuminate\Contracts\Console\Kernel as ConsoleKernel;

$app = require $argv[1].'/bootstrap/app.php';

$app->make(ConsoleKernel::class)->bootstrap();

fwrite(STDOUT, json_encode([
    'isCached' => $app->configurationIsCached(),
    'timezone' => $app->make('config')->get('laravel-jalali.timezone'),
    'placeholder' => $app->make('config')->get('laravel-jalali.placeholder'),
]));
