<?php

use GenialDigitalNusantara\LaragitVersion\Helper\Constants;

describe('Constants', function () {
    describe('Format Constants', function () {
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

        it('defines default values', function () {
            expect(Constants::DEFAULT_FORMAT)->toBe(Constants::FORMAT_FULL);
            expect(Constants::DEFAULT_BRANCH)->toBe('main');
            expect(Constants::DEFAULT_DATETIME_FORMAT)->toBe('Y-m-d H:i');
            expect(Constants::DEFAULT_BLADE_DIRECTIVE)->toBe('laragitVersion');
        });
    });

    describe('Version Source Constants', function () {
        it('defines version source constants', function () {
            expect(Constants::VERSION_SOURCE_GIT_LOCAL)->toBe('git-local');
            expect(Constants::VERSION_SOURCE_GIT_REMOTE)->toBe('git-remote');
            expect(Constants::VERSION_SOURCE_FILE)->toBe('file');
            expect(Constants::DEFAULT_VERSION_SOURCE)->toBe(Constants::VERSION_SOURCE_GIT_LOCAL);
        });

        it('defines VERSION file constants', function () {
            expect(Constants::DEFAULT_VERSION_FILE)->toBe('VERSION');
        });
    });

    describe('Cache Constants', function () {
        it('defines cache keys', function () {
            expect(Constants::CACHE_KEY_VERSION)->toBe('laragit_version');
            expect(Constants::CACHE_KEY_COMMIT)->toBe('laragit_commit');
        });
    });

    describe('Semantic Version Pattern', function () {
        it('defines semantic version regex matcher', function () {
            expect(Constants::MATCHER)->toBeString();
            expect(Constants::MATCHER)->toContain('major');
            expect(Constants::MATCHER)->toContain('minor');
            expect(Constants::MATCHER)->toContain('patch');
        });

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
    });

    describe('Helper Methods', function () {
        it('gets all format constants', function () {
            $formats = Constants::getAllFormats();
            
            expect($formats)->toBeArray();
            expect($formats)->toHaveKey('full');
            expect($formats)->toHaveKey('compact');
            expect($formats)->toHaveKey('version');
            expect($formats)->toHaveKey('major');
            expect($formats)->toHaveKey('minor');
            expect($formats)->toHaveKey('patch');
            expect($formats['full'])->toBe('full');
            expect($formats['compact'])->toBe('compact');
            expect(count($formats))->toBe(10);
        });

        it('gets all version source constants', function () {
            $sources = Constants::getAllVersionSources();
            
            expect($sources)->toBeArray();
            expect($sources)->toHaveKey('git-local');
            expect($sources)->toHaveKey('git-remote');
            expect($sources)->toHaveKey('file');
            expect($sources['git-local'])->toBe('git-local');
            expect($sources['git-remote'])->toBe('git-remote');
            expect($sources['file'])->toBe('file');
            expect(count($sources))->toBe(3);
        });

        it('validates format correctly', function () {
            expect(Constants::isValidFormat('full'))->toBeTrue();
            expect(Constants::isValidFormat('compact'))->toBeTrue();
            expect(Constants::isValidFormat('version'))->toBeTrue();
            expect(Constants::isValidFormat('major'))->toBeTrue();
            expect(Constants::isValidFormat('minor'))->toBeTrue();
            expect(Constants::isValidFormat('patch'))->toBeTrue();
            
            expect(Constants::isValidFormat('invalid'))->toBeFalse();
            expect(Constants::isValidFormat('unknown'))->toBeFalse();
            expect(Constants::isValidFormat(''))->toBeFalse();
        });

        it('validates version source correctly', function () {
            expect(Constants::isValidVersionSource('git-local'))->toBeTrue();
            expect(Constants::isValidVersionSource('git-remote'))->toBeTrue();
            expect(Constants::isValidVersionSource('file'))->toBeTrue();
            
            expect(Constants::isValidVersionSource('invalid'))->toBeFalse();
            expect(Constants::isValidVersionSource('unknown'))->toBeFalse();
            expect(Constants::isValidVersionSource(''))->toBeFalse();
        });

        it('gets cache keys', function () {
            $cacheKeys = Constants::getCacheKeys();
            
            expect($cacheKeys)->toBeArray();
            expect($cacheKeys)->toHaveKey('version');
            expect($cacheKeys)->toHaveKey('commit');
            expect($cacheKeys['version'])->toBe('laragit_version');
            expect($cacheKeys['commit'])->toBe('laragit_commit');
            expect(count($cacheKeys))->toBe(2);
        });

        it('validates version format using matcher', function () {
            $validVersions = [
                '1.0.0',
                '1.2.3',
                '2.1.0-alpha.1',
                '1.0.0+build.123',
                '3.2.1-beta.2+build.456',
            ];

            foreach ($validVersions as $version) {
                expect(Constants::isValidVersionFormat($version))->toBeTrue("Version '$version' should be valid");
            }
            
            $invalidVersions = [
                'invalid',
                '1.x.0',
                '1.2',
                'not-a-version',
                '',
            ];

            foreach ($invalidVersions as $version) {
                expect(Constants::isValidVersionFormat($version))->toBeFalse("Version '$version' should be invalid");
            }
        });
    });
});