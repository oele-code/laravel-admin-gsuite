<?php

use function Pest\Faker\faker;
use function Tests\getEmailAccount;

use oeleco\Larasuite\Services\UserService;
use oeleco\Larasuite\Services\UserPhotoService;

beforeEach(function () {
    $this->service = app(UserPhotoService::class);
    $this->img     = "https://placekitten.com/200/300";
    $this->email    = config('gsuite.service_account_impersonate');
});

test('client will have desired scopes', function () {
    $client = $this->service->getClient();

    assertEquals($client->getScopes(), $this->service->getServiceSpecificScopes());
});

it('will have desired service', function () {
    assertInstanceOf(\Google_Service_Directory::class, $this->service->service);
});

test('get user photo', function () {
    $photoInstance   = $this->service->fetch($this->email);

    assertEquals($photoInstance->getPrimaryEmail(), $this->email);
});

test('update user photo', function () {
    $img = file_get_contents($this->img);
    list($width, $height, $type) = getimagesizefromstring($img);

    $data = [
        'width'     => $width,
        'height'    => $height,
        'mimeType'  => $type,
        'photoData' => base64_encode($img)
    ];

    $photoInstance = $this->service->update($this->email, $data);

    assertEquals(
        [
            'width'     => $photoInstance->getWidth(),
            'height'    => $photoInstance->getHeight(),
            'mimeType'  => $photoInstance->getMimeType(),
        ],
        [
            'width'    => $data['width'],
            'height'   => $data['height'],
            'mimeType' => $data['mimeType'],
        ],
    );
});

test('delete user photo', function () {
    $photoInstance = $this->service->destroy($this->email);

    assertEmpty($photoInstance);
});
