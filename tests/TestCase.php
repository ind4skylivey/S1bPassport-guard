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
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // We also need Passport migrations for the Token model
        // In a real package test we might load them from vendor/laravel/passport/database/migrations
        // but since we don't have vendor, we mock or assume the environment handles it.
        // For 'orchestra/testbench' usually we need to load legacy factories or migrations manually if not standard.
    }
}
