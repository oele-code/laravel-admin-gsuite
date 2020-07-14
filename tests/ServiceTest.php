<?php

use oeleco\Larasuite\Services\Service;
use Illuminate\Contracts\Container\BindingResolutionException;

beforeEach(function () {
    $this->service = $this->getMockForAbstractClass(Service::class);
});

test('a standalone service cant be initialized', function () {
    $serviceClass = app(Service::class);
})->throws(BindingResolutionException::class);

it('will have an initialized google client', function () {
    assertInstanceOf(\Google_Client::class, $this->service->getClient());
});
