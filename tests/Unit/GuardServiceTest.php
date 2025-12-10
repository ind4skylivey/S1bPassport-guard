<?php

namespace S1bTeam\PassportGuard\Tests\Unit;

use S1bTeam\PassportGuard\Tests\TestCase;
use S1bTeam\PassportGuard\Services\GuardService;
use S1bTeam\PassportGuard\Services\ThreatDetectorService;
use S1bTeam\PassportGuard\Models\OauthTokenMetric;

class GuardServiceTest extends TestCase
{
    protected GuardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GuardService(new ThreatDetectorService());
    }

    public function test_export_data_returns_empty_array_when_no_data(): void
    {
        $data = $this->service->exportData(30);

        $this->assertIsArray($data);
        $this->assertEmpty($data);
    }

    public function test_scan_returns_expected_structure(): void
    {
        // Without actual Passport Token table, we test structure
        $result = $this->service->scan(30);

        $this->assertArrayHasKey('active', $result);
        $this->assertArrayHasKey('expiring_7d', $result);
        $this->assertArrayHasKey('revoked', $result);
        $this->assertArrayHasKey('avg_lifespan_days', $result);
        $this->assertArrayHasKey('threats', $result);
        $this->assertArrayHasKey('top_clients', $result);
    }

    public function test_scan_accepts_filters(): void
    {
        // Test that method accepts filter parameters without error
        $result = $this->service->scan(30, clientId: 1, userId: 1);

        $this->assertIsArray($result);
    }
}
