<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_saved_games', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 32);
            $table->string('provider_game_id', 191);
            $table->string('title');
            $table->string('image', 2048)->nullable();
            $table->json('genres')->nullable();
            $table->json('platforms')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'provider', 'provider_game_id'], 'catalog_saved_unique');
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('catalog_recent_games', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 32);
            $table->string('provider_game_id', 191);
            $table->string('title');
            $table->string('image', 2048)->nullable();
            $table->json('genres')->nullable();
            $table->json('platforms')->nullable();
            $table->timestamp('viewed_at')->index();
            $table->timestamps();
            $table->unique(['user_id', 'provider', 'provider_game_id'], 'catalog_recent_unique');
            $table->index(['user_id', 'viewed_at']);
        });

        Schema::create('user_game_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('preferred_genres')->nullable();
            $table->json('preferred_platforms')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_game_preferences');
        Schema::dropIfExists('catalog_recent_games');
        Schema::dropIfExists('catalog_saved_games');
    }
};