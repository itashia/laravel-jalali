<?php

declare(strict_types=1);

namespace Jalali\Jalali\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Jalali\Jalali\Jalali
 */
class Jalali extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Jalali\Jalali\Jalali::class;
    }
}
