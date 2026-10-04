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
        Schema::create('cashback_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('cashback_percentage', 5, 2);
            $table->integer('lose_calculation');
            $table->integer('wager');
            $table->integer('playing_time'); 
            $table->string('activation_days');
            $table->integer('maximum_claim');
            $table->foreignId('level_id')->constrained('levels')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashback_settings');
    }
};
