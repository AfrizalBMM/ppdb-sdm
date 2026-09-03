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
        if (!Schema::hasTable('kelas_siswa')) {
            Schema::create('kelas_siswa', function (Blueprint $table) {
                $table->id();
                $table->string('nama_kelas', 100)->unique();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('siswa') && !Schema::hasColumn('siswa', 'kelas_siswa_id')) {
            Schema::table('siswa', function (Blueprint $table) {
                $table->unsignedBigInteger('kelas_siswa_id')->nullable()->after('registration_id');
                $table->foreign('kelas_siswa_id')->references('id')->on('kelas_siswa')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('siswa') && Schema::hasColumn('siswa', 'kelas_siswa_id')) {
            Schema::table('siswa', function (Blueprint $table) {
                $table->dropForeign(['kelas_siswa_id']);
                $table->dropColumn('kelas_siswa_id');
            });
        }

        Schema::dropIfExists('kelas_siswa');
    }
};
