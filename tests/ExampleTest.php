<?php

use GenialDigitalNusantara\LaragitVersion\Exceptions\TagNotFound;
use GenialDigitalNusantara\LaragitVersion\Helper\Constants;
use GenialDigitalNusantara\LaragitVersion\Helper\FileCommands;
use GenialDigitalNusantara\LaragitVersion\Helper\GitCommands;

// Exception Handling Tests
it('creates specific exception for Git not installed', function () {
    $exception = TagNotFound::gitNotInstalled();
    expect($exception)->toBeInstanceOf(TagNotFound::class);
    expect($exception->getMessage())->toContain('Git is not installed');
});

it('creates specific exception for non-Git repository', function () {
    $exception = TagNotFound::notGitRepository();
    expect($exception)->toBeInstanceOf(TagNotFound::class);
    expect($exception->getMessage())->toContain('not a Git repository');
});

it('creates specific exception for no tags found', function () {
    $exception = TagNotFound::noTagsFound();
    expect($exception)->toBeInstanceOf(TagNotFound::class);
    expect($exception->getMessage())->toContain('No Git tags found');
});

it('creates specific exception for invalid tag format', function () {
    $exception = TagNotFound::invalidTagFormat('invalid-tag');
    expect($exception)->toBeInstanceOf(TagNotFound::class);
    expect($exception->getMessage())->toContain('Invalid tag format');
    expect($exception->getMessage())->toContain('invalid-tag');
});

it('creates specific exception for remote repository unavailable', function () {
    $exception = TagNotFound::remoteRepositoryUnavailable('https://github.com/invalid/repo.git');
    expect($exception)->toBeInstanceOf(TagNotFound::class);
    expect($exception->getMessage())->toContain('not accessible');
    expect($exception->getMessage())->toContain('https://github.com/invalid/repo.git');
});

// VERSION File Exception Tests
it('creates specific exception for VERSION file not found', function () {
    $exception = TagNotFound::versionFileNotFound('/path/to/VERSION');
    expect($exception)->toBeInstanceOf(TagNotFound::class);
    expect($exception->getMessage())->toContain('VERSION file not found');
    expect($exception->getMessage())->toContain('/path/to/VERSION');
});

it('creates specific exception for invalid VERSION file', function () {
    $exception = TagNotFound::invalidVersionFile('/path/to/VERSION');
    expect($exception)->toBeInstanceOf(TagNotFound::class);
    expect($exception->getMessage())->toContain('invalid content');
    expect($exception->getMessage())->toContain('/path/to/VERSION');
});

it('creates specific exception for empty VERSION file', function () {
    $exception = TagNotFound::emptyVersionFile('/path/to/VERSION');
    expect($exception)->toBeInstanceOf(TagNotFound::class);
    expect($exception->getMessage())->toContain('is empty');
    expect($exception->getMessage())->toContain('/path/to/VERSION');
});

// FileCommands Tests
it('can instantiate FileCommands class', function () {
    $fileCommands = new FileCommands();
    expect($fileCommands)->toBeInstanceOf(FileCommands::class);
});

it('can check if file exists', function () {
    $fileCommands = new FileCommands();
    $nonExistentFile = '/path/to/nonexistent/file.txt';
    expect($fileCommands->fileExists($nonExistentFile))->toBeFalse();
});

it('can get version file path', function () {
    $fileCommands = new FileCommands();
    $basePath = '/project/root';
    $fileName = 'VERSION';

    $result = $fileCommands->getVersionFilePath($basePath, $fileName);
    expect($result)->toContain('VERSION');
    expect($result)->toContain('project');
    expect($result)->toContain('root');
});

it('can parse version content with whitespace', function () {
    $fileCommands = new FileCommands();
    $content = "  1.0.0  \n";
    $result = $fileCommands->parseVersionContent($content);
    expect($result)->toBe('1.0.0');
});

// GitCommands Tests
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

it('provides remote repository validation command', function () {
    $gitCommands = new GitCommands();
    $repository = 'https://github.com/example/repo.git';
    $command = $gitCommands->validateRemoteRepository($repository);
    expect($command)->toBeString();
    expect($command)->toContain('git ls-remote --exit-code');
    expect($command)->toContain($repository);
});

