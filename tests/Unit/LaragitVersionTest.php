<?php

use GenialDigitalNusantara\LaragitVersion\Exceptions\TagNotFound;
use GenialDigitalNusantara\LaragitVersion\Helper\Constants;
use GenialDigitalNusantara\LaragitVersion\LaragitVersion;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;

describe('LaragitVersion', function () {
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

    describe('Version Formatting', function () {
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
    });

    describe('Version Information', function () {
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
            expect($versionInfo)->toHaveKeys(['version', 'commit', 'branch', 'source']);
            expect($versionInfo['source'])->toBe(Constants::VERSION_SOURCE_FILE);
            expect($versionInfo['version'])->toHaveKeys(['full', 'clean', 'major', 'minor', 'patch']);
            expect($versionInfo['version']['clean'])->toBe('1.5.0');
            
            // Cleanup
            unlink($versionFile);
        });

        it('gets current branch for file source', function () {
            $container = new Container();
            $config = new Repository([
                'version' => [
                    'source' => Constants::VERSION_SOURCE_FILE,
                    'branch' => 'development'
                ]
            ]);
            $container->instance('config', $config);
            
            $laragitVersion = new LaragitVersion($container);
            $branch = $laragitVersion->getCurrentBranch();
            
            expect($branch)->toBe('development');
        });

        it('gets commit hash for file source', function () {
            $container = new Container();
            $config = new Repository([
                'version' => [
                    'source' => Constants::VERSION_SOURCE_FILE
                ]
            ]);
            $container->instance('config', $config);
            
            $laragitVersion = new LaragitVersion($container);
            $commitHash = $laragitVersion->getCommitHash();
            
            expect($commitHash)->toBe('');
        });
    });

    describe('Error Handling', function () {
        it('handles missing version file gracefully', function () {
            $container = new Container();
            $config = new Repository([
                'version' => [
                    'source' => Constants::VERSION_SOURCE_FILE,
                    'version_file' => 'NONEXISTENT_VERSION'
                ]
            ]);
            $container->instance('config', $config);
            
            $laragitVersion = new class($container) extends LaragitVersion {
                public function getBasePath(): string {
                    return sys_get_temp_dir();
                }
                
                // Override show method to avoid facade calls
                public function show(?string $format = null): string {
                    try {
                        $version = $this->getCurrentVersion();
                        return $version;
                    } catch (TagNotFound $e) {
                        return 'No version available';
                    }
                }
                
                public function getCurrentVersion(): string {
                    $source = $this->config->get('version.source');
                    if ($source === Constants::VERSION_SOURCE_FILE) {
                        return $this->getVersion();
                    }
                    throw TagNotFound::noTagsFound();
                }
            };
            
            $result = $laragitVersion->show();
            expect($result)->toBe('No version available');
        });

        it('handles error cases in version info', function () {
            $container = new Container();
            $config = new Repository([
                'version' => [
                    'source' => Constants::VERSION_SOURCE_FILE,
                    'version_file' => 'NONEXISTENT'
                ]
            ]);
            $container->instance('config', $config);
            
            $laragitVersion = new class($container) extends LaragitVersion {
                public function getBasePath(): string {
                    return sys_get_temp_dir();
                }
                
                // Override getCurrentVersion to avoid cache facade
                public function getCurrentVersion(): string {
                    $source = $this->config->get('version.source');
                    if ($source === Constants::VERSION_SOURCE_FILE) {
                        return $this->getVersion();
                    }
                    throw TagNotFound::noTagsFound();
                }
            };
            
            $versionInfo = $laragitVersion->getVersionInfo();
            
            expect($versionInfo)->toBeArray();
            expect($versionInfo)->toHaveKey('error');
            expect($versionInfo)->toHaveKey('source');
            expect($versionInfo['source'])->toBe(Constants::VERSION_SOURCE_FILE);
        });
    });

    describe('Git Repository Operations', function () {
        it('validates repository URL functionality', function () {
            $container = new Container();
            $config = new Repository(['version' => ['source' => 'git-local']]);
            $container->instance('config', $config);
            
            $laragitVersion = new class($container) extends LaragitVersion {
                protected function shell($command): string {
                    return '';
                }
                
                public function getBasePath(): string {
                    return '/mock/path';
                }
            };
            
            // Test with empty repository URL
            $isValid = $laragitVersion->validateRemoteRepository('');
            expect($isValid)->toBeFalse();
            
            // Test with invalid repository URL
            $isValid = $laragitVersion->validateRemoteRepository('invalid-repo');
            expect($isValid)->toBeFalse();
        });

        it('can get repository URL', function () {
            $container = new Container();
            $config = new Repository(['version' => ['source' => 'git-local']]);
            $container->instance('config', $config);
            
            $laragitVersion = new class($container) extends LaragitVersion {
                protected function shell($command): string {
                    return 'https://github.com/example/repo.git';
                }
                
                public function getBasePath(): string {
                    return '/mock/path';
                }
            };
            
            $repositoryUrl = $laragitVersion->getRepositoryUrl();
            expect($repositoryUrl)->toBe('https://github.com/example/repo.git');
        });

        it('checks git availability and repository status', function () {
            $container = new Container();
            $config = new Repository(['version' => ['source' => 'git-local']]);
            $container->instance('config', $config);
            
            $laragitVersion = new class($container) extends LaragitVersion {
                protected function shell($command): string {
                    if (str_contains($command, 'git --version')) {
                        return 'git version 2.30.0';
                    }
                    if (str_contains($command, 'git rev-parse --git-dir')) {
                        return '.git';
                    }
                    if (str_contains($command, 'git tag -l | wc -l')) {
                        return '5';
                    }
                    return '';
                }
                
                public function getBasePath(): string {
                    return '/mock/path';
                }
            };
            
            // These methods should return boolean values
            $isGitAvailable = $laragitVersion->isGitAvailable();
            $isGitRepo = $laragitVersion->isGitRepository();
            $hasGitTags = $laragitVersion->hasGitTags();
            
            expect($isGitAvailable)->toBeTrue();
            expect($isGitRepo)->toBeTrue();
            expect($hasGitTags)->toBeTrue();
        });
    });
});