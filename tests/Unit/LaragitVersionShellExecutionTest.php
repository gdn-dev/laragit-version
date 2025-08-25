<?php

use GenialDigitalNusantara\LaragitVersion\LaragitVersion;
use GenialDigitalNusantara\LaragitVersion\Helper\Constants;
use Illuminate\Container\Container;
use Illuminate\Config\Repository;

it('tests constructor structure', function () {
    // This test verifies the constructor exists and has the expected parameters
    $reflection = new ReflectionClass(LaragitVersion::class);
    $constructor = $reflection->getConstructor();
    
    expect($constructor)->not->toBeNull();
    expect($constructor->getNumberOfParameters())->toBe(1);
    
    $parameters = $constructor->getParameters();
    expect($parameters[0]->getName())->toBe('app');
    expect($parameters[0]->getType()->getName())->toBe('Illuminate\Contracts\Container\Container');
    expect($parameters[0]->isDefaultValueAvailable())->toBeTrue();
});

it('tests method signatures', function () {
    $reflection = new ReflectionClass(LaragitVersion::class);
    
    // Test getBasePath method signature
    $method = $reflection->getMethod('getBasePath');
    expect($method->isPublic())->toBeTrue();
    expect($method->getNumberOfParameters())->toBe(0);
    expect($method->hasReturnType())->toBeTrue();
    expect($method->getReturnType()->getName())->toBe('string');
    
    // Test cleanOutput method signature
    $method = $reflection->getMethod('cleanOutput');
    expect($method->isPrivate())->toBeTrue();
    expect($method->getNumberOfParameters())->toBe(1);
    expect($method->hasReturnType())->toBeTrue();
    expect($method->getReturnType()->getName())->toBe('string');
    
    // Test getCommitLength method signature
    $method = $reflection->getMethod('getCommitLength');
    expect($method->isPrivate())->toBeTrue();
    expect($method->getNumberOfParameters())->toBe(0);
    expect($method->hasReturnType())->toBeTrue();
    expect($method->getReturnType()->getName())->toBe('int');
    
    // Test execShellWithProcess method signature
    $method = $reflection->getMethod('execShellWithProcess');
    expect($method->isPrivate())->toBeTrue();
    expect($method->getNumberOfParameters())->toBe(2);
    
    // Test execShellDirectly method signature
    $method = $reflection->getMethod('execShellDirectly');
    expect($method->isPrivate())->toBeTrue();
    expect($method->getNumberOfParameters())->toBe(2);
    
    // Test shell method signature
    $method = $reflection->getMethod('shell');
    expect($method->isProtected())->toBeTrue();
    expect($method->getNumberOfParameters())->toBe(1);
    
    // Test getRepositoryUrl method signature
    $method = $reflection->getMethod('getRepositoryUrl');
    expect($method->isPublic())->toBeTrue();
    expect($method->getNumberOfParameters())->toBe(0);
    
    // Test validateRemoteRepository method signature
    $method = $reflection->getMethod('validateRemoteRepository');
    expect($method->isPublic())->toBeTrue();
    expect($method->getNumberOfParameters())->toBe(1);
    
    // Test getCommitHash method signature
    $method = $reflection->getMethod('getCommitHash');
    expect($method->isPublic())->toBeTrue();
    expect($method->getNumberOfParameters())->toBe(0);
    
    // Test getVersion method signature
    $method = $reflection->getMethod('getVersion');
    expect($method->isProtected())->toBeTrue();
    expect($method->getNumberOfParameters())->toBe(0);
    
    // Test getVersionFromRemote method signature
    $method = $reflection->getMethod('getVersionFromRemote');
    expect($method->isProtected())->toBeTrue();
    expect($method->getNumberOfParameters())->toBe(0);
    
    // Test getVersionFromFile method signature
    $method = $reflection->getMethod('getVersionFromFile');
    expect($method->isProtected())->toBeTrue();
    expect($method->getNumberOfParameters())->toBe(0);
    
    // Test isGitRepository method signature
    $method = $reflection->getMethod('isGitRepository');
    expect($method->isPublic())->toBeTrue();
    expect($method->getNumberOfParameters())->toBe(0);
    
    // Test isGitAvailable method signature
    $method = $reflection->getMethod('isGitAvailable');
    expect($method->isPublic())->toBeTrue();
    expect($method->getNumberOfParameters())->toBe(0);
    
    // Test hasGitTags method signature
    $method = $reflection->getMethod('hasGitTags');
    expect($method->isPublic())->toBeTrue();
    expect($method->getNumberOfParameters())->toBe(0);
    
    // Test getVersionInfo method signature
    $method = $reflection->getMethod('getVersionInfo');
    expect($method->isPublic())->toBeTrue();
    expect($method->getNumberOfParameters())->toBe(0);
});

