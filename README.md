# S1b Passport Guard 🛡️

![License](https://img.shields.io/badge/license-MIT-blue)
![Laravel](https://img.shields.io/badge/laravel-11.x-red)
![PHP](https://img.shields.io/badge/php-8.2+-purple)
[![Latest Version](https://img.shields.io/packagist/v/s1b-team/s1b-passport-guard)](https://packagist.org/packages/s1b-team/s1b-passport-guard)

Advanced OAuth2 token monitoring & threat detection for Laravel Passport. Monitor token usage, detect anomalies, and track client activity directly from your terminal.

## 🚀 Features

-   **Real-time Dashboard:** View active tokens, expiration rates, and top clients.
-   **Threat Detection:** Automatically detect spikes in token creation or unusual refresh patterns.
-   **Client & User Filters:** Filter analytics by specific clients or users.
-   **Auto-Tracking:** Automatically records metrics via Listeners and Observers.
-   **CSV Export:** Export analytics data to CSV for external analysis.
-   **Expired Token Tracking:** Scheduled command to track token expirations.
-   **Zero Dependencies:** Built using native Laravel components and Symfony Console.

## 📦 Installation

1.  **Require the package via Composer:**

    ```bash
    composer require s1b-team/s1b-passport-guard
    ```

2.  **Publish the configuration and migrations:**

    ```bash
    php artisan vendor:publish --provider="S1bTeam\\PassportGuard\\S1bPassportGuardServiceProvider"
    ```

3.  **Run migrations:**

    ```bash
    php artisan migrate
    ```

    _This creates the `oauth_token_metrics` table to store aggregated data._

4.  **(Optional) Schedule expired token tracking:**

    Add to your `app/Console/Kernel.php`:

    ```php
    $schedule->command('s1b:track-expired')->daily();
    ```

## 🛠 Usage

### View General Analytics Dashboard

Get a 30-day overview of your OAuth ecosystem:

```bash
php artisan s1b:guard
```

**Output Example:**

```text
🛡️ S1B PASSPORT GUARD REPORT (Last 30 days)
═══════════════════════════════════════════════

TOKENS STATUS
┌──────────────────────┬──────────┐
│ Active Tokens        │ 1,247    │
│ Expiring (7d)        │ 156      │
│ Revoked              │ 892      │
│ Avg Lifespan         │ 45.2 days│
└──────────────────────┴──────────┘

⚠️  THREATS DETECTED (2)
  • Creation spike +250% on 2025-12-08 (Client #3: Mobile App)
  • Unusual refreshes on 2025-12-09 (User #105: 2400/day)

TOP CLIENTS BY TOKENS
┌────┬─────────────────────┬──────────┐
│ #  │ Client              │ Tokens   │
├────┼─────────────────────┼──────────┤
│ 1  │ Mobile App          │ 567      │
│ 2  │ Web SPA             │ 234      │
│ 3  │ Admin API           │ 156      │
└────┴─────────────────────┴──────────┘
```

### Command Options

| Option         | Description                | Example        |
| -------------- | -------------------------- | -------------- |
| `--days=N`     | Number of days to analyze  | `--days=7`     |
| `--hunt=ID`    | Filter by Client ID        | `--hunt=1`     |
| `--user=ID`    | Filter by User ID          | `--user=105`   |
| `--threats`    | Show only detected threats | `--threats`    |
| `--export=csv` | Export data to CSV file    | `--export=csv` |

### Examples

**Filter by timeframe:**

```bash
php artisan s1b:guard --days=7
```

**Filter by client:**

```bash
php artisan s1b:guard --hunt=1
```

**Filter by user:**

```bash
php artisan s1b:guard --user=105
```

**Combined filters:**

```bash
php artisan s1b:guard --days=14 --hunt=1 --user=105
```

**Show only threats:**

```bash
php artisan s1b:guard --threats
```

**Export to CSV:**

```bash
php artisan s1b:guard --export=csv
# Exports to: storage/passport_guard_export_2025-12-10_120000.csv
```

### Track Expired Tokens

Run manually or via scheduler:

```bash
php artisan s1b:track-expired

# For a specific date:
php artisan s1b:track-expired --date=2025-12-01
```

## ⚙️ Configuration

Customize thresholds and settings in `config/s1b-passport-guard.php`:

```php
return [
    'enabled' => env('S1B_PASSPORT_GUARD_ENABLED', true),

    // Thresholds for threat detection
    'threat_thresholds' => [
        'creation_spike_pct' => 200, // Alert if creation is 200% above average
        'max_refreshes_hour' => 50,  // Alert if refreshes exceed 50/hour
    ],

    'retention_days' => 365,
];
```

## 🏗 Architecture

```
src/
├── Commands/
│   ├── GuardCommand.php              # Main CLI dashboard
│   └── TrackExpiredTokensCommand.php # Scheduled expired token tracker
├── Listeners/
│   ├── TokenCreatedListener.php      # AccessTokenCreated event handler
│   └── TokenRefreshedListener.php    # RefreshTokenCreated event handler
├── Observers/
│   └── TokenObserver.php             # Token model observer (revocations)
├── Services/
│   ├── GuardService.php              # Core analytics logic
│   └── ThreatDetectorService.php     # Anomaly detection engine
├── Models/
│   └── OauthTokenMetric.php          # Metrics storage model
└── S1bPassportGuardServiceProvider.php # Package bootstrapper
```

### Database Schema

The package creates an `oauth_token_metrics` table:

| Column                     | Type    | Description                    |
| -------------------------- | ------- | ------------------------------ |
| `id`                       | bigint  | Primary key                    |
| `client_id`                | bigint  | Foreign key to `oauth_clients` |
| `user_id`                  | bigint  | Foreign key to `users`         |
| `date`                     | date    | Metric date (indexed)          |
| `tokens_created`           | int     | Tokens created count           |
| `tokens_revoked`           | int     | Tokens revoked count           |
| `tokens_refreshed`         | int     | Token refresh count            |
| `tokens_expired`           | int     | Expired tokens count           |
| `failed_requests`          | int     | Failed OAuth requests          |
| `avg_token_lifespan_hours` | decimal | Average token TTL              |

## 🧪 Testing

```bash
composer install
composer test
```

## 📄 License

Proprietary License. See [LICENSE](LICENSE) for details. All rights reserved.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📞 Support

-   **Issues:** [GitHub Issues](https://github.com/s1b-team/s1b-passport-guard/issues)
-   **Security:** For security vulnerabilities, please email directly instead of opening issues.

---

Made with ❤️ by [S1b-Team](https://github.com/s1b-team)
