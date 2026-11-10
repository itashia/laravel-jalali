<?php

declare(strict_types=1);

namespace Jalali\Jalali\Tests;

use Jalali\Jalali\JalaliServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            JalaliServiceProvider::class,
        ];
    }
}
