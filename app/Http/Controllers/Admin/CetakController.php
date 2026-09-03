<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\Siswa;
use App\Models\Pembayaran;
use App\Models\LogCetak;    
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CetakController extends Controller
{
    private function loadRelasiCetakFormulir(Siswa $siswa): void
    {
        $siswa->load([
            'registration.tahunAjaran',
            'alamat',
            'ibu',
            'ayah',
            'wali',
            'dataPendukung',
            'tagihan.biaya',
            'tagihan.voucher',
        ]);
    }

    /**
     * CETAK FORMULIR PENDAFTARAN (F4)
     */
    public function formulir(Request $request, Siswa $siswa)
    {
        $this->loadRelasiCetakFormulir($siswa);

        if ($request->boolean('preview')) {
            logAktivitas(
                'Review Formulir',
                'Review formulir pendaftaran #' . $siswa->id . ' ' . $siswa->nama .
                ' (' . $siswa->registration->nomor_registrasi . ')'
            );

            return view('pdf.formulir', compact('siswa'));
        }

        logAktivitas(
            'Cetak Formulir',
            'Cetak formulir pendaftaran #' . $siswa->id . ' ' . $siswa->nama .
            ' (' . $siswa->registration->nomor_registrasi . ')'
        );

        $pdf = Pdf::loadView('pdf.formulir', compact('siswa'))
            ->setPaper('f4', 'portrait'); // PALING AMAN

        return $pdf->stream(
            'Formulir-' . $siswa->registration->nomor_registrasi . '.pdf'
        );
    }

    public function formulirPreview(Siswa $siswa)
    {
        $this->loadRelasiCetakFormulir($siswa);

        logAktivitas(
            'Review Formulir',
            'Review formulir pendaftaran #' . $siswa->id . ' ' . $siswa->nama .
            ' (' . $siswa->registration->nomor_registrasi . ')'
        );

        return view('pdf.formulir', compact('siswa'));
    }

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

    /**
     * CETAK NOTA PEMBAYARAN (2 PER A4)
     * NAMA ADMIN DIINPUT SAAT CETAK
     */
    public function nota(Request $request, Pembayaran $pembayaran)
{
    $request->validate([
        'nama_admin' => 'required|string|max:100'
    ]);

    $pembayaran->load([
        'tagihan.siswa.registration.tahunAjaran',
        'tagihan.siswa.alamat',
        'tagihan.biaya',
        'tagihan.voucher',
    ]);

    $namaAdmin = $request->nama_admin;

    logAktivitas(
        'Admin - Cetak Nota Pembayaran',
        'Mencetak nota pembayaran ID '.$pembayaran->id.
        ' untuk siswa '.$pembayaran->tagihan->siswa->nama.
        ' oleh admin/penerima '.$namaAdmin
    );

    $pdf = Pdf::loadView(
        'cetak.nota-2x-a4',
        compact('pembayaran','namaAdmin')
    )->setPaper('a4', 'portrait');

    return $pdf->stream(
        'Kwitansi-'.$pembayaran->id.'.pdf'
    );
}
}
