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
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('registration_id');

            // ================= IDENTITAS =================
            $table->string('nama');
            $table->enum('jenis_kelamin',['laki-laki','perempuan']);
            $table->char('nik',16)->unique();
            $table->char('no_kk',16);

            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('akta_no')->nullable();

            $table->string('agama')->default('Islam');
            $table->string('kewarganegaraan')->default('Indonesia');
            $table->string('berkebutuhan_khusus')->default('Tidak');

            $table->string('tinggal_bersama');
            $table->string('transportasi');

            // ================= PROGRAM BANTUAN =================
            $table->string('no_kks')->nullable();
            $table->string('kps')->nullable();
            $table->string('kip')->nullable();

            // ================= HASIL =================
            $table->string('hasil_tes');

            $table->enum('status',['pending','diterima'])
                ->default('pending');

            $table->timestamps();

            // ================= FOREIGN KEY =================

            $table->foreign('registration_id')
                ->references('id')
                ->on('registrations')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
