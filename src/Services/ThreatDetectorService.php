<?php

namespace S1bTeam\PassportGuard\Services;

use Illuminate\Support\Facades\DB;
use S1bTeam\PassportGuard\Models\OauthTokenMetric;

class ThreatDetectorService
{
    protected float $creationSpikeThreshold;
    protected int $maxRefreshesHour;

    public function __construct()
    {
        $this->creationSpikeThreshold = config('s1b-passport-guard.threat_thresholds.creation_spike_pct', 200) / 100;
        $this->maxRefreshesHour = config('s1b-passport-guard.threat_thresholds.max_refreshes_hour', 50);
    }

    /**
     * Detect all threats in the given time period.
     *
     * @param int $days
     * @param int|null $clientId Filter by specific client
     * @param int|null $userId Filter by specific user
     * @return array
     */
    public function detectThreats(int $days = 7, ?int $clientId = null, ?int $userId = null): array
    {
        return [
            'creation_spike' => $this->detectCreationSpikes($days, $clientId, $userId),
            'refresh_anomaly' => $this->detectRefreshAnomalies($days, $clientId, $userId),
        ];
    }

    /**
     * Detect unusual spikes in token creation compared to baseline.
     *
     * @param int $days
     * @param int|null $clientId
     * @param int|null $userId
     * @return array
     */
    protected function detectCreationSpikes(int $days, ?int $clientId = null, ?int $userId = null): array
    {
        $baselineStart = now()->subDays(30 + $days);
        $baselineEnd = now()->subDays($days);

        // Build baseline query
        $baselineQuery = OauthTokenMetric::whereBetween('date', [$baselineStart, $baselineEnd]);

        if ($clientId !== null) {
            $baselineQuery->where('client_id', $clientId);
        }

        if ($userId !== null) {
            $baselineQuery->where('user_id', $userId);
        }

        $baselines = $baselineQuery
            ->select('client_id', DB::raw('AVG(tokens_created) as avg_created'))
            ->groupBy('client_id')
            ->get()
            ->keyBy('client_id');

        // Build recent query
        $recentQuery = OauthTokenMetric::with('client')
            ->whereBetween('date', [now()->subDays($days), now()]);

        if ($clientId !== null) {
            $recentQuery->where('client_id', $clientId);
        }

        if ($userId !== null) {
            $recentQuery->where('user_id', $userId);
        }

        $recent = $recentQuery
            ->select('client_id', 'date', 'tokens_created')
            ->get();

        $anomalies = [];

        foreach ($recent as $metric) {
            $baseline = $baselines[$metric->client_id]->avg_created ?? 0;

            if ($baseline > 0) {
                $increase = ($metric->tokens_created - $baseline) / $baseline;
                if ($increase >= $this->creationSpikeThreshold) {
                    $message = sprintf(
                        "Creation spike +%d%% on %s (Client #%d: %s)",
                        round($increase * 100),
                        $metric->date->format('Y-m-d'),
                        $metric->client_id,
                        $metric->client->name ?? 'Unknown'
                    );
                    $anomalies[] = $message;

                    \S1bTeam\PassportGuard\Events\ThreatDetected::dispatch(
                        'creation_spike',
                        $message,
                        $metric->client_id,
                        $metric->user_id,
                        ['increase_pct' => round($increase * 100)]
                    );
                }
            } elseif ($metric->tokens_created > 50) {
                $anomalies[] = sprintf(
                    "High volume new client: %d tokens on %s (Client #%d: %s)",
                    $metric->tokens_created,
                    $metric->date->format('Y-m-d'),
                    $metric->client_id,
                    $metric->client->name ?? 'Unknown'
                );
            }
        }

        return array_unique($anomalies);
    }

    /**
     * Detect unusual refresh token patterns.
     *
     * @param int $days
     * @param int|null $clientId
     * @param int|null $userId
     * @return array
     */
    protected function detectRefreshAnomalies(int $days, ?int $clientId = null, ?int $userId = null): array
    {
        $anomalies = [];

        $query = OauthTokenMetric::with('user')
            ->whereBetween('date', [now()->subDays($days), now()])
            ->where('tokens_refreshed', '>', $this->maxRefreshesHour * 24);

        if ($clientId !== null) {
            $query->where('client_id', $clientId);
        }

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $metrics = $query->get();

        foreach ($metrics as $metric) {
            $message = sprintf(
                "Unusual refreshes on %s (User #%d: %d/day)",
                $metric->date->format('Y-m-d'),
                $metric->user_id ?? 0,
                $metric->tokens_refreshed
            );
            $anomalies[] = $message;

            \S1bTeam\PassportGuard\Events\ThreatDetected::dispatch(
                'refresh_anomaly',
                $message,
                $metric->client_id,
                $metric->user_id,
                ['count' => $metric->tokens_refreshed]
            );
        }

        return array_unique($anomalies);
    }
}
