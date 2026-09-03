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
        Schema::create('data_pendukung', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('siswa_id')->unique();

            $table->smallInteger('tinggi')->nullable();
            $table->smallInteger('berat')->nullable();
            $table->smallInteger('jarak')->nullable();
            $table->smallInteger('jumlah_saudara')->nullable();

            $table->unsignedBigInteger('paud_tk_id')->nullable();
            $table->text('alamat_tk')->nullable();

            $table->text('hobi')->nullable();
            $table->string('cita_cita')->nullable();

            $table->timestamps();

            $table->foreign('siswa_id')
                ->references('id')
                ->on('siswa')
                ->cascadeOnDelete();

            $table->foreign('paud_tk_id')
                ->references('id')
                ->on('paud_tk')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_pendukung');
    }
};