// Constants Tests
it('defines all required format constants', function () {
    expect(Constants::FORMAT_FULL)->toBe('full');
    expect(Constants::FORMAT_COMPACT)->toBe('compact');
    expect(Constants::FORMAT_VERSION)->toBe('version');
    expect(Constants::FORMAT_VERSION_ONLY)->toBe('version-only');
    expect(Constants::FORMAT_MAJOR)->toBe('major');
    expect(Constants::FORMAT_MINOR)->toBe('minor');
    expect(Constants::FORMAT_PATCH)->toBe('patch');
    expect(Constants::FORMAT_COMMIT)->toBe('commit');
});

it('defines version source constants', function () {
    expect(Constants::VERSION_SOURCE_GIT_LOCAL)->toBe('git-local');
    expect(Constants::VERSION_SOURCE_GIT_REMOTE)->toBe('git-remote');
    expect(Constants::VERSION_SOURCE_FILE)->toBe('file');
    expect(Constants::DEFAULT_VERSION_SOURCE)->toBe(Constants::VERSION_SOURCE_GIT_LOCAL);
});

it('defines VERSION file constants', function () {
    expect(Constants::DEFAULT_VERSION_FILE)->toBe('VERSION');
});

it('defines default values', function () {
    expect(Constants::DEFAULT_FORMAT)->toBe(Constants::FORMAT_FULL);
    expect(Constants::DEFAULT_BRANCH)->toBe('main');
    expect(Constants::DEFAULT_DATETIME_FORMAT)->toBe('Y-m-d H:i');
    expect(Constants::DEFAULT_BLADE_DIRECTIVE)->toBe('laragitVersion');
});

it('defines cache keys', function () {
    expect(Constants::CACHE_KEY_VERSION)->toBe('laragit_version');
    expect(Constants::CACHE_KEY_COMMIT)->toBe('laragit_commit');
});

it('defines semantic version regex matcher', function () {
    expect(Constants::MATCHER)->toBeString();
    expect(Constants::MATCHER)->toContain('major');
    expect(Constants::MATCHER)->toContain('minor');
    expect(Constants::MATCHER)->toContain('patch');
});

// Version Parser Test
it('validates semantic version regex pattern', function () {
    $validVersions = [
        'v1.0.0',
        '1.2.3',
        'version 2.1.0',
        'ver 1.0.0-alpha.1',
        '1.0.0+build.123',
    ];

    foreach ($validVersions as $version) {
        $cleanVersion = preg_replace('/^(v|ver|version)\s*/i', '', $version);
        $matches = [];
        $result = preg_match(Constants::MATCHER, $cleanVersion, $matches);
        expect($result)->toBeGreaterThan(0, "Version '$version' should match the pattern");
    }
});

it('handles invalid version formats gracefully', function () {
    $invalidVersions = [
        'invalid',
        'v1.x.0',
        '1.2',
        'not-a-version',
    ];

    foreach ($invalidVersions as $version) {
        $cleanVersion = preg_replace('/^(v|ver|version)\s*/i', '', $version);
        $matches = [];
        $result = preg_match(Constants::MATCHER, $cleanVersion, $matches);
        expect($result)->toBe(0, "Version '$version' should not match the pattern");
    }
});

// Version File Content Validation Tests
it('validates version file content with basic format', function () {
    $fileCommands = new FileCommands();

    $validContents = [
        '1.0.0',
        'v2.1.3',
        '1.0.0-alpha.1',
        '2.0.0+build.123',
        'version 1.2.3',
    ];

    foreach ($validContents as $content) {
        // Create a temporary file for testing
        $tempFile = tempnam(sys_get_temp_dir(), 'version_test_');
        file_put_contents($tempFile, $content);

        $isValid = $fileCommands->isValidVersionFile($tempFile);
        expect($isValid)->toBeTrue("Content '$content' should be valid");

        unlink($tempFile);
    }
});

it('can parse multiline version content', function () {
    $fileCommands = new FileCommands();
    $content = "\n\n1.2.3\nSome other content\n";
    $result = $fileCommands->parseVersionContent($content);
    expect($result)->toBe('1.2.3');
});

it('handles version file path with Windows directory separators', function () {
    $fileCommands = new FileCommands();
    $basePath = 'C:\\project\\root';
    $fileName = 'VERSION';

    $result = $fileCommands->getVersionFilePath($basePath, $fileName);
    expect($result)->toContain('VERSION');
    expect($result)->toContain('project');
});
