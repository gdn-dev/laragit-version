<?php

namespace GenialDigitalNusantara\LaragitVersion;

use GenialDigitalNusantara\LaragitVersion\Helper\Constants;
use GenialDigitalNusantara\LaragitVersion\Helper\GitCommands;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
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
}
