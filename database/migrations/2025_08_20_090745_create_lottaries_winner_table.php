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
        Schema::create('lottaries_winner', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('lottary_id')->constrained('lottaries')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('transaction_id')->constrained('lottery_transactions')->onDelete('cascade');
            $table->foreignId('prize_id')->constrained('lottaries_price')->onDelete('cascade');

            // Winner details
            $table->string('ticket_number');
            $table->decimal('price', 10, 2);
            $table->integer('position')->comment('Winner position: 1st, 2nd, etc.');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lottaries_winner');
    }
};
