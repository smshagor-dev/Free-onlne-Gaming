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
        Schema::table('game_points', function (Blueprint $table) {
            $table->integer('points_amount')->default(0)->after('points');
            $table->integer('get_balance')->default(0)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_points', function (Blueprint $table) {
            $table->dropColumn('points_amount', 'get_balance');
        });
    }
};
