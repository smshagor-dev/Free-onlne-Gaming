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
        Schema::create('lottery_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lottary_id')->constrained()->cascadeOnDelete();
            $table->string('ticket_number', 25)->unique();
            $table->string('transaction_number', 20)->unique();
            $table->decimal('amount', 12, 2);
            $table->timestamps();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('transaction_type')->nullable()->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lottery_transactions');

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('transaction_type');
        });
    }
};
