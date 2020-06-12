<?php

namespace oeleco\LaravelAdminGSuite\Facades;

use oeleco\LaravelAdminGSuite\Services\UserService;
use Illuminate\Support\Facades\Facade;

class GSuiteUserService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return UserService::class;
    }
}
