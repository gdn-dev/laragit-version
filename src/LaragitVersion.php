<?php

namespace GenialDigitalNusantara\LaragitVersion;

use GenialDigitalNusantara\LaragitVersion\Exceptions\TagNotFound;
use GenialDigitalNusantara\LaragitVersion\Helper\Constants;
use GenialDigitalNusantara\LaragitVersion\Helper\FileCommands;
use GenialDigitalNusantara\LaragitVersion\Helper\GitCommands;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Process\Process;

class LaragitVersion
{
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

    private function cleanOutput($getOutput): string
    {
        return trim(str_replace("\n", '', $getOutput));
    }

    private function getCommitLength(): int
    {
        return 6;
    }

    private function execShellWithProcess($command, $path): string
    {
        try {
            if (method_exists(Process::class, 'fromShellCommandline')) {
                $process = Process::fromShellCommandline($command, $path);
            } else {
                $process = new Process($command, $path);
            }

            $process->mustRun();
            if ($process->isSuccessful()) {
                $output = $process->getOutput();
            } else {
                Log::error("execShellWithProcess($command, $path): " . $process->getErrorOutput());
                $output = '';
            }
        } catch (RuntimeException $e) {
            Log::error("execShellWithProcess($command, $path): " . $e->getMessage());
            $output = '';
        }

        return $output;
    }

    private function execShellDirectly($command, $path): string
    {
        $dir = getcwd();
        chdir($path);

        // Redirect stderr to capture error output
        $output = shell_exec($command . ' 2>&1');

        chdir($dir);

        // Check if the output contains error indicators
        if ($output === null || $output === false) {
            Log::error("execShellDirectly($command, $path): Command execution failed");

            return '';
        }

        // Check for common error indicators in the output
        if (stripos($output, 'error') !== false ||
            stripos($output, 'fatal') !== false ||
            stripos($output, 'command not found') !== false) {
            Log::warning("execShellDirectly($command, $path): Potential error in command output: " . $output);

            return '';
        }

        return $output;
    }

    protected function shell($command): string
    {
        $output = class_exists('\Symfony\Component\Process\Process') ?
            $this->execShellWithProcess($command, $this->getBasePath()) :
            $this->execShellDirectly($command, $this->getBasePath());

        return $this->cleanOutput($output);
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
     * Get the current version from Git tags.
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
        $result = $this->shell($this->commands->checkGitAvailable());

        return ! empty($result) && str_contains($result, 'git version');
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
