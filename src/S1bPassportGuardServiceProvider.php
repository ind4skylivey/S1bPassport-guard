<?php

namespace S1bTeam\PassportGuard;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Events\RefreshTokenCreated;
use Laravel\Passport\Token;
use S1bTeam\PassportGuard\Commands\GuardCommand;
use S1bTeam\PassportGuard\Commands\TrackExpiredTokensCommand;
use S1bTeam\PassportGuard\Listeners\TokenCreatedListener;
use S1bTeam\PassportGuard\Listeners\TokenRefreshedListener;
use S1bTeam\PassportGuard\Observers\TokenObserver;
use Illuminate\Support\Facades\Event;

class S1bPassportGuardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/s1b-passport-guard.php',
            's1b-passport-guard'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GuardCommand::class,
                TrackExpiredTokensCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../config/s1b-passport-guard.php' => config_path('s1b-passport-guard.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'migrations');
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if (config('s1b-passport-guard.enabled', true)) {
            // Listen to Passport events
            Event::listen(
                AccessTokenCreated::class,
                TokenCreatedListener::class
            );

            Event::listen(
                RefreshTokenCreated::class,
                TokenRefreshedListener::class
            );

            Event::listen(
                \S1bTeam\PassportGuard\Events\ThreatDetected::class,
                \S1bTeam\PassportGuard\Listeners\SendThreatNotification::class
            );

            // Observe Token model
            Token::observe(TokenObserver::class);
        }
    }
}
