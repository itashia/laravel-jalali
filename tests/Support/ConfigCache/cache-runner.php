<?php

declare(strict_types=1);

require $argv[2];

use Illuminate\Contracts\Console\Kernel as ConsoleKernel;

$app = require $argv[1].'/bootstrap/app.php';

$exitCode = $app->make(ConsoleKernel::class)->call('config:cache');

fwrite(STDOUT, json_encode([
    'exitCode' => $exitCode,
    'cachedConfigPath' => $app->getCachedConfigPath(),
]));
