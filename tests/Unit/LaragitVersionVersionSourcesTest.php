<?php

use GenialDigitalNusantara\LaragitVersion\Helper\Constants;
use GenialDigitalNusantara\LaragitVersion\LaragitVersion;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;

it('gets version from git-local source', function () {
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_GIT_LOCAL
        ]
    ]);
    $container->instance('config', $config);
    
    $laragitVersion = new class($container) extends LaragitVersion {
        protected function shell($command): string {
            if (str_contains($command, 'git describe --tags --abbrev=0')) {
                return 'v1.0.0';
            }
            if (str_contains($command, 'git rev-parse --git-dir')) {
                return '.git';
            }
            if (str_contains($command, 'git --version')) {
                return 'git version 2.39.0';
            }
            if (str_contains($command, 'wc -l')) {
                return '1';
            }
            return '';
        }
        
        public function testGetVersion(): string {
            return $this->getVersion();
        }
    };
    
    expect($laragitVersion->testGetVersion())->toBe('v1.0.0');
});

it('gets version from git-remote source', function () {
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_GIT_REMOTE
        ]
    ]);
    $container->instance('config', $config);
    
    $laragitVersion = new class($container) extends LaragitVersion {
        protected function getVersionFromRemote(): string {
            return 'v2.0.0';
        }
        
        public function testGetVersion(): string {
            return $this->getVersion();
        }
    };
    
    expect($laragitVersion->testGetVersion())->toBe('v2.0.0');
});

it('gets version from file source', function () {
    // Create a temporary VERSION file for testing
    $tempDir = sys_get_temp_dir();
    $versionFile = $tempDir . DIRECTORY_SEPARATOR . 'VERSION';
    file_put_contents($versionFile, '3.2.1');
    
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
        
        public function testGetVersion(): string {
            return $this->getVersion();
        }
    };
    
    expect($laragitVersion->testGetVersion())->toBe('3.2.1');
    
    // Cleanup
    unlink($versionFile);
});

it('gets version from file source with complex version', function () {
    // Create a temporary VERSION file for testing
    $tempDir = sys_get_temp_dir();
    $versionFile = $tempDir . DIRECTORY_SEPARATOR . 'VERSION';
    file_put_contents($versionFile, 'v4.1.0-rc.1+build.789');
    
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
        
        public function testGetVersion(): string {
            return $this->getVersion();
        }
    };
    
    expect($laragitVersion->testGetVersion())->toBe('v4.1.0-rc.1+build.789');
    
    // Cleanup
    unlink($versionFile);
});

it('handles different version file names', function () {
    // Create a temporary custom version file for testing
    $tempDir = sys_get_temp_dir();
    $versionFile = $tempDir . DIRECTORY_SEPARATOR . 'RELEASE_VERSION';
    file_put_contents($versionFile, '2.5.8');
    
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_FILE,
            'version_file' => 'RELEASE_VERSION'
        ]
    ]);
    $container->instance('config', $config);
    
    $laragitVersion = new class($container) extends LaragitVersion {
        public function getBasePath(): string {
            return sys_get_temp_dir();
        }
        
        public function testGetVersion(): string {
            return $this->getVersion();
        }
    };
    
    expect($laragitVersion->testGetVersion())->toBe('2.5.8');
    
    // Cleanup
    unlink($versionFile);
});

it('handles version file with whitespace', function () {
    // Create a temporary VERSION file with whitespace for testing
    $tempDir = sys_get_temp_dir();
    $versionFile = $tempDir . DIRECTORY_SEPARATOR . 'VERSION';
    file_put_contents($versionFile, "  \n  1.9.5  \n  ");
    
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
        
        public function testGetVersion(): string {
            return $this->getVersion();
        }
    };
    
    expect($laragitVersion->testGetVersion())->toBe('1.9.5');
    
    // Cleanup
    unlink($versionFile);
});

it('handles git-local with multiple tags', function () {
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_GIT_LOCAL
        ]
    ]);
    $container->instance('config', $config);
    
    $laragitVersion = new class($container) extends LaragitVersion {
        protected function shell($command): string {
            if (str_contains($command, 'git describe --tags --abbrev=0')) {
                return 'v2.3.4';
            }
            if (str_contains($command, 'git rev-parse --git-dir')) {
                return '.git';
            }
            if (str_contains($command, 'git --version')) {
                return 'git version 2.39.0';
            }
            if (str_contains($command, 'wc -l')) {
                return '5';
            }
            return '';
        }
        
        public function testGetVersion(): string {
            return $this->getVersion();
        }
    };
    
    expect($laragitVersion->testGetVersion())->toBe('v2.3.4');
});

it('handles git-remote with latest tag', function () {
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_GIT_REMOTE
        ]
    ]);
    $container->instance('config', $config);
    
    $laragitVersion = new class($container) extends LaragitVersion {
        protected function getVersionFromRemote(): string {
            return 'v3.7.2';
        }
        
        public function testGetVersion(): string {
            return $this->getVersion();
        }
    };
    
    expect($laragitVersion->testGetVersion())->toBe('v3.7.2');
});

it('file source skips git validation', function () {
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
        
        public function isGitAvailable(): bool {
            return false; // Simulate git not available
        }
        
        public function isGitRepository(): bool {
            return false; // Simulate not a git repository
        }
        
        public function getCurrentVersion(): string {
            return $this->getVersion();
        }
    };
    
    // Should work even without git
    expect($laragitVersion->getCurrentVersion())->toBe('1.0.0');
    
    // Cleanup
    unlink($versionFile);
});

it('git sources require git validation', function () {
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_GIT_LOCAL
        ]
    ]);
    $container->instance('config', $config);
    
    $laragitVersion = new class($container) extends LaragitVersion {
        public function isGitAvailable(): bool {
            return true;
        }
        
        public function isGitRepository(): bool {
            return true;
        }
        
        public function hasGitTags(): bool {
            return true;
        }
        
        protected function shell($command): string {
            if (str_contains($command, 'git describe --tags --abbrev=0')) {
                return 'v1.5.0';
            }
            return '';
        }
        
        public function getCurrentVersion(): string {
            // Simulate the git validation process
            if (!$this->isGitAvailable()) {
                throw new \Exception('Git not available');
            }
            if (!$this->isGitRepository()) {
                throw new \Exception('Not a git repository');
            }
            if (!$this->hasGitTags()) {
                throw new \Exception('No git tags');
            }
            
            return $this->getVersion();
        }
    };
    
    expect($laragitVersion->getCurrentVersion())->toBe('v1.5.0');
});
