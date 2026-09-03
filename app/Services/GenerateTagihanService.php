<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\Biaya;
use App\Models\TagihanSiswa;
use Illuminate\Support\Facades\DB;

class GenerateTagihanService
{
    public static function generate(Siswa $siswa): void
    {
        if ($siswa->tagihan()->exists()) {
            return;
        }

        DB::transaction(function () use ($siswa) {

            $biayaList = self::ambilBiaya($siswa);

            $jumlahTagihan = 0;

            foreach ($biayaList as $biaya) {
                self::simpanTagihan($siswa, $biaya);
                $jumlahTagihan++;
            }

            /*
            |--------------------------------------------------------------------------
            | LOG AKTIVITAS
            |--------------------------------------------------------------------------
            */

            logAktivitas(
                'Generate Tagihan',
                "Tagihan dibuat untuk siswa: {$siswa->nama} (ID: {$siswa->id}) | ".
                "Jumlah tagihan: {$jumlahTagihan}"
            );
        });
    }

    private static function ambilBiaya(Siswa $siswa)
    {
        return Biaya::aktif()
            ->untukTahun($siswa->registration->tahun_ajaran_id)
            ->untukJenisKelamin($siswa->jenis_kelamin)
            ->get();
    }

    private static function simpanTagihan(Siswa $siswa, $biaya): void
    {
        TagihanSiswa::create([
            'siswa_id'     => $siswa->id,
            'biaya_id'     => $biaya->id,
            'nominal'      => $biaya->nominal,
            'diskon'       => 0,
            'total'        => $biaya->nominal,

            // Persist initial remaining balance.
            'sisa'         => $biaya->nominal,
            'is_lunas'     => false,

            'voucher_id'   => null,
            'kode_voucher' => null,

            'status'       => 'belum_lunas',
        ]);
    }

}
