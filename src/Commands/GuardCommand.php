<?php

namespace S1bTeam\PassportGuard\Commands;

use Illuminate\Console\Command;
use S1bTeam\PassportGuard\Services\GuardService;
use S1bTeam\PassportGuard\Services\ThreatDetectorService;
use Symfony\Component\Console\Helper\Table;

class GuardCommand extends Command
{
    protected $signature = 's1b:guard
                            {--days=30 : Number of days to analyze}
                            {--hunt= : Filter by Client ID}
                            {--user= : Filter by User ID}
                            {--export= : Export format (csv)}
                            {--threats : Show only threats}';

    protected $description = 'S1b Passport Guard: Advanced OAuth2 monitoring & threat detection';

    protected GuardService $guard;
    protected ThreatDetectorService $detector;

    public function __construct(GuardService $guard, ThreatDetectorService $detector)
    {
        parent::__construct();
        $this->guard = $guard;
        $this->detector = $detector;
    }

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $clientId = $this->option('hunt') ? (int) $this->option('hunt') : null;
        $userId = $this->option('user') ? (int) $this->option('user') : null;

        switch ($this->option('export')) {
            case 'csv':
                return $this->exportCsv($days, $clientId, $userId);
            case 'json':
                return $this->exportJson($days, $clientId, $userId);
        }

        if ($this->option('threats')) {
            return $this->showThreats($days, $clientId, $userId);
        }

        $this->showDashboard($days, $clientId, $userId);

        return self::SUCCESS;
    }

    protected function exportJson(int $days, ?int $clientId = null, ?int $userId = null): int
    {
        $scan = $this->guard->scan($days, $clientId, $userId);

        // Enrich with raw metrics data for detailed analysis
        $scan['metrics'] = $this->guard->exportData($days, $clientId, $userId);

        $this->output->write(json_encode($scan, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }

    protected function showDashboard(int $days, ?int $clientId = null, ?int $userId = null): void
    {
        $scan = $this->guard->scan($days, $clientId, $userId);
        $threats = $scan['threats'];

        // Header with filter info
        $filterInfo = $this->buildFilterInfo($clientId, $userId);
        $this->info(sprintf("\n🛡️ S1B PASSPORT GUARD REPORT (Last %d days)%s", $days, $filterInfo));
        $this->line("═══════════════════════════════════════════════\n");

        $this->line('<fg=yellow>TOKENS STATUS</>');
        $table = new Table($this->output);
        $table->setHeaders([]);
        $table->setRows([
            ['Active Tokens', number_format($scan['active'])],
            ['Expiring (7d)', number_format($scan['expiring_7d'])],
            ['Revoked', number_format($scan['revoked'])],
            ['Avg Lifespan', $scan['avg_lifespan_days'] . ' days'],
        ]);
        $table->setStyle('box');
        $table->render();

        // Threats section
        $countThreats = count($threats['creation_spike']) + count($threats['refresh_anomaly']);
        if ($countThreats > 0) {
            $this->line(sprintf("\n<bg=red;fg=white> ⚠️  THREATS DETECTED (%d) </>", $countThreats));
            foreach ($threats['creation_spike'] as $alert) {
                $this->line("  • <fg=red>$alert</>");
            }
            foreach ($threats['refresh_anomaly'] as $alert) {
                $this->line("  • <fg=red>$alert</>");
            }
        } else {
            $this->line("\n<fg=green>✅ No threats detected.</>");
        }

        // Top clients section
        if ($scan['top_clients']->isNotEmpty()) {
            $this->line("\n<fg=yellow>TOP CLIENTS BY TOKENS</>");
            $clientTable = new Table($this->output);
            $clientTable->setHeaders(['#', 'Client', 'Tokens']);

            $rows = [];
            $i = 1;
            foreach ($scan['top_clients'] as $client) {
                $rows[] = [$i++, $client->name, number_format($client->total_tokens)];
            }
            $clientTable->setRows($rows);
            $clientTable->setStyle('box');
            $clientTable->render();
        }

        $this->line("");
    }

    protected function showThreats(int $days, ?int $clientId = null, ?int $userId = null): int
    {
        $threats = $this->detector->detectThreats($days, $clientId, $userId);
        $countThreats = count($threats['creation_spike']) + count($threats['refresh_anomaly']);

        $filterInfo = $this->buildFilterInfo($clientId, $userId);
        $this->info(sprintf("\n🛡️ THREAT SCAN (Last %d days)%s", $days, $filterInfo));

        if ($countThreats > 0) {
            $this->line(sprintf("\n<bg=red;fg=white> ⚠️  THREATS DETECTED (%d) </>", $countThreats));

            if (!empty($threats['creation_spike'])) {
                $this->line("\n<fg=yellow>Token Creation Spikes:</>");
                foreach ($threats['creation_spike'] as $alert) {
                    $this->line("  • <fg=red>$alert</>");
                }
            }

            if (!empty($threats['refresh_anomaly'])) {
                $this->line("\n<fg=yellow>Refresh Anomalies:</>");
                foreach ($threats['refresh_anomaly'] as $alert) {
                    $this->line("  • <fg=red>$alert</>");
                }
            }
        } else {
            $this->info("\n<fg=green>✅ No threats found.</>");
        }

        return self::SUCCESS;
    }

    protected function exportCsv(int $days, ?int $clientId = null, ?int $userId = null): int
    {
        $data = $this->guard->exportData($days, $clientId, $userId);

        if (empty($data)) {
            $this->warn("No data to export for the specified period.");
            return self::SUCCESS;
        }

        $filename = sprintf(
            'passport_guard_export_%s.csv',
            now()->format('Y-m-d_His')
        );
        $filepath = storage_path($filename);

        $file = fopen($filepath, 'w');

        // Write headers
        fputcsv($file, array_keys($data[0]));

        // Write data rows
        foreach ($data as $row) {
            fputcsv($file, $row);
        }

        fclose($file);

        $this->info(sprintf("✅ Exported %d records to: %s", count($data), $filepath));

        return self::SUCCESS;
    }

    /**
     * Build filter info string for display.
     */
    protected function buildFilterInfo(?int $clientId, ?int $userId): string
    {
        $filters = [];

        if ($clientId !== null) {
            $filters[] = "Client #$clientId";
        }

        if ($userId !== null) {
            $filters[] = "User #$userId";
        }

        if (empty($filters)) {
            return '';
        }

        return ' [Filtered: ' . implode(', ', $filters) . ']';
    }
}
