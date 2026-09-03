<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('biaya', function (Blueprint $table) {
            $table->boolean('is_acuan_status_ppdb')
                ->default(false)
                ->after('aktif');
        });
    }

    public function down(): void
    {
        Schema::table('biaya', function (Blueprint $table) {
            $table->dropColumn('is_acuan_status_ppdb');
        });
    }
};
