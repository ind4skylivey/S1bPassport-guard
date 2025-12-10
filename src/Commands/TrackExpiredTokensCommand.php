<?php

namespace S1bTeam\PassportGuard\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Token;
use S1bTeam\PassportGuard\Models\OauthTokenMetric;

class TrackExpiredTokensCommand extends Command
{
    protected $signature = 's1b:track-expired
                            {--date= : Date to process (default: today)}';

    protected $description = 'Track expired tokens and update metrics. Run daily via scheduler.';

    public function handle(): int
    {
        $date = $this->option('date')
            ? \Carbon\Carbon::parse($this->option('date'))
            : now();

        $this->info(sprintf("🔍 Tracking expired tokens for %s...", $date->format('Y-m-d')));

        // Find tokens that expired on the given date, grouped by client and user
        $expiredTokens = Token::query()
            ->where('revoked', false)
            ->whereDate('expires_at', $date->toDateString())
            ->select('client_id', 'user_id', DB::raw('COUNT(*) as expired_count'))
            ->groupBy('client_id', 'user_id')
            ->get();

        $totalProcessed = 0;

        foreach ($expiredTokens as $group) {
            OauthTokenMetric::updateOrCreate(
                [
                    'client_id' => $group->client_id,
                    'user_id' => $group->user_id,
                    'date' => $date->toDateString(),
                ],
                [
                    'tokens_expired' => DB::raw("tokens_expired + {$group->expired_count}"),
                ]
            );

            $totalProcessed += $group->expired_count;
        }

        $this->info(sprintf("✅ Tracked %d expired tokens across %d groups.", $totalProcessed, $expiredTokens->count()));

        return self::SUCCESS;
    }
}
