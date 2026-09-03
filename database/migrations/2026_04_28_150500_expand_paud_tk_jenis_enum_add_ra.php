<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('paud_tk')) {
            return;
        }

        // Only relevant for MySQL (enum).
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE paud_tk MODIFY jenis ENUM('PAUD','TK','BA','RA') NOT NULL");
    }

    public function down(): void
    {
        if (!Schema::hasTable('paud_tk')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Map RA back to PAUD before shrinking enum.
        DB::statement("UPDATE paud_tk SET jenis = 'PAUD' WHERE jenis = 'RA'");
        DB::statement("ALTER TABLE paud_tk MODIFY jenis ENUM('PAUD','TK','BA') NOT NULL");
    }
};
