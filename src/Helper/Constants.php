<?php

namespace GenialDigitalNusantara\LaragitVersion\Helper;

class Constants
{
    public const DEFAULT_FORMAT = self::FORMAT_FULL;

    public const FORMAT_FULL = 'full';

    public const FORMAT_COMPACT = 'compact';

    public const FORMAT_TIMESTAMP_YEAR = 'timestamp-year';

    public const FORMAT_TIMESTAMP_MONTH = 'timestamp-month';

    public const FORMAT_TIMESTAMP_DAY = 'timestamp-day';

    public const FORMAT_TIMESTAMP_HOUR = 'timestamp-hour';

    public const FORMAT_TIMESTAMP_MINUTE = 'timestamp-minute';

    public const FORMAT_TIMESTAMP_SECOND = 'timestamp-second';

    public const FORMAT_TIMESTAMP_TIMEZONE = 'timestamp-timezone';

    public const FORMAT_TIMESTAMP_DATETIME = 'timestamp-datetime';

    public const FORMAT_VERSION = 'version';

    public const FORMAT_VERSION_ONLY = 'version-only';

    public const FORMAT_MAJOR = 'major';

    public const FORMAT_MINOR = 'minor';

    public const FORMAT_PATCH = 'patch';

    public const FORMAT_COMMIT = 'commit';

    public const FORMAT_PRERELEASE = 'prerelease';

    public const FORMAT_BUILD = 'buildmetadata';

    public const DEFAULT_VERSION_SOURCE = self::VERSION_SOURCE_GIT_LOCAL;

    public const VERSION_SOURCE_GIT_LOCAL = 'git-local';

    public const VERSION_SOURCE_GIT_REMOTE = 'git-remote';

    public const VERSION_SOURCE_FILE = 'file';

    public const DEFAULT_VERSION_FILE = 'VERSION';

    public const DEFAULT_BLADE_DIRECTIVE = 'laragitVersion';

    public const DEFAULT_BRANCH = 'main';

    public const DEFAULT_DATETIME_FORMAT = 'Y-m-d H:i';

    public const CACHE_KEY_VERSION = 'laragit_version';

    public const CACHE_KEY_COMMIT = 'laragit_commit';

    public const MATCHER = '/^(?P<label>[v|V]*[er]*[sion]*)[\.|\s]*(?P<major>0|[1-9]\d*)\.(?P<minor>0|[1-9]\d*)\.(?P<patch>0|[1-9]\d*)(?:-(?P<prerelease>(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+(?P<buildmetadata>[0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/';

    /**
     * Get all available format constants
     *
     * @return array<string, string>
     */
    public static function getAllFormats(): array
    {
        return [
            'full' => self::FORMAT_FULL,
            'compact' => self::FORMAT_COMPACT,
            'version' => self::FORMAT_VERSION,
            'version-only' => self::FORMAT_VERSION_ONLY,
            'major' => self::FORMAT_MAJOR,
            'minor' => self::FORMAT_MINOR,
            'patch' => self::FORMAT_PATCH,
            'commit' => self::FORMAT_COMMIT,
            'prerelease' => self::FORMAT_PRERELEASE,
            'build' => self::FORMAT_BUILD,
        ];
    }

    /**
     * Get all available version source constants
     *
     * @return array<string, string>
     */
    public static function getAllVersionSources(): array
    {
        return [
            'git-local' => self::VERSION_SOURCE_GIT_LOCAL,
            'git-remote' => self::VERSION_SOURCE_GIT_REMOTE,
            'file' => self::VERSION_SOURCE_FILE,
        ];
    }

    /**
     * Check if a format is valid
     *
     * @param string $format
     * @return bool
     */
    public static function isValidFormat(string $format): bool
    {
        $validFormats = array_values(self::getAllFormats());
        return in_array($format, $validFormats, true);
    }

    /**
     * Check if a version source is valid
     *
     * @param string $source
     * @return bool
     */
    public static function isValidVersionSource(string $source): bool
    {
        $validSources = array_values(self::getAllVersionSources());
        return in_array($source, $validSources, true);
    }

    /**
     * Get cache keys
     *
     * @return array<string, string>
     */
    public static function getCacheKeys(): array
    {
        return [
            'version' => self::CACHE_KEY_VERSION,
            'commit' => self::CACHE_KEY_COMMIT,
        ];
    }

    /**
     * Validate version string against semantic version pattern
     *
     * @param string $version
     * @return bool
     */
    public static function isValidVersionFormat(string $version): bool
    {
        return preg_match(self::MATCHER, $version) === 1;
    }
}
