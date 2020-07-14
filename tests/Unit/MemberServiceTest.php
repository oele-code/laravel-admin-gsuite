<?php

use function Pest\Faker\faker;
use function Tests\getEmailAccount;

use oeleco\Larasuite\Services\MemberService;
use oeleco\Larasuite\Services\GroupService;
use oeleco\Larasuite\Services\UserService;

beforeEach(function () {
    $this->service = app(MemberService::class);
    $this->group   = getGroup();
    $this->user    = getUser();
});

afterEach(function () {
    app(GroupService::class)->destroy($this->group);
    app(UserService::class)->destroy($this->user);
});

function getGroup()
{
    $createdGroup = app(GroupService::class)->create([
            'name'  => faker()->company,
            'email' => getEmailAccount()
        ]);

    return $createdGroup->getId();
}

function getUser()
{
    $createdUser = app(UserService::class)->create([
        'email'     => getEmailAccount(),
        'password'  => faker()->bothify('##??##??'),
        'firstName' => faker()->firstName,
        'lastName'  => faker()->lastName
    ]);

    return $createdUser->getEmail();
}

test('client will have desired scopes', function () {
    $client = $this->service->getClient();

    assertEquals($client->getScopes(), $this->service->getServiceSpecificScopes());
});

it('will have desired service', function () {
    assertInstanceOf(\Google_Service_Directory::class, $this->service->service);
});

test('add new member to google group', function () {
    $addedMember = $this->service->create($this->group, [ 'email' => $this->user ]);

    assertEquals($addedMember->getEmail(), $this->user);
});

test('update role for group member', function () {
    $addedMember   = $this->service->create($this->group, [
        'email' => $this->user,
        'role'  => 'OWNER'
    ]);

    sleep(5); // wait for data propagation by API

    $updatedMember = $this->service->update($this->group, $addedMember->getEmail(), 'MEMBER');

    assertEquals('MEMBER', $updatedMember->getRole());
});

test('remove a group member', function () {
    $addedMember = $this->service->create($this->group, [ 'email' => $this->user ]);

    assertEmpty(
        $this->service->destroy($this->group, $addedMember->getEmail())
    );
});