it('tests cleanOutput method logic', function () {
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);
    
    $laragitVersion = new LaragitVersion($container);
    
    // Use reflection to test the method directly
    $reflection = new ReflectionClass($laragitVersion);
    $method = $reflection->getMethod('cleanOutput');
    $method->setAccessible(true);
    
    $testCases = [
        ["v1.0.0\n", 'v1.0.0'],
        ["  1.2.3  \n", '1.2.3'],
        ["\n\nversion\n", 'version'],
        ['clean-output', 'clean-output'],
        ["  \n  test  \n  ", 'test'],
        ['', ''],
    ];

    foreach ($testCases as [$input, $expected]) {
        $result = $method->invoke($laragitVersion, $input);
        expect($result)->toBe($expected);
    }
});

it('tests getCommitLength method returns expected value', function () {
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);
    
    $laragitVersion = new LaragitVersion($container);
    
    // Use reflection to test the method directly
    $reflection = new ReflectionClass($laragitVersion);
    $method = $reflection->getMethod('getCommitLength');
    $method->setAccessible(true);

    // Test that it returns the expected value (6)
    $result = $method->invoke($laragitVersion);
    expect($result)->toBeInt();
    expect($result)->toBe(6);
});

it('tests class constants and properties', function () {
    $reflection = new ReflectionClass(LaragitVersion::class);
    
    // Test that the class has the expected properties
    $properties = $reflection->getProperties();
    $propertyNames = array_map(fn($prop) => $prop->getName(), $properties);
    
    expect($propertyNames)->toContain('app');
    expect($propertyNames)->toContain('config');
    expect($propertyNames)->toContain('commands');
    expect($propertyNames)->toContain('fileCommands');
    
    // Test property visibility
    $appProperty = $reflection->getProperty('app');
    expect($appProperty->isProtected())->toBeTrue();
    
    $configProperty = $reflection->getProperty('config');
    expect($configProperty->isProtected())->toBeTrue();
    
    $commandsProperty = $reflection->getProperty('commands');
    expect($commandsProperty->isProtected())->toBeTrue();
    
    $fileCommandsProperty = $reflection->getProperty('fileCommands');
    expect($fileCommandsProperty->isProtected())->toBeTrue();
});

it('tests that class implements expected methods', function () {
    $reflection = new ReflectionClass(LaragitVersion::class);
    $methods = $reflection->getMethods();
    $methodNames = array_map(fn($method) => $method->getName(), $methods);
    
    // Test that the class has the core public methods
    expect($methodNames)->toContain('getBasePath');
    expect($methodNames)->toContain('getRepositoryUrl');
    expect($methodNames)->toContain('getCommitHash');
    expect($methodNames)->toContain('getCurrentVersion');
    expect($methodNames)->toContain('getCurrentBranch');
    expect($methodNames)->toContain('getCommitInfo');
    expect($methodNames)->toContain('show');
    expect($methodNames)->toContain('isGitAvailable');
    expect($methodNames)->toContain('isGitRepository');
    expect($methodNames)->toContain('hasGitTags');
    expect($methodNames)->toContain('getVersionInfo');
});

