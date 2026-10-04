<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposit_settings', function (Blueprint $table) {
            $table->enum('bonus_type', [
                'Welcome Bonus',
                'First Deposit Bonus',
                'Provider',
                'Day',
                'Birthday Bonus',
                'Special Bonus',
            ])->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('deposit_settings', function (Blueprint $table) {
            $table->enum('bonus_type', [
                'First Deposit Bonus',
                'Provider',
                'Day',
                'Birthday Bonus',
                'Special Bonus',
            ])->nullable()->change();
        });
    }
};
