<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pembayaran;
use App\Models\TagihanSiswa;
use App\Services\StatusPpdbService;
use Illuminate\Validation\ValidationException;

class PembayaranController extends Controller
{
    public function __construct(private readonly StatusPpdbService $statusPpdbService)
    {
    }

    public function store(Request $request)
    {
        $request->validate([
            'tagihan_siswa_id' => 'required|exists:tagihan_siswa,id',
            'nominal_bayar'    => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, &$pembayaran) {

            $tagihan = TagihanSiswa::with('pembayaran')
                ->lockForUpdate()
                ->findOrFail($request->tagihan_siswa_id);

            // gunakan accessor model
            $sisa = $tagihan->sisa;

            if ($request->nominal_bayar > $sisa) {
                throw ValidationException::withMessages([
                    'nominal_bayar' => 'Nominal melebihi sisa tagihan.'
                ]);
            }

            $pembayaran = Pembayaran::create([

                'tagihan_siswa_id' => $tagihan->id,
                'nominal_bayar'    => $request->nominal_bayar,
                'tanggal_bayar'    => $request->tanggal_bayar,
                'metode'           => $request->metode,
                'admin_penerima'   => $request->admin_penerima,
                'keterangan'       => $request->keterangan,

            ]);

            // Ensure status is recalculated from latest payments in DB.
            $tagihan->unsetRelation('pembayaran');
            $tagihan->refreshStatus();
            $this->statusPpdbService->syncBySiswa($tagihan->siswa);

            logAktivitas(
                'Pembayaran',
                'Pembayaran #'.$pembayaran->id.' '.
                $tagihan->biaya->nama_biaya.' untuk siswa '.$tagihan->siswa->nama.
                ' sebesar Rp '.number_format($request->nominal_bayar)
            );
        });

        return back()->with('success', 'Pembayaran berhasil disimpan');
    }
}