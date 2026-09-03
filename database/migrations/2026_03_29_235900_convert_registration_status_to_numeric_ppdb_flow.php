<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('registrations', 'status') && !Schema::hasColumn('registrations', 'status_tmp')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->unsignedTinyInteger('status_tmp')->default(1)->after('voucher_id');
            });

            DB::statement("UPDATE registrations SET status_tmp = CASE
                WHEN status IN ('diterima') THEN 3
                WHEN status IN ('pending') THEN 1
                ELSE 1
            END");

            Schema::table('registrations', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        if (Schema::hasColumn('registrations', 'status_tmp') && !Schema::hasColumn('registrations', 'status')) {
            DB::statement("ALTER TABLE registrations CHANGE status_tmp status TINYINT UNSIGNED NOT NULL DEFAULT 1");
        }

        if (!Schema::hasColumn('registrations', 'status')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->unsignedTinyInteger('status')->default(1)->after('voucher_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('registrations', 'status') && !Schema::hasColumn('registrations', 'status_text_tmp')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->enum('status_text_tmp', ['pending', 'diterima', 'ditolak', 'arsip'])
                    ->default('pending')
                    ->after('voucher_id');
            });

            DB::statement("UPDATE registrations SET status_text_tmp = CASE
                WHEN status = 3 THEN 'diterima'
                ELSE 'pending'
            END");

            Schema::table('registrations', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        if (Schema::hasColumn('registrations', 'status_text_tmp') && !Schema::hasColumn('registrations', 'status')) {
            DB::statement("ALTER TABLE registrations CHANGE status_text_tmp status ENUM('pending','diterima','ditolak','arsip') NOT NULL DEFAULT 'pending'");
        }
    }
};
