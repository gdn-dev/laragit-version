<?php

namespace GenialDigitalNusantara\LaragitVersion;

class LaragitVersion
{
    /**
     * Get the current version using a simple approach.
     *
     * @return string
     */
    public function show(?string $format = null): string
    {
        $version = $this->getCurrentVersion();

        if ($format === 'compact') {
            return "v{$version}";
        }

        return "Version {$version}";
    }

    /**
     * Get the current version - simplified version.
     *
     * @return string
     */
    public function getCurrentVersion(): string
    {
        // Simple implementation like the user's custom solution
        $basePath = $this->getBasePath();
        $versionFile = $basePath . DIRECTORY_SEPARATOR . 'VERSION';

        if (file_exists($versionFile)) {
            return trim(file_get_contents($versionFile));
        }

        // Only try Git if it's available
        if ($this->isGitAvailable()) {
            $version = trim(@exec('git describe --tags --abbrev=0 2>/dev/null'));
            if (!empty($version)) {
                return $version;
            }
        }

        // Return a default if no version found
        return '0.0.0';
    }

    /**
     * Get version information as array.
     *
     * @return array
     */
    public function getVersionInfo(): array
    {
        $version = $this->getCurrentVersion();

        return [
            'version' => $version,
            'formatted' => $this->show(),
            'compact' => $this->show('compact'),
        ];
    }

    /**
     * Check if Git is available.
     *
     * @return bool
     */
    public function isGitAvailable(): bool
    {
        // Suppress errors and redirect stderr to prevent logs
        $output = trim(@exec('git --version 2>/dev/null'));

        return ! empty($output) && strpos($output, 'git version') !== false;
    }

    /**
     * Get the base path for the application.
     *
     * @return string
     */
    protected function getBasePath(): string
    {
        // In a Laravel application, this would be the base path
        // For testing, we'll use a reasonable default
        return defined('BASE_PATH') ? BASE_PATH : getcwd();
    }
}