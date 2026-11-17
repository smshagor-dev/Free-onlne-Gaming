<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('crypto_payments', function (Blueprint $table): void {
            $table->id();
            $table->uuid('reference')->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('provider', 24)->default('nowpayments');
            $table->string('invoice_id', 160)->nullable()->unique();
            $table->string('provider_payment_id', 160)->nullable()->unique();
            $table->string('checkout_url', 2048)->nullable();
            $table->decimal('amount', 15, 2);
            $table->char('currency', 3);
            $table->string('status', 24)->default('pending');
            $table->string('additional_status', 40)->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_payments');
    }
};
