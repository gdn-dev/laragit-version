# Changelog

All notable changes to `laragit-version` will be documented in this file.

## v1.0.1 - 2025-09-04

### Changelog

All notable changes to this project will be documented in this file.

#### v1.0.1 - 2025-09-04

##### 🐛 Fixed

- **Git Error Suppression**: Eliminated "fatal: not a git repository" error logs when deployed to servers without Git repositories
- **Git Availability Checking**: Added proper Git availability detection before executing Git commands
- **Error Output Redirection**: Redirected stderr to `/dev/null` to prevent Git error logs
- **Graceful Fallback**: Ensured the package gracefully falls back to default version when Git is not available

##### 📊 Test Coverage

- Added `GitErrorSuppressionTest` to verify no Git errors are produced
- Added tests for Git availability detection


---

**Full Changelog**: https://github.com/gdn-dev/laragit-version/compare/v1.0.0...1.0.1

## v1.0.0 - 2025-08-27

### 🎉 Initial Stable Release

This is the first stable release of the LaraGit Version package, featuring a simplified implementation and significantly improved test coverage.

### ✨ Key Features

- **Git-based Version Detection**: Automatically detects the current version from Git tags
- **File-based Version Support**: Reads version information from VERSION files as an alternative to Git
- **Simple API**: Easy-to-use methods for retrieving version information
- **Laravel Integration**: Seamless integration with Laravel applications via Service Provider and Facade
- **Blade Directive Support**: Use `@laragitVersion` directly in your Blade templates

### 🚀 Major Improvements

- **Simplified Implementation**: Streamlined package architecture focusing on essential functionality
- **Enhanced Test Coverage**: Improved from 64.3% to 82.1% overall coverage
- **Reduced Complexity**: Removed unnecessary traits, helpers, and configuration options
- **Better Performance**: Lightweight implementation with minimal overhead
- **Improved Documentation**: Clear and concise documentation for all features

### 📊 Test Coverage

- Facade class: 100% coverage
- LaragitVersion class: 100% coverage
- ServiceProvider class: 37.5% coverage
- Overall project coverage: 82.1%

### ⚙️ Requirements

- PHP 8.1+
- Laravel 9.0+

### 📦 Installation

```bash
composer require gdn-dev/laragit-version


```
### 📖 Usage

```php
// Get current version
$version = LaragitVersion::show();

// In Blade templates
@laragitVersion


```
### 📋 Changelog

#### Added

- Git-based version detection
- File-based version support with VERSION files
- Simple API with `show()`, `getCurrentVersion()`, and `getVersionInfo()` methods
- Laravel Service Provider for easy integration
- Facade for convenient access
- Blade directive support (`@laragitVersion`)
- Comprehensive test suite with 82.1% coverage

#### Changed

- Simplified package architecture
- Streamlined implementation focusing on core functionality
- Reduced package complexity by removing unnecessary features

#### Fixed

- Improved error handling and edge case management
- Enhanced version detection reliability
- Better Git command execution handling
