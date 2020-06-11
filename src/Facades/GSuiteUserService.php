<?php

namespace oele_code\LaravelAdminGSuite\Facades;

use oele_code\LaravelAdminGSuite\Services\UserService;
use Illuminate\Support\Facades\Facade;

class GSuiteUserService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return UserService::class;
    }
}
