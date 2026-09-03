<?php

namespace App\Services;

use App\Models\Biaya;
use App\Models\Pembayaran;
use App\Models\Registration;
use App\Models\Siswa;
use App\Models\TagihanSiswa;

class StatusPpdbService
{
    public function syncBySiswa(Siswa $siswa): void
    {
        $siswa->loadMissing('registration');

        if (!$siswa->registration) {
            return;
        }

        $hasAcuan = Biaya::query()
            ->where('is_acuan_status_ppdb', true)
            ->exists();

        // Relevant tagihan are the acuan biaya (if configured), regardless of total.
        // Tagihan with total = 0 (e.g., fully waived by voucher) are treated as settled.
        $relevantTagihanQuery = TagihanSiswa::query()
            ->where('siswa_id', $siswa->id)
            ->when($hasAcuan, function ($q) {
                $q->whereHas('biaya', function ($q2) {
                    $q2->where('is_acuan_status_ppdb', true);
                });
            });

        $hasRelevantTagihan = (clone $relevantTagihanQuery)->exists();
        $hasBelumLunas = (clone $relevantTagihanQuery)
            ->where('total', '>', 0)
            ->where('status', '!=', 'lunas')
            ->exists();

        $hasAnyPayment = Pembayaran::query()
            ->whereHas('tagihan', function ($q) use ($siswa, $hasAcuan) {
                $q->where('siswa_id', $siswa->id)
                    ->when($hasAcuan, function ($q2) {
                        $q2->whereHas('biaya', function ($q3) {
                            $q3->where('is_acuan_status_ppdb', true);
                        });
                    });
            })
            ->exists();

        // Status ditentukan ulang dari tagihan acuan yang masih tersisa.
        // Jika semua tagihan relevan sudah lunas atau total=0, maka Peserta Didik.
        if ($hasRelevantTagihan && !$hasBelumLunas) {
            $targetStatus = Registration::STATUS_PESERTA_DIDIK;
        } elseif ($hasAnyPayment) {
            $targetStatus = Registration::STATUS_CALON;
        } else {
            $targetStatus = Registration::STATUS_BAKAL_CALON;
        }

        if ((int) $siswa->registration->status !== $targetStatus) {
            $siswa->registration->status = $targetStatus;
            $siswa->registration->save();
        }
    }
}
