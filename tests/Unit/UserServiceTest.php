<?php

namespace oele_code\LaravelAdminGSuite\Test\Unit;

use oele_code\LaravelAdminGSuite\Services\Service;
use Illuminate\Contracts\Container\BindingResolutionException;
use oele_code\LaravelAdminGSuite\Services\UserService;
use oele_code\LaravelAdminGSuite\Test\TestCase;

class UserServiceTest extends TestCase
{
    protected $service;

    public function setUp()
    {
        parent::setUp();
        $this->service = app(UserService::class);
    }

    /** @test */
    public function client_will_have_desired_scopes()
    {
        $client = $this->service->getClient();
        $this->assertEquals($client->getScopes(), $this->service->getServiceSpecificScopes());
    }

    /** @test */
    public function it_will_have_desired_service()
    {
        $this->assertInstanceOf(\Google_Service_Directory::class, $this->service->service);
    }
}
