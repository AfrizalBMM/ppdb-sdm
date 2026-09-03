<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\TagihanSiswa;
use App\Models\Voucher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Sistem klaim voucher:
 * - Voucher TIDAK lagi dipilih saat pendaftaran.
 * - Voucher diklaim oleh admin/panitia pada halaman rincian biaya.
 * - Syarat klaim: seluruh tagihan selain jenis biaya target voucher sudah LUNAS,
 *   dan tagihan jenis biaya target masih ada yang belum lunas.
 * - Voucher wajib diklaim dalam periode berlaku; lewat tanggal selesai = hangus.
 */
class VoucherClaimService
{
    public function __construct(private readonly StatusPpdbService $statusPpdbService)
    {
    }

    /**
     * Daftar voucher yang bisa diklaim untuk siswa tertentu.
     */
    public function getEligibleVouchers(Siswa $siswa): Collection
    {
        $tagihanList = $siswa->tagihan()
            ->with('biaya')
            ->get();

        if ($tagihanList->isEmpty()) {
            return collect();
        }

        // Satu siswa hanya boleh punya satu voucher aktif pada tagihannya.
        if ($tagihanList->firstWhere('voucher_id')) {
            return collect();
        }

        $belumLunas = $tagihanList->filter(fn (TagihanSiswa $t) => !$t->is_lunas);

        return Voucher::query()
            ->aktif()
            ->dalamPeriode()
            ->get()
            ->filter(function (Voucher $voucher) use ($belumLunas) {
                if (!$voucher->masihAdaKuota()) {
                    return false;
                }

                // Semua tagihan di luar jenis biaya target harus lunas.
                $belumLunasNonTarget = $belumLunas->filter(
                    fn (TagihanSiswa $t) => (string) ($t->biaya?->jenis_biaya ?? '') !== (string) $voucher->jenis_biaya
                );
                if ($belumLunasNonTarget->isNotEmpty()) {
                    return false;
                }

                // Harus masih ada tagihan target yang belum lunas (ada sisa untuk dipotong).
                return $belumLunas->contains(
                    fn (TagihanSiswa $t) => (string) ($t->biaya?->jenis_biaya ?? '') === (string) $voucher->jenis_biaya
                );
            })
            ->values();
    }

    /**
     * Klaim voucher untuk siswa: potongan diterapkan pada tagihan jenis biaya target.
     */
    public function claim(Siswa $siswa, int $voucherId, string $actorLabel = 'Admin'): TagihanSiswa
    {
        return DB::transaction(function () use ($siswa, $voucherId, $actorLabel) {
            $tagihanList = TagihanSiswa::query()
                ->with(['biaya', 'pembayaran'])
                ->where('siswa_id', $siswa->id)
                ->lockForUpdate()
                ->get();

            if ($tagihanList->isEmpty()) {
                throw ValidationException::withMessages([
                    'voucher_id' => 'Siswa belum memiliki tagihan biaya.',
                ]);
            }

            if ($tagihanList->firstWhere('voucher_id')) {
                throw ValidationException::withMessages([
                    'voucher_id' => 'Siswa sudah memiliki voucher yang diklaim. Batalkan klaim sebelumnya terlebih dahulu.',
                ]);
            }

            /** @var Voucher|null $voucher */
            $voucher = Voucher::query()->lockForUpdate()->find($voucherId);

            if (!$voucher) {
                throw ValidationException::withMessages([
                    'voucher_id' => 'Voucher tidak ditemukan.',
                ]);
            }

            if (!$voucher->aktif) {
                throw ValidationException::withMessages([
                    'voucher_id' => 'Voucher tidak aktif.',
                ]);
            }

            if ($voucher->tanggal_mulai && now()->startOfDay()->lt($voucher->tanggal_mulai->startOfDay())) {
                throw ValidationException::withMessages([
                    'voucher_id' => 'Voucher belum berlaku (berlaku mulai ' . $voucher->tanggal_mulai->format('d/m/Y') . ').',
                ]);
            }

            if ($voucher->tanggal_selesai && now()->endOfDay()->gt($voucher->tanggal_selesai->endOfDay())) {
                throw ValidationException::withMessages([
                    'voucher_id' => 'Voucher sudah hangus (melewati tenggat ' . $voucher->tanggal_selesai->format('d/m/Y') . ').',
                ]);
            }

            if (!$voucher->masihAdaKuota()) {
                throw ValidationException::withMessages([
                    'voucher_id' => 'Kuota voucher sudah habis.',
                ]);
            }

            // Syarat klaim: seluruh tagihan selain jenis biaya target harus lunas.
            $belumLunasNonTarget = $tagihanList->filter(
                fn (TagihanSiswa $t) => !$t->is_lunas
                    && (string) ($t->biaya?->jenis_biaya ?? '') !== (string) $voucher->jenis_biaya
            );

            if ($belumLunasNonTarget->isNotEmpty()) {
                $namaBiaya = $belumLunasNonTarget->first()->biaya?->nama_biaya ?? '-';
                throw ValidationException::withMessages([
                    'voucher_id' => 'Voucher belum bisa diklaim. Masih ada biaya lain yang belum lunas (mis. ' . $namaBiaya . ').',
                ]);
            }

            $tagihanTarget = $tagihanList->first(
                fn (TagihanSiswa $t) => !$t->is_lunas
                    && (string) ($t->biaya?->jenis_biaya ?? '') === (string) $voucher->jenis_biaya
            );

            if (!$tagihanTarget) {
                throw ValidationException::withMessages([
                    'voucher_id' => 'Biaya target voucher (' . ui_label($voucher->jenis_biaya) . ') sudah lunas atau tidak dimiliki siswa ini. Voucher tidak bisa diklaim.',
                ]);
            }

            // Diskon dibatasi maksimal sisa nominal yang belum terbayar
            // agar tidak menimbulkan kelebihan pembayaran.
            $sisaNominal = max(0, (int) $tagihanTarget->nominal - (int) $tagihanTarget->total_dibayar);
            $diskon = min((int) $voucher->diskon_nominal, $sisaNominal);

            if ($diskon <= 0) {
                throw ValidationException::withMessages([
                    'voucher_id' => 'Diskon voucher tidak dapat diterapkan (sisa biaya target sudah 0).',
                ]);
            }

            $tagihanTarget->update([
                'diskon' => $diskon,
                'total' => max(0, (int) $tagihanTarget->nominal - $diskon),
                'voucher_id' => $voucher->id,
                'kode_voucher' => $voucher->kode,
            ]);

            $voucher->increment('digunakan');

            $tagihanTarget->unsetRelation('pembayaran');
            $tagihanTarget->refreshStatus();

            $this->statusPpdbService->syncBySiswa($siswa);

            logAktivitas(
                $actorLabel . ' - Klaim Voucher',
                'Mengklaim voucher ' . $voucher->kode
                . ' untuk siswa ' . $siswa->nama . ' (ID: ' . $siswa->id . ')'
                . ' pada tagihan ID ' . $tagihanTarget->id
                . ' dengan potongan Rp ' . number_format($diskon, 0, ',', '.') . '.'
            );

            return $tagihanTarget;
        });
    }

