<?php

namespace oeleco\Larasuite\Facades;

use oeleco\Larasuite\Services\UserService;
use Illuminate\Support\Facades\Facade;

class GSuiteUserService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return UserService::class;
    }
}
