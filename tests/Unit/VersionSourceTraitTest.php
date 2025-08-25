<?php

use GenialDigitalNusantara\LaragitVersion\Exceptions\TagNotFound;
use GenialDigitalNusantara\LaragitVersion\Helper\Constants;
use GenialDigitalNusantara\LaragitVersion\LaragitVersion;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;

it('tests getVersion method via reflection', function () {
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_GIT_LOCAL,
        ],
    ]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        protected function shell($command): string
        {
            if (str_contains($command, 'git describe --tags --abbrev=0')) {
                return 'v1.0.0';
            }
            return '';
        }

        public function getBasePath(): string
        {
            return sys_get_temp_dir();
        }

        public function testGetVersion(): string
        {
            $reflection = new ReflectionClass($this);
            $method = $reflection->getMethod('getVersion');
            $method->setAccessible(true);
            return $method->invoke($this);
        }
    };

    expect($laragitVersion->testGetVersion())->toBe('v1.0.0');
});

it('tests getVersion method with git remote source via reflection', function () {
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_GIT_REMOTE,
        ],
    ]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        protected function getVersionFromRemote(): string
        {
            return 'v2.0.0';
        }

        public function getBasePath(): string
        {
            return sys_get_temp_dir();
        }

        public function testGetVersion(): string
        {
            $reflection = new ReflectionClass($this);
            $method = $reflection->getMethod('getVersion');
            $method->setAccessible(true);
            return $method->invoke($this);
        }
    };

    expect($laragitVersion->testGetVersion())->toBe('v2.0.0');
});

it('tests getVersion method with file source via reflection', function () {
    // Create a temporary VERSION file for testing
    $tempDir = sys_get_temp_dir();
    $versionFile = $tempDir . DIRECTORY_SEPARATOR . 'VERSION';
    file_put_contents($versionFile, '3.2.1');

    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_FILE,
            'version_file' => 'VERSION',
        ],
    ]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        public function getBasePath(): string
        {
            return sys_get_temp_dir();
        }

        public function testGetVersion(): string
        {
            $reflection = new ReflectionClass($this);
            $method = $reflection->getMethod('getVersion');
            $method->setAccessible(true);
            return $method->invoke($this);
        }
    };

    expect($laragitVersion->testGetVersion())->toBe('3.2.1');

    // Cleanup
    unlink($versionFile);
});

it('tests getVersionFromFile method via reflection', function () {
    // Create a temporary VERSION file for testing
    $tempDir = sys_get_temp_dir();
    $versionFile = $tempDir . DIRECTORY_SEPARATOR . 'VERSION';
    file_put_contents($versionFile, '1.5.0');

    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_FILE,
            'version_file' => 'VERSION',
        ],
    ]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        public function getBasePath(): string
        {
            return sys_get_temp_dir();
        }

        public function testGetVersionFromFile(): string
        {
            $reflection = new ReflectionClass($this);
            $method = $reflection->getMethod('getVersionFromFile');
            $method->setAccessible(true);
            return $method->invoke($this);
        }
    };

    expect($laragitVersion->testGetVersionFromFile())->toBe('1.5.0');

    // Cleanup
    unlink($versionFile);
});

it('tests getVersionFromFile method throws exception when file not found', function () {
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_FILE,
            'version_file' => 'NONEXISTENT_FILE',
        ],
    ]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        public function getBasePath(): string
        {
            return sys_get_temp_dir();
        }

        public function testGetVersionFromFile(): string
        {
            $reflection = new ReflectionClass($this);
            $method = $reflection->getMethod('getVersionFromFile');
            $method->setAccessible(true);
            return $method->invoke($this);
        }
    };

    expect(fn() => $laragitVersion->testGetVersionFromFile())->toThrow(TagNotFound::class);
});

it('tests getVersionFromFile method throws exception when file is empty', function () {
    // Create an empty VERSION file for testing
    $tempDir = sys_get_temp_dir();
    $versionFile = $tempDir . DIRECTORY_SEPARATOR . 'EMPTY_VERSION';
    file_put_contents($versionFile, '');

    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_FILE,
            'version_file' => 'EMPTY_VERSION',
        ],
    ]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        public function getBasePath(): string
        {
            return sys_get_temp_dir();
        }

        public function testGetVersionFromFile(): string
        {
            $reflection = new ReflectionClass($this);
            $method = $reflection->getMethod('getVersionFromFile');
            $method->setAccessible(true);
            return $method->invoke($this);
        }
    };

    expect(fn() => $laragitVersion->testGetVersionFromFile())->toThrow(TagNotFound::class);

    // Cleanup
    unlink($versionFile);
});

it('tests getVersionFromRemote method via reflection', function () {
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_GIT_REMOTE,
        ],
    ]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        protected function shell($command): string
        {
            if (str_contains($command, 'git ls-remote')) {
                return 'abc123';
            }
            return '';
        }

        public function getRepositoryUrl(): string
        {
            return 'https://github.com/test/repo.git';
        }

        public function validateRemoteRepository(string $repository): bool
        {
            return true;
        }

        public function getBasePath(): string
        {
            return sys_get_temp_dir();
        }

        public function testGetVersionFromRemote(): string
        {
            $reflection = new ReflectionClass($this);
            $method = $reflection->getMethod('getVersionFromRemote');
            $method->setAccessible(true);
            return $method->invoke($this);
        }
    };

    expect($laragitVersion->testGetVersionFromRemote())->toBe('abc123');
});

it('tests getVersionFromRemote method throws exception when remote repository is unavailable', function () {
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_GIT_REMOTE,
        ],
    ]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        public function getRepositoryUrl(): string
        {
            return 'https://github.com/test/unavailable-repo.git';
        }

        public function validateRemoteRepository(string $repository): bool
        {
            return false; // Simulate unavailable repository
        }

        public function getBasePath(): string
        {
            return sys_get_temp_dir();
        }

        public function testGetVersionFromRemote(): string
        {
            $reflection = new ReflectionClass($this);
            $method = $reflection->getMethod('getVersionFromRemote');
            $method->setAccessible(true);
            return $method->invoke($this);
        }
    };

    expect(fn() => $laragitVersion->testGetVersionFromRemote())->toThrow(TagNotFound::class);
});