<?php

namespace S1bTeam\PassportGuard\Observers;

use Laravel\Passport\Token;
use S1bTeam\PassportGuard\Models\OauthTokenMetric;
use Illuminate\Support\Facades\DB;

class TokenObserver
{
    public function updated(Token $token): void
    {
        if ($token->isDirty('revoked') && $token->revoked) {
            OauthTokenMetric::updateOrCreate(
                [
                    'client_id' => $token->client_id,
                    'user_id' => $token->user_id,
                    'date' => now()->toDateString(),
                ],
                [
                    'tokens_revoked' => DB::raw('tokens_revoked + 1')
                ]
            );
        }
    }
}