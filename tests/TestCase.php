<?php

namespace oeleco\LaravelAdminGSuite\Test;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use oeleco\LaravelAdminGSuite\Facades\GSuiteUserService;
use oeleco\LaravelAdminGSuite\Providers\GSuiteServiceProvider;

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
            'hosted_domain'   => env('GSUITE_HOSTED_DOMAIN'),
            'application_credentials' => env('GSUITE_APPLICATION_CREDENTIALS'),
            'service_account_impersonate' => env('GSUITE_SERVICE_ACCOUNT_IMPERSONATE')
        ]);
    }
}
