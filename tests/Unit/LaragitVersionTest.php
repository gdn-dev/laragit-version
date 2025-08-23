<?php

use GenialDigitalNusantara\LaragitVersion\Helper\Constants;
use GenialDigitalNusantara\LaragitVersion\LaragitVersion;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;

describe('LaragitVersion Integration Tests', function () {
    describe('End-to-End Workflow', function () {
        it('demonstrates complete file-based version workflow', function () {
            // Create a temporary VERSION file with semantic version
            $tempDir = sys_get_temp_dir();
            $versionFile = $tempDir . DIRECTORY_SEPARATOR . 'VERSION';
            file_put_contents($versionFile, 'v2.5.3-beta.1+build.123');
            
            $container = new Container();
            $config = new Repository([
                'version' => [
                    'source' => Constants::VERSION_SOURCE_FILE,
                    'version_file' => 'VERSION',
                    'format' => Constants::FORMAT_FULL,
                    'branch' => 'develop'
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
                
                public function getCommitInfo(): array {
                    return [
                        'hash' => 'abc123def456789012345678901234567890abcd',
                        'short' => 'abc123'
                    ];
                }
            };
            
            // Test complete workflow
            $version = $laragitVersion->getCurrentVersion();
            expect($version)->toBe('v2.5.3-beta.1+build.123');
            
            // Test formatted output
            expect($laragitVersion->show(Constants::FORMAT_FULL))->toBe('Version 2.5.3-beta.1+build.123');
            expect($laragitVersion->show(Constants::FORMAT_VERSION))->toBe('v2.5.3-beta.1+build.123');
            expect($laragitVersion->show(Constants::FORMAT_COMPACT))->toBe('v2.5.3-beta.1+build.123');
            expect($laragitVersion->show(Constants::FORMAT_VERSION_ONLY))->toBe('2.5.3-beta.1+build.123');
            expect($laragitVersion->show(Constants::FORMAT_MAJOR))->toBe('2');
            expect($laragitVersion->show(Constants::FORMAT_MINOR))->toBe('5');
            expect($laragitVersion->show(Constants::FORMAT_PATCH))->toBe('3');
            expect($laragitVersion->show(Constants::FORMAT_PRERELEASE))->toBe('beta.1');
            expect($laragitVersion->show(Constants::FORMAT_BUILD))->toBe('build.123');
            expect($laragitVersion->show(Constants::FORMAT_COMMIT))->toBe('abc123');
            
            // Test version info array
            $versionInfo = $laragitVersion->getVersionInfo();
            expect($versionInfo)->toBeArray();
            expect($versionInfo['source'])->toBe(Constants::VERSION_SOURCE_FILE);
            expect($versionInfo['version_file'])->toBe('VERSION');
            expect($versionInfo['version_file_exists'])->toBeTrue();
            expect($versionInfo['branch'])->toBe('develop');
            
            // Test commit info (should return mocked commit data)
            $commitInfo = $laragitVersion->getCommitInfo();
            expect($commitInfo)->toHaveKeys(['hash', 'short']);
            expect($commitInfo['hash'])->toBe('abc123def456789012345678901234567890abcd');
            expect($commitInfo['short'])->toBe('abc123');
            
            // Test branch info
            expect($laragitVersion->getCurrentBranch())->toBe('develop');
            
            // Test custom format
            $customResult = $laragitVersion->show('Version {major}.{minor}.{patch} ({prerelease})');
            expect($customResult)->toBe('Version 2.5.3 (beta.1)');
            
            // Cleanup
            unlink($versionFile);
        });
        
        it('demonstrates error handling in workflow', function () {
            $container = new Container();
            $config = new Repository([
                'version' => [
                    'source' => Constants::VERSION_SOURCE_FILE,
                    'version_file' => 'MISSING_VERSION_FILE'
                ]
            ]);
            $container->instance('config', $config);
            
            $laragitVersion = new class($container) extends LaragitVersion {
                public function getBasePath(): string {
                    return sys_get_temp_dir();
                }
                
                // Override show method to demonstrate error handling
                public function show(?string $format = null): string {
                    try {
                        $this->getCurrentVersion();
                        return 'version found';
                    } catch (\Exception $e) {
                        return 'No version available';
                    }
                }
                
                public function getCurrentVersion(): string {
                    return $this->getVersion();
                }
            };
            
            // Demonstrate graceful error handling
            $result = $laragitVersion->show();
            expect($result)->toBe('No version available');
            
            // Error info should be available in version info
            $versionInfo = $laragitVersion->getVersionInfo();
            expect($versionInfo)->toHaveKey('error');
            expect($versionInfo['version_file_exists'])->toBeFalse();
        });
    });
});