<?php

use GenialDigitalNusantara\LaragitVersion\LaragitVersion;

it('does not produce git errors when git is not available', function () {
    // Create a temporary directory without Git
    $tempDir = sys_get_temp_dir() . '/laragit_test_' . uniqid();
    mkdir($tempDir);

    // Set the base path to our temp directory
    define('BASE_PATH', $tempDir);

    $version = new LaragitVersion();

    // This should not produce any Git errors
    $result = $version->getCurrentVersion();

    // Should return default version
    expect($result)->toBe('0.0.0');

    // Clean up
    rmdir($tempDir);
});

it('checks git availability correctly', function () {
    $version = new LaragitVersion();

    // In most test environments, Git should be available
    // This test just ensures the method works without errors
    $isAvailable = $version->isGitAvailable();

    // Should be boolean
    expect($isAvailable)->toBeBool();
});
