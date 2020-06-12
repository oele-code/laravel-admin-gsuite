<?php

namespace oele_code\LaravelAdminGSuite\Test\Unit;

use oele_code\LaravelAdminGSuite\Services\Service;
use Illuminate\Contracts\Container\BindingResolutionException;
use oele_code\LaravelAdminGSuite\Services\UserService;
use oele_code\LaravelAdminGSuite\Test\TestCase;

class UserServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(UserService::class);
        $this->faker   = \Faker\Factory::create();
        $this->email   = $this->getFakeEmail();
    }

    protected function getFakeEmail()
    {
        return "issac53@instiguaimaral.edu.co";
        // return $this->faker->userName .'@'. config('gsuite.admin-domain');
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

    /** @test */
    public function impsersonate_user_is_defined()
    {
        $email = config('gsuite.service-account-impersonate');
        $user  = $this->service->fetch($email);
        $this->assertEquals($user->getEmail(), $email);
    }


    /** @test */
    public function create_user()
    {
        $params = [
            'email'     => $this->email,
            'password'  => $this->faker->password,
            'firstName' => $this->faker->firstName,
            'lastName'  => $this->faker->lastName
        ];

        $user = $this->service->create($params);
        $this->assertEquals($user->getEmail(), $params['email']);
    }

    /** @test  */
    public function update_user_fullname()
    {
        $params =  [
            'firstName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName
        ];

        $user = $this->service->updateName($this->email, $params);
        $this->assertContains($user->getFullName(), implode(' ', $params));
    }

    /** @test */
    public function deleted_user()
    {
        $user = $this->service->deleteUser($this->email);
        $this->assertEmpty($user);
    }
}
