# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2025-12-10

### Added

-   **JSON Export:** Added `--export=json` option to `s1b:guard` command for programmatic consumption.
-   **Notifications System:** Native support for Mail, Slack, and Discord alerts when threats are detected.
-   **Observability:** New `ThreatDetected` event for custom integrations.
-   **Zero-Dependency Channels:** Custom lightweight webhook channels for Slack and Discord.
-   **CI/CD:** GitHub Actions workflow for automated testing.
-   **Static Analysis:** PHPStan configuration for code quality.

### Changed

-   **Performance:** Added composite index `(client_id, user_id, date)` to `oauth_token_metrics` table for faster queries.
-   **Docs:** Added `GUIDE.md` and `ROADMAP.md`.
-   **License:** Clarified "Source Available" terms in README.

## [1.0.0] - 2025-12-10

### Added

-   **Core Dashboard** - `s1b:guard` artisan command with real-time token analytics
-   **Threat Detection** - Automatic detection of token creation spikes and refresh anomalies
-   **Token Tracking** - Listeners and observers for automatic metric collection
-   **Filtering** - Support for `--hunt` (client) and `--user` filters
-   **CSV Export** - Export analytics data to CSV files
-   **Expired Tokens Job** - Scheduled command to track expired tokens
-   **Configurable Thresholds** - Customizable threat detection thresholds via config

### Technical

-   Laravel 11.x support
-   PHP 8.2+ requirement
-   Laravel Passport 13.x integration
-   PSR-12 compliant code style
-   Orchestra Testbench for testing
