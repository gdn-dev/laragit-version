# LaraGit Version

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gdn-dev/laragit-version.svg?style=flat-square)](https://packagist.org/packages/gdn-dev/laragit-version)
[![Tests](https://img.shields.io/github/actions/workflow/status/gdn-dev/laragit-version/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/gdn-dev/laragit-version/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/gdn-dev/laragit-version.svg?style=flat-square)](https://packagist.org/packages/gdn-dev/laragit-version)

Effortlessly manage versions in your Laravel project using Git tags. No configuration required - just install and use!

LaraGit Version automatically detects your Git tags and provides a simple API to display version information in your Laravel applications. Perfect for showing version numbers in footers, admin panels, or anywhere you need to display your application's version.

## ✨ Features

- 🚀 **Zero Configuration** - Works out of the box
- 🎯 **Git Integration** - Uses your existing Git tags
- 🎨 **Flexible Formatting** - Multiple display formats
- 💾 **Caching Support** - Performance optimized
- 🛡️ **Error Handling** - Graceful fallbacks
- 🔧 **Blade Directive** - Easy template integration
- 📱 **Laravel 9+** - Modern Laravel support

## 📋 Requirements

- PHP 8.0 or higher
- Laravel 9.0 or higher
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
    // Version source: 'git-local' or 'git-remote'
    'source' => 'git-local',
    
    // Default branch
    'branch' => 'main',
    
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
    // Git is installed and accessible
}

// Check if current directory is a Git repository
if (LaragitVersion::isGitRepository()) {
    // We're in a Git repository
}
```

### Remote Repository

Use remote Git repository for version detection:

```php
// In config/version.php
'source' => 'git-remote',
```

This will fetch version information from the remote repository instead of local tags.

## 🔧 Git Tag Requirements

For best results, use semantic versioning for your Git tags:

```bash
# Good examples
git tag v1.0.0
git tag v2.1.3
git tag v1.0.0-alpha.1
git tag v1.0.0+build.123

# Also supported
git tag 1.0.0
git tag version-1.0.0
```

## 🚨 Error Scenarios

The package handles various error scenarios gracefully:

| Scenario | Behavior |
|----------|----------|
| No Git installed | Returns "No version available" |
| Not a Git repository | Returns "No version available" |
| No Git tags | Throws `TagNotFound` exception |
| Remote repository unavailable | Throws `TagNotFound` exception |
| Invalid tag format | Graceful fallback |

## 🧪 Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

## 🎨 Code Style

Format code using PHP CS Fixer:

```bash
composer format
```

## 📚 API Reference

### LaragitVersion Methods

```php
// Display formatted version
show(?string $format = null): string

// Get current version
getCurrentVersion(): string

// Get current Git branch
getCurrentBranch(): string

// Get commit information
getCommitInfo(): array

// Get comprehensive version info
getVersionInfo(): array

// Check Git availability
isGitAvailable(): bool

// Check if Git repository
isGitRepository(): bool

// Get repository URL
getRepositoryUrl(): string
```

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'feat: add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 🔒 Security

If you discover any security-related issues, please email security@genilogi.id instead of using the issue tracker.

## 📄 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## 📜 License

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