    /**
     * Guard perubahan pembayaran: pengurangan pembayaran (edit/hapus cicilan)
     * tidak boleh membuat tagihan biaya lain menjadi belum lunas selama masih
     * ada voucher yang diklaim (syarat klaim: semua biaya lain lunas).
     *
     * @param int $totalTerbayarSetelah total pembayaran tagihan setelah perubahan
     */
    public function assertPerubahanPembayaranAman(TagihanSiswa $tagihan, int $totalTerbayarSetelah): void
    {
        $claimed = TagihanSiswa::query()
            ->where('siswa_id', $tagihan->siswa_id)
            ->whereNotNull('voucher_id')
            ->where('id', '!=', $tagihan->id)
            ->first();

        if (!$claimed) {
            return;
        }

        if ($totalTerbayarSetelah < (int) $tagihan->total) {
            throw ValidationException::withMessages([
                'nominal_bayar' => 'Tidak dapat mengurangi pembayaran biaya "' . ($tagihan->biaya->nama_biaya ?? '-')
                    . '" karena akan menjadi belum lunas, sedangkan voucher ' . $claimed->kode_voucher
                    . ' sudah diklaim (syarat klaim: seluruh biaya lain lunas). Batalkan klaim voucher terlebih dahulu.',
            ]);
        }
    }

    /**
     * Batalkan klaim voucher pada tagihan siswa (kuota voucher dikembalikan).
     */
    public function cancel(Siswa $siswa, string $actorLabel = 'Admin'): TagihanSiswa
    {
        return DB::transaction(function () use ($siswa, $actorLabel) {
            $tagihanList = TagihanSiswa::query()
                ->with(['biaya', 'pembayaran'])
                ->where('siswa_id', $siswa->id)
                ->lockForUpdate()
                ->get();

            $tagihanVoucher = $tagihanList->firstWhere('voucher_id');

            if (!$tagihanVoucher) {
                throw ValidationException::withMessages([
                    'voucher_id' => 'Tidak ada voucher yang diklaim pada siswa ini.',
                ]);
            }

            $oldVoucherId = $tagihanVoucher->voucher_id;
            $oldKode = $tagihanVoucher->kode_voucher;

            $tagihanVoucher->update([
                'diskon' => 0,
                'total' => (int) $tagihanVoucher->nominal,
                'voucher_id' => null,
                'kode_voucher' => null,
            ]);

            if ($oldVoucherId) {
                $voucher = Voucher::query()->lockForUpdate()->find($oldVoucherId);
                if ($voucher && (int) $voucher->digunakan > 0) {
                    $voucher->decrement('digunakan');
                }
            }

            $tagihanVoucher->unsetRelation('pembayaran');
            $tagihanVoucher->refreshStatus();

            $this->statusPpdbService->syncBySiswa($siswa);

            logAktivitas(
                $actorLabel . ' - Batalkan Klaim Voucher',
                'Membatalkan klaim voucher ' . ($oldKode ?: ('#' . ($oldVoucherId ?: '-')))
                . ' pada tagihan ID ' . $tagihanVoucher->id
                . ' siswa ' . $siswa->nama . ' (ID: ' . $siswa->id . ').'
            );

            return $tagihanVoucher;
        });
    }
}
