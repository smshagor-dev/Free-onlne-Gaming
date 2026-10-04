<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('provider_name')->nullable()->after('trx');
            $table->string('provider_transaction_id')->nullable()->after('provider_name');
            $table->string('provider_action')->nullable()->after('provider_transaction_id');
            $table->unique(
                ['provider_name', 'provider_transaction_id', 'provider_action'],
                'transactions_provider_idempotency_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique('transactions_provider_idempotency_unique');
            $table->dropColumn(['provider_name', 'provider_transaction_id', 'provider_action']);
        });
    }
};
