<?php

use function Pest\Faker\faker;

use oeleco\Larasuite\Services\OrgUnitService;

beforeEach(function () {
    $this->service = app(OrgUnitService::class);
});

test('client will have desired scopes', function () {
    $client = $this->service->getClient();

    assertEquals($client->getScopes(), $this->service->getServiceSpecificScopes());
});

it('will have desired service', function () {
    assertInstanceOf(\Google_Service_Directory::class, $this->service->service);
});

test('create a new organizational unit', function () {
    $name    = faker()->company;
    $orgUnitCreated = $this->service->create(['name' => $name]);

    assertEquals($orgUnitCreated->getName(), $name);

    $this->service->destroy($orgUnitCreated->getId());
});

test('update description of organizational unit', function () {
    $orgUnitCreated = $this->service->create([
        'name' => faker()->company
    ]);

    sleep(5); // wait for data replication by api

    $data = [
        'name'        => faker()->company,
        'description' => faker()->catchPhrase,
    ];

    $orgUnitUpdated = $this->service->update($orgUnitCreated->getId(), $data);

    assertEquals(
        [
        'name' => $orgUnitUpdated->getName(),
        'description' => $orgUnitUpdated->getDescription()
    ],
        $data
    );

    $this->service->destroy($orgUnitUpdated->getId());
});

test('delete organizational unit', function () {
    $orgUnitCreated = $this->service->create([
        'name' => faker()->company
    ]);

    $this->assertEmpty(
        $this->service->destroy($orgUnitCreated->getId())
    );
});
