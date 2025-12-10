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

    /**
     * Perform a complete security scan of OAuth tokens.
     *
     * @param int $days Number of days to analyze
     * @param int|null $clientId Filter by specific client
     * @param int|null $userId Filter by specific user
     * @return array
     */
    public function scan(int $days = 30, ?int $clientId = null, ?int $userId = null): array
    {
        $baseQuery = Token::query();

        if ($clientId !== null) {
            $baseQuery->where('client_id', $clientId);
        }

        if ($userId !== null) {
            $baseQuery->where('user_id', $userId);
        }

        return [
            'active' => (clone $baseQuery)
                ->where('revoked', false)
                ->where('expires_at', '>', now())
                ->count(),
            'expiring_7d' => (clone $baseQuery)
                ->where('revoked', false)
                ->whereBetween('expires_at', [now(), now()->addDays(7)])
                ->count(),
            'revoked' => (clone $baseQuery)
                ->where('revoked', true)
                ->count(),
            'avg_lifespan_days' => $this->calculateAverageLifespan($days, $clientId, $userId),
            'threats' => $this->detector->detectThreats($days, $clientId, $userId),
            'top_clients' => $this->getTopClients(5, $days, $clientId, $userId),
        ];
    }

    /**
     * Get top clients by token creation volume.
     *
     * @param int $limit
     * @param int $days
     * @param int|null $clientId
     * @param int|null $userId
     * @return Collection
     */
    public function getTopClients(int $limit = 5, int $days = 30, ?int $clientId = null, ?int $userId = null): Collection
    {
        $query = DB::table('oauth_token_metrics')
            ->join('oauth_clients', 'oauth_clients.id', '=', 'oauth_token_metrics.client_id')
            ->whereBetween('date', [now()->subDays($days)->toDateString(), now()->toDateString()]);

        if ($clientId !== null) {
            $query->where('oauth_token_metrics.client_id', $clientId);
        }

        if ($userId !== null) {
            $query->where('oauth_token_metrics.user_id', $userId);
        }

        return $query
            ->select('oauth_clients.name', DB::raw('SUM(tokens_created) as total_tokens'))
            ->groupBy('oauth_clients.id', 'oauth_clients.name')
            ->orderByDesc('total_tokens')
            ->limit($limit)
            ->get();
    }

    /**
     * Calculate average token lifespan in days.
     *
     * @param int $days
     * @param int|null $clientId
     * @param int|null $userId
     * @return float
     */
    protected function calculateAverageLifespan(int $days, ?int $clientId = null, ?int $userId = null): float
    {
        $query = OauthTokenMetric::whereBetween('date', [now()->subDays($days), now()]);

        if ($clientId !== null) {
            $query->where('client_id', $clientId);
        }

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $result = $query
            ->selectRaw('SUM(avg_token_lifespan_hours * tokens_created) as total_hours, SUM(tokens_created) as total_count')
            ->first();

        if (!$result || $result->total_count == 0) {
            return 0.0;
        }

        return round(($result->total_hours / $result->total_count) / 24, 2);
    }

    /**
     * Export metrics data as array for CSV generation.
     *
     * @param int $days
     * @param int|null $clientId
     * @param int|null $userId
     * @return array
     */
    public function exportData(int $days = 30, ?int $clientId = null, ?int $userId = null): array
    {
        $query = OauthTokenMetric::with(['client', 'user'])
            ->whereBetween('date', [now()->subDays($days)->toDateString(), now()->toDateString()])
            ->orderBy('date', 'desc');

        if ($clientId !== null) {
            $query->where('client_id', $clientId);
        }

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query->get()->map(function ($metric) {
            return [
                'date' => $metric->date->format('Y-m-d'),
                'client_id' => $metric->client_id,
                'client_name' => $metric->client?->name ?? 'N/A',
                'user_id' => $metric->user_id,
                'tokens_created' => $metric->tokens_created,
                'tokens_revoked' => $metric->tokens_revoked,
                'tokens_refreshed' => $metric->tokens_refreshed,
                'tokens_expired' => $metric->tokens_expired,
                'failed_requests' => $metric->failed_requests,
                'avg_lifespan_hours' => $metric->avg_token_lifespan_hours,
            ];
        })->toArray();
    }
}
