<?php

namespace S1bTeam\PassportGuard\Listeners;

use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Token;
use S1bTeam\PassportGuard\Models\OauthTokenMetric;
use Illuminate\Support\Facades\DB;

class TokenCreatedListener
{
    /**
     * Handle the AccessTokenCreated event.
     *
     * @param AccessTokenCreated $event
     * @return void
     */
    public function handle(AccessTokenCreated $event): void
    {
        // Get the token to calculate lifespan
        $token = Token::find($event->tokenId);
        $lifespanHours = null;

        if ($token && $token->expires_at) {
            $lifespanHours = now()->diffInHours($token->expires_at);
        }

        // Find existing metric to update average lifespan
        $existingMetric = OauthTokenMetric::where([
            'client_id' => $event->clientId,
            'user_id' => $event->userId,
            'date' => now()->toDateString(),
        ])->first();

        if ($existingMetric) {
            // Update with new weighted average
            $newCount = $existingMetric->tokens_created + 1;
            $currentAvg = $existingMetric->avg_token_lifespan_hours ?? 0;
            $newAvg = $lifespanHours !== null
                ? (($currentAvg * $existingMetric->tokens_created) + $lifespanHours) / $newCount
                : $currentAvg;

            $existingMetric->update([
                'tokens_created' => $newCount,
                'avg_token_lifespan_hours' => round($newAvg, 2),
            ]);
        } else {
            // Create new metric
            OauthTokenMetric::create([
                'client_id' => $event->clientId,
                'user_id' => $event->userId,
                'date' => now()->toDateString(),
                'tokens_created' => 1,
                'avg_token_lifespan_hours' => $lifespanHours,
            ]);
        }
    }
}
