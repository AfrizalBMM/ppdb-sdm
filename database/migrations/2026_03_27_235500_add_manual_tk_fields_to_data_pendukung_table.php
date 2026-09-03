<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_pendukung', function (Blueprint $table) {
            $table->boolean('is_tk_manual')->default(false)->after('paud_tk_id');
            $table->string('nama_tk_manual', 150)->nullable()->after('is_tk_manual');
        });
    }

    public function down(): void
    {
        Schema::table('data_pendukung', function (Blueprint $table) {
            $table->dropColumn(['is_tk_manual', 'nama_tk_manual']);
        });
    }
};
