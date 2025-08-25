# LaraGit Version

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gdn-dev/laragit-version.svg?style=flat-square)](https://packagist.org/packages/gdn-dev/laragit-version)
[![Tests](https://img.shields.io/github/actions/workflow/status/gdn-dev/laragit-version/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/gdn-dev/laragit-version/actions/workflows/run-tests.yml)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=gdn-dev_laragit-version&metric=coverage)](https://sonarcloud.io/summary/new_code?id=gdn-dev_laragit-version)
[![Total Downloads](https://img.shields.io/packagist/dt/gdn-dev/laragit-version.svg?style=flat-square)](https://packagist.org/packages/gdn-dev/laragit-version)

Effortlessly manage versions in your Laravel project using Git tags. No configuration required - just install and use!

LaraGit Version automatically detects your Git tags and provides a simple API to display version information in your Laravel applications. Perfect for showing version numbers in footers, admin panels, or anywhere you need to display your application's version.

## ✨ Features

- 🚀 **Zero Configuration** - Works out of the box
- 🎯 **Git Integration** - Uses your existing Git tags
- 📄 **VERSION File Support** - Read version from VERSION files
- 🎨 **Flexible Formatting** - Multiple display formats
- 💾 **Caching Support** - Performance optimized
- 🛡️ **Error Handling** - Graceful fallbacks
- 🔧 **Blade Directive** - Easy template integration
- 📱 **Laravel 9-12** - Supports Laravel 9, 10, 11, and 12

## 📋 Requirements

- PHP 8.1 or higher
- Laravel 9.0 - 12.x
- Git (installed and accessible)

## 📦 Installation

Install the package via Composer:

```bash
composer require gdn-dev/laragit-version
```

The package will automatically register itself via Laravel's auto-discovery.

## 🚀 Quick Start

### Basic Usage

```php
use GenialDigitalNusantara\LaragitVersion\Facade as LaragitVersion;

// Get version with default formatting
echo LaragitVersion::show(); // "Version 1.0.0 (commit a33376b)"

// Get compact version
echo LaragitVersion::show('compact'); // "v1.0.0"

// Get version info as array
$info = LaragitVersion::getVersionInfo();
```

### Using VERSION File

LaraGit Version automatically detects when a `VERSION` file is present in your project root and will use it instead of Git tags, even if the source is configured as `git-local`. This prevents unnecessary Git command executions and log spam when Git is not available.

```php
// Create VERSION file in project root
file_put_contents(base_path('VERSION'), '2.1.0');

// Package automatically uses VERSION file even with default configuration
echo LaragitVersion::show(); // "Version 2.1.0"

// You can also explicitly configure to use VERSION file
config(['version.source' => 'file']);
echo LaragitVersion::show(); // "Version 2.1.0"
```

### Blade Templates

Use the convenient Blade directive:

```blade
{{-- Default format --}}
@laragitVersion

{{-- Compact format --}}
@laragitVersion('compact')

{{-- Custom format --}}
@laragitVersion('v{version} built on {branch}')
```

### Service Container

```php
// Via service container
$version = app('gdn-dev.laragit-version')->show();

// Via dependency injection
public function __construct(LaragitVersion $laragitVersion)
{
    $this->version = $laragitVersion->show();
}
```

## 🎨 Format Options

### Predefined Formats

| Format | Output Example | Description |
|--------|----------------|-------------|
| `full` | `Version 1.0.0 (commit a33376b)` | Complete version with commit |
| `compact` | `v1.0.0` | Short version with v prefix |
| `version` | `v1.0.0` | Version tag as-is |
| `version-only` | `1.0.0` | Version number only |
| `major` | `1` | Major version number |
| `minor` | `0` | Minor version number |
| `patch` | `0` | Patch version number |
| `commit` | `a33376b` | Short commit hash |

### Custom Format Strings

Use placeholders to create custom formats:

```php
// Available placeholders
LaragitVersion::show('v{major}.{minor}.{patch}'); // "v1.0.0"
LaragitVersion::show('Version {version} on {branch}'); // "Version v1.0.0 on main"
LaragitVersion::show('{version} ({commit})'); // "v1.0.0 (a33376b)"
```

#### Available Placeholders

- `{full}` - Full formatted version
- `{compact}` - Compact version (v1.0.0)
- `{version}` - Version tag as-is
- `{version-only}` - Version number only
- `{major}` - Major version number
- `{minor}` - Minor version number  
- `{patch}` - Patch version number
- `{commit}` - Short commit hash
- `{branch}` - Current Git branch
- `{prerelease}` - Prerelease identifier
- `{buildmetadata}` - Build metadata

## ⚙️ Configuration

Publish the configuration file (optional):

```bash
php artisan vendor:publish --provider="GenialDigitalNusantara\LaragitVersion\ServiceProvider" --tag="config"
```

Configuration options in `config/version.php`:

```php
return [
    // Version source: 'git-local', 'git-remote', or 'file'
    'source' => 'git-local',
    
    // Default branch
    'branch' => 'main',
    
    // VERSION file path (when using 'file' source)
    'version_file' => 'VERSION',
    
    // Default format
    'format' => 'full',
    
    // Datetime format
    'datetime_format' => 'Y-m-d H:i',
    
    // Blade directive name
    'blade_directive' => 'laragitVersion',
];
```

## 🎯 Advanced Usage

### Error Handling

```php
use GenialDigitalNusantara\LaragitVersion\Exceptions\TagNotFound;

try {
    $version = LaragitVersion::getCurrentVersion();
} catch (TagNotFound $e) {
    // Handle when no Git tags found
    $version = 'Development';
}
```

### Version Information

```php
// Get comprehensive version info
$info = LaragitVersion::getVersionInfo();
/*
[
    'version' => ['full' => 'v1.0.0', 'major' => '1', ...],
    'commit' => ['hash' => 'a33376b...', 'short' => 'a33376b'],
    'branch' => 'main',
    'repository_url' => 'https://github.com/...',
    'is_git_repo' => true,
]
*/

// Check Git availability
if (LaragitVersion::isGitAvailable()) {
    // Git is available
}

// Get current branch
$branch = LaragitVersion::getCurrentBranch();
```

## 🛠️ Troubleshooting

### Git Not Found Issues

If you're seeing repeated Git command errors in your logs, it's likely because:

1. Git is not installed on your system
2. Git is not in your system PATH
3. You're in a directory that is not a Git repository

To resolve this:

1. **Install Git** - Make sure Git is installed and accessible from the command line
2. **Use VERSION file** - Create a VERSION file in your project root for environments where Git is not available
3. **Configure source** - Explicitly set `'source' => 'file'` in your configuration

### Version File Issues

When using VERSION files:

1. Ensure the file is in your project root
2. The file should contain only the version string (e.g., "1.0.0" or "v1.2.3")
3. The file should be readable by your application

### Logging Issues

The package now automatically detects when to use VERSION files and skips unnecessary Git operations, which should significantly reduce log spam in environments where Git is not available.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 👥 Credits

- [Muhammad Rosyid Izzulkhaq](https://github.com/rsdiz)
- [All Contributors](../../contributors)

## ⭐ Show Your Support

Give a ⭐️ if this project helped you!

---

<p align="center">
  <strong>Made with ❤️ by <a href="https://genilogi.id">Genial Digital Nusantara</a></strong>
</p>
