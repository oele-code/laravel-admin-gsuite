<?php

use function Pest\Faker\faker;
use function Tests\getEmailAccount;

use oeleco\Larasuite\Services\UserService;

beforeEach(function () {
    $this->service = app(UserService::class);
    $this->email   = getEmailAccount();
});

test('client will have desired scopes', function () {
    $client = $this->service->getClient();

    assertEquals($client->getScopes(), $this->service->getServiceSpecificScopes());
});

it('it will have desired service', function () {
    assertInstanceOf(\Google_Service_Directory::class, $this->service->service);
});

it('impersonate user is defined', function () {
    $email = $this->service->getImpersonateUser();
    $user  = $this->service->fetch($email);

    assertEquals($user->getEmail(), $email);
});

test('create user account', function () {
    $params = [
        'email'     => $this->email,
        'password'  => faker()->bothify('##??##??'),
        'firstName' => faker()->firstName,
        'lastName'  => faker()->lastName
    ];

    $createdUser = $this->service->create($params);

    assertEquals($createdUser->getEmail(), $params['email']);

    $this->service->destroy($createdUser->getEmail());
});

test('cant create duplicate account', function () {
    $params = [
        'email'     => $this->email,
        'password'  =>faker()->bothify('##??##??'),
        'firstName' =>faker()->firstName,
        'lastName'  =>faker()->lastName
    ];

    $createdUser = $this->service->create($params);
    sleep(5); // wait for data replication by API

    try {
        $this->service->create($params);
    } catch (\Exception $e) {
        assertEquals($e->getMessage(), 'already exists a count with the email '. $this->email);
    }

    $this->service->destroy($createdUser->getEmail());
});

test('update user name', function () {
    $createdUser = $this->service->create([
        'email'     => $this->email,
        'password'  => faker()->bothify('##??##??'),
        'firstName' => faker()->firstName,
        'lastName'  => faker()->lastName
    ]);

    sleep(5); // wait for data replication by API

    $updatedParams =  [
        'firstName' => faker()->firstName,
        'lastName'  => faker()->lastName
    ];

    $updatedUser = $this->service->update($createdUser->getEmail(), $updatedParams);

    assertEquals($updatedParams, [
        'firstName' => $updatedUser->getFirstName(),
        'lastName'  => $updatedUser->getLastName()
    ]);

    $this->service->destroy($updatedUser->getEmail());
});

test('update password account', function () {
    $createdUser = $this->service->create([
        'email'     => $this->email,
        'password'  => faker()->bothify('##??##??'),
        'firstName' => faker()->firstName,
        'lastName'  => faker()->lastName
    ]);

    sleep(5); // wait for data replication by API

    $updatedUser = $this->service->setPassword(
        $createdUser->getEmail(),
        faker()->bothify('##??##??')
    );

    assertEquals($updatedUser->getEmail(), $createdUser->getEmail());

    $this->service->destroy($updatedUser->getEmail());
});

test('suspend account', function () {
    $createdUser = $this->service->create([
        'email'     => $this->email,
        'password'  => faker()->bothify('##??##??'),
        'firstName' => faker()->firstName,
        'lastName'  => faker()->lastName
    ]);

    sleep(5); // wait for data replication by API

    $suspendedUser = $this->service->suspend($createdUser->getEmail());

    assertTrue($suspendedUser->getSuspended());

    $this->service->destroy($suspendedUser->getEmail());
});

test('activate account', function () {
    $createdUser = $this->service->create([
        'email'     => $this->email,
        'password'  => faker()->bothify('##??##??'),
        'firstName' => faker()->firstName,
        'lastName'  => faker()->lastName
    ]);

    sleep(5); // wait for data replication by API

    $activeUser = $this->service->activate($createdUser->getEmail());

    assertFalse($activeUser->getSuspended());

    $this->service->destroy($activeUser->getEmail());
});


test('delete user account', function () {
    $createdUser = $this->service->create([
        'email'     => $this->email,
        'password'  => faker()->bothify('##??##??'),
        'firstName' => faker()->firstName,
        'lastName'  => faker()->lastName
    ]);

    sleep(5); // wait for data replication by API

    $deletedUser = $this->service->destroy($createdUser->getEmail());

    assertEmpty($deletedUser);
});
