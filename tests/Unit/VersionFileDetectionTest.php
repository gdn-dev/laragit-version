<?php

namespace Tests\Unit;

use GenialDigitalNusantara\LaragitVersion\LaragitVersion;
use Illuminate\Foundation\Application;

it('can detect version from file with laravel app', function () {
    // Create a mock Laravel application
    $app = $this->createMock(Application::class);
    $app->method('basePath')->willReturn(__DIR__);
    
    // Create a temporary VERSION file
    $versionFile = __DIR__ . DIRECTORY_SEPARATOR . 'VERSION';
    file_put_contents($versionFile, '1.2.3');
    
    $version = new LaragitVersion($app);
    $result = $version->getCurrentVersion();
    
    // Clean up
    unlink($versionFile);
    
    // Should return the version from the file
    expect($result)->toBe('1.2.3');
});

it('falls back to default when no version file exists', function () {
    // Create a mock Laravel application
    $app = $this->createMock(Application::class);
    $app->method('basePath')->willReturn(__DIR__);
    
    $version = new LaragitVersion($app);
    $result = $version->getCurrentVersion();
    
    // Should return default version when no VERSION file exists
    expect($result)->toBe('0.0.0');
});
