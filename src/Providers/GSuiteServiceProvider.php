<?php

namespace oeleco\Larasuite\Providers;

use Illuminate\Support\ServiceProvider;
use oeleco\Larasuite\Services\UserService;
use oeleco\Larasuite\Services\GroupService;
use oeleco\Larasuite\Services\MemberService;
use oeleco\Larasuite\Services\OrgUnitService;

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

        $this->app->bind(GroupService::class, function () {
            return new GroupService;
        });

        $this->app->bind(MemberService::class, function () {
            return new MemberService;
        });

        $this->mergeConfigFrom(
            __DIR__.'/../config/gsuite.php',
            'gsuite'
        );
    }
}
