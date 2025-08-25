<?php

use GenialDigitalNusantara\LaragitVersion\LaragitVersion;
use Illuminate\Container\Container;
use Illuminate\Config\Repository;

it('tests execShellDirectly method structure', function () {
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);

    $laragitVersion = new class($container) extends LaragitVersion {
        public function getBasePath(): string {
            return '/test/path';
        }
    };

    $reflection = new ReflectionClass($laragitVersion);
    $method = $reflection->getMethod('execShellDirectly');
    $method->setAccessible(true);

    // Just test that the method exists and has the correct signature
    expect($method)->not->toBeNull();
    expect($method->isPrivate())->toBeTrue();
    expect($method->getNumberOfParameters())->toBe(2);
});

it('tests execShellWithProcess method structure', function () {
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);

    $laragitVersion = new class($container) extends LaragitVersion {
        public function getBasePath(): string {
            return '/test/path';
        }
    };

    $reflection = new ReflectionClass($laragitVersion);
    $method = $reflection->getMethod('execShellWithProcess');
    $method->setAccessible(true);

    // Just test that the method exists and has the correct signature
    expect($method)->not->toBeNull();
    expect($method->isPrivate())->toBeTrue();
    expect($method->getNumberOfParameters())->toBe(2);
});

it('tests shell method structure', function () {
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);

    $laragitVersion = new class($container) extends LaragitVersion {
        public function getBasePath(): string {
            return '/test/path';
        }
    };

    $reflection = new ReflectionClass($laragitVersion);
    $method = $reflection->getMethod('shell');
    $method->setAccessible(true);

    // Just test that the method exists and has the correct signature
    expect($method)->not->toBeNull();
    expect($method->isProtected())->toBeTrue();
    expect($method->getNumberOfParameters())->toBe(1);
});