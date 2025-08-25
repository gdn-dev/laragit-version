<?php

namespace GenialDigitalNusantara\LaragitVersion;

use GenialDigitalNusantara\LaragitVersion\Exceptions\TagNotFound;
use GenialDigitalNusantara\LaragitVersion\Helper\Constants;
use GenialDigitalNusantara\LaragitVersion\Helper\FileCommands;
use GenialDigitalNusantara\LaragitVersion\Helper\GitCommands;
use GenialDigitalNusantara\LaragitVersion\Traits\GitOperationsTrait;
use GenialDigitalNusantara\LaragitVersion\Traits\VersionFormattingTrait;
use GenialDigitalNusantara\LaragitVersion\Traits\VersionSourceTrait;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Throwable;

class LaragitVersion
{
    use GitOperationsTrait;
    use VersionFormattingTrait;
    use VersionSourceTrait;

    /**
     * The Laravel application instance.
     *
     * @var Container
     */
    protected Container $app;

    /** @var Repository */
    protected Repository $config;

    /** @var GitCommands */
    protected GitCommands $commands;

    /** @var FileCommands */
    protected FileCommands $fileCommands;

    /**
     * @param Container|null $app
     */
    public function __construct(?Container $app = null)
    {
        if (! $app) {
            $app = app();
        }
        $this->app = $app;
        $this->config = $app['config'];
        $this->commands = new GitCommands();
        $this->fileCommands = new FileCommands();
    }

    /**
     * Get the current git root path.
     *
     * @return string
     */
    public function getBasePath(): string
    {
        return base_path();
    }

    private function getCommitLength(): int
    {
        return 6;
    }

    public function getRepositoryUrl(): string
    {
        $url = $this->shell(
            $this->commands->getRepositoryUrl()
        );

        if (empty($url)) {
            Log::warning('No remote repository URL found');
        }

        return $url;
    }

    /**
     * Validate remote repository accessibility.
     *
     * @param string $repository
     * @return bool
     */
    public function validateRemoteRepository(string $repository): bool
    {
        if (empty($repository)) {
            return false;
        }

        $result = $this->shell($this->commands->validateRemoteRepository($repository));

        return ! empty($result) && ! str_contains($result, 'fatal');
    }

    public function getCommitHash(): string
    {
        $source = $this->config->get('version.source');

        // For file source, return empty string since there's no commit hash
        if ($source === Constants::VERSION_SOURCE_FILE) {
            return '';
        }

        return $source === Constants::VERSION_SOURCE_GIT_LOCAL ?
            $this->shell($this->commands->getCommitOnLocal()) :
            $this->shell($this->commands->getLatestCommitOnRemote($this->getRepositoryUrl()));
    }

    /**
     * Get the current Git branch.
     *
     * @return string
     */
    public function getCurrentBranch(): string
    {
        $source = $this->config->get('version.source');

        // For file source, return configured branch or default
        if ($source === Constants::VERSION_SOURCE_FILE) {
            return $this->config->get('version.branch', Constants::DEFAULT_BRANCH);
        }

        return $this->shell($this->commands->getCurrentBranch());
    }

    /**
     * Get commit information.
     *
     * @return array
     */
    public function getCommitInfo(): array
    {
        $hash = $this->getCommitHash();
        $shortHash = substr($hash, 0, $this->getCommitLength());

        return [
            'hash' => $hash,
            'short' => $shortHash,
        ];
    }

    /**
     * Format version string according to the specified format.
     *
     * @param string|null $format
     * @return string
     */
    public function show(?string $format = null): string
    {
        $format = $format ?? $this->config->get('version.format', Constants::DEFAULT_FORMAT);

        try {
            $version = $this->getCurrentVersion();
            $commit = $this->getCommitInfo();
            $branch = $this->getCurrentBranch();
            $versionParts = $this->parseVersion($version);
            $source = $this->config->get('version.source');

            return match ($format) {
                Constants::FORMAT_FULL => $this->getFullFormat($versionParts, $commit, $source),
                Constants::FORMAT_COMPACT => "v{$versionParts['clean']}",
                Constants::FORMAT_VERSION => $versionParts['full'],
                Constants::FORMAT_VERSION_ONLY => $versionParts['clean'],
                Constants::FORMAT_MAJOR => $versionParts['major'],
                Constants::FORMAT_MINOR => $versionParts['minor'],
                Constants::FORMAT_PATCH => $versionParts['patch'],
                Constants::FORMAT_COMMIT => $commit['short'],
                Constants::FORMAT_PRERELEASE => $versionParts['prerelease'],
                Constants::FORMAT_BUILD => $versionParts['buildmetadata'],
                default => $this->formatCustom($format, $versionParts, $commit, $branch),
            };
        } catch (TagNotFound $e) {
            Log::warning('Version not found: ' . $e->getMessage());

            return 'No version available';
        }
    }

