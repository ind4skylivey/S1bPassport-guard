<?php

return [
    'enabled' => env('S1B_PASSPORT_GUARD_ENABLED', true),

    'threat_thresholds' => [
        'creation_spike_pct' => 200, // Alert if creation is 200% above average
        'max_refreshes_hour' => 50,  // Alert if refreshes exceed 50/hour
    ],

    'retention_days' => 365,

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Configure how you want to be notified when threats are detected.
    | Supported channels: 'mail', 'slack', 'discord'
    |
    */
    'notifications' => [
        'enabled' => env('S1B_PASSPORT_GUARD_NOTIFICATIONS', false),

        'channels' => ['mail'], // Add 'slack' or 'discord' here

        'mail' => [
            'to' => env('S1B_PASSPORT_GUARD_MAIL_TO', 'admin@example.com'),
        ],

        'slack' => [
            'webhook_url' => env('S1B_PASSPORT_GUARD_SLACK_WEBHOOK'),
        ],

        'discord' => [
            'webhook_url' => env('S1B_PASSPORT_GUARD_DISCORD_WEBHOOK'),
        ],
    ],
];
