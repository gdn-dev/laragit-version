# Changelog

All notable changes to `laragit-version` will be documented in this file.

## [Unreleased]

### Added
- Complete core API implementation with `show()`, `getCurrentVersion()`, and `getVersionInfo()` methods
- Comprehensive Git command validation and error handling
- Enhanced exception handling with specific error contexts
- Caching support for improved performance
- Blade directive support with `@laragitVersion`
- Multiple version format options (full, compact, version-only, etc.)
- Custom format string support with placeholders
- Remote repository support for version detection
- Comprehensive unit test coverage
- Detailed documentation and usage examples

### Fixed
- ServiceProvider configuration loading and Blade directive registration
- Git command execution with proper error handling
- Version parsing for semantic versioning compliance
- Constructor compatibility with Laravel's Container interface

### Changed
- Restructured package architecture following Laravel conventions
- Improved Git command reliability with error redirection
- Enhanced exception classes with factory methods
- Updated documentation with comprehensive examples

### Technical
- Added proper PSR-4 autoloading
- Implemented Laravel service provider lifecycle
- Added facade support for easy access
- Included comprehensive error handling and logging
- Added caching mechanism for performance optimization

