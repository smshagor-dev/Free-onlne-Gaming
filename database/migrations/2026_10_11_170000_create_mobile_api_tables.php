<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_api_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('auth_fingerprint', 64);
            $table->string('device_name', 80);
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at')->index();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
        Schema::create('mobile_game_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 30);
            $table->string('game_id', 120);
            $table->boolean('favorite')->default(false);
            $table->json('snapshot')->nullable();
            $table->timestamp('played_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'provider', 'game_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_game_states');
        Schema::dropIfExists('mobile_api_tokens');
    }
};
