<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_pendukung', function (Blueprint $table) {
            $table->unsignedTinyInteger('anak_ke')->nullable()->after('jumlah_saudara');
        });
    }

    public function down(): void
    {
        Schema::table('data_pendukung', function (Blueprint $table) {
            $table->dropColumn('anak_ke');
        });
    }
};
