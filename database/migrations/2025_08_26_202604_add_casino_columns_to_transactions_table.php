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
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('session_id')->nullable()->after('user_id');
            $table->string('trx_type', 5)->nullable()->after('amount'); 
            $table->string('trx')->nullable()->after('trx_type');
            $table->longText('casino_details')->nullable()->after('trx_type');
            $table->longText('remark')->nullable()->after('casino_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['session_id', 'trx_type', 'trx', 'casino_details']);
        });
    }
};
