# Change Log

All notable changes to this project will be documented in this file.

## [1.0.0] - 2026-08-12

### Added

- Laravel integration for `tims/shiprocket-php-sdk`
- Config publish tag `shiprocket-config`
- JWT token caching via Laravel Cache
- HTTP retry middleware for 429 / 5xx
- `Shiprocket` facade and container bindings
- Multi-account credentials via `config('shiprocket.credentials')` and `Shiprocket::withCredential()`
- Facade / manager resource shortcuts (`orders()`, `couriers()`, `warehouse()`, …)
- `Shiprocket::getToken()` helper
- Warehouse SRF support via SDK `warehouse()` (requires `tims/shiprocket-php-sdk` ^1.1)
