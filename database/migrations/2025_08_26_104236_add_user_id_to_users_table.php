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
            $table->string('user_id', 10)->nullable()->after('id');
        });

        $users = \App\Models\User::all();
    foreach ($users as $user) {
        do {
            $randomId = mt_rand(1000000000, 9999999999);
        } while (\App\Models\User::where('user_id', $randomId)->exists());
        $user->user_id = $randomId;
        $user->save();
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
