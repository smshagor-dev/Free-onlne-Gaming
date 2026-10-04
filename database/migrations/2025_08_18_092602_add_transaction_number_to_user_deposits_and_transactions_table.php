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
        Schema::table('user_deposits', function (Blueprint $table) {
            $table->string('transaction_number')->after('id')->nullable();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('transaction_number')->after('id')->nullable();
            $table->foreignId('user_deposit_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_deposits', function (Blueprint $table) {
            $table->dropColumn('transaction_number');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('transaction_number');
            $table->foreignId('user_deposit_id')->nullable(false)->change();
        });
    }
};
