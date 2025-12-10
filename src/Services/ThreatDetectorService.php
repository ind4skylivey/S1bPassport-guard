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

    public function detectThreats(int $days = 7): array
    {
        return [
            'creation_spike' => $this->detectCreationSpikes($days),
            'refresh_anomaly' => $this->detectRefreshAnomalies($days),
        ];
    }

    protected function detectCreationSpikes(int $days): array
    {
        $baselineStart = now()->subDays(30 + $days);
        $baselineEnd = now()->subDays($days);

        $baselines = OauthTokenMetric::whereBetween('date', [$baselineStart, $baselineEnd])
            ->select('client_id', DB::raw('AVG(tokens_created) as avg_created'))
            ->groupBy('client_id')
            ->get()
            ->keyBy('client_id');

        $recent = OauthTokenMetric::with('client')
            ->whereBetween('date', [now()->subDays($days), now()])
            ->select('client_id', 'date', 'tokens_created')
            ->get();

        $anomalies = [];

        foreach ($recent as $metric) {
            $baseline = $baselines[$metric->client_id]->avg_created ?? 0;
            if ($baseline > 0) {
                $increase = ($metric->tokens_created - $baseline) / $baseline;
                if ($increase >= $this->creationSpikeThreshold) {
                    $anomalies[] = sprintf(
                        "Creation spike +%d%% (Client #%d: %s)",
                        round($increase * 100),
                        $metric->client_id,
                        $metric->client->name ?? 'Unknown'
                    );
                }
            } elseif ($metric->tokens_created > 50) {
                 $anomalies[] = sprintf(
                    "High volume new client: %d tokens (Client #%d: %s)",
                    $metric->tokens_created,
                    $metric->client_id,
                    $metric->client->name ?? 'Unknown'
                );
            }
        }

        return array_unique($anomalies);
    }

    protected function detectRefreshAnomalies(int $days): array
    {
        $anomalies = [];
        
        $metrics = OauthTokenMetric::with('user')
            ->whereBetween('date', [now()->subDays($days), now()])
            ->where('tokens_refreshed', '>', $this->maxRefreshesHour * 24) 
            ->get();
            
        foreach($metrics as $metric) {
             $anomalies[] = sprintf(
                "Unusual refreshes (User #%d: %d/day approx)",
                $metric->user_id ?? 0,
                $metric->tokens_refreshed
            );
        }

        return array_unique($anomalies);
    }
}
