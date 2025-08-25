<?php

namespace GenialDigitalNusantara\LaragitVersion\Traits;

use GenialDigitalNusantara\LaragitVersion\Helper\Constants;
use Illuminate\Support\Facades\Log;

trait VersionFormattingTrait
{
    /**
     * Parse version string into components.
     *
     * @param string $version
     * @return array
     */
    protected function parseVersion(string $version): array
    {
        // Remove common prefixes
        $cleanVersion = preg_replace('/^(v|ver|version)\s*/i', '', $version);

        if (preg_match(Constants::MATCHER, $cleanVersion, $matches)) {
            return [
                'full' => $version,
                'clean' => $cleanVersion,
                'major' => $matches['major'] ?? '',
                'minor' => $matches['minor'] ?? '',
                'patch' => $matches['patch'] ?? '',
                'prerelease' => $matches['prerelease'] ?? '',
                'buildmetadata' => $matches['buildmetadata'] ?? '',
            ];
        }

        return [
            'full' => $version,
            'clean' => $cleanVersion,
            'major' => '',
            'minor' => '',
            'patch' => '',
            'prerelease' => '',
            'buildmetadata' => '',
        ];
    }

    /**
     * Get full format string based on source type.
     *
     * @param array $versionParts
     * @param array $commit
     * @param string $source
     * @return string
     */
    protected function getFullFormat(array $versionParts, array $commit, string $source): string
    {
        if ($source === Constants::VERSION_SOURCE_FILE) {
            return "Version {$versionParts['clean']}";
        }

        return "Version {$versionParts['clean']} (commit {$commit['short']})";
    }

    /**
     * Format version using custom format string.
     *
     * @param string $format
     * @param array $versionParts
     * @param array $commit
     * @param string $branch
     * @return string
     */
    protected function formatCustom(string $format, array $versionParts, array $commit, string $branch): string
    {
        $replacements = [
            '{full}' => "Version {$versionParts['clean']} (commit {$commit['short']})",
            '{compact}' => "v{$versionParts['clean']}",
            '{version}' => $versionParts['full'],
            '{version-only}' => $versionParts['clean'],
            '{major}' => $versionParts['major'],
            '{minor}' => $versionParts['minor'],
            '{patch}' => $versionParts['patch'],
            '{commit}' => $commit['short'],
            '{prerelease}' => $versionParts['prerelease'],
            '{buildmetadata}' => $versionParts['buildmetadata'],
            '{branch}' => $branch,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $format);
    }
}