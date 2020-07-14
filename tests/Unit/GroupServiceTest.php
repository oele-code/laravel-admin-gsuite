<?php

use function Pest\Faker\faker;
use function Tests\getEmailAccount;

use oeleco\Larasuite\Services\GroupService;

beforeEach(function () {
    $this->service = app(GroupService::class);
    $this->email   = getEmailAccount();
});

test('client will have desired scopes', function () {
    $client = $this->service->getClient();

    assertEquals($client->getScopes(), $this->service->getServiceSpecificScopes());
});

it('will have desired service', function () {
    assertInstanceOf(\Google_Service_Directory::class, $this->service->service);
});

test('create a new group', function () {
    $data =  [
        'name'  => faker()->company,
        'email' => $this->email,
    ];

    $createdGroup = $this->service->create($data);

    assertEquals(
        [
        'name'  => $createdGroup->getName(),
        'email' => $createdGroup->getEmail()
    ],
        $data
    );

    $this->service->destroy($createdGroup->getId());
});

test('update a google description', function () {
    $createdGroup = $this->service->create([
        'name'  => faker()->company,
        'email' => $this->email
    ]);

    sleep(5); // wait for data propagation by API

    $data = [
        'name'        => faker()->company,
        'description' => faker()->catchPhrase,
    ];

    $UpdatedGroup = $this->service->update($createdGroup->getId(), $data);

    $this->assertEquals([
        'name'        => $UpdatedGroup->getName(),
        'description' => $UpdatedGroup->getDescription()
    ], $data);

    $this->service->destroy($UpdatedGroup->getId());
});


test('delete a google group', function () {
    $createdGroup = $this->service->create([
        'name'  => faker()->company,
        'email' => $this->email
    ]);

    sleep(5); // wait for data propagation by API

    $this->assertEmpty(
        $this->service->destroy($createdGroup->getId())
    );
});
