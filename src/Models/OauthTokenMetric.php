<?php

namespace S1bTeam\PassportGuard\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\Client;
use Illuminate\Foundation\Auth\User;

class OauthTokenMetric extends Model
{
    use HasFactory;

    protected $table = 'oauth_token_metrics';

    protected $fillable = [
        'client_id',
        'user_id',
        'date',
        'tokens_created',
        'tokens_revoked',
        'tokens_refreshed',
        'tokens_expired',
        'failed_requests',
        'total_token_lifespan_hours',
    ];

    protected $casts = [
        'date' => 'date',
        'tokens_created' => 'integer',
        'tokens_revoked' => 'integer',
        'tokens_refreshed' => 'integer',
        'tokens_expired' => 'integer',
        'failed_requests' => 'integer',
        'total_token_lifespan_hours' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model', User::class), 'user_id');
    }
}