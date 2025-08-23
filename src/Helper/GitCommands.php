<?php

namespace GenialDigitalNusantara\LaragitVersion\Helper;

class GitCommands
{
    /**
     * Get the URL of the repository.
     *
     * @return string
     */
    public function getRepositoryUrl(): string
    {
        return "git config --get remote.origin.url";
    }

    /**
     * Get the commit hash of the current branch.
     *
     * @return string
     */
    public function getCommitOnLocal(): string
    {
        return "git rev-parse --verify HEAD";
    }

    /**
     * Get the commit hash of the selected branch.
     *
     * @param string|null $version The version tag to check. If null, the current HEAD is checked.
     *
     * @return string
     */
    protected function getSelectedCommitOnLocal(?string $version): string
    {
        return "git rev-parse --verify " . ($version ? "refs/tags/$version" : "HEAD");
    }

    /**
     * Get the commit hashes of all commits on the remote repository.
     *
     * @param string $repository The URL of the remote repository.
     *
     * @return string
     */
    protected function getAllCommitOnRemote(string $repository): string
    {
        return "git ls-remote $repository";
    }

    /**
     * Get the commit hash of the current branch on the remote repository.
     *
     * @param string $repository The URL of the remote repository.
     *
     * @return string
     */
    protected function getCurrentCommitOnRemote(string $repository): string
    {
        return "git ls-remote $repository | grep HEAD | cut -d / -f 3";
    }

    /**
     * Get the commit hash of the latest commit on the remote repository.
     *
     * @param string $repository The URL of the remote repository.
     *
     * @return string
     */
    public function getLatestCommitOnRemote(string $repository): string
    {
        return "git ls-remote $repository | tail -1 | cut -f1";
    }

    /**
     * Get the latest version tag on the local repository.
     *
     * @return string
     */
    public function getLatestVersionOnLocal(): string
    {
        return "git describe --tags --abbrev=0";
    }

    /**
     * Get the current version tag on the local repository.
     *
     * @return string
     */
    protected function getCurrentVersionOnLocal(): string
    {
        return "git describe";
    }

    /**
     * Get the current branch name.
     *
     * @return string
     */
    public function getCurrentBranch(): string
    {
        return "git rev-parse --abbrev-ref HEAD";
    }

    /**
     * Get the latest version tag on the remote repository.
     *
     * @param string $repository The URL of the remote repository.
     *
     * @return string
     */
    public function getLatestVersionOnRemote(string $repository): string
    {
        return "git ls-remote $repository | grep tags/ | grep -v {} | cut -d / -f 3 | sort --version-sort | tail -1";
    }
}
