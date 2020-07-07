<?php

namespace oeleco\Larasuite\Providers;

use Illuminate\Support\ServiceProvider;
use oeleco\Larasuite\Services\OrgUnitService;
use oeleco\Larasuite\Services\UserService;

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

        $this->app->bind(OrgUnitService::class, function () {
            return new OrgUnitService;
        });

        $this->mergeConfigFrom(
            __DIR__.'/../config/gsuite.php',
            'gsuite'
        );
    }
}
