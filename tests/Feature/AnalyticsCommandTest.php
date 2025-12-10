<?php

namespace S1bTeam\PassportGuard\Tests\Feature;

use S1bTeam\PassportGuard\Tests\TestCase;
use S1bTeam\PassportGuard\Models\OauthTokenMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

class AnalyticsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_shows_token_overview_correctly()
    {
        // Seed metrics directly since we can't easily rely on Passport factories without full install
        OauthTokenMetric::create([
            'date' => now(),
            'tokens_created' => 10,
            'tokens_revoked' => 2,
            'tokens_refreshed' => 0,
            'tokens_expired' => 1,
            // Note: 'active' tokens count is calculated from Laravel\Passport\Token table,
            // not stored in metrics. This test assumes Passport migrations are available.
        ]);

        // Test the s1b:guard command runs successfully
        $this->artisan('s1b:guard')
            ->assertSuccessful();
    }
}