    /**
     * Check if Git repository exists and is valid.
     *
     * @return bool
     */
    public function isGitRepository(): bool
    {
        $result = $this->shell($this->commands->checkGitRepository());

        return ! empty($result) && ! str_contains($result, 'not a git repository');
    }

    /**
     * Check if Git is available on the system.
     *
     * @return bool
     */
    public function isGitAvailable(): bool
    {
        // First try using shell_exec if available
        if (function_exists('shell_exec')) {
            // Use a simple command that should work on all systems
            $output = shell_exec('git --version 2>&1');

            // On Windows, shell_exec might return null or false even when the command works
            if ($output === null || $output === false) {
                // Try alternative method
                $output = $this->execShellDirectly($this->commands->checkGitAvailable(), $this->getBasePath());
            }

            if (! empty($output) && str_contains($output, 'git version')) {
                return true;
            }
        }

        // Fallback to Symfony Process if available
        if (class_exists('\Symfony\Component\Process\Process')) {
            try {
                $process = Process::fromShellCommandline($this->commands->checkGitAvailable(), $this->getBasePath());
                $process->run();

                if ($process->isSuccessful() && str_contains($process->getOutput(), 'git version')) {
                    return true;
                }
            } catch (Throwable $e) {
                Log::warning("isGitAvailable(): Symfony Process failed - " . $e->getMessage());
            }
        }

        // If we reach here, Git is not available or not accessible
        Log::warning("isGitAvailable(): Git is not available or not in system PATH");

        return false;
    }

    /**
     * Check if repository has any tags.
     *
     * @return bool
     */
    public function hasGitTags(): bool
    {
        if (! $this->isGitRepository()) {
            return false;
        }

        $result = $this->shell($this->commands->hasAnyTags());

        return ! empty($result) && intval(trim($result)) > 0;
    }

    /**
     * Get version information as array.
     *
     * @return array
     */
    public function getVersionInfo(): array
    {
        try {
            $version = $this->getCurrentVersion();
            $commit = $this->getCommitInfo();
            $branch = $this->getCurrentBranch();
            $versionParts = $this->parseVersion($version);
            $source = $this->config->get('version.source');

            $info = [
                'version' => $versionParts,
                'commit' => $commit,
                'branch' => $branch,
                'source' => $source,
            ];

            // Add source-specific information
            if ($source === Constants::VERSION_SOURCE_FILE) {
                $fileName = $this->config->get('version.version_file', Constants::DEFAULT_VERSION_FILE);
                $filePath = $this->fileCommands->getVersionFilePath($this->getBasePath(), $fileName);
                $info['version_file'] = $fileName;
                $info['version_file_path'] = $filePath;
                $info['version_file_exists'] = $this->fileCommands->fileExists($filePath);
            } else {
                $info['repository_url'] = $this->getRepositoryUrl();
                $info['is_git_repo'] = $this->isGitRepository();
            }

            return $info;
        } catch (TagNotFound $e) {
            $source = $this->config->get('version.source');
            $info = [
                'error' => $e->getMessage(),
                'source' => $source,
            ];

            if ($source === Constants::VERSION_SOURCE_FILE) {
                $fileName = $this->config->get('version.version_file', Constants::DEFAULT_VERSION_FILE);
                $filePath = $this->fileCommands->getVersionFilePath($this->getBasePath(), $fileName);
                $info['version_file'] = $fileName;
                $info['version_file_path'] = $filePath;
                $info['version_file_exists'] = $this->fileCommands->fileExists($filePath);
            } else {
                $info['is_git_repo'] = $this->isGitRepository();
            }

            return $info;
        }
    }
}
