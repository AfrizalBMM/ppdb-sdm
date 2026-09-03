<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\LogCetak;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LogCetakController extends Controller
{
    // ================== CETAK FORMULIR ==================
    public function cetakFormulir(Request $request, \App\Services\CetakService $cetakService)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'nama_panitia' => 'required_without_all:nama_penerima,nama_petugas|string|max:255',
            'nama_penerima' => 'required_without_all:nama_panitia,nama_petugas|string|max:255',
            'nama_petugas' => 'required_without_all:nama_panitia,nama_penerima|string|max:255',
        ]);

        $namaPenerima = trim((string) ($request->nama_panitia ?? $request->nama_penerima ?? $request->nama_petugas));

        $siswa = Siswa::with([
            'ibu','ayah','wali','alamat','dataPendukung.paudTk','registration.tahunAjaran'
        ])->findOrFail($request->siswa_id);

        $pdf = $cetakService->generatePdfFormulir($siswa, $namaPenerima);

        $fileName = 'FORMULIR-' . preg_replace('/[^A-Za-z0-9\-]+/', '-', strtoupper($siswa->nama)) . '.pdf';

        return $pdf->download($fileName);
    }

    // ================== LIST LOG ==================
    public function index()
    {
        $logs = LogCetak::with('siswa')->latest()->get();

        return view('admin.log-cetak.index', compact('logs'));
    }
}
