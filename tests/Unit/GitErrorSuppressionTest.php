<?php

namespace Tests\Unit;

use GenialDigitalNusantara\LaragitVersion\LaragitVersion;
use PHPUnit\Framework\TestCase;

uses(TestCase::class);

class MockLaragitVersion extends LaragitVersion
{
    private bool $gitAvailable;

    public function __construct(bool $gitAvailable = false)
    {
        parent::__construct();
        $this->gitAvailable = $gitAvailable;
    }

    public function isGitAvailable(): bool
    {
        return $this->gitAvailable;
    }

    protected function getBasePath(): string
    {
        // Return a temporary directory that doesn't have Git
        return sys_get_temp_dir() . '/laragit_test_' . uniqid();
    }
}

it('does not produce git errors when git is not available', function () {
    // Create a mock version instance where Git is not available
    $version = new MockLaragitVersion(false);

    // This should not produce any Git errors
    $result = $version->getCurrentVersion();

    // Should return default version
    expect($result)->toBe('0.0.0');
});

it('checks git availability correctly', function () {
    $version = new LaragitVersion();

    // In most test environments, Git should be available
    // This test just ensures the method works without errors
    $isAvailable = $version->isGitAvailable();

    // Should be boolean
    expect($isAvailable)->toBeBool();
});