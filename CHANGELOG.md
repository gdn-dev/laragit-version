# Changelog

All notable changes to `laragit-version` will be documented in this file.

## [Unreleased]

### Added
- **VERSION File Support**: Read version information from VERSION files as alternative to Git
- VERSION_SOURCE_FILE constant for file-based version detection
- FileCommands helper class for file operations and validation
- Comprehensive VERSION file validation and error handling
- Support for custom VERSION file paths via configuration
- Enhanced getVersionInfo() with source-specific metadata
- Complete core API implementation with `show()`, `getCurrentVersion()`, and `getVersionInfo()` methods
- Comprehensive Git command validation and error handling
- Enhanced exception handling with specific error contexts
- Caching support for improved performance
- Blade directive support with `@laragitVersion`
- Multiple version format options (full, compact, version-only, etc.)
- Custom format string support with placeholders
- Remote repository support for version detection
- Comprehensive unit test coverage (31+ tests)
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

