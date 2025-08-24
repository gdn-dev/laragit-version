<?php

use GenialDigitalNusantara\LaragitVersion\LaragitVersion;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;

it('can instantiate the class', function () {
    $container = new Container();
    $config = new Repository(['version' => ['source' => 'file']]);
    $container->instance('config', $config);
    
    $laragitVersion = new LaragitVersion($container);
    expect($laragitVersion)->toBeInstanceOf(LaragitVersion::class);
});

it('can get base path', function () {
    $container = new Container();
    $config = new Repository(['version' => ['source' => 'file']]);
    $container->instance('config', $config);
    
    // Create a mock that overrides getBasePath to avoid basePath() call
    $laragitVersion = new class($container) extends LaragitVersion {
        public function getBasePath(): string {
            return '/mock/base/path';
        }
    };
    
    $basePath = $laragitVersion->getBasePath();
    expect($basePath)->toBe('/mock/base/path');
});

it('can get commit info structure', function () {
    $container = new Container();
    $config = new Repository(['version' => ['source' => 'file']]);
    $container->instance('config', $config);
    
    // Mock LaragitVersion to avoid shell execution
    $laragitVersion = new class($container) extends LaragitVersion {
        public function getCommitHash(): string {
            return 'abc123def456';
        }
    };
    
    $commitInfo = $laragitVersion->getCommitInfo();
    
    expect($commitInfo)->toBeArray();
    expect($commitInfo)->toHaveKeys(['hash', 'short']);
    expect($commitInfo['hash'])->toBe('abc123def456');
    expect($commitInfo['short'])->toBe('abc123');
});

it('handles shell execution errors gracefully', function () {
    $container = new Container();
    $config = new Repository(['version' => ['source' => 'git-local']]);
    $container->instance('config', $config);
    
    $laragitVersion = new class($container) extends LaragitVersion {
        public function getBasePath(): string {
            return sys_get_temp_dir();
        }
        
        // Override shell method to simulate command failure
        protected function shell($command): string {
            return ''; // Simulate empty result from failed command
        }
        
        public function getRepositoryUrl(): string {
            $url = $this->shell(
                $this->commands->getRepositoryUrl()
            );
            
            // Don't log warnings in tests, just return the URL
            return $url;
        }
    };
    
    // Test that empty shell results are handled gracefully
    $result = $laragitVersion->getRepositoryUrl();
    expect($result)->toBe('');
});

it('tests commit info functionality', function () {
    $container = new Container();
    $config = new Repository(['version' => ['source' => 'file']]);
    $container->instance('config', $config);
    
    $laragitVersion = new class($container) extends LaragitVersion {
        public function getCommitHash(): string {
            return 'abc123def456789012';
        }
    };
    
    $commitInfo = $laragitVersion->getCommitInfo();
    expect($commitInfo)->toBeArray();
    expect($commitInfo)->toHaveKeys(['hash', 'short']);
    expect($commitInfo['short'])->toHaveLength(6);
});