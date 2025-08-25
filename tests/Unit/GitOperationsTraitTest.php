<?php

use GenialDigitalNusantara\LaragitVersion\LaragitVersion;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;

it('tests cleanOutput method via reflection', function () {
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        public function getBasePath(): string
        {
            return sys_get_temp_dir();
        }
    };

    $reflection = new ReflectionClass($laragitVersion);
    $method = $reflection->getMethod('cleanOutput');
    $method->setAccessible(true);

    $testCases = [
        ["v1.0.0\n", 'v1.0.0'],
        ["  1.2.3  \n", '1.2.3'],
        ["\n\nversion\n", 'version'],
        ['clean-output', 'clean-output'],
        ["  \n  \n  ", ''],
    ];

    foreach ($testCases as [$input, $expected]) {
        $result = $method->invoke($laragitVersion, $input);
        expect($result)->toBe($expected);
    }
});

it('tests isValidPath method via reflection', function () {
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        public function getBasePath(): string
        {
            return sys_get_temp_dir();
        }
    };

    $reflection = new ReflectionClass($laragitVersion);
    $method = $reflection->getMethod('isValidPath');
    $method->setAccessible(true);

    // Test with valid path
    $result = $method->invoke($laragitVersion, sys_get_temp_dir());
    expect($result)->toBeTrue();

    // Test with invalid path
    $result = $method->invoke($laragitVersion, '/this/path/definitely/does/not/exist');
    expect($result)->toBeFalse();
});

it('tests hasErrorIndicators method via reflection', function () {
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        public function getBasePath(): string
        {
            return sys_get_temp_dir();
        }
    };

    $reflection = new ReflectionClass($laragitVersion);
    $method = $reflection->getMethod('hasErrorIndicators');
    $method->setAccessible(true);

    // Test with error indicators
    $testCases = [
        'error: something went wrong',
        'fatal: repository not found',
        'command not found',
        "'git' is not recognized as an internal or external command",
        'is not recognized as an internal or external command',
    ];

    foreach ($testCases as $testCase) {
        $result = $method->invoke($laragitVersion, $testCase);
        expect($result)->toBeTrue();
    }

    // Test with clean output
    $result = $method->invoke($laragitVersion, 'v1.0.0');
    expect($result)->toBeFalse();

    // Test with empty output
    $result = $method->invoke($laragitVersion, '');
    expect($result)->toBeFalse();
});

it('tests log methods via reflection', function () {
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        public function getBasePath(): string
        {
            return sys_get_temp_dir();
        }
    };

    $reflection = new ReflectionClass($laragitVersion);

    // Test logError method
    $method = $reflection->getMethod('logError');
    $method->setAccessible(true);
    expect(fn() => $method->invoke($laragitVersion, 'Test error message'))->not->toThrow(Exception::class);

    // Test logWarning method
    $method = $reflection->getMethod('logWarning');
    $method->setAccessible(true);
    expect(fn() => $method->invoke($laragitVersion, 'Test warning message'))->not->toThrow(Exception::class);

    // Test logDebug method
    $method = $reflection->getMethod('logDebug');
    $method->setAccessible(true);
    expect(fn() => $method->invoke($laragitVersion, 'Test debug message'))->not->toThrow(Exception::class);
});

it('tests execShellDirectly method via reflection', function () {
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        public function getBasePath(): string
        {
            return sys_get_temp_dir();
        }
    };

    $reflection = new ReflectionClass($laragitVersion);
    $method = $reflection->getMethod('execShellDirectly');
    $method->setAccessible(true);

    // Test with a simple command that should work on all systems
    $result = $method->invoke($laragitVersion, 'echo test', sys_get_temp_dir());
    expect($result)->toBeString();

    // Test with invalid path
    $result = $method->invoke($laragitVersion, 'echo test', '/invalid/path');
    expect($result)->toBeEmpty();
});

it('tests shell method with recursion prevention', function () {
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);

    // Create a mock that tracks calls to isGitRepository
    $callCount = 0;
    $laragitVersion = new class ($container) extends LaragitVersion {
        public $isGitRepositoryCallCount = 0;

        public function getBasePath(): string
        {
            return sys_get_temp_dir();
        }

        public function isGitRepository(): bool
        {
            $this->isGitRepositoryCallCount++;
            return false;
        }
    };

    $reflection = new ReflectionClass($laragitVersion);
    $method = $reflection->getMethod('shell');
    $method->setAccessible(true);

    // Test with a git command - this should trigger the recursion prevention
    $result = $method->invoke($laragitVersion, 'git --version');
    
    // Verify the result is a string
    expect($result)->toBeString();
    
    // Verify that isGitRepository was called exactly once
    expect($laragitVersion->isGitRepositoryCallCount)->toBe(1);
});

it('tests shell method with non-git command', function () {
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);

    // Create a mock that tracks calls to isGitRepository
    $callCount = 0;
    $laragitVersion = new class ($container) extends LaragitVersion {
        public $isGitRepositoryCallCount = 0;

        public function getBasePath(): string
        {
            return sys_get_temp_dir();
        }

        public function isGitRepository(): bool
        {
            $this->isGitRepositoryCallCount++;
            return false;
        }
    };

    $reflection = new ReflectionClass($laragitVersion);
    $method = $reflection->getMethod('shell');
    $method->setAccessible(true);

    // Test with a non-git command - this should not trigger isGitRepository
    $result = $method->invoke($laragitVersion, 'echo test');
    
    // Verify the result is a string
    expect($result)->toBeString();
    
    // Verify that isGitRepository was not called
    expect($laragitVersion->isGitRepositoryCallCount)->toBe(0);
});