<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();

            $table->string('kode')->unique();
            $table->string('nama');
            $table->enum('jenis_biaya', ['pendaftaran','daftar_ulang','udp']);
            $table->integer('diskon_nominal');

            $table->integer('maks_penggunaan');
            $table->integer('digunakan')->default(0);

            $table->boolean('aktif')->default(true);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
