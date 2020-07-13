<?php

namespace oeleco\Larasuite\Test\Unit;

use Exception;
use oeleco\Larasuite\Test\TestCase;
use oeleco\Larasuite\Services\GroupService;

class GroupServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(GroupService::class);
        $this->faker   = \Faker\Factory::create();
    }

    protected function getEmail($username)
    {
        return $username .'@'. config('gsuite.hosted_domain');
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
    public function create_group()
    {
        $data =  [
            'name'  => $this->faker->name,
            'email' => $this->getEmail($this->faker->username),
        ];

        $group = $this->service->create($data);

        $this->assertEquals($group->getName(), $data['name']);
    }

    /** @test */
    public function updated_group()
    {
        $group = $this->service->create([
                    'name' => $this->faker->company,
                    'email' => $this->getEmail($this->faker->username),
                ]);

        $data = [
            'name' => $this->faker->company,
            'description' => $this->faker->catchPhrase,
        ];

        $UpdatedGroup = $this->service->update($group->getId(), $data);

        $this->assertEquals($UpdatedGroup->getName(), $data['name']);
    }

    /** @test */
    public function delete_group()
    {
        $group = $this->service->create([
            'name' => $this->faker->company,
            'email' => $this->getEmail($this->faker->username),
        ]);

        $this->assertEmpty(
            $this->service->destroy($group->getId())
        );
    }
}
