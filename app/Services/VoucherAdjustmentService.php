<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\TagihanSiswa;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VoucherAdjustmentService
{
    public function __construct(private readonly StatusPpdbService $statusPpdbService)
    {
    }

    /**
     * Adjust voucher usage for an existing student by mutating tagihan + voucher usage count
     * and reallocating payments if needed.
     *
     * IMPORTANT: Call this within an active DB transaction.
     */
    public function adjustForSiswa(Siswa $siswa, ?int $targetVoucherId, string $actorLabel = 'System'): void
    {
        // Lock all tagihan rows for the siswa.
        $tagihanList = TagihanSiswa::query()
            ->with(['biaya', 'pembayaran'])
            ->where('siswa_id', $siswa->id)
            ->lockForUpdate()
            ->get();

        if ($tagihanList->isEmpty()) {
            // Nothing to adjust.
            return;
        }

        $tagihanDenganVoucher = $tagihanList->whereNotNull('voucher_id')->values();
        if ($tagihanDenganVoucher->count() > 1) {
            throw ValidationException::withMessages([
                'voucher_id' => 'Data voucher pada tagihan tidak konsisten (lebih dari 1 tagihan memiliki voucher).',
            ]);
        }

        $tagihanOldVoucher = $tagihanDenganVoucher->first();
        $oldVoucherId = $tagihanOldVoucher?->voucher_id;

        // If nothing changes, exit early.
        if ((int) ($oldVoucherId ?? 0) === (int) ($targetVoucherId ?? 0)) {
            return;
        }

        // Cancel old voucher (if any).
        if ($tagihanOldVoucher) {
            $this->cancelVoucherOnTagihan($tagihanOldVoucher, $actorLabel);
        }

        // Claim new voucher (if any).
        if ($targetVoucherId) {
            $this->claimVoucherForSiswa($siswa, $tagihanList, $targetVoucherId, $actorLabel);
        }

        // Reallocate payments to avoid overpayment on any tagihan.
        $this->reallocatePaymentsIfNeeded($siswa, $actorLabel);

        // Refresh status for all tagihan.
        $this->refreshAllTagihanStatus($siswa);

        // Sync PPDB status.
        $this->statusPpdbService->syncBySiswa($siswa);
    }

    private function cancelVoucherOnTagihan(TagihanSiswa $tagihan, string $actorLabel): void
    {
        $oldVoucherId = $tagihan->voucher_id;
        $oldKode = $tagihan->kode_voucher;

        $tagihan->update([
            'diskon' => 0,
            'total' => (int) $tagihan->nominal,
            'voucher_id' => null,
            'kode_voucher' => null,
        ]);

        // Decrement voucher usage if voucher still exists.
        if ($oldVoucherId) {
            $voucher = Voucher::query()->lockForUpdate()->find($oldVoucherId);
            if ($voucher && (int) $voucher->digunakan > 0) {
                $voucher->decrement('digunakan');
            }
        }

        logAktivitas(
            $actorLabel . ' - Cancel Voucher',
            'Membatalkan voucher ' . ($oldKode ?: ('#' . ($oldVoucherId ?: '-')))
            . ' pada tagihan ID ' . $tagihan->id . ' siswa ID ' . $tagihan->siswa_id . '.'
        );
    }

    private function claimVoucherForSiswa(Siswa $siswa, $tagihanList, int $targetVoucherId, string $actorLabel): void
    {
        $voucher = Voucher::query()->lockForUpdate()->findOrFail($targetVoucherId);

        if (!$voucher->masihBerlaku()) {
            throw ValidationException::withMessages([
                'voucher_id' => 'Voucher tidak dapat digunakan (tidak aktif / periode habis / kuota habis).',
            ]);
        }

        $jenisBiaya = $voucher->jenis_biaya;
        $tagihanTarget = $tagihanList->first(function (TagihanSiswa $t) use ($jenisBiaya) {
            return (string) ($t->biaya?->jenis_biaya ?? '') === (string) $jenisBiaya;
        });

        if (!$tagihanTarget) {
            throw ValidationException::withMessages([
                'voucher_id' => 'Tagihan untuk jenis biaya voucher tidak ditemukan pada siswa ini.',
            ]);
        }

        $diskon = min((int) $voucher->diskon_nominal, (int) $tagihanTarget->nominal);

        $tagihanTarget->update([
            'diskon' => $diskon,
            'total' => max(0, (int) $tagihanTarget->nominal - $diskon),
            'voucher_id' => $voucher->id,
            'kode_voucher' => $voucher->kode,
        ]);

        $voucher->increment('digunakan');

        logAktivitas(
            $actorLabel . ' - Claim Voucher',
            'Meng-claim voucher ' . $voucher->kode . ' pada tagihan ID ' . $tagihanTarget->id
            . ' siswa ID ' . $siswa->id . ' (diskon Rp ' . number_format($diskon, 0, ',', '.') . ').'
        );
    }

    private function refreshAllTagihanStatus(Siswa $siswa): void
    {
        $tagihanList = TagihanSiswa::query()
            ->with('pembayaran')
            ->where('siswa_id', $siswa->id)
            ->lockForUpdate()
            ->get();

        foreach ($tagihanList as $tagihan) {
            $tagihan->unsetRelation('pembayaran');
            $tagihan->refreshStatus();
        }
    }

    /**
     * Reallocate excess payments from overpaid tagihan to other unpaid tagihan.
     *
     * This avoids hidden overpayments after voucher changes.
     */
    private function reallocatePaymentsIfNeeded(Siswa $siswa, string $actorLabel): void
    {
        $tagihanList = TagihanSiswa::query()
            ->with(['biaya', 'pembayaran' => function ($q) {
                $q->orderByDesc('tanggal_bayar')->orderByDesc('id');
            }])
            ->where('siswa_id', $siswa->id)
            ->lockForUpdate()
            ->get();

        if ($tagihanList->isEmpty()) {
            return;
        }

        // Lock all related payments too.
        $tagihanIds = $tagihanList->pluck('id')->all();
        Pembayaran::query()->whereIn('tagihan_siswa_id', $tagihanIds)->lockForUpdate()->get();

        $needsLoop = true;
        $movedTotal = 0;

        while ($needsLoop) {
            $needsLoop = false;

            // Reload a fresh snapshot each iteration to avoid stale payment relations
            // after updates/inserts.
            $tagihanList = TagihanSiswa::query()
                ->with(['biaya', 'pembayaran' => function ($q) {
                    $q->orderByDesc('tanggal_bayar')->orderByDesc('id');
                }])
                ->where('siswa_id', $siswa->id)
                ->lockForUpdate()
                ->get();

            // Compute current sisa/excess snapshot.
            $snapshot = $tagihanList->map(function (TagihanSiswa $t) {
                $paid = (int) $t->pembayaran->sum('nominal_bayar');
                $excess = max(0, $paid - (int) $t->total);
                $need = max(0, (int) $t->total - $paid);
                return [
                    'tagihan' => $t,
                    'paid' => $paid,
                    'excess' => $excess,
                    'need' => $need,
                ];
            });

            $donors = $snapshot->filter(fn ($x) => $x['excess'] > 0)->values();
            $receivers = $snapshot->filter(fn ($x) => $x['need'] > 0)->values();

            if ($donors->isEmpty() || $receivers->isEmpty()) {
                break;
            }

            foreach ($donors as $donorRow) {
                /** @var TagihanSiswa $donor */
                $donor = $donorRow['tagihan'];
                $excessRemaining = (int) $donorRow['excess'];

                if ($excessRemaining <= 0) {
                    continue;
                }

                foreach ($donor->pembayaran as $payment) {
                    if ($excessRemaining <= 0) {
                        break;
                    }

                    $paymentNominal = (int) $payment->nominal_bayar;
                    if ($paymentNominal <= 0) {
                        continue;
                    }

                    // Find current receiver (re-evaluate each time to keep it correct).
                    $receiver = $tagihanList
                        ->filter(function (TagihanSiswa $t) {
                            $paid = (int) $t->pembayaran->sum('nominal_bayar');
                            return $paid < (int) $t->total;
                        })
                        ->sortBy('id')
                        ->first();

                    if (!$receiver) {
                        // Nowhere to move; leave remaining excess.
                        break;
                    }

                    $receiverPaid = (int) $receiver->pembayaran->sum('nominal_bayar');
                    $receiverNeed = max(0, (int) $receiver->total - $receiverPaid);

                    if ($receiverNeed <= 0) {
                        continue;
                    }

                    $move = min($excessRemaining, $receiverNeed, $paymentNominal);

                    if ($move <= 0) {
                        continue;
                    }

                    if ($move === $paymentNominal) {
                        // Move the entire payment record.
                        $payment->update([
                            'tagihan_siswa_id' => $receiver->id,
                        ]);
                    } else {
                        // Split the payment.
                        $payment->update([
                            'nominal_bayar' => $paymentNominal - $move,
                        ]);

                        Pembayaran::create([
                            'tagihan_siswa_id' => $receiver->id,
                            'tanggal_bayar' => $payment->tanggal_bayar,
                            'nominal_bayar' => $move,
                            'metode' => $payment->metode,
                            'admin_penerima' => $payment->admin_penerima,
                            'keterangan' => $payment->keterangan,
                        ]);
                    }

                    $excessRemaining -= $move;
                    $movedTotal += $move;
                    $needsLoop = true;
                }
            }
        }

        if ($movedTotal > 0) {
            logAktivitas(
                $actorLabel . ' - Realokasi Pembayaran',
                'Melakukan realokasi pembayaran sebesar Rp ' . number_format($movedTotal, 0, ',', '.')
                . ' untuk siswa ID ' . $siswa->id . ' akibat penyesuaian voucher.'
            );
        }
    }
}
