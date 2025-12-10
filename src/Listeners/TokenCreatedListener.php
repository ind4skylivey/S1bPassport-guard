<?php

namespace S1bTeam\PassportGuard\Listeners;

use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Events\AccessTokenRefreshed;
use S1bTeam\PassportGuard\Models\OauthTokenMetric;
use Illuminate\Support\Facades\DB;

class TokenCreatedListener
{
    public function handle(AccessTokenCreated $event): void
    {
        $lifespanHours = $event->accessToken->expires_at->diffInHours($event->accessToken->created_at);
        OauthTokenMetric::updateOrCreate(
            [
                'client_id' => $event->clientId,
                'user_id' => $event->userId,
                'date' => now()->toDateString(),
            ],
            [
                'tokens_created' => DB::raw('tokens_created + 1'),
                'total_token_lifespan_hours' => DB::raw("total_token_lifespan_hours + $lifespanHours"),
            ]
        );
    }
}
