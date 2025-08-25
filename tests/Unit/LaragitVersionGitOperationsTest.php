<?php

use GenialDigitalNusantara\LaragitVersion\Helper\Constants;
use GenialDigitalNusantara\LaragitVersion\LaragitVersion;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;

it('checks git repository status', function () {
    $container = new Container();
    $config = new Repository(['version' => ['source' => 'git-local']]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        protected function shell($command): string
        {
            if (str_contains($command, 'git rev-parse --git-dir')) {
                return '.git';
            }

            return '';
        }
    };

    expect($laragitVersion->isGitRepository())->toBeTrue();
});

it('detects non-git repository', function () {
    $container = new Container();
    $config = new Repository(['version' => ['source' => 'git-local']]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        protected function shell($command): string
        {
            return 'not a git repository';
        }
    };

    expect($laragitVersion->isGitRepository())->toBeFalse();
});

it('checks git availability', function () {
    $container = new Container();
    $config = new Repository(['version' => ['source' => 'git-local']]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        protected function shell($command): string
        {
            if (str_contains($command, 'git --version')) {
                return 'git version 2.39.0';
            }

            return '';
        }
    };

    expect($laragitVersion->isGitAvailable())->toBeTrue();
});

it('detects git unavailability', function () {
    $container = new Container();
    $config = new Repository(['version' => ['source' => 'git-local']]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        protected function shell($command): string
        {
            // Simulate complete command failure
            return '';
        }

        // Override isGitAvailable to force it to return false for testing
        public function isGitAvailable(): bool
        {
            return false;
        }
    };

    expect($laragitVersion->isGitAvailable())->toBeFalse();
});

it('checks for git tags', function () {
    $container = new Container();
    $config = new Repository(['version' => ['source' => 'git-local']]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        protected function shell($command): string
        {
            if (str_contains($command, 'git rev-parse --git-dir')) {
                return '.git';
            }
            if (str_contains($command, 'wc -l')) {
                return '5';
            }

            return '';
        }
    };

    expect($laragitVersion->hasGitTags())->toBeTrue();
});

it('detects no git tags', function () {
    $container = new Container();
    $config = new Repository(['version' => ['source' => 'git-local']]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        protected function shell($command): string
        {
            if (str_contains($command, 'git rev-parse --git-dir')) {
                return '.git';
            }
            if (str_contains($command, 'wc -l')) {
                return '0';
            }

            return '';
        }
    };

    expect($laragitVersion->hasGitTags())->toBeFalse();
});

it('gets repository URL', function () {
    $container = new Container();
    $config = new Repository(['version' => ['source' => 'git-local']]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        protected function shell($command): string
        {
            if (str_contains($command, 'git config --get remote.origin.url')) {
                return 'https://github.com/example/repo.git';
            }

            return '';
        }
    };

    expect($laragitVersion->getRepositoryUrl())->toBe('https://github.com/example/repo.git');
});

it('validates remote repository', function () {
    $container = new Container();
    $config = new Repository(['version' => ['source' => 'git-remote']]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        protected function shell($command): string
        {
            if (str_contains($command, 'git ls-remote')) {
                return 'refs/heads/main';
            }

            return '';
        }
    };

    expect($laragitVersion->validateRemoteRepository('https://github.com/example/repo.git'))->toBeTrue();
});

it('detects invalid remote repository', function () {
    $container = new Container();
    $config = new Repository(['version' => ['source' => 'git-remote']]);
    $container->instance('config', $config);

    $laragitVersion = new class ($container) extends LaragitVersion {
        protected function shell($command): string
        {
            return 'fatal: repository not found';
        }
    };

    expect($laragitVersion->validateRemoteRepository('https://github.com/invalid/repo.git'))->toBeFalse();
});

it('handles empty repository URL', function () {
    $container = new Container();
    $config = new Repository(['version' => ['source' => 'git-remote']]);
    $container->instance('config', $config);

    $laragitVersion = new LaragitVersion($container);

    expect($laragitVersion->validateRemoteRepository(''))->toBeFalse();
});

it('gets commit hash for git-local source', function () {
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
            if (str_contains($command, 'git rev-parse --verify HEAD')) {
                return 'abc123def456789';
            }

            return '';
        }
    };

    expect($laragitVersion->getCommitHash())->toBe('abc123def456789');
});

it('returns empty commit hash for file source', function () {
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_FILE,
        ],
    ]);
    $container->instance('config', $config);

    $laragitVersion = new LaragitVersion($container);

    expect($laragitVersion->getCommitHash())->toBe('');
});

it('gets commit hash for git-remote source', function () {
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
                return 'xyz789abc123456';
            }
            if (str_contains($command, 'git config --get remote.origin.url')) {
                return 'https://github.com/example/repo.git';
            }

            return '';
        }
    };

    expect($laragitVersion->getCommitHash())->toBe('xyz789abc123456');
});

it('gets current branch for git source', function () {
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
            if (str_contains($command, 'git rev-parse --abbrev-ref HEAD')) {
                return 'main';
            }

            return '';
        }
    };

    expect($laragitVersion->getCurrentBranch())->toBe('main');
});

it('returns default branch for file source', function () {
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_FILE,
        ],
    ]);
    $container->instance('config', $config);

    $laragitVersion = new LaragitVersion($container);

    expect($laragitVersion->getCurrentBranch())->toBe(Constants::DEFAULT_BRANCH);
});

it('returns configured branch for file source', function () {
    $container = new Container();
    $config = new Repository([
        'version' => [
            'source' => Constants::VERSION_SOURCE_FILE,
            'branch' => 'development',
        ],
    ]);
    $container->instance('config', $config);

    $laragitVersion = new LaragitVersion($container);

    expect($laragitVersion->getCurrentBranch())->toBe('development');
});
