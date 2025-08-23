<?php

use GenialDigitalNusantara\LaragitVersion\Helper\GitCommands;

describe('GitCommands', function () {
    describe('Basic Git Command Generation', function () {
        it('can instantiate GitCommands class', function () {
            $gitCommands = new GitCommands();
            expect($gitCommands)->toBeInstanceOf(GitCommands::class);
        });

        it('provides Git repository check command', function () {
            $gitCommands = new GitCommands();
            $command = $gitCommands->checkGitRepository();
            expect($command)->toBeString();
            expect($command)->toContain('git rev-parse --git-dir');
        });

        it('provides Git availability check command', function () {
            $gitCommands = new GitCommands();
            $command = $gitCommands->checkGitAvailable();
            expect($command)->toBeString();
            expect($command)->toContain('git --version');
        });

        it('provides repository URL command', function () {
            $gitCommands = new GitCommands();
            $command = $gitCommands->getRepositoryUrl();
            expect($command)->toBeString();
            expect($command)->toContain('git config --get remote.origin.url');
        });

        it('provides current branch command', function () {
            $gitCommands = new GitCommands();
            $command = $gitCommands->getCurrentBranch();
            expect($command)->toBeString();
            expect($command)->toContain('git rev-parse --abbrev-ref HEAD');
        });
    });

    describe('Version and Tag Commands', function () {
        it('provides latest version command with error handling', function () {
            $gitCommands = new GitCommands();
            $command = $gitCommands->getLatestVersionOnLocal();
            expect($command)->toBeString();
            expect($command)->toContain('git describe --tags --abbrev=0');
            expect($command)->toContain('2>/dev/null'); // Error redirection
        });

        it('provides tag checking commands', function () {
            $gitCommands = new GitCommands();
            $command = $gitCommands->hasAnyTags();
            expect($command)->toBeString();
            expect($command)->toContain('git tag -l | wc -l');

            $allTagsCommand = $gitCommands->getAllTags();
            expect($allTagsCommand)->toBeString();
            expect($allTagsCommand)->toContain('git tag -l --sort=-version:refname');
        });

        it('provides current version on local command', function () {
            $gitCommands = new GitCommands();
            
            // Use reflection to test protected method
            $reflection = new ReflectionClass($gitCommands);
            $method = $reflection->getMethod('getCurrentVersionOnLocal');
            $method->setAccessible(true);
            
            $command = $method->invoke($gitCommands);
            expect($command)->toBeString();
            expect($command)->toContain('git describe --tags');
        });
    });

    describe('Commit Commands', function () {
        it('provides commit on local command', function () {
            $gitCommands = new GitCommands();
            $command = $gitCommands->getCommitOnLocal();
            expect($command)->toBeString();
            expect($command)->toContain('git rev-parse --verify HEAD');
        });
        
        it('provides selected commit on local command', function () {
            $gitCommands = new GitCommands();
            
            // Use reflection to test protected method
            $reflection = new ReflectionClass($gitCommands);
            $method = $reflection->getMethod('getSelectedCommitOnLocal');
            $method->setAccessible(true);
            
            $commit = 'abc123';
            $command = $method->invoke($gitCommands, $commit);
            expect($command)->toBeString();
            expect($command)->toContain('git rev-parse --verify');
            expect($command)->toContain($commit);
        });
    });

    describe('Remote Repository Commands', function () {
        it('provides remote repository validation command', function () {
            $gitCommands = new GitCommands();
            $repository = 'https://github.com/example/repo.git';
            $command = $gitCommands->validateRemoteRepository($repository);
            expect($command)->toBeString();
            expect($command)->toContain('git ls-remote --exit-code');
            expect($command)->toContain($repository);
        });
        
        it('provides all commit on remote command', function () {
            $gitCommands = new GitCommands();
            
            // Use reflection to test protected method
            $reflection = new ReflectionClass($gitCommands);
            $method = $reflection->getMethod('getAllCommitOnRemote');
            $method->setAccessible(true);
            
            $repository = 'https://github.com/example/repo.git';
            $command = $method->invoke($gitCommands, $repository);
            expect($command)->toBeString();
            expect($command)->toContain('git ls-remote');
            expect($command)->toContain($repository);
        });
        
        it('provides current commit on remote command', function () {
            $gitCommands = new GitCommands();
            
            // Use reflection to test protected method
            $reflection = new ReflectionClass($gitCommands);
            $method = $reflection->getMethod('getCurrentCommitOnRemote');
            $method->setAccessible(true);
            
            $repository = 'https://github.com/example/repo.git';
            $command = $method->invoke($gitCommands, $repository);
            expect($command)->toBeString();
            expect($command)->toContain('git ls-remote');
            expect($command)->toContain($repository);
            expect($command)->toContain('grep HEAD');
        });
        
        it('provides latest commit on remote command', function () {
            $gitCommands = new GitCommands();
            $repository = 'https://github.com/example/repo.git';
            $command = $gitCommands->getLatestCommitOnRemote($repository);
            expect($command)->toBeString();
            expect($command)->toContain('git ls-remote');
            expect($command)->toContain($repository);
        });
        
        it('provides latest version on remote command', function () {
            $gitCommands = new GitCommands();
            $repository = 'https://github.com/example/repo.git';
            $command = $gitCommands->getLatestVersionOnRemote($repository);
            expect($command)->toBeString();
            expect($command)->toContain('git ls-remote');
            expect($command)->toContain($repository);
            expect($command)->toContain('refs/tags/');
        });
    });

    describe('Edge Cases and Error Handling', function () {
        it('handles empty repository URL in remote operations', function () {
            $gitCommands = new GitCommands();
            
            $command = $gitCommands->validateRemoteRepository('');
            expect($command)->toBeString();
            expect($command)->toContain('git ls-remote --exit-code');
            
            $command = $gitCommands->getLatestCommitOnRemote('');
            expect($command)->toBeString();
            expect($command)->toContain('git ls-remote');
            
            $command = $gitCommands->getLatestVersionOnRemote('');
            expect($command)->toBeString();
            expect($command)->toContain('git ls-remote');
            expect($command)->toContain('refs/tags/');
        });
        
        it('generates proper git commands with error handling', function () {
            $gitCommands = new GitCommands();
            
            // Test commands that include error redirection
            $latestVersionCommand = $gitCommands->getLatestVersionOnLocal();
            expect($latestVersionCommand)->toContain('2>/dev/null');
            
            // Test tag counting command
            $hasTagsCommand = $gitCommands->hasAnyTags();
            expect($hasTagsCommand)->toContain('wc -l');
        });
        
        it('provides git commands for different scenarios', function () {
            $gitCommands = new GitCommands();
            
            // Test all public methods return non-empty strings
            expect($gitCommands->checkGitRepository())->toBeString()->not->toBeEmpty();
            expect($gitCommands->checkGitAvailable())->toBeString()->not->toBeEmpty();
            expect($gitCommands->getRepositoryUrl())->toBeString()->not->toBeEmpty();
            expect($gitCommands->getCommitOnLocal())->toBeString()->not->toBeEmpty();
            expect($gitCommands->getLatestVersionOnLocal())->toBeString()->not->toBeEmpty();
            expect($gitCommands->getAllTags())->toBeString()->not->toBeEmpty();
            expect($gitCommands->hasAnyTags())->toBeString()->not->toBeEmpty();
            expect($gitCommands->getCurrentBranch())->toBeString()->not->toBeEmpty();
            
            $repo = 'https://example.com/repo.git';
            expect($gitCommands->getLatestCommitOnRemote($repo))->toBeString()->not->toBeEmpty();
            expect($gitCommands->getLatestVersionOnRemote($repo))->toBeString()->not->toBeEmpty();
            expect($gitCommands->validateRemoteRepository($repo))->toBeString()->not->toBeEmpty();
        });
    });
});