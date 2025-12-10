<?php

namespace S1bTeam\PassportGuard\Tests\Unit;

use S1bTeam\PassportGuard\Tests\TestCase;
use S1bTeam\PassportGuard\Services\ThreatDetectorService;

class ThreatDetectorServiceTest extends TestCase
{
    protected ThreatDetectorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ThreatDetectorService();
    }

    public function test_detect_threats_returns_expected_structure(): void
    {
        $threats = $this->service->detectThreats(7);

        $this->assertArrayHasKey('creation_spike', $threats);
        $this->assertArrayHasKey('refresh_anomaly', $threats);
        $this->assertIsArray($threats['creation_spike']);
        $this->assertIsArray($threats['refresh_anomaly']);
    }

    public function test_detect_threats_accepts_filters(): void
    {
        $threats = $this->service->detectThreats(7, clientId: 1, userId: 1);

        $this->assertIsArray($threats);
    }

    public function test_threshold_config_is_loaded(): void
    {
        // Access protected properties via reflection
        $reflection = new \ReflectionClass($this->service);

        $spikeThreshold = $reflection->getProperty('creationSpikeThreshold');
        $spikeThreshold->setAccessible(true);

        $maxRefreshes = $reflection->getProperty('maxRefreshesHour');
        $maxRefreshes->setAccessible(true);

        // Default config values
        $this->assertEquals(2.0, $spikeThreshold->getValue($this->service)); // 200 / 100
        $this->assertEquals(50, $maxRefreshes->getValue($this->service));
    }
}
