<?php

namespace GenialDigitalNusantara\LaragitVersion\Traits;

use GenialDigitalNusantara\LaragitVersion\Exceptions\TagNotFound;
use GenialDigitalNusantara\LaragitVersion\Helper\Constants;
use Illuminate\Support\Facades\Cache;

trait VersionSourceTrait
{
    /**
     * Get version from remote Git repository.
     *
     * @return string
     */
    protected function getVersionFromRemote(): string
    {
        $repositoryUrl = $this->getRepositoryUrl();
        if (! $this->validateRemoteRepository($repositoryUrl)) {
            throw TagNotFound::remoteRepositoryUnavailable($repositoryUrl);
        }

        return $this->shell($this->commands->getLatestVersionOnRemote($repositoryUrl));
    }

    /**
     * Get version from VERSION file.
     *
     * @return string
     */
    protected function getVersionFromFile(): string
    {
        $fileName = $this->config->get('version.version_file', Constants::DEFAULT_VERSION_FILE);
        $filePath = $this->fileCommands->getVersionFilePath($this->getBasePath(), $fileName);

        if (! $this->fileCommands->fileExists($filePath)) {
            throw TagNotFound::versionFileNotFound($filePath);
        }

        if (! $this->fileCommands->isValidVersionFile($filePath)) {
            throw TagNotFound::invalidVersionFile($filePath);
        }

        $version = $this->fileCommands->getVersionFromFile($filePath);

        if (empty($version)) {
            throw TagNotFound::emptyVersionFile($filePath);
        }

        return $this->fileCommands->parseVersionContent($version);
    }

    /**
     * Get version based on source configuration.
     *
     * @return string
     */
    protected function getVersion(): string
    {
        $source = $this->config->get('version.source');

        return match ($source) {
            Constants::VERSION_SOURCE_GIT_LOCAL => $this->shell($this->commands->getLatestVersionOnLocal()),
            Constants::VERSION_SOURCE_GIT_REMOTE => $this->getVersionFromRemote(),
            Constants::VERSION_SOURCE_FILE => $this->getVersionFromFile(),
            default => $this->shell($this->commands->getLatestVersionOnLocal()),
        };
    }

    /**
     * Get the current version from Git tags or file.
     *
     * @return string
     * @throws TagNotFound
     */
    public function getCurrentVersion(): string
    {
        $cacheKey = Constants::CACHE_KEY_VERSION;

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $source = $this->config->get('version.source');

        // For file source, skip Git validation
        if ($source === Constants::VERSION_SOURCE_FILE) {
            $version = $this->getVersion();
        } else {
            // Validate Git availability and repository for Git sources
            if (! $this->isGitAvailable()) {
                throw TagNotFound::gitNotInstalled();
            }

            if (! $this->isGitRepository()) {
                throw TagNotFound::notGitRepository();
            }

            if (! $this->hasGitTags()) {
                throw TagNotFound::noTagsFound();
            }

            $version = $this->getVersion();
        }

        if (empty($version)) {
            throw TagNotFound::noTagsFound();
        }

        Cache::put($cacheKey, $version, 300); // Cache for 5 minutes

        return $version;
    }
}