it('tests parseVersion method with valid semantic versions', function () {
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);
    
    $laragitVersion = new LaragitVersion($container);
    
    // Use reflection to test the method directly
    $reflection = new ReflectionClass($laragitVersion);
    $method = $reflection->getMethod('parseVersion');
    $method->setAccessible(true);
    
    // Test various valid semantic version formats
    $testCases = [
        // Standard versions
        ['1.0.0', [
            'full' => '1.0.0',
            'clean' => '1.0.0',
            'major' => '1',
            'minor' => '0',
            'patch' => '0',
            'prerelease' => '',
            'buildmetadata' => ''
        ]],
        
        // Versions with 'v' prefix
        ['v2.1.3', [
            'full' => 'v2.1.3',
            'clean' => '2.1.3',
            'major' => '2',
            'minor' => '1',
            'patch' => '3',
            'prerelease' => '',
            'buildmetadata' => ''
        ]],
        
        // Pre-release versions
        ['1.0.0-alpha.1', [
            'full' => '1.0.0-alpha.1',
            'clean' => '1.0.0-alpha.1',
            'major' => '1',
            'minor' => '0',
            'patch' => '0',
            'prerelease' => 'alpha.1',
            'buildmetadata' => ''
        ]],
        
        // Versions with build metadata
        ['2.0.0+build.123', [
            'full' => '2.0.0+build.123',
            'clean' => '2.0.0+build.123',
            'major' => '2',
            'minor' => '0',
            'patch' => '0',
            'prerelease' => '',
            'buildmetadata' => 'build.123'
        ]],
        
        // Complex versions with both pre-release and build metadata
        ['1.0.0-beta.2+exp.sha.5114f85', [
            'full' => '1.0.0-beta.2+exp.sha.5114f85',
            'clean' => '1.0.0-beta.2+exp.sha.5114f85',
            'major' => '1',
            'minor' => '0',
            'patch' => '0',
            'prerelease' => 'beta.2',
            'buildmetadata' => 'exp.sha.5114f85'
        ]],
    ];

    foreach ($testCases as [$input, $expected]) {
        $result = $method->invoke($laragitVersion, $input);
        expect($result)->toBe($expected);
    }
});

it('tests parseVersion method with invalid versions', function () {
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);
    
    $laragitVersion = new LaragitVersion($container);
    
    // Use reflection to test the method directly
    $reflection = new ReflectionClass($laragitVersion);
    $method = $reflection->getMethod('parseVersion');
    $method->setAccessible(true);
    
    // Test invalid version formats
    $testCases = [
        // Completely invalid
        ['not-a-version', [
            'full' => 'not-a-version',
            'clean' => 'not-a-version', // The clean method doesn't remove anything for invalid formats
            'major' => '',
            'minor' => '',
            'patch' => '',
            'prerelease' => '',
            'buildmetadata' => ''
        ]],
        
        // Empty string
        ['', [
            'full' => '',
            'clean' => '',
            'major' => '',
            'minor' => '',
            'patch' => '',
            'prerelease' => '',
            'buildmetadata' => ''
        ]],
        
        // Only text (this will be cleaned but still not match the regex)
        ['version', [
            'full' => 'version',
            'clean' => 'ersion', // 'version' with 'v' prefix removed
            'major' => '',
            'minor' => '',
            'patch' => '',
            'prerelease' => '',
            'buildmetadata' => ''
        ]],
    ];

    foreach ($testCases as [$input, $expected]) {
        $result = $method->invoke($laragitVersion, $input);
        expect($result)->toBe($expected);
    }
});

it('tests getFullFormat method', function () {
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);
    
    $laragitVersion = new LaragitVersion($container);
    
    // Use reflection to test the method directly
    $reflection = new ReflectionClass($laragitVersion);
    $method = $reflection->getMethod('getFullFormat');
    $method->setAccessible(true);
    
    // Test with git source
    $versionParts = [
        'clean' => '1.0.0'
    ];
    $commit = [
        'short' => 'abc123'
    ];
    $source = Constants::VERSION_SOURCE_GIT_LOCAL;
    
    $result = $method->invoke($laragitVersion, $versionParts, $commit, $source);
    expect($result)->toBe('Version 1.0.0 (commit abc123)');
    
    // Test with file source
    $source = Constants::VERSION_SOURCE_FILE;
    $result = $method->invoke($laragitVersion, $versionParts, $commit, $source);
    expect($result)->toBe('Version 1.0.0');
});

