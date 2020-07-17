<?php

namespace Tests;

use oeleco\Larasuite\Facades\GSuiteUserService;
use oeleco\Larasuite\Facades\GSuiteGroupService;
use oeleco\Larasuite\Facades\GSuiteMemberService;
use oeleco\Larasuite\Facades\GSuiteOrgUnitService;

use oeleco\Larasuite\Facades\GSuiteUserPhotoService;
use oeleco\Larasuite\Providers\GSuiteServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

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
            'GSuiteOrgUnitService' => GSuiteOrgUnitService::class,
            'GSuiteGroupService' => GSuiteGroupService::class,
            'GSuiteMemberService' => GSuiteMemberService::class,
            'GSuiteUserPhotoService' => GSuiteUserPhotoService::class
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
