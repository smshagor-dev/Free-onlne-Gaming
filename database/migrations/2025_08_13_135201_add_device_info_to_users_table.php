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
        Schema::table('users', function (Blueprint $table) {
            $table->string('device_type')->nullable()->after('user_browser');
            $table->string('device_name')->nullable()->after('device_type');
            $table->string('user_country')->nullable()->after('user_location');
            $table->string('user_region')->nullable()->after('user_country');
            $table->string('user_city')->nullable()->after('user_region');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['device_type', 'device_name', 'user_country', 'user_region', 'user_city']);
        });
    }
};
