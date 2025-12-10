<?php

return [
    'enabled' => env('S1B_PASSPORT_GUARD_ENABLED', true),
    
    'threat_thresholds' => [
        'creation_spike_pct' => 200, // Alert if creation is 200% above average
        'max_refreshes_hour' => 50,  // Alert if refreshes exceed 50/hour
    ],
    
    'retention_days' => 365,
];
