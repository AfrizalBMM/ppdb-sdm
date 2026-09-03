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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();

            $table->string('nomor_registrasi')->unique();
            $table->date('tanggal_daftar');

            $table->unsignedBigInteger('tahun_ajaran_id');
            $table->unsignedBigInteger('voucher_id')->nullable();

            $table->enum('status', ['pending','diterima'])
                ->default('pending');

            $table->unsignedBigInteger('input_by')->nullable();

            $table->timestamps();

            // ================= FOREIGN KEY =================

            $table->foreign('tahun_ajaran_id')
                ->references('id')
                ->on('tahun_ajaran')
                ->cascadeOnDelete();

            $table->foreign('voucher_id')
                ->references('id')
                ->on('vouchers')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
