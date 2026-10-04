<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deposit_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->enum('bonus_type', [
                'First Deposit Bonus', 
                'Provider', 
                'Day',
                'Birthday Bonus',
                'Special Bonus'
            ])->nullable();
            $table->json('providers')->nullable(); 
            $table->json('days')->nullable();
            $table->decimal('wager', 15, 2)->nullable();
            $table->decimal('minimum_bonus', 15, 2)->nullable();
            $table->enum('bonus_time', ['12 hour', '24 hour', '3 days', '7 days', '15 days', '1 month'])->nullable();
            $table->integer('maximum_claim_in_a_day')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposit_settings');
    }
};
