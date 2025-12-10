<?php

namespace S1bTeam\PassportGuard\Listeners;

use Laravel\Passport\Events\AccessTokenCreated;
use S1bTeam\PassportGuard\Models\OauthTokenMetric;
use Illuminate\Support\Facades\DB;

class TokenCreatedListener
{
    public function handle(AccessTokenCreated $event): void
    {
        OauthTokenMetric::updateOrCreate(
            [
                'client_id' => $event->clientId,
                'user_id' => $event->userId,
                'date' => now()->toDateString(),
            ],
            [
                'tokens_created' => DB::raw('tokens_created + 1')
            ]
        );
    }
}