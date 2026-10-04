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
            $table->string('remember')->nullable(); // custom remember field
            $table->string('mobile_number')->nullable();
            $table->string('photo')->nullable();
            $table->string('user_location')->nullable();
            $table->string('user_ip')->nullable();
            $table->string('user_browser')->nullable();
            $table->string('username')->unique()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'remember',
                'mobile_number',
                'photo',
                'user_location',
                'user_ip',
                'user_browser',
                'username'
            ]);
        });
    }
};
