<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Services\VoucherClaimService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VoucherKlaimController extends Controller
{
    public function __construct(private readonly VoucherClaimService $voucherClaimService)
    {
    }

    public function klaim(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'voucher_id' => 'required|integer|exists:vouchers,id',
            'petugas' => 'nullable|string|max:100',
        ]);

        $aktor = $this->actorLabel($validated['petugas'] ?? null);

        try {
            $tagihan = $this->voucherClaimService->claim($siswa, (int) $validated['voucher_id'], $aktor);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $kode = $tagihan->kode_voucher ?: '-';
        $diskon = number_format((int) $tagihan->diskon, 0, ',', '.');

        return back()->with('success', 'Voucher ' . $kode . ' berhasil diklaim. Potongan Rp ' . $diskon . ' diterapkan pada biaya ' . ui_label($tagihan->biaya?->jenis_biaya ?? '-') . '.');
    }

    public function batal(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'petugas' => 'nullable|string|max:100',
        ]);

        $aktor = $this->actorLabel($validated['petugas'] ?? null);

        try {
            $this->voucherClaimService->cancel($siswa, $aktor);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', 'Klaim voucher berhasil dibatalkan. Total tagihan dikembalikan ke nominal penuh.');
    }

    private function actorLabel(?string $nama): string
    {
        $nama = trim((string) $nama);

        if ($nama !== '') {
            return $nama;
        }

        $user = auth()->user();

        return $user?->name ?: 'Admin';
    }
}
