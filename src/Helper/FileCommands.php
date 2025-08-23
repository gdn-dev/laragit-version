<?php

namespace GenialDigitalNusantara\LaragitVersion\Helper;

class FileCommands
{
    /**
     * Check if a file exists.
     *
     * @param string $filePath
     * @return bool
     */
    public function fileExists(string $filePath): bool
    {
        return file_exists($filePath);
    }

    /**
     * Check if a file is readable.
     *
     * @param string $filePath
     * @return bool
     */
    public function isReadable(string $filePath): bool
    {
        return is_readable($filePath);
    }

    /**
     * Read the contents of a file.
     *
     * @param string $filePath
     * @return string|false
     */
    public function readFile(string $filePath): string|false
    {
        if (!$this->fileExists($filePath)) {
            return false;
        }

        if (!$this->isReadable($filePath)) {
            return false;
        }

        return file_get_contents($filePath);
    }

    /**
     * Get the version from a VERSION file.
     *
     * @param string $filePath
     * @return string
     */
    public function getVersionFromFile(string $filePath): string
    {
        $content = $this->readFile($filePath);
        
        if ($content === false) {
            return '';
        }

        // Clean the version string
        return trim($content);
    }

    /**
     * Validate if the version file contains a valid version format.
     *
     * @param string $filePath
     * @return bool
     */
    public function isValidVersionFile(string $filePath): bool
    {
        $version = $this->getVersionFromFile($filePath);
        
        if (empty($version)) {
            return false;
        }

        // Basic validation - should not be empty and should be a reasonable length
        if (strlen($version) > 100) {
            return false;
        }

        // Check if it contains only valid version characters
        return preg_match('/^[a-zA-Z0-9\.\-\+\_\s]+$/', $version) === 1;
    }

    /**
     * Get the absolute path to the version file.
     *
     * @param string $basePath
     * @param string $fileName
     * @return string
     */
    public function getVersionFilePath(string $basePath, string $fileName): string
    {
        return rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($fileName, DIRECTORY_SEPARATOR);
    }

    /**
     * Parse version content and clean it for display.
     *
     * @param string $content
     * @return string
     */
    public function parseVersionContent(string $content): string
    {
        // Remove common whitespace and line breaks
        $version = trim($content);
        
        // Handle multiline files - take first non-empty line
        $lines = explode("\n", $version);
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                // Remove multiple spaces within the line
                $line = preg_replace('/\s+/', ' ', $line);
                return $line;
            }
        }
        
        // Fallback: remove multiple spaces
        $version = preg_replace('/\s+/', ' ', $version);
        
        return $version;
    }
}