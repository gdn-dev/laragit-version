<?php

use GenialDigitalNusantara\LaragitVersion\Helper\Constants;
use GenialDigitalNusantara\LaragitVersion\LaragitVersion;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;

it('formats version with show method using file source', function () {
    // Create a temporary VERSION file for testing
    $tempDir = sys_get_temp_dir();
    $versionFile = $tempDir . DIRECTORY_SEPARATOR . 'VERSION';
    file_put_contents($versionFile, '1.2.3');
    
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_FILE,
            'version_file' => 'VERSION',
            'format' => Constants::FORMAT_COMPACT
        ]
    ]);
    $container->instance('config', $config);
    
    // Mock LaragitVersion to avoid Cache facade and base_path issues
    $laragitVersion = new class($container) extends LaragitVersion {
        public function getBasePath(): string {
            return sys_get_temp_dir();
        }
        
        public function getCurrentVersion(): string {
            return $this->getVersion();
        }
    };
    
    $result = $laragitVersion->show(Constants::FORMAT_COMPACT);
    expect($result)->toBe('v1.2.3');
    
    // Cleanup
    unlink($versionFile);
});

it('formats version with different formats', function () {
    // Create a temporary VERSION file for testing
    $tempDir = sys_get_temp_dir();
    $versionFile = $tempDir . DIRECTORY_SEPARATOR . 'VERSION';
    file_put_contents($versionFile, 'v2.1.0');
    
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_FILE,
            'version_file' => 'VERSION'
        ]
    ]);
    $container->instance('config', $config);
    
    $laragitVersion = new class($container) extends LaragitVersion {
        public function getBasePath(): string {
            return sys_get_temp_dir();
        }
        
        public function getCurrentVersion(): string {
            return $this->getVersion();
        }
    };
    
    // Test different formats
    expect($laragitVersion->show(Constants::FORMAT_VERSION_ONLY))->toBe('2.1.0');
    expect($laragitVersion->show(Constants::FORMAT_VERSION))->toBe('v2.1.0');
    expect($laragitVersion->show(Constants::FORMAT_MAJOR))->toBe('2');
    expect($laragitVersion->show(Constants::FORMAT_MINOR))->toBe('1');
    expect($laragitVersion->show(Constants::FORMAT_PATCH))->toBe('0');
    
    // Cleanup
    unlink($versionFile);
});

it('handles custom format strings', function () {
    // Create a temporary VERSION file for testing
    $tempDir = sys_get_temp_dir();
    $versionFile = $tempDir . DIRECTORY_SEPARATOR . 'VERSION';
    file_put_contents($versionFile, '1.0.0');
    
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_FILE,
            'version_file' => 'VERSION'
        ]
    ]);
    $container->instance('config', $config);
    
    $laragitVersion = new class($container) extends LaragitVersion {
        public function getBasePath(): string {
            return sys_get_temp_dir();
        }
        
        public function getCurrentVersion(): string {
            return $this->getVersion();
        }
    };
    
    $customFormat = 'v{major}.{minor}.{patch}';
    $result = $laragitVersion->show($customFormat);
    expect($result)->toBe('v1.0.0');
    
    // Cleanup
    unlink($versionFile);
});

it('parses complex version strings correctly', function () {
    // Create a temporary VERSION file for testing
    $tempDir = sys_get_temp_dir();
    $versionFile = $tempDir . DIRECTORY_SEPARATOR . 'VERSION';
    file_put_contents($versionFile, '2.1.0-beta.1+build.456');
    
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_FILE,
            'version_file' => 'VERSION'
        ]
    ]);
    $container->instance('config', $config);
    
    $laragitVersion = new class($container) extends LaragitVersion {
        public function getBasePath(): string {
            return sys_get_temp_dir();
        }
        
        public function getCurrentVersion(): string {
            return $this->getVersion();
        }
    };
    
    expect($laragitVersion->show(Constants::FORMAT_MAJOR))->toBe('2');
    expect($laragitVersion->show(Constants::FORMAT_MINOR))->toBe('1');
    expect($laragitVersion->show(Constants::FORMAT_PATCH))->toBe('0');
    expect($laragitVersion->show(Constants::FORMAT_PRERELEASE))->toBe('beta.1');
    expect($laragitVersion->show(Constants::FORMAT_BUILD))->toBe('build.456');
    
    // Cleanup
    unlink($versionFile);
});

it('parses semantic version correctly', function () {
    $container = new Container();
    $config = new Repository(['version' => ['source' => 'file']]);
    $container->instance('config', $config);
    
    $laragitVersion = new class($container) extends LaragitVersion {
        public function testParseVersion(string $version): array {
            return $this->parseVersion($version);
        }
    };
    
    $result = $laragitVersion->testParseVersion('v1.2.3-alpha.1+build.123');
    
    expect($result)->toBeArray();
    expect($result['major'])->toBe('1');
    expect($result['minor'])->toBe('2');
    expect($result['patch'])->toBe('3');
    expect($result['prerelease'])->toBe('alpha.1');
    expect($result['buildmetadata'])->toBe('build.123');
});

it('handles invalid version format', function () {
    $container = new Container();
    $config = new Repository(['version' => ['source' => 'file']]);
    $container->instance('config', $config);
    
    $laragitVersion = new class($container) extends LaragitVersion {
        public function testParseVersion(string $version): array {
            return $this->parseVersion($version);
        }
    };
    
    $result = $laragitVersion->testParseVersion('invalid-version');
    
    expect($result)->toBeArray();
    expect($result['major'])->toBe('');
    expect($result['minor'])->toBe('');
    expect($result['patch'])->toBe('');
});

it('gets version info as array for file source', function () {
    // Create a temporary VERSION file for testing
    $tempDir = sys_get_temp_dir();
    $versionFile = $tempDir . DIRECTORY_SEPARATOR . 'VERSION';
    file_put_contents($versionFile, '1.5.0');
    
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_FILE,
            'version_file' => 'VERSION'
        ]
    ]);
    $container->instance('config', $config);
    
    $laragitVersion = new class($container) extends LaragitVersion {
        public function getBasePath(): string {
            return sys_get_temp_dir();
        }
        
        public function getCurrentVersion(): string {
            return $this->getVersion();
        }
    };
    
    $versionInfo = $laragitVersion->getVersionInfo();
    
    expect($versionInfo)->toBeArray();
    expect($versionInfo)->toHaveKeys(['version', 'commit', 'branch', 'source', 'version_file', 'version_file_path', 'version_file_exists']);
    expect($versionInfo['source'])->toBe(Constants::VERSION_SOURCE_FILE);
    expect($versionInfo['version_file'])->toBe('VERSION');
    expect($versionInfo['version_file_exists'])->toBeTrue();
    
    // Cleanup
    unlink($versionFile);
});

it('gets version info with error when file not found', function () {
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_FILE,
            'version_file' => 'NON_EXISTENT_VERSION'
        ]
    ]);
    $container->instance('config', $config);
    
    $laragitVersion = new class($container) extends LaragitVersion {
        public function getBasePath(): string {
            return sys_get_temp_dir();
        }
        
        // Override getCurrentVersion to avoid Cache facade
        public function getCurrentVersion(): string {
            return $this->getVersion();
        }
    };
    
    $versionInfo = $laragitVersion->getVersionInfo();
    
    expect($versionInfo)->toBeArray();
    expect($versionInfo)->toHaveKey('error');
    expect($versionInfo['error'])->toContain('VERSION file not found');
    expect($versionInfo['version_file_exists'])->toBeFalse();
});
