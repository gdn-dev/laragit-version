<?php

use GenialDigitalNusantara\LaragitVersion\Facade;
use GenialDigitalNusantara\LaragitVersion\LaragitVersion;
use Illuminate\Support\Facades\Facade as IlluminateFacade;

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
    
    // Test that the method is protected
    expect($method->isProtected())->toBeTrue();
});

it('returns the correct facade accessor via reflection', function () {
    // Test that the facade returns the correct accessor string using reflection
    $reflection = new ReflectionClass(Facade::class);
    $method = $reflection->getMethod('getFacadeAccessor');
    $method->setAccessible(true);
    
    // Create a new instance of the facade
    $facade = new Facade();
    
    // Call the protected method
    $accessor = $method->invoke($facade);
    
    expect($accessor)->toBeString();
    expect($accessor)->toBe('gdn-dev.laragit-version');
});

it('extends the correct base class', function () {
    // Test that the facade extends the correct base class
    expect(Facade::class)->toExtend(IlluminateFacade::class);
});