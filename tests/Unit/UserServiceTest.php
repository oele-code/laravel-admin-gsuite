<?php

namespace oeleco\LaravelAdminGSuite\Test\Unit;

use Exception;
use oeleco\LaravelAdminGSuite\Test\TestCase;
use oeleco\LaravelAdminGSuite\Services\UserService;

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
        return $this->faker->userName .'@'. config('gsuite.hosted_domain');
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
    public function impersonate_user_is_defined()
    {
        $email = config('gsuite.service_account_impersonate');
        $user  = $this->service->fetch($email);
        $this->assertEquals($user->getEmail(), $email);
    }


    /** @test */
    public function create_user_account()
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

    /** @test */
    public function cant_create_duplicate_account()
    {
        try {
            $params = [
                'email'     => $this->email,
                'password'  => $this->faker->password,
                'firstName' => $this->faker->firstName,
                'lastName'  => $this->faker->lastName
            ];

            $user = $this->service->create($params);
            $this->assertEquals($user->getEmail(), $params['email']);
        } catch (Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e, 'An invalid exception was thrown');
        }
    }

    /** @test  */
    public function update_user_name()
    {
        $params =  [
            'firstName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName
        ];

        $user = $this->service->updateName($this->email, $params);
        $this->assertSame($user->getFirstName() . ' ' . $user->getLastName(), implode(' ', $params));
    }

    /** @test */
    public function update_password_account()
    {
        $user = $this->service->setPassword($this->email, $this->faker->password);
        $this->assertEquals($user->getEmail(), $this->email);
    }

    /** @test */
    public function suspend_account()
    {
        $user = $this->service->suspend($this->email);
        $this->assertTrue($user->getSuspended());
    }

    /** @test */
    public function active_account()
    {
        $user = $this->service->activate($this->email);
        $this->assertFalse($user->getSuspended());
    }

    /** @test */
    public function deleted_user_account()
    {
        $user = $this->service->destroy($this->email);
        $this->assertEmpty($user);
    }
}
