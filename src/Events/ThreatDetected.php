<?php

namespace S1bTeam\PassportGuard\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ThreatDetected
{
    use Dispatchable, SerializesModels;

    public string $type;
    public string $message;
    public ?int $clientId;
    public ?int $userId;
    public array $metadata;

    /**
     * Create a new event instance.
     *
     * @param string $type The type of threat (e.g., 'creation_spike', 'refresh_anomaly')
     * @param string $message Human readable alert message
     * @param int|null $clientId Related Client ID
     * @param int|null $userId Related User ID
     * @param array $metadata Additional context data
     */
    public function __construct(string $type, string $message, ?int $clientId = null, ?int $userId = null, array $metadata = [])
    {
        $this->type = $type;
        $this->message = $message;
        $this->clientId = $clientId;
        $this->userId = $userId;
        $this->metadata = $metadata;
    }
}
