# S1b Passport Guard 🛡️

![License](https://img.shields.io/badge/license-MIT-blue)
![Laravel](https://img.shields.io/badge/laravel-11.x-red)
![PHP](https://img.shields.io/badge/php-8.2+-purple)

Advanced OAuth2 token monitoring & threat detection for Laravel Passport. Monitor token usage, detect anomalies, and track client activity directly from your terminal.

## 🚀 Features

- **Real-time Dashboard:** View active tokens, expiration rates, and top clients.
- **Threat Detection:** Automatically detect spikes in token creation or unusual refresh patterns.
- **Client & User Insights:** Filter analytics by specific clients or users.
- **Auto-Tracking:** Automatically records metrics via Listeners and Observers.
- **Data Export:** Export analytics data to CSV for external analysis.
- **Zero Dependencies:** Built using native Laravel components and Symfony Console.

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
  • Creation spike +250% (Client #3: Mobile App)
  • Unusual refreshes (User #105: 2400/day approx)

TOP CLIENTS BY TOKENS
┌────┬─────────────────────┬──────────┐
│ #  │ Client              │ Tokens   │
├────┼─────────────────────┼──────────┤
│ 1  │ Mobile App          │ 567      │
│ 2  │ Web SPA             │ 234      │
│ 3  │ Admin API           │ 156      │
└────┴─────────────────────┴──────────┘
```

### Advanced Options

**Filter by Timeframe:**

```bash
php artisan s1b:guard --days=7
```

**Filter by Client or User:**

```bash
php artisan s1b:guard --hunt=1
php artisan s1b:guard --user=105
```

**Show Only Threats:**

```bash
php artisan s1b:guard --threats
```

**Export Data:**

```bash
php artisan s1b:guard --export=csv
```

## ⚙️ Configuration

You can customize thresholds and settings in `config/s1b-passport-guard.php`:

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

- **Services:** `GuardService` aggregates token data, `ThreatDetectorService` handles anomaly detection.
- **Observers:** `TokenObserver` watches the `Laravel\Passport\Token` model for revocation updates.
- **Listeners:** `TokenCreatedListener` listens for `AccessTokenCreated` events to track new tokens.
- **Commands:** `GuardCommand` renders the CLI dashboard using Symfony Console Table.
- **Models:** `OauthTokenMetric` stores aggregated daily metrics per client/user.

## 🧪 Testing

```bash
composer test
```

## 📄 License

MIT.
