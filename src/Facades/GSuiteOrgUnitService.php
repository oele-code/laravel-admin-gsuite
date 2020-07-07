<?php

namespace oeleco\Larasuite\Facades;

use Illuminate\Support\Facades\Facade;
use oeleco\Larasuite\Services\OrgUnitService;

class GSuiteOrgUnitService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return OrgUnitService::class;
    }
}
