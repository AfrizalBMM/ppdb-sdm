<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\LogCetak;
use Barryvdh\DomPDF\Facade\Pdf;

class CetakService
{
    /**
     * Generate PDF Formulir Pendaftaran dan log cetak
     *
     * @param Siswa $siswa
     * @param string $namaPetugas
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generatePdfFormulir(Siswa $siswa, string $namaPetugas)
    {
        $siswa->loadMissing([
            'registration',
            'alamat',
            'ayah',
            'ibu',
            'wali',
            'dataPendukung.paudTk',
        ]);

        // SIMPAN LOG CETAK
        LogCetak::create([
            'siswa_id' => $siswa->id,
            'jenis_dokumen' => 'formulir',
            'nama_petugas' => $namaPetugas
        ]);

        $pdf = Pdf::loadView('pdf.formulir', [
            'siswa'   => $siswa,
            'petugas' => $namaPetugas
        ]);

        // F4 (215mm x 330mm) — samakan dengan @page di view
        $pdf->setPaper([0.0, 0.0, 609.45, 935.43], 'portrait');

        return $pdf;
    }
}
