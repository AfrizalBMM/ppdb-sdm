<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('ayah', 'no_hp')) {
            Schema::table('ayah', function (Blueprint $table) {
                $table->string('no_hp', 15)->nullable()->after('nik');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ayah', 'no_hp')) {
            Schema::table('ayah', function (Blueprint $table) {
                $table->dropColumn('no_hp');
            });
        }
    }
};
