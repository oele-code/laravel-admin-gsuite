<?php

namespace oeleco\Larasuite\Facades;

use oeleco\Larasuite\Services\UserPhotoService;
use Illuminate\Support\Facades\Facade;

class GSuiteUserPhotoService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return UserPhotoService::class;
    }
}
