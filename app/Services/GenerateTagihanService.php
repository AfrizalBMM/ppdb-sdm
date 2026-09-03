<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\Biaya;
use App\Models\Voucher;
use App\Models\TagihanSiswa;
use Illuminate\Support\Facades\DB;

class GenerateTagihanService
{
    public static function generate(Siswa $siswa, ?int $voucherId = null): void
    {
        if ($siswa->tagihan()->exists()) {
            return;
        }

        DB::transaction(function () use ($siswa, $voucherId) {

            $voucher = self::ambilVoucher($voucherId);
            $biayaList = self::ambilBiaya($siswa);

            $voucherDipakai = false;
            $jumlahTagihan = 0;

            foreach ($biayaList as $biaya) {

                [$diskon, $pakaiVoucherBarisIni] =
                    self::hitungDiskon($voucher, $voucherDipakai, $biaya);

                if ($pakaiVoucherBarisIni) {
                    $voucherDipakai = true;
                }

                self::simpanTagihan(
                    $siswa,
                    $biaya,
                    $diskon,
                    $voucher,
                    $pakaiVoucherBarisIni
                );

                $jumlahTagihan++;
            }

            self::updateVoucher($voucher, $voucherDipakai);

            /*
            |--------------------------------------------------------------------------
            | LOG AKTIVITAS
            |--------------------------------------------------------------------------
            */

            $voucherKode = $voucherDipakai ? $voucher?->kode : '-';

            logAktivitas(
                'Generate Tagihan',
                "Tagihan dibuat untuk siswa: {$siswa->nama} (ID: {$siswa->id}) | ".
                "Jumlah tagihan: {$jumlahTagihan} | ".
                "Voucher digunakan: {$voucherKode}"
            );
        });
    }

    private static function ambilVoucher(?int $voucherId): ?Voucher
    {
        if (!$voucherId) {
            return null;
        }

        $voucher = Voucher::where('id', $voucherId)
            ->where('aktif', true)
            ->lockForUpdate()
            ->first();

        if (!$voucher) {
            return null;
        }

        return $voucher->masihBerlaku() ? $voucher : null;
    }

    private static function ambilBiaya(Siswa $siswa)
    {
        return Biaya::aktif()
            ->untukTahun($siswa->registration->tahun_ajaran_id)
            ->untukJenisKelamin($siswa->jenis_kelamin)
            ->get();
    }

    private static function hitungDiskon($voucher, bool $voucherDipakai, $biaya): array
    {
        if (
            $voucher &&
            !$voucherDipakai &&
            $voucher->jenis_biaya === $biaya->jenis_biaya
        ) {
            $diskon = min($voucher->diskon_nominal, $biaya->nominal);

            return [$diskon, true];
        }

        return [0, false];
    }

    private static function simpanTagihan(
        Siswa $siswa,
        $biaya,
        int $diskon,
        ?Voucher $voucher,
        bool $pakaiVoucherBarisIni
    ): void {

        TagihanSiswa::create([
            'siswa_id'     => $siswa->id,
            'biaya_id'     => $biaya->id,
            'nominal'      => $biaya->nominal,
            'diskon'       => $diskon,
            'total'        => max(0, $biaya->nominal - $diskon),

            // Persist initial remaining balance.
            'sisa'         => max(0, $biaya->nominal - $diskon),
            'is_lunas'     => false,

            'voucher_id'   => $pakaiVoucherBarisIni ? $voucher?->id : null,
            'kode_voucher' => $pakaiVoucherBarisIni ? $voucher?->kode : null,

            'status'       => 'belum_lunas',
        ]);
    }

    private static function updateVoucher(?Voucher $voucher, bool $voucherDipakai): void
    {
        if ($voucher && $voucherDipakai) {
            $voucher->increment('digunakan');
        }
    }

}
