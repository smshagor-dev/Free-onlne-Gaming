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
        Schema::create('user_withdraw_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_withdrew_id')->constrained('user_withdrew')->onDelete('cascade');
            $table->foreignId('required_document_id')->constrained('required_documents')->onDelete('cascade');
            $table->longText('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_withdraw_documents');
    }
};
