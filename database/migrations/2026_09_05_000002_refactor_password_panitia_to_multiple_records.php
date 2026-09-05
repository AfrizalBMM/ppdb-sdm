<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ubah struktur tabel password_panitia:
 * - Sebelumnya: foreignId tahun_ajaran_id (unique) + password  (single record per tahun ajaran)
 * - Sesudahnya: nama (unique) + password  (multiple record, lepas tahun ajaran)
 *
 * Data lama (jika ada) dipertahankan: kolom nama diisi default berdasarkan id.
 *
 * Catatan urutan drop di MySQL:
 *  1. Drop FOREIGN KEY constraint terlebih dahulu
 *  2. Baru drop UNIQUE index (butuh foreign key hilang dulu)
 *  3. Lalu drop kolomnya
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom nama (nullable sementara untuk migrasi data) jika belum ada
        if (!Schema::hasColumn('password_panitia', 'nama')) {
            Schema::table('password_panitia', function (Blueprint $table) {
                $table->string('nama')->nullable()->after('id');
            });
        }

        // 2. Isi default nama untuk row lama (jika ada) yang masih null
        DB::table('password_panitia')
            ->whereNull('nama')
            ->update([
                'nama' => DB::raw("CONCAT('Panitia ', id)"),
            ]);

        // 3. Drop foreign key + unique index + kolom tahun_ajaran_id (jika masih ada)
        if (Schema::hasColumn('password_panitia', 'tahun_ajaran_id')) {
            // Drop foreign key constraint terlebih dahulu
            $sm = Schema::getConnection()->getSchemaBuilder();
            $hasFk = collect($sm->getForeignKeys('password_panitia'))
                ->contains(fn ($fk) => in_array('tahun_ajaran_id', $fk['columns'] ?? []));

            if ($hasFk) {
                Schema::table('password_panitia', function (Blueprint $table) {
                    $table->dropForeign(['tahun_ajaran_id']);
                });
            }

            // Setelah FK di-drop, baru bisa drop unique index
            $indexes = collect($sm->getIndexes('password_panitia'))
                ->map(fn ($i) => $i['name'])
                ->toArray();

            Schema::table('password_panitia', function (Blueprint $table) use ($indexes) {
                if (in_array('password_panitia_tahun_ajaran_id_unique', $indexes, true)) {
                    $table->dropUnique('password_panitia_tahun_ajaran_id_unique');
                }
            });

            // Baru drop kolomnya
            Schema::table('password_panitia', function (Blueprint $table) {
                $table->dropColumn('tahun_ajaran_id');
            });
        }

        // 4. Finalisasi kolom nama: NOT NULL + UNIQUE (jika belum)
        $sm = Schema::getConnection()->getSchemaBuilder();
        $indexes = collect($sm->getIndexes('password_panitia'))
            ->map(fn ($i) => $i['name'])
            ->toArray();

        Schema::table('password_panitia', function (Blueprint $table) use ($indexes) {
            $table->string('nama')->nullable(false)->change();

            if (!in_array('password_panitia_nama_unique', $indexes, true)) {
                $table->unique('nama');
            }
        });
    }

    public function down(): void
    {
        // 1. Drop unique di nama
        Schema::table('password_panitia', function (Blueprint $table) {
            $table->dropUnique('password_panitia_nama_unique');
        });

        // 2. Kembalikan struktur: tambah tahun_ajaran_id
        Schema::table('password_panitia', function (Blueprint $table) {
            $table->foreignId('tahun_ajaran_id')
                ->nullable()
                ->constrained('tahun_ajaran')
                ->cascadeOnDelete()
                ->after('id');
        });

        // 3. Hapus kolom nama
        Schema::table('password_panitia', function (Blueprint $table) {
            $table->dropColumn('nama');
        });

        // 4. Kembalikan unique di tahun_ajaran_id
        Schema::table('password_panitia', function (Blueprint $table) {
            $table->unique('tahun_ajaran_id');
        });
    }
};
