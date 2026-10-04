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
        Schema::table('game_opens', function (Blueprint $table) {
            $table->integer('review')->default(0)->after('points');
            $table->string('comments')->default(0)->after('review');
            $table->integer('like')->default(0)->after('comments');
            $table->integer('dislike')->default(0)->after('like');
            $table->boolean('bookmark')->default(false)->after('dislike');
            $table->integer('report')->default(0)->after('bookmark');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_opens', function (Blueprint $table) {
            $table->dropColumn([
                'review',
                'comments',
                'like',
                'dislike',
                'bookmark',
                'report',
            ]);
        });
    }
};
