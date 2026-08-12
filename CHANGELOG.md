# Change Log

All notable changes to this project will be documented in this file.

## [Unreleased]

### Changed

- Documented full SDK resource surface and webhook guidance in README
- Notes that underlying `tims/shiprocket-php-sdk` supports PHP 8.0+

### Added

- Initial Laravel integration for `tims/shiprocket-php-sdk`
- Config publish tag `shiprocket-config`
- JWT token caching via Laravel Cache
- HTTP retry middleware for 429 / 5xx
- `Shiprocket` facade and container bindings
