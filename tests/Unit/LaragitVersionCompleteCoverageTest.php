<?php

use GenialDigitalNusantara\LaragitVersion\LaragitVersion;
use GenialDigitalNusantara\LaragitVersion\Helper\Constants;
use GenialDigitalNusantara\LaragitVersion\Exceptions\TagNotFound;
use Illuminate\Container\Container;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mockery;

describe('LaragitVersion Complete Coverage Tests', function () {
    beforeEach(function () {
        Mockery::globalHelpers();
    });
    
    afterEach(function () {
        Mockery::close();
    });

    describe('Private Methods Testing via Reflection', function () {
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

        it('tests getCommitLength method via reflection', function () {
            $container = new Container();
            $config = new Repository([]);
            $container->instance('config', $config);

            $laragitVersion = new LaragitVersion($container);
            $reflection = new ReflectionClass($laragitVersion);
            $method = $reflection->getMethod('getCommitLength');
            $method->setAccessible(true);

            $result = $method->invoke($laragitVersion);
            expect($result)->toBe(6);
        });

        it('tests execShellWithProcess method via reflection', function () {
            $container = new Container();
            $config = new Repository([]);
            $container->instance('config', $config);

            $laragitVersion = new LaragitVersion($container);
            $reflection = new ReflectionClass($laragitVersion);
            $method = $reflection->getMethod('execShellWithProcess');
            $method->setAccessible(true);

            // Test with a safe command that should work on Windows
            $result = $method->invoke($laragitVersion, 'echo test', sys_get_temp_dir());
            expect($result)->toBeString();
        });

        it('tests execShellDirectly method via reflection', function () {
            $container = new Container();
            $config = new Repository([]);
            $container->instance('config', $config);

            $laragitVersion = new LaragitVersion($container);
            $reflection = new ReflectionClass($laragitVersion);
            $method = $reflection->getMethod('execShellDirectly');
            $method->setAccessible(true);

            // Test with a safe command
            $result = $method->invoke($laragitVersion, 'echo test', sys_get_temp_dir());
            expect($result)->toBeString();
        });
    });

    describe('Version Parsing and Formatting', function () {
        it('tests parseVersion method with various formats', function () {
            $container = new Container();
            $config = new Repository([]);
            $container->instance('config', $config);
            
            $laragitVersion = new LaragitVersion($container);
            $reflection = new ReflectionClass($laragitVersion);
            $method = $reflection->getMethod('parseVersion');
            $method->setAccessible(true);
            
            $testCases = [
                'v1.0.0' => ['major' => '1', 'minor' => '0', 'patch' => '0'],
                '2.1.3-alpha.1' => ['major' => '2', 'minor' => '1', 'patch' => '3', 'prerelease' => 'alpha.1'],
                '1.0.0+build.123' => ['major' => '1', 'minor' => '0', 'patch' => '0', 'buildmetadata' => 'build.123'],
                'version 3.2.1' => ['major' => '3', 'minor' => '2', 'patch' => '1'],
            ];
            
            foreach ($testCases as $input => $expectedParts) {
                $result = $method->invoke($laragitVersion, $input);
                expect($result['full'])->toBe($input);
                foreach ($expectedParts as $key => $value) {
                    expect($result[$key])->toBe($value);
                }
            }
        });

        it('tests parseVersion with invalid version format', function () {
            $container = new Container();
            $config = new Repository([]);
            $container->instance('config', $config);
            
            $laragitVersion = new LaragitVersion($container);
            $reflection = new ReflectionClass($laragitVersion);
            $method = $reflection->getMethod('parseVersion');
            $method->setAccessible(true);
            
            $result = $method->invoke($laragitVersion, 'invalid-version');
            expect($result['full'])->toBe('invalid-version');
            expect($result['clean'])->toBe('invalid-version');
            expect($result['major'])->toBe('');
            expect($result['minor'])->toBe('');
            expect($result['patch'])->toBe('');
        });
    });

    describe('Format Methods Testing', function () {
        it('tests getFullFormat method', function () {
            $container = new Container();
            $config = new Repository([]);
            $container->instance('config', $config);
            
            $laragitVersion = new LaragitVersion($container);
            $reflection = new ReflectionClass($laragitVersion);
            $method = $reflection->getMethod('getFullFormat');
            $method->setAccessible(true);
            
            $versionParts = ['clean' => '1.0.0'];
            $commit = ['short' => 'abc123'];
            
            // Test with git source
            $result = $method->invoke($laragitVersion, $versionParts, $commit, Constants::VERSION_SOURCE_GIT_LOCAL);
            expect($result)->toBe('Version 1.0.0 (commit abc123)');
            
            // Test with file source
            $result = $method->invoke($laragitVersion, $versionParts, $commit, Constants::VERSION_SOURCE_FILE);
            expect($result)->toBe('Version 1.0.0');
        });

        it('tests formatCustom method', function () {
            $container = new Container();
            $config = new Repository([]);
            $container->instance('config', $config);
            
            $laragitVersion = new LaragitVersion($container);
            $reflection = new ReflectionClass($laragitVersion);
            $method = $reflection->getMethod('formatCustom');
            $method->setAccessible(true);
            
            $versionParts = [
                'full' => 'v1.0.0',
                'clean' => '1.0.0',
                'major' => '1',
                'minor' => '0',
                'patch' => '0',
                'prerelease' => '',
                'buildmetadata' => '',
            ];
            $commit = ['short' => 'abc123'];
            $branch = 'main';
            
            $testCases = [
                '{version}' => 'v1.0.0',
                '{major}.{minor}.{patch}' => '1.0.0',
                '{commit}' => 'abc123',
                '{branch}' => 'main',
                'v{major}.{minor}' => 'v1.0',
            ];
            
            foreach ($testCases as $format => $expected) {
                $result = $method->invoke($laragitVersion, $format, $versionParts, $commit, $branch);
                expect($result)->toBe($expected);
            }
        });
    });

    describe('getCurrentVersion with Caching', function () {
        it('tests getCurrentVersion with cache hit', function () {
            Cache::shouldReceive('has')
                ->with(Constants::CACHE_KEY_VERSION)
                ->once()
                ->andReturn(true);
                
            Cache::shouldReceive('get')
                ->with(Constants::CACHE_KEY_VERSION)
                ->once()
                ->andReturn('1.0.0');
            
            $container = new Container();
            $config = new Repository([
                'version' => [
                    'source' => Constants::VERSION_SOURCE_GIT_LOCAL,
                    'format' => Constants::FORMAT_FULL,
                ]
            ]);
            $container->instance('config', $config);
            
            $laragitVersion = new LaragitVersion($container);
            $result = $laragitVersion->getCurrentVersion();
            
            expect($result)->toBe('1.0.0');
        });

        it('tests getCurrentVersion with cache miss and caching', function () {
            Cache::shouldReceive('has')
                ->with(Constants::CACHE_KEY_VERSION)
                ->once()
                ->andReturn(false);
                
            Cache::shouldReceive('put')
                ->with(Constants::CACHE_KEY_VERSION, '1.2.0', 300)
                ->once();
            
            $container = new Container();
            $config = new Repository([
                'version' => [
                    'source' => Constants::VERSION_SOURCE_GIT_LOCAL,
                    'format' => Constants::FORMAT_FULL,
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
                
                protected function getVersion(): string {
                    return '1.2.0';
                }
            };
            
            $result = $laragitVersion->getCurrentVersion();
            expect($result)->toBe('1.2.0');
        });
    });

    describe('Show Method Coverage', function () {
        it('tests show method with TagNotFound exception', function () {
            Log::shouldReceive('warning')
                ->with(Mockery::type('string'))
                ->once();
            
            $container = new Container();
            $config = new Repository([
                'version' => [
                    'source' => Constants::VERSION_SOURCE_GIT_LOCAL,
                    'format' => Constants::FORMAT_FULL,
                ]
            ]);
            $container->instance('config', $config);
            
            $laragitVersion = new class($container) extends LaragitVersion {
                public function getCurrentVersion(): string {
                    throw TagNotFound::noTagsFound();
                }
            };
            
            $result = $laragitVersion->show();
            expect($result)->toBe('No version available');
        });
    });

    describe('Error Scenarios and Edge Cases', function () {
        it('tests repository URL with empty result', function () {
            Log::shouldReceive('warning')
                ->with('No remote repository URL found')
                ->once();
            
            $container = new Container();
            $config = new Repository([]);
            $container->instance('config', $config);
            
            $laragitVersion = new class($container) extends LaragitVersion {
                protected function shell($command): string {
                    return ''; // Empty result
                }
            };
            
            $result = $laragitVersion->getRepositoryUrl();
            expect($result)->toBe('');
        });

        it('tests validateRemoteRepository with empty repository', function () {
            $container = new Container();
            $config = new Repository([]);
            $container->instance('config', $config);
            
            $laragitVersion = new LaragitVersion($container);
            $result = $laragitVersion->validateRemoteRepository('');
            expect($result)->toBeFalse();
        });

        it('tests validateRemoteRepository with fatal error', function () {
            $container = new Container();
            $config = new Repository([]);
            $container->instance('config', $config);
            
            $laragitVersion = new class($container) extends LaragitVersion {
                protected function shell($command): string {
                    return 'fatal: repository not found';
                }
            };
            
            $result = $laragitVersion->validateRemoteRepository('https://github.com/nonexistent/repo.git');
            expect($result)->toBeFalse();
        });

        it('tests getCommitHash with file source', function () {
            $container = new Container();
            $config = new Repository([
                'version' => [
                    'source' => Constants::VERSION_SOURCE_FILE,
                ]
            ]);
            $container->instance('config', $config);
            
            $laragitVersion = new LaragitVersion($container);
            $result = $laragitVersion->getCommitHash();
            expect($result)->toBe('');
        });

        it('tests getCurrentBranch with file source', function () {
            $container = new Container();
            $config = new Repository([
                'version' => [
                    'source' => Constants::VERSION_SOURCE_FILE,
                    'branch' => 'develop',
                ]
            ]);
            $container->instance('config', $config);
            
            $laragitVersion = new LaragitVersion($container);
            $result = $laragitVersion->getCurrentBranch();
            expect($result)->toBe('develop');
        });

        it('tests getCurrentBranch with file source and default branch', function () {
            $container = new Container();
            $config = new Repository([
                'version' => [
                    'source' => Constants::VERSION_SOURCE_FILE,
                ]
            ]);
            $container->instance('config', $config);
            
            $laragitVersion = new LaragitVersion($container);
            $result = $laragitVersion->getCurrentBranch();
            expect($result)->toBe(Constants::DEFAULT_BRANCH);
        });

        it('tests getVersionInfo with exception', function () {
            $app = new class extends \Illuminate\Foundation\Application {
                public function __construct() {
                    // Don't call parent constructor to avoid dependencies
                }
                
                public function basePath($path = '') {
                    return '/fake/base/path' . ($path ? '/' . $path : '');
                }
            };
            
            $config = new Repository([
                'version' => [
                    'source' => Constants::VERSION_SOURCE_FILE,
                    'version_file' => 'VERSION',
                ]
            ]);
            $app->instance('config', $config);
            
            $laragitVersion = new class($app) extends LaragitVersion {
                public function getCurrentVersion(): string {
                    throw TagNotFound::versionFileNotFound('/nonexistent/VERSION');
                }
                
                public function getBasePath(): string {
                    return '/fake/base/path';
                }
            };
            
            $result = $laragitVersion->getVersionInfo();
            expect($result)->toHaveKey('error');
            expect($result)->toHaveKey('source');
            expect($result)->toHaveKey('version_file');
            expect($result)->toHaveKey('version_file_path');
            expect($result)->toHaveKey('version_file_exists');
        });

        it('tests getVersionInfo with git source and exception', function () {
            $container = new Container();
            $config = new Repository([
                'version' => [
                    'source' => Constants::VERSION_SOURCE_GIT_LOCAL,
                ]
            ]);
            $container->instance('config', $config);
            
            $laragitVersion = new class($container) extends LaragitVersion {
                public function getCurrentVersion(): string {
                    throw TagNotFound::noTagsFound();
                }
                
                public function isGitRepository(): bool {
                    return false;
                }
            };
            
            $result = $laragitVersion->getVersionInfo();
            expect($result)->toHaveKey('error');
            expect($result)->toHaveKey('source');
            expect($result)->toHaveKey('is_git_repo');
            expect($result['is_git_repo'])->toBeFalse();
        });

        it('tests getVersionInfo successful case with file source', function () {
            $app = new class extends \Illuminate\Foundation\Application {
                public function __construct() {
                    // Don't call parent constructor to avoid dependencies
                }
                
                public function basePath($path = '') {
                    return '/fake/base/path' . ($path ? '/' . $path : '');
                }
            };
            
            $config = new Repository([
                'version' => [
                    'source' => Constants::VERSION_SOURCE_FILE,
                    'version_file' => 'VERSION',
                ]
            ]);
            $app->instance('config', $config);
            
            $laragitVersion = new class($app) extends LaragitVersion {
                public function getCurrentVersion(): string {
                    return '1.0.0';
                }
                
                public function getCommitInfo(): array {
                    return ['hash' => '', 'short' => ''];
                }
                
                public function getCurrentBranch(): string {
                    return 'main';
                }
                
                public function getBasePath(): string {
                    return '/fake/base/path';
                }
            };
            
            $result = $laragitVersion->getVersionInfo();
            expect($result)->toHaveKey('version');
            expect($result)->toHaveKey('commit');
            expect($result)->toHaveKey('branch');
            expect($result)->toHaveKey('source');
            expect($result)->toHaveKey('version_file');
            expect($result)->toHaveKey('version_file_path');
            expect($result)->toHaveKey('version_file_exists');
        });

        it('tests getVersionInfo successful case with git source', function () {
            $container = new Container();
            $config = new Repository([
                'version' => [
                    'source' => Constants::VERSION_SOURCE_GIT_LOCAL,
                ]
            ]);
            $container->instance('config', $config);
            
            $laragitVersion = new class($container) extends LaragitVersion {
                public function getCurrentVersion(): string {
                    return '1.0.0';
                }
                
                public function getCommitInfo(): array {
                    return ['hash' => 'abc123', 'short' => 'abc'];
                }
                
                public function getCurrentBranch(): string {
                    return 'main';
                }
                
                public function getRepositoryUrl(): string {
                    return 'https://github.com/example/repo.git';
                }
                
                public function isGitRepository(): bool {
                    return true;
                }
            };
            
            $result = $laragitVersion->getVersionInfo();
            expect($result)->toHaveKey('version');
            expect($result)->toHaveKey('commit');
            expect($result)->toHaveKey('branch');
            expect($result)->toHaveKey('source');
            expect($result)->toHaveKey('repository_url');
            expect($result)->toHaveKey('is_git_repo');
        });
    });
});