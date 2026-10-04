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
        Schema::table('bonus_users', function (Blueprint $table) {
            $table->string('bonus_type')->nullable()->after('deposit_setting_id'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bonus_users', function (Blueprint $table) {
            $table->dropColumn('bonus_type');
        });
    }
};
