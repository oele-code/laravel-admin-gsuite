<?php
namespace oeleco\Larasuite\Facades;

use Illuminate\Support\Facades\Facade;
use oeleco\Larasuite\Services\GroupService;

class GSuiteGroupService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return GroupService::class;
    }
}
