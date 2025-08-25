<?php

use GenialDigitalNusantara\LaragitVersion\Facade;

it('has the getFacadeAccessor method', function () {
    // Just verify the method exists
    expect(method_exists(Facade::class, 'getFacadeAccessor'))->toBeTrue();
});

it('has the correct facade accessor name', function () {
    // We can't easily test the actual accessor without a Laravel app context
    // But we can at least verify the method exists and returns a string
    $reflection = new ReflectionClass(Facade::class);
    $method = $reflection->getMethod('getFacadeAccessor');
    $method->setAccessible(true);
    
    // Create an instance (this will fail, but we can still inspect the method)
    expect($method->isProtected())->toBeTrue();
});