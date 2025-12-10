<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oauth_token_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('oauth_clients')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date')->index();
            $table->unsignedInteger('tokens_created')->default(0);
            $table->unsignedInteger('tokens_revoked')->default(0);
            $table->unsignedInteger('tokens_refreshed')->default(0);
            $table->unsignedInteger('tokens_expired')->default(0);
            $table->unsignedInteger('failed_requests')->default(0);
            $table->decimal('total_token_lifespan_hours', 10, 4)->default(0);
            $table->timestamps();

            $table->unique(['client_id', 'user_id', 'date'], 'client_user_date');
            $table->index('client_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oauth_token_metrics');
    }
};
