<?php

namespace GenialDigitalNusantara\LaragitVersion\Exceptions;

class TagNotFound extends \Exception
{
    /**
     * Create a new TagNotFound exception for missing Git installation.
     *
     * @return static
     */
    public static function gitNotInstalled(): static
    {
        return new static('Git is not installed or not available in the system PATH.');
    }

    /**
     * Create a new TagNotFound exception for invalid Git repository.
     *
     * @return static
     */
    public static function notGitRepository(): static
    {
        return new static('The current directory is not a Git repository.');
    }

    /**
     * Create a new TagNotFound exception for missing tags.
     *
     * @return static
     */
    public static function noTagsFound(): static
    {
        return new static('No Git tags found in the repository. Please create at least one version tag.');
    }

    /**
     * Create a new TagNotFound exception for invalid tag format.
     *
     * @param string $tag
     * @return static
     */
    public static function invalidTagFormat(string $tag): static
    {
        return new static("Invalid tag format: '$tag'. Expected semantic version format (e.g., v1.0.0).");
    }

    /**
     * Create a new TagNotFound exception for remote repository issues.
     *
     * @param string $repository
     * @return static
     */
    public static function remoteRepositoryUnavailable(string $repository): static
    {
        return new static("Remote repository '$repository' is not accessible or does not exist.");
    }
}
