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
        Schema::create('ibu', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('siswa_id')->unique();

            $table->string('nama');
            $table->string('no_hp',14);

            $table->char('nik',16)->nullable();
            $table->year('tahun_lahir')->nullable();
            $table->string('pendidikan')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('pekerjaan_lainnya')->nullable();
            $table->string('penghasilan')->nullable();

            $table->timestamps();

            $table->foreign('siswa_id')
                ->references('id')
                ->on('siswa')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ibu');
    }
};
