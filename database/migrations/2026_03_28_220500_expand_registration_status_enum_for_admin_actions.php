<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE registrations MODIFY status ENUM('pending','diterima','ditolak','arsip') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('registrations')
            ->whereIn('status', ['ditolak', 'arsip'])
            ->update(['status' => 'pending']);

        DB::statement("ALTER TABLE registrations MODIFY status ENUM('pending','diterima') NOT NULL DEFAULT 'pending'");
    }
};
