<?php

namespace S1bTeam\PassportGuard\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Token;
use S1bTeam\PassportGuard\Models\OauthTokenMetric;

class GuardService
{
    protected ThreatDetectorService $detector;

    public function __construct(ThreatDetectorService $detector)
    {
        $this->detector = $detector;
    }

    public function scan(int $days = 30): array
    {
        return [
            'active' => Token::where('revoked', false)
                ->where('expires_at', '>', now())
                ->count(),
            'expiring_7d' => Token::where('revoked', false)
                ->whereBetween('expires_at', [now(), now()->addDays(7)])
                ->count(),
            'revoked' => Token::where('revoked', true)->count(),
            'avg_lifespan_days' => $this->calculateAverageLifespan($days),
            'threats' => $this->detector->detectThreats($days),
            'top_clients' => $this->getTopClients(5, $days),
        ];
    }

    public function getTopClients(int $limit = 5, int $days = 30): Collection
    {
        return DB::table('oauth_token_metrics')
            ->join('oauth_clients', 'oauth_clients.id', '=', 'oauth_token_metrics.client_id')
            ->whereBetween('date', [now()->subDays($days)->toDateString(), now()->toDateString()])
            ->select('oauth_clients.name', DB::raw('SUM(tokens_created) as total_tokens'))
            ->groupBy('oauth_clients.id', 'oauth_clients.name')
            ->orderByDesc('total_tokens')
            ->limit($limit)
            ->get();
    }

    protected function calculateAverageLifespan(int $days): float
    {
        $result = OauthTokenMetric::whereBetween('date', [now()->subDays($days), now()])
            ->selectRaw('SUM(avg_token_lifespan_hours * tokens_created) as total_hours, SUM(tokens_created) as total_count')
            ->first();

        if (!$result || $result->total_count == 0) {
            return 0.0;
        }

        return round(($result->total_hours / $result->total_count) / 24, 2);
    }
}
