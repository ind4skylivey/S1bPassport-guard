<?php

namespace S1bTeam\PassportGuard\Tests;

use S1bTeam\PassportGuard\S1bPassportGuardServiceProvider;
use Laravel\Passport\PassportServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [
            PassportServiceProvider::class,
            S1bPassportGuardServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Disable auto tracking during tests
        $app['config']->set('s1b-passport-guard.enabled', false);
    }

    protected function setUpDatabase(): void
    {
        $schema = $this->app['db']->connection()->getSchemaBuilder();

        // Users table
        $schema->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });

        // OAuth clients table
        $schema->create('oauth_clients', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->text('secret')->nullable();
            $table->text('redirect_uris')->nullable();
            $table->boolean('personal_access_client')->default(false);
            $table->boolean('password_client')->default(false);
            $table->boolean('revoked')->default(false);
            $table->timestamps();
        });

        // OAuth access tokens table
        $schema->create('oauth_access_tokens', function ($table) {
            $table->string('id', 100)->primary();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('client_id');
            $table->string('name')->nullable();
            $table->text('scopes')->nullable();
            $table->boolean('revoked')->default(false);
            $table->timestamps();
            $table->datetime('expires_at')->nullable();
        });

        // OAuth refresh tokens table
        $schema->create('oauth_refresh_tokens', function ($table) {
            $table->string('id', 100)->primary();
            $table->string('access_token_id', 100);
            $table->boolean('revoked')->default(false);
            $table->datetime('expires_at')->nullable();
        });

        // Package metrics table
        $schema->create('oauth_token_metrics', function ($table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->date('date')->index();
            $table->unsignedInteger('tokens_created')->default(0);
            $table->unsignedInteger('tokens_revoked')->default(0);
            $table->unsignedInteger('tokens_refreshed')->default(0);
            $table->unsignedInteger('tokens_expired')->default(0);
            $table->unsignedInteger('failed_requests')->default(0);
            $table->decimal('avg_token_lifespan_hours', 8, 2)->nullable();
            $table->timestamps();
        });
    }
}
