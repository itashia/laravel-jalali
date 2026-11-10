<?php

declare(strict_types=1);

namespace Jalali\Jalali;

use Illuminate\Support\ServiceProvider;

class JalaliServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-jalali.php', 'laravel-jalali');

        $this->app->singleton(Jalali::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'laravel-jalali');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/laravel-jalali.php' => config_path('laravel-jalali.php'),
        ], ['laravel-jalali', 'laravel-jalali-config']);

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/laravel-jalali'),
        ], ['laravel-jalali', 'laravel-jalali-lang']);
    }
}
