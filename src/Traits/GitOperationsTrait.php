<?php

namespace GenialDigitalNusantara\LaragitVersion\Traits;

use GenialDigitalNusantara\LaragitVersion\Helper\GitCommands;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

trait GitOperationsTrait
{
    /**
     * Execute shell command using Symfony Process.
     *
     * @param string $command
     * @param string $path
     * @return string
     */
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

    /**
     * Execute shell command directly.
     *
     * @param string $command
     * @param string $path
     * @return string
     */
    private function execShellDirectly($command, $path): string
    {
        $originalDir = getcwd();
        $output = '';

        // Validate path exists and is accessible
        if (!is_dir($path) || !is_readable($path)) {
            Log::error("execShellDirectly($command, $path): Path is not accessible");
        } else if (!chdir($path)) {
            // Change to the specified directory
            Log::error("execShellDirectly($command, $path): Failed to change directory");
        } else {
            try {
                // Execute command with error redirection
                // On Windows, we need to be more careful with command execution
                $output = shell_exec($command . ' 2>&1');

                // Check if the output contains error indicators
                if ($output === null || $output === false) {
                    Log::error("execShellDirectly($command, $path): Command execution failed or returned null");
                    $output = '';
                } else {
                    // Check for common error indicators in the output
                    $errorIndicators = ['error', 'fatal', 'command not found', 'is not recognized', "'git' is not recognized"];
                    foreach ($errorIndicators as $indicator) {
                        if (stripos($output, $indicator) !== false) {
                            Log::warning("execShellDirectly($command, $path): Potential error in command output: " . trim($output));
                            $output = '';
                            break;
                        }
                    }
                }
            } catch (Throwable $e) {
                Log::error("execShellDirectly($command, $path): Exception occurred - " . $e->getMessage());
                $output = '';
            } finally {
                // Restore original directory even if an exception occurs
                chdir($originalDir);
            }
        }

        return $output ?? '';
    }

    /**
     * Clean shell command output.
     *
     * @param string $getOutput
     * @return string
     */
    private function cleanOutput($getOutput): string
    {
        return trim(str_replace("\n", '', $getOutput));
    }

    /**
     * Execute shell command.
     *
     * @param string $command
     * @return string
     */
    protected function shell($command): string
    {
        Log::debug("Executing Git command: $command");

        $basePath = $this->getBasePath();
        Log::debug("Using base path: $basePath");

        // Validate base path
        if (! is_dir($basePath) || ! is_readable($basePath)) {
            Log::error("shell($command): Base path is not accessible: $basePath");

            return '';
        }

        // Check if we're in a Git repository for Git commands
        if (str_starts_with($command, 'git') && ! $this->isGitRepository()) {
            Log::warning("shell($command): Attempting to run Git command outside of Git repository");
        }

        $output = class_exists('\Symfony\Component\Process\Process') ?
            $this->execShellWithProcess($command, $basePath) :
            $this->execShellDirectly($command, $basePath);

        $cleanOutput = $this->cleanOutput($output);
        Log::debug("Command output: " . ($cleanOutput ?: '[empty]'));

        return $cleanOutput;
    }
}