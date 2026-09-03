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
        Schema::table('tagihan_siswa', function (Blueprint $table) {
            $table->integer('sisa')->default(0)->after('total')->comment('Sisa tagihan yang belum dibayar');
            $table->boolean('is_lunas')->default(false)->after('status')->comment('Status lunas/belum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tagihan_siswa', function (Blueprint $table) {
            $table->dropColumn(['sisa', 'is_lunas']);
        });
    }
};
