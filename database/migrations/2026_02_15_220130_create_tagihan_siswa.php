<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tagihan_siswa', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('biaya_id');
            $table->unsignedBigInteger('voucher_id')->nullable(); // ✅ TAMBAHAN

            $table->integer('nominal');
            $table->integer('diskon')->default(0);
            $table->integer('total');

            $table->enum('status', ['belum_lunas','lunas'])->default('belum_lunas');

            $table->timestamps();

            $table->foreign('siswa_id')
                  ->references('id')->on('siswa')
                  ->onDelete('cascade');

            $table->foreign('biaya_id')
                  ->references('id')->on('biaya')
                  ->onDelete('cascade');

            $table->foreign('voucher_id') // ✅ TAMBAHAN
                  ->references('id')->on('vouchers')
                  ->nullOnDelete();

            $table->unique(['siswa_id', 'biaya_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan_siswa');
    }
};
