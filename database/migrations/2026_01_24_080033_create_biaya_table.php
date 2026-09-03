<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('biaya', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tahun_ajaran_id');

            $table->enum('jenis_biaya', ['pendaftaran','daftar_ulang','udp']);
            $table->enum('kategori', ['wajib','opsional']);
            $table->enum('jenis_kelamin', ['laki-laki','perempuan','semua']);

            $table->string('nama_biaya');
            $table->integer('nominal');
            $table->boolean('aktif')->default(true);

            $table->timestamps();

            $table->foreign('tahun_ajaran_id')
                ->references('id')->on('tahun_ajaran')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biaya');
    }
};
