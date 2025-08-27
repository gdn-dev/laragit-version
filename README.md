# LaraGit Version (Simplified)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gdn-dev/laragit-version.svg?style=flat-square)](https://packagist.org/packages/gdn-dev/laragit-version)
[![Tests](https://img.shields.io/github/actions/workflow/status/gdn-dev/laragit-version/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/gdn-dev/laragit-version/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/gdn-dev/laragit-version.svg?style=flat-square)](https://packagist.org/packages/gdn-dev/laragit-version)

A simple Laravel package to manage versions in your project using either VERSION files or Git tags. No complex configuration required.

## ✨ Features

- 🚀 **Zero Configuration** - Works out of the box
- 📄 **VERSION File Support** - Read version from VERSION files
- 🎯 **Git Integration** - Uses your existing Git tags as fallback
- 🔧 **Blade Directive** - Easy template integration
- 📱 **Laravel 9-12** - Supports Laravel 9, 10, 11, and 12

## 📋 Requirements

- PHP 8.1 or higher
- Laravel 9.0 - 12.x

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
echo LaragitVersion::show(); // "Version 1.0.0"

// Get compact version
echo LaragitVersion::show('compact'); // "v1.0.0"

// Get version info as array
$info = LaragitVersion::getVersionInfo();
```

### Using VERSION File

```php
// Create VERSION file in project root
file_put_contents(base_path('VERSION'), '2.1.0');

// Use normally
echo LaragitVersion::show(); // "Version 2.1.0"
echo LaragitVersion::show('compact'); // "v2.1.0"
```

### Blade Templates

Use the convenient Blade directive:

```blade
{{-- Default format --}}
@laragitVersion

{{-- Compact format --}}
@laragitVersion('compact')
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

## 🎯 How It Works

The package uses a simple approach:

```php
'version' => file_exists(base_path('VERSION')) 
    ? trim(file_get_contents(base_path('VERSION')))
    : trim(exec('git describe --tags --abbrev=0'))
```

1. First checks for a VERSION file in your project root
2. If found, uses the content as the version
3. If not found, tries to get the version from Git tags
4. If neither works, returns "0.0.0" as default

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
