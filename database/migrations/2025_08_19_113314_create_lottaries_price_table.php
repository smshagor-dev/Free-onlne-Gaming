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
        Schema::create('lottaries_price', function (Blueprint $table) {
            $table->id();

            // Relationship with lotteries table
            $table->foreignId('lottary_id')
                ->constrained('lottaries')
                ->onDelete('cascade');

            // Relationship with users table
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');


            $table->string('name');

            $table->string('description');
            
            $table->decimal('price', 10, 2);

            // Random unique lottery card number
            $table->string('price_number', 200);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lottaries_price');
    }
};
