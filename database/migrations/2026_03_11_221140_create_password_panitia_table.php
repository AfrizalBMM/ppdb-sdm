<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_panitia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->string('password');
            $table->timestamps();

            $table->unique('tahun_ajaran_id'); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_panitia');
    }
};