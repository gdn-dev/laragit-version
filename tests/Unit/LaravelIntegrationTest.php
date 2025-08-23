<?php

use GenialDigitalNusantara\LaragitVersion\ServiceProvider;
use GenialDigitalNusantara\LaragitVersion\Facade;
use Illuminate\Container\Container;

describe('Laravel Integration', function () {
    describe('ServiceProvider', function () {
        it('can instantiate ServiceProvider', function () {
            $serviceProvider = new ServiceProvider(new Container());
            expect($serviceProvider)->toBeInstanceOf(ServiceProvider::class);
        });

        it('provides correct services', function () {
            $serviceProvider = new ServiceProvider(new Container());
            $services = $serviceProvider->provides();
            
            expect($services)->toBeArray();
            expect($services)->toContain('gdn-dev.laragit-version');
        });
    });

    describe('Facade', function () {
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
    });
});