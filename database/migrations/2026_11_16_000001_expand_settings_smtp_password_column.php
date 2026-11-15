<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Widen the legacy 255-byte column to hold authenticated encrypted secrets.
        // Existing plaintext values are preserved byte-for-byte.
        Schema::table('settings', function (Blueprint $table): void {
            $table->text('MAIL_PASSWORD')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Intentionally non-destructive: shrinking this field risks truncating
        // credentials saved by administrators after the upgrade.
    }
};
