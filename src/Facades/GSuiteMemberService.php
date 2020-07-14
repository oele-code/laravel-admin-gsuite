<?php
namespace oeleco\Larasuite\Facades;

use Illuminate\Support\Facades\Facade;
use oeleco\Larasuite\Services\MemberService;

class GSuiteMemberService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return MemberService::class;
    }
}
