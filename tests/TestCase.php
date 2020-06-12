<?php

namespace oele_code\LaravelAdminGSuite\Test;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use oele_code\LaravelAdminGSuite\Facades\GSuiteUserService;
use oele_code\LaravelAdminGSuite\Providers\GSuiteServiceProvider;

class TestCase extends OrchestraTestCase
{
    /**
     * Load package service provider
     * @param  \Illuminate\Foundation\Application $app
     * @return lasselehtinen\MyPackage\MyPackageServiceProvider
     */
    protected function getPackageProviders($app)
    {
        return [
            GSuiteServiceProvider::class,
        ];
    }

    /**
     * Load package alias
     * @param  \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'GSuiteUserService' => GSuiteUserService::class,
        ];
    }

    /**
     * Set up the environment.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('gsuite', [
            'admin-domain'   => env('GOOGLE_ADMIN_DOMAIN'),
            'application-credentials' => env('GOOGLE_APPLICATION_CREDENTIALS'),
            'service-account-impersonate' => env('GOOGLE_SERVICE_ACCOUNT_IMPERSONATE')
        ]);
    }
}
