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
        Schema::table('wali', function (Blueprint $table) {
            $table->string('hubungan_lainnya')->nullable()->after('hubungan');
            $table->string('nik', 16)->nullable()->after('no_hp');
            $table->unsignedSmallInteger('tahun_lahir')->nullable()->after('nik');
            $table->string('pendidikan')->nullable()->after('tahun_lahir');
            $table->string('pekerjaan')->nullable()->after('pendidikan');
            $table->string('pekerjaan_lainnya')->nullable()->after('pekerjaan');
            $table->string('penghasilan')->nullable()->after('pekerjaan_lainnya');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wali', function (Blueprint $table) {
            $table->dropColumn([
                'hubungan_lainnya',
                'nik',
                'tahun_lahir',
                'pendidikan',
                'pekerjaan',
                'pekerjaan_lainnya',
                'penghasilan',
            ]);
        });
    }
};
