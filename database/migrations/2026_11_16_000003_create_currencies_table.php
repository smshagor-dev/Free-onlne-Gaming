<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table): void {
            $table->id();
            $table->char('code', 3)->unique();
            $table->string('name', 100);
            $table->string('symbol', 16);
            $table->char('numeric_code', 3)->nullable();
            $table->unsignedTinyInteger('decimals')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
