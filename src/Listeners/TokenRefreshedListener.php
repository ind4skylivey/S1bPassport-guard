<?php

namespace S1bTeam\PassportGuard\Listeners;

use Laravel\Passport\Events\RefreshTokenCreated;
use S1bTeam\PassportGuard\Models\OauthTokenMetric;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Token;

class TokenRefreshedListener
{
    /**
     * Handle the RefreshTokenCreated event.
     * This event fires when a refresh token is created (which happens during token refresh).
     *
     * @param RefreshTokenCreated $event
     * @return void
     */
    public function handle(RefreshTokenCreated $event): void
    {
        // Get the access token to find client_id and user_id
        $accessToken = Token::find($event->accessTokenId);

        if (!$accessToken) {
            return;
        }

        OauthTokenMetric::updateOrCreate(
            [
                'client_id' => $accessToken->client_id,
                'user_id' => $accessToken->user_id,
                'date' => now()->toDateString(),
            ],
            [
                'tokens_refreshed' => DB::raw('tokens_refreshed + 1')
            ]
        );
    }
}
