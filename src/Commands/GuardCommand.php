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

        if ($this->option('export') === 'csv') {
            return $this->exportCsv($days);
        }

        if ($this->option('threats')) {
            return $this->showThreats($days);
        }

        $this->showDashboard($days);

        return self::SUCCESS;
    }

    protected function showDashboard(int $days): void
    {
        $scan = $this->guard->scan($days);
        $threats = $scan['threats'];

        $this->info(sprintf("\n🛡️ S1B PASSPORT GUARD REPORT (Last %d days)", $days));
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

        $this->line("");
    }

    protected function showThreats(int $days): int
    {
        $threats = $this->detector->detectThreats($days);
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
             $this->info("No threats found.");
        }
        return self::SUCCESS;
    }

    protected function exportCsv(int $days): int
    {
        $this->info("Exporting data to CSV... [Not implemented in MVP]");
        return self::SUCCESS;
    }
}
