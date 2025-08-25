<?php

use GenialDigitalNusantara\LaragitVersion\Facade;

it('can instantiate Facade', function () {
    $facade = new Facade();
    expect($facade)->toBeInstanceOf(Facade::class);
});

it('returns correct facade accessor', function () {
    // Test the static method directly without instantiating through facade
    $reflectionClass = new ReflectionClass(Facade::class);
    $method = $reflectionClass->getMethod('getFacadeAccessor');
    $method->setAccessible(true);

    $accessor = $method->invoke(null);
    expect($accessor)->toBe('gdn-dev.laragit-version');
});
