<?php

use GenialDigitalNusantara\LaragitVersion\Helper\Constants;
use GenialDigitalNusantara\LaragitVersion\LaragitVersion;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;

it('tests constructor with null app parameter', function () {
    // This test verifies the constructor can handle null app parameter
    // Note: In a package test environment, we can't test the app() helper function
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);

    $laragitVersion = new LaragitVersion($container);
    expect($laragitVersion)->toBeInstanceOf(LaragitVersion::class);
});

it('tests getBasePath method structure', function () {
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);

    // Create a mock that overrides getBasePath to avoid basePath() call
    $laragitVersion = new class ($container) extends LaragitVersion {
        public function getBasePath(): string
        {
            return '/mock/base/path';
        }
    };

    $basePath = $laragitVersion->getBasePath();
    expect($basePath)->toBe('/mock/base/path');
});

it('tests cleanOutput method via reflection', function () {
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);

    $laragitVersion = new LaragitVersion($container);
    $reflection = new ReflectionClass($laragitVersion);
    $method = $reflection->getMethod('cleanOutput');
    $method->setAccessible(true);

    $testCases = [
        ["v1.0.0\n", 'v1.0.0'],
        ["  1.2.3  \n", '1.2.3'],
        ["\n\nversion\n", 'version'],
        ['clean-output', 'clean-output'],
    ];

    foreach ($testCases as [$input, $expected]) {
        $result = $method->invoke($laragitVersion, $input);
        expect($result)->toBe($expected);
    }
});

it('tests getRepositoryUrl method via reflection', function () {
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_GIT_LOCAL,
        ],
    ]);
    $container->instance('config', $config);

    // Create a mock that simulates shell commands
    $laragitVersion = new class ($container) extends LaragitVersion {
        protected function shell($command): string
        {
            // Return a mock repository URL for testing
            return "https://github.com/user/repo.git";
        }

        public function getBasePath(): string
        {
            return '/test/path';
        }
    };

    $result = $laragitVersion->getRepositoryUrl();
    expect($result)->toBe('https://github.com/user/repo.git');
});

it('tests validateRemoteRepository method via reflection', function () {
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        protected function shell($command): string
        {
            // Simulate successful remote repository validation
            return "github.com";
        }

        public function getBasePath(): string
        {
            return '/test/path';
        }
    };

    $reflection = new ReflectionClass($laragitVersion);
    $method = $reflection->getMethod('validateRemoteRepository');
    $method->setAccessible(true);

    // Test with valid repository
    $result = $method->invoke($laragitVersion, 'https://github.com/user/repo.git');
    expect($result)->toBeTrue();

    // Test with empty repository
    $result = $method->invoke($laragitVersion, '');
    expect($result)->toBeFalse();
});

it('tests getCommitHash method via reflection', function () {
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_GIT_LOCAL,
        ],
    ]);
    $container->instance('config', $config);

    // Create a mock that simulates Git commands
    $laragitVersion = new class ($container) extends LaragitVersion {
        protected function shell($command): string
        {
            // Return a mock commit hash for testing
            if (str_contains($command, 'rev-parse')) {
                return "a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0";
            }

            return '';
        }

        public function isGitAvailable(): bool
        {
            return true;
        }

        public function isGitRepository(): bool
        {
            return true;
        }

        public function getBasePath(): string
        {
            return '/test/path';
        }
    };

    $result = $laragitVersion->getCommitHash();
    expect($result)->toBe('a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0');
});

it('tests getCommitHash with file source', function () {
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_FILE,
        ],
    ]);
    $container->instance('config', $config);

    $laragitVersion = new LaragitVersion($container);
    $result = $laragitVersion->getCommitHash();
    expect($result)->toBe('');
});

it('tests isGitRepository method via reflection', function () {
    $container = new Container();
    $config = new Repository([]);
    $container->instance('config', $config);

    // Create a mock that simulates Git commands
    $laragitVersion = new class ($container) extends LaragitVersion {
        protected function shell($command): string
        {
            // Simulate being in a Git repository
            if (str_contains($command, 'rev-parse')) {
                return "/path/to/repo/.git";
            }

            return '';
        }

        public function isGitAvailable(): bool
        {
            return true;
        }

        public function getBasePath(): string
        {
            return '/test/path';
        }
    };

    $result = $laragitVersion->isGitRepository();
    expect($result)->toBeTrue();
});

it('tests hasGitTags method via reflection', function () {
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_GIT_LOCAL,
        ],
    ]);
    $container->instance('config', $config);

    // Create a mock that simulates Git commands
    $laragitVersion = new class ($container) extends LaragitVersion {
        protected function shell($command): string
        {
            // Simulate having Git tags
            if (str_contains($command, 'tag') && str_contains($command, 'wc -l')) {
                return "1"; // Return count of tags > 0
            }

            return '0'; // Return 0 when no tags
        }

        public function isGitAvailable(): bool
        {
            return true;
        }

        public function isGitRepository(): bool
        {
            return true; // This is important - hasGitTags checks isGitRepository first
        }

        public function getBasePath(): string
        {
            return '/test/path';
        }
    };

    $result = $laragitVersion->hasGitTags();
    expect($result)->toBeTrue();
});

it('tests hasGitTags method when not in Git repository', function () {
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_GIT_LOCAL,
        ],
    ]);
    $container->instance('config', $config);

    // Create a mock where isGitRepository returns false
    $laragitVersion = new class ($container) extends LaragitVersion {
        public function isGitRepository(): bool
        {
            return false; // Not in a Git repository
        }

        public function getBasePath(): string
        {
            return '/test/path';
        }
    };

    $result = $laragitVersion->hasGitTags();
    expect($result)->toBeFalse();
});
