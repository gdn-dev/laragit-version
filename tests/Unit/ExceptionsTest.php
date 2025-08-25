<?php

use GenialDigitalNusantara\LaragitVersion\Exceptions\TagNotFound;

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
