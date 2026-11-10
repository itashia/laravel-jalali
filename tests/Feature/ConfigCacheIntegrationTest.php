<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

function makeConfigCacheApp(): string
{
    $basePath = sys_get_temp_dir().'/laravel-jalali-config-cache-'.bin2hex(random_bytes(6));

    foreach (['bootstrap/cache', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'config'] as $directory) {
        mkdir($basePath.'/'.$directory, 0755, true);
    }

    file_put_contents($basePath.'/bootstrap/app.php', <<<'PHP'
        <?php

        use Illuminate\Foundation\Application;

        return Application::configure(basePath: dirname(__DIR__))->withProviders()->create();
        PHP);

    file_put_contents($basePath.'/bootstrap/providers.php', <<<'PHP'
        <?php

        return [
            Jalali\Jalali\JalaliServiceProvider::class,
        ];
        PHP);

    return $basePath;
}

function runConfigCacheScript(string $script, string $basePath): array
{
    $process = new Process([
        PHP_BINARY,
        __DIR__.'/../Support/ConfigCache/'.$script,
        $basePath,
        __DIR__.'/../../vendor/autoload.php',
    ]);

    $process->run();

    expect($process->isSuccessful())->toBeTrue($process->getErrorOutput());

    return json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
}

it('caches the package configuration for a freshly booted Laravel application and reads it back', function () {
    $basePath = makeConfigCacheApp();

    try {
        $cacheResult = runConfigCacheScript('cache-runner.php', $basePath);

        expect($cacheResult['exitCode'])->toBe(0);
        expect(is_file($cacheResult['cachedConfigPath']))->toBeTrue();

        $cached = require $cacheResult['cachedConfigPath'];

        expect($cached)->toHaveKey('laravel-jalali');
        expect($cached['laravel-jalali'])->toBe([
            'placeholder' => 'default',
            'timezone' => 'UTC',
        ]);

        $bootResult = runConfigCacheScript('boot-runner.php', $basePath);

        expect($bootResult['isCached'])->toBeTrue();
        expect($bootResult['timezone'])->toBe('UTC');
        expect($bootResult['placeholder'])->toBe('default');
    } finally {
        (new Process(['rm', '-rf', $basePath]))->run();
    }
});
