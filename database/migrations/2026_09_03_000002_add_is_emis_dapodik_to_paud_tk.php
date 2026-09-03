<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paud_tk', function (Blueprint $table) {
            $table->boolean('is_emis_dapodik')->default(false)->after('akreditasi');
        });
    }

    public function down(): void
    {
        Schema::table('paud_tk', function (Blueprint $table) {
            $table->dropColumn('is_emis_dapodik');
        });
    }
};
