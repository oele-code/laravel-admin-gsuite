<?php

namespace oeleco\Larasuite\Test\Unit;

use Exception;
use oeleco\Larasuite\Test\TestCase;
use oeleco\Larasuite\Services\OrgUnitService;

class OrgUnitServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(OrgUnitService::class);
        $this->faker   = \Faker\Factory::create();
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
    public function create_organizational_unit()
    {
        $name    = $this->faker->company;
        $orgUnit = $this->service->create(['name' => $name]);

        $this->assertEquals($orgUnit->getName(), $name);
    }

    /** @test */
    public function updated_organizational_unit()
    {
        $orgUnit = $this->service->create(['name' => $this->faker->company]);

        $data = [
            'name' => $this->faker->company,
            'description' => $this->faker->catchPhrase,
        ];

        $UpdatedOrgUnit = $this->service->update($orgUnit->getId(), $data);

        $this->assertEquals($UpdatedOrgUnit->getDescription(), $data['description']);
    }

    /** @test */
    public function delete_organizational_unit()
    {
        $orgUnit = $this->service->create(['name' => $this->faker->company]);

        $this->assertEmpty(
            $this->service->destroy($orgUnit->getId())
        );
    }
}
