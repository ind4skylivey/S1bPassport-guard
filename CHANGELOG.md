# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-12-10

### Added

- **Core Dashboard** - `s1b:guard` artisan command with real-time token analytics
- **Threat Detection** - Automatic detection of token creation spikes and refresh anomalies
- **Token Tracking** - Listeners and observers for automatic metric collection
- **Filtering** - Support for `--hunt` (client) and `--user` filters
- **CSV Export** - Export analytics data to CSV files
- **Expired Tokens Job** - Scheduled command to track expired tokens
- **Configurable Thresholds** - Customizable threat detection thresholds via config

### Technical

- Laravel 11.x support
- PHP 8.2+ requirement
- Laravel Passport 13.x integration
- PSR-12 compliant code style
- Orchestra Testbench for testing
