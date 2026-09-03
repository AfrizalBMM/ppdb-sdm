<?php

namespace App\Exports;

use App\Models\Registration;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PendaftarExport implements FromCollection, WithHeadings, ShouldAutoSize, WithColumnFormatting, WithStyles
{
    public function __construct(private readonly Collection $rows)
    {
    }

    public function headings(): array
    {
        return [
            'No',           // A
            'No Registrasi', // B
            'Nama',         // C
            'Jenis Kelamin', // D
            'Tanggal Daftar', // E
            'Status PPDB',  // F
            'Status Pembayaran', // G
            'NIK',          // H  ← must be text
        ];
    }

    /**
     * Format column H (NIK) as plain text so Excel doesn't mangle the 16-digit number.
     */
    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function styles(Worksheet $sheet): void
    {
        $sheet->getStyle('1')->getFont()->setBold(true);
    }

    public function collection(): Collection
    {
        return $this->rows->values()->map(function ($siswa, $index) {
            $statusPpdb = Registration::statusLabel((int) optional($siswa->registration)->status);
            $tagihanAktif = $siswa->tagihan->filter(fn ($t) => (float) $t->total > 0);

            if ($tagihanAktif->isEmpty()) {
                $statusPembayaran = 'Belum Ada Tagihan';
            } elseif ($tagihanAktif->every(fn ($t) => $t->status === 'lunas')) {
                $statusPembayaran = 'Lunas';
            } else {
                $statusPembayaran = 'Belum Lunas';
            }

            return [
                $index + 1,
                $siswa->registration->nomor_registrasi ?? '-',
                $siswa->nama,
                ui_label($siswa->jenis_kelamin ?? '-'),
                optional($siswa->registration?->tanggal_daftar)->format('d-m-Y') ?? '-',
                $statusPpdb,
                $statusPembayaran,
                (string) ($siswa->nik ?? '-'),  // force string → text cell
            ];
        });
    }
}
