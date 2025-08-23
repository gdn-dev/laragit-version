<?php

use GenialDigitalNusantara\LaragitVersion\Helper\Constants;

return [
    /*
    |--------------------------------------------------------------------------
    | Version Source
    |--------------------------------------------------------------------------
    |
    | Determines how the package retrieves the version information.
    | Available options:
    | - 'git-local': Retrieves version from local git repository (default)
    | - 'git-remote': Retrieves version from remote git repository
    | - 'file': Retrieves version from a VERSION file
    |
    */
    'source' => Constants::VERSION_SOURCE_GIT_LOCAL,
    
    /*
    |--------------------------------------------------------------------------
    | Branch
    |--------------------------------------------------------------------------
    |
    | Specifies which git branch to use when retrieving version information.
    | Defaults to 'main' if not specified.
    |
    */
    'branch' => Constants::DEFAULT_BRANCH,

    /*
    |--------------------------------------------------------------------------
    | Version File Path
    |--------------------------------------------------------------------------
    |
    | When using 'file' as the version source, this specifies the path to
    | the VERSION file relative to the project root. The file should contain
    | only the version string (e.g., "1.0.0" or "v1.2.3").
    |
    */
    'version_file' => Constants::DEFAULT_VERSION_FILE,

    /*
    |--------------------------------------------------------------------------
    | Version Format
    |--------------------------------------------------------------------------
    |
    | Defines how the version string will be displayed.
    | 
    | Available placeholders:
    | - {full}: Full version string (e.g "Version 1.0.0 (commit a33376b)")
    | - {compact}: Short version string (e.g "v1.0.0")
    | - {timestamp-year}: Year (e.g 2025")
    | - {timestamp-month}: Month (e.g 01")
    | - {timestamp-day}: Day (e.g 30")
    | - {timestamp-hour}: Hour (e.g 17")
    | - {timestamp-minute}: Minute (e.g 08")
    | - {timestamp-second}: Second (e.g 39")
    | - {timestamp-timezone}: Timezone (e.g +07:00")
    | - {timestamp-datetime}: Datetime (e.g 2025-01-30 17:08:39 +07:00")
    | - {version}: Version string (e.g "v1.0.0")
    | - {version-only}: Version number (e.g "1.0.0")
    | - {major}: Major version number
    | - {minor}: Minor version number
    | - {patch}: Patch version number
    | - {prerelease}: Prerelease version number (e.g alpha.1")
    | - {buildmetadata}: Build metadata (e.g 21AF26D3")
    | - {commit}: Short git commit hash
    | - {branch}: Current branch name
    |
    | Default format: 'full'
    | Example: 'v{major}.{minor}.{patch} ({commit})'
    |
    */
    'format' => Constants::DEFAULT_FORMAT,

    /*
    |--------------------------------------------------------------------------
    | Datetime Format
    |--------------------------------------------------------------------------
    |
    | Defines how the datetime string will be displayed.
    | 
    | Default format: 'Y-m-d H:i'
    |
    */
    'datetime_format' => Constants::DEFAULT_DATETIME_FORMAT,

    /*
    |--------------------------------------------------------------------------
    | Blade Directive
    |--------------------------------------------------------------------------
    |
    | Custom Blade directive allows you to change the name of the Blade directive.
    | 
    | By default, use @laragitVersion in your Blade templates.
    | You can customize this to use a different directive name.
    |
    */
    'blade_directive' => Constants::DEFAULT_BLADE_DIRECTIVE,
];
