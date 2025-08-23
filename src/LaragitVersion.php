<?php

namespace GenialDigitalNusantara\LaragitVersion;

use GenialDigitalNusantara\LaragitVersion\Exceptions\TagNotFound;
use GenialDigitalNusantara\LaragitVersion\Helper\Constants;
use GenialDigitalNusantara\LaragitVersion\Helper\GitCommands;
use Illuminate\Config\Repository;
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
     * @var Application
     */
    protected Application $app;

    /** @var Repository */
    protected Repository $config;

    /** @var GitCommands */
    protected GitCommands $commands;

    /**
     * @param Application|null $app
     */
    public function __construct(?Application $app)
    {
        if (! $app) {
            $app = app();
        }
        $this->app = $app;
        $this->config = $app['config'];
        $this->commands = new GitCommands();
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
        return $this->shell(
            $this->commands->getRepositoryUrl()
        );
    }

    public function getCommitHash(): string
    {
        return $this->config->get('version.source') === Constants::VERSION_SOURCE_GIT_LOCAL ?
            $this->shell($this->commands->getCommitOnLocal()) :
            $this->shell($this->commands->getLatestCommitOnRemote($this->getRepositoryUrl()));
    }

    protected function getVersion(): string
    {
        return $this->config->get('version.source') === Constants::VERSION_SOURCE_GIT_LOCAL ?
            $this->shell($this->commands->getLatestVersionOnLocal()) :
            $this->shell($this->commands->getLatestVersionOnRemote($this->getRepositoryUrl()));
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

        // Validate Git availability and repository
        if (!$this->isGitAvailable()) {
            throw new TagNotFound('Git is not available on this system.');
        }
        
        if (!$this->isGitRepository()) {
            throw new TagNotFound('Current directory is not a Git repository.');
        }
        
        if (!$this->hasGitTags()) {
            throw new TagNotFound('No Git tags found in the repository.');
        }

        $version = $this->getVersion();
        
        if (empty($version)) {
            throw new TagNotFound('No valid version tags found in the repository.');
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
            
            return match ($format) {
                Constants::FORMAT_FULL => "Version {$versionParts['clean']} (commit {$commit['short']})",
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
        return !empty($result) && !str_contains($result, 'not a git repository');
    }

    /**
     * Check if Git is available on the system.
     *
     * @return bool
     */
    public function isGitAvailable(): bool
    {
        $result = $this->shell($this->commands->checkGitAvailable());
        return !empty($result) && str_contains($result, 'git version');
    }

    /**
     * Check if repository has any tags.
     *
     * @return bool
     */
    public function hasGitTags(): bool
    {
        if (!$this->isGitRepository()) {
            return false;
        }
        
        $result = $this->shell($this->commands->hasAnyTags());
        return !empty($result) && intval(trim($result)) > 0;
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
            
            return [
                'version' => $versionParts,
                'commit' => $commit,
                'branch' => $branch,
                'repository_url' => $this->getRepositoryUrl(),
                'is_git_repo' => $this->isGitRepository(),
            ];
        } catch (TagNotFound $e) {
            return [
                'error' => $e->getMessage(),
                'is_git_repo' => $this->isGitRepository(),
            ];
        }
    }
}
