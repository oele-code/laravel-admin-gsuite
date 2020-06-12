<?php

namespace oele_code\LaravelAdminGSuite\Providers;

use Illuminate\Support\ServiceProvider;
use oele_code\LaravelAdminGSuite\Services\UserService;

class GSuiteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/gsuite.php' => config_path('gsuite.php'),
        ], 'config');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UserService::class, function () {
            return new UserService;
        });

        $this->mergeConfigFrom(
            __DIR__.'/../config/gsuite.php',
            'gsuite'
        );
    }
}