it('tests formatCustom method', function () {
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);
    
    $laragitVersion = new LaragitVersion($container);
    
    // Use reflection to test the method directly
    $reflection = new ReflectionClass($laragitVersion);
    $method = $reflection->getMethod('formatCustom');
    $method->setAccessible(true);
    
    // Test data
    $versionParts = [
        'full' => 'v1.0.0',
        'clean' => '1.0.0',
        'major' => '1',
        'minor' => '0',
        'patch' => '0',
        'prerelease' => 'beta.1',
        'buildmetadata' => 'build.123'
    ];
    $commit = [
        'short' => 'abc123'
    ];
    $branch = 'main';
    
    // Test various format strings
    $testCases = [
        ['Version {version-only}', 'Version 1.0.0'],
        ['{compact}', 'v1.0.0'],
        ['{major}.{minor}.{patch}', '1.0.0'],
        ['Commit: {commit}', 'Commit: abc123'],
        ['Branch: {branch}', 'Branch: main'],
        ['Pre-release: {prerelease}', 'Pre-release: beta.1'],
        ['Build: {buildmetadata}', 'Build: build.123'],
        ['Full version: {version}', 'Full version: v1.0.0'],
        ['Complex: {version} on {branch} ({commit})', 'Complex: v1.0.0 on main (abc123)'],
    ];

    foreach ($testCases as [$format, $expected]) {
        $result = $method->invoke($laragitVersion, $format, $versionParts, $commit, $branch);
        expect($result)->toBe($expected);
    }
});

it('tests validateRemoteRepository method with empty repository', function () {
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);
    
    $laragitVersion = new LaragitVersion($container);
    
    // Use reflection to test the method directly
    $reflection = new ReflectionClass($laragitVersion);
    $method = $reflection->getMethod('validateRemoteRepository');
    $method->setAccessible(true);
    
    // Test with empty repository
    $result = $method->invoke($laragitVersion, '');
    expect($result)->toBeFalse();
});

it('tests getCommitInfo method structure', function () {
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);
    
    // Create a mock that overrides getCommitHash to return a specific value
    $laragitVersion = new class($container) extends LaragitVersion {
        public function getCommitHash(): string {
            return 'a1b2c3d4e5f67890';
        }
        
        // Override getCommitLength to return a specific value for testing
        private function getCommitLength(): int {
            return 6;
        }
    };
    
    $commitInfo = $laragitVersion->getCommitInfo();
    
    expect($commitInfo)->toBeArray();
    expect($commitInfo)->toHaveKeys(['hash', 'short']);
    expect($commitInfo['hash'])->toBe('a1b2c3d4e5f67890');
    expect($commitInfo['short'])->toBe('a1b2c3');
});

it('tests getVersionInfo method structure', function () {
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_FILE,
        ]
    ]);
    $container->instance('config', $config);
    
    // Create a mock that overrides methods to avoid shell execution and Cache facade
    $laragitVersion = new class($container) extends LaragitVersion {
        public function getBasePath(): string {
            return '/mock/base/path';
        }
        
        protected function shell($command): string {
            // Return empty string to simulate no git repository
            return '';
        }
        
        public function isGitRepository(): bool {
            return false;
        }
        
        protected function getVersion(): string {
            return '1.0.0';
        }
        
        public function getCommitHash(): string {
            return 'a1b2c3d4e5f67890';
        }
        
        private function getCommitLength(): int {
            return 6;
        }
        
        public function getCurrentBranch(): string {
            return 'main';
        }
        
        // Override getCurrentVersion to avoid Cache facade
        public function getCurrentVersion(): string {
            return $this->getVersion();
        }
    };
    
    $versionInfo = $laragitVersion->getVersionInfo();
    
    expect($versionInfo)->toBeArray();
    expect($versionInfo)->toHaveKey('version');
    expect($versionInfo)->toHaveKey('commit');
    expect($versionInfo)->toHaveKey('branch');
    expect($versionInfo)->toHaveKey('source');
});